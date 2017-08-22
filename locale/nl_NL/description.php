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
 * @version    	CVS: $Id: description.php,v 1.2 2010/06/24 17:25:53 asimon83 Exp $
 * @link 		http://www.teamst.org/index.php
 *
 * @internal Revisions:
 * 20100409 - eloff - BUGID 3050 - Update execution help text
 **/


// printFilter.html
$TLS_hlp_generateDocOptions = "<h2>Opties voor een gegenereerd document</h2>

<p>In deze tabel kan de gebruiker testcases filteren voordat ze worden bekeken. 
Geselecteerde (aangevinkte) gegevens zullen worden getoond. Om de voorgestelde gegevens te wijzigen,
, vink aan of uit, klikt u op Filter, en selecteer het gewenste data
niveau van de boom.</p>

<p><b>Document Hoofding:</b> Gebruikers kunnen informatie in de hoofding filteren. 
Document hoofding informatie omvat: inleiding, bereik, referenties, 
testmethodologie en test beperkingen.</p>

<p><b>Testcase Body:</b> Gebruikers kunnen testcase body informatie filteren. Testcase Body informatie
bestaat uit: samenvatting, stappen, verwachte resultaten en sleutelwoorden </p>

<p><b>Testcase samenvatting:</b> Gebruikers kunnen testcase samenvattingen filteren van de testcase titel,
ze kunnen echter geen informatie uit de testcase samenvatting testcase
body. Testcase samenvatting is slechts gedeeltelijk gescheiden van testcase
body ter ondersteuning van het bekijken van de titels met een korte samenvatting en het ontbreken van
stappen, verwachte resultaten en trefwoorden. Als een gebruiker besluit om een testcase body te bekijken
, zal de tescase samenvatting altijd worden opgenomen. </p>

<p><b>Inhoudsopgave:</b> TestLink voegt een overzicht toe van alle geselecteerde titels met interne hyperlinks</p>

<p><b>Uitvoerformaat:</b> Er zijn twee mogelijkheden: HTML en MS Word. Browser roept MS Word component aan 
in het tweede geval.</p>";

// testPlan.html
$TLS_hlp_testPlan = "<h2>Testplan</h2>

<h3>Algemeen</h3>
<p>Een testplan is een systematische aanpak voor het testen van een systeem zoals software. U kunt het testen van de activiteit organiseren 
met bepaalde builds van het product in de tijd en resultaten traceren.</p>

<h3>Tests Uitvoeren</h3>
<p>Dit gedeelte is waar de gebruikers testcases kunnen uitvoeren (testresultaten schrijven) en 
een testcase suite van het testplan afdrukken. Deze sectie is waar gebruikers de resultaten kunnen bijhouden 
van het uitvoeren van een testcase. </p> 

<h2>Testplan beheer</h2>
<p>Deze sectie, die alleen toegankelijk is voor leiders, stelt gebruikers in staat om testplannen te beheren. 
Administratie van testplannen omvat het maken/bewerken/verwijderen van de plannen, 
toevoegen/bewerken/verwijderen/updaten van testcases in de plannen, builds creëren evenals bepalen wie welke 
plannen kan zien.<br />
Gebruikers met leider permissies kunnen ook de prioriteit/risico en de eigendom van 
testcase suites (categorieën) en test mijlpalen maken.</p> 

<p>Opmerking: Het is mogelijk dat gebruikers geen dropdown met testplannen kunnen zien. 
In deze situatie zullen alle links (behalve deze geactiveerd door een leider) losgekoppeld zijn. Als u zich 
in deze situatie bent moet u contact opnemen met een leider of administrator om u de juiste rechten voor het testplan toe te kennen 
or een testplan voor u aan te maken.</p>";

// custom_fields.html
$TLS_hlp_customFields = "<h2>Gebruikersvelden</h2>
<p>Hier volgen enkele feiten over de implementatie van de gebruikersvelden: </p>
<ul>
<li>Gebruikersvelden worden gedefinieerd het hele systeem.</li>
<li>Gebruikersvelden zijn gekoppeld aan een type element (Testsuite, Testcase)</li>
<li>Gebruikersvelden kunnen worden gekoppeld aan meerdere testprojecten.</li>
<li>De volgorde van de weergave van gebruikersvelden kunnen verschillen per testproject.</li>
<li>Gebruikersvelden kunnen inactief worden gezet voor een specifiek testproject.</li>
<li>Het aantal gebruikersvelden is onbeperkt.</li>
<ul>

