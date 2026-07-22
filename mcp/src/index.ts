#!/usr/bin/env node
/**
 * testlink-mcp — MCP server exposing the TestLink REST v3 API as agent-friendly tools.
 *
 * Env:
 *   TESTLINK_URL     Base URL of the TestLink instance (default http://localhost:8090)
 *   TESTLINK_APIKEY  API key sent as the `Apikey` header (required)
 */
import { McpServer } from "@modelcontextprotocol/sdk/server/mcp.js";
import { StdioServerTransport } from "@modelcontextprotocol/sdk/server/stdio.js";
import { z } from "zod";

// ---------------------------------------------------------------------------
// Configuration (fail fast on missing API key)
// ---------------------------------------------------------------------------

const BASE_URL = (process.env.TESTLINK_URL ?? "http://localhost:8090").replace(/\/+$/, "");
const API_KEY = process.env.TESTLINK_APIKEY;

if (!API_KEY) {
  console.error(
    "testlink-mcp: TESTLINK_APIKEY environment variable is not set.\n" +
      "Set it to a TestLink API key (user profile > API interface, or the SPA login endpoint) and restart.\n" +
      "Optionally set TESTLINK_URL (default http://localhost:8090)."
  );
  process.exit(1);
}

const API = `${BASE_URL}/lib/api/rest/v3`;

// ---------------------------------------------------------------------------
// HTTP helper
// ---------------------------------------------------------------------------

type Query = Record<string, string | number | undefined>;

class TestLinkError extends Error {
  constructor(message: string) {
    super(message);
    this.name = "TestLinkError";
  }
}

async function tl(
  method: "GET" | "POST" | "PUT",
  path: string,
  opts: { query?: Query; body?: unknown } = {}
): Promise<any> {
  const url = new URL(API + path);
  for (const [k, v] of Object.entries(opts.query ?? {})) {
    if (v !== undefined && v !== "") url.searchParams.set(k, String(v));
  }

  let res: Response;
  try {
    res = await fetch(url, {
      method,
      headers: {
        Apikey: API_KEY!,
        ...(opts.body !== undefined ? { "Content-Type": "application/json" } : {}),
      },
      body: opts.body !== undefined ? JSON.stringify(opts.body) : undefined,
    });
  } catch (e) {
    throw new TestLinkError(
      `Cannot reach TestLink at ${url.origin} (${e instanceof Error ? e.message : String(e)}). ` +
        "Check TESTLINK_URL and that the server is running."
    );
  }

  const text = await res.text();
  if (!res.ok) {
    throw new TestLinkError(`HTTP ${res.status} ${res.statusText} on ${method} ${path}: ${text.slice(0, 2000)}`);
  }
  try {
    return JSON.parse(text);
  } catch {
    // e.g. a PHP fatal error page returned with HTTP 200
    throw new TestLinkError(`Non-JSON response from ${method} ${path}: ${text.slice(0, 2000)}`);
  }
}

// ---------------------------------------------------------------------------
// Tool result helpers
// ---------------------------------------------------------------------------

type ToolResult = {
  content: Array<{ type: "text"; text: string }>;
  isError?: boolean;
};

function ok(data: unknown): ToolResult {
  return { content: [{ type: "text", text: JSON.stringify(data, null, 2) }] };
}

function fail(message: string): ToolResult {
  return { isError: true, content: [{ type: "text", text: message }] };
}

async function run(fn: () => Promise<unknown>): Promise<ToolResult> {
  try {
    return ok(await fn());
  } catch (e) {
    return fail(e instanceof Error ? e.message : String(e));
  }
}

/** TestLink sometimes returns `items` as an array, sometimes as an id-keyed map. */
function itemsAsArray(payload: any): any[] {
  const items = payload?.items;
  if (Array.isArray(items)) return items;
  if (items && typeof items === "object") return Object.values(items);
  return [];
}

const IMPORTANCE_CODE: Record<string, number> = { low: 1, medium: 2, high: 3 };

const STATUS_LABEL: Record<string, string> = {
  p: "passed",
  f: "failed",
  b: "blocked",
  n: "not_run",
};

function labelStatusCounts(byStatus: Record<string, number> | undefined): Record<string, number> {
  const out: Record<string, number> = {};
  for (const [code, count] of Object.entries(byStatus ?? {})) {
    out[STATUS_LABEL[code] ?? code] = count;
  }
  return out;
}

// ---------------------------------------------------------------------------
// Server + tools
// ---------------------------------------------------------------------------

const server = new McpServer({ name: "testlink-mcp", version: "1.0.0" });

