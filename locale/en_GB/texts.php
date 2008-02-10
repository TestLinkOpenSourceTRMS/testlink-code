<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 *
 * Filename $RCSfile: texts.php,v $
 *
 * @version $Revision: 1.2 $
 * @modified $Date: 2008/02/10 23:30:01 $ by $Author: havlat $
 * @author Martin Havlat and reviewers from TestLink Community
 *
 * Scope: 
 * English (en_GB) texts for help/instruction pages. Strings for dynamic pages
 * are stored in strings.txt pages.
 * 
 * Here we are defining GLOBAL variables. To avoid override of other globals
 * we are using reserved prefixes:
 * $TLS_help[<key>] and $TLS_help_title[<key>]
 * or 
 * $TLS_instruct[<key>] and $TLS_instruct_title[<key>] 
 *
 *
 * Revisions history is not stored for the file
 *       
**/

// ------------------------------------------------------------------------------------------
$TLS_htmltext_title['assignReqs']	= "Assign Requirements to Test Case";
$TLS_htmltext['assignReqs'] 		= "<h2>Purpose:</h2>
<p>This feature allows to set relations between requirements 
and test cases. A designer could define relations 0..n to 0..n. I.e. One test case
could be assigned to none, one or more test cases and vice versa.</p>

<h2>Get Started:</h2>
<ol>
	<li>Choose an Test Case in tree at the left. The combo box with list of Requirements 
	Specifications is shown at the top of workarea.</li>
	<li>Choose a Requirements Specification. TestLink automatically reload the page.</li>
	<li>A middle block of workarea lists all requirements (from choosen Specification), which 
	are connected with the test case. Bottom block 'Available Requirements' lists all 
	requirements which have not relation
	to the current test case. A designer could mark requirements which are covered by this 
	test case and then click the button 'Assign'. These new assigned test case are shown in 
	the middle block 'Assigned Requirements'.</li>
</ol>";


// ------------------------------------------------------------------------------------------
$TLS_htmltext_title['editTc']	= "Test Specification";
$TLS_htmltext['editTc'] 		= "<h2>Purpose:</h2>
<p>The <span class=\"help\" onclick=\"javascript:open_help_window('glosary','$locale');\">Test
Specification</span> is a place where a user can view and edit all of the
existing <span class=\"help\" 
onclick=\"javascript:open_help_window('glosary','$locale');\">Test project</span>, test suite,and <span class=\"help\" 
onclick=\"javascript:open_help_window('glosary','$locale');\">Test case</span> information.
A user can look at a different versions of test cases. </p>

<h2>Get Started:</h2>

<ol>
	<li>Select Test project name in navigation pane. (You can change test project,selectable in top right corner.)</li>
	<li>Create a new Test suites. Test Suite structualize your Test Specification according your need. 
			You can differ components, functional and non-functional tests, etc.</li>
	<li>Create a new Test cases into active Test Suite. Test case specifies particular testing scenario, expected results and more</li>
	<li>Navigate via the tree view on the left side and edit data. Test cases stores own history.</li>
	<li>Assign your created Test Specification to <span class=\"help\" onclick=
	\"javascript:open_help_window('glosary','$locale');\">Test Plan</span> when Test cases are ready.</li>
</ol>

<p>TestLink offers organize test cases into N levels of test suites.
You can describe a content of test suites.
This information could be printed together with test cases.</p>";


// ------------------------------------------------------------------------------------------
$TLS_htmltext_title['searchTc']	= "Test Case Search Page";
$TLS_htmltext['searchTc'] 		= "<h2>Purpose:</h2>

<p>Navigation according to keywords and/or searched strings. The search is not
case sensitive. Result include just test cases from actual Test Project.</p>

<h2>To search:</h2>

<ol>
	<li>Write searched string to an appropriate box. Left blank unused fields in form.</li>
	<li>Choose required keyword or left value 'Not applied'.</li>
	<li>Click the Search button.</li>
	<li>All fulfilled test cases are shown. You can modify test cases via 'Title' link.</li>
</ol>";