<p>De definitie van een gebruikersveld bevat de volgende logische
attributen:</p>
<ul>
<li>Gebruikersveld naam</li>
<li>Bijschrift naam van de variabele (bijvoorbeeld: Dit is de waarde die
geleverd wordt aan lang_get () API, of zo weergegeven wordt als deze niet wordt gevonden in een taalbestand).</li>
<li>Type gebruikersveld (string, numeric, float, enum, e-mail)</li>
<li>Het bepalen mogelijke waarden (bijvoorbeeld: ROOD|GEEL|BLAUW), die van toepassing zijn in een lijst 
en combo types.<br/>
<i>Gebruik het pijp ('|') karakter
om mogelijke waarden voor een opsomming te scheiden. Een mogelijke waarde
kan een lege tekenreeks zijn. </i>
</li>
<li>Standaard waarde: NOG NIET GEIMPLEMENTEERD</li>
<li>Minimum/maximum lengte voor de gebruikersveld waarde (gebruik 0 om uit te schakelen). (NOG NIET GEIMPLEMENTEERD)</li>
<li>Reguliere expressie te gebruiken voor het valideren van input van de gebruiker
(<a href=\"http://au.php.net/manual/en/function.ereg.php\">ereg()</a>
syntaxis). <b>(NOG NIET GEIMPLEMENTEERD)</b></li>
<li>Alle gebruikersvelden worden momenteel opgeslagen in een veld van het type VARCHAR (255) in de database.</li>
<li>Toon in testspecificatie.</li>
<li>Aanpassen bij testspecificatie. De gebruiker kan tijdens het testcase specificatie ontwerp de waarde veranderen</li>
<li>Toon bij testuitvoering. </li>
<li>Aanpassen bij testuitvoering. De gebruiker kan tijdens testcase uitvoering de waarde veranderen</li>
<li>Toon op testplan ontwerp.</li>
<li>Aanpassen bij testplan ontwerp. De gebruiker kan de waarde veranderen tijdens het testplan ontwerp (testgevallen aan testplan toevoegen)</li>
<li>Beschikbaar voor. De gebruiker kan kiezen om wat voor soort punt het veld gaat.</li>
<ul>
";

// execMain.html
$TLS_hlp_executeMain = "<h2>Testcases uitvoeren</h2>
<p>Hiermee kunnen gebruikers testcases 'uitvoeren'. Uitvoeren zelf is louter
het toewijzen van resultaat aan een testcase (OK, gefaald, geblokkeerd) in een geselecteerde build. </p>
<p>De toegang tot een bug tracking systeem kan worden geconfigureerd. De gebruiker kan dan direct nieuwe bugs toevoegen
en door bestaande bladeren. Zie installatiehandleiding voor meer informatie.</p> ";

//bug_add.html
$TLS_hlp_btsIntegration = "<h2>Bugs toevoegen aan een testcase </h2>
<p><i>(alleen als dit geconfigureerd is)</i>
TestLink heeft een zeer eenvoudige integratie met Bug Tracking Systems (BTS),
zonder te een verzoek om een bug aan te maken te versturen aan BTS, noch het terugkrijgen bug id.
De integratie wordt gedaan met behulp van links naar pagina's op BTS, die de volgende functies oproepen:
<ul>
<li>Nieuwe bug toevoegen.</li>
<li>Toon bestaande bug info.</li>
<ul>
</p>  

<h3> Proces om een bug toe te voegen </h3>
</p>
   <ul>
   <li>Stap 1: Gebruik de link naar BTS openen naar een nieuwe bug in te voegen.</li>
   <li>Stap 2: Noteer de BUGID toegewezen door BTS</li>
   <li>Stap 3: Schrijf BUGID in het invoerveld</li>
   <li>Stap 4: Gebruik bug  toevoegen knop</li>
   <ul>  

Na het sluiten van de bug toevoegen pagina vindt u de relevante bug gegevens op de tests uitvoeren pagina te zien.
</p> ";

// execFilter.html
$TLS_hlp_executeFilter = "<h2>Instellingen</h2>

<p>In instellingen kunt u het testplan, build en platform (indien aanwezig) om uit te voeren selecteren
</p>

<h3>Testplan</h3>
<p>U kunt het gewenste testplan kiezen. Volgens de gekozen testplan zullen de geschikte
builds worden getoond. Na het kiezen van een testplan zullen filters gereset worden.</p>

<h3>Platform</h3>
<p>Als de functie platformen wordt gebruikt, moet u het juiste platform te kiezen om een test uit te voeren.</p>

<h3>Uit te voeren build</h3>
<p>U kunt de build kiezen waarvoore u de testcases wukt uitvoeren.</p>

<h2>Filters</h2>
<p>Filters bieden de mogelijkheid de set van de getoonde testcases verder te beinvloeden
voor ze uit te voeren. U kunt de set van getoonde testcases verkleinen door filters op te geven
en op de \"Apply\" knop te klikken.</p>

<p>Met geavanceerde filters kunt u een reeks waarden opgeven voor de filters door
CTRL-klik te gebruiken in de multi-select listbox.</p>


<h3>Trefwoord filter</h3>
<p>U kunt testcases filteren op de trefwoorden die eraan zijn toegewezen. Je kan meerdere trefwoorden kiezen " .
"met CTRL-klik. Als u meer dan één trefwoord koos kun je ".
"beslissen of alleen testcases worden getoond waaraan alle gekozen trefwoorden zijn toegewezen".
"(Radiobutton \"en\") of ten minste één van de gekozen trefwoorden (radioknop \"Of\"). </p>

