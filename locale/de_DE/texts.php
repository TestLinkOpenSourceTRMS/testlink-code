<?php
/**
 * ♔ TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later.
 *
 * German (de_DE) texts for help/instruction pages. Strings for dynamic pages
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
 * @package 	TestLink
 * @author 		Martin Havlat, Julian Krien
 * @copyright 	2003-2009, TestLink community 
 * @version    	CVS: $Id: texts.php,v 1.14 2010/06/24 17:25:57 asimon83 Exp $
 * @link 		http://www.teamst.org/index.php
 *
 * @internal Revisions:
 * 20100517 - Julian - update of header according to en_GB texts.php
 **/


// --------------------------------------------------------------------------------------
$TLS_htmltext_title['error']	= "Anwendungsfehler";
$TLS_htmltext['error'] 		= "<p>Es ist ein unerwarteter Fehler aufgetreten. Bitte " .
		"überprüfen Sie den Event Viewer und/oder Log-Dateien für weitere Details." .
		"</p><p>Sie können das Problem gerne melden. Besuchen Sie hierzu bitte unsere " .
		"<a href='http://www.teamst.org'>Webseite</a>.</p>";



$TLS_htmltext_title['assignReqs']	= "Zuweisung von Anforderungen zu Testfällen";
$TLS_htmltext['assignReqs'] 		= "<h2>Zweck:</h2>
<p>Benutzer können eine Beziehung zwischen Anforderungen und Testfällen herstellen. 
Ein Testfall kann keinem, einen oder mehreren Anforderungen zugewiesen oder umgekehrt.
Diese Zuweisungen erlauben es später eine Aussage darüber zu treffen, welche
Anforderungen abhängig von den Testergebnissen erfolgreich umgesetzt wurden.</p>

<h2>Anweisung:</h2>
<ol>
	<li>Wählen Sie einen Testfall im Baum auf der linken Seite. Auf der rechten Seite
	erscheinen dann verfügbaren Anforderungen, denen Sie den Testfall zuweisen können.</li>
	<li>Wählen Sie die gewünschte Anforderungsspezifikation aus der Liste \"Anforderungen
	definieren\". Anschließend wird die Seite neu geladen.</li>
	<li>Es werden nun alle Anforderungen der Anforderungsspezifikation angezeigt.
	In dem Block \"Zugewiesene Anforderungen\" sehen Sie die Anforderungen, die dem Testfall
	bereits zugewiesen wurden. Im Block \"Verfügbare\" Anforderungen sehen Sie die
	Anforderungen, die dem Testfall noch nicht zugewiesen wurden. Wählen Sie die
	Anforderungen, für die Sie eine Testfallzuweisung hinzufügen bzw. entfernen möchten und
	klicken Sie anschließend den entsprechenden Button.</li>
</ol>";


// --------------------------------------------------------------------------------------
$TLS_htmltext_title['editTc']	= "Testfälle bearbeiten";
$TLS_htmltext['editTc'] 		= "<p>Die <i>Testspezifikation</i> ermöglicht das " .
		"Anzeigen und Editieren aller vorhandenen Test Suiten und Testfälle. Testfälle sind " .
		"versioniert. Alle früheren Versionen sind noch für die Einsicht und Verwaltung " .
		"verfügbar.</p>
		
