import { useState } from 'react'
import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query'
import { Trash2 } from 'lucide-react'
import { api } from '../lib/api'
import { useT, type StringMsgKey } from '../lib/i18n'
import { useWorkspace } from '../lib/workspace'
import {
  Button,
  EmptyState,
  Panel,
  Spinner,
  TextInput,
} from '../components/ui'

type Tab = 'users' | 'keywords' | 'platforms' | 'cfields'

const TABS: { id: Tab; labelKey: StringMsgKey }[] = [
  { id: 'users', labelKey: 'adminTabUsers' },
  { id: 'keywords', labelKey: 'adminTabKeywords' },
  { id: 'platforms', labelKey: 'adminTabPlatforms' },
  { id: 'cfields', labelKey: 'adminTabCustomFields' },
]

/* ------------------------------- Users -------------------------------- */
function UsersTab() {
  const { t } = useT()
  const qc = useQueryClient()
  const [login, setLogin] = useState('')
  const [password, setPassword] = useState('')
  const [first, setFirst] = useState('')
  const [last, setLast] = useState('')
  const [email, setEmail] = useState('')

  const users = useQuery({ queryKey: ['adminUsers'], queryFn: api.adminUsers })

  const reset = () => {
    setLogin('')
    setPassword('')
    setFirst('')
    setLast('')
    setEmail('')
  }

  const createUser = useMutation({
    mutationFn: () =>
      api.createUser({
        login: login.trim(),
        password,
        firstName: first.trim(),
        lastName: last.trim(),
        email: email.trim(),
      }),
    onSuccess: () => {
      reset()
      qc.invalidateQueries({ queryKey: ['adminUsers'] })
    },
  })

  const toggleActive = useMutation({
    mutationFn: (u: { id: string; active: number }) =>
      api.setUserActive(u.id, u.active ? 0 : 1),
    onSuccess: () => qc.invalidateQueries({ queryKey: ['adminUsers'] }),
  })

  const canSubmit =
    login.trim() && password && first.trim() && last.trim() && email.trim()

  return (
    <div className="flex flex-col gap-4">
      <Panel title={t('adminTabUsers')}>
        {users.isPending ? (
          <Spinner />
        ) : (users.data?.length ?? 0) === 0 ? (
          <EmptyState>{t('noUsers')}</EmptyState>
        ) : (
          <table className="w-full text-[13px]">
            <thead>
              <tr className="text-mute border-line border-b text-left text-xs uppercase">
                <th className="py-1.5 font-medium">{t('adminColLogin')}</th>
                <th className="py-1.5 font-medium">{t('adminColName')}</th>
                <th className="py-1.5 font-medium">{t('adminColEmail')}</th>
                <th className="py-1.5 font-medium">{t('adminColRole')}</th>
                <th className="py-1.5 font-medium">{t('adminColStatus')}</th>
                <th className="py-1.5" />
              </tr>
            </thead>
            <tbody>
              {users.data!.map((u) => (
                <tr key={u.id} className="border-line border-b last:border-0">
                  <td className="py-1.5 font-medium">{u.login}</td>
                  <td className="text-mute py-1.5">
                    {`${u.first} ${u.last}`.trim() || '—'}
                  </td>
                  <td className="text-mute py-1.5">{u.email || '—'}</td>
                  <td className="text-mute py-1.5">{u.role ?? '—'}</td>
                  <td className="py-1.5">
                    <span
                      className={
                        u.active ? 'text-pass text-xs' : 'text-mute text-xs'
                      }
                    >
                      {u.active ? t('activeLabel') : t('inactiveLabel')}
                    </span>
                  </td>
                  <td className="py-1.5 text-right">
                    <Button
                      kind="ghost"
                      disabled={toggleActive.isPending}
                      onClick={() =>
                        toggleActive.mutate({ id: u.id, active: u.active })
                      }
                    >
                      {u.active ? t('adminDeactivate') : t('adminActivate')}
                    </Button>
                  </td>
                </tr>
              ))}
            </tbody>
          </table>
        )}
      </Panel>

      <Panel title={t('newUserTitle')}>
        <form
          className="flex flex-col gap-2"
          onSubmit={(e) => {
            e.preventDefault()
            if (canSubmit) createUser.mutate()
          }}
        >
          <div className="flex gap-2">
            <TextInput
              placeholder={t('userLoginPlaceholder')}
              value={login}
              onChange={(e) => setLogin(e.target.value)}
            />
            <TextInput
              type="password"
              placeholder={t('userPasswordPlaceholder')}
              value={password}
              onChange={(e) => setPassword(e.target.value)}
            />
          </div>
          <div className="flex gap-2">
            <TextInput
              placeholder={t('userFirstPlaceholder')}
              value={first}
              onChange={(e) => setFirst(e.target.value)}
            />
            <TextInput
              placeholder={t('userLastPlaceholder')}
              value={last}
              onChange={(e) => setLast(e.target.value)}
            />
          </div>
          <div className="flex gap-2">
            <TextInput
              type="email"
              placeholder={t('userEmailPlaceholder')}
              value={email}
              onChange={(e) => setEmail(e.target.value)}
            />
            <Button type="submit" disabled={!canSubmit || createUser.isPending}>
              {t('createUser')}
            </Button>
          </div>
          {createUser.isError && (
            <p className="text-fail text-xs">{t('adminSaveError')}</p>
          )}
        </form>
      </Panel>
    </div>
  )
}

