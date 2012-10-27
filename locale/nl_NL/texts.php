<?php
/**
 * ♔ TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later.
 *
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
 * @package 	TestLink
 * @author 		Martin Havlat
 * @copyright 	2003-2009, TestLink community 
 * @version    	CVS: $Id: texts.php,v 1.29 2010/07/22 14:14:44 asimon83 Exp $
 * @link 		http://www.teamst.org/index.php
 *
 **/

// LET OP: om consistente vertalingen te bewerkstelligen, maak gebruik van de standaard
// woordvertalingen (inclusief hoofdlettergebruik) die bovenaan het bestand  nl_NL/strings.txt 
// zijn aangegeven.


// --------------------------------------------------------------------------------------
$TLS_htmltext_title['error']	= "Applicatiefout";
$TLS_htmltext['error'] 		= "<p>Een onverwachte fout heeft plaatsgevonden. A.u.b. de event viewer of " .
		"log controleren voor details.</p><p>U bent welkom om het probleem te melden. Bezoek onze " .
		"<a href='http://www.teamst.org'>website</a>.</p>";



$TLS_htmltext_title['assignReqs']	= "Requirements toekennen aan Testgeval";
$TLS_htmltext['assignReqs'] 		= "<h2>Doel</h2>
<p>Hier kunt u relaties leggen tussen Requirements en Testgevallen. De relaties zijn
<i>n:n</i>; d.w.z. een Testgeval kan nul, een of meerdere Requirements worden toegekend en
andersom. Deze koppeling ondersteunt controle van de dekking van Requirements door Testgevallen
en identificeert welke Requirements succesvol werden vervuld tijdens testen.
Deze analyse kan bevestigen dat aan alle verwachtingen is voldaan.</p>

<h2>Werkmethode</h2>
<p> Als u bezig bent met werkzaamheden kunt u terugkeren naar dit uitleg door op de naam van het 
Testproject bovenaan de boomstructuur links te klikken.</p>
<ol>
	<li>Kies een Testgeval in de boomstructuur links. Het werkgebied verschijnt rechts. De combo-box
	met de lijst van Requirements Specificaties wordt bovenaan het werkgebied getoond.

	<li>Kies een Requirements Specificatie document als meer dan één bestaat. 
	TestLink herlaadt de pagina automatisch.</li>
	<li>De middelste blok van het werkgebied geeft alle Requirements weer van de gekozen Specificatie
	die aan het Testgeval zijn toegekend.</li>
	<li>De onderste blok geeft alle Requirements weer van de gekozen Specificatie
	die niet aan het huidige Testgeval zijn toegekend. Vink de relevante Requirements aan en
	klik de knop 'Toewijzen'. Deze worden dan naar de middelste blok verplaatst.</li>
</ol>";


// --------------------------------------------------------------------------------------
$TLS_htmltext_title['editTc']	= "Testspecificatie";
$TLS_htmltext['editTc'] 		= "<p>Een Testgeval is de fundamentele eenheid van testen. Tijdens de 
	uitvoering krijgt het het resultaat 'geslaagd' of 'gefaald'. In TestLink kunt u Testgevallen 
	organiseren in Test Suites. Deze kunnen hiërarchisch worden genest in andere Test Suites. </p>
	<p>Op deze <i>Testspecificatie</i> pagina kunt u alle 
	<i>Test Suites</i> and <i>Testgevallen</i> bekijken en bewerken. 
	Versies van Testgevallen worden ondersteund, en alle vorige versies zijn beschikbaar en kunnen hier 
	bekeken en beheerd worden.</p>
		