// ------------------------------------------------------------------------------------------
$TLS_htmltext_title['printTestSpec']	= "Print Test Specification"; //printTC.html
$TLS_htmltext['printTestSpec'] 			= "<h2>Purpose:</h2>
<p>From here you can print a single test case, all the test cases within a test suite, 
or all the test cases in a test project or plan.</p>
<h2>Get Started:</h2>
<ol>
<li>
<p>Select the parts of the test cases you want to display, and then click on a test case, test suite, or the test project.
A printable page will be displayed.</p>
</li>
<li><p>Use the \"Show As\" drop-box in the navigation pane to specify whether you want the information displayed as HTML or in a 
Microsoft Word document. See <span class=\"help\" onclick=\"javascript:open_help_window('printFilter',
'{$locale}');\">help</span> for more information.</p>
</li>
<li><p>Use your browser's print functionality to actually print the information.<br />
 <i>Note: Make sure to only print the right-hand frame.</i></p></li>
</ol>";


// ------------------------------------------------------------------------------------------
$TLS_htmltext_title['reqSpecMgmt']	= "Requirements Specification Design"; //printTC.html
$TLS_htmltext['reqSpecMgmt'] 			= "<p>You can manage Requirement Specification documents.</p>

<h2>Requirements Specification</h2>

<p>Requirements are grouped by <b>Requirements Specification document</b>, which is related to 
Test Project.<br /> TestLink doesn't support (yet) versions for both Requirements Specification  
and Requirements itself. So, version of document should be added after 
a Specification <b>Title</b>.
An user can add simple description or notes to <b>Scope</b> field.</p> 

<p><b><a name='total_count'>Total count</a></b> of all Requirements serves for 
evaluation Req. coverage in case that not all requirements are added to TestLink. 
The value <b>n/a</b> means that current count of requirements is used
for metrics.</p> 
<p><i>E.g. SRS includes 200 requirements but only 50 are added in TestLink. Test 
coverage is 25% (if all these added requirements will be tested).</i></p>

<h2><a name='req'>Requirements</a></h2>

<p>Click on title of a created Requirements Specification. You can create, edit, delete
or import requirements for the document. Each requirement has title, scope and status.
Status should be 'Normal' or 'Not testable'. Not testable requirements are not counted
to metrics. This parameter should be used for both unimplemented features and 
wrong designed requirements.</p> 

<p>You can create new test cases for requirements by using multi action with checked 
requirements within the specification screen. These Test Cases are created into Test Suite
with name defined in configuration <i>(default is: \$g_req_cfg->default_testsuite_name = 
'Test suite created by Requirement - Auto';)</i>. Title and Scope are copied to these Test cases.</p>";


// ------------------------------------------------------------------------------------------
$TLS_htmltext_title['keywordsAssign']	= "Keyword Assignment";
$TLS_htmltext['keywordsAssign'] 			= "<h2>Purpose:</h2>
<p>The Keyword Assignment page is the place where users can batch 
assign keywords to the existing Test Suite or Test Case</p>

<h2>To Assign Keywords:</h2>
<ol>
	<li>Select a Test Suite, or Test Case on the tree view 
		on the left.</li>
	<li>The top most box that shows up on the right hand side will 
		allow you to assign available keywords to every single test 
		case.</li>
	<li>The selections below allow you to assign cases at a more 
		granular level.</li>
</ol>

<h2>Important Information Regarding Keyword Assignments in Test Plans:</h2>
<p>Keyword assignments you make to the specification will only effect test cases 
in your Test plans if and only if the test plan contains the latest version of the Test case. 
Otherwise if a test plan contains older versions of a test case, assignments you make 
now WILL NOT appear in the test plan.
</p>
<p>TestLink uses this approach so that older versions of test cases in test plans are not effected 
by keyword assignments you make to the most recent version of the test case. If you want your 
test cases in your test plan to be updated, first verify they are up to date using the 'Update 
Modified Test Cases' functionality BEFORE making keyword assignments.</p>";


// ------------------------------------------------------------------------------------------
$TLS_htmltext_title['executeTest']	= "Test Case Execution";
$TLS_htmltext['executeTest'] 		= "<h2>Purpose:</h2>

<p>Allows user to execute Test cases. User can assign Test result
to Test Case for Build. See <span class='help' 
onclick=\"javascript:open_popup('./execFilter.html');\">help</span>
 for more information about filter and settings.</p>

<h2>Get started:</h2>

