import { useState } from 'react'
import { useMutation, useQueryClient } from '@tanstack/react-query'
import { Plus, Trash2 } from 'lucide-react'
import { api, type CaseDetail } from '../lib/api'
import { Button, Panel, TextInput } from './ui'

interface EditableStep {
  actions: string
  expected_results: string
}

export function CaseEditor({
  caseId,
  detail,
  onDone,
}: {
  caseId: string
  detail: CaseDetail
  onDone: () => void
}) {
  const qc = useQueryClient()
  const [name, setName] = useState(detail.name)
  const [summary, setSummary] = useState(detail.summary)
  const [preconditions, setPreconditions] = useState(detail.preconditions)
  const [steps, setSteps] = useState<EditableStep[]>(
    detail.steps.map((s) => ({
      actions: s.actions,
      expected_results: s.expected_results,
    })),
  )

  const save = useMutation({
    mutationFn: () =>
      api.updateCase(caseId, {
        name,
        summary,
        preconditions,
        steps: steps
          .filter((s) => s.actions.trim() || s.expected_results.trim())
          .map((s) => ({ ...s, execution_type: 1 })),
      }),
    onSuccess: () => {
      qc.invalidateQueries({ queryKey: ['case', caseId] })
      qc.invalidateQueries({ queryKey: ['suiteCases'] })
      onDone()
    },
  })

  const setStep = (i: number, patch: Partial<EditableStep>) =>
    setSteps(steps.map((s, idx) => (idx === i ? { ...s, ...patch } : s)))

  return (
    <div className="flex flex-col gap-4">
      <div className="flex items-center gap-2">
        <TextInput
          value={name}
          onChange={(e) => setName(e.target.value)}
          className="font-display text-lg font-bold"
        />
        <Button onClick={() => save.mutate()} disabled={save.isPending || !name.trim()}>
          {save.isPending ? 'Saving…' : 'Save changes'}
        </Button>
        <Button kind="ghost" onClick={onDone}>
          Cancel
        </Button>
      </div>
      {save.isError && (
        <div className="text-[13px] text-[var(--color-fail)]">
          Could not save. Try again.
        </div>
      )}

      <Panel title="Summary">
        <textarea
          className="border-line min-h-20 w-full rounded-md border p-2 text-[13px] focus-visible:outline-2 focus-visible:outline-[var(--color-accent)]"
          value={summary}
          onChange={(e) => setSummary(e.target.value)}
          placeholder="What this case verifies"
        />
      </Panel>

      <Panel title="Preconditions">
        <textarea
          className="border-line min-h-16 w-full rounded-md border p-2 text-[13px] focus-visible:outline-2 focus-visible:outline-[var(--color-accent)]"
          value={preconditions}
          onChange={(e) => setPreconditions(e.target.value)}
          placeholder="State required before running"
        />
      </Panel>

      <Panel
        title={`Steps · ${steps.length}`}
        actions={
          <button
            className="text-accent inline-flex items-center gap-1 text-xs hover:underline"
            onClick={() =>
              setSteps([...steps, { actions: '', expected_results: '' }])
            }
          >
            <Plus className="size-3.5" /> Add step
          </button>
        }
      >
        {steps.length === 0 ? (
          <div className="text-mute py-4 text-center text-sm">
            No steps — add the first one.
          </div>
        ) : (
          <ol className="flex flex-col gap-2">
            {steps.map((s, i) => (
              <li
                key={i}
                className="border-line grid grid-cols-[24px_1fr_1fr_28px] gap-2 rounded-md border p-2"
              >
                <div className="text-mute pt-2 text-center font-mono text-sm">
                  {i + 1}
                </div>
                <textarea
                  className="border-line min-h-16 rounded-md border p-2 text-[13px]"
                  placeholder="Action"
                  value={s.actions}
                  onChange={(e) => setStep(i, { actions: e.target.value })}
                />
                <textarea
                  className="border-line min-h-16 rounded-md border p-2 text-[13px]"
                  placeholder="Expected result"
                  value={s.expected_results}
                  onChange={(e) =>
                    setStep(i, { expected_results: e.target.value })
                  }
                />
                <button
                  className="text-mute hover:text-[var(--color-fail)]"
                  title="Remove step"
                  onClick={() => setSteps(steps.filter((_, idx) => idx !== i))}
                >
                  <Trash2 className="size-4" />
                </button>
              </li>
            ))}
          </ol>
        )}
      </Panel>
    </div>
  )
}
