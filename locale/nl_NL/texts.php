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
 * @version    	CVS: $Id: texts.php,v 1.2 2010/06/24 17:25:53 asimon83 Exp $
 * @link 		http://www.teamst.org/index.php
 *
 **/


// --------------------------------------------------------------------------------------
$TLS_htmltext_title['error']	= "Applicatie fout";
$TLS_htmltext [ 'error'] = "<p>Er is een onverwachte fout gebeurt. Controleer event viewer of ".
"logboek voor meer informatie.</p><p>U bent van harte welkom om het probleem te melden. Ga naar onze ".
"<a href='http://www.teamst.org'>website</a></p>";



$TLS_htmltext_title['assignReqs']	= "Vereisten toewijzen aan testcases";
$TLS_htmltext['assignReqs'] 		= "<h2>Doel:</h2>
<p>Gebruikers kunnen de relaties tussen vereisten en testcases aanmaken. Een test ontwerper zou kunnen
0..n tot 0..n relaties definiëren. D.w.z Één testcase kan worden toegewezen aan geen, één of meerdere
vereisten en vice versa. Dergelijke traceerbaarheidsmatrix helpt test dekking te onderzoeken
van vereisten en erachter te komen welke mislukken tijdens een test. Deze
analyse dient als bevestiging dat aan alle gedefinieerde verwachtingen wordt voldaan. </p>

<h2>Aan de slag:</h2>
<ol>
<li>Kies een testcase in de boomstructuur aan de linkerkant. De combobox met lijst van vereisten
specificaties wordt bovenaan het werkgebied getoond. </li>
<li>Kies een vereisten specificatie document als er meerdere zijn gedefinieerd. 
TestLink herlaadt automatisch de pagina. </li>
<li>Een blok in het midden van het werkgebied lijsten toont van alle vereisten (van de gekozen specificatie), die
zijn verbonden met de testcase. Het onderste blok toont een lijst met  'Beschikbare vereisten' alle
vereisten die geen relatie hebben met
de huidige test case. Een ontwerper kan vereisten die worden gedekt door deze testcase markeren
en klikken op de knop 'Toewijzen'. Deze nieuwe toegewezen testcases worden getoond in
het middelste blok 'Toegewezen vereisten'. </li>
</ol>";


// --------------------------------------------------------------------------------------
$TLS_htmltext_title['editTc']	= "Testspecificatie";
$TLS_htmltext['editTc'] = "<p>De <i>Testspecificatie </i> stelt gebruikers in staat  alle bestaande testcases te ".
"en aan te passen <i>Testsuites</i> en <i>Testcases</i>.".
"Testcases hebben versiebeheer en alle vorige versies zijn beschikbaar en kunnen hier worden".
"bekeken en beheerd.</p>

<h2>Aan de slag: </h2>
<ol>
<li>Kies uw <i>Testproject</i> in de navigatiestructuur (de root node). <i>Let op: ".
"U kunt altijd het actieve testproject wijzigen door een andere optie uit de".
"drop-down lijst in de rechterbovenhoek te kiezen.</i></li>
<li>Maak een nieuwe testsuite door te klikken op <b>Nieuwe testsuite </b>. Testsuites kunnen ".
"structuur brengen in uw testdocumenten volgens uw conventies (functioneel/niet-functioneel".
"testen, product componenten of functies, change requests, etc.). De omschrijving van ".
"een testsuite kan de reikwijdte van de inbegrepen testcases, standaardconfiguratie,".
"links naar relevante documenten, beperkingen en andere nuttige informatie bevatten. In het algemeen, " .
"alle informaties die gemeenschappelijk is voor de onderliggende testcases. Testsuites volgen ".
" zien er uit als &quot;mappen&quot;, waardoor gebruikers testsuites kunnen verplaatsen en kopiëren binnen".
"het testproject. Ook kunnen ze worden geïmporteerd of geëxporteerd (inclusief testcases). </li>
<li>Testsuites zijn schaalbare mappen. Gebruikers kunnen testsuites verplaatsen of kopiëren binnen ".
"het testproject. Test Suites kunnen worden geïmporteerd of geëxporteerd (inclusief testcases).
<li>Selecteer uw nieuwe testsuite in de navigatiestructuur en maak ".
"een nieuwe testcases door te klikken op <b>Testcase </b>. Een testcase specificeert ".
"een bepaalde test scenario, verwachte resultaten en de gedefiniëerde gebruikersvelden".
"In het testproject (zie de handleiding voor meer informatie). Het is ook mogelijk ".
"<b>trefwoorden</b> toe te wijzen voor een betere traceerbaarheid. </li>
<li>Navigeer via de boomstructuur aan de linkerkant en bewerk de data. Elke testcase heeft zijn eigen geschiedenis.</li>
<li>Wijs uw gecreëerde testspecificatie voor <span class=\"help\" onclick=
	\"Javascript: open_help_window ( 'glosary', '$locale'); \">Testplan </span> wanneer uw testcases klaar zijn.</li>
