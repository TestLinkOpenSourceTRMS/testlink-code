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
$TLS_hlp_generateDocOptions = "<h2>Opties voor een gegenereerd document</h2>

<p>Met deze tabel kan de gebruiker Testgevallen filteren voordat ze worden getoond.
Als aangevinkt wordt de data getoond. Om de getoonde gegevens te wijzigen, de item
aan- of uitvinken, klik op 'Filter' en selecteer het gewenste dataniveau van de
boomstructuur.</p>

<p><b>Document kop:</b> Gebruikers kunnen documentkop informatie filteren, waaronder
Inleiding, Scope, Verwijzingen, Test Methodologie, Test Beperkingen.</p>

<p><b>Testgeval:</b> Gebruikers kunnen Testgeval informatie filteren, waaronder
Samenvatting, Stappen, Verwachte resultaten en Steekwoorden.</p>

<p><b>Testgeval Samenvatting:</b> Gebruikers kunnen Testgeval samenvatting informatie
filteren voor de Testgeval Titel, maar niet voor de Testgeval Body. Testgeval Titel
is alleen gedeeltelijk gescheiden van Testgeval Body om het mogelijk te maken om
Titels te bekijken met een beknopte samenvatting. Als een gebruiker de Testgeval Body 
wil bekijken, de samenvatting zit er altijd bij.</p>

<p><b>Inhoudsopgave:</b> TestLink neemt een lijst titles op met hyperlinks.</p>

<p><b>Output formaat:</b> Er zijn twee opties: HTML en MS Word. In het tweede geval roept de browser het MSWord component 
aan.</p>";

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
$TLS_hlp_customFields = "<h2>Gebruikersvelden</h2>
<p>Informatie over gebruikersvelden:</p>
<ul>
	<li>Gebruikersveld worden systeembreed gedefinieerd.</li>
	<li>Zij kunnen voor meerdere Testprojecten worden gebruikt.</li>
	<li>Zij worden gekoppeld aan een bepaald element (Test Suite of Testgeval).</li>
	<li>De volgorde waarin ze worden getoond kan per Testproject worden ingesteld.</li>
	<li>Het aantal gebruikersvelden is onbeperkt.</li>
</ul>

<p>De definitie van een gebruikersveld bevat de volgende attributen:</p>
<ul>
	<li>Naam veld</li>
	<li>Variabele caption naam; dit wordt als parameter aan de lang_get() API
	voor vertaling, of wordt aan de gebruiker getoond als hij niet in het
	taalbestand wordt gevonden.</li>
	<li>Type veld (string, numeric, float, enum, email).</li>
	<li>Enumeration waarden. Gebruik '|' karakters om de waarden te scheiden (eg: ROOD|GEEL|BLAUW).
	Van toepassing op list,
	multiselection list en combobox types. </li>
	<li>Default waarde (Nog niet geïmplementeerd).</li>
	<li>Minimum/maximum lengte voor het gebruikersveld. 0 om uit te schakelen. (Nog niet geïmplementeerd).</li>
	<li>'Regular expression' voor validatie gebruikersinput. Gebruik de syntaxis van 
	<a href=\"http://au.php.net/manual/en/function.ereg.php\">ereg()</a>  (Nog niet geïmplementeerd).</li>
	<li>Toon in testspecificatie</li>
	<li>Beschikbaar stellen in testspecificatie. Gebruiker kan de waarde wijzigen tijdens het ontwerp
	van Testgevallen.</li>
	<li>Toon bij testuitvoering.</li>
	<li>Beschikbaar stellen in testuitvoering. Gebruiker kan de waarde wijzigen tijdens testuitvoering.</li>
	<li>Toon bij ontwerp Testplan.</li>
	<li>Beschikbaar stellen in ontwerp Testplan. Gebruiker kan de waarde wijzigen tijdens 
	ontwerp Testplan (toevoegen Testgevallen aan Testplan).</li>
	<li>Beschikbaar voor. Geeft aan wat bij wat voor type item het veld hoort.</li>
</ul>

<p>Alle gebruikersvelden worden in een database veld opgeslagen van type  VARCHAR(255).</p>
";

// execMain.html
$TLS_hlp_executeMain = "<h2>Testgevallen uitvoeren</h2>
<p>Hier kan men Testgevallen uitvoeren. Uitvoering houdt in dat men een Testgeval een testresultaat
(succes, gefaald, geblokkeerd) toewijst voor een bepaalde Build. </p>

