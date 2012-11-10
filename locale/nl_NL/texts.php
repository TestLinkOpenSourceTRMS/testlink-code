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

<h2>Werkwijze</h2>
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
	<i>Test Suites</i> en <i>Testgevallen</i> bekijken en bewerken. 
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

<h2>Werkwijze</h2>
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

<h2>Werkwijze</h2>
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
$TLS_htmltext_title['executeTest']	= "Testgeval Uitvoering";
$TLS_htmltext['executeTest'] 		= "<h2>Doel</h2>

<p>Hier kunt u Testgevallen uitvoeren: een testresultaat opgeven voor een Testgeval voor
een bepaalde Oplevering.</p>

<h2>Werkwijze</h2>

<ol>
	<li>Tenminste één Oplevering moet gedefinieerd zijn voor het Testplan.</li>
	<li>Selecteer een Oplevering van de drop-down.</li>
	<li>Als u alleen enkele Testgevallen wilt zien i.p.v. de hele boomstructuur, 
		kies filters om toe te passen. Klik dan 'Pas filter toe'.</li>	
	<li>Klik op een Testgeval in de boomstructuur.</li>
	<li>Voor het testresultaat in, samen met eventuele aantekeningen of fouten. </li>
	<li>Sla de resultaten op.</li>
</ol>
<p>Zie de help voor meer informatie over filters en instellingen: klik op de vraagteken.</p>

<p><i>NB: u kunt een probleem rapport rechtstreeks vanuit TestLink creëren.  
Om dit te doen moet TestLink zijn geconfigureerd om met uw Bug tracker samen te werken. </i></p>";

// ------------------------------------------------------------------------------------------
$TLS_htmltext_title['showMetrics']	= "Beschrijving van Testrapportage en Metrieken";
$TLS_htmltext['showMetrics'] 		= "<p>Rapporten hebben betrekking op een Testplan.
	Dit wordt bovenaan het navigatiepaneel gedefinieerd, en kan dus anders zijn dan het
	huidige Testplan voor testuitvoering.</p>
	<p>U kunt een rapport formaat selecteren:</p>
	<ul>
	<li><b>HTML</b> &ndash; rapport wordt in de webpagina getoond</li>
	<li><b>OpenOffice Writer</b> </li> 
	<li><b>OpenOffice Calc</b> </li>
	<li><b>MS Excel</b> </li>
	<li><b>HTML Email</b> - rapport wordt gemaild naar het e-mail adres van de gebruiker.</li>
	</ul>
	<p>De 'afdrukken' knop activeert rechtstreeks afdrukken.</p>
	<p>Er zijn meerdere rapporten beschikbaar; hun doel en functie wordt hieronder beschreven.</p>

<h3>Laatste Test Resultaat</h3>
Het 'laatste testresultaat' is een concept dat in meedere rapporten wordt gebruikt. Het wordt als 
volgt bepaald.
<ul> 
<li> Het wordt in eerste instantie bepaald door de Oplevering. De volgorde waarin Opleveringen 
aan een Testplan worden toegevoegd bepaalt welke het meest recente is. (Dus niet de opleverdatum). 
</li>
<li> Als een Testgeval meerdere keer wordt uitgevoerd voor een Oplevering (bijvoorbeeld omdat de 
tester de eerste keer een fout maakt) wordt de laatste uitvoering meegenomen. </li>
<li> Testgevallen die voor een bepaalde Oplevering niet worden uitgevoerd, tellen niet mee. Dus als 
een Testgeval op 'fout' staat voor Oplevering 1, 'geslaagd' voor Oplevering 2 en 'niet uitgevoerd' 
voor Oplevering 3, is het laatste testresultaat 'geslaagd'. </li>
<li>Het laatste testresultaat staat alleen op 'niet uitgevoerd' als het Testgeval voor geen enkele
Oplevering is uitgevoerd.</li>
</ul>


<h3>Testplan Rapport</h3>
<p>Dit bevat de Testgevallen.  </p> 
<p>Het heeft opties om de inhoud en structuur van het rapport te definieren. Klik op
een Test Suite om het rapport te genereren voor die Test Suite, of de bovenste rij
van de boomstructuur (Testproject) om het rapport te genereren voor alle Test Suites.</p>

<h3>Test Rapport</h3>
<p>Dit heeft een soortgelijke structuur als het Testplan rapport, maar bevat ook de testresultaten.

<h3>Algemene Testplan Metrieken</h3>
<p>Dit rapport toont de huidige status van een Testplan op basis van Test Suite, eigenaar en steekwoorden.
De huidige status wordt bepaald door de laatste Testresultaat (zoals hierboven beschreven.)</p>

<p>Toont de volgende informatie:</p>
<ul>
<li>Resultaten per hoogste-niveau Test Suite;</li>
<li>Resultaten op basis van prioriteit;</li>
<li>Resultaten per steekwoord</li>
<li>Status van mijlpalen.</li>
</ul>

<h3>Gefaalde, geblokkeerde em niet uitgevoerde Testgevallen</h3>
<p>Toont Testgevallen met genoemde status volgens het hierboven beschreven 'laatste testresultaat' logica.
Geblokkeerde en gefaalde Testgevallen tonen ook de bijbehorende foutmeldingen als men een 
geïntegreerde issue tracking systeem gebruikt.</p>