<h2>Anweisung:</h2>
<ol>
	<li>Select your <i>Test Project</i> in the navigation tree (the root node). <i>Please note: " .
	"You can always change the active Test Project by selecting a different one from the " .
	"drop-down list in the top-right corner.</i></li>
	<li>Create a new Test Suite by clicking on <b>New Child Test Suite</b>. Test Suites can " .
	"bring structure to your test documents according to your conventions (functional/non-functional " .
	"tests, product components or features, change requests, etc.). The description of " .
	"a Test Suite could hold the scope of the included test cases, default configuration, " .
	"links to relevant documents, limitations and other useful information. In general, " .
	"all annotations that are common to the Child Test Cases. Test Suites follow " .
	"the &quot;folder&quot; metaphor, thus users can move and copy Test Suites within " .
	"the Test project. Also, they can be imported or exported (including the contained Test cases).</li>
	<li>Test Suites are scalable folders. Users can move or copy Test Suites within " .
	"the Test project. Test Suites can be imported or exported (include Test Cases).
	<li>Select your newly created Test Suite in the navigation tree and create " .
	"a new Test Case by clicking on <b>Create Test Case</b>. A Test Case specifies " .
	"a particular testing scenario, expected results and custom fields defined " .
	"in the Test Project (refer to the user manual for more information). It is also possible " .
	"to assign <b>keywords</b> for improved traceability.</li>
	<li>Navigate via the tree view on the left side and edit data. Each Test case stores own history.</li>
	<li>Assign your created Test Specification to a 	<span class=\"help\" onclick=
	\"javascript:open_help_window('glosary','$locale');\">Test Plan</span> when your Test cases are ready.</li>
</ol>

<p>With TestLink you can organize Test Cases into Test Suites." .
"Test Suites can be nested within other test suites, enabling you to create hierarchies of Test Suites.
 You can then print this information together with the Test Cases.</p>";


// ------------------------------------------------------------------------------------------
$TLS_htmltext_title['searchTc']	= "Testfälle suchen";
$TLS_htmltext['searchTc'] 		= "<h2>Zweck:</h2>

<p>Suche von Testfällen anhand frei definierbaren Parametern.</p>

<h2>Anweisung:</h2>

<ol>
	<li>Suchbegriff in die entsprechenden Felder der Suchmaske eingeben.</li>
	<li>Stichwörter/Benutzerdefinierte Felder/... wählen.</li>
	<li>Suchen Button klicken.</li>
	<li>Testfälle, die den Suchkriterien entsprechen werden angezeigt. Über den Link des
	Testfalltitels können Sie den Testfall einsehen und editieren.</li>
</ol>

<h2>Hinweis:</h2>

<p>- Es werden nur Testfälle innerhalb des aktuellen Testprojekts durchsucht.<br>
- Die Suche ist unabhängig von \"Groß- und Kleinschreibung\".<br>
- Leere Felder der Suchmaske werden nicht berücksichtigt.</p>";

/* contribution by asimon for 2976 */
// requirements search
// ------------------------------------------------------------------------------------------
$TLS_htmltext_title['searchReq']	= "Anforderungen suchen";
$TLS_htmltext['searchReq'] 		= "<h2>Zweck:</h2>

<p>Suche von Anforderungen anhand frei definierbaren Parametern.</p>

<h2>Anweisung:</h2>

<ol>
	<li>Suchbegriff in die entsprechenden Felder der Suchmaske eingeben.</li>
	<li>Status/Typ/Beziehungstyp/Benutzerdefinierte Felder/... wählen.</li>
	<li>Suchen Button klicken.</li>
	<li>Anforderungen, die den Suchkriterien entsprechen werden angezeigt. Über den Link des
	Anforderungstitels können Sie die Anforderung einsehen und editieren.</li>
</ol>

<h2>Hinweis:</h2>

<p>- Es werden nur Anforderungen innerhalb des aktuellen Testprojekts durchsucht.<br>
- Die Suche ist unabhängig von \"Groß- und Kleinschreibung\".<br>
- Leere Felder der Suchmaske werden nicht berücksichtigt.</p>";

// requirement specification search
// ------------------------------------------------------------------------------------------
$TLS_htmltext_title['searchReqSpec']	= "Anforderungsspezifikationen suchen";
$TLS_htmltext['searchReqSpec'] 		= "<h2>Zweck:</h2>

<p>Suche von Anforderungenspezifikationen anhand frei definierbaren Parametern.</p>

<h2>Anweisung:</h2>

