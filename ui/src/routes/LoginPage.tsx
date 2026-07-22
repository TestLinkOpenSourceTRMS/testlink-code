import { useState } from 'react'
import { useNavigate } from '@tanstack/react-router'
import { api, setSession } from '../lib/api'
import { useT } from '../lib/i18n'
import { Button, TextInput } from '../components/ui'

export function LoginPage() {
  const navigate = useNavigate()
  const { t } = useT()
  const [login, setLogin] = useState('')
  const [password, setPassword] = useState('')
  const [error, setError] = useState('')
  const [busy, setBusy] = useState(false)

  async function submit(e: React.FormEvent) {
    e.preventDefault()
    setBusy(true)
    setError('')
    try {
      const r = await api.login(login, password)
      setSession({ apikey: r.apikey, user: r.user })
      navigate({ to: '/' })
    } catch (err) {
      setError(err instanceof Error ? err.message : t('signInFailed'))
    } finally {
      setBusy(false)
    }
  }

  return (
    <div className="flex h-full items-center justify-center">
      <div className="w-[340px]">
        <div className="mb-8">
          <div className="font-display text-3xl font-bold tracking-tight">
            Test<span className="text-[var(--color-blocked)]">Link</span>
          </div>
          <p className="text-mute mt-1 text-sm">
            {t('loginTagline')}
          </p>
        </div>
        <form onSubmit={submit} className="flex flex-col gap-3">
          <TextInput
            placeholder={t('loginPlaceholder')}
            value={login}
            onChange={(e) => setLogin(e.target.value)}
            autoFocus
            autoComplete="username"
          />
          <TextInput
            placeholder={t('passwordPlaceholder')}
            type="password"
            value={password}
            onChange={(e) => setPassword(e.target.value)}
            autoComplete="current-password"
          />
          {error && (
            <div className="text-[13px] text-[var(--color-fail)]">{error}</div>
          )}
          <Button type="submit" disabled={busy || !login || !password}>
            {busy ? t('signingIn') : t('signIn')}
          </Button>
        </form>
      </div>
    </div>
  )
}