<h3>Testresultaat Matrix</h3>
<p>Toont de status van elk Testgeval voor elke actieve Oplevering. Bij gebruik van grote hoeveelheid
gegevens wordt export naar Excel aanbevolen.</p>

<h3>Charts</h3>
<p>Charts van algemene Testplan metrieken, volgens 'laatste testresultaat' logica.
<ul>
<li>Globale verdeling Testgevallen in geslaagd / gefaald / geblokkeerd / niet uitgevoerd. </li>
<li>Resultaten per steekwoord</li>
<li>Resultaten per hoogste-niveau Test Suite</li>
</ul>
";


// ------------------------------------------------------------------------------------------
$TLS_htmltext_title['planAddTC']	= "Testgevallen Toevoegen Aan / Verwijderen Van Testplan"; // testSetAdd
$TLS_htmltext['planAddTC'] 			= "<h2>Doel</h2>
	<p>Op deze pagina kan een gebruiker (met testleider rechten) Testgevallen toevoegen aan,
	of verwijderen van, een Testplan.</p>

<h2>Werkwijze</h2>
<ol>
	<li>Klik op een Test Suite om alle dochter Test Suites en Testgevallen te zien.</li>
	<li>Klik op de gewenste Testgevallen en dan op de 'Voeg toe / Verwijder geselecteerde' knop.
		Het is niet mogelijk om een Testgeval meerdere keer toe te voegen aan een Testplan.</li>
</ol>
";


// ------------------------------------------------------------------------------------------
$TLS_htmltext_title['tc_exec_assignment']	= "Testers toekennen voor test uitvoering";
$TLS_htmltext['tc_exec_assignment'] 		= "<h2>Doel</h2>
<p>Op deze pagina kunnen testleiders testers toekennen aan Testgevallen binnen een bepaald Testplan.
U kunt steeds naar deze pagina terugkeren door op het Testproject te klikken, bovenaan de 
boomstructuur. </p>

<h2>Werkwijze</h2>
<ol>
	<li>Kies een Test Suite of Testgeval in de boomstructuur.</li>
	<li>Kies een tester in de selectielijst naast het Testgeval.</li>
	<li>Klik de 'Opslaan' knop bovenaan om de Testgevallen toe te kennen</li>
</ol>

<h2>Bulk toewijzing</h2>
<ol>
	<li>Kies een Test Suite in de boomstructuur.</li>
	<li>Selecteer de gewenste Testgevallen. Gebruik desgewenst de 'alles aan/uitvinken' optie bovenaan.</li>
	<li>Selecteer de gewenste tester bovenaan ('Bulk gebruikerstoekenning').</li>
	<li>Klik de 'doe' knop naast de naam van de tester.</li>
	<li>Klik de 'Opslaan' knop bovenaan om de Testgevallen toe te kennen</li>
</ol>
";

// ------------------------------------------------------------------------------------------
$TLS_htmltext_title['planUpdateTC']	= "Testgeval versies bijwerken in het Testplan";
$TLS_htmltext['planUpdateTC'] 		= "<h2>Doel</h2>
	<p>Hier kunt u een Testgeval bijwerken tot een andere (meestal nieuwe) versie, als een 
	Testspecificatie gewijzigd is. Het komt vaak voor dat functionaliteit wordt gewijzigd,
	of een Testgeval wordt gecorrigeerd. Deze wijzigingen moeten worden doorgevoerd in het
	relevante Testplan (of Testplannen). </p>

<h2>Werkwijze</h2>
<ol>
	<li>In de boomstructuur, kies een Testgeval of Test Suite om bij te werken.</li>
	<li>Kies een andere versie uit de selectielijst voor elk Testgeval waar dat
	gewenst is.</li>
	<li>Klik de 'Testplan bijwerken' knop onderaan.</li>
</ol>";


// ------------------------------------------------------------------------------------------
$TLS_htmltext_title['test_urgency']	= "Testen specificeren met hoge of lage prioriteit";
$TLS_htmltext['test_urgency'] 		= "<h2>Doel</h2>
<p>Met TestLink kunt u de urgentie van een Testgeval instellen. Samen met het belang definieert
die de prioriteit van een Testgeval. Bij voorbeeld: een Testgeval met hoog belang in een Test Suite
met lage urgentie zal prioriteit 'midden' krijgen.</p> 

<p>U kunt bijvoorbeeld een lagere urgentie instellen voor
delen die in deze release niet zijn gewijzigd en hoge urgentie voor delen met vele wijzigingen.
Dit helpt om te zorgen dat de meest belangrijke testen worden uitgevoerd als men in tijdsnood zit.</p>

<h2>Werkwijze</h2>
<ol>
	<li>Kies een Test Suite in de boomstructuur links.</li>
	<li>Kies een urgentie niveau (hoog, midden, laag) Midden is de default.</li>
	<li>Klik de 'Opslaan' knop.</li>
</ol>
";


// ------------------------------------------------------------------------------------------

?>