</ol>

<p>Met TestLink kunt u testcases in testsuites organiseren. ".
"Testsuites kunnen worden genest binnen andere testsuites, zodat u hiërarchieën van testsuites kan maken.
Vervolgens kunt u deze informatie samen met de testcases afdrukken.</p>";


// ------------------------------------------------------------------------------------------

$TLS_htmltext_title['searchTc']	= "Testcase zoeken pagina";
$TLS_htmltext['searchTc'] 		= "<h2>Doel:</h2>

<p>Navigatie op basis van trefwoorden en/of gezochte strings. Het zoeken is niet
hoofdlettergevoelig Resultaten bevatten enkel testcases van het actuele testproject.</p>

<h2>Om te zoeken: </h2>

<ol>
<li>Schrijf de gezochte string in een passend veld. Laat ongebruikte velden in het formulier leeg.</li>
<li>Kies een trefwoord of laat de waarde 'Niet van toepassing'. </li>
<li>Klik op de knop 'Zoeken'. </li>
<li>Alle uitgevoerde testcases worden getoond. U kunt testcases wijzigen via de 'titel' link. </li>
</ol>";

/* contribution by asimon for 2976 */
// requirements search
// ------------------------------------------------------------------------------------------
$TLS_htmltext_title['searchReq']	= "Vereisten zoek pagina";
$TLS_htmltext['searchReq'] 		= "<h2>Doel:</h2>

<p>Navigatie op basis van trefwoorden en/of gezochte strings. Het zoeken is niet
hoofdlettergevoelig. Resultaten bevatten enkel vereisten van het actuele testproject.</p>

<h2>Om te zoeken:</h2>

<ol>
<li>Schrijf de gezochte string in een passend veld. Laat ongebruikte velden in het formulier leeg.</li>
<li>Kies het gewenste trefwoord of laat de waarde 'Niet van toepassing'. </li>
<li>Klik op 'Zoeken' te klikken. </li>
<li>Alle vervulde vereisten worden getoond. U kunt vereisten aanpassen via de 'titel' link.</li>
</ol>";

// requirement specification search
// ------------------------------------------------------------------------------------------
$TLS_htmltext_title['searchReqSpec']	= "Vereisten specificatie zoekpagina";
$TLS_htmltext['searchReqSpec'] 		= "<h2>Doel:</h2>

<p>Navigatie op basis van trefwoorden en/of gezochte strings. Het zoeken is niet
hoofdlettergevoelig. Resultaten bevatten slechts vereisten specificaties van het actuele testproject.</p>

<h2>Om te zoeken: </h2>

<ol>
<li>Schrijf de gezochte string in een passend veld. Laat ongebruikte velden in het formulier leeg. </li>
<li>Kies de gewenste trefwoorden of laat de waarde 'Niet van toepassing'.</li>
<li>Klik op 'Zoeken'.</li>
<li>Alle vervulde vereisten specificaties worden getoond. U kunt de vereisten specificaties te wijzigen via de 'Titel' link. </li>
</ol>";
/* end contribution */


// ------------------------------------------------------------------------------------------
$TLS_htmltext_title['printTestSpec']	= "Druk testspecificatie"; //printTC.html
$TLS_htmltext [ 'printTestSpec'] = "<h2>Doel: </h2>
<p>Hier kunt u een enkele testcase of alle testcases het binnen een testsuite afdrukken,
of alle testcases in een testproject of testplan.</p>
<h2>Aan de slag: </h2>
<ol>
<li>
<p>Selecteer de onderdelen van de testcases die u wilt weergeven, en klik vervolgens op een test case, 
testsuite of het testproject. Een afdrukbare pagina wordt getoond. </P>
</li>
<li><p>Met de \"Toon als \" dropbox in het navigatiepaneel kunt u kiezen of u 
de informatie wilt weergegeven als HTML, OpenOffice Writer of in een Micosoft Word document. 
Zie <span class=\"help\" onclick=\"javascript:open_help_window('printFilter',
'{$locale}');\">help</span> voor meer informatie </p>.
</li>
<li><p>Gebruik de printfunctionaliteit van uw browser om de informatie af te drukken.<br />
<i>. Opmerking: Zorg ervoor dat u enkel het rechtse frame afdrukt.</i></p>
</li>
</ol>";


