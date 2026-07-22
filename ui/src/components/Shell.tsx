import { Link, Outlet, useNavigate } from '@tanstack/react-router'
import {
  ClipboardList,
  FileCheck2,
  FolderTree,
  Gauge,
  Grid3X3,
  LineChart,
  LogOut,
  PlayCircle,
  Settings,
} from 'lucide-react'
import { useRef, useState } from 'react'
import { useQuery } from '@tanstack/react-query'
import { api, getSession, setSession } from '../lib/api'
import {
  LOCALE_OPTIONS,
  useT,
  type Locale,
  type StringMsgKey,
} from '../lib/i18n'
import { useWorkspace } from '../lib/workspace'

function SearchBox({ projectId }: { projectId: string | undefined }) {
  const { t } = useT()
  const navigate = useNavigate()
  const [q, setQ] = useState('')
  const [open, setOpen] = useState(false)
  const blurTimer = useRef<number>(0)

  const results = useQuery({
    queryKey: ['search', projectId, q],
    queryFn: () => api.search(projectId!, q),
    enabled: projectId != null && q.trim().length >= 2,
    placeholderData: (prev) => prev,
  })

  return (
    <div className="relative ml-auto w-72">
      <input
        className="border-line bg-paper w-full rounded-md border px-3 py-1.5 text-[13px] focus-visible:outline-2 focus-visible:outline-[var(--color-accent)]"
        placeholder={t('searchPlaceholder')}
        value={q}
        onChange={(e) => {
          setQ(e.target.value)
          setOpen(true)
        }}
        onFocus={() => setOpen(true)}
        onBlur={() => {
          blurTimer.current = window.setTimeout(() => setOpen(false), 150)
        }}
      />
      {open && q.trim().length >= 2 && (
        <div className="bg-panel border-line absolute top-full right-0 left-0 z-20 mt-1 max-h-80 overflow-auto rounded-md border shadow-lg">
          {(results.data?.length ?? 0) === 0 ? (
            <div className="text-mute px-3 py-3 text-sm">
              {t('searchNoResults')}
            </div>
          ) : (
            results.data!.map((hit) => (
              <button
                key={hit.tcase_id}
                className="border-line hover:bg-paper flex w-full items-center gap-2 border-b px-3 py-2 text-left text-[13px] last:border-0"
                onMouseDown={(e) => e.preventDefault()}
                onClick={() => {
                  window.clearTimeout(blurTimer.current)
                  setOpen(false)
                  setQ('')
                  navigate({ to: '/spec', search: { caseId: hit.tcase_id } })
                }}
              >
                <span className="text-mute font-mono text-xs">
                  {hit.tc_external_id}
                </span>
                <span className="truncate">{hit.name}</span>
                <span className="text-mute ml-auto shrink-0 text-xs">
                  {hit.suite_name}
                </span>
              </button>
            ))
          )}
        </div>
      )}
    </div>
  )
}

const NAV: { to: string; labelKey: StringMsgKey; icon: typeof Gauge }[] = [
  { to: '/', labelKey: 'navDashboard', icon: Gauge },
  { to: '/spec', labelKey: 'navTestCases', icon: FolderTree },
  { to: '/requirements', labelKey: 'navRequirements', icon: FileCheck2 },
  { to: '/run', labelKey: 'navRun', icon: PlayCircle },
  { to: '/matrix', labelKey: 'navMatrix', icon: Grid3X3 },
  { to: '/plans', labelKey: 'navPlans', icon: ClipboardList },
  { to: '/reports', labelKey: 'navReports', icon: LineChart },
  { to: '/admin', labelKey: 'navAdmin', icon: Settings },
]

export function Shell() {
  const navigate = useNavigate()
  const session = getSession()
  const { t, locale, setLocale } = useT()
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
          {NAV.map(({ to, labelKey, icon: Icon }) => (
            <Link
              key={to}
              to={to}
              className="flex items-center gap-2.5 rounded-md px-3 py-2 text-[13px] text-white/70 transition-colors hover:bg-rail-hover hover:text-white [&.active]:bg-rail-hover [&.active]:text-white"
              activeOptions={{ exact: to === '/' }}
            >
              <Icon className="size-4" strokeWidth={1.8} />
              {t(labelKey)}
            </Link>
          ))}
        </nav>
        <div className="mt-auto px-4 py-4 text-xs text-white/50">
          <div className="mb-2 truncate">
            {session?.user.firstName} {session?.user.lastName}
          </div>
          <select
            aria-label={t('language')}
            className="mb-3 w-full rounded-md border border-white/20 bg-transparent px-1.5 py-1 text-xs text-white/70 focus-visible:outline-2 focus-visible:outline-[var(--color-accent)]"
            value={locale}
            onChange={(e) => setLocale(e.target.value as Locale)}
          >
            {LOCALE_OPTIONS.map((o) => (
              <option key={o.value} value={o.value} className="text-black">
                {o.label}
              </option>
            ))}
          </select>
          <button
            className="flex items-center gap-1.5 text-white/60 transition-colors hover:text-white"
            onClick={() => {
              setSession(null)
              navigate({ to: '/login' })
            }}
          >
            <LogOut className="size-3.5" /> {t('signOut')}
          </button>
        </div>
      </aside>

      {/* content column */}
      <div className="flex min-w-0 flex-1 flex-col">
        <header className="border-line bg-panel flex items-center gap-3 border-b px-5 py-2.5">
          <label className="text-mute text-xs">{t('project')}</label>
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
          <label className="text-mute ml-2 text-xs">{t('testPlan')}</label>
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
          <SearchBox projectId={project?.id} />
        </header>
        <main className="min-h-0 flex-1 overflow-auto p-5">
          <Outlet />
        </main>
      </div>
    </div>
  )
}
