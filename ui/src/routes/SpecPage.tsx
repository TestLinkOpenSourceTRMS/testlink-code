import { useEffect, useMemo, useState } from 'react'
import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query'
import { useSearch } from '@tanstack/react-router'
import {
  ChevronDown,
  ChevronRight,
  FileText,
  FolderPlus,
  Link2,
  Paperclip,
  Pencil,
  Plus,
} from 'lucide-react'
import { api, type Suite } from '../lib/api'
import { useT } from '../lib/i18n'
import { useWorkspace } from '../lib/workspace'
import { CaseEditor } from '../components/CaseEditor'
import {
  Button,
  CaseId,
  EmptyState,
  Panel,
  Spinner,
  TextInput,
  VerdictBadge,
  normVerdict,
} from '../components/ui'

interface TreeNode extends Suite {
  children: TreeNode[]
}

function buildTree(suites: Suite[], rootId: number): TreeNode[] {
  const byParent = new Map<number, Suite[]>()
  for (const s of suites) {
    const list = byParent.get(s.parent_id) ?? []
    list.push(s)
    byParent.set(s.parent_id, list)
  }
  const attach = (parentId: number): TreeNode[] =>
    (byParent.get(parentId) ?? []).map((s) => ({
      ...s,
      children: attach(s.id),
    }))
  return attach(rootId)
}

function SuiteRow({
  node,
  depth,
  selected,
  onSelect,
}: {
  node: TreeNode
  depth: number
  selected: number | null
  onSelect: (id: number) => void
}) {
  const { t } = useT()
  const [open, setOpen] = useState(depth === 0)
  const isSelected = selected === node.id
  return (
    <>
      <div
        className={`flex cursor-pointer items-center gap-1 rounded-md py-1 pr-2 text-[13px] transition-colors ${
          isSelected ? 'bg-accent-soft text-accent font-medium' : 'hover:bg-paper'
        }`}
        style={{ paddingLeft: depth * 14 + 4 }}
        onClick={() => onSelect(node.id)}
      >
        {node.children.length > 0 ? (
          <button
            onClick={(e) => {
              e.stopPropagation()
              setOpen(!open)
            }}
            className="text-mute p-0.5"
            aria-label={open ? t('collapse') : t('expand')}
          >
            {open ? (
              <ChevronDown className="size-3.5" />
            ) : (
              <ChevronRight className="size-3.5" />
            )}
          </button>
        ) : (
          <span className="w-[18px]" />
        )}
        <span className="truncate">{node.name}</span>
        <span className="text-mute ml-auto font-mono text-[11px]">
          {node.tcCount}
        </span>
      </div>
      {open &&
        node.children.map((c) => (
          <SuiteRow
            key={c.id}
            node={c}
            depth={depth + 1}
            selected={selected}
            onSelect={onSelect}
          />
        ))}
    </>
  )
}