// ------------------------------------------------------------------------------------------
$TLS_htmltext_title['reqSpecMgmt']	= "Vereisten specificatie ontwerp"; //printTC.html
$TLS_htmltext['reqSpecMgmt'] 			= "<p>U kunt vereisten specificatie documenten beheren.</p>

<h3>Vereisten specificatie </h3>

<p>De vereisten zijn gegroepeerd per <b>vereisten specificatie document</b>, dat gerelateerd is aan een
testproject. <br />TestLink ondersteunt geen versies voor vereisten specificaties
of vereisten. Dus, moet een document versie toegevoegd worden nadat een specificatie <b>Titel</b>.
Een gebruiker kan een eenvoudige beschrijving of opmerkingen toevoegen aan het <b>Bereik</b> veld.</p>

<p><b><a name='total_count'>Overschreven telling van vereisten</a></b>dient om de
 vereisten dekking te evalueren in het geval dat niet alle vereisten zijn toegevoegd aan TestLink.
De waarde <b>0</b> betekent dat de huidige telling van vereisten wordt gebruikt
voor de statistieken. </p>
<p><i>Bv SRS bevat 200 vereisten, maar slechts 50 worden toegevoegd in TestLink. Test
dekking is 25% (ervan uitgaande dat de 50 extra vereisten daadwerkelijk zal worden getest). </i></p>

<h2><a name='req'>Vereisten</a></h2>

<p>Klik op de titel van een bestaande vereisten specificatie. Als er geen bestaan, ".
"klik op de project node om er een te maken. U kunt vereisten maken, bewerken, verwijderen
of importeren voor het document. Elke vereiste heeft een titel, bereik en status.
Een status moet ofwel 'Normaal' of 'Niet toetsbaar' zijn. Niet toetsbare vereisten worden niet meegeteld
in statistieken. Deze parameter moet worden gebruikt voor niet geïmplementeerde functies en
verkeerd ontworpen vereisten. </p>

<p>U kunt nieuwe testcases voor de vereisten aanmaken door het gebruik van multi actie met gecontroleerde
vereisten in het specificatie scherm. Deze testcases worden gemaakt in een testsuite
met de naam opgegeven in configuratie <i>(standaard is: \$tlCfg->req_cfg->default_testsuite_name =
'Test suite created by Requirement - Auto';) </i>. Titel en bereik worden gekopieerd naar deze testcases.</p>";


$TLS_htmltext_title['printReqSpec'] = "Print vereisten specificatie"; 
$TLS_htmltext['printReqSpec'] = "<h2>Doel: </h2>
<p>Vanaf hier kunt u een enkele vereiste af te drukken, alle vereisten binnen een vereisten specificatie,
of alle vereisten in een testproject. </p>
<h2>Aan de slag: </h2>
<ol>
<li>
<p>Selecteer de onderdelen van de vereisten die u wilt weergeven, en klik dan op een vereiste, 
vereisten specificatie, of het testproject. Een afdrukbare pagina wordt getoond.</p>
</li>
<li><p>Met de \"Toon als\" drop-box in het navigatiepaneel kunt u kiezen of u 
de informatie wilt weergeven als HTML, OpenOffice Writer of in een Micosoft Word document. 
Zie <span class=\"help\" onclick=\"javascript:open_help_window('printFilter',
'{$locale}');\">help</span> voor meer informatie.</p>
</li>
<li><p>Gebruik de printfunctionaliteit van uw browser om de informatie af te drukken.<br />
<i>Opmerking: Zorg ervoor dat u alleen het rechter frame afdrukt.</i></p>
</li>
</ol>";


// ------------------------------------------------------------------------------------------
$TLS_htmltext_title['keywordsAssign']= "Trefwoorden toewijzen";
$TLS_htmltext['keywordsAssign'] = "<h2>Doel: </h2>
<p>De Trefwoorden toewijzen pagina is de plaats waar gebruikers 
trefwoorden kunnen toewijzen aan bestaande testsuites of testcases</p>

