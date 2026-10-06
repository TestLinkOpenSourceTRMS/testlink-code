import { useMemo, useState } from 'react'
import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query'
import { Link } from '@tanstack/react-router'
import {
  ChevronDown,
  ChevronRight,
  FileCheck2,
  FolderPlus,
  Plus,
  X,
} from 'lucide-react'
import { api, type ReqSpec, type Verdict } from '../lib/api'
import { useT } from '../lib/i18n'
import { useWorkspace } from '../lib/workspace'
import {
  Button,
  EmptyState,
  Panel,
  Spinner,
  TextInput,
  VERDICT_COLOR,
  useVerdictLabels,
} from '../components/ui'

interface SpecNode extends ReqSpec {
  children: SpecNode[]
}

function buildSpecTree(specs: ReqSpec[], rootId: number): SpecNode[] {
  const byParent = new Map<number, ReqSpec[]>()
  for (const s of specs) {
    const list = byParent.get(s.parent_id) ?? []
    list.push(s)
    byParent.set(s.parent_id, list)
  }
  const attach = (parentId: number): SpecNode[] =>
    (byParent.get(parentId) ?? []).map((s) => ({
      ...s,
      children: attach(s.id),
    }))
  return attach(rootId)
}

function SpecRow({
  node,
  depth,
  selected,
  onSelect,
}: {
  node: SpecNode
  depth: number
  selected: number | null
  onSelect: (id: number) => void
}) {
  const { t } = useT()
  const [open, setOpen] = useState(true)
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
        <span className="text-mute shrink-0 font-mono text-[11px]">
          {node.doc_id}
        </span>
        <span className="truncate">{node.name}</span>
        <span className="text-mute ml-auto font-mono text-[11px]">
          {node.reqCount}
        </span>
      </div>
      {open &&
        node.children.map((c) => (
          <SpecRow
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

const VERDICT_ORDER: Verdict[] = ['p', 'f', 'b', 'n']

/** proportional stacked bar of latest-run verdicts for one requirement */
function VerdictBar({
  counts,
}: {
  counts: Record<Verdict, number>
}) {
  const labels = useVerdictLabels()
  const total = VERDICT_ORDER.reduce((sum, k) => sum + counts[k], 0)
  if (total === 0) return null
  return (
    <div className="border-line flex h-2.5 w-40 shrink-0 overflow-hidden rounded-full border">
      {VERDICT_ORDER.filter((k) => counts[k] > 0).map((k) => (
        <div
          key={k}
          title={`${labels[k]}: ${counts[k]}`}
          style={{
            width: `${(counts[k] / total) * 100}%`,
            background: VERDICT_COLOR[k],
          }}
        />
      ))}
    </div>
  )
}

function CoverageRollup() {
  const { plan } = useWorkspace()
  const { t } = useT()
  const labels = useVerdictLabels()

  const rollup = useQuery({
    queryKey: ['reqPlanCoverage', plan?.id],
    queryFn: () => api.planReqCoverage(plan!.id),
    enabled: plan != null,
  })

  if (!plan) return null
  return (
    <Panel title={t('reqPlanCoverage')(plan.name)}>
      {rollup.isPending ? (
        <Spinner />
      ) : (rollup.data?.length ?? 0) === 0 ? (
        <EmptyState>{t('noReqCoverageInPlan')}</EmptyState>
      ) : (
        <div className="flex flex-col gap-1.5">
          {rollup.data!.map((r) => (
            <div key={r.req_id} className="flex items-center gap-3 text-[13px]">
              <span className="text-mute w-24 shrink-0 truncate font-mono text-xs">
                {r.req_doc_id}
              </span>
              <span className="min-w-0 flex-1 truncate">{r.name}</span>
              <span className="text-mute shrink-0 text-xs">
                {t('inPlanOfCovered')(r.inPlan, r.covered)}
              </span>
              <VerdictBar counts={{ p: r.p, f: r.f, b: r.b, n: r.n }} />
              <span className="flex w-40 shrink-0 items-center gap-2">
                {VERDICT_ORDER.map((k) => (
                  <span
                    key={k}
                    title={labels[k]}
                    className="inline-flex items-center gap-1 font-mono text-[11px]"
                  >
                    <span
                      className="size-1.5 rounded-full"
                      style={{ background: VERDICT_COLOR[k] }}
                    />
                    {{ p: r.p, f: r.f, b: r.b, n: r.n }[k]}
                  </span>
                ))}
              </span>
            </div>
          ))}
        </div>
      )}
    </Panel>
  )
}

export function RequirementsPage() {
  const { project } = useWorkspace()
  const { t } = useT()
  const qc = useQueryClient()
  const [specId, setSpecId] = useState<number | null>(null)
  const [reqId, setReqId] = useState<number | null>(null)
  const [creatingSpec, setCreatingSpec] = useState(false)
  const [newSpecDocId, setNewSpecDocId] = useState('')
  const [newSpecTitle, setNewSpecTitle] = useState('')
  const [newReqDocId, setNewReqDocId] = useState('')
  const [newReqTitle, setNewReqTitle] = useState('')
  const [coverQ, setCoverQ] = useState('')

  const specs = useQuery({
    queryKey: ['reqSpecs', project?.id],
    queryFn: () => api.reqSpecs(project!.id),
    enabled: project != null,
  })

  const tree = useMemo(
    () =>
      specs.data && project
        ? buildSpecTree(specs.data, Number(project.id))
        : [],
    [specs.data, project],
  )

  const reqs = useQuery({
    queryKey: ['specReqs', specId],
    queryFn: () => api.specRequirements(specId!),
    enabled: specId != null,
  })

  const detail = useQuery({
    queryKey: ['req', reqId],
    queryFn: () => api.requirementDetail(reqId!),
    enabled: reqId != null,
  })

  const coverHits = useQuery({
    queryKey: ['reqCoverSearch', project?.id, coverQ],
    queryFn: () => api.search(project!.id, coverQ),
    enabled: project != null && coverQ.trim().length >= 2,
    placeholderData: (prev) => prev,
  })

  const invalidateAfterCoverageChange = () => {
    qc.invalidateQueries({ queryKey: ['req', reqId] })
    qc.invalidateQueries({ queryKey: ['specReqs', specId] })
    qc.invalidateQueries({ queryKey: ['reqPlanCoverage'] })
  }

  const createSpec = useMutation({
    mutationFn: () =>
      api.createReqSpec({
        testProjectID: Number(project!.id),
        parentID: specId ?? undefined,
        docID: newSpecDocId,
        title: newSpecTitle,
      }),
    onSuccess: (r) => {
      setNewSpecDocId('')
      setNewSpecTitle('')
      setCreatingSpec(false)
      qc.invalidateQueries({ queryKey: ['reqSpecs'] })
      setSpecId(r.id)
      setReqId(null)
    },
  })

  const createReq = useMutation({
    mutationFn: () =>
      api.createRequirement({
        reqSpecID: specId!,
        docID: newReqDocId,
        title: newReqTitle,
      }),
    onSuccess: (r) => {
      setNewReqDocId('')
      setNewReqTitle('')
      qc.invalidateQueries({ queryKey: ['specReqs', specId] })
      qc.invalidateQueries({ queryKey: ['reqSpecs'] })
      setReqId(r.id)
    },
  })

  const addCoverage = useMutation({
    mutationFn: (tcaseId: number) => api.addReqCoverage(reqId!, [tcaseId]),
    onSuccess: invalidateAfterCoverageChange,
  })

  const removeCoverage = useMutation({
    mutationFn: (tcaseId: number) => api.removeReqCoverage(reqId!, tcaseId),
    onSuccess: invalidateAfterCoverageChange,
  })

  const coveredIds = new Set(
    (detail.data?.coverage ?? []).map((c) => c.tcase_id),
  )

  return (
    <div className="flex h-full min-h-0 flex-col gap-4">
      <div className="flex min-h-0 flex-1 gap-4">
        {/* spec tree */}
        <div className="bg-panel border-line flex w-72 shrink-0 flex-col overflow-hidden rounded-lg border">
          <div className="border-line flex items-center justify-between border-b px-3 py-2">
            <span className="text-mute text-xs font-medium uppercase">
              {t('reqSpecs')}
            </span>
            <button
              className="text-accent inline-flex items-center gap-1 text-xs hover:underline"
              onClick={() => setCreatingSpec(!creatingSpec)}
              title={t('newReqSpecTitle')}
            >
              <FolderPlus className="size-3.5" /> {t('newReqSpec')}
            </button>
          </div>
          {creatingSpec && (
            <form
              className="border-line flex flex-col gap-1 border-b p-2"
              onSubmit={(e) => {
                e.preventDefault()
                if (newSpecDocId.trim() && newSpecTitle.trim())
                  createSpec.mutate()
              }}
            >
              <TextInput
                placeholder={t('reqSpecDocIdPlaceholder')}
                value={newSpecDocId}
                onChange={(e) => setNewSpecDocId(e.target.value)}
                autoFocus
              />
              <div className="flex gap-1">
                <TextInput
                  placeholder={t('reqSpecTitlePlaceholder')}
                  value={newSpecTitle}
                  onChange={(e) => setNewSpecTitle(e.target.value)}
                />
                <Button
                  type="submit"
                  disabled={
                    !newSpecDocId.trim() ||
                    !newSpecTitle.trim() ||
                    createSpec.isPending
                  }
                >
                  {t('add')}
                </Button>
              </div>
            </form>
          )}
          <div className="flex-1 overflow-auto p-2">
            {specs.isPending ? (
              <Spinner />
            ) : (
              tree.map((n) => (
                <SpecRow
                  key={n.id}
                  node={n}
                  depth={0}
                  selected={specId}
                  onSelect={(id) => {
                    setSpecId(id)
                    setReqId(null)
                  }}
                />
              ))
            )}
          </div>
        </div>

        {/* requirement list */}
        <div className="bg-panel border-line flex w-96 shrink-0 flex-col overflow-hidden rounded-lg border">
          {specId != null && (
            <form
              className="border-line flex gap-1 border-b p-2"
              onSubmit={(e) => {
                e.preventDefault()
                if (newReqDocId.trim() && newReqTitle.trim()) createReq.mutate()
              }}
            >
              <TextInput
                placeholder={t('newReqDocIdPlaceholder')}
                value={newReqDocId}
                onChange={(e) => setNewReqDocId(e.target.value)}
                className="w-32 shrink-0"
              />
              <TextInput
                placeholder={t('newReqTitlePlaceholder')}
                value={newReqTitle}
                onChange={(e) => setNewReqTitle(e.target.value)}
              />
              <Button
                type="submit"
                disabled={
                  !newReqDocId.trim() ||
                  !newReqTitle.trim() ||
                  createReq.isPending
                }
              >
                <Plus className="size-4" />
              </Button>
            </form>
          )}
          <div className="flex-1 overflow-auto">
            {specId == null ? (
              <EmptyState>{t('pickReqSpec')}</EmptyState>
            ) : reqs.isPending ? (
              <Spinner />
            ) : (reqs.data?.length ?? 0) === 0 ? (
              <EmptyState>{t('emptyReqSpec')}</EmptyState>
            ) : (
              reqs.data!.map((r) => (
                <button
                  key={r.id}
                  onClick={() => setReqId(r.id)}
                  className={`border-line flex w-full items-center gap-2.5 border-b px-3 py-2 text-left text-[13px] transition-colors last:border-0 ${
                    reqId === r.id ? 'bg-accent-soft' : 'hover:bg-paper'
                  }`}
                >
                  <FileCheck2
                    className="text-mute size-3.5 shrink-0"
                    strokeWidth={1.8}
                  />
                  <span className="text-mute shrink-0 font-mono text-xs">
                    {r.req_doc_id}
                  </span>
                  <span className="truncate">{r.name}</span>
                  <span
                    className="text-mute ml-auto shrink-0 font-mono text-[11px]"
                    title={t('coverage')}
                  >
                    {r.coverageCount}
                  </span>
                </button>
              ))
            )}
          </div>
        </div>

        {/* requirement detail */}
        <div className="min-w-0 flex-1 overflow-auto">
          {reqId == null ? (
            <EmptyState>{t('selectRequirement')}</EmptyState>
          ) : detail.isPending ? (
            <Spinner />
          ) : detail.data ? (
            <div className="flex flex-col gap-4">
              <div>
                <div className="flex items-baseline gap-3">
                  <h1 className="font-display text-lg font-bold tracking-tight">
                    {detail.data.name}
                  </h1>
                  <span className="text-mute font-mono text-xs tracking-tight">
                    {detail.data.req_doc_id}
                  </span>
                  <span className="text-mute font-mono text-xs">
                    v{detail.data.version}
                  </span>
                </div>
                {detail.data.scope && (
                  <div className="mt-2">
                    <div className="text-mute text-xs font-medium uppercase">
                      {t('scopeLabel')}
                    </div>
                    <div
                      className="text-mute mt-1 text-sm"
                      dangerouslySetInnerHTML={{ __html: detail.data.scope }}
                    />
                  </div>
                )}
              </div>

              <Panel title={t('coveredCases')}>
                {(detail.data.coverage.length === 0) ? (
                  <EmptyState>{t('noCoveredCases')}</EmptyState>
                ) : (
                  <ul className="flex flex-col">
                    {detail.data.coverage.map((c) => (
                      <li
                        key={c.tcase_id}
                        className="border-line flex items-center gap-2.5 border-b py-1.5 text-[13px] last:border-0"
                      >
                        <span className="text-mute shrink-0 font-mono text-xs">
                          {project?.prefix}-{c.tc_external_id}
                        </span>
                        <Link
                          to="/spec"
                          search={{ caseId: c.tcase_id }}
                          className="text-accent truncate hover:underline"
                          title={t('openInTestCases')}
                        >
                          {c.name}
                        </Link>
                        <button
                          className="text-mute hover:text-fail ml-auto shrink-0 p-0.5"
                          title={t('unlink')}
                          aria-label={t('unlink')}
                          onClick={() =>
                            removeCoverage.mutate(Number(c.tcase_id))
                          }
                        >
                          <X className="size-3.5" />
                        </button>
                      </li>
                    ))}
                  </ul>
                )}

                {/* add coverage via search */}
                <div className="relative mt-3">
                  <TextInput
                    placeholder={t('coverageSearchPlaceholder')}
                    value={coverQ}
                    onChange={(e) => setCoverQ(e.target.value)}
                  />
                  {coverQ.trim().length >= 2 && (
                    <div className="border-line mt-1 max-h-52 overflow-auto rounded-md border">
                      {(coverHits.data?.filter(
                        (h) => !coveredIds.has(h.tcase_id),
                      ).length ?? 0) === 0 ? (
                        <div className="text-mute px-3 py-2 text-sm">
                          {t('searchNoResults')}
                        </div>
                      ) : (
                        coverHits
                          .data!.filter((h) => !coveredIds.has(h.tcase_id))
                          .map((h) => (
                            <button
                              key={h.tcase_id}
                              className="border-line hover:bg-paper flex w-full items-center gap-2 border-b px-3 py-1.5 text-left text-[13px] last:border-0"
                              disabled={addCoverage.isPending}
                              onClick={() => {
                                addCoverage.mutate(Number(h.tcase_id))
                                setCoverQ('')
                              }}
                            >
                              <Plus className="text-accent size-3.5 shrink-0" />
                              <span className="text-mute font-mono text-xs">
                                {h.tc_external_id}
                              </span>
                              <span className="truncate">{h.name}</span>
                              <span className="text-mute ml-auto shrink-0 text-xs">
                                {h.suite_name}
                              </span>
                            </button>
                          ))
                      )}
                    </div>
                  )}
                </div>
              </Panel>
            </div>
          ) : null}
        </div>
      </div>

      {/* plan-scoped verdict rollup per requirement */}
      <CoverageRollup />
    </div>
  )
}
