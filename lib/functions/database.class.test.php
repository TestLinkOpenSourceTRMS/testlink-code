<?php
  /**
   *  20060615 - kevinlevy - test class which displays function calls
   *  and results into database.class.php
   */

require_once('../../config.inc.php');
require_once('common.php');
require_once('database.class.php');

print "<h3>/lib/functions/database.class.test.php</h3>";
print "author : Kevin Levy <BR>";
print "last updated 20060630 <BR>";
print "WORK IN PROGRESS <BR>";

print "<BR>This page displays the functions in /lib/functions/database.class.php and examples of their usage.  This page will first call an initialization method, then the testplan class will be instantiated, then we will retrieve the current testplan and testproject ids.  Once this initial information has been gathered, each method of the testplan class will be used and we will inspect the results.<BR>";

print "============================================== <BR> ";

print "<h3>MUST BE DONE 1st : initialize the page and \$db reference</h3>";
print "testlinkInitPage(\$db) <BR>";
testlinkInitPage($db);

print "============================================== <BR>";

print "function microtime_float() <BR>";

print "function database($db_type) <BR>";

print "function get_dbmgr_object() <BR>";

print "function connect(\$p_dsn, \$p_hostname = null, \$p_username = null, \$p_password = null, \$p_database_name = null) <BR>";

print "function exec_query(\$p_query, \$p_limit = -1, \$p_offset = -1) <BR>";

print "function fetch_array( \&\$p_result) <BR>";

print "function db_result(\$p_result, \$p_index1=0, \$p_index2=0) <BR>";

print "function insert_id(\$p_table = null) <BR>";

print "function db_is_pgsql() <BR>";

print "function db_table_exists(\$p_table_name) <BR>";

print "function db_field_exists(\$p_field_name, \$p_table_name) <BR>";

print "function key_exists_on_field(\$p_table, \$p_field, \$p_key) <BR>";

print "function prepare_string(\$p_string) <BR>";

print "function prepare_int(\$p_int) <BR>";

print "function prepare_bool (\$p_bool) <BR>";

print "function db_now() <BR>";

print "function db_timestamp(\$p_date = null) <BR>";

print "function db_unixtimestamp(\$p_date = null) <BR>";

print "function count_queries() <BR>";

print "function count_unique_queries() <BR>";

print "function time_queries() <BR>";

print "function close() <BR>";

print "function error_num() <BR>";

print "function error_msg() <BR>";

print "function error(\$p_query = null) <BR>";

print "function num_rows(\$p_result) <BR>";

print "function affected_rows() <BR>";

print "function fetchFirstRowSingleColumn(\$query, \$column) <BR>";

print "function fetchFirstRow(\$query) <BR>";

print "function fetchColumnsIntoArray(\$query, \$column) <BR>";

print "function fetchRowsIntoMap(\$query, \$column) <BR>";

print "function fetchColumnsIntoMap(\$query, \$column1, \$column2) <BR>";

print "function get_version_info() <BR>";

print "function get_recordset(\$sql) <BR>";

print "function fetchArrayRowsIntoMap(\$query, \$column) <BR>";

print "function build_sql_create_db(\$db_name) <BR>";

?>