<h2>Om trefwoorden toe te wijzen: </h2>
<ol>
<li>Selecteer een testsuite of testcase in de boomstructuur
aan de linkerkant. </li>
<li>In het veld bovenaan rechts kunt u beschikbare 
trefwoorden toewijzen aan iedere testcase. </li>
<li>In de selecties eronder kunt u testcases toewijzen op een meer
granulair niveau. </li>
</ol>

<h2>Belangrijke informatie over trefwoorden toewijzen aan testplannen: </h2>
<p>Trefwoorden die u aan de specificatie toekent zullen alleen toegepast worden op testcases
in uw testplannen als het testplan de meest recente versie van de testcase bevat.
Als een testplan oudere versies van een testcase bevat, zullen de trefwoorden die u nu toewijst
niet verschijnen in het testplan.
</p>
<p>TestLink gebruikt deze aanpak, zodat oudere versies van testcases in testplannen niet worden beïnvloed
door trefwoorden die u aan de meest recente versie van de testcase toewijst. Als u wilt dat de
testcases in uw testplan worden bijgewerkt, controleer dan eerst of ze up-to-date met behulp van de 'update
gewijzigde testcases functie 'VOORDAT u trefwoorden toewijst.</p>".


// ------------------------------------------------------------------------------------------
$TLS_htmltext_title['executeTest']= "Testcases uitvoeren";
$TLS_htmltext['executeTest'] = "<h2>Doel: </h2>

<p>Hiermee kan de gebruiker testcases uitvoeren. De gebruiker kan een testresultaat toewijzen
aan testcases voor een build. Zie help voor meer informatie over filters en instellingen ".
"(Klik op het vraagteken). </P>

<h2>Aan de slag: </h2>

<ol>
<li>Er moet een build voor het testplan gedefinieerd zijn.</li>
<li>Selecteer een build uit de keuzelijst</li>
<li>Als u slechts een paar testcases wilt zien in plaats van de hele boomstructuur,
kunt u filters toepassen. Klik op de \"Toepassen\"-knop 
nadat u de filters heeft veranderd.</li>
<li>Klik op een testcase in de menustructuur.</li>
<li>Vul het testcase resultaat en eventuele notities of bugs in.</li>
<li>Resultaat opslaan.</li>
</ol>
<p><i>Opmerking: TestLink moet worden geconfigureerd om samen te werken met je Bug tracker 
als je wilt een probleem rapport rechtstreeks vanuit de GUI wilt maken of zoeken</i></p>".

// ------------------------------------------------------------------------------------------
$TLS_htmltext_title['showMetrics']= "Beschrijving van de testrapporten en statistieken";
$TLS_htmltext['showMetrics'] = "<p>Rapporten zijn gerelateerd aan een testplan".
"(Gedefinieerd bovenaan de navigatie). Dit testplan kan anders zijn dan het
huidige testplan voor de uitvoering. U kunt ook een rapport formaat kiezen: </p>
<ul>
<li><b>Normaal</b>- rapport wordt weergegeven in de webpagina</li>
<li><b>OpenOffice Writer</b>- rapport word naar OpenOffice Writer geëxporteerd /li>
<li><b>OpenOffice Calc</b>- rapport word naar OpenOffice Calc geëxporteerd</li>
<li><b>MS Excel</b>- rapport word naar Microsoft Excel geëxporteerd </li>
<li><b>HTML e-mail</b>- rapport word naar het e-mailadres gebruiker gemaild</li>
<li><b>Charts</b>- rapport met grafieken (flash-technologie) </li>
<ul>

<p>De knop afdrukken activeert het afdrukken van een rapport (zonder navigatie).</p>
<p>Er zijn verschillende afzonderlijke rapporten om uit te kiezen, hun doel en functie worden hieronder toegelicht.</p>

<h3>Testplan</h3>
<p>Het document 'Testplan' heeft opties om een ​​inhoud en een document structuur te definiëren.</p>

<h3>Testrapport</h3>
<p>In het document 'Testrapport' heeft opties om een ​​inhoud en document structuur.
Het omvat testcases samen met de testresultaten.</p>

<h3>Algemene testplan statistieken</h3>
<p>Op deze pagina zie je alleen de meest actuele status van een testplan per testsuite, eigenaar en trefwoord.
De meest 'actuele status' wordt bepaald door de meest recente build waarop testcases werden uitgevoerd.
Bijvoorbeeld, als een testcase over meerdere builds werd uitgevoerd, wordt alleen het laatste resultaat meegenomen.</p>