<h2>Starten</h2>
<ol>
	<li>Selecteert u het <i>Testproject</i> in de boomstructuur. U kunt op elk moment een
	andere Testproject kiezen in de drop-down rechtsboven (als u rechten heeft op meerdere Testprojecten).</li>
	<li>Creëer een nieuwe Test Suite met de knop <b>Nieuwe dochter Test Suite</b> (Test Suite Operaties).
	U kunt Test Suites gebruiken om uw testdocumenten te structuren volgens eigen conventies
	(functioneel / niet-functioneel, per component of feature, per wijzigingsverzoek enz.)
	De beschrijving van een Test Suite zou in het algemeen alle informatie bevatten die voor alle
	Testgevallen in de Test Suite relevant is, bijvoorbeeld een samenvatting van de scope van de Testgevallen,
	verwijzingen naar relevante documenten enz.</li> 
	<li>Test Suites hebben de aard van een 'map'; gebruikers
	kunnen op een soortgelijke manier als met een map Test Suites verplaatsen en kopieren binnen het
	Testproject. Tevens kan een Test Suite worden geïmporteerd of geëxporteerd, tezamen met zijn
	Testgevallen.</li>
	<li>Selecteert u de net gecreëerde Test Suite in de boomstructuur en creëer een nieuw Testgeval
	door op de knop <b>Creëer Testgeval</b> te klikken (onder Testgeval Operaties).
	Een Testgeval bevat een bepaalde test scenario, de verwachte resultaten, en eventueel
	gebruikersvelden die in het Testproject zijn gedefinieerd (zie de gebruikershandleiding voor meer
	informatie). Men kan ook <b>steekwoorden</b> toewijzen t.b.v. de traceerbaarheid.</li>
	<li>Navigeer via de boomstructuur links en bewerk gegevens. Elk Testgeval bevat zijn eigen
	geschiedenis.</li>
	<li>Wijs de aangemaakte Testspecificatie toe aan een Testplan als de Testgevallen klaar zijn.
</ol>	
";


// ------------------------------------------------------------------------------------------
$TLS_htmltext_title['searchTc']	= "Testgeval Zoekpagina";
$TLS_htmltext['searchTc'] 		= "<h2>Doel</h2>

<p>Navigatie op basis van steekwoorden of zoekteksten. Het zoeken is niet hoofdlettergevoelig. 
de resultaten bevatten Testgevallen uit het huidige Testproject.</p>

<h2>Om te zoeken</h2>

<ol>
	<li>Voer de gewenste zoekteksten op in de relevante velden. Laat andere velden leeg.</li>
	<li>Kies gewenste datumcriteria met de calendar knoppen.</li>
	<li>Kies gewenste steekwoorden in de drop-downs. </li>
	<li>Klik de 'Vind' knop.</li>
</ol>
<p>	De overeenkomstige Testgevallen worden getoond. U kunt Testgevallen bewerken via de 'Titel' link.</p>
";

// requirements search
// ------------------------------------------------------------------------------------------
$TLS_htmltext_title['searchReq']	= "Requirements Zoekpagina";
$TLS_htmltext['searchReq'] 		= "<h2>Doel</h2>

<p>Zoek Requirements documenten in het huidige Testproject.</p>

<h2>Zoekmethode</h2>

<ol>
	<li>Voer de gewenste teksten in de zoekvelden. Laat velden waar men niet in de waarde wil zoeken blanco.</li>
	<li>Kies desgewenst steekwoorden in de drop-downs.</li>
	<li>Vul desgewenst datumvelden met kalendar knop.</li> 
	<li>Klik de Vind knop.</li>
	<li>Een lijst van overeenkomstige requirements wordt getoond.</li>
</ol>

<p>Het zoeken is niet hoofdlettergevoelig.</p>
";

// requirement specification search
// ------------------------------------------------------------------------------------------
$TLS_htmltext_title['searchReqSpec']	= "Requirements Specificatie Zoekpagina";
$TLS_htmltext['searchReqSpec'] 		= "<h2>Doel</h2>

<p>Zoek Requirements Specificatie documenten in het huidige Testproject.</p>


<h2>Zoekmethode</h2>

<ol>
	<li>Voer de gewenste teksten in de zoekvelden. Laat velden waar men niet in de waarde wil zoeken blanco.</li>
	<li>Kies desgewenst steekwoorden in de drop-down.</li>
	<li>Klik de Vind knop.</li>
	<li>Een lijst van overeenkomstige requirements wordt getoond.</li>
</ol>

<p>Het zoeken is niet hoofdlettergevoelig.</p>
";



// ------------------------------------------------------------------------------------------
$TLS_htmltext_title['printTestSpec']	= "Genereer Test Specificatie document"; //printTC.html
$TLS_htmltext['printTestSpec'] 			= "<h2>Doel</h2>
<p>Hier kunt u een document genereren &ndash; bijvoorbeeld om af te drukken of te e-mailen &ndash; met 
de Testgevallen in een Test Suite, of alle Testgevallen in een Testproject.</p> 

