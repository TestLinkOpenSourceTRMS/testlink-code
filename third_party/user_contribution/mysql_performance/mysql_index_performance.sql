/*
SQL script to grab the worst performing indexes in the whole server
From:
http://www.bserban.org/2009/02/mysql-index-performance/
*/
SELECT
t.TABLE_SCHEMA AS `db`
, t.TABLE_NAME AS `table`
, s.INDEX_NAME AS `inde name`
, s.COLUMN_NAME AS `field name`
, s.SEQ_IN_INDEX `seq in index`
, s2.max_columns AS `# cols`
, s.CARDINALITY AS `card`
, t.TABLE_ROWS AS `est rows`
, ROUND(((s.CARDINALITY / IFNULL(t.TABLE_ROWS, 0.01)) * 100), 2) AS `sel %`
FROM INFORMATION_SCHEMA.STATISTICS s
INNER JOIN INFORMATION_SCHEMA.TABLES t
ON s.TABLE_SCHEMA = t.TABLE_SCHEMA
AND s.TABLE_NAME = t.TABLE_NAME
INNER JOIN (
SELECT
TABLE_SCHEMA
, TABLE_NAME
, INDEX_NAME
, MAX(SEQ_IN_INDEX) AS max_columns
FROM INFORMATION_SCHEMA.STATISTICS
WHERE TABLE_SCHEMA != 'mysql'
GROUP BY TABLE_SCHEMA, TABLE_NAME, INDEX_NAME
) AS s2
ON s.TABLE_SCHEMA = s2.TABLE_SCHEMA
AND s.TABLE_NAME = s2.TABLE_NAME
AND s.INDEX_NAME = s2.INDEX_NAME
WHERE t.TABLE_SCHEMA != 'mysql'                         /* Filter out the mysql system DB */
AND t.TABLE_ROWS > 10                                   /* Only tables with some rows */
AND s.CARDINALITY IS NOT NULL                           /* Need at least one non-NULL value in the field */
AND (s.CARDINALITY / IFNULL(t.TABLE_ROWS, 0.01)) < 1.00 /* Selectivity < 1.0 b/c unique indexes are perfect anyway */
ORDER BY `sel %`, s.TABLE_SCHEMA, s.TABLE_NAME          /* Switch to `sel %` DESC for best non-unique indexes */
