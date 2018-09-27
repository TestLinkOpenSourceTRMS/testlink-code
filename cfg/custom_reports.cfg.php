<?php
# PDS - Customization

define('FORMAT_PDS', 7);

$tlCfg->reports_formats = array(FORMAT_HTML => 'format_html', FORMAT_PDS => 'format_pds');

/** Mime Content Type */
$tlCfg->reports_applications = array(FORMAT_HTML => 'text/html',FORMAT_XLS => 'application/vnd.ms-excel', 
                   FORMAT_MSWORD => 'application/vnd.ms-word', FORMAT_PDS => 'application/vnd.ms-word');

/** Report file extension */
$tlCfg->reports_file_extension = array(FORMAT_HTML => 'html',FORMAT_XLS => 'xls',FORMAT_MSWORD => 'doc', FORMAT_PDS => 'doc');


$tlCfg->reports_list['results_flat_custom'] = array( 
	'title' => 'link_report_test_flat_custom',
	'url' => 'lib/results/Custom/resultsTCFlat.php',
	'enabled' => 'all', 
	'directLink' => '%slnl.php?apikey=%s&tproject_id=%s&tplan_id=%s&type=results_flat',
	'format' => 'format_html'
);

$tlCfg->reports_list['results_matrix_custom'] = array( 
	'title' => 'link_report_test_matrix_custom',
	'url' => 'lib/results/Custom/resultsTCMatrix.php',
	'enabled' => 'all', 
	'directLink' => '%slnl.php?apikey=%s&tproject_id=%s&tplan_id=%s&type=results_flat',
	'format' => 'format_pds'
);

$tlCfg->reports_list['results_regression_trace_matrix_custom'] = array( 
	'title' => 'link_report_regression_trace_matrix',
	'url' => 'lib/results/Custom/resultsTCRegressionTraceMatrix.php',
	'enabled' => 'all', 
	'directLink' => '%slnl.php?apikey=%s&tproject_id=%s&tplan_id=%s&type=results_flat',
	'format' => 'format_pds'
);

$tlCfg->reports_list['results_docx_test_report_onbuild_custom'] = array(
    'title' => 'results_docx_test_report_onbuild_custom',
    'url' => 'lib/results/Custom/printDocxOptions.php?type=' .DOC_TEST_PLAN_EXECUTION_ON_BUILD,
    'enabled' => 'all',
    'directLink' => '%slnl.php?apikey=%s&tproject_id=%s&tplan_id=%s&type=results_flat',
    'format' => 'format_pds'
);

?>