<h2>Werkmethode</h2>
<ol>
		<li>Selecteer de onderdelen die u wilt meenemen in het paneel linksboven.</li>
		<li>Gebruik de 'Toon als' drop-box in het navigatiepaneel om te kiezen tussen
		HTML, OpenOffice Writer or een Microsoft Word document. 
		</li>
		<li>Selecteer de Test Suite die u in het document wilt hebben,
		in de boomstructuur linksonder, of klik het
		Testproject naam om alle Test Suites te selecteren.</li>
		<li>Bij de HTML optie, gebruik de print functie van de browser om het HTML af te drukken. 
		Let op: print alleen de rechter frame.</li> 
</ol>";


// ------------------------------------------------------------------------------------------
$TLS_htmltext_title['reqSpecMgmt']	= "Requirements Specificatie Ontwerp"; //printTC.html
$TLS_htmltext['reqSpecMgmt'] 		= "<p>Hier kunt u Requirements Specificatie documenten beheren.</p>

<h2>Requirements Specificatie</h2>

<p>Requirements zijn gegroepeerd per <b>Requirements Specificatie</b>, die gekoppeld is aan een 
Testproject.</p> 

<p>Requirements Specificaties kunnen hierarchisch gerangschikt worden.  
Creëer Requirements Specificaties op het hoogste niveau door op de projectnaam te klikken. </p>

<p>TestLink ondersteunt geen versies van Requirements Specificaties of Requirements. 
Dus als een document versie nodig is, moet u het achter de Specificatie <b>Titel</b> plaatsen.
Een eenvoudige beschrijving of aantekeningen kunnen in het <b>Scope</b> veld worden geplaatst.</p>

<p>Het 'overschreven aantal REQs' kan men gebruiken om de Requirement dekking te evalueren
als nog niet alle Requirements in TestLink staan. Het aantal 0 betekent dat het aantal 
daadwerkelijk aanwezige Requirements  voor metrieken wordt gebruikt.</p>

<p>Voorbeeld: SRS beschrijft 200 Requirements, maar nog maar 50 zijn in TestLink ingevoerd.
TestLink dekking is 25% (als de 50 ingevoerde Requirements daadwerkelijk zijn getest).<p>

<h2>Requirements</h2>

<p>Klik de titel van een Requirements Specificatie. U kunt Requirements creëren, bewerken, verwijderen
of importeren voor het document. Elke Requirement heeft een titel, scope en status. 
De status is 'Normaal' of 'Informatief'. Requirements met status 'Informatief' worden niet in de metrieken
meegeteld. Deze statuswaarde kan worden gebruikt voor features die nog niet zijn geïmplementeerd of
Requirements die nog niet goed zijn ontworpen.</p>

<p>U kunt nieuwe Testgevallen in skeletvorm creëren voor Requirements met de 'Creëer Testgevallen' knop
op de Requirement Specificatie pagina. Dit start een pagina waar u kunt aangeven hoeveel Testgevallen
gewenst zijn voor elke Requirements document in de Requirements Specificatie.
Deze Testgevallen worden gecreëerd in de Test Suite met de naam die in de configuratie gedefinieerd is
<i>(de default is:<br> \$tlCfg->req_cfg->default_testsuite_name = 'Test suite created by Requirement - Auto';)</i>
De titel en Scope worden naar deze Testgevallen gekopieerd.</p>";


// ------------------------------------------------------------------------------------------
$TLS_htmltext_title['printReqSpec'] = "Genereer Requirements Specificatie document"; //printReq
$TLS_htmltext['printReqSpec'] = "<h2>Doel</h2>
<p>Hier kunt u een document genereren &ndash; bijvoorbeeld om af te drukken of te e-mailen &ndash; met 
de Requirements in een Requirements Specificatie, of alle Requirements in een Testproject.</p> 

<h2>Werkmethode</h2>
<ol>
		<li>Selecteer de onderdelen die u wilt meenemen in het paneel linksboven.</li>
		<li>Gebruik de 'Toon als' drop-box in het navigatiepaneel om te kiezen tussen
		HTML, OpenOffice Writer or een Microsoft Word document. 
		</li>
		<li>Selecteer de Requirement Specificatie die u in het document wilt hebben,
		in de boormstructuur linksonder, of klik het
		Testproject naam om alle Requirement Specificaties te selecteren.</li>
		<li>Bij de HTML optie, gebruik de print functie van de browser om het HTML af te drukken. 
		Let op: print alleen de rechter frame.</li> 
