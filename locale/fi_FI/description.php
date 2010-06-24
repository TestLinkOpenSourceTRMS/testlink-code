<?php
/** 
 * ♔ TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later. 
 * 
 * Localization: Finnish (fi_FI) texts 
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
 * @author 		Kirsi Mäkinen, Jan-Erik Finlander, Juho Kauppi
 * @author 		Heikki Alonen, Jari Ahonen, Otto Moilanen
 * @copyright 	2003-2009, TestLink community 
 * @version    	CVS: $Id: description.php,v 1.3 2010/06/24 17:25:55 asimon83 Exp $
 * @link 		http://www.teamst.org/index.php
 *
 **/

	
// printFilter.html
$TLS_hlp_generateDocOptions = "<h2>Vaihtoehtoja asiakirjan luomiseen</h2>

<p>Tällä taulukolla käyttäjät voivat suodattattaa testitapauksia, ennen niiden näyttämistä. Jos valitut (tarkastettu) tiedot tulevat näkyviin. Muuttaaksesi tietojen esitystä, valitse tai poista, valitse Suodatus ja valitse haluamasi tiedot hakemistosta.</p>

<p><b>Asiakirjan otsikko:</b> Käyttäjät voivat suodattaa asiakirjan otsikko tietoja. Asiakirjan otsikko tietoihin kuuluvat: Esittely, Laajuus, Viitteet, Testausmenetelmät ja Testausrajat.</p>

<p><b>Testitapauksen runko</b> Käyttäjät voivat suodattaa Testitapauksien rungon tietoja. Testitapauksien runko tietoihin kuuluvat: Yhteenveto, Vaiheet, Odotetut tulokset ja Avainsanat.</p>

<p><b>Testitapauksien yhteenveto:</b> Käyttäjät voivat suodattaa Testitapauksien yhteenveto tietoja, Testitapauksien otsikko vähintään, ne eivät voi suodattaa pois Testitapauksien yhteenveto tietoja Testitapauksien rungosta. Testitapauksien yhteenveto on vain osittain erotettu Testitapauksien rungosta, jotta voitaisiin näyttää Otsikot lyhyen yhteenvedon kanssa ja puutuvat vaiheet, odotetut tulokset ja avainsanat. Jos käyttäjä päättää tarkastella Testitapauksien runkoja, Testitapauksien yhteenvedon on aina oltava mukana.</p>

<p><b>Sisällysluettelo:</b> TestLink lisätään luettelo kaikista Nimekkeitä sisäiseen hypertekstilinkkejä jos tarkastetaan..</p>

<p><b>Esitysmuoto:</b> Mahdollisuuksia on kaksi: HTML ja MS Word. Selain vaatii MS Word-osan toisessa tapauksessa.</p>";

// testPlan.html
$TLS_hlp_testPlan = "<h2>Test Plan</h2>

<h3>Yleistä</h3>
<p>A-testin suunnitelmassa on systemaattinen lähestymistapa testaus järjestelmä, kuten tietokoneohjelmat. Voit järjestää testaus toimintaa erityisesti rakentaa tuotteen aikaa ja jäljittää tuloksia.</p>

<h3>Test Execution</h3>
<p>Tähän käyttäjät voivat suorittaa testin tapauksissa (kirjoita testitulokset) ja tulostaa Test tapauksessa sarja Testaus Plan. Tämä jakso, jossa käyttäjät voivat seurata tuloksia testejä tapauksessa suorituksen.</p>

<h2>Test Plan Management</h2>
<p>Tämä osio, joka on vain johtaa saatavilla avulla käyttäjät voivat hallita testi suunnitelmia. Hallinnoivan testi suunnitelmiin kuuluu luodaan tai muokataan / poistetaan suunnitelmien lisääminen / muokkaaminen / poistaminen / tai päivittäminen testi tapauksissa suunnitelmiin, luomalla rakentuu sekä määritellään, kuka voi nähdä, mitkä suunnitelman.<br />
Käyttäjät, joilla on johtaa oikeudet voivat myös asettaa ensisijaiset / riski-ja omistajanvaihdoksiin Test tapauksessa suites (luokat) ja luoda testaus välietapit.</p>