<ol>
	<li>Suchbegriff in die entsprechenden Felder der Suchmaske eingeben.</li>
	<li>Typ/Benutzerdefinierte Felder/... wählen.</li>
	<li>Suchen Button klicken.</li>
	<li>Anforderungsspezifikationen, die den Suchkriterien entsprechen werden angezeigt.
	Über den Link des Anforderungsspezifikationstitels können Sie die Anforderungsspezifikationen
	einsehen und editieren.</li>
</ol>

<h2>Hinweis:</h2>

<p>- Es werden nur Anforderungsspezifikationen innerhalb des aktuellen Testprojekts durchsucht.<br>
- Die Suche ist unabhängig von \"Groß- und Kleinschreibung\".<br>
- Leere Felder der Suchmaske werden nicht berücksichtigt.</p>";
/* end contribution */


// ------------------------------------------------------------------------------------------
$TLS_htmltext_title['printTestSpec']	= "Testspezifikation erstellen"; //printTC.html
$TLS_htmltext['printTestSpec'] 			= "<h2>Purpose:</h2>
<p>From here you can print a single test case, all the test cases within a test suite,
or all the test cases in a test project or plan.</p>
<h2>Get Started:</h2>
<ol>
<li>
<p>Select the parts of the test cases you want to display, and then click on a test case, 
test suite, or the test project. A printable page will be displayed.</p>
</li>
<li><p>Use the \"Show As\" drop-box in the navigation pane to specify whether you want 
the information displayed as HTML, OpenOffice Writer or in a Micosoft Word document. 
See <span class=\"help\" onclick=\"javascript:open_help_window('printFilter',
'{$locale}');\">help</span> for more information.</p>
</li>
<li><p>Use your browser's print functionality to actually print the information.<br />
<i>Note: Make sure to only print the right-hand frame.</i></p>
</li>
</ol>";


// ------------------------------------------------------------------------------------------
$TLS_htmltext_title['reqSpecMgmt']	= "Anforderungen definieren"; //printTC.html
$TLS_htmltext['reqSpecMgmt'] 			= "<p>You can manage Requirement Specification documents.</p>

<h2>Requirements Specification</h2>

<p>Requirements are grouped by <b>Requirements Specification document</b>, which is related to
Test Project.<br /> TestLink doesn't support (yet) versions for both Requirements Specification
and Requirements itself. So, a document version should be added after a Specification <b>Title</b>.
An user can add a simple description or notes to the <b>Scope</b> field.</p>

<p><b><a name='total_count'>Overwritten count of REQs</a></b> serves for
evaluating Req. coverage in case that not all requirements are added to TestLink.
The value <b>0</b> means that current count of requirements is used
for metrics.</p>
<p><i>E.g. SRS includes 200 requirements but only 50 are added in TestLink. Test
coverage is 25% (assuming the 50 added requirements will actually be tested).</i></p>

<h2><a name='req'>Requirements</a></h2>

<p>Click the title of an existing Requirements Specification. If none exist, " .
		"click on the project node to create one. You can create, edit, delete
or import requirements for the document. Each requirement has a title, scope and status.
A status should be either 'Normal' or 'Not testable'. Not testable requirements are not counted
to metrics. This parameter should be used for both unimplemented features and
wrong designed requirements.</p>

<p>You can create new test cases for requirements by using multi action with checked
requirements within the specification screen. These Test Cases are created into Test Suite
with name defined in configuration <i>(default is: \$tlCfg->req_cfg->default_testsuite_name =
'Test suite created by Requirement - Auto';)</i>. Title and Scope are copied to these Test cases.</p>";


