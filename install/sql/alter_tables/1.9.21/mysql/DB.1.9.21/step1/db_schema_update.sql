# TestLink Open Source Project - http://testlink.sourceforge.net/
# This script is distributed under the GNU General Public License 2 or later.
# ---------------------------------------------------------------------------------------
# @filesource db_schema_update.sql
#
# SQL script - updates DB schema for MySQL -
# From TestLink 1.9.20 to 1.9.21
#
# Performance indexes for execution-heavy installations.
#
# The dashboard/metrics queries filter executions by test plan and
# resolve the LATEST execution per test case version. The historic
# executions_idx1 index starts with tcversion_id, so a plan-scoped
# scan could not use it and degraded to a full table scan
# (measured: full scan over 500k rows for status totals).
#
# idx_exec_tplan_tcv_id backs:
#   - latest-status-per-version windows
#     (WHERE testplan_id=? ... PARTITION BY tcversion_id ORDER BY id)
#   - flaky/flip analysis (WHERE testplan_id=? ORDER BY tcversion_id, id)
# idx_exec_tplan_ts backs:
#   - daily trend aggregation
#     (WHERE testplan_id=? GROUP BY DATE(execution_ts))

ALTER TABLE /*prefix*/executions
  ADD INDEX /*prefix*/idx_exec_tplan_tcv_id (testplan_id, tcversion_id, id);

ALTER TABLE /*prefix*/executions
  ADD INDEX /*prefix*/idx_exec_tplan_ts (testplan_id, execution_ts);