const stepSchema = z.object({
  actions: z.string().describe("What the tester does in this step"),
  expected_results: z.string().describe("What should happen if the step passes"),
  execution_type: z
    .union([z.literal(1), z.literal(2)])
    .optional()
    .describe("1 = manual (default), 2 = automated"),
});

// ---- Browse -----------------------------------------------------------------

server.registerTool(
  "list_projects",
  {
    title: "List test projects",
    description:
      "List all TestLink test projects with their id, name and test-case ID prefix. " +
      "Start here to find the projectId used by most other tools.",
    inputSchema: {},
  },
  async () =>
    run(async () => {
      const projects = (await tl("GET", "/testprojects")) as any[];
      return projects.map((p) => ({
        id: Number(p.id),
        name: p.name,
        prefix: p.prefix,
        active: p.active === "1" || p.active === 1,
        testCaseCount: Number(p.tc_counter ?? 0),
      }));
    })
);

server.registerTool(
  "list_plans",
  {
    title: "List test plans",
    description: "List the test plans of a project (id, name, notes, active).",
    inputSchema: {
      projectId: z.number().int().describe("Test project id (from list_projects)"),
    },
  },
  async ({ projectId }) =>
    run(async () => {
      const payload = await tl("GET", `/testprojects/${projectId}/testplans`);
      return itemsAsArray(payload).map((p: any) => ({
        id: Number(p.id),
        name: p.name,
        notes: p.notes,
        active: p.active === "1" || p.active === 1,
      }));
    })
);

server.registerTool(
  "list_builds",
  {
    title: "List builds of a test plan",
    description: "List the builds of a test plan, newest first (id, name, notes, active, created).",
    inputSchema: {
      planId: z.number().int().describe("Test plan id (from list_plans)"),
    },
  },
  async ({ planId }) =>
    run(async () => {
      const payload = await tl("GET", `/testplans/${planId}/buildsById`);
      return itemsAsArray(payload).map((b: any) => ({
        id: Number(b.id),
        name: b.name,
        notes: b.notes,
        active: b.active === "1" || b.active === 1,
        open: b.is_open === "1" || b.is_open === 1,
        created: b.creation_ts,
      }));
    })
);

server.registerTool(
  "list_suites",
  {
    title: "List test suites",
    description:
      "List all test suites of a project as a flat list with parent_id links (build the tree from parent_id). " +
      "Each row includes tcCount, the number of test cases in the suite.",
    inputSchema: {
      projectId: z.number().int().describe("Test project id"),
    },
  },
  async ({ projectId }) =>
    run(async () => {
      const payload = await tl("GET", `/testprojects/${projectId}/suites`);
      return itemsAsArray(payload);
    })
);

server.registerTool(
  "list_suite_cases",
  {
    title: "List test cases in a suite",
    description:
      "List the test cases directly inside one test suite (id, name, external id, importance, latest version). " +
      "Use get_case for full detail on a single case.",
    inputSchema: {
      suiteId: z.number().int().describe("Test suite id (from list_suites)"),
    },
  },
  async ({ suiteId }) =>
    run(async () => {
      const payload = await tl("GET", `/testsuites/${suiteId}/testcases`);
      return itemsAsArray(payload);
    })
);

server.registerTool(
  "search_cases",
  {
    title: "Search test cases",
    description:
      "Full-text search for test cases by name within a project. Returns matching cases with their " +
      "internal id (tcase_id), suite name and external id.",
    inputSchema: {
      projectId: z.number().int().describe("Test project id"),
      query: z.string().min(1).describe("Search text, matched against test case names"),
    },
  },
  async ({ projectId, query }) =>
    run(async () => {
      const payload = await tl("GET", `/testprojects/${projectId}/search`, { query: { q: query } });
      return itemsAsArray(payload);
    })
);

server.registerTool(
  "get_case",
  {
    title: "Get test case detail",
    description:
      "Full detail of one test case: summary, preconditions, steps, and its most recent executions " +
      "(verdict, build, tester, notes, attachments). Takes the internal case id (tcase_id), not the external 'PFX-123' id.",
    inputSchema: {
      caseId: z.number().int().describe("Internal test case id (e.g. tcase_id from search_cases / list_suite_cases)"),
      maxExecutions: z
        .number()
        .int()
        .min(0)
        .max(100)
        .optional()
        .describe("How many recent executions to include (default 10)"),
    },
  },
  async ({ caseId, maxExecutions }) =>
    run(async () => {
      const payload = await tl("GET", `/testcases/${caseId}/detail`);
      const item = payload?.item ?? payload;
      const executions = Array.isArray(item?.executions) ? item.executions : [];
      const limit = maxExecutions ?? 10;
      return {
        ...item,
        totalExecutions: executions.length,
        executions: executions.slice(0, limit).map((e: any) => ({
          ...e,
          statusLabel: STATUS_LABEL[e.status] ?? e.status,
        })),
      };
    })
);

