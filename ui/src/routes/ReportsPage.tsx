import { useQuery } from '@tanstack/react-query'
import { api } from '../lib/api'
import { useT } from '../lib/i18n'
import { useWorkspace } from '../lib/workspace'
import { Button, Panel, Spinner } from '../components/ui'
import { TrendChart } from '../components/TrendChart'

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

  if (!plan) return <Spinner />

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
      <h1 className="font-display text-xl font-bold tracking-tight">
        {t('navReports')} · {plan.name}
      </h1>

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
