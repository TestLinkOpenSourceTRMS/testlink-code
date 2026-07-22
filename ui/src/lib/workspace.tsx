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

  const { data: plans = [] } = useQuery({
    queryKey: ['plans', project?.id],
    queryFn: () => api.projectPlans(project!.id),
    enabled: authed && project != null,
  })

  const plan = plans.find((p) => p.id === planId) ?? plans[0] ?? null

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
      value={{ projects, plans, project, plan, selectProject, selectPlan }}
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
