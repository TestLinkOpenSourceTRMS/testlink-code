# testlink-mcp

An MCP (Model Context Protocol) server that exposes the TestLink REST v3 API
(`/lib/api/rest/v3`) as tools, so Claude Code and other MCP clients can operate
TestLink: browse and search test cases, author cases with steps, plan runs,
record verdicts, and report on plan health.

Node.js + TypeScript, built on `@modelcontextprotocol/sdk` (`McpServer` +
`StdioServerTransport`), with zod input schemas on every tool.

## Setup

```sh
cd mcp
npm install
npm run build        # compiles src/ -> dist/ with tsc
```

Requires Node.js 18+ (tested with Node 24).

### Environment variables

| Variable          | Required | Default                 | Meaning                                   |
| ----------------- | -------- | ----------------------- | ----------------------------------------- |
| `TESTLINK_URL`    | no       | `http://localhost:8090` | Base URL of the TestLink instance         |
| `TESTLINK_APIKEY` | **yes**  | —                       | API key sent as the `Apikey` HTTP header  |

The server fails fast at startup with a clear message if `TESTLINK_APIKEY` is
not set.

### Getting an API key

Either of:

- **SPA login endpoint** — `POST {TESTLINK_URL}/lib/api/rest/v3/auth/login`
  with `{"login": "...", "password": "..."}` returns
  `{"status":"ok","apikey":"...", "user":{...}}` (a key is minted automatically
  if the user has none yet):

  ```sh
  curl -s -X POST http://localhost:8090/lib/api/rest/v3/auth/login \
    -H 'Content-Type: application/json' \
    -d '{"login":"admin","password":"<password>"}'
  ```

- **User profile** — in the TestLink web UI: *My Settings → API interface →
  Generate a new key*.

## Registering with Claude Code

The repository root contains a project-level `.mcp.json` that Claude Code
auto-detects (you will be asked to approve it on first use):

```json
{
  "mcpServers": {
    "testlink": {
      "type": "stdio",
      "command": "node",
      "args": ["mcp/dist/index.js"],
      "env": {
        "TESTLINK_URL": "${TESTLINK_URL:-http://localhost:8090}",
        "TESTLINK_APIKEY": "${TESTLINK_APIKEY}"
      }
    }
  }
}
```

`.mcp.json` supports `${VAR}` / `${VAR:-default}` environment-variable
expansion in `command`, `args`, and `env`, so export the key in the shell you
launch Claude Code from:

```sh
export TESTLINK_APIKEY=<your key>       # e.g. deadbeefcafebabe0123456789abcde for the local dev instance
claude
```

Alternatively, register it manually (project scope shown):

```sh
claude mcp add testlink --scope project \
  --env TESTLINK_URL='${TESTLINK_URL:-http://localhost:8090}' \
  --env TESTLINK_APIKEY='${TESTLINK_APIKEY}' \
  -- node mcp/dist/index.js
```

Check the connection inside Claude Code with `/mcp`.

## Tools

| Tool                 | What it does                                                                  |
| -------------------- | ----------------------------------------------------------------------------- |
| `list_projects`      | All test projects (id, name, prefix)                                          |
| `list_plans`         | Test plans of a project                                                       |
| `list_builds`        | Builds of a test plan, newest first                                           |
| `list_suites`        | All suites of a project (flat list with `parent_id` links + case counts)      |
| `list_suite_cases`   | Case summaries inside one suite                                               |
| `search_cases`       | Full-text search of case names within a project                               |
| `get_case`           | Full case detail: steps + recent executions (default 10, configurable)        |
| `create_suite`       | New suite (top-level or nested)                                               |
| `create_case`        | New case; optional `steps` are applied via an automatic follow-up update      |
| `update_case`        | Update name/summary/preconditions/importance/steps (`steps` replaces ALL)     |
| `create_plan`        | New active, public test plan                                                  |
| `create_build`       | New active build under a plan                                                 |
| `link_cases_to_plan` | Link cases (internal ids) to a plan; duplicates are skipped                   |
| `record_execution`   | Record a verdict (`p`/`f`/`b`) for a case (by external id) in a plan+build    |
| `plan_status`        | Combined plan health: totals by verdict + per-build breakdown                 |
| `plan_queue`         | Paged execution work queue for a plan+build, filterable by assignee           |
| `plan_flaky`         | Flakiness ranking (verdict flips) over the last N days                        |
| `assign_cases`       | Assign/unassign cases to testers for a plan+build (userId 0 unassigns)        |
| `list_users`         | TestLink users for assignments and queue filters                              |

Every tool returns pretty-printed JSON as text content. HTTP errors (and
non-JSON responses such as PHP fatal-error pages) are returned as `isError`
results carrying the response body text, so the calling agent can see exactly
what the backend said.

Notes:

- Most tools take the **internal** case id; `record_execution` takes the
  **external** id (`PREFIX-number`, e.g. `P8T-10006`).
- `plan_flaky` defaults to a 7-day window. On very large plans, wide windows
  (e.g. 30 days over ~10k cases) can exhaust the backend's PHP memory limit;
  the resulting error page is surfaced via `isError`.

## Usage examples

Inside Claude Code, with the server connected:

- "List the TestLink projects, then show me the failing cases in the queue for
  Plan1 / Build2."
- "Create a suite 'Checkout' under project php8test and add a test case
  'Guest checkout works' with 3 steps."
- "Create a plan 'Sprint 42' with a build 'RC1', link the cases matching
  'login', and assign them all to admin."
- "Record a pass for P8T-10006 in plan 20126 build 3 with note 'verified
  manually on Chrome 126'."
- "How healthy is plan 5? Which cases were flaky in the last week?"

### Smoke test from a shell

```sh
cd mcp
( printf '%s\n' '{"jsonrpc":"2.0","id":1,"method":"initialize","params":{"protocolVersion":"2025-06-18","capabilities":{},"clientInfo":{"name":"smoke","version":"0"}}}'
  sleep 0.4
  printf '%s\n' '{"jsonrpc":"2.0","method":"notifications/initialized"}'
  printf '%s\n' '{"jsonrpc":"2.0","id":2,"method":"tools/call","params":{"name":"list_projects","arguments":{}}}'
  sleep 1
) | TESTLINK_APIKEY=<your key> node dist/index.js
```

## The testlink-qa subagent

`.claude/agents/testlink-qa.md` defines a project-scoped Claude Code subagent
that uses this server as a QA operator: it browses the case repository,
authors cases with steps, plans runs, records verdicts with evidence notes,
and summarizes plan health. It confirms destructive/bulk operations (notably
`update_case` step replacement and bulk link/assign/execute) and reports the
ids of everything it creates. Invoke it by asking Claude Code to use the
`testlink-qa` agent, e.g. "Use the testlink-qa agent to triage plan 5".