// ------------------------------------------------------------------------------------------
$TLS_htmltext_title['printReqSpec'] = "Anforderungsspezifikation erstellen"; //printReq
$TLS_htmltext['printReqSpec'] = "<h2>Purpose:</h2>
<p>From here you can print a single requirement, all the requirements within a requirement specification,
or all the requirements in a test project.</p>
<h2>Get Started:</h2>
<ol>
<li>
<p>Select the parts of the requirements you want to display, and then click on a requirement, 
requirement specification, or the test project. A printable page will be displayed.</p>
</li>
<li><p>Use the \"Show As\" drop-box in the navigation pane to specify whether you want 
the information displayed as HTML, OpenOffice Writer or in a Micosoft Word document. 
See <span class=\"help\" onclick=\"javascript:open_help_window('printFilter',
'{$locale}');\">help</span> for more information.</p>
</li>
<li><p>Use your browser's print functionality to actually print the information.<br />
<i>Note: Make sure to only print the right-hand frame.</i></p>
</li>
</ol>";


// ------------------------------------------------------------------------------------------
$TLS_htmltext_title['keywordsAssign']	= "Stichwörter zuweisen";
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
<p>TestLink uses this approach so that older versions of test cases in test plans are not affected
by keyword assignments you make to the most recent version of the test case. If you want your
test cases in your test plan to be updated, first verify they are up to date using the 'Update
Modified Test Cases' functionality BEFORE making keyword assignments.</p>";


// ------------------------------------------------------------------------------------------
$TLS_htmltext_title['executeTest']	= "Testfälle ausführen";
$TLS_htmltext['executeTest'] 		= "<h2>Zweck:</h2>

<p>Allows user to execute Test cases. User can assign Test result
to Test Case for a Build. See help for more information about filters and settings " .
		"(click on the question mark icon).</p>

<h2>Anweisung:</h2>

<ol>
	<li>User must have defined a Build for the Test Plan.</li>
	<li>Select a Build from the drop down box</li>
	<li>If you want to see only a few testcases instead of the whole tree,
		you can choose which filters to apply. Click the \"Apply\"-Button 
		after you have changed the filters.</li>	
	<li>Click on a test case in the tree menu.</li>
	<li>Fill out the test case result and any applicable notes or bugs.</li>
	<li>Save results.</li>
</ol>
<p><i>Note: TestLink must be configurated to collaborate with your Bug tracker 
if you would like to create/trace a problem report directly from the GUI.</i></p>";

// ------------------------------------------------------------------------------------------
$TLS_htmltext_title['showMetrics']	= "Testberichte und Metriken";
$TLS_htmltext['showMetrics'] 		= "<p>Reports are related to a Test Plan " .
		"(defined in top of navigator). This Test Plan could differ from the
current Test Plan for execution. You can also select a Report format:</p>
<ul>
<li><b>Normal</b> - report is displayed in web page</li>
<li><b>OpenOffice Writer</b> - report imported to OpenOffice Writer</li>
<li><b>OpenOffice Calc</b> - report imported to OpenOffice Calc</li>
<li><b>MS Excel</b> - report imported to Microsoft Excel</li>
<li><b>HTML Email</b> - report is emailed to user's email address</li>
<li><b>Charts</b> - report include graphs (flash technology)</li>
</ul>

<p>The print button activates printing of a report only (without navigation).</p>
<p>There are several separate reports to choose from, their purpose and function are explained below.</p>

<h3>Test Plan</h3>
<p>The document 'Test Plan' has options to define a content and a document structure.</p>

<h3>Test Report</h3>
<p>The document 'Test Report' has options to define a content and document structure.
It includes Test cases together with test results.</p>

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
The Query Form Page presents with a query page with 4 controls. Each control is set to a default which
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
<p>Click the 'submit' button to proceed with the query and display the output page.</p>

<p>Query Report Page will display: </p>
<ol>
<li>the query parameters used to create report</li>
<li>totals for the entire test plan</li>
<li>a per suite breakdown of the totals (sum / pass / fail / blocked / not run) and all executions performed
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
<p>View the status of every test case on every build. The most recent execution result will be used
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

