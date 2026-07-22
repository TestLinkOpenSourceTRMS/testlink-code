import { Link, Outlet, useNavigate } from '@tanstack/react-router'
import {
  ClipboardList,
  FolderTree,
  Gauge,
  Grid3X3,
  LineChart,
  LogOut,
  PlayCircle,
} from 'lucide-react'
import { getSession, setSession } from '../lib/api'
import { useWorkspace } from '../lib/workspace'

const NAV = [
  { to: '/', label: 'Dashboard', icon: Gauge },
  { to: '/spec', label: 'Test cases', icon: FolderTree },
  { to: '/run', label: 'Run', icon: PlayCircle },
  { to: '/matrix', label: 'Matrix', icon: Grid3X3 },
  { to: '/plans', label: 'Plans', icon: ClipboardList },
  { to: '/reports', label: 'Reports', icon: LineChart },
]

export function Shell() {
  const navigate = useNavigate()
  const session = getSession()
  const { projects, plans, project, plan, selectProject, selectPlan } =
    useWorkspace()

  return (
    <div className="flex h-full">
      {/* ink rail */}
      <aside className="bg-rail flex w-52 shrink-0 flex-col text-white">
        <div className="px-4 pt-5 pb-6">
          <span className="font-display text-lg font-bold tracking-tight">
            Test<span className="text-[var(--color-blocked)]">Link</span>
          </span>
          <div className="font-mono mt-0.5 text-[10px] text-white/40">
            2.0 preview
          </div>
        </div>
        <nav className="flex flex-col gap-0.5 px-2">
          {NAV.map(({ to, label, icon: Icon }) => (
            <Link
              key={to}
              to={to}
              className="flex items-center gap-2.5 rounded-md px-3 py-2 text-[13px] text-white/70 transition-colors hover:bg-rail-hover hover:text-white [&.active]:bg-rail-hover [&.active]:text-white"
              activeOptions={{ exact: to === '/' }}
            >
              <Icon className="size-4" strokeWidth={1.8} />
              {label}
            </Link>
          ))}
        </nav>
        <div className="mt-auto px-4 py-4 text-xs text-white/50">
          <div className="mb-2 truncate">
            {session?.user.firstName} {session?.user.lastName}
          </div>
          <button
            className="flex items-center gap-1.5 text-white/60 transition-colors hover:text-white"
            onClick={() => {
              setSession(null)
              navigate({ to: '/login' })
            }}
          >
            <LogOut className="size-3.5" /> Sign out
          </button>
        </div>
      </aside>

      {/* content column */}
      <div className="flex min-w-0 flex-1 flex-col">
        <header className="border-line bg-panel flex items-center gap-3 border-b px-5 py-2.5">
          <label className="text-mute text-xs">Project</label>
          <select
            className="border-line rounded-md border bg-transparent px-2 py-1.5 text-[13px] focus-visible:outline-2 focus-visible:outline-[var(--color-accent)]"
            value={project?.id ?? ''}
            onChange={(e) => selectProject(e.target.value)}
          >
            {projects.map((p) => (
              <option key={p.id} value={p.id}>
                {p.name}
              </option>
            ))}
          </select>
          <label className="text-mute ml-2 text-xs">Test plan</label>
          <select
            className="border-line rounded-md border bg-transparent px-2 py-1.5 text-[13px] focus-visible:outline-2 focus-visible:outline-[var(--color-accent)]"
            value={plan?.id ?? ''}
            onChange={(e) => selectPlan(e.target.value)}
          >
            {plans.map((p) => (
              <option key={p.id} value={p.id}>
                {p.name}
              </option>
            ))}
          </select>
        </header>
        <main className="min-h-0 flex-1 overflow-auto p-5">
          <Outlet />
        </main>
      </div>
    </div>
  )
}
