# usersAssign — server-side pagination & filtering

This document describes how **Assign Test Project / Test Plan roles**
(`lib/usermanagement/usersAssign.php` + the `tl-classic` template
`gui/templates/tl-classic/usermanagement/usersAssign.tpl`) works **after**
the MantisBT-style rework.

Before the change the page loaded **every active user** at once and let the
browser's DataTables plugin paginate/filter client-side. Now the user list is
**paged and filtered on the server**, the same way MantisBT does it in
`manage_user_page.php`.

---

## What it looks like

On top of the user table there is:

- **A filter toolbar** — one chip for `All` and one per initial letter
  (`A`–`Z`, `0`–`9`) that has any matching users. Each chip shows a count
  badge (e.g. `All 3`, `A 2`, `B 1`). Only letters with matches are rendered.
  Clicking a chip is a plain **GET** link, so it is bookmarkable / shareable.
- **A user-name search box** with a **Filter** button. This is a separate
  **GET** form. It is deliberately kept **outside** the main role-assignment
  `<form method="post">` — an inner `<form>` start tag would make the HTML
  parser terminate the outer form early and orphan the table and the Update
  button.
- **A count badge** above the table: total matching users, plus
  `shown: first–last / total` for the current page.
- **A pagination footer** (`« Prev`, `1 … n`, `Next »`) that is only rendered
  when there is more than one page.

The rest of the original page is preserved: the Test Project / Test Plan
feature combo, the "set roles to … Do" bulk-apply row, the per-user role
`<select>`s (the admin/global-admin row is disabled), the inherited-role
option, the `not_authorized_user` row styling, the demo-mode guards, and the
`Update` (`do_update`) submit button.

The client-side DataTables includes
(`DataTables.inc.tpl`, `DataTablesColumnFiltering.inc.tpl`) are **removed**
because pagination/filtering are now done in PHP.

---

## URL parameters

All parameters are optional. The page is driven entirely by the **query
string** for navigation (filters, search, page) and by the **POST body** for
updating roles.

| Param         | Type   | Meaning                                                                     |
|---------------|--------|-----------------------------------------------------------------------------|
| `featureType` | string | `testproject` or `testplan` (required to show the form)                    |
| `featureID`   | int    | The selected test project / test plan id                                    |
| `page_number` | int    | 1-based page. Clamped to `[1 .. page_count]`. Default `1`.                  |
| `filter`      | string | `ALL` or a **single** letter `A`–`Z`/`0`–`9`. Anything else → `ALL`.        |
| `search`      | string | Login substring search (case-insensitive `LIKE '%term%'`).                  |
| `per_page`    | int    | Records per page. Capped at `1000`. `0`/absent → the configured default.    |

Example:

```
usersAssign.php?featureType=testproject&featureID=1&filter=A&search=a&page_number=2&per_page=10
```

---

## Backend (`lib/usermanagement/usersAssign.php`)

### `init_args()`
Reads and normalises the new parameters through the usual `R_PARAMS` input
validation:

- `page_number` → int, default `1`.
- `per_page` → int, capped at `1000`; `0` means "use the default".
- `filter` → upper-cased, trimmed; must be `ALL` or one letter in
  `A–Z`/`0–9`, otherwise it is treated as `ALL`.
- `search` → trimmed; may be empty.

The **default per-page** comes from the existing config block

```php
$tlCfg->gui->usersAssign->pagination->enabled   // default true
$tlCfg->gui->usersAssign->pagination->length    // '[20, 40, 60, -1], ...'
```

The first number in `length` is used as the default page size (20). If
`pagination->enabled` is `false`, paging is disabled entirely (`per_page = 0`),
which reproduces the old "show all active users" behaviour.

### `getFilteredUsersPage($db, $args)`
The core of the change. It:

1. Builds the `WHERE` clause:
   ```sql
   WHERE active = 1
     [AND login LIKE '<letter>%' ]          -- when filter != ALL
     [AND login LIKE '%<term>%' ESCAPE '!'] -- when search != ''
   ```
   The search term is escaped so `%`, `_` and the escape char `!` act as
   **literals** (they are prefixed with `!`). This prevents wildcard /
   quote-injection through the search box.
2. Runs `SELECT COUNT(id) ...` to get the **total** matching users.
3. Computes `page_count = ceil(total / per_page)`, clamps `page_number` into
   `[1 .. page_count]`, and derives the `LIMIT … OFFSET`.