<p>'Laatste testresultaat' is een concept dat in vele rapporten gebruikt wordt en wordt als volgt bepaald:</p>
<ul>
<li>De volgorde waarin builds worden toegevoegd aan een testplan bepaalt welke build de meest recente is. De resultaten
uit de meest recente build hebben voorrang ten opzichte van oudere builds. Bijvoorbeeld, als een test
'Gefaald' is in build 1, en 'OK' in build 2, zal het laatste resultaat 'OK' zijn.</li>
<li>Als een testcase meerdere keren wordt uitgevoerd op dezelfde build, krijgt de meest recente uitvoering
voorrang. Bijvoorbeeld, als build 3 wordt vrijgegeven aan uw team en tester 1 markeert deze als 'OK' om 14:00,
en tester 2 markeert het als 'gefaald' om 15:00 wordt de test als 'gefaald' getoond.</li>
<li>Testcases vermeld als 'niet uitgevoerd' in een build worden niet in aanmerking genomen. Bijvoorbeeld, als u
een case als 'OK' zet in build 1, en niet uitvoert in build 2, zal het laatste resultaat worden beschouwd als
'OK'.</li>
<ul>
<p>De volgende tabellen worden weergegeven:</p>
<ul>
<li><b>Resultaten van topniveau testsuites </b>
Geeft een overzicht van de resultaten van elke top level suite. Totaal aantal testcases, geslaagd, gefaald, geblokkeerd, niet uitgevoerd, en het percentage
voltooide tests zijn opgenomen. Een 'voltooide' testcase is er een met als resultaat geslaagd, gefaald, of geblokkeerd.
Resultaten voor top level suites bevatten alle kinderen suites. </li>
<li><b>Resultaten op trefwoord </b>
Geeft alle trefwoorden die aan testcases in het huidige testplan zijn toegewezen, en de resultaten in verband
met hen. </li>
<li><b>Resultaten eigenaar </b>
Lijst van elke eigenaar met testcases aan hen toegewezen in het huidige testplan. Testcases die
zijn niet toegewezen, worden geteld onder de 'niet-toegewezen' rubriek. </li>
<ul>

<h3>De Overall Build Status </h3>
<p>Geeft de uitvoering resultaten voor elke build. Voor elke build, het totale testcases, totaal pas,
% Pas, totale falen, falen%, geblokkeerd,% geblokkeerd, niet lopen,% niet worden uitgevoerd.  Als een testcase is uitgevoerd
tweemaal op dezelfde bouw, de laatste uitvoering wordt rekening gehouden. </p>

<h3>Query statistieken</h3>
<p>Dit rapport bestaat uit een query formulier pagina en een query resultaten pagina die de opgevraagde gegevens bevat.
De Query Form pagina presenteert met een query pagina met 4 velden. Elk veld is ingesteld op een standaard die
het aantal testcases en builds maximaliseert waarvoor de query moet worden uitgevoerd. Door de velden te veranderen
kan de gebruiker resultaten filtere en specifieke rapporten voor specifieke eigenaars, trefwoorden, testsuite 
en build combinaties genereren.</p>

