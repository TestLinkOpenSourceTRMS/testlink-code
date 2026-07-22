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
  redirect,
} from '@tanstack/react-router'
import './index.css'
import { getSession } from './lib/api'
import { I18nProvider } from './lib/i18n'
import { WorkspaceProvider } from './lib/workspace'
import { Shell } from './components/Shell'
import { LoginPage } from './routes/LoginPage'
import { DashboardPage } from './routes/DashboardPage'
import { SpecPage } from './routes/SpecPage'
import { RunPage } from './routes/RunPage'
import { MatrixPage } from './routes/MatrixPage'
import { PlansPage } from './routes/PlansPage'
import { ReportsPage } from './routes/ReportsPage'

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
  component: SpecPage,
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
  component: RunPage,
})

const matrixRoute = createRoute({
  getParentRoute: () => shellRoute,
  path: '/matrix',
  component: MatrixPage,
})

const plansRoute = createRoute({
  getParentRoute: () => shellRoute,
  path: '/plans',
  component: PlansPage,
})

const reportsRoute = createRoute({
  getParentRoute: () => shellRoute,
  path: '/reports',
  component: ReportsPage,
})

const routeTree = rootRoute.addChildren([
  loginRoute,
  shellRoute.addChildren([
    dashboardRoute,
    specRoute,
    runRoute,
    matrixRoute,
    plansRoute,
    reportsRoute,
  ]),
])

const router = createRouter({
  routeTree,
  history: createHashHistory(),
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