export function SpecPage() {
  const { project, plan } = useWorkspace()
  const { t } = useT()
  const qc = useQueryClient()
  const search = useSearch({ strict: false }) as { caseId?: string }
  const [suiteId, setSuiteId] = useState<number | null>(null)
  const [caseId, setCaseId] = useState<string | null>(search.caseId ?? null)
  const [editing, setEditing] = useState(false)
  const [newCaseName, setNewCaseName] = useState('')
  const [creatingSuite, setCreatingSuite] = useState(false)
  const [newSuiteName, setNewSuiteName] = useState('')
  const [flash, setFlash] = useState('')

  // deep links from Matrix/Run/Dashboard land here with ?caseId=
  useEffect(() => {
    if (search.caseId) setCaseId(search.caseId)
  }, [search.caseId])

  const suites = useQuery({
    queryKey: ['suites', project?.id],
    queryFn: () => api.suites(project!.id),
    enabled: project != null,
  })

  const tree = useMemo(
    () => (suites.data && project ? buildTree(suites.data, Number(project.id)) : []),
    [suites.data, project],
  )

  const cases = useQuery({
    queryKey: ['suiteCases', suiteId],
    queryFn: () => api.suiteCases(suiteId!),
    enabled: suiteId != null,
  })

  const detail = useQuery({
    queryKey: ['case', caseId],
    queryFn: () => api.caseDetail(caseId!),
    enabled: caseId != null,
  })

  // when deep-linked, select the owning suite once the case resolves
  useEffect(() => {
    if (detail.data && suiteId == null) {
      setSuiteId(Number(detail.data.suite_id))
    }
  }, [detail.data, suiteId])

  const createCase = useMutation({
    mutationFn: () =>
      api.createCase({
        name: newCaseName,
        suiteId: suiteId!,
        projectId: Number(project!.id),
      }),
    onSuccess: (r) => {
      setNewCaseName('')
      qc.invalidateQueries({ queryKey: ['suiteCases', suiteId] })
      setCaseId(String(r.id))
    },
  })

  const createSuite = useMutation({
    mutationFn: () =>
      api.createSuite({
        name: newSuiteName,
        testProjectID: Number(project!.id),
        parentID: suiteId ?? Number(project!.id),
      }),
    onSuccess: () => {
      setNewSuiteName('')
      setCreatingSuite(false)
      qc.invalidateQueries({ queryKey: ['suites'] })
    },
  })

  const linkCase = useMutation({
    mutationFn: () => api.linkToPlan(plan!.id, [Number(caseId)]),
    onSuccess: (r) => {
      qc.invalidateQueries({ queryKey: ['queue'] })
      qc.invalidateQueries({ queryKey: ['summary'] })
      setFlash(
        r.linked > 0
          ? t('linkedToPlan')(plan!.name)
          : t('alreadyInPlan')(plan!.name),
      )
      setTimeout(() => setFlash(''), 2500)
    },
  })

  return (
    <div className="flex h-full min-h-0 gap-4">
      {/* suite tree */}
      <div className="bg-panel border-line flex w-72 shrink-0 flex-col overflow-hidden rounded-lg border">
        <div className="border-line flex items-center justify-between border-b px-3 py-2">
          <span className="text-mute text-xs font-medium uppercase">
            {t('suites')}
          </span>
          <button
            className="text-accent inline-flex items-center gap-1 text-xs hover:underline"
            onClick={() => setCreatingSuite(!creatingSuite)}
            title={t('newSuiteTitle')}
          >
            <FolderPlus className="size-3.5" /> {t('newSuite')}
          </button>
        </div>
        {creatingSuite && (
          <form
            className="border-line flex gap-1 border-b p-2"
            onSubmit={(e) => {
              e.preventDefault()
              if (newSuiteName.trim()) createSuite.mutate()
            }}
          >
            <TextInput
              placeholder={t('suiteNamePlaceholder')}
              value={newSuiteName}
              onChange={(e) => setNewSuiteName(e.target.value)}
              autoFocus
            />
            <Button type="submit" disabled={!newSuiteName.trim() || createSuite.isPending}>
              {t('add')}
            </Button>
          </form>
        )}
        <div className="flex-1 overflow-auto p-2">
          {suites.isPending ? (
            <Spinner />
          ) : (
            tree.map((n) => (
              <SuiteRow
                key={n.id}
                node={n}
                depth={0}
                selected={suiteId}
                onSelect={(id) => {
                  setSuiteId(id)
                  setCaseId(null)
                  setEditing(false)
                }}
              />
            ))
          )}
        </div>
      </div>

      {/* case list */}
      <div className="bg-panel border-line flex w-96 shrink-0 flex-col overflow-hidden rounded-lg border">
        {suiteId != null && (
          <form
            className="border-line flex gap-1 border-b p-2"
            onSubmit={(e) => {
              e.preventDefault()
              if (newCaseName.trim()) createCase.mutate()
            }}
          >
            <TextInput
              placeholder={t('newCaseNamePlaceholder')}
              value={newCaseName}
              onChange={(e) => setNewCaseName(e.target.value)}
            />
            <Button
              type="submit"
              disabled={!newCaseName.trim() || createCase.isPending}
            >
              <Plus className="size-4" />
            </Button>
          </form>
        )}
        <div className="flex-1 overflow-auto">
          {suiteId == null ? (
            <EmptyState>{t('pickSuite')}</EmptyState>
          ) : cases.isPending ? (
            <Spinner />
          ) : (cases.data?.length ?? 0) === 0 ? (
            <EmptyState>{t('emptySuite')}</EmptyState>
          ) : (
            cases.data!.map((c) => (
              <button
                key={c.id}
                onClick={() => {
                  setCaseId(c.id)
                  setEditing(false)
                }}
                className={`border-line flex w-full items-center gap-2.5 border-b px-3 py-2 text-left text-[13px] transition-colors last:border-0 ${
                  caseId === c.id ? 'bg-accent-soft' : 'hover:bg-paper'
                }`}
              >
                <FileText className="text-mute size-3.5 shrink-0" strokeWidth={1.8} />
                <span className="truncate">{c.name}</span>
                <span className="ml-auto shrink-0">
                  <CaseId prefix={project?.prefix ?? ''} ext={c.tc_external_id} />
                </span>
              </button>
            ))
          )}
        </div>
      </div>

      {/* case detail */}
      <div className="min-w-0 flex-1 overflow-auto">
        {caseId == null ? (
          <EmptyState>{t('selectCase')}</EmptyState>
        ) : detail.isPending ? (
          <Spinner />
        ) : detail.data && editing ? (
          <CaseEditor
            caseId={caseId}
            detail={detail.data}
            onDone={() => setEditing(false)}
          />
        ) : detail.data ? (
          <div className="flex flex-col gap-4">
            <div>
              <div className="flex items-baseline gap-3">
                <h1 className="font-display text-lg font-bold tracking-tight">
                  {detail.data.name}
                </h1>
                <CaseId
                  prefix={project?.prefix ?? ''}
                  ext={detail.data.tc_external_id}
                />
                <span className="text-mute font-mono text-xs">
                  v{detail.data.version}
                </span>
                <span className="ml-auto flex items-center gap-2">
                  {flash && (
                    <span className="text-accent text-xs">{flash}</span>
                  )}
                  <Button kind="ghost" onClick={() => setEditing(true)}>
                    <span className="inline-flex items-center gap-1.5">
                      <Pencil className="size-3.5" /> {t('edit')}
                    </span>
                  </Button>
                  <Button
                    kind="ghost"
                    onClick={() => linkCase.mutate()}
                    disabled={!plan || linkCase.isPending}
                  >
                    <span className="inline-flex items-center gap-1.5">
                      <Link2 className="size-3.5" /> {t('addToPlan')(plan?.name ?? '')}
                    </span>
                  </Button>
                </span>
              </div>
              {detail.data.summary && (
                <div
                  className="text-mute mt-1 text-sm"
                  dangerouslySetInnerHTML={{ __html: detail.data.summary }}
                />
              )}
            </div>

            <Panel title={t('stepsCount')(detail.data.steps.length)}>
              {detail.data.steps.length === 0 ? (
                <EmptyState>{t('noStepsWritten')}</EmptyState>
              ) : (
                <ol className="flex flex-col gap-2">
                  {detail.data.steps.map((s) => (
                    <li
                      key={s.step_number}
                      className="border-line grid grid-cols-[28px_1fr_1fr] gap-3 rounded-md border p-3"
                    >
                      <div className="font-mono text-mute text-sm">
                        {s.step_number}
                      </div>
                      <div
                        className="text-[13px]"
                        dangerouslySetInnerHTML={{ __html: s.actions }}
                      />
                      <div
                        className="text-mute border-line border-l pl-3 text-[13px]"
                        dangerouslySetInnerHTML={{ __html: s.expected_results }}
                      />
                    </li>
                  ))}
                </ol>
              )}
            </Panel>

            <Panel title={t('recentRuns')}>
              {detail.data.executions.length === 0 ? (
                <EmptyState>{t('neverExecuted')}</EmptyState>
              ) : (
                <table className="w-full text-[13px]">
                  <tbody>
                    {detail.data.executions.map((e) => (
                      <tr
                        key={e.id}
                        className={`spine spine-${normVerdict(e.status)} border-line border-b last:border-0`}
                      >
                        <td className="py-1.5 pl-3">
                          <VerdictBadge verdict={normVerdict(e.status)} />
                        </td>
                        <td className="text-mute py-1.5 font-mono text-xs">
                          {e.execution_ts}
                        </td>
                        <td className="py-1.5">{e.build_name}</td>
                        <td className="text-mute py-1.5">{e.tester}</td>
                        <td className="py-1.5" title={t('attachmentsLabel')}>
                          {e.attachments && e.attachments.length > 0 && (
                            <div className="flex flex-wrap gap-2">
                              {e.attachments.map((att) => (
                                <button
                                  key={att.id}
                                  className="text-accent inline-flex items-center gap-1 text-xs hover:underline"
                                  onClick={() =>
                                    api
                                      .attachmentUrl(att.id)
                                      .then((url) => window.open(url, '_blank'))
                                  }
                                >
                                  <Paperclip className="size-3" />
                                  {att.file_name}
                                </button>
                              ))}
                            </div>
                          )}
                        </td>
                      </tr>
                    ))}
                  </tbody>
                </table>
              )}
            </Panel>
          </div>
        ) : null}
      </div>
    </div>
  )
}
