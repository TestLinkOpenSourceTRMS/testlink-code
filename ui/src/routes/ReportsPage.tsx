import { useQuery } from '@tanstack/react-query'
import { api } from '../lib/api'
import { useWorkspace } from '../lib/workspace'
import { Panel, Spinner } from '../components/ui'
import { TrendChart } from '../components/TrendChart'

export function ReportsPage() {
  const { plan } = useWorkspace()

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

  return (
    <div className="mx-auto flex max-w-5xl flex-col gap-4">
      <h1 className="font-display text-xl font-bold tracking-tight">
        Reports · {plan.name}
      </h1>

      <Panel title="Execution trend">
        {trend.isPending ? <Spinner /> : <TrendChart days={trend.data ?? []} />}
      </Panel>

      <Panel
        title={`Flaky tests · ${flaky.data?.analyzed.toLocaleString() ?? '…'} versions analyzed`}
      >
        {flaky.isPending ? (
          <Spinner />
        ) : (
          <table className="w-full text-[13px]">
            <thead>
              <tr className="text-mute border-line border-b text-left text-xs">
                <th className="pb-2 font-medium">Test case</th>
                <th className="pb-2 text-right font-medium">Flips</th>
                <th className="pb-2 text-right font-medium">Runs</th>
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
