import { useState } from 'react'
import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query'
import { api } from '../lib/api'
import { useT } from '../lib/i18n'
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
  const { t } = useT()
  const qc = useQueryClient()
  const [newPlan, setNewPlan] = useState('')
  const [newBuild, setNewBuild] = useState('')
  const [newProjectName, setNewProjectName] = useState('')
  const [newProjectPrefix, setNewProjectPrefix] = useState('')

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

  const createProject = useMutation({
    mutationFn: () =>
      api.createProject(newProjectName, newProjectPrefix.toUpperCase()),
    onSuccess: () => {
      setNewProjectName('')
      setNewProjectPrefix('')
      qc.invalidateQueries({ queryKey: ['projects'] })
    },
  })

  const toggleBuild = useMutation({
    mutationFn: (input: { id: string; is_open: number }) =>
      api.updateBuild(input.id, { is_open: input.is_open }),
    onSuccess: () => qc.invalidateQueries({ queryKey: ['builds'] }),
  })

  return (
    <div className="mx-auto flex max-w-3xl flex-col gap-4">
      <h1 className="font-display text-xl font-bold tracking-tight">
        {t('testPlans')} · {project?.name}
      </h1>

      <Panel title={t('navPlans')}>
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
                <span className="text-mute ml-auto text-xs">
                  {t('selectedTag')}
                </span>
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
            placeholder={t('newPlanPlaceholder')}
            value={newPlan}
            onChange={(e) => setNewPlan(e.target.value)}
          />
          <Button type="submit" disabled={!newPlan.trim() || createPlan.isPending}>
            {t('createPlan')}
          </Button>
        </form>
      </Panel>

      <Panel title={`${t('builds')} · ${plan?.name ?? ''}`}>
        {builds.isPending ? (
          <Spinner />
        ) : (builds.data?.length ?? 0) === 0 ? (
          <EmptyState>{t('noBuilds')}</EmptyState>
        ) : (
          <table className="w-full text-[13px]">
            <tbody>
              {builds.data!.map((b) => (
                <tr key={b.id} className="border-line border-b last:border-0">
                  <td className="py-1.5 font-medium">{b.name}</td>
                  <td className="text-mute py-1.5 text-right text-xs">
                    {Number(b.active) ? t('activeLabel') : t('inactiveLabel')} ·{' '}
                    {Number(b.is_open) ? t('openLabel') : t('closedLabel')}
                  </td>
                  <td className="py-1.5 pl-2 text-right">
                    <Button
                      kind="ghost"
                      disabled={toggleBuild.isPending}
                      onClick={() =>
                        toggleBuild.mutate({
                          id: b.id,
                          is_open: Number(b.is_open) === 1 ? 0 : 1,
                        })
                      }
                    >
                      {Number(b.is_open) === 1 ? t('buildClose') : t('buildReopen')}
                    </Button>
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
            placeholder={t('newBuildPlaceholder')}
            value={newBuild}
            onChange={(e) => setNewBuild(e.target.value)}
          />
          <Button
            type="submit"
            disabled={!newBuild.trim() || createBuild.isPending || !plan}
          >
            {t('createBuild')}
          </Button>
        </form>
      </Panel>

      <Panel title={t('newProjectTitle')}>
        <form
          className="flex gap-2"
          onSubmit={(e) => {
            e.preventDefault()
            if (newProjectName.trim() && newProjectPrefix.trim())
              createProject.mutate()
          }}
        >
          <TextInput
            placeholder={t('projectNamePlaceholder')}
            value={newProjectName}
            onChange={(e) => setNewProjectName(e.target.value)}
          />
          <TextInput
            placeholder={t('projectPrefixPlaceholder')}
            value={newProjectPrefix}
            onChange={(e) => setNewProjectPrefix(e.target.value)}
          />
          <Button
            type="submit"
            disabled={
              !newProjectName.trim() ||
              !newProjectPrefix.trim() ||
              createProject.isPending
            }
          >
            {t('createProject')}
          </Button>
        </form>
      </Panel>
    </div>
  )
}