/* ------------------------------ Keywords ------------------------------ */
function KeywordsTab({ projectId }: { projectId: string }) {
  const { t } = useT()
  const qc = useQueryClient()
  const [keyword, setKeyword] = useState('')
  const [notes, setNotes] = useState('')

  const keywords = useQuery({
    queryKey: ['keywords', projectId],
    queryFn: () => api.keywords(projectId),
  })

  const createKw = useMutation({
    mutationFn: () =>
      api.createKeyword({
        testProjectID: Number(projectId),
        keyword: keyword.trim(),
        notes: notes.trim(),
      }),
    onSuccess: () => {
      setKeyword('')
      setNotes('')
      qc.invalidateQueries({ queryKey: ['keywords', projectId] })
    },
  })

  const deleteKw = useMutation({
    mutationFn: (id: number) => api.deleteKeyword(id),
    onSuccess: () => qc.invalidateQueries({ queryKey: ['keywords', projectId] }),
  })

  return (
    <Panel title={t('adminTabKeywords')}>
      {keywords.isPending ? (
        <Spinner />
      ) : (keywords.data?.length ?? 0) === 0 ? (
        <EmptyState>{t('noKeywords')}</EmptyState>
      ) : (
        <table className="w-full text-[13px]">
          <thead>
            <tr className="text-mute border-line border-b text-left text-xs uppercase">
              <th className="py-1.5 font-medium">{t('adminColKeyword')}</th>
              <th className="py-1.5 font-medium">{t('adminColNotes')}</th>
              <th className="py-1.5 font-medium">{t('adminColUsage')}</th>
              <th className="py-1.5" />
            </tr>
          </thead>
          <tbody>
            {keywords.data!.map((k) => (
              <tr key={k.id} className="border-line border-b last:border-0">
                <td className="py-1.5 font-medium">{k.keyword}</td>
                <td className="text-mute py-1.5">{k.notes || '—'}</td>
                <td className="text-mute py-1.5 font-mono text-xs">
                  {k.linkedCount}
                </td>
                <td className="py-1.5 text-right">
                  <button
                    className="text-mute hover:text-fail p-0.5"
                    title={t('adminDelete')}
                    aria-label={t('adminDelete')}
                    disabled={deleteKw.isPending}
                    onClick={() => {
                      if (window.confirm(t('confirmDeleteKeyword')(k.keyword)))
                        deleteKw.mutate(k.id)
                    }}
                  >
                    <Trash2 className="size-4" />
                  </button>
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
          if (keyword.trim()) createKw.mutate()
        }}
      >
        <TextInput
          placeholder={t('keywordPlaceholder')}
          value={keyword}
          onChange={(e) => setKeyword(e.target.value)}
          className="w-48 shrink-0"
        />
        <TextInput
          placeholder={t('keywordNotesPlaceholder')}
          value={notes}
          onChange={(e) => setNotes(e.target.value)}
        />
        <Button type="submit" disabled={!keyword.trim() || createKw.isPending}>
          {t('addKeyword')}
        </Button>
      </form>
      {createKw.isError && (
        <p className="text-fail mt-2 text-xs">{t('adminSaveError')}</p>
      )}
    </Panel>
  )
}

/* ------------------------------ Platforms ----------------------------- */
function PlatformsTab({ projectId }: { projectId: string }) {
  const { t } = useT()
  const qc = useQueryClient()
  const [name, setName] = useState('')
  const [notes, setNotes] = useState('')

  const platforms = useQuery({
    queryKey: ['platforms', projectId],
    queryFn: () => api.platforms(projectId),
  })

  const createPlatform = useMutation({
    mutationFn: () =>
      api.createPlatform({
        testProjectID: Number(projectId),
        name: name.trim(),
        notes: notes.trim(),
      }),
    onSuccess: () => {
      setName('')
      setNotes('')
      qc.invalidateQueries({ queryKey: ['platforms', projectId] })
    },
  })

  return (
    <Panel title={t('adminTabPlatforms')}>
      {platforms.isPending ? (
        <Spinner />
      ) : (platforms.data?.length ?? 0) === 0 ? (
        <EmptyState>{t('noPlatforms')}</EmptyState>
      ) : (
        <table className="w-full text-[13px]">
          <thead>
            <tr className="text-mute border-line border-b text-left text-xs uppercase">
              <th className="py-1.5 font-medium">{t('adminColPlatform')}</th>
              <th className="py-1.5 font-medium">{t('adminColNotes')}</th>
              <th className="py-1.5 font-medium">{t('adminColEnabled')}</th>
            </tr>
          </thead>
          <tbody>
            {platforms.data!.map((p) => (
              <tr key={p.id} className="border-line border-b last:border-0">
                <td className="py-1.5 font-medium">{p.name}</td>
                <td className="text-mute py-1.5">{p.notes || '—'}</td>
                <td className="py-1.5">
                  <span className="flex gap-1.5">
                    {p.enable_on_design ? (
                      <span className="bg-accent-soft text-accent rounded px-1.5 py-0.5 text-[11px]">
                        {t('enabledDesign')}
                      </span>
                    ) : null}
                    {p.enable_on_execution ? (
                      <span className="bg-accent-soft text-accent rounded px-1.5 py-0.5 text-[11px]">
                        {t('enabledExecution')}
                      </span>
                    ) : null}
                  </span>
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
          if (name.trim()) createPlatform.mutate()
        }}
      >
        <TextInput
          placeholder={t('platformNamePlaceholder')}
          value={name}
          onChange={(e) => setName(e.target.value)}
          className="w-48 shrink-0"
        />
        <TextInput
          placeholder={t('platformNotesPlaceholder')}
          value={notes}
          onChange={(e) => setNotes(e.target.value)}
        />
        <Button
          type="submit"
          disabled={!name.trim() || createPlatform.isPending}
        >
          {t('addPlatform')}
        </Button>
      </form>
      {createPlatform.isError && (
        <p className="text-fail mt-2 text-xs">{t('adminSaveError')}</p>
      )}
    </Panel>
  )
}

/* ---------------------------- Custom fields --------------------------- */
function CustomFieldsTab({ projectId }: { projectId: string }) {
  const { t } = useT()
  const cfields = useQuery({
    queryKey: ['customFields', projectId],
    queryFn: () => api.customFields(projectId),
  })

  return (
    <Panel title={t('adminTabCustomFields')}>
      <p className="text-mute mb-3 text-xs">{t('cfLegacyHint')}</p>
      {cfields.isPending ? (
        <Spinner />
      ) : (cfields.data?.length ?? 0) === 0 ? (
        <EmptyState>{t('noCustomFields')}</EmptyState>
      ) : (
        <table className="w-full text-[13px]">
          <thead>
            <tr className="text-mute border-line border-b text-left text-xs uppercase">
              <th className="py-1.5 font-medium">{t('adminColName')}</th>
              <th className="py-1.5 font-medium">{t('adminColLabel')}</th>
              <th className="py-1.5 font-medium">{t('adminColType')}</th>
              <th className="py-1.5 font-medium">{t('adminColAppliesTo')}</th>
              <th className="py-1.5 font-medium">{t('adminColEnabled')}</th>
            </tr>
          </thead>
          <tbody>
            {cfields.data!.map((c) => (
              <tr key={c.id} className="border-line border-b last:border-0">
                <td className="py-1.5 font-mono text-xs">{c.name}</td>
                <td className="py-1.5 font-medium">{c.label}</td>
                <td className="text-mute py-1.5">{c.type}</td>
                <td className="text-mute py-1.5">{c.appliesTo || '—'}</td>
                <td className="py-1.5">
                  <span className="flex gap-1.5">
                    {c.enable_on_design ? (
                      <span className="bg-accent-soft text-accent rounded px-1.5 py-0.5 text-[11px]">
                        {t('enabledDesign')}
                      </span>
                    ) : null}
                    {c.enable_on_execution ? (
                      <span className="bg-accent-soft text-accent rounded px-1.5 py-0.5 text-[11px]">
                        {t('enabledExecution')}
                      </span>
                    ) : null}
                  </span>
                </td>
              </tr>
            ))}
          </tbody>
        </table>
      )}
    </Panel>
  )
}

/* ------------------------------- Page --------------------------------- */
export function AdminPage() {
  const { project } = useWorkspace()
  const { t } = useT()
  const [tab, setTab] = useState<Tab>('users')

  const needsProject = tab !== 'users'

  return (
    <div className="mx-auto flex max-w-3xl flex-col gap-4">
      <h1 className="font-display text-xl font-bold tracking-tight">
        {t('navAdmin')}
      </h1>

      <div className="border-line flex gap-1 border-b">
        {TABS.map((tb) => (
          <button
            key={tb.id}
            onClick={() => setTab(tb.id)}
            className={`-mb-px border-b-2 px-3 py-2 text-[13px] font-medium transition-colors ${
              tab === tb.id
                ? 'border-accent text-accent'
                : 'text-mute hover:text-ink border-transparent'
            }`}
          >
            {t(tb.labelKey)}
          </button>
        ))}
      </div>

      {tab === 'users' ? (
        <UsersTab />
      ) : needsProject && !project ? (
        <EmptyState>{t('adminNeedProject')}</EmptyState>
      ) : tab === 'keywords' ? (
        <KeywordsTab projectId={project!.id} />
      ) : tab === 'platforms' ? (
        <PlatformsTab projectId={project!.id} />
      ) : (
        <CustomFieldsTab projectId={project!.id} />
      )}
    </div>
  )
}