<ol>
	<li>User must have defined a Build for the Test Plan.</li>
	<li>Select a Build from the drop down box and the Update button in the navigation pane.</li>
	<li>Click on a test suite to see all of its test suites and all of its test cases.</li>
	<li>Fill out the test case result and any applicable notes or bugs.</li>
</ol>";


// ------------------------------------------------------------------------------------------
$TLS_htmltext_title['showMetrics']	= "Description of Test Reports and Metrics";
$TLS_htmltext['showMetrics'] 		= "<p>Reports are related to a Test Plan (defined in top of navigator). This Test Plan could differs from the 
current Test Plan for execution. You can also select Report format:</p>
<ul>
<li><b>Normal</b> - report is displayed in web page</li>
<li><b>MS Excel</b> - report exported to Microsoft Excel</li>
<li><b>HTML Email</b> - report is emailed to user's email address</li>
<li><b>Charts</b> - report include graphs (flash technology)</li>
</ul>

<p>The print button activate print of a report only (without navigation).</p>
<p>There are several seperate reports to choose from, their purpose and function are explained below.</p>

<h3>General Test Plan Metrics</h3>
<p>This page shows you only the most current status of a Test plan by test suite, owner, and keyword. 
The most 'current status' is determined by the most recent build test cases were executed on.  For 
instance, if a test case was executed over multiple builds, only the latest result is taken into account.</p> 
	
<p>'Last Test Result' is a concept used in many reports, and is determined as follows:</p>
<ul> 
<li>The order in which builds are added to a Test Plan determines which build is most recent. The results 
from the most recent build will take precendence over older builds. For example, if you mark a test as 
'fail' in build 1, and mark it as 'pass' in build 2, it's latest result will be 'pass'.</li>
<li>If a test case is executed mulitple times on the same build, the most recent execution will take 
precedence.  For example, if build 3 is released to your team and tester 1 marks it as 'pass' at 2PM, 
and tester 2 marks it as 'fail' at 3PM - it will appear as 'fail'.</li>
<li>Test cases listed as 'not run' against a build are not taken into account. For example, if you mark 
a case as 'pass' in build 1, and don't execute it in build 2, it's last result will be considered as 
'pass'.</li>
</ul>
<p>The following tables are displayed:</p>
<ul>
	<li><b>Results by top level Test Suites</b>
	Lists the results of each top level suite. Total cases, passed, failed, blocked, not run, and percent 
	completed are listed. A 'completed' test case is one that has been marked pass, fail, or block.  
	Results for top level suites include all children suites.</li>
	<li><b>Results By Keyword</b>
	Lists all keywords that are assigned to cases in the current test plan, and the results associated 
	with them.</li>
	<li><b>Results by owner</b>
	Lists each owner that has test cases assigned to them in the current test plan. Test cases which 
	are not assigned are tallied under the 'unassigned' heading.</li> 
</ul>

<h3>The Overall Build Status</h3>
<p>Lists the execution results for every build. For each build, the total test cases, total pass, 
% pass, total fail, % fail, blocked, % blocked, not run, %not run.  If a test case has been executed 
twice on the same build, the most recent execution will be taken into account.</p>

<h3>Query Metrics</h3>
<p>This report consists of a query form page, and a query results page which contains the queried data.
Query Form Page presents with a query page with 4 controls. Each control is set to a default which 
maximizes the number of test cases and builds the query should be performed against. Altering the controls 
allows the user to filter the results and generate specific reports for specific owner, keyword, suite, 
and build combinations.</p>

