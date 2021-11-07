# ADOdb Changelog - v5.x

All notable changes to this project will be documented in this file.
As of version 5.20.1, its format is based on
[Keep a Changelog](https://keepachangelog.com/).

This project adheres to the [Semantic Versioning](https://semver.org/)
specification, since version 5.20.0.

Older changelogs:
[v4.x](changelog_v4.x.md),
[v3.x](changelog_v3.x.md),
[v2.x](changelog_v2.x.md).

--------------------------------------------------------------------------------

## [5.21.3] - 2021-10-31

### Fixed

- core: Ensure temp $ADODB_COUNTRECS changes really are temporary
  [#761](https://github.com/ADOdb/ADOdb/issues/761)
- mysqli: force error reporting mode to OFF (PHP 8.1 compatibility) 
  [#755](https://github.com/ADOdb/ADOdb/issues/755)
- pdo: fix metaIndexes declaration to match parent
  [#717](https://github.com/ADOdb/ADOdb/issues/717)


## [5.21.2] - 2021-08-22

### Fixed

- Fix syntax error in toexport.inc.php
  [#749](https://github.com/ADOdb/ADOdb/issues/749)
- pgsql: fix fetchField() parameter naming
  [#752](https://github.com/ADOdb/ADOdb/issues/752)


## [5.21.1] - 2021-08-15

### Changed

- Standardized source code file headers
  [#728](https://github.com/ADOdb/ADOdb/issues/728)
- Code cleanup: PHPDoc, code style, whitespace, etc.
  [#691](https://github.com/ADOdb/ADOdb/issues/691) 
  (and others)

### Fixed

- Caching in FieldTypesArray() causes problems
  [#687](https://github.com/ADOdb/ADOdb/issues/687)
- setConnectionParameter() method should not be final
  [#694](https://github.com/ADOdb/ADOdb/issues/694)
- Final private methods throw warning (PHP 8)
  [#711](https://github.com/ADOdb/ADOdb/issues/711)
- Fix record count when executing SQL with subqueries
  [#715](https://github.com/ADOdb/ADOdb/issues/715)
- Incorrect handling of $ADODB_QUOTE_FIELDNAMES = true
  [#721](https://github.com/ADOdb/ADOdb/issues/721)
- db2: fix columns always returned in lowercase
  [#719](https://github.com/ADOdb/ADOdb/issues/719)
- PDO: Bind parameters fail if sent in associative array
  [#705](https://github.com/ADOdb/ADOdb/issues/705)
- mssql: _insertid() doesn't work anymore  
  [#692](https://github.com/ADOdb/ADOdb/issues/692)
- mssql: PHP warnings in dropColumnSQL()
  [#696](https://github.com/ADOdb/ADOdb/issues/696)
- mssql: duplicate key in SQLDate convert formats
  [#748](https://github.com/ADOdb/ADOdb/issues/748)
- mysql: affected_rows() returns number instead of false
  [#604](https://github.com/ADOdb/ADOdb/issues/604)
- mysql: TypeError when calling get/setChangeSet on unset connection (PHP 8)
  [#686](https://github.com/ADOdb/ADOdb/issues/686)
- mysql: TypeError when calling setConnectionParameter() with non-numeric value (PHP 8)
  [#693](https://github.com/ADOdb/ADOdb/issues/693)
- pdo: Affected_Rows() throws Notice and returns 0 when rows affected
  [#733](https://github.com/ADOdb/ADOdb/issues/733)
- pgsql: sub-selects require aliasing
  [#736](https://github.com/ADOdb/ADOdb/issues/736)
- xml: Invalid SQL in extractSchema()
  [#707](https://github.com/ADOdb/ADOdb/issues/707)

### Removed

- Use of _ADODB_COUNT as workaround for counting in complex queries
  (introduced in [#88](https://github.com/ADOdb/ADOdb/issues/88))
  [#715](https://github.com/ADOdb/ADOdb/issues/715)



## [5.21.0] - 2021-02-27

### Fixed

- pgsql: param(0) returns invalid `$0` placeholder
  [#682](https://github.com/ADOdb/ADOdb/issues/682)


## [5.21.0-rc.1] - 2021-02-02

Includes all fixes from 5.20.20.

### Added

- Explicit support for PHP 8 with Composer

### Fixed

- Replace adodb_str_replace() calls with str_replace()
  [#646](https://github.com/ADOdb/ADOdb/issues/646)
- pgsql: override ADODB_DataDict::ChangeTableSQL()
  [#634](https://github.com/ADOdb/ADOdb/issues/634)
- sqlite: fix metaIndexes does not return primary key correctly
  [#656](https://github.com/ADOdb/ADOdb/issues/656)
- xmlschema: PHP8 compatibility
  [#658](https://github.com/ADOdb/ADOdb/issues/658)

### Removed

- Support for PHP < 5.5.9
  [#654](https://github.com/ADOdb/ADOdb/issues/654)
- XML-RPC Interface
  [#671](https://github.com/ADOdb/ADOdb/issues/671)
- Magic quotes related code
  [#674](https://github.com/ADOdb/ADOdb/issues/674)


## [5.20.20] - 2021-01-31

### Fixed

- Fix usage of get_magic_* functions
  [#619](https://github.com/ADOdb/ADOdb/issues/619)
  [#657](https://github.com/ADOdb/ADOdb/issues/657)
- Fix PHP warning in _rs2rs() function
  [#679](https://github.com/ADOdb/ADOdb/issues/679)
- pdo: Fix Fatal error in _query()
  [#666](https://github.com/ADOdb/ADOdb/issues/666)
- pdo: Fix undefined variable
  [#678](https://github.com/ADOdb/ADOdb/issues/678)
- pgsql: Fix Fatal error in _close() method (PHP8)
  [#666](https://github.com/ADOdb/ADOdb/issues/666)
- pgsql: fix deprecated function aliases (PHP8)
  [#667](https://github.com/ADOdb/ADOdb/issues/667)
- text: fix Cannot pass parameter by reference
  [#668](https://github.com/ADOdb/ADOdb/issues/668)


## [5.21.0-beta.1] - 2020-12-20

Includes all fixes from 5.20.19.

### Added

- adodb: New helper methods: day(), month(), year()
  [#225](https://github.com/ADOdb/ADOdb/issues/225)
- adodb: add Occitan ([#285](https://github.com/ADOdb/ADOdb/issues/285))
  and Indonesian ([#293](https://github.com/ADOdb/ADOdb/issues/293)) translations.
- adodb: add control over BLOB data dictionary feature (NOT NULL, DEFAULT)
  [#292](https://github.com/ADOdb/ADOdb/issues/292)
  [#478](https://github.com/ADOdb/ADOdb/issues/478)
- mssql: support Windows authentication
  [#353](https://github.com/ADOdb/ADOdb/issues/353)
- mysqli: support SSL connections
  [#415](https://github.com/ADOdb/ADOdb/issues/415)
- pdo/dblib: new driver
  [#496](https://github.com/ADOdb/ADOdb/issues/496)
- pdo/firebird: new driver
  [#378](https://github.com/ADOdb/ADOdb/issues/378)
- loadbalancer: read/write splitting and load balancing across multiple connections, thanks to Mike Benoit
  [#111](https://github.com/ADOdb/ADOdb/issues/111)

### Changed

- adodb: addColumnSQL datadict function now supports ENUM data types
  [#26](https://github.com/ADOdb/ADOdb/issues/26)
- adodb: introduce user-defined default Metatype
  [#165](https://github.com/ADOdb/ADOdb/issues/165)
- adodb: AutoExecute validates empty fields array
  [#154](https://github.com/ADOdb/ADOdb/issues/154)
- adodb: Add new value defaulting mode for getInsertSQL()
  [#214](https://github.com/ADOdb/ADOdb/issues/214)
- adodb: Added portable substring method
  [#219](https://github.com/ADOdb/ADOdb/issues/219)
- adodb: Optimize FieldTypesArray with static variable
  [#367](https://github.com/ADOdb/ADOdb/issues/367)
- adodb: Allow output handler to be callable
  [#312](https://github.com/ADOdb/ADOdb/issues/312)
- adodb-time: Add 'W' (week of year) format support in adodb_date()
  [#223](https://github.com/ADOdb/ADOdb/issues/223)
- db2: full driver rewrite
  [#442](https://github.com/ADOdb/ADOdb/issues/442)
- firebird: updated driver, thanks to Lester Caine
  [#201](https://github.com/ADOdb/ADOdb/issues/201)
- mssql: Add Convert on SQLDate Method
  [#304](https://github.com/ADOdb/ADOdb/issues/304)
- mssql: support alternative port in connect
  [#314](https://github.com/ADOdb/ADOdb/issues/314)
- mssql: MetaForeignKeys() not returning all FKs
  [#486](https://github.com/ADOdb/ADOdb/issues/486)
- mssql: support for T-SQL-style square brackets
  [#246](https://github.com/ADOdb/ADOdb/issues/246)
- mssqlnative: add support for 'l' (day of week) format in sqlDate()
  [#232](https://github.com/ADOdb/ADOdb/issues/232)
- mssqlnative: support metaProcedures() method
  [#578](https://github.com/ADOdb/ADOdb/issues/578)
- setConnectionParameter() now allows multiple parameters with the same key value
  [#187](https://github.com/ADOdb/ADOdb/issues/187)
- mysqli: Insert_ID() did not return correct value after executing stored procedure
  [#166](https://github.com/ADOdb/ADOdb/issues/166)
- mysqli: method failed if $associative set true
  [#181](https://github.com/ADOdb/ADOdb/issues/181)
- oci8: provide option to create compact trigger/sequence names
  [#565](https://github.com/ADOdb/ADOdb/issues/565)
- odbc/mssql: fix null strings concatenation issue with SQL server 2012
  [#148](https://github.com/ADOdb/ADOdb/issues/148)
- odbc/mssql: add missing Concat() method
  [#402](https://github.com/ADOdb/ADOdb/issues/402)
- pdo: add setConnectionParameter support
  [#247](https://github.com/ADOdb/ADOdb/issues/247)
- pdo: add meta extension points
  [#475](https://github.com/ADOdb/ADOdb/issues/475)
- pdo/mysql: add genID() and createSequence() support
  [#465](https://github.com/ADOdb/ADOdb/issues/465)
- pdo/pgsql: Add support for transactions
  [#363](https://github.com/ADOdb/ADOdb/issues/363)
- pdo/sqlsrv: add SetTransactionMode() method
  [#362](https://github.com/ADOdb/ADOdb/issues/362)
- pgsql: optimize version check
  [#334](https://github.com/ADOdb/ADOdb/issues/334)
- pgsql: use postgres9 driver by default
  [#474](https://github.com/ADOdb/ADOdb/issues/474)
- sqlite: Fix Metataypes mapping
  [#177](https://github.com/ADOdb/ADOdb/issues/177)
- sqlite: driver did not support metaForeignKeys
  [#179](https://github.com/ADOdb/ADOdb/issues/179)
- memcache: add support for memcached PECL library
  [#322](https://github.com/ADOdb/ADOdb/issues/322)
- xml: support table 'opt' attribute with mysqli
  [#267](https://github.com/ADOdb/ADOdb/issues/267)
- xml: add support for 'DESCR' tags for tables/fields
  [#265](https://github.com/ADOdb/ADOdb/issues/265)

### Deprecated

- mysqli: Deprecate $optionFlags property in favor of standard setConnectionParameter() method
  [#188](https://github.com/ADOdb/ADOdb/issues/188)
- proxy: the client driver and server.php script are deprecated
  [#444](https://github.com/ADOdb/ADOdb/issues/444)

### Removed

- adodb: Remove references to obsolete ADOdb Extension
  [#270](https://github.com/ADOdb/ADOdb/issues/270)
- adodb: Remove unneeded ADODB_str_replace function
  [#582](https://github.com/ADOdb/ADOdb/issues/582)
- adodb: Remove useless PHP 4 and 5 version checks
  [#583](https://github.com/ADOdb/ADOdb/issues/583)
  [#584](https://github.com/ADOdb/ADOdb/issues/584)
- adodb: replace _array_change_key_case() by internal PHP function
  [#587](https://github.com/ADOdb/ADOdb/issues/587)

### Fixed

- adodb: Remove useless constructors 
  [#171](https://github.com/ADOdb/ADOdb/issues/171)
- adodb: Define default constructor in ADOConnection base class 
  [#172](https://github.com/ADOdb/ADOdb/issues/172)
- adodb: Reimplement base methods charMax() and textMax() 
  [#183](https://github.com/ADOdb/ADOdb/issues/183)
  [#220](https://github.com/ADOdb/ADOdb/issues/220)
- adodb: fix getAssoc() 
  [#189](https://github.com/ADOdb/ADOdb/issues/189) 
  [#198](https://github.com/ADOdb/ADOdb/issues/198) 
  [#204](https://github.com/ADOdb/ADOdb/issues/204)
- adodb: Improve array identification in ADOrecordset::getAssoc() 
  [#101](https://github.com/ADOdb/ADOdb/issues/101)
- adodb: MetaColumns() consistently returns Actual Type by default in all drivers 
  [#184](https://github.com/ADOdb/ADOdb/issues/184) 
  [#133](https://github.com/ADOdb/ADOdb/issues/133)
- adodb: getAssoc() should not change case of result set's outermost key
  [#335](https://github.com/ADOdb/ADOdb/issues/335)
- adodb: getAssoc() fix fetch mode
  [#350](https://github.com/ADOdb/ADOdb/issues/350)
- adodb: Replace each() with foreach (PHP 7.2 compatibility)
  [#373](https://github.com/ADOdb/ADOdb/issues/373)
- adodb: fix ADORecordSet constructor signature
  [#278](https://github.com/ADOdb/ADOdb/issues/278)
- adodb: support use of spaces and reserved keywords in replace function
  [#390](https://github.com/ADOdb/ADOdb/issues/390)
- adodb: fix adodb_strip_order_by() to only remove the last order by statement
  [#549](https://github.com/ADOdb/ADOdb/issues/549)
- adodb: fix field names quoting when setting value to null
  [#572](https://github.com/ADOdb/ADOdb/issues/572)
- adodb: fix getAssoc returning key as value column with ADODB_FETCH_BOTH mode
  [#600](https://github.com/ADOdb/ADOdb/issues/600)
- adodb-time: Fix 'Q' (quarter of year) format in adodb_date()
  [#222](https://github.com/ADOdb/ADOdb/issues/222)
- active record: honor column and table name quoting
  [#309](https://github.com/ADOdb/ADOdb/issues/309)
- db2: fix ChangeTableSQL() signature
  [#338](https://github.com/ADOdb/ADOdb/issues/338)
- mssqlnative: Query not returning id
  [#185](https://github.com/ADOdb/ADOdb/issues/185)
- mssqlnative: fix invalid return value for ErrorNo()
  [#298](https://github.com/ADOdb/ADOdb/issues/298)
- mssqlnative: ensure that the bind array is numeric
  [#336](https://github.com/ADOdb/ADOdb/issues/336)
- mssqlnative: fix crash with driver version 5.6 on queries returning no data
  [#492](https://github.com/ADOdb/ADOdb/issues/492)
- mysql: prevent use of driver with PHP >= 7.0
  [#310](https://github.com/ADOdb/ADOdb/issues/310)
- mysqli: return fields as ADOFieldObject objects
  [#175](https://github.com/ADOdb/ADOdb/issues/175)
- mysqli (perf): tables() method definition inconsistent with parent
  [#435](https://github.com/ADOdb/ADOdb/issues/435)
- mysql: genId() not returning next sequence value
  [#493](https://github.com/ADOdb/ADOdb/issues/493)
- oci8: fix syntax error preventing sequence creation
  [#540](https://github.com/ADOdb/ADOdb/issues/540)
- oci8: remove use of curly braces in string offsets (deprecated in PHP 7.4)
  [#570](https://github.com/ADOdb/ADOdb/issues/570)
- odbc: MetaColumns() can optionally be set to return MetaType for backwards compatibility
  [#184](https://github.com/ADOdb/ADOdb/issues/184)
- pdo: allow loading of subclassed recordset
  [#245](https://github.com/ADOdb/ADOdb/issues/245)
- pdo: fix PHP notice
  [#248](https://github.com/ADOdb/ADOdb/issues/248)
- pdo: fix ADORecordSet class loading
  [#250](https://github.com/ADOdb/ADOdb/issues/250)
- pdo/sqlsrv: fix fetchField() method
  [#251](https://github.com/ADOdb/ADOdb/issues/251)
  [#234](https://github.com/ADOdb/ADOdb/issues/234)
- pgsql: add CIDR data type to MetaType()
  [#281](https://github.com/ADOdb/ADOdb/issues/281)
- pgsql: fix param number reset with param(false)
  [#380](https://github.com/ADOdb/ADOdb/issues/380)
- pgsql: specialized casts for _recreate_copy_table()
  [#207](https://github.com/ADOdb/ADOdb/issues/207)
- sqlite: _createSuffix is now compatible with parent
  [#178](https://github.com/ADOdb/ADOdb/issues/178)
- sqlite: metaIndexes could not locate indexes on uppercase table name
  [#176](https://github.com/ADOdb/ADOdb/issues/176)
- sqlite: metaIndexes() returns column as array instead of CSV
  [#567](https://github.com/ADOdb/ADOdb/issues/567)
- session: string parameters for `assert` are deprecated in PHP 7.2
  [#438](https://github.com/ADOdb/ADOdb/issues/438)
- xml: fix invalid xmlschema03.dtd and descr tag in session schema XML
  [#595](https://github.com/ADOdb/ADOdb/issues/595)

### Security

- adodb: prevent SQL injection in SelectLimit() 
  [#311](https://github.com/ADOdb/ADOdb/issues/311)
- session: add 'httponly' flag to cookie
  [#190](https://github.com/ADOdb/ADOdb/issues/190)


## [5.20.19] - 2020-12-13

### Changed

- PDO: support persistent connections
  [#650](https://github.com/ADOdb/ADOdb/issues/650)
- mssql: connect to SQL Server database on a specified port
  [#624](https://github.com/ADOdb/ADOdb/issues/624)

### Fixed

- DSN database connection with password containing a `#` fails
  [#651](https://github.com/ADOdb/ADOdb/issues/651)
- Metacolumns returns wrong type for integer fields in MySQL 8
  [#642](https://github.com/ADOdb/ADOdb/issues/642)
- Uninitialized Variable access in mssqlnative ErrorNo() method
  [#637](https://github.com/ADOdb/ADOdb/issues/637)


## [5.20.18] - 2020-06-28

### Fixed

- mssql: Retrieve error messages early before connection closed
  [#614](https://github.com/ADOdb/ADOdb/issues/614)


## [5.20.17] - 2020-03-31

### Fixed

- core: fix PHP notice in ADOdb_Exception constructor when using transactions
  [#601](https://github.com/ADOdb/ADOdb/issues/601)
- mssql: fix PHP notice due to uninitialized array with PHP 7.4
  [#608](https://github.com/ADOdb/ADOdb/issues/608)
- active record: Fix UpdateActiveTable failing with mixed case column names
  [#610](https://github.com/ADOdb/ADOdb/issues/610)


## [5.20.16] - 2020-01-12

-### Fixed

 mssql: queries are not correctly closed
  [#590](https://github.com/ADOdb/ADOdb/issues/590)


## [5.20.15] - 2019-11-24

### Fixed

- core: remove unnecessary srand() calls
  [#532](https://github.com/ADOdb/ADOdb/issues/532)
- core: Fix getMenu with ADODB_FETCH_BOTH
  [#482](https://github.com/ADOdb/ADOdb/issues/482)
- core: code cleanup for getMenu and related functions
  [#563](https://github.com/ADOdb/ADOdb/issues/563)
- pgsql: stop using obsolete pg_attrdef.adsrc column
  [#562](https://github.com/ADOdb/ADOdb/issues/562)
- pdo/mysql: remove extraneous comma in $fmtTimeStamp
  [#531](https://github.com/ADOdb/ADOdb/issues/531)
- active record: Use ADODB_ASSOC_CASE constant
  [#536](https://github.com/ADOdb/ADOdb/issues/536)
- session: Remove session_module_name('user') calls (PHP 7.2 compatibility)
  [#449](https://github.com/ADOdb/ADOdb/issues/449)
- PHP 7.4 compatibility: fix deprecated usage of join()
  [#547](https://github.com/ADOdb/ADOdb/issues/547)


## [5.20.14] - 2019-01-06

### Fixed

- core: Fix support for getMenu with ADODB_FETCH_ASSOC
  [#460](https://github.com/ADOdb/ADOdb/issues/460)
- perf/mysql: fix tables() function incompatible with parent
  [#435](https://github.com/ADOdb/ADOdb/issues/435)
- perf/mysql: fix error when logging slow queries
  [#463](https://github.com/ADOdb/ADOdb/issues/463)

### Security

- security: Denial of service in adodb_date()
  [#467](https://github.com/ADOdb/ADOdb/issues/467)


## [5.20.13] - 2018-08-06

### Fixed

- core: Fix query execution failures with mismatched quotes
  [#420](https://github.com/ADOdb/ADOdb/issues/420)
- ldap: Fix connections using URIs
  [#340](https://github.com/ADOdb/ADOdb/issues/340)
- mssql: Fix Time field format, allowing autoExecute() to inserting time
  [#432](https://github.com/ADOdb/ADOdb/issues/432)
- mssql: Fix Insert_ID returning null with table name in brackets
  [#313](https://github.com/ADOdb/ADOdb/issues/313)
- mssql: Fix count wrapper
  [#423](https://github.com/ADOdb/ADOdb/issues/423)
- oci8: Fix prepared statements failure
  [#318](https://github.com/ADOdb/ADOdb/issues/318)
- oci8po: Fix incorrect query parameter replacements
  [#370](https://github.com/ADOdb/ADOdb/issues/370)
- pdo: fix PHP notice due to uninitialized variable
  [#437](https://github.com/ADOdb/ADOdb/issues/437)


## [5.20.12] - 2018-03-30

### Fixed

- adodb: PHP 7.2 compatibility
  - Replace each() with foreach
    [#373](https://github.com/ADOdb/ADOdb/issues/373)
  - Replace deprecated create_function() calls
    [#404](https://github.com/ADOdb/ADOdb/issues/404)
  - Replace $php_errormsg with error_get_last()
    [#405](https://github.com/ADOdb/ADOdb/issues/405)
- adodb: Don't call `dl()` when the function is disabled
  [#406](https://github.com/ADOdb/ADOdb/issues/406)
- adodb: Don't bother with magic quotes when not available
  [#407](https://github.com/ADOdb/ADOdb/issues/407)
- adodb: fix potential SQL injection vector in SelectLimit()
  [#190](https://github.com/ADOdb/ADOdb/issues/190)
  [#311](https://github.com/ADOdb/ADOdb/issues/311)
  [#401](https://github.com/ADOdb/ADOdb/issues/401)


## [5.20.11] - Withdrawn

This release has been withdrawn as it introduced a regression on PHP 5.x.
Please use version 5.20.12 or later.


## [5.20.10] - 2018-03-08

### Fixed

- Fix year validation in adodb_validdate()
  [#375](https://github.com/ADOdb/ADOdb/issues/375)
- Release db resource when closing connection
  [#379](https://github.com/ADOdb/ADOdb/issues/379)
- Avoid full file path disclosure in ADOLoadCode()
  [#389](https://github.com/ADOdb/ADOdb/issues/389)
- mssql: fix PHP warning in _adodb_getcount()
  [#359](https://github.com/ADOdb/ADOdb/issues/359)
- mssql: string keys are not allowed in parameters arrays
  [#316](https://github.com/ADOdb/ADOdb/issues/316)
- mysqli: fix PHP warning on DB connect
  [#348](https://github.com/ADOdb/ADOdb/issues/348)
- pdo: fix auto-commit error in sqlsrv
  [#347](https://github.com/ADOdb/ADOdb/issues/347)
- sybase: fix PHP Warning in _connect()/_pconnect
  [#371](https://github.com/ADOdb/ADOdb/issues/371)


## [5.20.9] - 2016-12-21

### Fixed

- mssql: fix syntax error in version matching regex
  [#305](https://github.com/ADOdb/ADOdb/issues/305)


## [5.20.8] - 2016-12-17

### Fixed

- mssql: support MSSQL Server 2016 and later
  [#294](https://github.com/ADOdb/ADOdb/issues/294)
- mssql: fix Find() returning no results
  [#298](https://github.com/ADOdb/ADOdb/issues/298)
- mssql: fix Sequence name forced to 'adodbseq'
  [#295](https://github.com/ADOdb/ADOdb/issues/295),
  [#300](https://github.com/ADOdb/ADOdb/issues/300)
- mssql: fix GenId() not returning next sequence value with SQL Server 2005/2008
  [#302](https://github.com/ADOdb/ADOdb/issues/302)
- mssql: fix drop/alter column with existing default constraint
  [#290](https://github.com/ADOdb/ADOdb/issues/290)
- mssql: fix PHP notice in MetaColumns()
  [#289](https://github.com/ADOdb/ADOdb/issues/289)
- oci8po: fix inconsistent variable binding in SelectLimit()
  [#288](https://github.com/ADOdb/ADOdb/issues/288)
- oci8po: fix SelectLimit() with prepared statements
  [#282](https://github.com/ADOdb/ADOdb/issues/282)


## [5.20.7] - 2016-09-20

### Fixed

- oci8po: prevent segfault on PHP 7
  [#259](https://github.com/ADOdb/ADOdb/issues/259)
- pdo/mysql: Fix MetaTables() method
  [#275](https://github.com/ADOdb/ADOdb/issues/275)

### Security

- security: Fix SQL injection in PDO drivers qstr() method (CVE-2016-7405)
  [#226](https://github.com/ADOdb/ADOdb/issues/226)


## [5.20.6] - 2016-08-31

### Fixed

- adodb: Exit with error/exception when the ADOdb Extension is loaded
  [#269](https://github.com/ADOdb/ADOdb/issues/269)
- adodb: Fix truncated exception messages
  [#273](https://github.com/ADOdb/ADOdb/issues/273)

### Security

- security: Fix XSS vulnerability in old test script (CVE-2016-4855)
  [#274](https://github.com/ADOdb/ADOdb/issues/274)

## [5.20.5] - 2016-08-10

### Fixed

- adodb: Fix fatal error when connecting with missing extension
  [#254](https://github.com/ADOdb/ADOdb/issues/254)
- adodb: Fix _adodb_getcount()
  [#236](https://github.com/ADOdb/ADOdb/issues/236)
- mssql: Destructor fails if recordset already closed
  [#268](https://github.com/ADOdb/ADOdb/issues/268)
- mssql: Use SQL server native data types if available
  [#234](https://github.com/ADOdb/ADOdb/issues/234)
- mysqli: Fix PHP notice in _close() method
  [#240](https://github.com/ADOdb/ADOdb/issues/240)
- pdo: Let driver handle SelectDB() and SQLDate() calls
  [#242](https://github.com/ADOdb/ADOdb/issues/242)
- xml: Fix PHP strict warning
  [#260](https://github.com/ADOdb/ADOdb/issues/260)
- xml: remove calls to 'unset($this)' (PHP 7.1 compatibility)
  [#257](https://github.com/ADOdb/ADOdb/issues/257)


## [5.20.4] - 2016-03-31

### Fixed

- adodb: Fix BulkBind() param count validation
  [#199](https://github.com/ADOdb/ADOdb/issues/199)
- mysqli: fix PHP warning in recordset destructor
  [#217](https://github.com/ADOdb/ADOdb/issues/217)
- mysqli: cast port number to int when connecting (PHP7 compatibility)
  [#218](https://github.com/ADOdb/ADOdb/issues/218)


## [5.20.3] - 2016-01-01

### Fixed

- mssql: PHP warning when closing recordset from destructor not fixed in v5.20.2
  [#180](https://github.com/ADOdb/ADOdb/issues/180)


## [5.20.2] - 2015-12-27

### Fixed

- adodb: Remove a couple leftover PHP 4.x constructors (PHP7 compatibility)
  [#139](https://github.com/ADOdb/ADOdb/issues/139)
- db2ora: Remove deprecated preg_replace '/e' flag (PHP7 compatibility)
  [#168](https://github.com/ADOdb/ADOdb/issues/168)
- mysql: MoveNext() now respects ADODB_ASSOC_CASE
  [#167](https://github.com/ADOdb/ADOdb/issues/167)
- mssql, mysql, informix: Avoid PHP warning when closing recordset from destructor
  [#170](https://github.com/ADOdb/ADOdb/issues/170)


## [5.20.1] - 2015-12-06

### Fixed

- adodb: Fix regression introduced in 5.20.0, causing a PHP Warning when
  calling GetAssoc() on an empty recordset
  [#162](https://github.com/ADOdb/ADOdb/issues/162)
- ADOConnection::Version() now handles SemVer
  [#164](https://github.com/ADOdb/ADOdb/issues/164)


## [5.20.0] - 2015-11-28

### Added

- adodb: new setConnectionParameter() method, 
  previously implemented in mssqlnative driver only
  [#158](https://github.com/ADOdb/ADOdb/issues/158).
- pdo: new sqlsrv driver, thanks to MarcelTO
  [#81](https://github.com/ADOdb/ADOdb/issues/81)
- adodb: support for pagination with complex queries, thanks to Mike Benoit
  [#88](https://github.com/ADOdb/ADOdb/issues/88)
- pdo/mysql: New methods to make the driver behave more like mysql/mysqli, thanks to Andy Theuninck
  [#40](https://github.com/ADOdb/ADOdb/issues/40)

### Changed

- adodb: Define DB_AUTOQUERY_* constants in main include file
  [#49](https://github.com/ADOdb/ADOdb/issues/49)
- adodb: Add mssql's DATETIME2 type to ADOConnection::MetaType(), thanks to MarcelTO
  [#80](https://github.com/ADOdb/ADOdb/issues/80)
- adodb: Initialize charset in ADOConnection::SetCharSet
  [#39](https://github.com/ADOdb/ADOdb/issues/39)
- adodb: Parse port out of hostname if specified in connection parameters, thanks to Andy Theuninck
  [#63](https://github.com/ADOdb/ADOdb/issues/63)
- adodb: Improve compatibility of ADORecordSet_empty, thanks to Sjan Evardsson
  [#43](https://github.com/ADOdb/ADOdb/issues/43)
- mssqlnative: Use ADOConnection::outp instead of error_log
  [#12](https://github.com/ADOdb/ADOdb/issues/12)

### Fixed

- adodb: Fix regression introduced in v5.19, causing queries to return empty rows
  [#20](https://github.com/ADOdb/ADOdb/issues/20)
  [#93](https://github.com/ADOdb/ADOdb/issues/93)
  [#95](https://github.com/ADOdb/ADOdb/issues/95)
- adodb: Fix regression introduced in v5.19 in GetAssoc() with ADODB_FETCH_ASSOC mode and '0' as data
  [#102](https://github.com/ADOdb/ADOdb/issues/102)
- adodb: AutoExecute correctly handles empty result set in case of updates
  [#13](https://github.com/ADOdb/ADOdb/issues/13)
- adodb: Fix regex in Version()
  [#16](https://github.com/ADOdb/ADOdb/issues/16)
- adodb: Align method signatures to definition in parent class ADODB_DataDict
  [#31](https://github.com/ADOdb/ADOdb/issues/31)
- adodb: fix ADODB_Session::open() failing after successful ADONewConnection() call, thanks to Sjan Evardsson
  [#44](https://github.com/ADOdb/ADOdb/issues/44)
- adodb: Only include memcache library once for PHPUnit 4.x, thanks to Alan Farquharson
  [#74](https://github.com/ADOdb/ADOdb/issues/74)
- adodb: Move() returns false when given row is < 0, thanks to Mike Benoit.
- adodb: Fix inability to set values from 0 to null (and vice versa) with Active Record, thanks to Louis Johnson
  [#71](https://github.com/ADOdb/ADOdb/issues/71)
- adodb: Fix PHP strict warning in ADODB_Active_Record::Reload(), thanks to Boštjan Žokš
  [#75](https://github.com/ADOdb/ADOdb/issues/75)
- adodb: When flushing cache, initialize it if it is not set, thanks to Paul Haggart
  [#57](https://github.com/ADOdb/ADOdb/issues/57)
- adodb: Improve documentation of fetch mode and assoc case
- adodb: Improve logic to build the assoc case bind array
- adodb: Strict-standards compliance for function names
  [#18](https://github.com/ADOdb/ADOdb/issues/18)
  [#142](https://github.com/ADOdb/ADOdb/issues/142)
- adodb: Remove old PHP 4.x constructors for compatibility with PHP 7
  [#139](https://github.com/ADOdb/ADOdb/issues/139)
- adodb: Fix incorrect handling of input array in Execute()
  [#146](https://github.com/ADOdb/ADOdb/issues/146)
- adodb: Release Recordset when raising exception
  [#143](https://github.com/ADOdb/ADOdb/issues/143)
- adodb-lib: Optimize query pagination, thanks to Mike Benoit
  [#110](https://github.com/ADOdb/ADOdb/issues/110)
- memcache: use include_once() to avoid issues with PHPUnit. See PHPLens Issue No: 19489
- mssql_n: Allow use of prepared statements with driver
  [#22](https://github.com/ADOdb/ADOdb/issues/22)
- mssqlnative: fix failure on Insert_ID() if the insert statement contains a semicolon in a value string, thanks to sketule
  [#96](https://github.com/ADOdb/ADOdb/issues/96)
- mssqlnative: Fix "invalid parameter was passed to sqlsrv_configure" error, thanks to Ray Morris
  [#103](https://github.com/ADOdb/ADOdb/issues/103)
- mssqlnative: Fix insert_ID() failing if server returns more than 1 row, thanks to gitjti
  [#41](https://github.com/ADOdb/ADOdb/issues/41)
- mysql: prevent race conditions when creating/dropping sequences, thanks to MikeB
  [#28](https://github.com/ADOdb/ADOdb/issues/28)
- mysql: Fix adodb_strip_order_by() bug causing SQL error for subqueries with order/limit clause, thanks to MikeB.
- mysql: workaround for HHVM behavior, thanks to Mike Benoit.
- mysqli: Fix qstr() when called without an active connection
  [#11](https://github.com/ADOdb/ADOdb/issues/11)
- oci8: Fix broken quoting of table name in AddColumnSQL and AlterColumnSQL, thanks to Andreas Fernandez
  [#67](https://github.com/ADOdb/ADOdb/issues/67)
- oci8: Allow oci8 driver to use lowercase field names in assoc mode
  [#21](https://github.com/ADOdb/ADOdb/issues/21)
- oci8po: Prevent replacement of '?' within strings, thanks to Mark Newnham
  [#132](https://github.com/ADOdb/ADOdb/issues/132)
- pdo: Added missing property (fixes PHP notices)
  [#56](https://github.com/ADOdb/ADOdb/issues/56)
- pdo: Align method signatures with parent class, thanks to Andy Theuninck
  [#62](https://github.com/ADOdb/ADOdb/issues/62)
- postgres: Stop using legacy function aliases
- postgres: Fix AlterColumnSQL when updating multiple columns, thanks to Jouni Ahto
  [#72](https://github.com/ADOdb/ADOdb/issues/72)
- postgres: Fix support for HHVM 3.6, thanks to Mike Benoit
  [#87](https://github.com/ADOdb/ADOdb/issues/87)
- postgres: Noblob optimization, thanks to Mike Benoit
  [#112](https://github.com/ADOdb/ADOdb/issues/112)
- postgres7: fix system warning in MetaColumns() with schema. See PHPLens Issue No: 19481
- sqlite3: ServerInfo() now returns driver's version
- sqlite3: Fix wrong connection parameter in _connect(), thanks to diogotoscano
  [#51](https://github.com/ADOdb/ADOdb/issues/51)
- sqlite3: Fix FetchField, thanks to diogotoscano
  [#53](https://github.com/ADOdb/ADOdb/issues/53)
- sqlite3: Fix result-less SQL statements executed twice
  [#99](https://github.com/ADOdb/ADOdb/issues/99)
- sqlite3: use -1 for _numOfRows
  [#151](https://github.com/ADOdb/ADOdb/issues/151)
- xmlschema: Fix ExtractSchema() when given $prefix and $stripprefix parameters, thanks to peterdd
  [#92](https://github.com/ADOdb/ADOdb/issues/92)
- Convert languages files to UTF-8, thanks to Marc-Etienne Vargenau
  [#32](https://github.com/ADOdb/ADOdb/issues/32)


## 5.19 - 2014-04-23

**NOTE:**
This release suffers from a [known issue with Associative Fetch Mode](https://github.com/ADOdb/ADOdb/issues/20)
(i.e. when $ADODB_FETCH_MODE is set to ADODB_FETCH_ASSOC).
It causes recordsets to return empty strings (no data) when using some database drivers.
The problem has been reported on MSSQL, Interbase and Foxpro, but possibly affects
other database types as well; all drivers derived from the above are also impacted.

- adodb: GetRowAssoc will return null as required. See PHPLens Issue No: 19289
- adodb: Fix GetRowAssoc bug introduced in 5.17, causing function to return data from previous fetch for NULL fields. See PHPLens Issue No: 17539
- adodb: GetAssoc will return a zero-based array when 2nd column is null. See https://sourceforge.net/p/adodb/bugs/130/
- adodb: Execute no longer ignores single parameters evaluating to false. See https://sourceforge.net/p/adodb/patches/32/
- adodb: Fix LIMIT 1 clause in subquery gets stripped off. See PHPLens Issue No: 17813
- adodb-lib: Fix columns quoting bug. See https://sourceforge.net/p/adodb/bugs/127/
- Added new ADODB_ASSOC_CASE_* constants. Thx to Damien Regad.
- sessions: changed lob handling to detect all variations of oci8 driver.
- ads: clear fields before fetching. See PHPLens Issue No: 17539
- mssqlnative: fixed many FetchField compat issues. See PHPLens Issue No: 18464. Also date format changed to remove timezone.
- mssqlnative: Numerous fixes and improvements by Mark Newnham
    - Driver supports SQL Server 2005, 2008 and 2012
    - Bigint data types mapped to I8 instead of I
    - Reintroduced MetaColumns function
    - On SQL Server 2012, makes use of new CREATE SEQUENCE statement
    - FetchField caches metadata at initialization to improve performance
    - etc.
- mssqlnative: Fix Insert ID on prepared statement, thanks to Mike Parks. See PHPLens Issue No: 19079
- mssql: timestamp format changed to `Y-m-d\TH:i:s` (ISO 8601) to make them independent from DATEFORMAT setting, as recommended on
  [Microsoft TechNet](http://technet.microsoft.com/en-us/library/ms180878%28v=sql.105%29.aspx#StringLiteralDateandTimeFormats).
- mysql/mysqli: Fix ability for MetaTables to filter by table name, broken since 5.15. See PHPLens Issue No: 19359
- odbc: Fixed MetaTables and MetaPrimaryKeys definitions in odbc driver to match adoconnection class.
- odbc: clear fields before fetching. See PHPLens Issue No: 17539
- oci8: GetRowAssoc now works in ADODB_FETCH_ASSOC fetch mode
- oci8: MetaType and MetaForeignKeys argument count are now strict-standards compliant
- oci8: Added trailing `;` on trigger creation for sequence fields, prevents occurence of ORA-24344
- oci8quercus: new oci8 driver with support for quercus jdbc data types.
- pdo: Fixed concat recursion bug in 5.3. See PHPLens Issue No: 19285
- pgsql: Default driver (postgres/pgsql) is now postgres8
- pgsql: Fix output of BLOB (bytea) columns with PostgreSQL >= 9.0
- pgsql: Fix handling of DEFAULT NULL columns in AlterColumnSQL
- pgsql: Fix mapping of error message to ADOdb error codes
- pgsql: Reset parameter number in Param() method when $name == false
- postgres8: New class/type with correct behavior for _insertid()
  [#8](https://github.com/ADOdb/ADOdb/issues/8)
- postgres9: Fixed assoc problem. See PHPLens Issue No: 19296
- sybase: Removed redundant sybase_connect() call in _connect()
  [#3](https://github.com/ADOdb/ADOdb/issues/3)
- sybase: Allow connection on custom port
  [#9](https://github.com/ADOdb/ADOdb/issues/9)
- sybase: Fix null values returned with ASSOC fetch mode
  [#10](https://github.com/ADOdb/ADOdb/issues/10)
- Added Composer support
  [#7](https://github.com/ADOdb/ADOdb/issues/7)

## 5.18 - 2012-09-03

- datadict-postgres: Fixes bug in ALTER COL. See PHPLens Issue No: 19202.
- datadict-postgres: fixed bugs in MetaType() checking $fieldobj properties.
- GetRowAssoc did not work with null values. Bug in 5.17.
- postgres9: New driver to better support PostgreSQL 9. Thx Glenn Herteg and Cacti team.
- sqlite3: Modified to support php 5.4. Thx GÃ¼nter Weber [built.development#googlemail.com]
- adodb: When fetch mode is ADODB_FETCH_ASSOC, and we execute `$db->GetAssoc("select 'a','0'");` we get an error. Fixed. See PHPLens Issue No: 19190
- adodb: Caching directory permissions now configurable using global variable $ADODB_CACHE_PERMS. Default value is 0771.
- mysqli: SetCharSet() did not return true (success) or false (fail) correctly. Fixed.
- mysqli: changed dataProvider to 'mysql' so that MetaError and other shared functions will work.
- mssqlnative: Prepare() did not work previously. Now calling Prepare() will work but the sql is not actually compiled. Unfortunately bind params are passed to sqlsrv_prepare and not to sqlsrv_execute. make Prepare() and empty function, and we still execute the unprepared stmt.
- mysql: FetchField(-1), turns it is is not possible to retrieve the max_length. Set to -1.
- mysql-perf: Fixed "SHOW INNODB STATUS". Change to "SHOW ENGINE INNODB STATUS"

## 5.17 - 2012-05-18

- Active Record: Removed trailing whitespace from adodb-active-record.inc.php.
- odbc: Added support for $database parameter in odbc Connect() function. E.g. $DB->Connect($dsn_without_db, $user, $pwd, $database).
  Previously $database had to be left blank and the $dsn was used to pass in this parameter.
- oci8: Added better empty($rs) error handling to metaindexes().
- oci8: Changed to use newer oci API to support PHP 5.4.
- adodb.inc.php: Changed GetRowAssoc to more generic code that will work in all scenarios.

## 5.16 - 2012-03-26

- mysqli: extra mysqli_next_result() in close() removed. See PHPLens Issue No: 19100
- datadict-oci8: minor typo in create sequence trigger fixed. See PHPLens Issue No: 18879.
- security: safe date parsing changes. Does not impact security, these are code optimisations. Thx Saithis.
- postgres, oci8, oci8po, db2oci: Param() function parameters inconsistent with base class. $type='C' missing. Fixed.
- active-record: locked bug fixed. PHPLens Issue:19073
- mysql, mysqli and informix: added MetaProcedures. Metaprocedures allows to retrieve an array list of all procedures in database. PHPLens Issue No: 18414
- Postgres7: added support for serial data type in MetaColumns().

## 5.15 - 2012-01-19

- pdo: fix ErrorMsg() to detect errors correctly. Thx Jens.
- mssqlnative: added another check for $this->fields array exists.
- mssqlnative: bugs in FetchField() fixed. See PHPLens Issue No: 19024
- DBDate and DBTimeStamp had sql injection bug. Fixed. Thx Saithis
- mysql and mysqli: MetaTables() now identifies views and tables correctly.
- Added function adodb_time() to adodb-time.inc.php. Generates current time in unsigned integer format.

## 5.14 - 2011-09-08

- mysqli: fix php compilation bug.
- postgres: bind variables did not work properly. Fixed.
- postgres: blob handling bug in _decode. Fixed.
- ActiveRecord: if a null field was never updated, activerecord would still update the record. Fixed.
- ActiveRecord: 1 char length string never quoted. Fixed.
- LDAP: Connection string ldap:// and ldaps:// did not work. Fixed.

## 5.13 - 2011-08-15

- Postgres: Fix in 5.12 was wrong. Uses pg_unescape_bytea() correctly now in _decode.
- GetInsertSQL/GetUpdateSQL: Now $ADODB_QUOTE_FIELDNAMES allows you to define 'NATIVE', 'UPPER', 'LOWER'. If set to true, will default to 'UPPER'.
- mysqli: added support for persistent connections 'p:'.
- mssqlnative: ADODB_FETCH_BOTH did not work properly. Fixed.
- mssqlnative: return values for stored procedures where not returned! Fixed. See PHPLens Issue No: 18919
- mssqlnative: timestamp and fetchfield bugs fixed. PHPLens Issue: 18453

## 5.12 - 2011-06-30

- Postgres: Added information_schema support for postgresql.
- Postgres: Use pg_unescape_bytea() in _decode.
- Fix bulk binding with oci8. PHPLens Issue No: 18786
- oci8 perf: added wait evt monitoring. Also db cache advice now handles multiple buffer pools properly.
- sessions2: Fixed setFetchMode problem.
- sqlite: Some DSN connection settings were not parsed correctly.
- mysqli: now GetOne obeys $ADODB_GETONE_EOF;
- memcache: compress option did not work. Fixed. See PHPLens Issue No: 18899

## 5.11 - 2010-05-05

- mysql: Fixed GetOne() to return null if no records returned.
- oci8 perf: added stats on sga, rman, memory usage, and flash in performance tab.
- odbtp: Now you can define password in $password field of Connect()/PConnect(), and it will add it to DSN.
- Datadict: altering columns did not consider the scale of the column. Now it does.
- mssql: Fixed problem with ADODB_CASE_ASSOC causing multiple versions of column name appearing in recordset fields.
- oci8: Added missing & to refLob.
- oci8: Added obj->scale to FetchField().
- oci8: Now you can get column info of a table in a different schema, e.g. MetaColumns("schema.table") is supported.
- odbc_mssql: Fixed missing $metaDatabasesSQL.
- xmlschema: Changed declaration of create() to create($xmls) to fix compat problems. Also changed constructor adoSchema() to pass in variable instead of variable reference.
- ado5: Fixed ado5 exceptions to only display errors when $this->debug=true;
- Added DSN support to sessions2.inc.php.
- adodb-lib.inc.php. Fixed issue with _adodb_getcount() not using $secs2cache parameter.
- adodb active record. Fixed caching bug. See PHPLens Issue No: 18288.
- db2: fixed ServerInfo().
- adodb_date: Added support for format 'e' for TZ as in adodb_date('e')
- Active Record: If you have a field which is a string field (with numbers in) and you add preceding 0's to it the adodb library does not pick up the fact that the field has changed because of the way php's == works (dodgily). The end result is that it never gets updated into the database - fix by Matthew Forrester (MediaEquals). [matthew.forrester#mediaequals.com]
- Fixes RowLock() and MetaIndexes() inconsistencies. See PHPLens Issue No: 18236
- Active record support for postgrseql boolean. See PHPLens Issue No: 18246
- By default, Execute 2D array is disabled for security reasons. Set $conn->bulkBind = true to enable. See PHPLens Issue No: 18270. Note this breaks backward compat.
- MSSQL: fixes for 5.2 compat. PHPLens Issue No: 18325
- Changed Version() to return a string instead of a float so it correctly returns 5.10 instead of 5.1.

## 5.10 - 2009-11-10

- Fixed memcache to properly support $rs->timeCreated.
- adodb-ado.inc.php: Added BigInt support for PHP5. Will return float instead to support large numbers. Thx nasb#mail.goo.ne.jp.
- adodb-mysqli.inc.php: mysqli_multi_query is now turned off by default. To turn it on, use $conn->multiQuery = true; This is because of the risks of sql injection. See PHPLens Issue No: 18144
- New db2oci driver for db2 9.7 when using PL/SQL mode. Allows oracle style :0, :1, :2 bind parameters which are remapped to ? ? ?.
- adodb-db2.inc.php: fixed bugs in MetaTables. SYS owner field not checked properly. Also in $conn->Connect($dsn, null, null, $schema) and PConnect($dsn, null, null, $schema), we do a SET SCHEMA=$schema if successful connection.
- adodb-mysqli.inc.php: Now $rs->Close() closes all pending next resultsets. Thx Clifton mesmackgod#gmail.com
- Moved _CreateCache() from PConnect()/Connect() to CacheExecute(). Suggested by Dumka.
- Many bug fixes to adodb-pdo_sqlite.inc.php and new datadict-sqlite.inc.php. Thx Andrei B. [andreutz#mymail.ro]
- Removed usage of split (deprecated in php 5.3). Thx david#horizon-nigh.org.
- Fixed RowLock() parameters to comply with PHP5 strict mode in multiple drivers.

## 5.09 - 2009-06-25

- Active Record: You can force column names to be quoted in INSERT and UPDATE statements, typically because you are using reserved words as column names by setting ADODB_Active_Record::$_quoteNames = true;
- Added memcache and cachesecs to DSN. e.g.

    ``` php
    # we have a memcache servers mem1,mem2 on port 8888, compression=off and cachesecs=120
    $dsn = 'mysql://user:pwd@localhost/mydb?memcache=mem1,mem2:8888:0&cachesecs=120';
    ```

- Fixed up MetaColumns and MetaPrimaryIndexes() for php 5.3 compat. Thx http://adodb.pastebin.com/m52082b16
- The postgresql driver's OffsetDate() apparently does not work with postgres 8.3. Fixed.
- Added support for magic_quotes_sybase in qstr() and addq(). Thanks Eloy and Sam Moffat.
- The oci8 driver did not handle LOBs properly when binding. Fixed. See PHPLens Issue No: 17991.
- Datadict: In order to support TIMESTAMP with subsecond accuracy, added to datadict the new TS type. Supported by mssql, postgresql and oci8 (oracle). Also changed oci8 $conn->sysTimeStamp to use 'SYSTIMESTAMP' instead of 'SYSDATE'. Should be backwards compat.
- Added support for PHP 5.1+ DateTime objects in DBDate and DBTimeStamp. This means that dates and timestamps will be managed by DateTime objects if you are running PHP 5.1+.
- Added new property to postgres64 driver to support returning I if type is unique int called $db->uniqueIisR, defaulting to true. See PHPLens Issue No: 17963
- Added support for bindarray in adodb_GetActiveRecordsClass with SelectLimit in adodb-active-record.inc.php.
- Transactions now allowed in ado_access driver. Thx to petar.petrov.georgiev#gmail.com.
- Sessions2 garbage collection is now much more robust. We perform ORDER BY to prevent deadlock in adodb-sessions2.inc.php.
- Fixed typo in pdo_sqlite driver.

## 5.08a - 2009-04-17

- Fixes wrong version number string.
- Incorrect + in adodb-datadict.inc.php removed.
- Fixes missing OffsetDate() function in pdo. Thx paul#mantisforge.org.

## 5.08 - 2009-04-17

- adodb-sybase.inc.php driver. Added $conn->charSet support. Thx Luis Henrique Mulinari (luis.mulinari#gmail.com)
- adodb-ado5.inc.php. Fixed some bind param issues. Thx Jirka Novak.
- adodb-ado5.inc.php. Now has improved error handling.
- Fixed typo in adodb-xmlschema03.inc.php. See XMLS_EXISTING_DATA, line 1501. Thx james johnson.
- Made $inputarr optional for _query() in all drivers.
- Fixed spelling mistake in flushall() in adodb.inc.ophp.
- Fixed handling of quotes in adodb_active_record::doquote. Thx Jonathan Hohle (jhohle#godaddy.com).
- Added new index parameter to adodb_active_record::setdatabaseadaptor. Thx Jonathan Hohle
- Fixed & readcache() reference compat problem with php 5.3 in adodb.Thx Jonathan Hohle.
- Some minor $ADODB_CACHE_CLASS definition issues in adodb.inc.php.
- Added Reset() function to adodb_active_record. Thx marcus.
- Minor dsn fix for pdo_sqlite in adodb.inc.php. Thx Sergey Chvalyuk.
- Fixed adodb-datadict _CreateSuffix() inconsistencies. Thx Chris Miller.
- Option to delete old fields $dropOldFlds in datadict ChangeTableSQL($table, $flds, $tableOptions, $dropOldFlds=false) added. Thx Philipp Niethammer.
- Memcache caching did not expire properly. Fixed.
- MetaForeignKeys for postgres7 driver changed from adodb_movenext to $rs->MoveNext (also in 4.99)
- Added support for ldap and ldaps url format in ldap driver. E.g. ldap://host:port/dn?attributes?scope?filter?extensions

## 5.07 - 2008-12-26

- BeginTrans/CommitTrans/RollbackTrans return true/false correctly on success/failure now for mssql, odbc, oci8, mysqlt, mysqli, postgres, pdo.
- Replace() now quotes all non-null values including numeric ones.
- Postgresql qstr() now returns booleans as *true* and *false* without quotes.
- MetaForeignKeys in mysql and mysqli drivers had this problem: A table can have two foreign keys pointing to the same column in the same table. The original code will incorrectly report only the last column. Fixed. https://sourceforge.net/p/adodb/bugs/100/
- Passing in full ado connection string in $argHostname with ado drivers was failing in adodb5 due to bug. Fixed.
- Fixed memcachelib flushcache and flushall bugs. Also fixed possible timeCreated = 0 problem in readcache. (Also in adodb 4.992). Thanks AlexB_UK (alexbarnes#hotmail.com).
- Fixed a notice in adodb-sessions2.inc.php, in _conn(). Thx bober m.derlukiewicz#rocktech.remove_me.pl;
- ADOdb Active Record: Fixed some issues with incompatible fetch modes (ADODB_FETCH_ASSOC) causing problems in UpdateActiveTable().
- ADOdb Active Record: Added support for functions that support predefining one-to-many relationships:
   _ClassHasMany ClassBelongsTo TableHasMany TableBelongsTo TableKeyHasMany TableKeyBelongsTo_.
- You can also define your child/parent class in these functions, instead of the default ADODB_Active_Record. Thx Arialdo Martini & Chris R for idea.
- ADOdb Active Record: HasMany hardcoded primary key to "id". Fixed.
- Many pdo and pdo-sqlite fixes from Sid Dunayer [sdunayer#interserv.com].
- CacheSelectLimit not working for mssql. Fixed. Thx AlexB.
- The rs2html function did not display hours in timestamps correctly. Now 24hr clock used.
- Changed ereg* functions to use preg* functions as ereg* is deprecated in PHP 5.3. Modified sybase and postgresql drivers.

## 5.06 - 2008-10-16

- Added driver adodb-pdo_sqlite.inc.php. Thanks Diogo Toscano (diogo#scriptcase.net) for the code.
- Added support for [one-to-many relationships](https://adodb.org/dokuwiki/doku.php?id=v5:userguide:active_record#one_to_many_relations) with BelongsTo() and HasMany() in adodb_active_record.
- Added BINARY type to mysql.inc.php (also in 4.991).
- Added support for SelectLimit($sql,-1,100) in oci8. (also in 4.991).
- New $conn->GetMedian($table, $field, $where='') to get median account no. (also in 4.991)
- The rs2html() function in tohtml.inc.php did not handle dates with ':' in it properly. Fixed. (also in 4.991)
- Added support for connecting to oci8 using `$DB->Connect($ip, $user, $pwd, "SID=$sid");` (also in 4.991)
- Added mysql type 'VAR_STRING' to MetaType(). (also in 4.991)
- The session and session2 code supports setfetchmode assoc properly now (also in 4.991).
- Added concat support to pdo. Thx Andrea Baron.
- Changed db2 driver to use format `Y-m-d H-i-s` for datetime instead of `Y-m-d-H-i-s` which was legacy from odbc_db2 conversion.
- Removed vestigal break on adodb_tz_offset in adodb-time.inc.php.
- MetaForeignKeys did not work for views in MySQL 5. Fixed.
- Changed error handling in GetActiveRecordsClass.
- Added better support for using existing driver when $ADODB_NEWCONNECTION function returns false.
- In _CreateSuffix in adodb-datadict.inc.php, adding unsigned variable for mysql.
- In adodb-xmlschema03.inc.php, changed addTableOpt to include db name.
- If bytea blob in postgresql is null, empty string was formerly returned. Now null is returned.
- Changed db2 driver CreateSequence to support $start parameter.
- rs2html() now does not add nbsp to end if length of string > 0
- The oci8po FetchField() now only lowercases field names if ADODB_ASSOC_CASE is set to 0.
- New mssqlnative drivers for php. TQ Garrett Serack of M'soft. [Download](http://www.microsoft.com/downloads/details.aspx?FamilyId=61BF87E0-D031-466B-B09A-6597C21A2E2A&displaylang=en) mssqlnative extension. Note that this is still in beta.
- Fixed bugs in memcache support.
- You can now change the return value of GetOne if no records are found using the global variable $ADODB_GETONE_EOF. The default is null. To change it back to the pre-4.99/5.00 behaviour of false, set $ADODB_GETONE_EOF = false;
- In Postgresql 8.2/8.3 MetaForeignkeys did not work. Fixed William Kolodny William.Kolodny#gt-t.net

## 5.05 - 2008-07-11

Released together with [v4.990](changelog_v4.x.md#4990---11-jul-2008)

- Added support for multiple recordsets in mysqli , thanks to Geisel Sierote geisel#4up.com.br. See PHPLens Issue No: 15917
- Malcolm Cook added new Reload() function to Active Record. See PHPLens Issue No: 17474
- Thanks Zoltan Monori (monzol#fotoprizma.hu) for bug fixes in iterator, SelectLimit, GetRandRow, etc.
- Under heavy loads, the performance monitor for oci8 disables Ixora views.
- Fixed sybase driver SQLDate to use str_replace(). Also for adodb5, changed sybase driver UnixDate and UnixTimeStamp calls to static.
- Changed oci8 lob handler to use & reference `$this->_refLOBs[$numlob]['VAR'] = &$var`.
- We now strtolower the get_class() function in PEAR::isError() for php5 compat.
- CacheExecute did not retrieve cache recordsets properly for 5.04 (worked in 4.98). Fixed.
- New ADODB_Cache_File class for file caching defined in adodb.inc.php.
- Farsi language file contribution by Peyman Hooshmandi Raad (phooshmand#gmail.com)
- New API for creating your custom caching class which is stored in $ADODB_CACHE:

    ``` php
    include "/path/to/adodb.inc.php";
    $ADODB_CACHE_CLASS = 'MyCacheClass';
    class MyCacheClass extends ADODB_Cache_File
    {
        function writecache($filename, $contents,$debug=false) {...}
        function &readcache($filename, &$err, $secs2cache, $rsClass) { ...}
         :
    }
    $DB = NewADOConnection($driver);
    $DB->Connect(...); ## MyCacheClass created here and stored in $ADODB_CACHE global variable.
    $data = $rs->CacheGetOne($sql); ## MyCacheClass is used here for caching...
    ```

- Memcache supports multiple pooled hosts now. Only if none of the pooled servers
  can be contacted will a connect error be generated. Usage example below:

    ``` php
    $db = NewADOConnection($driver);
    $db->memCache = true; /// should we use memCache instead of caching in files
    $db->memCacheHost = array($ip1, $ip2, $ip3); /// $db->memCacheHost = $ip1; still works
    $db->memCachePort = 11211; /// this is default memCache port
    $db->memCacheCompress = false; /// Use 'true' to store the item compressed (uses zlib)
    $db->Connect(...);
    $db->CacheExecute($sql);
    ```

## 5.04 - 2008-02-13

Released together with [v4.98](changelog_v4.x.md#498---13-feb-2008)

- Fixed adodb_mktime problem which causes a performance bottleneck in $hrs.
- Added mysqli support to adodb_getcount().
- Removed MYSQLI_TYPE_CHAR from MetaType().

## 5.03 - 2008-01-22

Released together with [v4.97](changelog_v4.x.md#497---22-jan-2008)

- Active Record: $ADODB_ASSOC_CASE=1 did not work properly. Fixed.
- Modified Fields() in recordset class to support display null fields in FetchNextObject().
- In ADOdb5, active record implementation, we now support column names with spaces in them - we autoconvert the spaces to _ using __set(). Thx Daniel Cook. PHPLens Issue No: 17200
- Removed $arg3 from mysqli SelectLimit. See PHPLens Issue No: 16243. Thx Zsolt Szeberenyi.
- Changed oci8 FetchField, which returns the max_length of BLOB/CLOB/NCLOB as 4000 (incorrectly) to -1.
- CacheExecute would sometimes return an error on Windows if it was unable to lock the cache file. This is harmless and has been changed to a warning that can be ignored. Also adodb_write_file() code revised.
- ADOdb perf code changed to only log sql if execution time >= 0.05 seconds. New $ADODB_PERF_MIN variable holds min sql timing. Any SQL with timing value below this and is not causing an error is not logged.
- Also adodb_backtrace() now traces 1 level deeper as sometimes actual culprit function is not displayed.
- Fixed a group by problem with adodb_getcount() for db's which are not postgres/oci8 based.
- Changed mssql driver Parameter() from SQLCHAR to SQLVARCHAR: case 'string': $type = SQLVARCHAR; break.
- Problem with mssql driver in php5 (for adodb 5.03) because some functions are not static. Fixed.

## 5.02 - 2007-09-24

Released together with [v4.96](changelog_v4.x.md#496---24-sept-2007)

- ADOdb perf for oci8 now has non-table-locking code when clearing the sql. Slower but better transparency. Added in 4.96a and 5.02a.
- Fix adodb count optimisation. Preg_match did not work properly. Also rewrote the ORDER BY stripping code in _adodb_getcount(), adodb-lib.inc.php.
- SelectLimit for oci8 not optimal for large recordsets when offset=0. Changed $nrows check.
- Active record optimizations. Added support for assoc arrays in Set().
- Now GetOne returns null if EOF (no records found), and false if error occurs. Use ErrorMsg()/ErrorNo() to get the error.
- Also CacheGetRow and CacheGetCol will return false if error occurs, or empty array() if EOF, just like GetRow and GetCol.
- Datadict now allows changing of types which are not resizable, eg. VARCHAR to TEXT in ChangeTableSQL. -- Mateo TibaquirÃ¡
- Added BIT data type support to adodb-ado.inc.php and adodb-ado5.inc.php.
- Ldap driver did not return actual ldap error messages. Fixed.
- Implemented GetRandRow($sql, $inputarr). Optimized for Oci8.
- Changed adodb5 active record to use static SetDatabaseAdapter() and removed php4 constructor. Bas van Beek bas.vanbeek#gmail.com.
- Also in adodb5, changed adodb-session2 to use static function declarations in class. Thx Daniel Berlin.
- Added "Clear SQL Log" to bottom of Performance screen.
- Sessions2 code echo'ed directly to the screen in debug mode. Now uses ADOConnection::outp().
- In mysql/mysqli, qstr(null) will return the string `null` instead of empty quoted string `''`.
- postgresql optimizeTable in perf-postgres.inc.php added by Daniel Berlin (mail#daniel-berlin.de)
- Added 5.2.1 compat code for oci8.
- Changed @@identity to SCOPE_IDENTITY() for multiple mssql drivers. Thx Stefano Nari.
- Code sanitization introduced in 4.95 caused problems in European locales (as float 3.2 was typecast to 3,2). Now we only sanitize if is_numeric fails.
- Added support for customizing ADORecordset_empty using $this->rsPrefix.'empty'. By Josh Truwin.
- Added proper support for ALterColumnSQL for Postgresql in datadict code. Thx. Josh Truwin.
- Added better support for MetaType() in mysqli when using an array recordset.
- Changed parser for pgsql error messages in adodb-error.inc.php to case-insensitive regex.

## 5.01 - 2007-05-17

Released together with [v4.95](changelog_v4.x.md#495---17-may-2007)

- CacheFlush debug outp() passed in invalid parameters. Fixed.
- Added Thai language file for adodb. Thx Trirat Petchsingh rosskouk#gmail.com and Marcos Pont
- Added zerofill checking support to MetaColumns for mysql and mysqli.
- CacheFlush no longer deletes all files/directories. Only *.cache files deleted.
- DB2 timestamp format changed to `var $fmtTimeStamp = "'Y-m-d-H:i:s'";`
- Added some code sanitization to AutoExecute in adodb-lib.inc.php.
- Due to typo, all connections in adodb-oracle.inc.php would become persistent, even non-persistent ones. Fixed.
- Oci8 DBTimeStamp uses 24 hour time for input now, so you can perform string comparisons between 2 DBTimeStamp values.
- Some PHP4.4 compat issues fixed in adodb-session2.inc.php
- For ADOdb 5.01, fixed some adodb-datadict.inc.php MetaType compat issues with PHP5.
- The $argHostname was wiped out in adodb-ado5.inc.php. Fixed.
- Adodb5 version, added iterator support for adodb_recordset_empty.
- Adodb5 version,more error checking code now will use exceptions if available.


[Unreleased]: https://github.com/adodb/adodb/compare/v5.21.3...master

[5.21.3]: https://github.com/adodb/adodb/compare/v5.21.2...v5.21.3
[5.21.2]: https://github.com/adodb/adodb/compare/v5.21.1...v5.21.2
[5.21.1]: https://github.com/adodb/adodb/compare/v5.21.0...v5.21.1
[5.21.0]: https://github.com/adodb/adodb/compare/v5.21.0-rc.1...v5.21.0
[5.21.0-rc.1]: https://github.com/adodb/adodb/compare/v5.21.0-beta.1...v5.21.0-rc.1
[5.21.0-beta.1]: https://github.com/adodb/adodb/compare/v5.20.20...v5.21.0-beta.1

[5.20.20]: https://github.com/adodb/adodb/compare/v5.20.19...v5.20.20
[5.20.19]: https://github.com/adodb/adodb/compare/v5.20.18...v5.20.19
[5.20.18]: https://github.com/adodb/adodb/compare/v5.20.17...v5.20.18
[5.20.17]: https://github.com/adodb/adodb/compare/v5.20.16...v5.20.17
[5.20.16]: https://github.com/adodb/adodb/compare/v5.20.15...v5.20.16
[5.20.15]: https://github.com/adodb/adodb/compare/v5.20.14...v5.20.15
[5.20.14]: https://github.com/adodb/adodb/compare/v5.20.13...v5.20.14
[5.20.13]: https://github.com/adodb/adodb/compare/v5.20.12...v5.20.13
[5.20.12]: https://github.com/adodb/adodb/compare/v5.20.11...v5.20.12
[5.20.11]: https://github.com/adodb/adodb/compare/v5.20.10...v5.20.11
[5.20.10]: https://github.com/adodb/adodb/compare/v5.20.9...v5.20.10
[5.20.9]: https://github.com/adodb/adodb/compare/v5.20.8...v5.20.9
[5.20.8]: https://github.com/adodb/adodb/compare/v5.20.7...v5.20.8
[5.20.7]: https://github.com/adodb/adodb/compare/v5.20.6...v5.20.7
[5.20.6]: https://github.com/adodb/adodb/compare/v5.20.5...v5.20.6
[5.20.5]: https://github.com/adodb/adodb/compare/v5.20.4...v5.20.5
[5.20.4]: https://github.com/adodb/adodb/compare/v5.20.3...v5.20.4
[5.20.3]: https://github.com/adodb/adodb/compare/v5.20.2...v5.20.3
[5.20.2]: https://github.com/adodb/adodb/compare/v5.20.1...v5.20.2
[5.20.1]: https://github.com/adodb/adodb/compare/v5.20.0...v5.20.1
[5.20.0]: https://github.com/adodb/adodb/compare/v5.19...v5.20.0