<h3>Prioriteitsfilter</h3>
<p>U kunt testcases filteren op test prioriteit. De test prioriteit is \"testcase belang\" ".
"gecombineerd met \"test dringendheid\" in het huidige testplan.</p> 

<h3>Gebruiker filter</h3>
<p>U kunt testcases filteren die niet zijn toegewezen (\"Niemand\") of toegewezen aan \"Iemand\". ".
"Je kunt ook testcases filteren die aan een specifieke tester zijn toegewezen. Als je een specifieke tester kiest ".
"heb je ook de mogelijkheid om testcases die niet toegewezen zijn erbij te laten zien".
"(geavanceerde filters zijn beschikbaar). </p>

<h3>Resultaat filter</h3>
<p>U kunt testcases filteren op resultaat (geavanceerde filters zijn beschikbaar). U kunt filteren op ".
"Resultaat \"op gekozen build \", \"op de nieuwste uitvoering\", \"op ALLE builds\", ".
"\"op om het even welke build\" en \"op specifieke build\". Als \"specifieke build\" gekozen is dan kan u".
"de build opgeven.</p>";


// newest_tcversions.html
$TLS_hlp_planTcModified = "<h2>De nieuwste versies van gekoppelde testcases</h2>
<p>De hele set testcases gekoppeld aan testplan wordt geanalyseerd, en een lijst van testcases
waarvan de nieuwste versie wordt weergegeven (vergeleken met de huidige set van het testplan).
</p>";


// requirementsCoverage.html
$TLS_hlp_requirementsCoverage = "<h3>Vereisten dekking</h3>
<br />
<p>Deze functie maakt het mogelijk om de ​​dekking in kaart te brengen van de gebruiker- of systeemvereisten door
testcases Openen via link \"Vereisten specificatie\" in het hoofdscherm.</p>

<h3>Vereisten specificatie</h3>
<p>Vereisten worden gegroepeerd door een 'Vereisten specificatie' document dat betrekking heeft op het 
testproject. <br /> TestLink ondersteunt geen versiebeheer voor vereisten specificaties  
of vereisten. Dus moet de versie van document worden toegevoegd na 
een specificatie <b>Titel</b>.
Een gebruiker kan eenvoudige beschrijvingen of opmerkingen toevoegen aan het <b>Bereik</b> veld.</p> 

<p><b><a name='total_count'>Overschreven telling van vereisten</a></b> dient voor 
evaluatie van vereisten dekking in het geval dat niet aan alle vereisten toegevoegd (of geïmporteerd) zijn. 
De waarde <b> 0 </b> betekent dat de huidige telling van eisen wordt gebruikt voor de statistieken.</p> 
<p><i>Bv SRS omvat 200 vereisten, maar slechts 50 worden toegevoegd in TestLink. Test 
dekking is 25% (indien alle toegevoegde vereisten worden getest).</i></p>

<h3><a name=\"req\">Vereisten</a></h3>
<p>Klik op de titel van een bestaande Vereisten specificatie. U kunt vereisten maken, bewerken, verwijderen
of importeren voor het document. Elke vereiste heeft een titel, bereik en status.
Status moet \"normaal\" of \"Niet toetsbaar\" zijn. Niet toetsbare vereisten worden niet meegeteld
in statistieken. Deze parameter moet worden gebruikt voor niet geïmplementeerde functies en 
verkeerd ontworpen vereisten.</p> 

<p>U kunt nieuwe testcases voor de vereisten aanmaken door het gebruik van multi actie met gecontroleerde 
vereisten in het specificaties scherm. Deze testcases worden gemaakt in testsuite
met de naam opgegeven in configuratie <i>(standaard is: &#36;tlCfg->req_cfg->default_testsuite_name = 
\"Test suite created by Requirement - Auto\";) </i>. Titel en bereik worden gekopieerd naar deze testcases. </p>
";

$TLS_hlp_req_coverage_table = "<h3>Dekking:</h3>
Een waarde van bijvoorbeeld \"40% (8/20)\" betekent dat 20 testcases moeten worden gemaakt om deze vereiste 
volledig testen. 8 ervan al zijn gemaakt en gekoppeld aan deze vereiste, die 
zo een dekking van 40 procent uitmaken.
";


// planAddTC_m1.tpl
$TLS_hlp_planAddTC = "<h2> Metbetrekking tot 'Gebruikersvelden opslaan'</h2>
Als u gebruikersvelden met <br/>
'Toon bij testplan ontwerp'<br/>
en 'Beschikbaar bij testplan ontwerp' <br/>
hebt gedefinieerd en toegewezen aan een testproject, <br /> 
zult u deze op deze pagina alleen zien voor testcases gekoppeld aan het testplan.
";

// xxx.html
// $TLS_hlp_xxx = "";

// ----- END ------------------------------------------------------------------
?>