<ul>
<li><b>trefwoord </b> 0->1 trefwoorden kunnen geselecteerd worden. Standaard is geen trefwoord geselecteerd. Als geen trefwoord is
geselecteerd, dan worden alle testcases beschouwd, ongeacht de trefwoorden. Trefwoorden zijn toegewezen
in de test specificatie of trefwoord beheer pagina's.  Trefwoorden toegewezen aan testcases overspannen alle testplannen,
en alle versies van een testcase. Bent u geïnteresseerd in de resultaten voor een bepaald trefwoord
dan kunt u dit veranderen. </li>
<li><b>eigenaar</b> 0->1 eigenaren kunnen worden geselecteerd. Standaard is geen eigenaar geselecteerd. Als geen eigenaar is geselecteerd,
dan zullen alle testcases ongeacht de eigenaar worden beschouwd.  Momenteel is er geen functionaliteit
om te zoeken naar 'toegewezen' testcases.  Eigendom wordt toegekend door de 'testcase uitvoeren toewijzen' pagina,
en wordt gedaan per testplan. Bent u geïnteresseerd in het werk van een specifieke tester
kunt u dit wijzigen.</li>
<li><b>top level suite </b>0->n top level suites kunnen geselecteerd worden. Standaard zijn alle suites geselecteerd.
Alleen suites die zijn geselecteerd zullen worden opgevraagd voor resultaat statistieken. Als u alleen in de resultaten
voor een specifieke suite geïnteresseerd bent kunt u dit veranderen. </li>
<li><b>Builds</b> 1->n builds kunnen worden geselecteerd.  Standaard zijn alle builds zijn geselecteerd. Alleen met executies
uitgevoerd voor de geslecteerde build zal rekening worden gehouden bij de productie van statistieken.  Bijvoorbeeld als u
wilt zien hoeveel testcases werden uitgevoerd op de laatste 3 builds kunt u dit aanpassen.
Trefwoord, eigenaar en top level suite selecties zal het aantal testcases bepalen van uw testplan
die worden gebruikt om statistieken per suite en per testplan te berekenen. Bijvoorbeeld, als u eigenaar 'Greg' selecteert,
trefwoord 'prioriteit 1' en alle beschikbare testsuites zullen alleen Prioriteit 1 testcases toegewezen aan Greg
beschouwd. De '# testcases' totalen die u ziet in het rapport zal worden beïnvloed door deze 3 keuzes.
Build selecties zullen van invloed zijn als een case wordt beschouwd als 'OK', 'gefaald', 'geblokkeerd' of 'niet uitgevoerd'.
Hiervoor kunt u refereren naar 'Laatste testresultaat' regels zoals ze hierboven zijn beschreven.</li>
<ul>
<p>Klik op de knop 'Verzenden' om door te gaan met de query en de resultaatpagina te tonen.</p>

<p>Op de query rapport pagina wordt weergegeven: </p>
<ol>
<li>De query parameters die werden gebruikt om het rapport te maken </li>
<li>Totalen voor het gehele testplan </li>
<li>Een uitsplitsing per suite van de totalen (som / OK / gefaald / geblokkeerd / niet uitgevoerd) en alle executies uitgevoerd
op die suite.  Als een test case niet meer dan een keer is uitgevoerd in meerdere meerdere builds zullen alle executies 
weergegeven worden die zijn uitgevoerd met de geselecteerde build. Echter, de samenvatting voor die suite zal alleen
de 'laatste testresultaten' voor de geselecteerde build bevatten. </li>
</ol>

<h3>Geblokkeerde, gefaalde, en niet uitgevoerde testcase rapporten </h3>
<p>Deze tonen alle momenteel geblokkeerde, gefaalde, of niet uitgevoerde testcases. 'Laatste testresultaat'
logica (die hierboven wordt beschreven in algemene testplan statistieken) wordt opnieuw gebruikt om te bepalen of
een testcase moet worden beschouwd geblokkeerd, gefaald of niet uigevoerd.  Voor de geblokkeerde en gefaalde testcase rapporten
worden de bijbehorende bugs weergegeven als de gebruiker een geïntegreerd storingmeldingssysteem gebruikt.</p>

<h3>Testrapport</h3>
<p>De status van elke testcase in elke build. Het meest recente resultaat zal worden gebruikt 
als een testcase meerdere keren werd uitgevoerd op dezelfde build. Het is aan te raden om dit rapport te exporteren
naar Excel-formaat voor eenvoudiger browsen als een grote dataset wordt gebruikt.</p>

<h3>Grafieken - Algemene testplan statistieken</h3>
<p>'Laatste testresultaat' logica wordt gebruikt voor alle vier de grafieken die u zult zien. De grafieken zijn geanimeerd om 
 van de statistieken van het huidige testplan te visualiseren. De vier grafieken zijn: </p>
<ul><li>cirkeldiagram van de totale OK / gefaald / geblokkeerd / niet uitgevoerde testcases</li>
<li>Staafdiagram van de resultaten per trefwoord </li>
<li>Staafdiagram van de resultaten per eigenaar </li>
<li>Staafdiagram van de resultaten per Top Level Suite </li>
<ul>
<p>De staven in de staafdiagrammen zijn zodanig gekleurd dat de gebruiker bij benadering het aantal kan identificeren.</p>

<h3>Totaal aantal bugs voor elke testcase</h3>
<p>Dit rapport toont elke testcase met alle bugs ervoor ingediend voor het gehele project.
Dit rapport is alleen beschikbaar als een Bug Tracking Systeem is aangesloten </p>".


