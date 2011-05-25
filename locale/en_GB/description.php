<?php
/** 
 * ♔ TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later. 
 * 
 * Localization: English (en_GB) texts - default development localization (World-wide English)
 *
 * 
 * The file contains global variables with html text. These variables are used as 
 * HELP or DESCRIPTION. To avoid override of other globals we are using "Test Link String" 
 * prefix '$TLS_hlp_' or '$TLS_txt_'. This must be a reserved prefix.
 * 
 * Contributors howto:
 * Add your localization to TestLink tracker as attachment to update the next release
 * for your language.
 *
 * No revision is stored for the the file - see CVS history
 * 
 * 
 * @package 	TestLink
 * @author 		Martin Havlat
 * @copyright 	2003-2009, TestLink community 
 * @version    	CVS: $Id: description.php,v 1.17 2010/09/13 09:52:42 mx-julian Exp $
 * @link 		http://www.teamst.org/index.php
 *
 * @internal Revisions:
 * 20100409 - eloff - BUGID 3050 - Update execution help text
 **/


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
and browse existing ones then. See Installation manual for more.</p>";

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
$TLS_hlp_executeFilter = "<h2>Settings</h2>

<p>Settings allows you to select the test plan, build and platform (if available) to
be executed.</p>

<h3>Test Plan</h3>
<p>You can choose the required test plan. According to the chosen test plan the appropriate
builds will be shown. After choosing a test plan filters will be reset.</p>

<h3>Platform</h3>
<p>If the platforms feature is used, you must select the appropriate platform before execution.</p>

<h3>Build to execute</h3>
<p>You can choose the build you whish to execute the test cases for.</p>

<h2>Filters</h2>
<p>Filters provide the opportunity to further influence the set of shown test cases
before the execution. You can reduce the set of shown test cases by specifying filters
and click the \"Apply\" button.</p>

<p> Advanced Filters will allow you to specify a set of values for applicable filters by
using CTRL-Click inside the Multi-Select ListBox</p>


<h3>Keyword Filter</h3>
<p>You can filter test cases by the keywords that have been assigned. You can choose " .
"multiple keywords by using CTRL-Click. If you chose more than one keyword you can " .
"decide whether only test cases are shown that have all chosen keywords assigned " .
"(radiobutton \"And\") or  at least one of the chosen keywords (radiobutton \"Or\").</p>

<h3>Priority Filter</h3>
<p>You can filter test cases by test priority. The test priority is \"test case importance\" " .
"combined with \"test urgency\" within the current test plan.</p> 

<h3>User Filter</h3>
<p>You can filter test cases that are not assigned (\"Nobody\") or assigned to \"Somebody\". " .
"You can also filter test cases that are assigned to a specific tester. If you chose a specific " .
"tester you also have the possibility to show test cases that are unassigned in addition to " .
"those test cases (advanced Filters are available). </p>

<h3>Result Filter</h3>
<p>You can filter test cases by result (advanced Filters are available). You can filter by " .
"result \"on chosen build for execution\", \"on latest execution\", \"on ALL builds\", " .
"\"on ANY build\" and \"on specific build\". If \"specific build\" is chosen you then can " .
"specify the build. </p>";


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

$TLS_hlp_req_coverage_table = "<h3>Coverage:</h3>
A value of e.g. \"40% (8/20)\" means that 20 Test Cases have to be created for this Requirement 
to test it completely. 8 of those have already been created and linked to this Requirement, which 
makes a coverage of 40 percent.
";


// req_edit
$TLS_hlp_req_edit = "<h3>Internal links on scope:</h3>
<p>Internal links serve the purpose of creating links to other requirements/requirement specifications 
with a special syntax. Internal Links behaviour can be changed in the config file.
<br /><br />
<b>Usage:</b>
<br />
Link to requirements: [req]req_doc_id[/req]<br />
Link to requirement specifications: [req_spec]req_spec_doc_id[/req_spec]</p>

<p>The test project of the requirement / requirement specification, a version and an anchor 
to jump to can also be specified:<br />
[req tproj=&lt;tproj_prefix&gt; anchor=&lt;anchor_name&gt; version=&lt;version_number&gt;]req_doc_id[/req]<br />
This syntax also works for requirement specifications (version attribute has no effect).<br />
If you do not specify a version the whole requirement including all versions will be shown.</p>

<h3>Log message for changes:</h3>
<p>Whenever a change is made Testlink will ask for a log message. This log message served the purpose of traceability.
If only the scope of the requirement has changed you are free to decide whether to create a new revision or not. 
Whenever anything besides the scope is changed you are forced to create a new revision.</p>
";


// req_view
$TLS_hlp_req_view = "<h3>Direct Links:</h3>
<p>To easily share this document with others simply click the globe icon at the top of this document to create a direct link.</p>

<h3>View History:</h3>
<p>This feature allows to compare revisions/versions of requirements if more than one revision/version of the requirement exists.
The overview provides the Log message for each revision/version, a timestamp and the author of the last change.</p>

<h3>Coverage:</h3>
<p>Shows all linked test cases for this requirement.</p>

<h3>Relations:</h3>
<p>Requirement Relations are used to model relationships between requirements. 
Custom relations and the option to allow relations between requirements of 
different test projects can be configured on the config file.
If you set the relation \"Requirement A is parent of Requirement B\", 
Testlink will set the relation \"Requirement B is child of Requirement A\" implicitly.</p>
";


// req_spec_edit
$TLS_hlp_req_spec_edit = "<h3>Internal links on scope:</h3>
<p>Internal links serve the purpose of creating links to other requirements/requirement specifications 
with a special syntax. Internal Links behaviour can be changed in the config file.
<br /><br />
<b>Usage:</b>
<br />
Link to requirements: [req]req_doc_id[/req]<br />
Link to requirement specifications: [req_spec]req_spec_doc_id[/req_spec]</p>

<p>The test project of the requirement / requirement specification, a version and an anchor 
to jump to can also be specified:<br />
[req tproj=&lt;tproj_prefix&gt; anchor=&lt;anchor_name&gt; version=&lt;version_number&gt;]req_doc_id[/req]<br />
This syntax also works for requirement specifications (version attribute has no effect).<br />
If you do not specify a version the whole requirement including all versions will be shown.</p>
";


// planAddTC_m1.tpl
$TLS_hlp_planAddTC = "<h2>Regarding 'Save Custom Fields'</h2>
If you have defined and assigned to Test Project,<br /> 
Custom Fields with:<br />
 'Display on test plan design=true' and <br />
 'Enable on test plan design=true'<br />
you will see these in this page ONLY for Test Cases linked to Test Plan.
";


// resultsByTesterPerBuild.tpl
$TLS_hlp_results_by_tester_per_build_table = "<b>More information about testers:</b><br />
If you click on a tester name in this table, you will get a more detailed overview
about all Test Cases assigned to that user and his testing progress.<br /><br />
<b>Note:</b><br />
This Report shows those test cases, which are assigned to a specific user and have been executed 
based on each active build. Even if a test case has been executed by another user than the assigned user, 
the test case will appear as executed for the assigned user.
";


// xxx.html
//$TLS_hlp_xxx = "";

// ----- END ------------------------------------------------------------------
?>