// ---- Author -----------------------------------------------------------------

server.registerTool(
  "create_suite",
  {
    title: "Create a test suite",
    description:
      "Create a new test suite in a project. Omit parentSuiteId to create it at the top level; " +
      "pass an existing suite id to nest it. Returns the new suite id.",
    inputSchema: {
      projectId: z.number().int().describe("Test project id"),
      name: z.string().min(1).describe("Suite name"),
      parentSuiteId: z
        .number()
        .int()
        .optional()
        .describe("Parent suite id; omit for a top-level suite"),
      notes: z.string().optional().describe("Optional description of the suite"),
      order: z.number().int().optional().describe("Sort order within the parent (default 0)"),
    },
  },
  async ({ projectId, name, parentSuiteId, notes, order }) =>
    run(async () => {
      const payload = await tl("POST", "/testsuites", {
        body: {
          name,
          testProjectID: projectId,
          parentID: parentSuiteId ?? projectId,
          notes: notes ?? "",
          order: order ?? 0,
        },
      });
      return { id: Number(payload.id), name, projectId, parentSuiteId: parentSuiteId ?? null };
    })
);

server.registerTool(
  "create_case",
  {
    title: "Create a test case",
    description:
      "Create a new test case in a suite, optionally with its steps in the same call " +
      "(steps are applied via a follow-up update). Returns the new case id; fetch get_case for the " +
      "assigned external id and version.",
    inputSchema: {
      projectId: z.number().int().describe("Test project id"),
      suiteId: z.number().int().describe("Test suite the case goes into"),
      name: z.string().min(1).describe("Test case title"),
      summary: z.string().optional().describe("What the case verifies (HTML or plain text)"),
      preconditions: z.string().optional().describe("Required state before executing"),
      importance: z.enum(["low", "medium", "high"]).optional().describe("Priority (default medium)"),
      executionType: z.enum(["manual", "automatic"]).optional().describe("Default manual"),
      authorLogin: z.string().optional().describe("TestLink login of the author (default 'admin')"),
      order: z.number().int().optional().describe("Sort order within the suite"),
      steps: z
        .array(stepSchema)
        .optional()
        .describe("Ordered execution steps; if given, they are set right after creation"),
    },
  },
  async ({ projectId, suiteId, name, summary, preconditions, importance, executionType, authorLogin, order, steps }) =>
    run(async () => {
      const created = await tl("POST", "/testcases", {
        body: {
          name,
          testSuite: { id: suiteId },
          testProject: { id: projectId },
          summary: summary ?? "",
          preconditions: preconditions ?? "",
          order: order ?? 0,
          authorLogin: authorLogin ?? "admin",
          importance: { name: importance ?? "medium" },
          executionType: { name: executionType ?? "manual" },
        },
      });
      const id = Number(created.id);
      let stepsSet = 0;
      if (steps && steps.length > 0) {
        const upd = await tl("PUT", `/testcases/${id}/update`, {
          body: {
            steps: steps.map((s) => ({
              actions: s.actions,
              expected_results: s.expected_results,
              execution_type: s.execution_type ?? 1,
            })),
          },
        });
        stepsSet = Number(upd.steps ?? steps.length);
      }
      return { id, name, suiteId, stepsSet };
    })
);

server.registerTool(
  "update_case",
  {
    title: "Update a test case",
    description:
      "Update fields of an existing test case. Only the fields you pass are changed. " +
      "WARNING: passing `steps` REPLACES the entire step list — include every step you want to keep " +
      "(read the current steps with get_case first).",
    inputSchema: {
      caseId: z.number().int().describe("Internal test case id"),
      name: z.string().optional().describe("New title"),
      summary: z.string().optional().describe("New summary"),
      preconditions: z.string().optional().describe("New preconditions"),
      importance: z.enum(["low", "medium", "high"]).optional().describe("New priority"),
      steps: z
        .array(stepSchema)
        .optional()
        .describe("FULL replacement step list (replaces all existing steps)"),
    },
  },
  async ({ caseId, name, summary, preconditions, importance, steps }) =>
    run(async () => {
      const body: Record<string, unknown> = {};
      if (name !== undefined) body.name = name;
      if (summary !== undefined) body.summary = summary;
      if (preconditions !== undefined) body.preconditions = preconditions;
      if (importance !== undefined) body.importance = IMPORTANCE_CODE[importance];
      if (steps !== undefined) {
        body.steps = steps.map((s) => ({
          actions: s.actions,
          expected_results: s.expected_results,
          execution_type: s.execution_type ?? 1,
        }));
      }
      if (Object.keys(body).length === 0) {
        throw new TestLinkError("Nothing to update: pass at least one of name/summary/preconditions/importance/steps.");
      }
      return await tl("PUT", `/testcases/${caseId}/update`, { body });
    })
);