<p>Huom: On mahdollista, että käyttäjät eivät voi nähdä Avattavan sisältävät kaikki Test suunnitelmia. Tässä tilanteessa kaikki linkit (paitsi johtaa käytössä ones) on purettu. Jos olet tässä tilanteessa sinun tulee ottaa yhteyttä lyijyä tai admin myöntää sinulle oikea hanke oikeuksia tai luo Test Plan sinulle.</p>";

// custom_fields.html
$TLS_hlp_customFields = "<h2>Custom Fields</h2>
<p>Seuraavassa on joitakin faktoja täytäntöönpanoa mukautetun aloilla:</p>
<ul>
<li>Mukautetut kentät on määritelty järjestelmä leveä.</li>
<li>Mukautetut kentät ovat sidoksissa eräänlainen tekijä (Test Suite Test Asia)</li>
<li>Mukautettu aloilla voidaan liittää useita Test Projektit.</li>
<li>Sarja näyttämään mukautetut kentät voivat olla eri per Test Project.</li>
<li>Mukautettu aloilla voidaan kytkeä inaktiivinen varten erityisiä Test Project.</li>
<li>Määrä mukautettuja kenttiä ei ole rajoitettu.</li>
</ul>

<p>Määritelmä mukautetun alalla kuuluvat seuraavat looginen attributes:</p>
<ul>
<li>Muokattava kentän nimi</li>
<li>Caption muuttujan nimi (esimerkiksi: Tämä on arvo, joka on toimitettu lang saada () API tai näyttöön kuin on, jos ei löydy kieli-tiedosto).</li>
<li>Oma kenttä tyyppi (merkkijono, numeerinen, float, enum, sähköposti)</li>
<li>Numerointi mahdollisia arvoja (esim. RED | Keltainen | Sininen), sovelletaan luettelossa multiselection luettelo ja yhdistelmäruudussa tyyppiä.<br />
<i>Käytä putki ('|') merkin erillinen mahdollisia arvoja luettelo. Yksi mahdollisista arvoista voi olla tyhjä merkkijono.</i>
</li>
<li>Oletusarvo: EI TOISTAISEKSI</li>
<li>Minimum / enimmäispituus mukautetun kentän arvo (käytä 0 pois). (Ei toteutettu vielä)</li>
<li>Säännöllinen lauseke käyttää validoida syöttötapa (käytä a href = \ http://au.php.net/manual/en/function.ereg.php \ ereg ()</a>
syntax). <b>(Ei toteutettu vielä)</b></li>
<li>Kaikki mukautetut kentät ovat tällä hetkellä tallennetaan kentän tyypin VARCHAR (255) vuonna tietokantaan.</li>
<li>Näyttö testieritelmä..</li>
<li>Ota käyttöön testieritelmä. Käyttäjä voi muuttaa arvoa aikana Test Asia Eritelmä Design</li>
<li>Näyttö testin suorituksen.</li>
<li>Ota käyttöön testin suorituksen. Käyttäjä voi muuttaa arvoa aikana Test Asia täytäntöönpano</li>
<li>Näyttö testi suunnitelman suunnitteluun.</li>
<li>Ota Testisivulla suunnitelman suunnitteluun. Käyttäjä voi muuttaa arvoa aikana Test Plan suunnittelu (lisää testi tapauksissa testi suunnitelma)</li>
<li>Käytettävissä. Käyttäjä päättää, millaisia erä alalla belows.</li>
</ul>
";

// execMain.html
$TLS_hlp_executeMain = "<h2>Executing Test Cases</h2>
<p>Avulla käyttäjät voivat 'toteuttaa' testi tapauksissa. Suorittaminen itse on vain siirtää testin tapauksessa tulos (siirrä, epäonnistuvat, estetty) vastaan valitun rakentaa.</p>
<p>Mahdollisuus käyttää vianseurantajärjestelmä voi kokoonpanosta. Käyttäjä voi suoraan lisätä uuden vikoja ja selata exesting kuin silloin.</p>";