<ul>
<li><b>keyword</b> 0->1 keywords can be selected. By default - no keyword is selected. If a keyword is not 
selected, then all test cases will be considered regardless of keyword assignments. Keywords are assigned 
in the test specification or Keyword Management pages.  Keywords assigned to test cases span all test plans, 
and span across all versions of a test case.  If you are interested in the results for a specific keyword 
you would alter this control.</li>
<li><b>owner</b> 0->1 owners can be selected. By default - no owner is selected. If an owner is not selected, 
then all test cases will be considered regardless of owner assignment.  Currently there is no functionality 
to search for 'unassigned' test cases.  Ownership is assigned through the 'Assign Test Case execution' page, 
and is done on a per test plan basis.  If you are interested in the work done by a specific tester you would 
alter this control.</li>
<li><b>top level suite</b> 0->n top level suites can be selected. By default - all suites are selected.  
Only suites that are selected will be queried for result metrics.  If you are only intested in the results 
for a specific suite you would alter this control.</li>
<li><b>Builds</b> 1->n builds can be selected.  By default - all builds are selected.  Only executions 
performed on builds you select will be taken into account when producing metrics.  For example - if you 
wanted to see how many test cases were executed on the last 3 builds - you would alter this control. 
Keyword, owner, and top level suite selections will dictate the number of test cases from your test plan 
are used to computate per suite and per test plan metrics.  For example, if you select owner = 'Greg', 
Keyword='Priority 1', and all available test suites - only Priority 1 test cases assigned to Greg will be 
considered. The '# of Test Cases' totals you will see on the report will be influenced by these 3 controls.
Build selections will influence if a case is considered 'pass', 'fail', 'blocked', or 'not run'.  Please 
refer to 'Last Test Result' rules as they appear above.</li>
</ul>
<p>Press the 'submit' button to proceed with the query and display the output page.</p>

<p>Query Report Page will display: </p>
<ol>
<li>the query parameters used to create report</li>
<li>totals for the entire test plan</li>
<li>a per suite breakdown of totals (sum / pass / fail / blocked / not run) and all executions performed 
on that suite.  If a test case has been executed more than once on multiple builds - all executions will be 
displayed that were recorded against the selected builds. However, the summary for that suite will only 
include the 'Last Test Result' for the selected builds.</li>
</ol>

<h3>Blocked, Failed, and  Not Run Test Case Reports</h3>
<p>These reports show all of the currently blocked, failing, or not run test cases.  'Last test Result' 
logic (which is described above under General Test Plan Metrics) is again employed to determine if 
a test case should be considered blocked, failed, or not run.  Blocked and failed test case reports will 
display the associated bugs if the user is using an integrated bug tracking system.</p>

<h3>Test Report</h3>
<p>View status of every test case on every build. The most recent execution result will be used 
if a test case was executed multiple times on the same build. It is recommended to export this report 
to Excel format for easier browsing if a large data set is being used.</p>

<h3>Charts - General Test Plan Metrics</h3>
<p>'Last test Result' logic is used for all four charts that you will see. The graphs are animated to help 
the user visualize the metrics from the current test plan. The four charts provide are :</p>
<ul><li>Pie chart of overall pass / fail / blocked / and not run test cases</li>
<li>Bar chart of Results by Keyword</li>
<li>Bar chart of Results By Owner</li>
<li>Bar chart of Results By Top Level Suite</li>
</ul>
<p>The bars in the bar charts are colored such that the user can identify the approximate number of 
pass, fail, blocked, and not run cases.</p>
<p><i>This report page requires your browser have a flash plugin (by http://www.maani.us) to display 
results in a graphical format.</i></p>

<h3>Total Bugs For Each Test Case</h3>
<p>This report shows each test case with all of the bugs filed against it for the entire project. 
This report is only available if a Bug Tracking System is connected.</p>";


// ------------------------------------------------------------------------------------------
$TLS_htmltext_title['planAddTC']	= "Add Test case to Test Plan"; // testSetAdd
$TLS_htmltext['planAddTC'] 			= "<h2>Purpose:</h2>
<p>Allows user (with lead level permissions) to add test cases into a Test plan.</p>

<h2>To add Test cases:</h2>
<ol>
	<li>Click on a test suite to see all of its test suites and all of its test cases.</li>
	<li>When you are done click the 'Add Test Cases' button to import the test cases. 
		Note: Is not possibile to add the same test case multiple times.</li>
	<li>You can also use filter according to Keywords.</li>
</ol>"; 


// ------------------------------------------------------------------------------------------
$TLS_htmltext_title['planRemoveTC']	= "Remove Test case from Test Plan"; 
$TLS_htmltext['planRemoveTC'] 		= "<h2>Purpose:</h2>

<p>Allows user to remove Test cases from Test Plan.</p>
<p>Old Test Case results will be REMOVED.</p>

<h2>Getting Started</h2>

<ol>
	<li>Click on a Test suite to show all its test cases or a single Test Case</li>
	<li>Set checkbox.</li>
	<li>Submit the page.</li>
</ol>";


// ------------------------------------------------------------------------------------------

?>