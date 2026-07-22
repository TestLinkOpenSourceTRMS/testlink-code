import { useQuery } from '@tanstack/react-query'
import { useNavigate } from '@tanstack/react-router'
import { api, type Verdict } from '../lib/api'
import { useT } from '../lib/i18n'
import { useWorkspace } from '../lib/workspace'
import {
  Panel,
  Spinner,
  VERDICT_COLOR,
  useVerdictLabels,
} from '../components/ui'
import { TrendChart } from '../components/TrendChart'

export function DashboardPage() {
  const { project, plan } = useWorkspace()
  const navigate = useNavigate()
  const { t } = useT()
  const verdictLabels = useVerdictLabels()

  const summary = useQuery({
    queryKey: ['summary', plan?.id],
    queryFn: () => api.planSummary(plan!.id),
    enabled: plan != null,
  })
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

  if (!plan) return <Spinner />

  const by = summary.data?.byStatus ?? {}
  const linked = summary.data?.linked ?? 0
  const executed = (by.p ?? 0) + (by.f ?? 0) + (by.b ?? 0)
  const passRate = executed > 0 ? Math.round(((by.p ?? 0) / executed) * 100) : 0

  return (
    <div className="mx-auto flex max-w-5xl flex-col gap-4">
      <div>
        <h1 className="font-display text-xl font-bold tracking-tight">
          {project?.name} · {plan.name}
        </h1>
        <p className="text-mute mt-0.5 text-sm">
          {t('linkedCasesCount')(linked.toLocaleString())} · {t('passRateLabel')}{' '}
          <span className="font-mono">{passRate}%</span>
          {t('passRateSuffix') && <> {t('passRateSuffix')}</>}
        </p>
      </div>

      {/* verdict wall — the plan's state in one row */}
      <div className="grid grid-cols-2 gap-3 sm:grid-cols-4">
        {(['p', 'f', 'b', 'n'] as Verdict[]).map((k) => (
          <div
            key={k}
            className={`spine spine-${k} bg-panel border-line rounded-lg border py-3 pr-4`}
          >
            <div className="font-mono text-2xl font-medium">
              {(by[k] ?? 0).toLocaleString()}
            </div>
            <div className="text-mute text-xs">{verdictLabels[k]}</div>
          </div>
        ))}
      </div>

      <Panel title={t('dailyExecutions')}>
        {trend.isPending ? <Spinner /> : <TrendChart days={trend.data ?? []} />}
      </Panel>

      <Panel title={t('flakyCandidates')}>
        {flaky.isPending ? (
          <Spinner />
        ) : (flaky.data?.items.length ?? 0) === 0 ? (
          <div className="text-mute py-6 text-center text-sm">
            {t('noFlaky')}
          </div>
        ) : (
          <table className="w-full text-[13px]">
            <thead>
              <tr className="text-mute border-line border-b text-left text-xs">
                <th className="pb-2 font-medium">{t('testCase')}</th>
                <th className="pb-2 text-right font-medium">{t('statusFlips')}</th>
                <th className="pb-2 text-right font-medium">{t('runs')}</th>
                <th className="pb-2 pl-6 font-medium">{t('instability')}</th>
              </tr>
            </thead>
            <tbody>
              {flaky.data!.items.slice(0, 12).map((f) => {
                const ratio = f.flips / Math.max(1, f.total - 1)
                return (
                  <tr key={f.tcase_id} className="border-line border-b last:border-0">
                    <td className="py-1.5">
                      <button
                        className="hover:text-accent text-left"
                        onClick={() =>
                          navigate({
                            to: '/spec',
                            search: { caseId: String(f.tcase_id) },
                          })
                        }
                      >
                        {f.name}
                      </button>
                    </td>
                    <td className="py-1.5 text-right font-mono">{f.flips}</td>
                    <td className="py-1.5 text-right font-mono">{f.total}</td>
                    <td className="py-1.5 pl-6">
                      <div className="bg-line h-1.5 w-full max-w-40 overflow-hidden rounded-full">
                        <div
                          className="h-full rounded-full"
                          style={{
                            width: `${Math.min(100, ratio * 100)}%`,
                            background: VERDICT_COLOR.b,
                          }}
                        />
                      </div>
                    </td>
                  </tr>
                )
              })}
            </tbody>
          </table>
        )}
      </Panel>
    </div>
  )
}