// ------------------------------------------------------------------------------------------
$TLS_htmltext_title['planAddTC']= "Testcases aan testplan toevoegen en verwijderen"; // testSetAdd
$TLS_htmltext['planAddTC'] = "<h2>Doel: </h2>
<p>Hiermee kan een gebruiker (met leider rol) testcases aan een testplan toevoegen of verwijderen.</p>

<h2>Testcases toevoegen of verwijderen</h2>
<ol>
<li>Klik op een testsuite om alle onderliggende testsuites en testcases te zien.</li>
<li>Als u klaar bent klik op de 'Testcases toevoegen/verwijderen' knop om de testcases toe te voegen of te verwijderen.
Let op: Het is niet mogelijk om dezelfde testcase meerdere keren toe te voegen.</li>
</ol>";

// ------------------------------------------------------------------------------------------
$TLS_htmltext_title['tc_exec_assignment']= "Testers toewijzen";
$TLS_htmltext['tc_exec_assignment'] = "<h2>Doel </h2>
<p>Op deze pagina kunnen testleiders testcases binnen een testplan aan testers toewijzen.</p>

<h2>Aan de slag </h2>
<ol>
<li>Kies een testcase of testsuite om te testen.</li>
<li>Selecteer een geplande tester. </li>
<li>Klik op de knop 'Opslaan' om toe te wijzen.</li>
<li>Open de uitvoeren pagina om te controleren. U kunt filter voor de gebruikers opzetten.</li>
</ol>

<h2>Om alle testers van testcases te verwijderen:</h2>
<ol>
<li>Klik op de root-node in de boomstructuur (het testproject).</li>
<li>Als er toegewezen testcases zijn, zal je een knop om alle toegewezen testcases te verwijderen. Als u erop klikt en bevestigd,
alle testers van alle testcases verwijderd worden.</li>
</ol>";

// ------------------------------------------------------------------------------------------
$TLS_htmltext_title['planUpdateTC']= "Update testcases in het testplan";
$TLS_htmltext [ 'planUpdateTC'] = "<h2>Doel </h2>
<p>Op deze pagina kunt een testcase bijwerken naar een nieuwere (andere) versie als een test
specificatie is veranderd. Het gebeurt vaak dat sommige functies worden verduidelijkt tijdens de test.".
"Als de gebruiker een testspecificatie wijzigt, moeten veranderingen ook doorgegeven aan het testplan. Anders behoud het ".
"testplan de originele versie om zeker te zijn, dat de resultaten verwijzen naar de juiste tekst van een testcase. </P>

<h2>Aan de slag </h2>
<ol>
<li>Kies een testcase of testsuite te testen. </li>
<li>Kies een nieuwe versie in de combo-box voor een bepaalde testcase. </li>
<li>Klik op de knop 'testplan bijwerken' om de wijzigingen in te dienen. </li>
<li>Om te controleren: Open tests uitvoeren pagina om de tekst van de testcase(s) te bekijken.</li>
</ol>";


// ------------------------------------------------------------------------------------------
$TLS_htmltext_title['test_urgency']= "Geef testen een hoge of lage urgentie";
$TLS_htmltext['test_urgency'] = "<h2>Doel </h2>
<p>TestLink maakt het mogelijk om de urgentie van een testsuite testcases te beïnvloeden. 
Test prioriteit is afhankelijk van zowel belang van testcase en urgentie gedefinieerd in 
het testplan. De test leider moet een set van testcases selecteren die eerst moeten worden uitgevoerd. 
Dit helpt omdat eerst de belangrijkste uitgevoerd worden, 
ook onder tijdsdruk. </p>

<h2>Aan de slag </h2>
<ol>
<li>Kies een testsuite om de urgentie van een product / onderdeel in te stellen in de navigator
aan de linkerkant van het venster. </li>
<li>Kies een urgentie niveau (hoog, gemiddeld of laag). Gemiddeld is standaard. Je kunt
prioriteit verlagen voor de ongewijzigde delen van het product en verhogen voor componenten met
belangrijke wijzigingen. </li>
<li>Klik op de knop 'Opslaan' om de wijzigingen in te dienen. </li>
</ol>
<p><i>Bijvoorbeeld een testcase met een groot belang in een testsuite met een lage urgentie ".
"zal Medium prioriteit krijgen.</i>";
// ------------------------------------------------------------------------------------------

?>