// ---- Plan runs --------------------------------------------------------------

server.registerTool(
  "create_plan",
  {
    title: "Create a test plan",
    description: "Create a new active, public test plan in a project. Returns the new plan id.",
    inputSchema: {
      projectId: z.number().int().describe("Test project id"),
      name: z.string().min(1).describe("Plan name"),
      notes: z.string().optional().describe("Optional plan description"),
    },
  },
  async ({ projectId, name, notes }) =>
    run(async () => {
      const payload = await tl("POST", "/testplans", {
        body: { name, testProjectID: projectId, notes: notes ?? "", active: 1, is_public: 1 },
      });
      return { id: Number(payload.id), name, projectId };
    })
);

server.registerTool(
  "create_build",
  {
    title: "Create a build",
    description:
      "Create a new active build under a test plan. Executions are recorded against a build. Returns the build id.",
    inputSchema: {
      planId: z.number().int().describe("Test plan id"),
      name: z.string().min(1).describe("Build name, e.g. a version or CI run id"),
      notes: z.string().optional().describe("Optional build notes"),
    },
  },
  async ({ planId, name, notes }) =>
    run(async () => {
      const payload = await tl("POST", "/builds", {
        body: { name, testplan: planId, notes: notes ?? "", active: 1, is_public: 1 },
      });
      return { id: Number(payload.id), name, planId };
    })
);

server.registerTool(
  "link_cases_to_plan",
  {
    title: "Link test cases to a plan",
    description:
      "Add test cases (by internal case id) to a test plan so they can be executed. " +
      "Already-linked cases are skipped, not duplicated. Returns linked/skipped counts.",
    inputSchema: {
      planId: z.number().int().describe("Test plan id"),
      caseIds: z.array(z.number().int()).min(1).describe("Internal test case ids to link"),
    },
  },
  async ({ planId, caseIds }) =>
    run(async () => await tl("POST", `/testplans/${planId}/link`, { body: { tcaseIDs: caseIds } }))
);

server.registerTool(
  "record_execution",
  {
    title: "Record a test execution",
    description:
      "Record a verdict for a test case in a plan+build. The case is addressed by its EXTERNAL id " +
      "(e.g. 'P8T-123' — project prefix, dash, number; see tc_external_id from search/list tools). " +
      "Verdicts: p = pass, f = fail, b = blocked. Include evidence in notes (what was observed, logs, env).",
    inputSchema: {
      planId: z.number().int().describe("Test plan id"),
      buildId: z.number().int().describe("Build id (from list_builds / create_build)"),
      caseExternalId: z
        .string()
        .min(1)
        .describe("External test case id including project prefix, e.g. 'P8T-10006'"),
      verdict: z.enum(["p", "f", "b"]).describe("p = pass, f = fail, b = blocked"),
      notes: z.string().optional().describe("Evidence / observations for this run"),
    },
  },
  async ({ planId, buildId, caseExternalId, verdict, notes }) =>
    run(async () => {
      const payload = await tl("POST", "/executions", {
        body: {
          testPlanID: planId,
          buildID: buildId,
          platformID: 0,
          testCaseExternalID: caseExternalId,
          statusCode: verdict,
          notes: notes ?? "",
          executionType: "1",
        },
      });
      return {
        executionId: Number(payload.id),
        caseExternalId,
        verdict: STATUS_LABEL[verdict] ?? verdict,
        planId,
        buildId,
      };
    })
);

// ---- Reporting --------------------------------------------------------------