</ol>";


// ------------------------------------------------------------------------------------------
$TLS_htmltext_title['keywordsAssign']	= "Steekwoorden Toekennen";
$TLS_htmltext['keywordsAssign'] 			= "<h2>Doel</h2>
<p>Op deze pagina kunt u groepen steekwoorden toekennen aan een Test Suite of Testgeval</p>

<h2>Om steekwoorden toe te kennen:</h2>
<ol>
	<li>Selecteer een Test Suite of Testgeval in de boomstructuur links. </li>
	<li>Selecteer de gewenste steekwoorden (u kunt de Ctrl of Shift toetsen gebruiken
	om meerdere steekwoorden te selecteren)</li>
	<li>Gebruik de pijlen om de steekwoorden toe te kennen.</li>
</ol>

<h2>Belangrijke informatie over het toekennen van steekwoorden:</h2>
<p>Steekwoorden worden alleen toegekend aan Testgevallen als het Testplan de nieuwste versie
van het Testgeval bevat. Als een Testplan alleen oude versies bevat zullen die toegekende steekwoorden
dus <i>niet</i> verschijnen in het Testplan.
</p>
<p>TestLink gebruikt deze aanpak zodat oude versies van Testgevallen niet worden vervuild door
steekwoord toewijzingen die bedoeld zijn voor de huidige versie. Om zeker te zijn dat uw 
Testgevallen de steekwoorden krijgen toegekend, gebruik eerst de 'Gewijzigde Testgeval versies bijwerken' 
functie <i>vóór</i> het toekennen van steekwoorden.</p>
";


// ------------------------------------------------------------------------------------------
$TLS_htmltext_title['executeTest']	= "Test Case Execution";
$TLS_htmltext['executeTest'] 		= "<h2>Purpose:</h2>

<p>Here the user can execute Test Cases: the user can assign a Test result
to a Test Case for a given Build. See help for more information about filters and settings " .
		"(click on the question-mark icon).</p>

<h2>Get started:</h2>

<ol>
	<li>You must have defined a Build for the Test Plan.</li>
	<li>Select a Build from the drop-down box.</li>
	<li>If you want to see only a few Test Cases instead of the whole tree,
		you can choose which filters to apply. Click the \"Apply\" button 
		after you have changed the filters.</li>	
	<li>Click on a Test Case in the tree menu.</li>
	<li>Fill out the Test Case result and any applicable notes or bugs.</li>
	<li>Save the results.</li>
</ol>
<p><i>Note: you can create/trace a problem report directly from the GUI. 
To do this, TestLink must be configured to collaborate with your Bug tracker. </i></p>";

// ------------------------------------------------------------------------------------------
$TLS_htmltext_title['showMetrics']	= "Description of Test Reports and Metrics";
$TLS_htmltext['showMetrics'] 		= "<p>Reports are related to a Test Plan. " .
		"This is defined at the top of the navigation panel, and so can be different from the
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
<p>There are several separate reports to choose from; their purpose and function are explained below.</p>

<h3>Test Plan</h3>
<p>The document 'Test Plan' has options to define a content and a document structure.</p>

<h3>Test Report</h3>
<p>The document 'Test Report' has options to define a content and document structure.
It includes Test Cases together with test results.</p>

<h3>General Test Plan Metrics</h3>
<p>This page shows you only the most current status of a Test plan by Test Suite, owner, and keyword.
The most 'current status' is determined by the most recent build Test Cases were executed on.  For
instance, if a Test Case was executed over multiple builds, only the latest result is taken into account.</p>

