import { useQuery } from '@tanstack/react-query'
import { Printer } from 'lucide-react'
import { api } from '../lib/api'
import { useT } from '../lib/i18n'
import { useWorkspace } from '../lib/workspace'
import {
  Button,
  Panel,
  Spinner,
  useVerdictLabels,
  VERDICT_COLOR,
} from '../components/ui'
import { TrendChart } from '../components/TrendChart'

const VERDICT_KEYS = ['p', 'f', 'b', 'n'] as const

/** double-quote a CSV field when it contains a comma, quote, or newline */
function csvEscape(v: string): string {
  return /[",\n]/.test(v) ? `"${v.replace(/"/g, '""')}"` : v
}

function downloadCsv(filename: string, rows: string[][]) {
  const csv = rows.map((r) => r.map(csvEscape).join(',')).join('\n')
  const blob = new Blob([csv], { type: 'text/csv' })
  const url = URL.createObjectURL(blob)
  const a = document.createElement('a')
  a.href = url
  a.download = filename
  a.click()
  URL.revokeObjectURL(url)
}

export function ReportsPage() {
  const { plan } = useWorkspace()
  const { t } = useT()

  const trend = useQuery({
    queryKey: ['trend', plan?.id],
    queryFn: () => api.planTrend(plan!.id),
    enabled: plan != null,
  })
  const flaky = useQuery({
    queryKey: ['flaky', plan?.id],
    queryFn: () => api.planFlaky(plan!.id),
    enabled: plan != null,
  })
  const byBuild = useQuery({
    queryKey: ['byBuild', plan?.id],
    queryFn: () => api.planByBuild(plan!.id),
    enabled: plan != null,
  })
  const byTester = useQuery({
    queryKey: ['byTester', plan?.id],
    queryFn: () => api.planByTester(plan!.id),
    enabled: plan != null,
  })
  const byKeyword = useQuery({
    queryKey: ['byKeyword', plan?.id],
    queryFn: () => api.planByKeyword(plan!.id),
    enabled: plan != null,
  })
  const verdictLabels = useVerdictLabels()

  if (!plan) return <Spinner />

  /** open the printable test report document (authenticated blob) */
  const openReportDocument = async () => {
    const url = await api.blobUrl(`/testplans/${plan.id}/document?type=report`)
    window.open(url, '_blank')
  }

  const exportTesterCsv = () => {
    if (!byTester.data) return
    const header = ['login', 'p', 'f', 'b', 'total']
    const rows = byTester.data.map((row) => [
      row.login,
      String(row.p),
      String(row.f),
      String(row.b),
      String(row.p + row.f + row.b + row.other),
    ])
    downloadCsv('tester-report.csv', [header, ...rows])
  }

  return (
    <div className="mx-auto flex max-w-5xl flex-col gap-4">
      <div className="flex items-center justify-between">
        <h1 className="font-display text-xl font-bold tracking-tight">
          {t('navReports')} · {plan.name}
        </h1>
        <Button kind="ghost" onClick={openReportDocument}>
          <span className="inline-flex items-center gap-1.5">
            <Printer className="size-3.5" /> {t('testReportDocument')}
          </span>
        </Button>
      </div>

      <Panel title={t('executionTrend')}>
        {trend.isPending ? <Spinner /> : <TrendChart days={trend.data ?? []} />}
      </Panel>

      <Panel title={t('buildComparison')}>
        {byBuild.isPending ? (
          <Spinner />
        ) : (
          <table className="w-full text-[13px]">
            <thead>
              <tr className="text-mute border-line border-b text-left text-xs">
                <th className="pb-2 font-medium">{t('build')}</th>
                <th className="pb-2 text-right font-medium">{t('verdictPassed')}</th>
                <th className="pb-2 text-right font-medium">{t('verdictFailed')}</th>
                <th className="pb-2 text-right font-medium">{t('verdictBlocked')}</th>
                <th className="pb-2 text-right font-medium">{t('coverage')}</th>
              </tr>
            </thead>
            <tbody>
              {byBuild.data!.map((b) => {
                const done = b.p + b.f + b.b
                return (
                  <tr key={b.build_id} className="border-line border-b last:border-0">
                    <td className="py-1.5 font-medium">{b.name}</td>
                    <td className="py-1.5 text-right font-mono">{b.p.toLocaleString()}</td>
                    <td className="py-1.5 text-right font-mono">{b.f.toLocaleString()}</td>
                    <td className="py-1.5 text-right font-mono">{b.b.toLocaleString()}</td>
                    <td className="text-mute py-1.5 text-right font-mono">
                      {Math.round((done / Math.max(1, b.linked)) * 100)}%
                    </td>
                  </tr>
                )
              })}
            </tbody>
          </table>
        )}
      </Panel>

      <Panel
        title={t('byTester')}
        actions={
          <Button kind="ghost" onClick={exportTesterCsv} disabled={!byTester.data}>
            {t('exportCsv')}
          </Button>
        }
      >
        {byTester.isPending ? (
          <Spinner />
        ) : (byTester.data?.length ?? 0) === 0 ? (
          <div className="text-mute py-4 text-center text-sm">
            {t('noExecutionsYet')}
          </div>
        ) : (
          <table className="w-full text-[13px]">
            <thead>
              <tr className="text-mute border-line border-b text-left text-xs">
                <th className="pb-2 font-medium">{t('tester')}</th>
                <th className="pb-2 text-right font-medium">{t('verdictPassed')}</th>
                <th className="pb-2 text-right font-medium">{t('verdictFailed')}</th>
                <th className="pb-2 text-right font-medium">{t('verdictBlocked')}</th>
                <th className="pb-2 text-right font-medium">{t('total')}</th>
              </tr>
            </thead>
            <tbody>
              {byTester.data!.map((t) => (
                <tr key={t.login} className="border-line border-b last:border-0">
                  <td className="py-1.5">{t.login}</td>
                  <td className="py-1.5 text-right font-mono">{t.p.toLocaleString()}</td>
                  <td className="py-1.5 text-right font-mono">{t.f.toLocaleString()}</td>
                  <td className="py-1.5 text-right font-mono">{t.b.toLocaleString()}</td>
                  <td className="py-1.5 text-right font-mono">
                    {(t.p + t.f + t.b + t.other).toLocaleString()}
                  </td>
                </tr>
              ))}
            </tbody>
          </table>
        )}
      </Panel>

      <Panel title={t('byKeyword')}>
        {byKeyword.isPending ? (
          <Spinner />
        ) : (byKeyword.data?.length ?? 0) === 0 ? (
          <div className="text-mute py-4 text-center text-sm">
            {t('noKeywordData')}
          </div>
        ) : (
          <table className="w-full text-[13px]">
            <thead>
              <tr className="text-mute border-line border-b text-left text-xs">
                <th className="pb-2 font-medium">{t('adminColKeyword')}</th>
                <th className="pb-2 text-right font-medium">{t('cases')}</th>
                {VERDICT_KEYS.map((v) => (
                  <th key={v} className="pb-2 text-right font-medium">
                    <span className="inline-flex items-center justify-end gap-1.5">
                      <span
                        className="size-2 rounded-full"
                        style={{ background: VERDICT_COLOR[v] }}
                      />
                      {verdictLabels[v]}
                    </span>
                  </th>
                ))}
              </tr>
            </thead>
            <tbody>
              {byKeyword.data!.map((k) => (
                <tr
                  key={k.keyword_id}
                  className="border-line border-b last:border-0"
                >
                  <td className="py-1.5 font-medium">{k.keyword}</td>
                  <td className="py-1.5 text-right font-mono">
                    {k.linked.toLocaleString()}
                  </td>
                  {VERDICT_KEYS.map((v) => (
                    <td key={v} className="py-1.5 text-right font-mono">
                      {k[v].toLocaleString()}
                    </td>
                  ))}
                </tr>
              ))}
            </tbody>
          </table>
        )}
      </Panel>

      <Panel
        title={t('flakyTestsAnalyzed')(flaky.data?.analyzed.toLocaleString() ?? '…')}
      >
        {flaky.isPending ? (
          <Spinner />
        ) : (
          <table className="w-full text-[13px]">
            <thead>
              <tr className="text-mute border-line border-b text-left text-xs">
                <th className="pb-2 font-medium">{t('testCase')}</th>
                <th className="pb-2 text-right font-medium">{t('flips')}</th>
                <th className="pb-2 text-right font-medium">{t('runs')}</th>
              </tr>
            </thead>
            <tbody>
              {flaky.data!.items.map((f) => (
                <tr key={f.tcase_id} className="border-line border-b last:border-0">
                  <td className="py-1.5">{f.name}</td>
                  <td className="py-1.5 text-right font-mono">{f.flips}</td>
                  <td className="py-1.5 text-right font-mono">{f.total}</td>
                </tr>
              ))}
            </tbody>
          </table>
        )}
      </Panel>
    </div>
  )
}
