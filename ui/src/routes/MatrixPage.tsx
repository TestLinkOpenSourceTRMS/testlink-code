import { useState } from 'react'
import { useQuery } from '@tanstack/react-query'
import { useNavigate } from '@tanstack/react-router'
import { X } from 'lucide-react'
import { api } from '../lib/api'
import { useT } from '../lib/i18n'
import { useWorkspace } from '../lib/workspace'
import {
  Button,
  CaseId,
  EmptyState,
  Spinner,
  VERDICT_COLOR,
  normVerdict,
  useVerdictLabels,
} from '../components/ui'

const PAGE_SIZE = 100

/** stacked p/f/b/not-run ratio bar for one suite × build cell */
function CellBar({
  cell,
  linked,
}: {
  cell: { p: number; f: number; b: number } | undefined
  linked: number
}) {
  const labels = useVerdictLabels()
  const p = cell?.p ?? 0
  const f = cell?.f ?? 0
  const b = cell?.b ?? 0
  const n = Math.max(0, linked - p - f - b)
  const total = Math.max(1, p + f + b + n)
  const seg = (qty: number, key: 'p' | 'f' | 'b' | 'n') =>
    qty > 0 && (
      <div
        key={key}
        style={{
          width: `${(qty / total) * 100}%`,
          background: VERDICT_COLOR[key],
          opacity: key === 'n' ? 0.2 : 1,
        }}
      />
    )
  return (
    <div
      className="flex h-3.5 w-full min-w-24 overflow-hidden rounded"
      title={`${labels.p} ${p} · ${labels.f} ${f} · ${labels.b} ${b} · ${labels.n} ${n}`}
    >
      {seg(p, 'p')}
      {seg(f, 'f')}
      {seg(b, 'b')}
      {seg(n, 'n')}
    </div>
  )
}

