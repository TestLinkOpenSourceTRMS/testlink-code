<?php
/** -------------------------------------------------------------------------------------
 * ♔ TestLink Open Source Project - http://testlink.sourceforge.net/
 * 
 * Filename $RCSfile: description.php,v $
 * @version $Revision: 1.3 $
 * @modified $Date: 2010/06/24 17:25:53 $ $Author: asimon83 $
 * @author Martin Havlat
 *
 * LOCALIZATION:
 * === English (en_GB) strings === - default development localization (World-wide English)
 *
 * @ABSTRACT
 * The file contains global variables with html text. These variables are used as 
 * HELP or DESCRIPTION. To avoid override of other globals we are using "Test Link String" 
 * prefix '$TLS_hlp_' or '$TLS_txt_'. This must be a reserved prefix.
 * 
 * Contributors:
 * Add your localization to TestLink tracker as attachment to update the next release
 * for your language.
 *
 * No revision is stored for the the file - see CVS history
 * The initial data are based on help files stored in gui/help/<lang>/ directory. 
 * This directory is obsolete now. It serves as source for localization contributors only. 
 *
 * ----------------------------------------------------------------------------------- */

// printFilter.html
$TLS_hlp_generateDocOptions = "<h2>Options for a generated document</h2>

<p>This table allows the user to filter test cases before they are viewed. If
selected (checked) the data will be shown. In order to change the data
presented, check or uncheck, click on Filter, and select the desired data
level from the tree.</p>

<p><b>Document Header:</b> Users can filter out Document Header information. 
Document Header information includes: Introduction, Scope, References, 
Test Methodology, and Test Limitations.</p>

<p><b>Test Case Body:</b> Users can filter out Test Case Body information. Test Case Body information
includes: Summary, Steps, Expected Results, and Keywords.</p>

<p><b>Test Case Summary:</b> Users can filter out Test Case Summary information from the Test Case Title,
however, they cannot filter out Test Case Summary information from the Test
Case Body. Test Case Summary has only been partially separated from Test Case
Body in order to support viewing Titles with a brief Summary and the absence of
Steps, Expected Results, and Keywords. If a user decides to view Test Case
Body, Test Case Summary will always be included.</p>

<p><b>Table of Content:</b> TestLink inserts list of all titles with internal hypertext links if checked.</p>

<p><b>Output format:</b> There are two possibilities: HTML and MS Word. Browser calls MS word component 
in second case.</p>";

// testPlan.html
$TLS_hlp_testPlan = "<h2>Test Plan</h2>

<h3>General</h3>
<p>A test plan is a systematic approach to testing a system such as software. You can organize testing activity with 
particular builds of product in time and trace results.</p>

<h3>Test Execution</h3>
<p>This section is where users can execute test cases (write test results) and 
print Test case suite of the Test Plan. This section is where users can track 
the results of their test case execution.</p> 

<h2>Test Plan Management</h2>
<p>This section, which is only lead accessible, allows users to administrate test plans. 
Administering test plans involves creating/editing/deleting plans, 
adding/editing/deleting/updating test cases in plans, creating builds as well as defining who can 
see which plan.<br />
Users with lead permissions may also set the priority/risk and ownership of 
Test case suites (categories) and create testing milestones.</p> 

<p>Note: It is possible that users may not see a dropdown containing any Test plans. 
In this situation all links (except lead enabled ones) will be unlinked. If you 
are in this situation you must contact a lead or admin to grant you the proper 
project rights or create a Test Plan for you.</p>"; 

// custom_fields.html
$TLS_hlp_customFields = "<h2>Custom Fields</h2>
<p>Following are some facts about the implementation of custom fields:</p>
<ul>
<li>Custom fields are defined system wide.</li>
<li>Custom fields are linked to a type of element (Test Suite, Test Case)</li>
<li>Custom fields can be linked to multiple Test Projects.</li>
<li>The sequence of displaying custom fields can be different per Test Project.</li>
<li>Custom fields can be turned inactive for an specific Test Project.</li>
<li>Number of custom fields is not restricted.</li>
</ul>

<p>The definition of a custom field includes the following logical
attributes:</p>
<ul>
<li>Custom field name</li>
<li>Caption variable name (eg: This is the value that is
supplied to lang_get() API , or displayed as-is if not found in language file).</li>
<li>Custom field type (string, numeric, float, enum, email)</li>
<li>Enumeration possible values (eg: RED|YELLOW|BLUE), applicable to list, multiselection list 
and combo types.<br />
<i>Use the pipe ('|') character to
separate possible values for an enumeration. One of the possible values
can be an empty string.</i>
</li>
<li>Default value: NOT IMPLEMENTED YET</li>
<li>Minimum/maximum length for the custom field value (use 0 to disable). (NOT IMPLEMENTED YET)</li>
<li>Regular expression to use for validating user input
(use <a href=\"http://au.php.net/manual/en/function.ereg.php\">ereg()</a>
syntax). <b>(NOT IMPLEMENTED YET)</b></li>
<li>All custom fields are currently saved to a field of type VARCHAR(255) in the database.</li>
<li>Display on test specification.</li>
<li>Enable on test specification. User can change the value during Test Case Specification Design</li>
<li>Display on test execution.</li>
<li>Enable on test execution. User can change the value during Test Case execution</li>
<li>Display on test plan design.</li>
<li>Enable on test plan design. User can change the value during Test Plan design (add test cases to test plan)</li>
<li>Available for. User choose to what kind of item the field belows.</li>
</ul>
";

// execMain.html
$TLS_hlp_executeMain = "<h2>Executing Test Cases</h2>
<p>Allows users to 'execute' test cases. Execution itself is merely
assigning a test case a result (pass,fail,blocked) against a selected build.</p>
<p>Access to a bug tracking system could be configured. User can directly add a new bugs
and browse exesting ones then.</p>";

