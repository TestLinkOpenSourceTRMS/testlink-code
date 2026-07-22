import type { ReactNode } from 'react'
import type { Verdict } from '../lib/api'

export const VERDICT_LABEL: Record<Verdict, string> = {
  p: 'Passed',
  f: 'Failed',
  b: 'Blocked',
  n: 'Not run',
}

export const VERDICT_COLOR: Record<Verdict, string> = {
  p: 'var(--color-pass)',
  f: 'var(--color-fail)',
  b: 'var(--color-blocked)',
  n: 'var(--color-notrun)',
}

export function normVerdict(s: string | null | undefined): Verdict {
  return s === 'p' || s === 'f' || s === 'b' ? s : 'n'
}

/** small colored dot + label */
export function VerdictBadge({ verdict }: { verdict: Verdict }) {
  return (
    <span className="inline-flex items-center gap-1.5 text-xs font-medium">
      <span
        className="size-2 rounded-full"
        style={{ background: VERDICT_COLOR[verdict] }}
      />
      {VERDICT_LABEL[verdict]}
    </span>
  )
}

/** monospace test case id — the currency of QA conversation */
export function CaseId({ prefix, ext }: { prefix: string; ext: string }) {
  return (
    <span className="text-mute font-mono text-xs tracking-tight">
      {prefix}-{ext}
    </span>
  )
}

export function Panel({
  title,
  children,
  actions,
}: {
  title?: string
  children: ReactNode
  actions?: ReactNode
}) {
  return (
    <section className="bg-panel border-line rounded-lg border">
      {title && (
        <header className="border-line flex items-center justify-between border-b px-4 py-2.5">
          <h2 className="font-display text-[13px] font-semibold tracking-wide uppercase">
            {title}
          </h2>
          {actions}
        </header>
      )}
      <div className="p-4">{children}</div>
    </section>
  )
}

export function Button({
  children,
  onClick,
  kind = 'primary',
  disabled,
  type,
}: {
  children: ReactNode
  onClick?: () => void
  kind?: 'primary' | 'ghost' | 'danger'
  disabled?: boolean
  type?: 'submit' | 'button'
}) {
  const styles = {
    primary:
      'bg-accent text-white hover:brightness-110 disabled:opacity-40',
    ghost:
      'bg-transparent text-ink border border-line hover:bg-accent-soft disabled:opacity-40',
    danger: 'bg-fail text-white hover:brightness-110 disabled:opacity-40',
  }
  return (
    <button
      type={type ?? 'button'}
      onClick={onClick}
      disabled={disabled}
      className={`rounded-md px-3 py-1.5 text-[13px] font-medium transition-[filter,background] focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[var(--color-accent)] ${styles[kind]}`}
    >
      {children}
    </button>
  )
}

export function TextInput(props: React.InputHTMLAttributes<HTMLInputElement>) {
  return (
    <input
      {...props}
      className={`border-line bg-panel w-full rounded-md border px-3 py-2 text-[13.5px] focus-visible:outline-2 focus-visible:outline-offset-1 focus-visible:outline-[var(--color-accent)] ${props.className ?? ''}`}
    />
  )
}

export function Spinner() {
  return (
    <div className="text-mute flex items-center gap-2 p-6 text-sm">
      <div className="border-line size-4 animate-spin rounded-full border-2 border-t-[var(--color-accent)]" />
      Loading…
    </div>
  )
}

export function EmptyState({ children }: { children: ReactNode }) {
  return (
    <div className="text-mute px-4 py-8 text-center text-sm">{children}</div>
  )
}
