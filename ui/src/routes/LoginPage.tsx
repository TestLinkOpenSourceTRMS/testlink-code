import { useState } from 'react'
import { useNavigate } from '@tanstack/react-router'
import { api, setSession } from '../lib/api'
import { Button, TextInput } from '../components/ui'

export function LoginPage() {
  const navigate = useNavigate()
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
      setError(err instanceof Error ? err.message : 'Sign-in failed')
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
            Plan, run and read your testing.
          </p>
        </div>
        <form onSubmit={submit} className="flex flex-col gap-3">
          <TextInput
            placeholder="Login"
            value={login}
            onChange={(e) => setLogin(e.target.value)}
            autoFocus
            autoComplete="username"
          />
          <TextInput
            placeholder="Password"
            type="password"
            value={password}
            onChange={(e) => setPassword(e.target.value)}
            autoComplete="current-password"
          />
          {error && (
            <div className="text-[13px] text-[var(--color-fail)]">{error}</div>
          )}
          <Button type="submit" disabled={busy || !login || !password}>
            {busy ? 'Signing in…' : 'Sign in'}
          </Button>
        </form>
      </div>
    </div>
  )
}