export function MatrixPage() {
  const { project, plan } = useWorkspace()
  const navigate = useNavigate()
  const { t } = useT()
  const verdictLabels = useVerdictLabels()
  const [mode, setMode] = useState<'suite' | 'case'>('suite')
  const [page, setPage] = useState(1)
  const [suiteFilter, setSuiteFilter] = useState<{
    id: number
    name: string
  } | null>(null)

  const bySuite = useQuery({
    queryKey: ['matrixBySuite', plan?.id],
    queryFn: () => api.planMatrixBySuite(plan!.id),
    enabled: plan != null && mode === 'suite',
  })

  const byCase = useQuery({
    queryKey: ['matrix', plan?.id, page, suiteFilter?.id],
    queryFn: () => api.planMatrix(plan!.id, page, PAGE_SIZE, suiteFilter?.id),
    enabled: plan != null && mode === 'case',
    placeholderData: (prev) => prev,
  })

  if (!plan) return <Spinner />

  const total = byCase.data?.total ?? 0
  const pages = Math.max(1, Math.ceil(total / PAGE_SIZE))

  return (
    <div className="flex h-full min-h-0 flex-col gap-3">
      <div className="flex items-center gap-3">
        <h1 className="font-display text-lg font-bold tracking-tight">
          {t('resultMatrix')}
        </h1>
        <div className="border-line flex overflow-hidden rounded-md border text-[13px]">
          {(['suite', 'case'] as const).map((m) => (
            <button
              key={m}
              onClick={() => setMode(m)}
              className={`px-3 py-1.5 transition-colors ${
                mode === m
                  ? 'bg-accent text-white'
                  : 'bg-panel hover:bg-accent-soft'
              }`}
            >
              {m === 'suite' ? t('bySuiteTab') : t('byCaseTab')}
            </button>
          ))}
        </div>
        {mode === 'case' && suiteFilter && (
          <button
            onClick={() => {
              setSuiteFilter(null)
              setPage(1)
            }}
            className="bg-accent-soft text-accent inline-flex items-center gap-1 rounded-full px-3 py-1 text-xs"
          >
            {suiteFilter.name} <X className="size-3" />
          </button>
        )}
        {mode === 'case' && (
          <span className="text-mute ml-auto flex items-center gap-2 font-mono text-xs">
            {t('casesPageInfo')(total.toLocaleString(), page, pages)}
            <Button kind="ghost" onClick={() => setPage(Math.max(1, page - 1))} disabled={page <= 1}>
              ‹
            </Button>
            <Button
              kind="ghost"
              onClick={() => setPage(Math.min(pages, page + 1))}
              disabled={page >= pages}
            >
              ›
            </Button>
          </span>
        )}
      </div>

      <div className="bg-panel border-line min-h-0 flex-1 overflow-auto rounded-lg border">
        {mode === 'suite' ? (
          bySuite.isPending ? (
            <Spinner />
          ) : (bySuite.data?.items.length ?? 0) === 0 ? (
            <EmptyState>{t('noSuitesLinked')}</EmptyState>
          ) : (
            <table className="w-full text-[13px]">
              <thead className="bg-panel sticky top-0 shadow-[0_1px_0_var(--color-line)]">
                <tr className="text-left">
                  <th className="text-mute px-3 py-2 text-xs font-medium">
                    {t('testSuite')}
                  </th>
                  <th className="text-mute w-16 px-3 py-2 text-right text-xs font-medium">
                    {t('cases')}
                  </th>
                  {bySuite.data!.builds.map((b) => (
                    <th
                      key={b.id}
                      className="text-mute px-3 py-2 text-xs font-medium"
                    >
                      {b.name}
                    </th>
                  ))}
                </tr>
              </thead>
              <tbody>
                {bySuite.data!.items.map((s) => (
                  <tr
                    key={s.suite_id}
                    onClick={() => {
                      setSuiteFilter({ id: s.suite_id, name: s.name })
                      setPage(1)
                      setMode('case')
                    }}
                    className="border-line hover:bg-paper cursor-pointer border-b last:border-0"
                    title={t('openCaseDetail')}
                  >
                    <td className="px-3 py-1.5 font-medium">{s.name}</td>
                    <td className="text-mute px-3 py-1.5 text-right font-mono text-xs">
                      {s.linked}
                    </td>
                    {bySuite.data!.builds.map((b) => (
                      <td key={b.id} className="px-3 py-1.5">
                        <CellBar cell={s.cells[b.id]} linked={s.linked} />
                      </td>
                    ))}
                  </tr>
                ))}
              </tbody>
            </table>
          )
        ) : byCase.isPending ? (
          <Spinner />
        ) : (byCase.data?.items.length ?? 0) === 0 ? (
          <EmptyState>{t('noCasesHere')}</EmptyState>
        ) : (
          <table className="w-full text-[13px]">
            <thead className="bg-panel sticky top-0 shadow-[0_1px_0_var(--color-line)]">
              <tr className="text-left">
                <th className="text-mute px-3 py-2 text-xs font-medium">
                  {t('testCase')}
                </th>
                {byCase.data!.builds.map((b) => (
                  <th
                    key={b.id}
                    className="text-mute px-3 py-2 text-center text-xs font-medium"
                  >
                    {b.name}
                  </th>
                ))}
              </tr>
            </thead>
            <tbody>
              {byCase.data!.items.map((row) => (
                <tr
                  key={row.tcversion_id}
                  className="border-line hover:bg-paper border-b last:border-0"
                >
                  <td className="px-3 py-1.5">
                    <button
                      className="hover:text-accent inline-flex items-center gap-2 text-left"
                      title={t('openInTestCases')}
                      onClick={() =>
                        navigate({
                          to: '/spec',
                          search: { caseId: row.tcase_id },
                        })
                      }
                    >
                      <CaseId
                        prefix={project?.prefix ?? ''}
                        ext={row.tc_external_id}
                      />
                      {row.name}
                    </button>
                  </td>
                  {byCase.data!.builds.map((b) => {
                    const v = normVerdict(row.results?.[b.id])
                    return (
                      <td key={b.id} className="px-3 py-1.5 text-center">
                        <span
                          title={verdictLabels[v]}
                          className="inline-block size-4 rounded"
                          style={{
                            background: VERDICT_COLOR[v],
                            opacity: v === 'n' ? 0.25 : 1,
                          }}
                        />
                      </td>
                    )
                  })}
                </tr>
              ))}
            </tbody>
          </table>
        )}
      </div>

      <div className="text-mute flex items-center gap-4 text-xs">
        {(['p', 'f', 'b', 'n'] as const).map((v) => (
          <span key={v} className="inline-flex items-center gap-1.5">
            <span
              className="inline-block size-3 rounded"
              style={{
                background: VERDICT_COLOR[v],
                opacity: v === 'n' ? 0.25 : 1,
              }}
            />
            {verdictLabels[v]}
          </span>
        ))}
        {mode === 'suite' && (
          <span className="ml-auto">{t('clickSuiteHint')}</span>
        )}
      </div>
    </div>
  )
}
