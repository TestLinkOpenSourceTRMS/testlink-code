const BASE = '/lib/api/rest/v3'

export type Verdict = 'p' | 'f' | 'b' | 'n'

export interface User {
  id: number
  login: string
  firstName: string
  lastName: string
}

export interface Suite {
  id: number
  parent_id: number
  name: string
  tcCount: number
}

export interface CaseRow {
  id: string
  name: string
  tc_external_id: string
  latest_version: string
  importance: string
  status: string
}

export interface Step {
  step_number: string
  actions: string
  expected_results: string
  execution_type: string
}

export interface Execution {
  id: string
  status: string
  execution_ts: string
  build_name: string
  tester: string
  notes: string
}

export interface CaseDetail {
  name: string
  suite_id: string
  tcversion_id: string
  version: string
  tc_external_id: string
  summary: string
  preconditions: string
  importance: string
  execution_type: string
  steps: Step[]
  executions: Execution[]
}

export interface PlanSummary {
  planID: number
  name: string
  linked: number
  byStatus: Partial<Record<Verdict, number>>
}

export interface TrendDay {
  day: string
  p: number
  f: number
  b: number
  other: number
}

export interface FlakyItem {
  tcase_id: number
  name: string
  flips: number
  total: number
}

export interface QueueItem {
  tcversion_id: string
  tcase_id: string
  name: string
  tc_external_id: string
  importance: string
  exec_status: string | null
}

export interface Project {
  id: string
  name: string
  prefix: string
}

export interface Plan {
  id: string
  name: string
}

export interface Build {
  id: string
  name: string
  active: string
  is_open: string
}

export function getSession() {
  const raw = localStorage.getItem('tl.session')
  return raw ? (JSON.parse(raw) as { apikey: string; user: User }) : null
}

export function setSession(s: { apikey: string; user: User } | null) {
  if (s) localStorage.setItem('tl.session', JSON.stringify(s))
  else localStorage.removeItem('tl.session')
}

async function request<T>(path: string, init?: RequestInit): Promise<T> {
  const session = getSession()
  const res = await fetch(BASE + path, {
    ...init,
    headers: {
      'Content-Type': 'application/json',
      ...(session ? { Apikey: session.apikey } : {}),
      ...init?.headers,
    },
  })
  if (res.status === 401) {
    setSession(null)
    window.location.hash = '/login'
    throw new Error('Session expired — sign in again')
  }
  if (!res.ok) throw new Error(`API error ${res.status}`)
  return res.json() as Promise<T>
}

export const api = {
  login: (login: string, password: string) =>
    fetch(BASE + '/auth/login', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ login, password }),
    }).then(async (r) => {
      if (!r.ok) throw new Error('Wrong login or password')
      return r.json() as Promise<{ apikey: string; user: User }>
    }),

  projects: () =>
    request<Project[] | { item: Project[] }>('/testprojects').then((r) =>
      Array.isArray(r) ? r : (r.item ?? []),
    ),

  projectPlans: (projectId: string) =>
    request<{ items: Record<string, Plan> | Plan[] | null }>(
      `/testprojects/${projectId}/testplans`,
    ).then((r) =>
      r.items == null
        ? []
        : Array.isArray(r.items)
          ? r.items
          : Object.values(r.items),
    ),

  suites: (projectId: string) =>
    request<{ items: Suite[] }>(`/testprojects/${projectId}/suites`).then(
      (r) => r.items,
    ),

  suiteCases: (suiteId: number) =>
    request<{ items: CaseRow[] }>(`/testsuites/${suiteId}/testcases`).then(
      (r) => r.items,
    ),

  caseDetail: (caseId: string) =>
    request<{ item: CaseDetail }>(`/testcases/${caseId}/detail`).then(
      (r) => r.item,
    ),

  planSummary: (planId: string) =>
    request<PlanSummary>(`/testplans/${planId}/summary`),

  planTrend: (planId: string) =>
    request<{ items: TrendDay[] }>(`/testplans/${planId}/trend`).then(
      (r) => r.items,
    ),

  planFlaky: (planId: string) =>
    request<{ items: FlakyItem[]; analyzed: number }>(
      `/testplans/${planId}/flaky`,
    ),

  planBuilds: (planId: string) =>
    request<{ items: Build[] }>(`/testplans/${planId}/buildsById`).then(
      (r) => r.items,
    ),

  planQueue: (planId: string, buildId: string, page: number, limit = 100) =>
    request<{ items: QueueItem[]; total: number }>(
      `/testplans/${planId}/queue?buildID=${buildId}&page=${page}&limit=${limit}`,
    ),

  planMatrix: (planId: string, page: number, limit = 100, suiteId?: number) =>
    request<{
      total: number
      page: number
      builds: { id: string; name: string }[]
      items: {
        tcversion_id: string
        tcase_id: string
        name: string
        tc_external_id: string
        results: Record<string, string>
      }[]
    }>(
      `/testplans/${planId}/matrix?page=${page}&limit=${limit}` +
        (suiteId ? `&suiteID=${suiteId}` : ''),
    ),

  planMatrixBySuite: (planId: string) =>
    request<{
      builds: { id: string; name: string }[]
      items: {
        suite_id: number
        name: string
        linked: number
        cells: Record<string, { p: number; f: number; b: number }>
      }[]
    }>(`/testplans/${planId}/matrixBySuite`),

  createPlan: (input: { name: string; testProjectID: number; notes: string }) =>
    request<{ status: string; id: number }>('/testplans', {
      method: 'POST',
      body: JSON.stringify({ ...input, active: 1, is_public: 1 }),
    }),

  createBuild: (input: { name: string; testplan: number; notes: string }) =>
    request<{ status: string; id: number }>('/builds', {
      method: 'POST',
      body: JSON.stringify({ ...input, active: 1, is_public: 1 }),
    }),

  createSuite: (input: {
    name: string
    testProjectID: number
    parentID: number
  }) =>
    request<{ status: string; id: number }>('/testsuites', {
      method: 'POST',
      body: JSON.stringify({ ...input, notes: '', order: 0 }),
    }),

  createCase: (input: {
    name: string
    suiteId: number
    projectId: number
  }) =>
    request<{ status: string; id: number }>('/testcases', {
      method: 'POST',
      body: JSON.stringify({
        name: input.name,
        testSuite: { id: input.suiteId },
        testProject: { id: input.projectId },
        summary: '',
        preconditions: '',
        order: 0,
        authorLogin: getSession()?.user.login ?? 'admin',
        importance: { name: 'medium' },
        executionType: { name: 'manual' },
      }),
    }),

  updateCase: (
    caseId: string,
    input: {
      name?: string
      summary?: string
      preconditions?: string
      steps?: { actions: string; expected_results: string; execution_type: number }[]
    },
  ) =>
    request<{ status: string; id: number }>(`/testcases/${caseId}/update`, {
      method: 'PUT',
      body: JSON.stringify(input),
    }),

  linkToPlan: (planId: string, tcaseIDs: number[]) =>
    request<{ status: string; linked: number; skipped: number }>(
      `/testplans/${planId}/link`,
      { method: 'POST', body: JSON.stringify({ tcaseIDs }) },
    ),

  recordExecution: (input: {
    testPlanID: number
    buildID: number
    testCaseExternalID: string
    statusCode: string
    notes: string
  }) =>
    request<{ status: string; id: number }>('/executions', {
      method: 'POST',
      body: JSON.stringify({
        ...input,
        platformID: 0,
        executionType: '1',
      }),
    }),
}