<p>'Last Test Result' is a concept used in many reports, and is determined as follows:</p>
<ul>
<li>The order in which builds are added to a Test Plan determines which build is most recent. The results
from the most recent build will take precedence over older builds. For example, if you mark a test as
'fail' in build 1, and mark it as 'pass' in build 2, its latest result will be 'pass'.</li>
<li>If a Test Case is executed multiple times for the same build, the most recent execution will take
precedence.  For example, if build 3 is released to your team and tester 1 marks it as 'pass' at 2PM,
and tester 2 marks it as 'fail' at 3PM - it will appear as 'fail'.</li>
<li>Test Cases listed as 'not run' against a particular build are not taken into account. For example, if you mark
a case as 'pass' in build 1, and don't execute it in build 2, its last result will be considered as
'pass'. Test Cases are only considered 'not run' if they have not been run for <i>any</i> build.</li>
</ul>
<p>The following tables are displayed:</p>
<ul>
<li><b>Results by top level Test Suites: </b>
	Lists the results of each top level suite. Total cases, passed, failed, blocked, not run, and percent
	completed are listed. A 'completed' Test Case is one that has been marked pass, fail, or block.
	Results for top level suites include all descendant suites.</li>
	<li><b>Results By Keyword: </b>
	Lists all keywords that are assigned to cases in the current Test Plan, and the results associated
	with them.</li>
	<li><b>Results by owner: </b>
	Lists each owner that has Test Cases assigned to them in the current Test Plan. Test Cases which
	are not assigned are tallied under the 'unassigned' heading.</li>
</ul>

<h3>The Overall Build Status</h3>
<p>Lists the execution results for every build. For each build, the total Test Cases, total pass,
% pass, total fail, % fail, blocked, % blocked, not run, %not run.  If a Test Case has been executed
twice for the same build, the most recent execution will be taken into account.</p>

<h3>Query Metrics</h3>
<p>This report consists of a query form page, and a query results page which contains the queried data.
The Query Form Page presents with a query page with four controls. Each control is set to a default which
maximises the number of Test Cases and builds the query should be performed against. Altering the controls
allows the user to filter the results and generate specific reports for specific owner, keyword, suite,
and build combinations.</p>

<ul>
<li><b>keyword:</b> 0->1 keywords can be selected. By default - no keyword is selected. If a keyword is not
selected, then all Test Cases will be considered regardless of keyword assignments. Keywords are assigned
in the test specification or Keyword Management pages.  Keywords assigned to Test Cases span all Test Plans,
and span across all versions of a Test Case.  If you are interested in the results for a specific keyword
you would alter this control.</li>
<li><b>owner:</b> 0->1 owners can be selected. By default - no owner is selected. If an owner is not selected,
then all Test Cases will be considered regardless of owner assignment.  Currently there is no functionality
to search for 'unassigned' Test Cases.  Ownership is assigned through the 'Assign Test Case execution' page,
and is done on a per Test Plan basis.  If you are interested in the work done by a specific tester you would
alter this control.</li>
<li><b>top level suite:</b> 0->n top level suites can be selected. By default - all suites are selected.
Only suites that are selected will be queried for result metrics.  If you are only intested in the results
for a specific suite you would alter this control.</li>
<li><b>builds:</b> 1->n builds can be selected.  By default - all builds are selected.  Only executions
performed on builds you select will be taken into account when producing metrics.  For example - if you
wanted to see how many Test Cases were executed on the last 3 builds - you would alter this control.
Keyword, owner, and top level suite selections will dictate the number of Test Cases from your Test Plan
are used to computate per suite and per Test Plan metrics.  For example, if you select owner = 'Greg',
Keyword='Priority 1', and all available Test Suites - only Priority 1 Test Cases assigned to Greg will be
considered. The '# of Test Cases' totals you will see on the report will be influenced by these 3 controls.
Build selections will influence if a case is considered 'pass', 'fail', 'blocked', or 'not run'.  Please
refer to 'Last Test Result' rules as they appear above.</li>
</ul>
<p>Click the 'submit' button to proceed with the query and display the output page.</p>

<p>Query Report Page will display: </p>
<ol>
<li>the query parameters used to create report</li>
<li>totals for the entire Test Plan</li>
<li>a per-suite breakdown of the totals (sum / pass / fail / blocked / not run) and all executions performed
on that suite.  If a Test Case has been executed more than once on multiple builds - all executions will be
displayed that were recorded against the selected builds. However, the summary for that suite will only
include the 'Last Test Result' for the selected builds.</li>
</ol>

<h3>Blocked, Failed, and  Not Run Test Case Reports</h3>
<p>These reports show all of the currently blocked, failing, or not run Test Cases.  'Last test Result'
logic (which is described above under General Test Plan Metrics) is again employed to determine if
a Test Case should be considered blocked, failed, or not run.  Blocked and failed Test Case reports will
display the associated bugs if the user is using an integrated bug tracking system.</p>