// bug_add.html
$TLS_hlp_btsIntegration = "<h2>Lisää Bugs Test asia</h2>
<p><i>(vain, jos se on määritetty)</i>
TestLink on hyvin yksinkertainen integrointi Bug Tracking System (BTS), ei voi joko lähettää virheraportin creationg pyynnön BTS, ei saada takaisin bug id. Integroituminen tapahtuu linkkejä sivut BTS, että puhelut seuraavat ominaisuudet:
<ul>
<li>Lisää uusi bug.</li>
<li>Näyttö olematon bug info.</li>
</ul>
</p>

<h3>Prosessin lisätä bug</h3>
<p>
<ul>
<li>Vaihe 1: käytä linkkiä auki BTS lisätä uuden vian. </li>
<li>Vaihe 2: Kirjoita alas BUGID, jonka BTS.</li>
<li>Vaihe 3: Kirjoita BUGID annetun kirjoituskentästä.</li>
<li>Vaihe 4: Käytä lisätä bug-painiketta.</li>
</ul>

Kun sulkemalla lisätä bug sivulla, näet asiaa bug koskevat tiedot suorittaa sivulle.
</p>";

// execFilter.html
$TLS_hlp_executeFilter = "<h2>Setup Suodatetaan ja Rakenna Testiparametrien täytäntöönpano</h2>

<p>Vasemmanpuoleisessa ruudussa koostuu alkaen navigaattorin avulla testi tapauksissa osoitettu nykyinen". 
"Test suunnitella ja taulukko, asetukset ja suodatetaan. Nämä suodattimet avulla käyttäjä ". 
"tarkentaa tarjotaan joukko testi tapauksissa, ennen kuin ne on suoritettu." . 
"Setup suodattimesi paina \"Käytä\" painiketta ja valitse sopiva Testitapaus" . 
" puu-valikosta.</p>

<h3>Build</h3>
<p>Käyttäjien tulee valita rakentaa, jotka liittyvät testin tuloksen. ".
" Rakentaa ovat perusasetuksen komponentin nykyinen Test Plan. Jokainen testi " .
"voidaan käyttää useamman kerran per rakentaa. Kuitenkin viime tuloksia on laskea vain.
<br />Rakentaa voidaan luoda johtaa käyttämällä Luo uusi Rakenna sivulla.</p>

<h3>Test Case ID filter</h3>
<p>Käyttäjät voivat testata suodattimet tapauksissa yksilöllinen tunniste. Tämä tunnus on luotu automaattisesti luoda aikaa. Tyhjä laatikko tarkoittaa, että suodatin ei sovelleta.</p>

<h3>Priority filter</h3>
<p>Käyttäjät voivat testata suodattimet tapauksissa testi etusijalla. Jokainen testi tapauksessa tärkeää on yhdistää" .
 "kanssa testi kiireellisissä nykyisen Test suunnitelma. Esimerkiksi 'KORKEAT' ensisijaisia testin tapauksessa" . 
"näkyy, jos merkitystä tai kiireellisyys on KORKEA ja toisen attribuutin on vähintään 'KESKITASO' tasolla.</p>

<h2>Result filter</h2>
<p>Käyttäjät voivat testata suodattimet tapauksissa tuloksia. Tulokset ovat mitä tapahtui, että testi tapauksessa aikana erityisesti rakentaa. Testitapauksia voi kulkea, epäonnistuvat, on estetty, tai ei saa olla päällä. " .
"Tämä suodin on oletusarvona pois päältä.</p>