<h3>Total Bugs For Each Test Case</h3>
<p>This report shows each test case with all of the bugs filed against it for the entire project.
This report is only available if a Bug Tracking System is connected.</p>";


// ------------------------------------------------------------------------------------------
$TLS_htmltext_title['planAddTC']	= "Testfälle hinzufügen / entfernen"; // testSetAdd
$TLS_htmltext['planAddTC'] 			= "<h2>Zweck:</h2>
<p>Nutzer mit entsprechenden Rechten können dem Testplan Testfälle hinzufügen oder Testfälle
aus dem Testplan entfernen.</p>

<h2>Anweisung:</h2>
<ol>
	<li>Wählen Sie eine Test Suite im Baum auf der linken Seite um alle in der Test Suite 
	enthaltenen Teställe angezeigt zu bekommen.</li>
	<li>Wählen sie alle Testfälle, die sie hinzufügen bzw. entfernen wollen und klicken Sie
	auf den \"Hinzufügen / Entfernen der ausgewählten Testfälle\" Button</li>
</ol>";

// ------------------------------------------------------------------------------------------
$TLS_htmltext_title['tc_exec_assignment']	= "Testfälle an Benutzer zuweisen";
$TLS_htmltext['tc_exec_assignment'] 		= "<h2>Zweck</h2>
<p>This page allows test leaders to assign users to particular tests within the Test Plan.</p>

<h2>Anweisung:</h2>
<ol>
	<li>Choose a Test case or Test Suite to test.</li>
	<li>Select a planned tester.</li>
	<li>Click the 'Save' button to submit assignment.</li>
	<li>Open execution page to verify assignment. You can set-up a filter for users.</li>
</ol>

<h2>To unassign all Test cases:</h2>
<ol>
	<li>Click on the root node in the tree (the Test Project).</li>
	<li>If there are assigned Test Cases, you will see a button to unassign all Test Cases. If you click it and confirm it,
	all Test Cases will be unassigned.</li>
</ol>";

// ------------------------------------------------------------------------------------------
$TLS_htmltext_title['planUpdateTC']	= "Verlinkte Testfälle aktualisieren";
$TLS_htmltext['planUpdateTC'] 		= "<h2>Zweck</h2>
<p>This page allows updating a Test case to a newer (different) version if a Test
Specification is changed. It often happens that some functionality is clarified during testing." .
		" User modifies Test Specification, but changes needs to propagate to Test Plan too. Otherwise Test" .
		" plan holds original version to be sure, that results refer to the correct text of a Test case.</p>

<h2>Anweisung:</h2>
<ol>
	<li>Choose a Test case or Test Suite to test.</li>
	<li>Choose a new version from the combo-box menu for a particular Test case.</li>
	<li>Click the 'Update Test plan' button to submit changes.</li>
	<li>To verify: Open execution page to view text of the test case(s).</li>
</ol>";


// ------------------------------------------------------------------------------------------
$TLS_htmltext_title['test_urgency']	= "Dringlichkeit der Tests bestimmen";
$TLS_htmltext['test_urgency'] 		= "<h2>Zweck</h2>
<p>TestLink allows setting the urgency of a Test Suite to affect the	 testing Priority of test cases. 
		Test priority depends on both Importance of Test cases and Urgency defined in 
		the Test Plan. Test leader should specify a set of test cases that could be tested
		at first. It helps to ensure that testing will cover the most important tests
		also under time pressure.</p>

<h2>Anweisung:</h2>
<ol>
	<li>Choose a Test Suite to set urgency of a product/component feature in navigator
	on the left side of window.</li>
	<li>Choose an urgency level (high, medium or low). Medium is default. You can
	decrease priority for untouched parts of product and increase for components with
	significant changes.</li>
	<li>Click the 'Save' button to submit changes.</li>
</ol>
<p><i>For example, a Test case with a High importance in a Test suite with Low urgency " .
		"will be Medium priority.</i>";


// ------------------------------------------------------------------------------------------

?>