4. Runs `SELECT id ... ORDER BY login LIMIT ? OFFSET ?` and hydrates just that
   page of `tlUser` objects.
5. Builds the **pager model** (a `stdClass`) for the template:
   - `enabled`, `per_page`, `page_number`, `page_count`
   - `total_users`, `first_row`, `last_row`
   - `filter`, `search` (echoed back into links/inputs)
   - `letter_counts` — a per-initial-letter count (`GROUP BY LEFT(login,1)`)
     that respects the active search term, used for the chip badges
   - `all_count`
   - `page_links` — a windowed list of page numbers with `…` ellipses
     (`1 … n-2 n-1 n n+1 n+2 … last`)
   - `has_prev`, `has_next`

### Effective-role computation is now page-scoped
The old code called `tlUser::getAll($db, "WHERE active=1", …)` and then ran
`readTestProjectRoles` / `readTestPlanRoles` plus
`get_tproject_effective_role` / `get_tplan_effective_role` for **every** active
user (an N+1 over the whole user base). Now the same calls run **only for the
users on the current page**, so the role lookups scale with the page size, not
the user base.

### Update flow (unchanged behaviour, preserved context)
```
1. if ($args->featureID && $args->doUpdate && $featureMgr)
     checkRightsForUpdate(...)  then  doUpdate(...)
2. getFilteredUsersPage(...)   <-- re-render AFTER the update
3. compute effective roles for the page users
```
`doUpdate()` still runs **before** the page is re-read, so the feedback message
("…roles updated") reflects the change. The main POST form carries hidden
inputs for `page_number`, `filter`, `search` and `per_page`, so after clicking
**Update** the user lands back on **the same filtered/paged subset** they were
editing.

> **Important semantics:** a role update applies to the **currently visible**
> (filtered/paged) set of users — i.e. the rows rendered on the submitted page.
> Users on other pages or outside the active filter are **not** touched by that
> submission.

`checkRights()`, `checkRightsForUpdate()` and `doUpdate()` are **unchanged**.

---

## Template (`usersAssign.tpl`)

- **Filter chips** and **search form** render only when a feature is selected
  and `$gui->pager->enabled` is true.
- The search form and chip links all preserve `featureType` and `featureID`
  (and the search form also preserves the active `filter`), so switching
  search terms does not reset the feature.
- **Count badge** (`total_users` + `first_row`–`last_row` / total) and
  **pagination footer** render from the pager model; the footer is gated on
  `page_count > 1`.
- Hidden paging inputs keep context through `do_update`.

---

## Locale strings

Six new UI strings were added to **en_US**, **en_GB** and **it_IT**:
`filter_all`, `search_user_hint`, `btn_filter`, `prev_page`, `next_page`,
`users_shown_of`. Other language packs fall back via `lang_get`.

---

## Defaults & configuration

| Setting                                                        | Default |
|----------------------------------------------------------------|---------|
| `$tlCfg->gui->usersAssign->pagination->enabled`                | `true`  |
| page size (first number of `pagination->length`)               | `20`    |
| `per_page` override cap                                         | `1000`  |
| `filter` fallback when invalid                                  | `ALL`   |

Setting `pagination->enabled = false` disables server-side paging (all active
users on one page, no chips/footer) — the pre-change behaviour.

---

## Verification performed (local Apache + MySQL:3307, admin account)

- Per-page limit, page links, and clamping of out-of-range `page_number`.
- Initial-letter filter (e.g. `A` → admin, alfa; `B` → beta; `Z`/`0` → empty)
  with correct chip counts.
- Username search (`al`→alfa, `be`→beta, `admin`→admin, `zzzz`→empty) and
  wildcard/quote safety (`%`, `_`, `'` are treated as literals).
- Filter + search + page combinations.
- A role update submitted on a filtered page changed **only** the visible user
  (confirmed against `user_testproject_roles`, then restored).
- Testplan mode (`featureType=testplan`) renders, paginates, filters and
  searches the same way.
- Rights-checking paths unchanged (diff review: 4 focused PHP hunks, no edits
  to `checkRights` / `checkRightsForUpdate` / `doUpdate`).

---

## Files touched

- `lib/usermanagement/usersAssign.php`
- `gui/templates/tl-classic/usermanagement/usersAssign.tpl`
- `locale/en_US/strings.txt`, `locale/en_GB/strings.txt`, `locale/it_IT/strings.txt`