//bug_add.html
$TLS_hlp_btsIntegration = "<h2>Add Bugs to Test Case</h2>
<p><i>(only if it is configured)</i>
TestLink has a very simple integration with Bug Tracking Systems (BTS),
not being able either send a bug creationg request to BTS, neither get back the bug id.
The integration is done using links to pages on BTS, that calls the following features:
<ul>
	<li>Insert new bug.</li>
	<li>Display existent bug info. </li>
</ul>
</p>  

<h3>Process to add a bug</h3>
<p>
   <ul>
   <li>Step 1: use the link to open BTS to insert a new bug. </li>
   <li>Step 2: write down the BUGID assigned by BTS.</li>
   <li>Step 3: write BUGID on the input field.</li>
   <li>Step 4: use add bug button.</li>
   </ul>  

After closing the add bug page, you will see relevant bug data on the execute page.
</p>";

// execFilter.html
$TLS_hlp_executeFilter = "<h2>Setup Filter and Build for test execution</h2>

<p>The left pane consists from navigator through test cases assigned to the current " .
"Test plan and table with settings and filter. These filters allows the user " .
"to refine offered set of test cases before they are executed." .
"Setup your filter, press the \"Apply\" button and select appropriate Test Case " .
"from tree menu.</p>

<h3>Build</h3>
<p>Users must choose a build that will be connected with a test result. " .
"Builds are the basic component for the current Test Plan. Each test case " .
"may be run more times per build. However the last results is count only. 
<br />Builds can be created by leads using the Create New Build page.</p>

<h3>Test Case ID filter</h3>
<p>Users can filter test cases by unique identifier. This ID is created automatically 
during create time. Empty box means that the filter doesn't apply.</p> 

<h3>Priority filter</h3>
<p>Users can filter test cases by test priority. Each test case importance is combined" .
"with test urgency within the current Test plan. For example 'HIGH' priority test case " .
"is shown if importance or urgency is HIGH and second attribute is at least MEDIUM level.</p> 

<h2>Result filter</h2>
<p>Users can filter test cases by results. Results are what happened to that test 
case during a particular build. Test cases can pass, fail, be blocked, or not be run." .
"This filter is disabled by default.</p>

<h3>User filter</h3>
<p>Users can filter test cases by their assignee. The check-box allows to include also " .
"\"unassigned\" tests into the resulted set in addtion.</p>";
/*
<h2>Most Current Result</h2>
<p>By default or if the 'most current' checkbox is unchecked, the tree will be sorted 
by the build that is chosen from the dropdown box. In this state the tree will display 
the test cases status. 
<br />Example: User selects build 2 from the dropdown box and doesn't check the 'most 
current' checkbox. All test cases will be shown with their status from build 2. 
So, if test case 1 passed in build 2 it will be colored green.
<br />If the user decideds to check the 'most current' checkbox the tree will be 
colored by the test cases most recent result.
<br />Ex: User selects build 2 from the dropdown box and this time checks 
the 'most current' checkbox. All test cases will be shown with most current 
status. So, if test case 1 passed in build 3, even though the user has also selected 
build 2, it will be colored green.</p>
 */


// newest_tcversions.html
$TLS_hlp_planTcModified = "<h2>Newest versions of linked Test Cases</h2>
<p>The whole set of Test Cases linked to Test Plan is analyzed, and a list of Test Cases
which have a newest version is displayed (against the current set of the Test Plan).
</p>";


// requirementsCoverage.html
$TLS_hlp_requirementsCoverage = "<h3>Requirements Coverage</h3>
<br />
<p>This feature allows to map a coverage of user or system requirements by
test cases. Navigate via link \"Requirement Specification\" in main screen.</p>

<h3>Requirements Specification</h3>
<p>Requirements are grouped by 'Requirements Specification' document which is related to 
Test Project.<br /> TestLink doesn't support versions for both Requirements Specification  
and Requirements itself. So, version of document should be added after 
a Specification <b>Title</b>.
An user can add simple description or notes to <b>Scope</b> field.</p> 

<p><b><a name='total_count'>Overwritten count of REQs</a></b> serves for 
evaluation Req. coverage in case that not all requirements are added (imported) in. 
The value <b>0</b> means that current count of requirements is used for metrics.</p> 
<p><i>E.g. SRS includes 200 requirements but only 50 are added in TestLink. Test 
coverage is 25% (if all these added requirements will be tested).</i></p>

<h3><a name=\"req\">Requirements</a></h3>
<p>Click on title of a created Requirements Specification. You can create, edit, delete
or import requirements for the document. Each requirement has title, scope and status.
Status should be \"Normal\" or \"Not testable\". Not testable requirements are not counted
to metrics. This parameter should be used for both unimplemented features and 
wrong designed requirements.</p> 

<p>You can create new test cases for requirements by using multi action with checked 
requirements within the specification screen. These Test Cases are created into Test Suite
with name defined in configuration <i>(default is: &#36;tlCfg->req_cfg->default_testsuite_name = 
\"Test suite created by Requirement - Auto\";)</i>. Title and Scope are copied to these Test cases.</p>
";


// planAddTC_m1.tpl
$TLS_hlp_planAddTC = "<h2>Regarding 'Save Custom Fields'</h2>
If you have defined and assigned to Test Project,<br /> 
Custom Fields with:<br />
 'Display on test plan design=true' and <br />
 'Enable on test plan design=true'<br />
you will see these in this page ONLY for Test Cases linked to Test Plan.
";

// xxx.html
//$TLS_hlp_xxx = "";

// ----- END ------------------------------------------------------------------
?>