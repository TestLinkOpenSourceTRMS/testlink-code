import {
  createContext,
  useCallback,
  useContext,
  useEffect,
  useState,
  type ReactNode,
} from 'react'
import { useQuery } from '@tanstack/react-query'
import { api, getSession, type Plan, type Project } from './api'

interface Workspace {
  projects: Project[]
  plans: Plan[]
  project: Project | null
  plan: Plan | null
  /** last-known ids from localStorage — usable before the lists load,
   *  so dependent queries can fire in parallel on a revisit instead of
   *  waiting out a 3-hop request waterfall over a high-latency link. */
  projectId: string
  planId: string
  selectProject: (id: string) => void
  selectPlan: (id: string) => void
}

const Ctx = createContext<Workspace | null>(null)

export function WorkspaceProvider({ children }: { children: ReactNode }) {
  const authed = getSession() != null
  const [projectId, setProjectId] = useState(
    () => localStorage.getItem('tl.project') ?? '',
  )
  const [planId, setPlanId] = useState(
    () => localStorage.getItem('tl.plan') ?? '',
  )

  const { data: projects = [] } = useQuery({
    queryKey: ['projects'],
    queryFn: api.projects,
    enabled: authed,
  })

  const project =
    projects.find((p) => p.id === projectId) ?? projects[0] ?? null

  // prefer the remembered id so plans can load without waiting for the
  // projects response; fall back to the resolved project once it arrives
  const effectiveProjectId = projectId || project?.id

  const { data: plans = [] } = useQuery({
    queryKey: ['plans', effectiveProjectId],
    queryFn: () => api.projectPlans(effectiveProjectId!),
    enabled: authed && effectiveProjectId != null,
  })

  const plan = plans.find((p) => p.id === planId) ?? plans[0] ?? null
  const effectivePlanId = planId || plan?.id || ''

  useEffect(() => {
    if (project) localStorage.setItem('tl.project', project.id)
  }, [project])
  useEffect(() => {
    if (plan) localStorage.setItem('tl.plan', plan.id)
  }, [plan])

  const selectProject = useCallback((id: string) => {
    setProjectId(id)
    setPlanId('')
  }, [])
  const selectPlan = useCallback((id: string) => setPlanId(id), [])

  return (
    <Ctx.Provider
      value={{
        projects,
        plans,
        project,
        plan,
        projectId: effectiveProjectId ?? '',
        planId: effectivePlanId,
        selectProject,
        selectPlan,
      }}
    >
      {children}
    </Ctx.Provider>
  )
}

export function useWorkspace() {
  const ws = useContext(Ctx)
  if (!ws) throw new Error('useWorkspace outside provider')
  return ws
}
