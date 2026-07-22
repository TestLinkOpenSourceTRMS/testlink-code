import { useState } from 'react'
import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query'
import { useNavigate } from '@tanstack/react-router'
import { api, type QueueItem, type Verdict } from '../lib/api'
import { useT } from '../lib/i18n'
import { useWorkspace } from '../lib/workspace'
import {
  Button,
  CaseId,
  EmptyState,
  Panel,
  Spinner,
  VERDICT_COLOR,
  VerdictBadge,
  normVerdict,
  useVerdictLabels,
} from '../components/ui'

const PAGE_SIZE = 100

export function RunPage() {
  const { project, plan } = useWorkspace()
  const navigate = useNavigate()
  const { t } = useT()
  const verdictLabels = useVerdictLabels()
  const qc = useQueryClient()
  const [buildId, setBuildId] = useState('')
  const [page, setPage] = useState(1)
  const [active, setActive] = useState<QueueItem | null>(null)
  const [notes, setNotes] = useState('')
  const [savedFlash, setSavedFlash] = useState('')
  const [assignedTo, setAssignedTo] = useState('')

  const builds = useQuery({
    queryKey: ['builds', plan?.id],
    queryFn: () => api.planBuilds(plan!.id),
    enabled: plan != null,
  })
  const build = builds.data?.find((b) => b.id === buildId) ?? builds.data?.[0]

  const users = useQuery({ queryKey: ['users'], queryFn: api.users })

  const queue = useQuery({
    queryKey: ['queue', plan?.id, build?.id, page, assignedTo],
    queryFn: () =>
      api.planQueue(plan!.id, build!.id, page, PAGE_SIZE, assignedTo),
    enabled: plan != null && build != null,
  })

  const assign = useMutation({
    mutationFn: (userID: number) =>
      api.assignCases(plan!.id, Number(build!.id), [
        { tcaseID: Number(active!.tcase_id), userID },
      ]),
    onSuccess: () => qc.invalidateQueries({ queryKey: ['queue'] }),
  })

  const record = useMutation({
    mutationFn: (verdict: Verdict) =>
      api.recordExecution({
        testPlanID: Number(plan!.id),
        buildID: Number(build!.id),
        testCaseExternalID: `${project!.prefix}-${active!.tc_external_id}`,
        statusCode: verdict,
        notes,
      }),
    onSuccess: (_r, verdict) => {
      setSavedFlash(`${active!.name} → ${verdictLabels[verdict]}`)
      setNotes('')
      setActive(null)
      qc.invalidateQueries({ queryKey: ['queue'] })
      qc.invalidateQueries({ queryKey: ['summary'] })
      setTimeout(() => setSavedFlash(''), 2500)
    },
  })

  if (!plan) return <Spinner />

  const total = queue.data?.total ?? 0
  const pages = Math.max(1, Math.ceil(total / PAGE_SIZE))

  return (
    <div className="flex h-full min-h-0 gap-4">
      {/* work queue */}
      <div className="flex min-w-0 flex-1 flex-col gap-3">
        <div className="flex items-center gap-3">
          <h1 className="font-display text-lg font-bold tracking-tight">
            {t('navRun')}
          </h1>
          <label className="text-mute ml-2 text-xs">{t('build')}</label>
          <select
            className="border-line rounded-md border bg-transparent px-2 py-1.5 text-[13px]"
            value={build?.id ?? ''}
            onChange={(e) => {
              setBuildId(e.target.value)
              setPage(1)
            }}
          >
            {builds.data?.map((b) => (
              <option key={b.id} value={b.id}>
                {b.name}
              </option>
            ))}
          </select>
          <label className="text-mute ml-2 text-xs">{t('assignedTo')}</label>
          <select
            className="border-line rounded-md border bg-transparent px-2 py-1.5 text-[13px]"
            value={assignedTo}
            onChange={(e) => {
              setAssignedTo(e.target.value)
              setPage(1)
            }}
          >
            <option value="">{t('everyone')}</option>
            <option value="none">{t('unassigned')}</option>
            {users.data?.map((u) => (
              <option key={u.id} value={u.id}>
                {u.login}
              </option>
            ))}
          </select>
          <span className="text-mute ml-auto font-mono text-xs">
            {t('casesPageInfo')(total.toLocaleString(), page, pages)}
          </span>
          <Button kind="ghost" onClick={() => setPage(Math.max(1, page - 1))} disabled={page <= 1}>
            ‹
          </Button>
          <Button kind="ghost" onClick={() => setPage(Math.min(pages, page + 1))} disabled={page >= pages}>
            ›
          </Button>
        </div>

        {savedFlash && (
          <div className="bg-accent-soft text-accent rounded-md px-3 py-2 text-[13px]">
            {t('saved')}: {savedFlash}
          </div>
        )}

        <div className="bg-panel border-line min-h-0 flex-1 overflow-auto rounded-lg border">
          {queue.isPending ? (
            <Spinner />
          ) : (queue.data?.items.length ?? 0) === 0 ? (
            <EmptyState>{t('noLinkedCases')}</EmptyState>
          ) : (
            queue.data!.items.map((item) => {
              const v = normVerdict(item.exec_status)
              return (
                <button
                  key={item.tcversion_id}
                  onClick={() => setActive(item)}
                  className={`spine spine-${v} border-line flex w-full items-center gap-3 border-b px-3 py-2 text-left text-[13px] transition-colors last:border-0 ${
                    active?.tcversion_id === item.tcversion_id
                      ? 'bg-accent-soft'
                      : 'hover:bg-paper'
                  }`}
                >
                  <CaseId prefix={project?.prefix ?? ''} ext={item.tc_external_id} />
                  <span className="truncate">{item.name}</span>
                  {item.assigned_login && (
                    <span className="bg-accent-soft text-accent ml-1 shrink-0 rounded-full px-2 py-0.5 text-[11px]">
                      {item.assigned_login.split('@')[0]}
                    </span>
                  )}
                  <span className="ml-auto shrink-0">
                    <VerdictBadge verdict={v} />
                  </span>
                </button>
              )
            })
          )}
        </div>
      </div>

      {/* verdict recorder */}
      <div className="w-80 shrink-0">
        <Panel title={t('recordResult')}>
          {active == null ? (
            <EmptyState>{t('pickFromQueue')}</EmptyState>
          ) : (
            <div className="flex flex-col gap-3">
              <div>
                <CaseId prefix={project?.prefix ?? ''} ext={active.tc_external_id} />
                <div className="mt-0.5 font-medium">{active.name}</div>
                <button
                  className="text-accent mt-1 text-xs hover:underline"
                  onClick={() =>
                    navigate({
                      to: '/spec',
                      search: { caseId: active.tcase_id },
                    })
                  }
                >
                  {t('viewStepsHistory')}
                </button>
              </div>
              <label className="text-mute text-xs">{t('assignTo')}</label>
              <select
                className="border-line rounded-md border bg-transparent px-2 py-1.5 text-[13px]"
                value={active.assigned_to ?? ''}
                onChange={(e) => assign.mutate(Number(e.target.value) || 0)}
                disabled={assign.isPending}
              >
                <option value="">{t('unassigned')}</option>
                {users.data?.map((u) => (
                  <option key={u.id} value={u.id}>
                    {u.login}
                  </option>
                ))}
              </select>
              <textarea
                className="border-line min-h-24 w-full rounded-md border p-2 text-[13px] focus-visible:outline-2 focus-visible:outline-[var(--color-accent)]"
                placeholder={t('notesPlaceholder')}
                value={notes}
                onChange={(e) => setNotes(e.target.value)}
              />
              <div className="grid grid-cols-3 gap-2">
                {(['p', 'f', 'b'] as Verdict[]).map((v) => (
                  <button
                    key={v}
                    disabled={record.isPending}
                    onClick={() => record.mutate(v)}
                    className="rounded-md py-2 text-[13px] font-semibold text-white transition-[filter] hover:brightness-110 disabled:opacity-40"
                    style={{ background: VERDICT_COLOR[v] }}
                  >
                    {verdictLabels[v]}
                  </button>
                ))}
              </div>
              {record.isError && (
                <div className="text-[13px] text-[var(--color-fail)]">
                  {t('saveResultError')}
                </div>
              )}
            </div>
          )}
        </Panel>
      </div>
    </div>
  )
}
