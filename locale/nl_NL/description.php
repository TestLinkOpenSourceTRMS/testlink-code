<?php
/** 
 * ♔ TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later. 
 * 
 * Localisation: English (en_GB) texts - default development localisation (World-wide English)
 *
 * 
 * The file contains global variables with html text. These variables are used as 
 * HELP or DESCRIPTION. To avoid override of other globals we are using "Test Link String" 
 * prefix '$TLS_hlp_' or '$TLS_txt_'. This must be a reserved prefix.
 * 
 * Contributors howto:
 * Add your localisation to TestLink tracker as attachment to update the next release
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

// LET OP: om consistente vertalingen te bewerkstelligen, maak gebruik van de standaard
// woordvertalingen (inclusief hoofdlettergebruik) die bovenaan het bestand  nl_NL/strings.txt 
// zijn aangegeven.


// printFilter.html
$TLS_hlp_generateDocOptions = "<h2>Options for a generated document</h2>

<p>This table allows the user to filter Test Cases before they are viewed. If
selected (checked), the data will be shown. In order to change the data
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

<p><b>Table of Content:</b> TestLink inserts a list of all titles with internal hypertext links if checked.</p>

<p><b>Output format:</b> There are two possibilities: HTML and MS Word. Browser calls MSWord component 
in the second case.</p>";

// testPlan.html
$TLS_hlp_testPlan = "<h2>Testplan</h2>

<h3>Algemeen</h3>
<p>Een Testplan is een systematisch aanpak voor het testen van een systeem, bijvoorbeeld software.
Men kan testwerkzaamheden voor een Testplan organiseren met Builds (versies van een product op verschillende
momenten), en men kan de resultaten vastleggen en volgen.</p>
 
<h3>Test Uitvoering</h3>
<p>In deze sectie kan men Testgevallen uitvoeren &ndash; d.w.z. test resultaten invoeren &ndash;
en Test Suites afdrukken.</p>

<h2>Testplan Beheer</h2>
<p>In deze sectie, die alleen voor testleiders toegankelijk is, kunnen Testplannen worden beheerd.
Het beheer van Testplannen omhelst het aanmaken, bewerken en verwijderen van Testplannen, 
het toevoegen, bewerken en verwijderen van Testgevallen, het aanmaken van Builds en definieren van wie
de rechten heeft om elk Testplan in te zien.

<p>Testleiders kunnen ook de prioriteit, risico en eigendom van Test Suites, en testmijlpalen aanmaken.</p>

<p>NB: het is mogelijk dat gebruikers geen dropdown zien met Testplannen en geen bruikbare links. 
Als je dit tegen komt moet u contact opnemen met een testleider of beheerder om een Testplan te laten
aanmaken of de juiste rechten te krijgen.</p>"; 

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
<li>Display on Test Plan design.</li>
<li>Enable on Test Plan design. User can change the value during Test Plan design (add Test Cases to Test Plan)</li>
<li>Available for. User choose to what kind of item the field belows.</li>
</ul>
";

// execMain.html
$TLS_hlp_executeMain = "<h2>Testgevallen uitvoeren</h2>
<p>Hier kan men Testgevallen uitvoeren. Uitvoering houdt in dat men een Testgeval een testresultaat
(succes, gefaald, geblokkeerd) toewijst voor een bepaalde Build. </p>

<p>Men kan ook rechtstreeks meldingen invoeren in een Issue Tracking Systeem. 
Dat moet wel door de beheerder zijn ingesteld. Zie de installatie handleiding voor meer informatie. </p>";


//bug_add.html
$TLS_hlp_btsIntegration = "<h2>Add Bugs to Test Case</h2>
<p><i>(only if it is configured)</i>
TestLink has a very simple integration with Bug Tracking Systems (BTS),
not being able either send a bug creation request to BTS, neither get back the bug id.
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

<p>After closing the add bug page, you will see the relevant bug data on the execute page.
</p>";

// execFilter.html
$TLS_hlp_executeFilter = "<h2>Settings</h2>

<p>The Settings panel allows you to select the Test Plan, build and platform (if available) to
be executed.</p>

<h3>Test Plan</h3>
<p>You can choose the required Test Plan. According to the chosen Test Plan the appropriate
builds will be shown. After choosing a Test Plan, filters will be reset.</p>

<h3>Platform</h3>
<p>If the platforms feature is used, you must select the appropriate platform before execution.</p>

<h3>Build to execute</h3>
<p>You can choose the build for which you wish to execute the Test Cases. </p>

<h2>Filters</h2>
<p>Filters provide the opportunity to further influence the set of Test Cases shown.
You can reduce the set of shown Test Cases by specifying filters
and clicking the \"Apply\" button.</p>

<p> Advanced Filters allow you to specify a set of values for applicable filters by
using CTRL-Click inside the Multi-Select ListBox.</p>


<h3>Keyword Filter</h3>
<p>You can filter Test Cases by the keywords that have been assigned. You can choose " .
"multiple keywords by using CTRL-Click. If you choose more than one keyword you can " .
"decide whether only Test Cases are shown that have all chosen keywords assigned " .
"(radiobutton \"And\") or at least one of the chosen keywords (radiobutton \"Or\").</p>

<h3>Priority Filter</h3>
<p>You can filter Test Cases by test priority. The test priority is \"Test Case importance\" " .
"combined with \"test urgency\" within the current Test Plan.</p> 

<h3>User Filter</h3>
<p>You can filter Test Cases that are not assigned (\"Nobody\") or assigned to \"Somebody\". " .
"You can also filter Test Cases that are assigned to a specific tester. If you choose a specific " .
"tester you also have the possibility to show Test Cases that are unassigned in addition to " .
"those Test Cases (advanced Filters are available). </p>

<h3>Result Filter</h3>
<p>You can filter Test Cases by result (advanced Filters are available). You can filter by " .
"result \"on chosen build for execution\", \"on latest execution\", \"on ALL builds\", " .
"\"on ANY build\" and \"on specific build\". If \"specific build\" is chosen you then " .
"specify the build. </p>";


// newest_tcversions.html
$TLS_hlp_planTcModified = "<h2>Newest versions of linked Test Cases</h2>
<p>The whole set of Test Cases linked to Test Plan is analysed, and a list of Test Cases
which have a newest version is displayed (against the current set of the Test Plan).
</p>";


// requirementsCoverage.html
$TLS_hlp_requirementsCoverage = "<h3>Requirements Coverage</h3>
<br />
<p>This feature allows you to map the coverage of user or system requirements by
Test Cases. Navigate via link \"Requirement Specification\" in main screen.</p>

<h3>Requirements Specification</h3>
<p>Requirements are grouped by 'Requirements Specification' document which is related to 
Test Project.<br /> TestLink doesn't support versions for both Requirements Specification  
and Requirements itself. So, version of document should be added after 
a Specification <b>Title</b>.
A user can add simple description or notes to <b>Scope</b> field.</p> 

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

<p>You can create new Test Cases for requirements by using multi action with checked 
requirements within the specification screen. These Test Cases are created into Test Suite
with name defined in configuration <i>(default is: &#36;tlCfg->req_cfg->default_testsuite_name = 
\"Test suite created by Requirement - Auto\";)</i>. Title and Scope are copied to these Test cases.</p>
";

$TLS_hlp_req_coverage_table = "<h3>Dekking:</h3>
Een waarde van bijvoorbeeld \"40% (8/20)\" betekent dat 20 Testgevallen moeten voor deze Requirement
worden creëerd om hem volledig te testen. 8 bestaan al en zijn aan deze Requirement gekoppeld,
wat betekent dat de dekking 40 procent is.
";


// planAddTC_m1.tpl
$TLS_hlp_planAddTC = "<h2>Regarding 'Save Custom Fields'</h2>
If you have defined and assigned to Test Project,<br /> 
Custom Fields with:<br />
 'Display on Test Plan design=true' and <br />
 'Enable on Test Plan design=true'<br />
you will see these in this page ONLY for Test Cases linked to Test Plan.
";


// resultsByTesterPerBuild.tpl
$TLS_hlp_results_by_tester_per_build_table = "<b>More information about testers:</b><br />
If you click on a tester name in this table, you will get a more detailed overview
about all Test Cases assigned to that user and his testing progress.<br /><br />
<b>Note:</b><br />
This report shows those Test Cases which are assigned to a specific user and have been executed 
based on each active build. Even if a Test Case has been executed by another user than the assigned user, 
the Test Case will appear as executed for the assigned user.
";


// req_edit
$TLS_hlp_req_edit = "<h3>Internal links on scope:</h3>
<p>Internal links serve the purpose of creating links to other requirements/requirement specifications. 
They have a special syntax. Internal Link behaviour can be changed in the config file.
<br /><br />
<b>Usage:</b>
<br />
Link to requirements: [req]req_doc_id[/req]<br />
Link to requirement specifications: [req_spec]req_spec_doc_id[/req_spec]</p>

<p>The test project of the requirement / requirement specification, a version and an anchor 
to jump to can also be specified:<br />
[req tproj=&lt;tproj_prefix&gt; anchor=&lt;anchor_name&gt; version=&lt;version_number&gt;]req_doc_id[/req]<br />
This syntax also works for requirement specifications (the version attribute has no effect).<br />
If you do not specify a version the whole requirement, including all versions, will be shown.</p>

<h3>Log message for changes:</h3>
<p>Whenever a change is made, Testlink will ask for a log message. This log message serves the purpose of traceability.
If only the scope of the requirement has changed you are free to decide whether to create a new revision or not. 
Whenever anything other than the scope is changed you are forced to create a new revision.</p>
";


// req_view
$TLS_hlp_req_view = "<h3>Directe Links:</h3>
<p>Om dit document gemakkelijk met anderen te kunnen delen, klik op de wereldbol bovenaan
dit document om een direct link aan te maken.</p>

<h3>Geschiedenis Bekijken</h3>
<p>Hiermee kunt u versies van Requirements vergelijken (als er meer dan één bestaat).
Het overzicht geeft het logbericht voor elke versie, een tijdstempel en de auteur van de laatste
versie.</p>

<h3>Dekking:</h3>
<p>Toont alle toegekende Testgevallen voor deze Requirement.</p>

<h3>Relaties:</h3>
<p>Requirement Relaties worden gebruikt om relaties tussen Requirements te modelleren.
Eigen relaties en de optie om relaties tussen verschillende Testprojecten toe te staan
kunnen in het configuratiebestand worden ingesteld.
Als u de relatie instelt 'Requirement A is moeder van Requirement B',
TestLink zet natuurlijk ook de relatie 'Requirement B is dochter van Requirement A'.</p>
";




// req_spec_edit
$TLS_hlp_req_spec_edit = "<h3>Interne links:</h3>
<p>Interne links verwijzen naar andere Requirements / Requirement Specificaties. 
Zij hebben een bijzonder syntaxis. Het gedrag van interne links kan gewijzigd worden
in het configuratiebestand. </p>
<h3>Gebruik:</h3>
<p>
Link naar Requirements: [req]req_doc_id[/req]<br />
Link naar Requirements Specificatie: [req_spec]req_spec_doc_id[/req_spec]</p>
<p>Het Testproject van de Requirement, een versie en een doellocatie (anchor) kan ook 
worden gespecificeerd:<br />
[req tproj=&lt;tproj_prefix&gt; anchor=&lt;anchor_name&gt; version=&lt;version_number&gt;]req_doc_id[/req]<br />
Dit syntaxis werkt ook voor Requirement Specifications (versie heeft in dat geval geen effect).
Als u geen versie opgeeft wordt de hele Requirement getoond, met alle versies.</p>
";


// xxx.html
//$TLS_hlp_xxx = "";

// ----- END ------------------------------------------------------------------
?>