<p>Men kan ook rechtstreeks meldingen invoeren in een Issue Tracking Systeem. 
Dat moet wel door de beheerder zijn ingesteld. Zie de installatie handleiding voor meer informatie. </p>";


//bug_add.html
$TLS_hlp_btsIntegration = "<h2>Bugs toevoegen aan Testgeval</h2>
<p><i>(Alleen beschikbaar als bug tracker geconfigureerd is)</i>
TestLink heeft een heel eenvoudige integratie met Bug Tracking Systems (BTS).
Dit wordt bereikt door links naar BTS pagina's voor de volgende features: </p>
<ul>
	<li>Nieuwe bug toevoegen</li>
	<li>Toon informatie over een bestaande bug</li>
</ul>

<h3>Proces om een bug toe te voegen</h3>
   <ol>
   <li>Gebruik de BTS link om het BTS te openen om een bug toe te voegen.</li>
   <li>Kopieer de BUGID toegekend door het BTS (Ctrl-C of opschrijven).</li>
   <li>Voer de BUGID in het TestLink invoer veld.</li>
   <li>Gebruik de 'bug toevoegen' knop.</li>
   </ol>  

<p>Na het sluiten van de 'bug toevoegen' pagina, verschijnen de bug gegevens op de uitvoer pagina.</p>
";


// execFilter.html
$TLS_hlp_executeFilter = "<h2>Instellingen</h2>

<p>In het Instellingen paneel kunt u het Testplan, Oplevering en Platform (als beschikbaar)
selecteren voor het uitvoeren van testen.</p>

<h3>Testplan</h3>
<p>U kiest het gewenste Testplan. Op basis hiervan worden de beschikbare Opleveringen 
getoond. Na het kiezen van een Testplan worden de filters reset.</p>

<h3>Platform</h3>
<p>Als de Platform feature in gebruik is, moet u het gewenste Platform kiezen voordat
u testen kunt uitvoeren.</p>

<h3>Oplevering</h3>
<p>U kiest de Oplevering waarvoor u Testgevallen wilt uitvoeren.</p>

<h2>Filters</h2>
<p>Filters geven de mogelijkheid om het aantal getoonde Testgevallen te beperken.
Stel de gewenste filters in en klik 'Toepassen'</p>

<p>Met geavanceerde filters kunt u een lijst van waarden voor een filter kiezen
door Ctrl-klik te gebruiken in een Multi-Select ListBox.</p>

<h3>Steekwoord Filter</h3>

<p>U kunt Testgevallen filteren d.m.v. de toegekende steekwoorden. U kunt
meerdere steekwoorden kiezen met Ctrl-klik. Als u meerdere steekwoorden selecteert
kunt u kiezen tussen het tonen van alleen Testgevallen waaraan alle gekozen steekwoorden
zijn toegekend (radioknop 'En') of de Testgevallen waaraan tenminste één van de steekwoorden 
toegekend is (radioknop 'Of'). </p>

<h3>Filter op prioriteit</h3>
<p>U kunt Testgevallen filteren op basis van testprioriteit. De prioriteit is de combinatie
van 'belang' en 'urgentie' in het huidige Testplan.</p>

<h3>Filter op tester</h3>
<p>U kunt Testgevallen selecteren die niet aan een tester zijn toegekend ('niemand') of
wel ('iemand'). U kunt ook Testgevallen selecteren die aan een specifieke tester zijn
toegekend. In dit laatste geval kunt u tevens Testgevallen selecteren die aan niemand
zijn toegekend. Dit is handig als u alle Testgevallen wilt selecteren die niet aan
iemand anders zijn toegekend. </p>

<h3>Filter op resultaat</h3>
<p>U kunt Testgevallen selecteren die een bepaald resultaat hebben. Daarnaast kiest u een Oplevering:
de huidige Oplevering voor testuitvoering, een specifieke Oplevering, alle Opleveringen, enige Oplevering
of het laatste resultaat.
Met de combinatie kunt u verscheidene nuttige selecties maken: bijvoorbeeld alle 
testen die nooit zijn uitgevoerd, of alle testen die bij de vorige Oplevering hebben
gefaald.</p>
";


