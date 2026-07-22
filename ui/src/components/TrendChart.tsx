import type { TrendDay } from '../lib/api'
import { useT } from '../lib/i18n'
import { VERDICT_COLOR, useVerdictLabels } from './ui'

/**
 * Daily stacked verdict bars, inline SVG.
 * Height maps to executions; each bar splits pass/fail/blocked.
 */
export function TrendChart({ days }: { days: TrendDay[] }) {
  const { t } = useT()
  const verdictLabels = useVerdictLabels()
  if (days.length === 0) {
    return (
      <div className="text-mute py-10 text-center text-sm">
        {t('noExecutionsYet')}
      </div>
    )
  }

  const W = 720
  const H = 180
  const PAD_L = 42
  const PAD_B = 26
  const plotW = W - PAD_L - 8
  const plotH = H - PAD_B - 8
  const max = Math.max(...days.map((d) => d.p + d.f + d.b + d.other), 1)
  const barW = Math.max(6, Math.min(48, Math.floor(plotW / days.length) - 6))

  return (
    <svg
      viewBox={`0 0 ${W} ${H}`}
      className="w-full"
      role="img"
      aria-label={t('trendAriaLabel')}
    >
      {[0, 0.5, 1].map((f) => {
        const y = 8 + plotH - plotH * f
        return (
          <g key={f}>
            <line
              x1={PAD_L}
              x2={W - 8}
              y1={y}
              y2={y}
              stroke="var(--color-line)"
            />
            <text
              x={PAD_L - 6}
              y={y + 3.5}
              textAnchor="end"
              className="fill-[var(--color-mute)] text-[9px]"
            >
              {Math.round(max * f)}
            </text>
          </g>
        )
      })}
      {days.map((d, i) => {
        const x = PAD_L + 6 + i * (barW + 6)
        if (x + barW > W - 8) return null
        let y = 8 + plotH
        const segs = (['p', 'f', 'b'] as const).map((k) => {
          const h = (plotH * d[k]) / max
          y -= h
          return { k, y, h }
        })
        return (
          <g key={d.day}>
            {segs.map(
              (s) =>
                s.h > 0 && (
                  <rect
                    key={s.k}
                    x={x}
                    y={s.y}
                    width={barW}
                    height={s.h}
                    rx={1.5}
                    fill={VERDICT_COLOR[s.k]}
                  >
                    <title>{`${d.day} ${verdictLabels[s.k]}: ${d[s.k]}`}</title>
                  </rect>
                ),
            )}
            <text
              x={x + barW / 2}
              y={H - 10}
              textAnchor="middle"
              className="fill-[var(--color-mute)] font-mono text-[8.5px]"
            >
              {d.day.slice(5)}
            </text>
          </g>
        )
      })}
    </svg>
  )
}
