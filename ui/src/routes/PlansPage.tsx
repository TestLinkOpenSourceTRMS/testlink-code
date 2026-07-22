import { useState } from 'react'
import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query'
import { api } from '../lib/api'
import { useWorkspace } from '../lib/workspace'
import {
  Button,
  EmptyState,
  Panel,
  Spinner,
  TextInput,
} from '../components/ui'

export function PlansPage() {
  const { project, plans, plan, selectPlan } = useWorkspace()
  const qc = useQueryClient()
  const [newPlan, setNewPlan] = useState('')
  const [newBuild, setNewBuild] = useState('')

  const builds = useQuery({
    queryKey: ['builds', plan?.id],
    queryFn: () => api.planBuilds(plan!.id),
    enabled: plan != null,
  })

  const createPlan = useMutation({
    mutationFn: () =>
      api.createPlan({
        name: newPlan,
        testProjectID: Number(project!.id),
        notes: '',
      }),
    onSuccess: () => {
      setNewPlan('')
      qc.invalidateQueries({ queryKey: ['plans'] })
    },
  })

  const createBuild = useMutation({
    mutationFn: () =>
      api.createBuild({
        name: newBuild,
        testplan: Number(plan!.id),
        notes: '',
      }),
    onSuccess: () => {
      setNewBuild('')
      qc.invalidateQueries({ queryKey: ['builds'] })
    },
  })

  return (
    <div className="mx-auto flex max-w-3xl flex-col gap-4">
      <h1 className="font-display text-xl font-bold tracking-tight">
        Test plans · {project?.name}
      </h1>

      <Panel title="Plans">
        <div className="flex flex-col gap-1">
          {plans.map((p) => (
            <button
              key={p.id}
              onClick={() => selectPlan(p.id)}
              className={`flex items-center rounded-md px-3 py-2 text-left text-[13.5px] transition-colors ${
                plan?.id === p.id
                  ? 'bg-accent-soft text-accent font-medium'
                  : 'hover:bg-paper'
              }`}
            >
              {p.name}
              {plan?.id === p.id && (
                <span className="text-mute ml-auto text-xs">selected</span>
              )}
            </button>
          ))}
        </div>
        <form
          className="mt-3 flex gap-2"
          onSubmit={(e) => {
            e.preventDefault()
            if (newPlan.trim()) createPlan.mutate()
          }}
        >
          <TextInput
            placeholder="New plan name"
            value={newPlan}
            onChange={(e) => setNewPlan(e.target.value)}
          />
          <Button type="submit" disabled={!newPlan.trim() || createPlan.isPending}>
            Create plan
          </Button>
        </form>
      </Panel>

      <Panel title={`Builds · ${plan?.name ?? ''}`}>
        {builds.isPending ? (
          <Spinner />
        ) : (builds.data?.length ?? 0) === 0 ? (
          <EmptyState>No builds yet — create the first one below.</EmptyState>
        ) : (
          <table className="w-full text-[13px]">
            <tbody>
              {builds.data!.map((b) => (
                <tr key={b.id} className="border-line border-b last:border-0">
                  <td className="py-1.5 font-medium">{b.name}</td>
                  <td className="text-mute py-1.5 text-right text-xs">
                    {Number(b.active) ? 'active' : 'inactive'} ·{' '}
                    {Number(b.is_open) ? 'open' : 'closed'}
                  </td>
                </tr>
              ))}
            </tbody>
          </table>
        )}
        <form
          className="mt-3 flex gap-2"
          onSubmit={(e) => {
            e.preventDefault()
            if (newBuild.trim()) createBuild.mutate()
          }}
        >
          <TextInput
            placeholder="New build name"
            value={newBuild}
            onChange={(e) => setNewBuild(e.target.value)}
          />
          <Button
            type="submit"
            disabled={!newBuild.trim() || createBuild.isPending || !plan}
          >
            Create build
          </Button>
        </form>
      </Panel>
    </div>
  )
}
