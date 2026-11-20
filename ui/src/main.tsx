import { StrictMode } from 'react'
import { createRoot } from 'react-dom/client'
import { QueryClient, QueryClientProvider } from '@tanstack/react-query'
import {
  Outlet,
  RouterProvider,
  createHashHistory,
  createRootRoute,
  createRoute,
  createRouter,
  lazyRouteComponent,
  redirect,
} from '@tanstack/react-router'
import './index.css'
import { getSession } from './lib/api'
import { I18nProvider } from './lib/i18n'
import { WorkspaceProvider } from './lib/workspace'
import { Spinner } from './components/ui'
// Shell + the landing routes stay in the entry chunk so the first paint
// needs no extra request; every other screen is split into its own chunk
// and fetched on navigation, which keeps the initial download small.
import { Shell } from './components/Shell'
import { LoginPage } from './routes/LoginPage'
import { DashboardPage } from './routes/DashboardPage'

const lazyPage = (
  load: () => Promise<Record<string, unknown>>,
  name: string,
) => lazyRouteComponent(load, name)

const rootRoute = createRootRoute({
  component: () => (
    <WorkspaceProvider>
      <Outlet />
    </WorkspaceProvider>
  ),
})

const loginRoute = createRoute({
  getParentRoute: () => rootRoute,
  path: '/login',
  component: LoginPage,
})

const shellRoute = createRoute({
  getParentRoute: () => rootRoute,
  id: 'shell',
  beforeLoad: () => {
    if (!getSession()) throw redirect({ to: '/login' })
  },
  component: Shell,
})

const dashboardRoute = createRoute({
  getParentRoute: () => shellRoute,
  path: '/',
  component: DashboardPage,
})

const specRoute = createRoute({
  getParentRoute: () => shellRoute,
  path: '/spec',
  component: lazyPage(() => import('./routes/SpecPage'), 'SpecPage'),
  validateSearch: (search: Record<string, unknown>) => ({
    // accept both "20117" (router-serialized) and bare 3 (hand-written)
    caseId:
      search.caseId != null && search.caseId !== ''
        ? String(search.caseId)
        : undefined,
  }),
})

const runRoute = createRoute({
  getParentRoute: () => shellRoute,
  path: '/run',
  component: lazyPage(() => import('./routes/RunPage'), 'RunPage'),
})

const matrixRoute = createRoute({
  getParentRoute: () => shellRoute,
  path: '/matrix',
  component: lazyPage(() => import('./routes/MatrixPage'), 'MatrixPage'),
})

const plansRoute = createRoute({
  getParentRoute: () => shellRoute,
  path: '/plans',
  component: lazyPage(() => import('./routes/PlansPage'), 'PlansPage'),
})

const reportsRoute = createRoute({
  getParentRoute: () => shellRoute,
  path: '/reports',
  component: lazyPage(() => import('./routes/ReportsPage'), 'ReportsPage'),
})

const requirementsRoute = createRoute({
  getParentRoute: () => shellRoute,
  path: '/requirements',
  component: lazyPage(
    () => import('./routes/RequirementsPage'),
    'RequirementsPage',
  ),
})

const adminRoute = createRoute({
  getParentRoute: () => shellRoute,
  path: '/admin',
  component: lazyPage(() => import('./routes/AdminPage'), 'AdminPage'),
})

const routeTree = rootRoute.addChildren([
  loginRoute,
  shellRoute.addChildren([
    dashboardRoute,
    specRoute,
    requirementsRoute,
    runRoute,
    matrixRoute,
    plansRoute,
    reportsRoute,
    adminRoute,
  ]),
])

const router = createRouter({
  routeTree,
  history: createHashHistory(),
  defaultPendingComponent: () => (
    <div className="grid place-items-center py-20">
      <Spinner />
    </div>
  ),
})

declare module '@tanstack/react-router' {
  interface Register {
    router: typeof router
  }
}

const queryClient = new QueryClient({
  defaultOptions: {
    queries: { retry: 1, staleTime: 30_000, refetchOnWindowFocus: false },
  },
})

createRoot(document.getElementById('root')!).render(
  <StrictMode>
    <I18nProvider>
      <QueryClientProvider client={queryClient}>
        <RouterProvider router={router} />
      </QueryClientProvider>
    </I18nProvider>
  </StrictMode>,
)