// newest_tcversions.html
$TLS_hlp_planTcModified = "<h2>Nieuwste versies van gekoppelde Testgevallen</h2>
<p>Een lijst wordt getoond van alle Testgevallen in het huidige Testplan waarvoor
een nieuwere versie beschikbaar is.</p>";


// requirementsCoverage.html
$TLS_hlp_requirementsCoverage = "<h3>Requirements Dekking</h3>
<p>Deze feature toont de dekking van gebruikers- of systeemrequirements door
Testgevallen. Navigeer via link 'Requirement Specificatie' op het startscherm.</p>

<h3>Requirements Specificatie</h3>
<p>Requirements zijn gegroepeerd in 'Requirements Specificaties' die verwant zijn
aan een Testproject. Men kan een omschrijving of aantekeningen in het <b>Scope</b>
veld plaatsen.</p>

<p><b><a name='total_count'>Overschreven aantal REQs</a></b>  wordt gebruikt om
requirements dekking te berekenen in het geval dat nog niet alle requirements
zijn ingevoerd. De waarde 0 betekent dat het werkelijk aantal Requirements wordt gebruikt
voor de berekening. Bij een waarde groter dan nul wordt de waarde van het veld gebruikt.
Als bijvoorbeeld 50 Requirements zijn ingevoerd, die allemaal Testgevallen hebben, maar 
dit getal op 200 wordt gezet, dan is de dekking 25% (i.p.v. 100%).
</p>

<h3><a name=\"req\">Requirements</a></h3>
<p>Klik op de titel van een Requirements Specificatie. U kunt Requirements creëren,
bewerken, verwijderen of importeren. Elke Requirement heeft een titel, scope en status.
De status kan 'normaal' zijn of 'niet testbaar'. Niet testbare Requirements worden niet
in de metrieken meegeteld. Deze waarde kan worden gebruikt voor features die nog niet 
geïmplementeerd zijn of Requirements die nog niet correct zijn.</p> 

<p>U kunt automatisch Testgeval skeletten creëren voor de aangevinkte Requirements
in de specificatie lijst. Deze Testgevallen worden aangemaakt in de Test Suite met de 
geconfigureerde naam <i>(default is: &#36;tlCfg->req_cfg->default_testsuite_name = 
\"Test suite created by Requirement - Auto\";)</i>. De titel en scope zijn naar deze
Testgevallen gekopieerd.</p>
";


$TLS_hlp_req_coverage_table = "<h3>Dekking:</h3>
Een waarde van bijvoorbeeld \"40% (8/20)\" betekent dat 20 Testgevallen moeten voor deze Requirement
worden creëerd om hem volledig te testen. 8 bestaan al en zijn aan deze Requirement gekoppeld,
wat betekent dat de dekking 40 procent is.
";


// planAddTC_m1.tpl
$TLS_hlp_planAddTC = "<h2>Gebruikersvelden</h2>
Als u gebruikersvelden aan het Testproject heeft toegekend met:<br />
 Aanzetten bij: Testplan ontwerp, en <br />
 Beschikbaar voor: Testplan'<br />
dan ziet u deze velden hier alleen voor Testgevallen die aan het Testplan zijn gekoppeld.
";


// resultsByTesterPerBuild.tpl
$TLS_hlp_results_by_tester_per_build_table = "<b>Meer informatie over testers:</b><br />
Als u op de naam van een tester klik in deze tabel krijgt u een meer gedetailleerd
overzicht van alle Testgevallen die aan die tester zijn toegewezen en zijn/haar test voortgang.<br /><br />
<b>NB:</b><br />
Dit rapport toont de Testgevallen die zijn toegekend aan een specifieke gebruiker en voor een bepaalde
Oplevering zijn uitgevoerd. Ook als het Testgeval daadwerkelijk door een andere tester is uitgevoerd
verschijnt het Testgeval voor de toegewezen tester.
";


// req_edit
$TLS_hlp_req_edit = "<h3>Interne links voor scope:</h3>

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

<h3>Logbericht voor wijzigingen:</h3>
<p>Wanneer een wijziging wordt gemaakt vraagt TestLink om een logbericht. Dit bericht
is ten behoeve van de traceerbaarheid van de eisen en testen. Als alleen de scope is 
gewijzigd bent u vrij om te kiezen of u een nieuwe revisie wilt maken of niet.
Als iets anders dan de scope wordt gewijzigd dan moet u een nieuwe revisie maken.</p>
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
