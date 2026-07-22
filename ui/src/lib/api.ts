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

export interface AttachmentRow {
  id: string
  file_name: string
  file_size: string
}

export interface Execution {
  id: string
  status: string
  execution_ts: string
  build_name: string
  tester: string
  notes: string
  attachments?: AttachmentRow[]
}

export interface SearchHit {
  tcase_id: string
  name: string
  suite_name: string
  tc_external_id: string
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
  assigned_to: string | null
  assigned_login: string | null
}

export interface UserRow {
  id: string
  login: string
  first: string
  last: string
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

export interface ReqSpec {
  id: number
  parent_id: number
  name: string
  doc_id: string
  reqCount: number
}

export interface ReqRow {
  id: number
  name: string
  req_doc_id: string
  version: string
  status: string
  coverageCount: number
}

export interface ReqCoverageCase {
  tcase_id: string
  name: string
  tc_external_id: string
}

export interface ReqDetail {
  id: number
  name: string
  srs_id: string
  req_doc_id: string
  version: string
  scope: string
  status: string
  expected_coverage: string
  coverage: ReqCoverageCase[]
}

export interface ReqPlanCoverage {
  req_id: number
  req_doc_id: string
  name: string
  inPlan: number
  covered: number
  p: number
  f: number
  b: number
  n: number
}

export interface AdminUser {
  id: string
  login: string
  first: string
  last: string
  email: string
  role_id: number
  role: string | null
  active: number
}

export interface Keyword {
  id: number
  keyword: string
  notes: string
  linkedCount: number
}

export interface Platform {
  id: number
  name: string
  notes: string
  enable_on_design: number
  enable_on_execution: number
  is_open: number
  linked_count: number
}

export interface CustomField {
  id: number
  name: string
  label: string
  type: string
  appliesTo: string
  active: number
  enable_on_design: number
  enable_on_execution: number
}

export interface Milestone {
  id: number
  name: string
  target_date: string
  start_date: string | null
  a: number
  b: number
  c: number
  linked: number
  executed: number
  passed: number
  executedPct: number
  passPct: number
}

export interface KeywordReportRow {
  keyword_id: number
  keyword: string
  linked: number
  p: number
  f: number
  b: number
  n: number
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

  planQueue: (
    planId: string,
    buildId: string,
    page: number,
    limit = 100,
    assignedTo = '',
  ) =>
    request<{ items: QueueItem[]; total: number }>(
      `/testplans/${planId}/queue?buildID=${buildId}&page=${page}&limit=${limit}` +
        (assignedTo ? `&assignedTo=${assignedTo}` : ''),
    ),

  users: () =>
    request<{ items: UserRow[] }>('/users').then((r) => r.items),

  assignCases: (
    planId: string,
    buildId: number,
    items: { tcaseID: number; userID: number }[],
  ) =>
    request<{ status: string; assigned: number }>(
      `/testplans/${planId}/assign`,
      { method: 'POST', body: JSON.stringify({ buildID: buildId, items }) },
    ),

  planByTester: (planId: string, buildId = '') =>
    request<{
      items: { login: string; p: number; f: number; b: number; other: number }[]
    }>(
      `/testplans/${planId}/byTester` + (buildId ? `?buildID=${buildId}` : ''),
    ).then((r) => r.items),

  planMilestones: (planId: string) =>
    request<{ items: Milestone[] }>(
      `/testplans/${planId}/milestones`,
    ).then((r) => r.items),

  createMilestone: (input: {
    testplanID: number
    name: string
    target_date: string
    start_date?: string
    A?: number
    B?: number
    C?: number
  }) =>
    request<{ status: string; id: number }>('/milestones', {
      method: 'POST',
      body: JSON.stringify(input),
    }),

  deleteMilestone: (id: number) =>
    request<{ status: string; id: number }>(`/milestones/${id}`, {
      method: 'DELETE',
    }),

  planByKeyword: (planId: string) =>
    request<{ items: KeywordReportRow[] }>(
      `/testplans/${planId}/byKeyword`,
    ).then((r) => r.items),

  planByBuild: (planId: string) =>
    request<{
      items: {
        build_id: number
        name: string
        linked: number
        p: number
        f: number
        b: number
      }[]
    }>(`/testplans/${planId}/byBuild`).then((r) => r.items),

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

  search: (projectId: string, q: string) =>
    request<{ items: SearchHit[] }>(
      `/testprojects/${projectId}/search?q=${encodeURIComponent(q)}`,
    ).then((r) => r.items),

  uploadAttachment: (executionId: number, file: File) => {
    const fd = new FormData()
    fd.append('file', file)
    fd.append('title', file.name)
    return fetch(`${BASE}/executions/${executionId}/attachments`, {
      method: 'POST',
      headers: { Apikey: getSession()?.apikey ?? '' },
      body: fd,
    }).then((r) => {
      if (!r.ok) throw new Error('upload failed')
      return r.json() as Promise<{ status: string }>
    })
  },

  /** fetch with auth header, hand back an object URL for viewing */
  attachmentUrl: (attachmentId: string) =>
    fetch(`${BASE}/attachments/${attachmentId}`, {
      headers: { Apikey: getSession()?.apikey ?? '' },
    })
      .then((r) => r.blob())
      .then((b) => URL.createObjectURL(b)),