server.registerTool(
  "plan_status",
  {
    title: "Test plan status overview",
    description:
      "One readable health summary for a test plan: total linked cases, verdict totals " +
      "(passed/failed/blocked/not_run) and a per-build breakdown.",
    inputSchema: {
      planId: z.number().int().describe("Test plan id"),
    },
  },
  async ({ planId }) =>
    run(async () => {
      const [summary, byBuild] = await Promise.all([
        tl("GET", `/testplans/${planId}/summary`),
        tl("GET", `/testplans/${planId}/byBuild`),
      ]);
      return {
        planId,
        name: summary.name,
        linkedCases: summary.linked,
        totals: labelStatusCounts(summary.byStatus),
        byBuild: itemsAsArray(byBuild).map((b: any) => ({
          buildId: Number(b.build_id),
          name: b.name,
          linked: b.linked,
          passed: b.p ?? 0,
          failed: b.f ?? 0,
          blocked: b.b ?? 0,
          other: b.other ?? 0,
        })),
      };
    })
);

server.registerTool(
  "plan_queue",
  {
    title: "Execution work queue",
    description:
      "Paged list of test cases to execute in a plan for a given build, with current status and assignee. " +
      "Use assignedTo (a TestLink user id) to see one tester's queue.",
    inputSchema: {
      planId: z.number().int().describe("Test plan id"),
      buildId: z.number().int().describe("Build id"),
      page: z.number().int().min(1).optional().describe("Page number (default 1)"),
      limit: z.number().int().min(1).max(200).optional().describe("Rows per page (default 20)"),
      assignedTo: z.number().int().optional().describe("Filter by assignee user id (see list_users)"),
    },
  },
  async ({ planId, buildId, page, limit, assignedTo }) =>
    run(async () => {
      const payload = await tl("GET", `/testplans/${planId}/queue`, {
        query: { buildID: buildId, page: page ?? 1, limit: limit ?? 20, assignedTo },
      });
      return {
        total: payload.total,
        page: payload.page,
        limit: payload.limit,
        items: itemsAsArray(payload).map((r: any) => ({
          ...r,
          exec_status_label: STATUS_LABEL[r.exec_status] ?? r.exec_status,
        })),
      };
    })
);

server.registerTool(
  "plan_flaky",
  {
    title: "Flaky test ranking",
    description:
      "Rank test cases in a plan by verdict flips (pass<->fail transitions) over the last N days — " +
      "the top entries are the flakiest. Keep `days` small (default 7) on very large plans: wide windows " +
      "can exhaust server memory.",
    inputSchema: {
      planId: z.number().int().describe("Test plan id"),
      days: z.number().int().min(1).max(365).optional().describe("Lookback window in days (default 7)"),
    },
  },
  async ({ planId, days }) =>
    run(async () => {
      const payload = await tl("GET", `/testplans/${planId}/flaky`, { query: { days: days ?? 7 } });
      return { analyzed: payload.analyzed, days: days ?? 7, items: itemsAsArray(payload) };
    })
);

// ---- People -----------------------------------------------------------------

server.registerTool(
  "assign_cases",
  {
    title: "Assign test cases to testers",
    description:
      "Assign (or unassign) linked test cases to testers for a plan+build. Each assignment pairs an " +
      "internal case id with a user id from list_users; userId 0 removes the assignment. " +
      "Returns assigned/removed/skipped counts.",
    inputSchema: {
      planId: z.number().int().describe("Test plan id"),
      buildId: z.number().int().describe("Build id"),
      assignments: z
        .array(
          z.object({
            caseId: z.number().int().describe("Internal test case id"),
            userId: z.number().int().describe("TestLink user id; 0 unassigns"),
          })
        )
        .min(1)
        .describe("Case/user pairs to apply"),
    },
  },
  async ({ planId, buildId, assignments }) =>
    run(async () =>
      tl("POST", `/testplans/${planId}/assign`, {
        body: {
          buildID: buildId,
          items: assignments.map((a) => ({ tcaseID: a.caseId, userID: a.userId })),
        },
      })
    )
);

server.registerTool(
  "list_users",
  {
    title: "List TestLink users",
    description: "List TestLink users (id, login, first/last name) for assignments and queue filters.",
    inputSchema: {},
  },
  async () =>
    run(async () => {
      const payload = await tl("GET", "/users");
      return itemsAsArray(payload).map((u: any) => ({
        id: Number(u.id),
        login: u.login,
        name: [u.first, u.last].filter(Boolean).join(" "),
      }));
    })
);

// ---------------------------------------------------------------------------
// Start
// ---------------------------------------------------------------------------

async function main() {
  const transport = new StdioServerTransport();
  await server.connect(transport);
  console.error(`testlink-mcp: connected (backend ${API})`);
}

main().catch((e) => {
  console.error("testlink-mcp: fatal:", e);
  process.exit(1);
});