<h3>Test Report</h3>
<p>View the status of every Test Case on every build. The most recent execution result will be used
if a Test Case was executed multiple times on the same build. It is recommended to export this report
to Excel format for easier browsing if a large data set is being used.</p>

<h3>Charts - General Test Plan Metrics</h3>
<p>'Last test Result' logic is used for all four charts that you will see. The graphs are animated to help
the user visualise the metrics from the current Test Plan. The four charts provided are :</p>
<ul><li>Pie chart of overall pass / fail / blocked / and not run Test Cases</li>
<li>Bar chart of Results by Keyword</li>
<li>Bar chart of Results By Owner</li>
<li>Bar chart of Results By Top Level Suite</li>
</ul>
<p>The bars in the bar charts are coloured such that the user can identify the approximate number of
pass, fail, blocked, and not run cases.</p>

<h3>Total Bugs For Each Test Case</h3>
<p>This report shows each Test Case with all of the bugs filed against it for the entire project.
This report is only available if a Bug Tracking System is connected.</p>";


// ------------------------------------------------------------------------------------------
$TLS_htmltext_title['planAddTC']	= "Add / Remove Test Cases to Test Plan"; // testSetAdd
$TLS_htmltext['planAddTC'] 			= "<h2>Purpose:</h2>
<p>Allows user (with lead level permissions) to add Test Cases to, or remove them from, a Test Plan.</p>

<h2>To add or remove Test Cases:</h2>
<ol>
	<li>Click on a Test Suite to see all of its child Test Suites and Test Cases.</li>
	<li>When you have selected the required Test Cases click the 'Add / Remove Selected' button. 
		Note: it is not possible to add the same Test Case multiple times.</li>
</ol>";

// ------------------------------------------------------------------------------------------
$TLS_htmltext_title['tc_exec_assignment']	= "Assign Testers to test execution";
$TLS_htmltext['tc_exec_assignment'] 		= "<h2>Purpose</h2>
<p>This page allows test leaders to assign users to particular Test Cases within the Test Plan.</p>

<h2>Get Started</h2>
<ol>
	<li>Choose a Test Case or Test Suite to test.</li>
	<li>Select a planned tester.</li>
	<li>Click the 'Save' button to submit assignment.</li>
	<li>Open the execution page to verify the assignment. You can set up a filter for users.</li>
</ol>";

// ------------------------------------------------------------------------------------------
$TLS_htmltext_title['planUpdateTC']	= "Update Test Cases in the Test Plan";
$TLS_htmltext['planUpdateTC'] 		= "<h2>Purpose</h2>
<p>This page allows the updating of a Test Case to a newer (different) version if a Test
Specification is changed. It often happens that some functionality is clarified during testing. " .
		" The user modifies the Test Specification, but the changes need to propagate to the Test Plan too. </p>

<h2>Get Started</h2>
<ol>
	<li>Choose a Test Case or Test Suite to update.</li>
	<li>Choose a new version from the combo-box menu for a particular Test Case.</li>
	<li>Click the 'Update Test plan' button to submit changes.</li>
	<li>To verify: open the execution page and view the header of the Test Case(s).</li>
</ol>";


// ------------------------------------------------------------------------------------------
$TLS_htmltext_title['test_urgency']	= "Specify tests with high or low urgency";
$TLS_htmltext['test_urgency'] 		= "<h2>Purpose</h2>
<p>TestLink allows setting the urgency of a Test Suite to affect the test priority of Test Cases. 
		Test priority depends on both Importance of Test Cases and Urgency defined in 
		the Test Plan. The Test Leader should specify a set of Test Cases that should be tested
		first. This helps to ensure that testing will cover the most important tests
		when under time pressure.</p>

<h2>Get Started</h2>
<ol>
	<li>Choose a Test Suite in the navigation panel on the left side of the page.</li>
	<li>Choose an urgency level (high, medium or low). Medium is the default. You can
	decrease priority for untouched parts of product and increase for components with
	significant changes.</li>
	<li>Click the 'Save' button to submit changes.</li>
</ol>
<p><i>For example, a Test Case with a High importance in a Test suite with Low urgency " .
		"will be Medium priority.</i>";


// ------------------------------------------------------------------------------------------

?>