  /**
   * fetch any authenticated endpoint (HTML documents, XML export)
   * as a blob and hand back an object URL — same pattern as
   * attachmentUrl, for arbitrary paths
   */
  blobUrl: (path: string) =>
    fetch(BASE + path, {
      headers: { Apikey: getSession()?.apikey ?? '' },
    }).then(async (r) => {
      if (!r.ok) throw new Error(`API error ${r.status}`)
      return URL.createObjectURL(await r.blob())
    }),

  /** POST raw TestLink-format XML into a suite; returns import counts */
  importSuiteXml: (suiteId: number, xml: string) =>
    fetch(`${BASE}/testsuites/${suiteId}/xml`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/xml',
        Apikey: getSession()?.apikey ?? '',
      },
      body: xml,
    }).then(async (r) => {
      const body = (await r.json()) as {
        status: string
        created: number
        skippedDuplicates: number
        errors: string[]
        message?: string
      }
      if (!r.ok) throw new Error(body.message ?? `API error ${r.status}`)
      return body
    }),

  createProject: (name: string, prefix: string) =>
    request<{ status: string; id: number }>('/testprojects', {
      method: 'POST',
      body: JSON.stringify({
        name,
        prefix,
        notes: '',
        active: 1,
        is_public: 1,
        options: {
          requirementsEnabled: 0,
          testPriorityEnabled: 1,
          automationEnabled: 1,
          inventoryEnabled: 0,
        },
      }),
    }),

  updateBuild: (buildId: string, patch: { is_open?: number; active?: number }) =>
    request<{ status: string }>(`/builds/${buildId}`, {
      method: 'PUT',
      body: JSON.stringify(patch),
    }),

  reqSpecs: (projectId: string) =>
    request<{ items: ReqSpec[] }>(`/testprojects/${projectId}/reqspecs`).then(
      (r) => r.items,
    ),

  createReqSpec: (input: {
    testProjectID: number
    parentID?: number
    docID: string
    title: string
  }) =>
    request<{ status: string; id: number }>('/reqspecs', {
      method: 'POST',
      body: JSON.stringify(input),
    }),

  specRequirements: (specId: number) =>
    request<{ items: ReqRow[] }>(`/reqspecs/${specId}/requirements`).then(
      (r) => r.items,
    ),

  createRequirement: (input: {
    reqSpecID: number
    docID: string
    title: string
  }) =>
    request<{ status: string; id: number; versionID: number }>(
      '/requirements',
      { method: 'POST', body: JSON.stringify(input) },
    ),

  requirementDetail: (reqId: number) =>
    request<{ item: ReqDetail }>(`/requirements/${reqId}/detail`).then(
      (r) => r.item,
    ),

  addReqCoverage: (reqId: number, tcaseIDs: number[]) =>
    request<{ status: string; linked: number; skipped: number }>(
      `/requirements/${reqId}/coverage`,
      { method: 'POST', body: JSON.stringify({ tcaseIDs }) },
    ),

  removeReqCoverage: (reqId: number, tcaseId: number) =>
    request<{ status: string; removed: number }>(
      `/requirements/${reqId}/coverage/${tcaseId}`,
      { method: 'DELETE' },
    ),

  planReqCoverage: (planId: string) =>
    request<{ items: ReqPlanCoverage[] }>(
      `/testplans/${planId}/reqCoverage`,
    ).then((r) => r.items),

  // ---- admin module ----
  adminUsers: () =>
    request<{ items: AdminUser[] }>('/users').then((r) => r.items),

  createUser: (input: {
    login: string
    password: string
    firstName: string
    lastName: string
    email: string
    roleID?: number
  }) =>
    request<{ status: string; id: number; login: string }>('/users', {
      method: 'POST',
      body: JSON.stringify(input),
    }),

  setUserActive: (id: string, active: number) =>
    request<{ status: string; id: number; active: number }>(`/users/${id}`, {
      method: 'PUT',
      body: JSON.stringify({ active }),
    }),

  keywords: (projectId: string) =>
    request<{ items: Keyword[] }>(
      `/testprojects/${projectId}/keywords`,
    ).then((r) => r.items),

  createKeyword: (input: {
    testProjectID: number
    keyword: string
    notes: string
  }) =>
    request<{ status: string; id: number }>('/keywords', {
      method: 'POST',
      body: JSON.stringify(input),
    }),

  deleteKeyword: (id: number) =>
    request<{ status: string; id: number }>(`/keywords/${id}`, {
      method: 'DELETE',
    }),

  platforms: (projectId: string) =>
    request<{ items: Platform[] }>(
      `/testprojects/${projectId}/platforms`,
    ).then((r) => r.items),

  createPlatform: (input: {
    testProjectID: number
    name: string
    notes: string
  }) =>
    request<{ status: string; id: number }>('/platforms', {
      method: 'POST',
      body: JSON.stringify(input),
    }),

  customFields: (projectId: string) =>
    request<{ items: CustomField[] }>(
      `/testprojects/${projectId}/customfields`,
    ).then((r) => r.items),

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