<h3>User filter</h3>
<p>Käyttäjät voivat testata suodattimet tapauksissa niiden siirronsaajalle. Tarkastus-box mahdollistaa sisällyttää myös " .
"\"Vapaana\" testit osaksi johtanut asettaa lisäksi.</p>";
/*
<h2>Useimmat Nykyinen tulos</h2>
<p>Oletusarvon tai jos 'viimeisintä' valintaruutu ei ole valittu, puussa on LAJITTELE luontinumero että valitaan avattavasta laatikko. Tässä tilassa puun näyttää testin tapauksissa tila.
<br /> Esimerkki: Käyttäjä valitsee rakentaa 2 avattavasta kentästä ja ei tarkistaaksesi 'viimeisintä' valintaruutu. Kaikki testin tapauksissa näkyy niiden tilan rakentaa 2. Joten, jos testi 1 hyväksyttiin rakentaa 2 on väritetty vihreäksi.
<br /> Jos käyttäjä decideds tarkistaaksesi 'viimeisintä' valintaruutu puussa on värillinen, että testi tapauksissa viimeisimmän tuloksen.
<br /> Esim: Käyttäjä valitsee rakentaa 2 avattavasta ruutuun ja tällä kertaa tarkistaa 'viimeisintä' valintaruutu. Kaikki testin tapauksissa näkyy useimpien nykyinen tila. Joten, jos testi 1 hyväksyttiin rakentaa 3, vaikka käyttäjä on myös valittu rakentaa 2, se on väritetty vihreäksi.</p>"; */

// newest_tcversions.html
$TLS_hlp_planTcModified = "<h2>Uusimmat versiot liittyvät Test Cases</h2>
<p>Koko joukko Test asiat liittyvät Test Plan on analysoitu, ja luettelo Test asiat, joilla on uusin versio näkyy (verrattuna nykyiseen, että Test Plan).
</p>";

// requirementsCoverage.html
$TLS_hlp_requirementsCoverage = "<h3>Vaatimukset Kattavuus</h3>
<br />
<p>Tämä ominaisuus mahdollistaa kartta kattavuuskerrointa käyttäjän tai järjestelmän vaatimuksia testi tapauksissa. Navigoi kautta linkki \"Requirement Specification\" vuonna päänäyttö.</p>

<h3>Requirements Specification</h3>
<p>Vaatimukset on ryhmitelty 'eritelmä' asiakirja, joka liittyy Test Project. br / TestLink ei tue versiot sekä eritelmä ja vaatimukset itse. Joten versio asiakirjan olisi lisättävä jälkeen Eritelmä<b>Title</b>.
Yksi käyttäjä voi lisätä yksinkertainen kuvaus tai muistiinpanoja <b>Vaiheet</b> kenttä.</p>

<p><b><a name='total_count'>Päällekirjoittamalla lasken reqs</a></b> palvelee arvioinnin Req. kattavuus tapauksessa, etteivät kaikki vaatimukset on lisätty (tuonti) tuumaa arvo<b>0</b> tarkoittaa, että nykyinen määrä vaatimuksia käytetään lukuja.</p>
<p><i>E.g. SRS includes 200 requirements but only 50 are added in TestLink. Test
coverage is 25% (if all these added requirements will be tested).</i></p>

<h3><a name=\"req\">Vaatimukset</a></h3>
<p>Klikkaa otsikkoa, joka luotiin eritelmä. Voit luoda, muokata, poistaa tai maahantuonnin vaatimukset asiakirjan. Jokainen vaatimus on nimi, laajuus ja tila. Tila olisi \ Normaali \ tai \ Ei testable \. Ei testable vaatimuksia ei lasketa ja käyttötiedot Tämä parametri olisi käyttää sekä unimplemented ominaisuuksia ja väärin suunnitellut vaatimukset.</p>

<p>Voit luoda uuden testin tapauksissa vaatimukset käyttämällä useita toimia tarkastetaan vaatimukset eritelmän näytöllä. Nämä Test Cases luodaan osaksi testausohjelmisto nimi määritelty kokoonpano i (oletus on: $ tlCfg-req cfg-default testsuite nimi = \ testausohjelmisto luoma Vaatimus - Auto \;) / i. Otsikko ja Soveltamisala kopioidaan nämä testitapauksia</p>
";

// planAddTC_m1.tpl
$TLS_hlp_planAddTC = "<h2>Mitä 'Tallenna Custom Fields'</h2>
Jos olet määrittänyt, ja niihin liitetään Test Project,<br />
Custom Fields kanssa:<br />
'Näyttö testi suunnitelman suunnittelu = true' ja<br />
'Ota Testisivulla suunnitelman suunnittelu = true'<br />
näette nämä tällä sivulla AINOASTAAN Test asiat liittyvät Test Plan.
";

// xxx.html
$TLS_hlp_xxx = "";

// ----- END ------------------------------------------------------------------
?>