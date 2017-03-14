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
 * 	$TLS_help[<key>] and $TLS_help_title[<key>]
 * or
 * 	$TLS_instruct[<key>] and $TLS_instruct_title[<key>]
 *
 * Revisions history is not stored for the file
 * 
 * 
 * @package 	TestLink
 * @author 		Kirsi Mäkinen, Jan-Erik Finlander, Juho Kauppi, Heikki Alonen, Jari Ahonen, Otto Moilanen
 * @copyright 	2003-2009, TestLink community 
 * @version    	CVS: $Id: texts.php,v 1.3 2010/06/24 17:25:55 asimon83 Exp $
 * @link 		http://www.teamst.org/index.php
 *
 **/


$TLS_htmltext_title['assignReqs'] = "Määritä Testitapauksen vaatimukset ";
$TLS_htmltext['assignReqs'] = "<h2>Tarkoitus:</h2>
<p>Käyttäjät voivat asettaa suhteita ja testata tapauksissa. Testi suunnittelija voi määrittää suhteet 0..n to 0..n. I.e. Yksi testi tapauksessa voitaisiin antaa, ei yhtään, yhden tai useamman vaatimuksen ja päinvastoin. Tällainen jäljitettävyys matriisi auttaa tutkimaan testin kattavuuden vaatimukset ja selvittää, mitkä onnistuneesti jättänyt aikana testaus. Tämä analysoida toimii varmistetaan, että kaikki määritellyt odotukset ovat täyttyneet.</p>

<h2>Aloita:</h2>
<ol>
<li>Valitse Test Asia on puu milloin vasemmalle. Yhdistelmäruutuun ruutuun luettelo Vaatimukset Tekniset näkyy yläosassa olevaa workarea. </li> 
<li>Valitse eritelmä Asiakirja jos useammat kerran määritelty.</li>
<li>Valitse eritelmä Asiakirja jos useammat kerran määritelty. TestLink automaattisesti reloads sivua.</li>
<li>Keski block workarea luetellaan kaikki vaatimukset (alkaen valittu Specification), jotka liittyvät testin tapauksessa. Pohja estää 'Saatavilla olevat vaatimukset' luetellaan kaikki vaatimukset, jotka eivät ole suhteessa nykyisen testin tapauksessa. Suunnittelija voi tavaramerkin vaatimuksia, jotka kuuluvat tämän testin, ja valitse sitten painiketta 'Valitse'. Nämä uudet sidottuja testi näkyvät keskellä estää 'sidotut Vaatimukset'.</li>
</ol>";

// ------------------------------------------------------------------------------------------

$TLS_htmltext_title['editTc'] = "Testauseritelmä";
$TLS_htmltext['editTc'] = "<h2>Tarkoitus:</h2>
<h2>Tarkoitus:</h2>
<p>The <i>Testauseritelmä</i> avulla käyttäjät voivat tarkastella ja muokata kaikki nykyiset " .
"<i>Test Suites</i> ja <i>Testi Cases</i>. Test Cases ovat versioidut ja kaikki " .
"ja aiemmat versiot ovat saatavilla ja niitä voidaan tarkastella ja hallinnoi täällä.</p>

<h2>Aloita:</h2>
<ol>
<li>Valitse Test projekti navigointisivun puu (root node). <i>Huomaa: " .
"Voit aina muuttaa aktivoida Test Project valitsemalla eri jokin " .
"alasvetovalikossa yläkulmasta oikeaan alakulmaan.</i></li>
<li>Luo uusi testausohjelmisto klikkaamalla <b>New Child testausohjelmisto</b>. testausohjelmisto voi " .
"saatettava rakenteen testi-asiakirjojen mukaan teidän yleissopimusten (toiminnallinen / ei-toiminnallinen" .
"testit, tuotteen osia tai toimintoja, muuttaa jne.). Kuvaus" .
"testiohjelmisto voisi pitää soveltamisalaan mukana testi tapauksissa oletuskokoonpanoon," .
"linkkejä asiaan liittyvät asiakirjat, rajoituksia ja muita hyödyllisiä tietoja. Yleensä" .
"kaikki merkinnät, jotka ovat yhteisiä lapsen Test Cases. Test Suites seurata" .
"me &quot;kansio&quot; metafora, näin käyttäjät voivat siirtää ja kopioida Test Suites kanssa" .
"Testaus-hankkeeseen. Lisäksi niitä voidaan tuoda maahan tai viedään maasta (mukaan lukien suljettuun testitapauksia).</li>
<li>Test sviitit ovat skaalattavat kansioihin. Käyttäjä voi siirtää tai kopioida Test Suites kanssa " .
"Testaus-hankkeeseen. Test sviittiä voidaan tuoda maahan tai viedä maasta (myös testitapauksia).
<li>Valitse uudesta Test Suite-navigointi puu ja luoda" .
"uusi Test Asia klikkaamalla <b>Luo Test Case</b>. Test Case täsmennetään " .
"erityisesti testausta skenaario ja odotetut tulokset sekä mukautetut kentät määritelty " .
"että Test Project (katso käyttöohjetta lisätietoja). On myös mahdollista " .
"siirtää <b>avainsanat</b> parantaa jäljitettävyyttä.</li>
<li>Navigoi kautta puunäkymässä vasemmalla puolella ja muokata tietoja. Testitapauksia tallentaa oman historian.</li>
<li>Anna teidän luonut testauseritelmä on <span class=\"help\" onclick=
\"javascript:open_help_window('glosary','$locale');\">Test Plan kun testitapauksia ovat valmiita.</li>
</ol>

<p>TestLinkillä voit järjestellä testi tapauksissa osaksi testi sviittiä." .
"Test sviittiä voidaan nested muiden koe-sviittiä, joiden avulla voit luoda hierarkioita testityypin sviittiä. Voit tulostaa nämä tiedot yhdessä testin tapauksissa.</p>";

$TLS_htmltext_title['searchTc'] = "Test Case hakusivu";
$TLS_htmltext['searchTc'] = "<h2>Purpose:</h2>

<p>Navigation according to keywords and/or searched strings. The search is not
case sensitive. Result include just test cases from actual Test Project.</p>

<h2>Jos haluat etsiä:</h2>

<ol>
<li>Kirjoita hakea merkkijonoa sopivaan ruutuun. Tyhjä käyttämättömät kentät muodossa.</li>
<li>Valitse vaaditaan avainsanan tai vasemmalle arvo 'Ei sovelleta'.</li>
<li>Napsauta Hae-painiketta.</li>
<li>Kaikki täyttyvät testi tapaukset ovat osoittaneet. Voit muokata testi tapauksissa kautta 'Otsikko'-linkkiä.</li>
</ol>";//

// ------------------------------------------------------------------------------------------

$TLS_htmltext_title['printTestSpec'] = "Tulosta testauseritelmä"; //printTC.html
$TLS_htmltext['printTestSpec'] = "<h2>Tarkoitus:</h2>
<p>Täältä voit tulostaa yhden testin tapauksessa kaikki testin tapauksissa testi sviitti tai kaikki testin tapauksissa testi hanketta tai suunnitelmaa.</p>
<h2>Aloita:</h2>
<ol>
<li>
<p>Valitse osat testisivun tapauksissa haluat näyttää, ja sitten testin tapauksessa testausohjelmisto tai kokeiluhanke. A Tulostettava sivu tulee näyttöön.</p>
</li>
<li><p>Käytä \"Näytä kuten\" drop-ruutuun navigointisivun ruudussa määritellä, haluatko tiedot näytetään HTML, OpenOffice Writer tai Microsoft Word-asiakirjaan.
See <span class=\"help\" onclick=\"javascript:open_help_window('printFilter',
'{$locale}');\">apua lisätietoja.</p>
</li>
<li><p>Käytä selaimen tulosta toiminnot itse tulostaa tiedot.<br />
<i>Huomaa: Varmista, että vain tulostaa oikean käden kanssa.</i></p>
</li>
</ol>";//

// ------------------------------------------------------------------------------------------

$TLS_htmltext_title['reqSpecMgmt'] = "Vaatimukset Eritelmä Design"; //printTC.html
$TLS_htmltext['reqSpecMgmt'] = "<p>Voit hallita vaatimustarve määrittely asiakirjoja.</p>

<h2>Requirements Specification</h2>

<p>Vaatimukset on ryhmitelty <b>Vaatimukset Eritelmä asiakirja</b>, joka liittyy Testi projekti. <br />TestLink ei tue (vielä) versiot sekä eritelmä ja vaatimukset itse. Joten asiakirjan versio olisi lisättävä jälkeen Eritelmä <b>Otsiko</b>.
Yksi käyttäjä voi lisätä yksinkertainen kuvaus tai huomautukset, <b>Laajuus</b> kenttä.</p>

<p><b><a name='total_count'>Päällekirjoittamalla lasken REQs</a></b> kattavuus tapauksessa, etteivät kaikki vaatimukset on lisätty TestLink. Arvo <b>0</b> tarkoittaa, että nykyinen määrä vaatimuksia käytetään lukuja.</p>
<p><i>Esim. SRS sisältää 200 vaatimukset, mutta vain 50 on lisätty TestLinkiin. Testauksen kattavuus on 25% (olettaen, että 50 lisännyt vaatimuksen todella testataan).</i></p>

<h2><a name='req'>Vaatimukset</a></h2>

<p>Klikkaa otsikkoa olemassa oleva eritelmä. Jos ei ole," . "klikkaa hankkeen node luoda sellainen. Voit luoda, muokata, poistaa tai maahantuonnin vaatimukset asiakirjan. Jokainen vaatimus on otsikko, laajuus ja tila. A-asema olisi joko 'Normaali' tai 'Epävakaa'. Epävakaa vaatimuksia ei lasketa ja käyttötiedot Tämä parametri olisi käyttää sekä unimplemented ominaisuuksia ja väärin suunnitellut vaatimukset.</p>

<p>Voit luoda uuden testin tapauksissa vaatimukset käyttämällä useita toimia tarkastetaan vaatimukset eritelmän näytöllä. Nämä Test Cases luodaan osaksi testausohjelmisto nimi määritelty kokoonpanoasetuksia <i>(default is: \$tlCfg->req_cfg->default_testsuite_name =
'Testausohjelmisto luotu Vaatimus - Auto';)</i>. Otsikko ja Soveltamisala kopioidaan nämä testitapauksia.</p>";//

// ------------------------------------------------------------------------------------------

$TLS_htmltext_title['keywordsAssign'] = "Keyword Assignment";
$TLS_htmltext['keywordsAssign'] = "<h2>Tarkoitus:</h2>
<p>Avainsanatyökalun Tehtävä sivu on paikka, jossa käyttäjät voivat erän antaa avainsanoja nykyisten testausohjelmisto tai Test Case</p>

<h2>To Assign Keywords:</h2>
<ol>
<li>Valitse testausohjelmisto tai Test tapauksessa puunäkymässä vasemmalla.</li>
<li>Alkuun kaikkein box näkyy oikealla puolella voit määrittää käytettävissä avainsanoja jokaisen testin tapauksessa.</li>
<li>Valinnat alle voit määrittää tapauksissa entistä rakeisessa tasolla.</li>
</ol>

<h2>Tärkeitä tietoja Hakusanat Luokitusprosessin vuonna Test Plans:</h2>
<p>Hakusanalla tehtäviä teet sen eritelmän vain vaikutus testi tapauksissa sinun Test suunnitelmia jos ja vain jos testin suunnitelma sisältää uusimman version Testaus tapauksessa. Muuten, jos testi suunnitelma sisältää vanhoja versioita testin tapauksessa tehtäviä teet nyt ei näy testissä suunnitelma.
</p>
<p>TestLink käyttää tätä lähestymistapaa niin, että vanhemmat versiot testi tapauksissa testi suunnitelmat eivät vaikuta Hakusanalla tehtäviä teet sen viimeisintä versiota testin tapauksessa. Jos haluat testata tapauksissa testi-suunnitelma on päivitetty, ensin vahvistaa ne ovat ajan tasalla käyttämällä 'Päivitä Modified Test Cases' toiminnallisuutta, ennen kuin teet Hakusanalla toimeksiannoissa.</p>";

$TLS_htmltext_title['executeTest'] = "Test Case Execution";
$TLS_htmltext['executeTest'] = "<h2>Tarkoitus:</h2> 

<p>Avulla käyttäjä voi suorittaa testitapauksia. Käyttäjä voi määrittää Testitulos Test Johdanto Build. Katso ohjeesta lisätietoja suodatin ja asetukset. (klikkaa kysymysmerkki-kuvake).</p>

<h2>Aloita:</h2>

<ol>
<li>Käyttäjä on määrittänyt Build Testisuunnittelmä varten.</li>
<li>Valitse Build olevasta avattavasta laatikko ja \ Käytä \ painiketta navigointi-osiossa.</li>
<li>Klikkaa testin tapauksessa puun valikosta.</li>
<li>Täytä testin tapauksessa johtaa ja muussa sovellettavassa muistiinpanoja tai vikoja.</li>
<li>Tallenna tulokset.</li>
</ol>
<p><i>Note: Huom: TestLink on configurated tehdä yhteistyötä teidän Bug tracker jos haluat luoda / jäljittää ongelmaraportti suoraan niiden GUI.</i></p>";


// ------------------------------------------------------------------------------------------

$TLS_htmltext_title['showMetrics'] = "Kuvaus testausselosteet ja Metrics";
$TLS_htmltext['showMetrics'] = "<p>Raportit liittyvät testisuunnittelmaan".
"(määritelty alkuun navigator). Tämä Test Plan voisi poiketa nykyisen testin suunnitelman toteutusta varten. Voit myös valita raportin muodossa:</p>
<ul>
<li><b>Normal</b> - raportti näkyy Web-sivuna</li>
<li><b>OpenOffice Writer</b> - raportti tuodaan OpenOffice Writeriin</li>
<li><b>OpenOffice Calc</b> - raportti tuodaan OpenOffice Calc:in</li>
<li><b>MS Excel</b> - raportti tuodaan Microsoft Excel:in</li>
<li><b>HTML Email</b> - Raportti on lähetetty sähköpostitse käyttäjän sähköpostiosoiteeseen</li>
<li><b>Charts</b> - raportti isisältää kaavioita (flash-teknologia) </li>
</ul>

<p>Tulosta-painike aktivoi painaminen raportin vain (ilman suunnistus).</p>
<p>On olemassa useita erillisiä raportteja valita, niiden merkitys ja asema on selitetty alla.</p>

<h3>Testisuunnittelma</h3>
<p>Asiakirja 'Testisuunnitelma' Plan on vaihtoehtoja määritellä sisältöä ja dokumentin rakenne.</p>

<h3>Testiraportti</h3>
<p>Asiakirja 'Testitulokset' on vaihtoehtoja määritellä sisällön ja asiakirjojen rakenne. Se sisältää testitapauksia yhdessä testitulokset.</p>

<h3>Yleinen testisuunnittelman mittaus</h3>
<p>Tämä sivu näyttää vain kaikkein nykyinen tila testi suunnitelman testausohjelmisto, omistaja, ja avainsanan mukaan. Kaikkein 'nykyinen tila' määräytyy viimeisimmän rakentaa testi tapauksissa teloitettiin päälle. Esimerkiksi, jos testi on suoritettu useita rakentuu vain viimeisin tulos otetaan huomioon.</p>

<p>'Viime Testitulos' on käsite, josta käytetään monissa, ja se määritellään seuraavasti:</p>
<ul>
<li>Järjestystä, joka perustuu lisätään testin suunnitelmassa määritellään, mitä kasvu uusimpaan. Tulokset viimeisimmän rakentaa, se on etusijalla verrattuna vanhempiin rakentuu. Jos esimerkiksi merkitä testataan kuin 'ei' on rakentaa 1 ja merkitse sitä 'siirrä' vuonna rakentaa 2, on uusin tulos on 'siirrä'.</li>
<li>Jos testi on suoritettu useita kertoja samalla rakentaa, viimeisimmän teloituksen edelle. Esimerkiksi, jos rakentaa 3 vapautuu tiimiäsi ja testauslaitteen 1 merkitsee sitä 'läppäisyt' at 2pm, ja testauslaitteen 2 merkitsee sitä 'ei' at 3pm - se näkyy 'fail'.</li>
<li>Test tapauksissa kuin 'ei toimi' vastaan rakentaa ei ole otettu huomioon. Jos esimerkiksi merkitä tapauksessa 'pass' on rakentaa 1, ja eivät suorita se rakentaa 2, on viime tuloksena voidaan pitää 'siirrä'.</li>
</ul>
<p>Seuraavissa taulukoissa näkyvät:</p>
<ul>
<li><b>Tulokset huipputason Test Suites</b>
Listaa tulokset kunkin huipputason sviitti. Yhteensä tapauksissa hyväksytään, epäonnistunut, estetty, ei näytetä, ja prosenttia valmistunut on lueteltu. A 'valmistunut testausta on yksi, joka on merkitty kulkea, epäonnistua tai estää. Tulokset huipputason suites sisältää kaikkien lasten sviittiä.</li>
<li><b>Tulokset avainsanan mukaan</b>
Luetellaan kaikki avainsanat, jotka on osoitettu tapauksissa nykyisen testin suunnitelmassa, ja tulokset liittyvät niihin.</li>
<li><b>Tulokset omistajan mukaan</b>
Luettelot kunkin omistaja, joka on testi tapauksissa niille nykyisen testin suunnitelma. Testitapauksia, jotka eivät ole sidottuja ovat kerätään mukaan 'vapaana-otsikon' alle.</li>
</ul>

<h3>The Overall Build Status</h3>
<p>Listaa täytäntöönpanon tuloksia jokaisesta rakentaa. Kunkin rakentaa koko testi tapauksissa yhteensä pässyt
%pässyt, yhteensä epäonnistuvat, %epäonnistuvat, estetty, % estetty, ei näytetä, %ei näytetä. Jos testi on suoritettu kahdesti saman rakentaa viimeisintä toteuttaminen otetaan huomioon.</p>

<h3>Kysely mittarit</h3>
<p>Tämä raportti koostuu kyselyn muodossa sivulla, ja kyselyn tulokset sivun, joka sisältää kysyi tietoja. Kyselyn muoto Page esittää kyselyä sivun 4 valvontaa. Kukin valvonta on asetettu oletuksena joka maksimoi määrä testisivun tapauksissa ja rakentaa kyselyn olisi tehtävä vastaan Muuttamatta valvonnan avulla käyttäjä voi suodattaa tuloksia ja tuottaa raportteja erityisiä omistaja, avainsanan, sarja ja rakentaa yhdistelmiä.</p>

<ul>
<li><b>avainsanat</b> 0->1 avainsanat voidaan valita. Oletusarvon - avainsanatason on valittu. Jos avainsana ei ole valittu, niin kaikki testin tapauksissa pidetään riippumatta Hakusanalla toimeksiannoissa. Avainsanat luokitellaan testin eritelmän tai Hakusanat Management sivua. Avainsanat annetaan testi tapauksissa span testausjärjestelmien suunnitelmia ja vertailukaasut kaikissa versioissa testin tapauksessa. Jos olet kiinnostunut tulokset tietyllä avainsanalla, voit muuttaa tätä valvontaa.</li>
<li><b>omistaja</b> 0->1 omistajat voivat valita. Oletuksena - ei omistaja on valittu. Jos omistaja ei ole valittu, niin kaikki testin tapauksissa pidetään riippumatta omistajan assignment. Tällä hetkellä ei ole toimintoa, haun 'määrittämätön' testi tapauksissa. Omistus on sijoitettu kautta 'Määritä Test Asia toteutusta' sivu ja tehdään per testi suunnitelman pohjalta. Jos olet kiinnostunut työn erityinen testauslaitteen voit muuttaa tätä valvontaa.</li>
<li><b>huipputason suite</b> 0->n huipputason sviittiä voidaan valita. Oletusarvon - kaikki sviitit on valittu. Vain sviittiä, jotka on valittu kyselyitä tulosta mittatietoja. Jos olet vain testataan tulosten erityinen sarja voit muuttaa tätä valvontaa.</li>
<li><b>Builds</b> 1->n rakentuu voidaan valita. Oletusarvon - kaikki rakentuu on valittu. Vain teloituksia tehtävä rakentaa valitset otetaan huomioon tuotannon lukuja. Esimerkiksi - jos haluat nähdä kuinka monta testi tapauksissa teloitettiin vuoden viimeisenä 3 rakentuu - voit muuttaa tätä valvontaa Avainsana, omistaja, ja huipputason suite valinnat sanella määrä testi tapauksissa testi-suunnitelmaa käytetään computate per sarja ja testiä kohti suunnitelma mittatietoja. Esimerkiksi, jos valitset omistaja = 'Greg', Keyword ='Priority 1', ja kaikki saatavilla olevat testi sviittiä - vain Priority 1 testi asioissa että Greg otetaan huomioon. The 'testi asiat' -kokonaissummat näet mietinnöstä on vaikuttanut näiden 3 valvontaa. Rakenna selections vaikuttaa, jos kyseessä on 'siirtää', 'ei', 'lukittu' tai 'ei toimi'. Tutustu viimeiset Testitulos säännöt kuin ne näkyvät yllä.</li>
</ul>
<p>Napsauta Lähetä-painiketta jatkaa kyselyä ja näyttämiseen sivulla.</p>

<p>Hakuraportti sivu näyttää: </p>
<ol>
<li>Kyselyn parametrit, joiden avulla luoda</li>
<li>kokonaismäärät koko testi suunnitelma</li>
<li>per sarja jakautuminen kokonaismäärät (summa / hyväksytty / hylätty / tukossa / ei näytetä) ja kaikki teloitukset suorittaa että sviitti. Jos testi on suoritettu useammin kuin kerran useita rakentuu - kaikki teloitukset näkyy, että kirjattiin vastaan valitun rakentuu Kuitenkin yhteenveto siitä suite sisältävät vain viimeiset 'Testitulos' varten valittua rakentuu.</li>
</ol>

<h3>Estetty, ei onnistunut, eikä Suorita Test Case Raportit</h3>
<p>Nämä raportit osoittavat, kaikki tällä hetkellä pysähdyksissä, ei ole tai ei suorittamaan tapauksissa. 'Viime testin tulos' logiikka (joka on kuvattu edellä General Test Plan Metrics) on jälleen palveluksessa, onko testi tapauksessa olisi harkittava estetty, ei ole, tai ei toimi Estetyt ja epäonnistunut testi tapausselostukset näyttää liittyviä vikoja, jos käyttäjä käyttää integroitua vianseurantajärjestelmä.</p>

<h3>Testi Raportti</h3>
<p>Näytä tilan jokainen testi joka rakentaa. Viimeisimmän suorittamisen tuloksena on käytettävä, jos testi on suoritettu useita kertoja samalla rakentaa. On suositeltavaa viedä tämän raportin Excel-muodossa, joka helpottaa selaamista jos suuri tietokokonaisuutta on käytössä.</p>

<h3>Charts - General Test Plan Metrics</h3>
<p>'Viime testin tulos' logiikka on käytetty kaikkien neljän kaavioita, että näet. Kaaviot ovat animoituja auttaa käyttäjä havainnollistamaan mittatietoja alkaen nykyisen testin suunnitelma. Neljä kaavioita säätää ovat:</p>
<ul><li>Ympyräkaavio yleinen hyväksytty / hylätty / tukossa / eikä suorittamaan tapauksissa</li>
<li>Pylväsdiagrammi tuloksia Hakusanat</li>
<li>Pylväsdiagrammi tuloksia Omistajan mukaan</li>
<li>Pylväsdiagrammi tuloksia Ylätaso Suite</li>
</ul>
<p>Palkit vuonna palkki kartat ovat värillisiä sellainen, että käyttäjä voi tunnistaa arvioitu määrä kulkea, epäonnistuvat, estetty, ei näytetä tapauksissa.</p>

<h3>Yhteensä Bugs Kunkin Test Case</h3>
<p>Tämä raportti osoittaa kunkin testin tapauksessa kaikki virheraportit arkistoida sitä vastaan koko hankkeeseen. Tämä raportti on käytettävissä vain, jos Vianjäljitysjärjestelmä on kytketty.</p>";

$TLS_htmltext_title['planAddTC'] = "Lisää / Poista testitapauksia Test Plan"; // testSetAdd
$TLS_htmltext['planAddTC'] = "<h2>Tarkoitus:</h2>
<p>Avulla käyttäjä (ja johtaa tason oikeudet) lisätä tai poistaa testi tapauksissa osaksi Test suunnitelma.</p>

<h2>Voit lisätä tai poistaa testitapauksia:</h2>
<ol>
<li>Klikkaa testausohjelmisto nähdä kaikki se testi sviittiä ja kaikki sen testin tapauksissa.</li>
<li>Kun olet valmis, klikkaa *Lisää / Poista' Test Cases-painiketta lisätä tai poistaa testin tapauksissa. Huomautus: Ei ole mahdollista lisätä samassa testissä tapauksessa useita kertoja.</li>
</ol>";

$TLS_htmltext_title['tc_exec_assignment'] = "Anna Tester testata täytäntöönpano";
$TLS_htmltext['tc_exec_assignment'] = "<h2>Tarkoitus</h2>
<p>Tämän sivun avulla testi johtajia määrittää käyttäjät erityisesti testeistä Testaus suunnitelma.</p>

<h2>Aloita</h2>
<ol>
<li>Valitse testi tai testausohjelmisto testata.</li>
<li>Valitse suunniteltua testauslaite.</li>
<li>Napsauta Tallenna-painiketta esittämään luokitukseen.</li>
<li>Avaa suorittamisen sivu tarkistaa assignment. Voit perustaa suodatin käyttäjille.</li>
</ol>";//


// ------------------------------------------------------------------------------------------

$TLS_htmltext_title['reqSpecMgmt'] = "Vaatimukset Eritelmä Design"; //printTC.html
$TLS_htmltext['reqSpecMgmt'] = "<p>Voit hallita Requirement Specification asiakirjoja.</p>

<h2>Requirements Specification</h2>

<p>Vaatimukset on ryhmitelty <b>Vaatimukset Eritelmä asiakirja</b>,joka liittyy Test Project.<br/> TestLink ei tue (vielä) versiot sekä eritelmä ja vaatimukset itse. Joten asiakirjan versio olisi lisättävä jälkeen Eritelmä <b>Title</b>.
Yksi käyttäjä voi lisätä yksinkertainen kuvaus tai huomautukset <b>Scope</b> field.</p>

<p><b><a name='total_count'>Overwritten count of REQs</a></b> serves for
evaluating Req. coverage in case that not all requirements are added to TestLink.
The value <b>0</b> means that current count of requirements is used
for metrics.</p>
<p><i>E.g. SRS includes 200 requirements but only 50 are added in TestLink. Test
coverage is 25% (assuming the 50 added requirements will actually be tested).</i></p>

<h2><a name='req'>Requirements</a></h2>

<p>Klikkaa otsikkoa olemassa oleva eritelmä. Jos niitä ei ole, klikkaa hankkeen node luoda sellainen. Voit luoda, muokata, poistaa tai maahantuonnin vaatimukset asiakirjan. Jokainen vaatimus on otsikko, laajuus ja tila.
A-asema olisi joko 'Normaali' tai 'Ei testavissa'. Ei testable vaatimuksia ei lasketa ja käyttötiedot. Tämä parametri olisi käyttää sekä unimplemented ominaisuuksia ja väärin suunnitellut vaatimukset.</p>

<p>Voit luoda uuden testin tapauksissa vaatimukset käyttämällä useita toimia tarkastetaan vaatimukset eritelmän näytöllä. Nämä Test Cases luodaan osaksi testausohjelmisto nimi määritelty kokoonpanoasetuksia <i>(default is: \$tlCfg->req_cfg->default_testsuite_name =
'Testausohjelmisto luotu Vaatimus - Auto';)</i>. Otsikko ja Soveltamisala kopioidaan nämä testitapauksia.</p>";

// ------------------------------------------------------------------------------------------

$TLS_htmltext_title['keywordsAssign'] = "Keyword Assignment";

$TLS_htmltext['keywordsAssign']    = "<h2>Purpose:</h2>

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

$TLS_htmltext_title['keywordsAssign'] = "Hakusanalla Tehtävä";
$TLS_htmltext['keywordsAssign'] = "<h2>Tarkoitus:</h2>
<p>Avainsanatyökalun Tehtävä sivu on paikka, jossa käyttäjät voivat erän antaa avainsanoja nykyisten testausohjelmisto tai Test asia</p>

<h2>To Assign Keywords:</h2>
<ol>
<li>Valitse testausohjelmisto tai Test tapauksessa puunäkymässä vasemmalla.</li>
<li>Alkuun kaikkein box näkyy oikealla puolella voit määrittää käytettävissä avainsanoja jokaisen testin tapauksessa.</li>
<li>Valinnat alle voit määrittää tapauksissa entistä rakeisessa tasolla.</li>
</ol>

<h2>Tärkeitä tietoja Hakusanat Luokitusprosessin vuonna Test Plans:</h2>
<p>Hakusanalla tehtäviä teet sen eritelmän vain vaikutus testi tapauksissa sinun Test suunnitelmia jos ja vain jos testin suunnitelma sisältää uusimman version Testaus tapauksessa. Muuten, jos testi suunnitelma sisältää vanhoja versioita testin tapauksessa tehtäviä teet nyt ei näy testi suunnitelmassa.</p>
<p>TestLink käyttää tätä lähestymistapaa niin, että vanhemmat versiot testi tapauksissa testi suunnitelmat eivät vaikuta Hakusanalla tehtäviä teet sen viimeisintä versiota testin tapauksessa.Jos haluat testata tapauksissa testi-suunnitelma on päivitetty, ensin vahvistaa ne ovat ajan tasalla käyttämällä 'Päivitä Modified Test Cases' toiminnallisuutta, ennen kuin teet Hakusanalla toimeksiannoissa.</p>";

$TLS_htmltext_title['executeTest'] = "Test Asia Suoritusaika";
$TLS_htmltext['executeTest'] = "<h2>Purpose:</h2>

<p>Avulla käyttäjä voi suorittaa testitapauksia. Käyttäjä voi määrittää Testitulos Test Johdanto Build. Katso ohjeesta lisätietoja suodatin ja asetukset. (klikkaa kysymysmerkki-kuvake).</p>

<h2>Aloita:</h2>

<ol>
<li>Käyttäjä on määrittänyt Rakenna varten Test Plan.</li>
<li>Käyttäjä on määrittänyt Rakenna varten Test Plan.</li>
<li>Klikkaa testin tapauksessa puun valikosta.</li>
<li>Täytä testin tapauksessa johtaa ja muussa sovellettavassa muistiinpanoja tai vikoja.</li>
<li>Tallenna tulokset.</li>
</ol>
<p><i>Huom: TestLink on configurated tehdä yhteistyötä teidän Bug tracker jos haluat luoda / jäljittää ongelmaraportti suoraan niiden GU</i></p>";//

// ------------------------------------------------------------------------------------------

$TLS_htmltext_title['showMetrics'] = "Kuvaus testausselosteet ja mittarit";
$TLS_htmltext['showMetrics'] = "<p>Raportit liittyvät testisuunnitelmaan " ."
(määritelty alkuun navigator). Tämä Test Plan voisi poiketa nykyisen testin suunnitelman toteutusta varten. Voit myös valita raportin muodossa:</p>
<ul>
<li><b>Normaalitila</b> - raportti näkyy Web-sivun</li>
<li><b>OpenOffice Writer</b> - Raportti tuodaan OpenOffice Writer</li>
<li><b>OpenOffice Calc</b> - Raportti tuodaan OpenOffice Calc</li>
<li><b>MS Excel</b>- Raportti tuodaan Microsoft Excel</li>
<li><b>HTML Email</b> - Raportti on lähetetty sähköpostitse käyttäjän sähköpostiosoite</li>
<li><b>Charts</b> - Raportti sisältää kaavioita (flash-teknologia)</li>
</ul>

<p>Tulosta-painike aktivoi painaminen raportin vain (ilman suunnistus).</p>
<p>On olemassa useita erillisiä raportteja valita, niiden merkitys ja asema on selitetty alla.</p>

<h3>Test Plan</h3>
<p>Asiakirja 'Test Plan' on vaihtoehtoja määritellä sisältöä ja dokumentin rakenne.</p>

<h3>Test Report</h3>
<p>Asiakirja 'Testitulokset' on vaihtoehtoja määritellä sisällön ja asiakirjojen rakenne. Se sisältää testitapauksia yhdessä testitulokset.</p>

<h3>Yleiset Test Plan Metrics</h3>
<p>Tämä sivu näyttää vain kaikkein nykyinen tila testi suunnitelman testausohjelmisto, omistaja, ja avainsanan mukaan. Kaikkein 'nykyinen tila' määräytyy viimeisimmän rakentaa testi tapauksissa teloitettiin päälle. Esimerkiksi, jos testi on suoritettu useita rakentuu vain viimeisin tulos otetaan huomioon.</p>

<p>'Viime Testitulos' on käsite, josta käytetään monissa, ja se määritellään seuraavasti:</p>
<ul>
<li>Järjestystä, joka perustuu lisätään testin suunnitelmassa määritellään, mitä kasvu uusimpaan. Tulokset viimeisimmän rakentaa tulevat precendence verrattuna vanhempiin rakentuu. Jos esimerkiksi merkitä testataan kuin 'ei toimi' on build 1 ja merkitse sitä 'siirrä' vuonna build 2, on uusin tulos on 'siirrä'.</li>
<li>Jos testi on suoritettu mulitple kertaan samalle rakentaa viimeisintä teloituksen edelle. Esimerkiksi, jos rakentaa 3 vapautuu tiimiäsi ja testauslaitteen 1 merkitsee sitä 'pass' at 2PM, ja testauslaitteen 2 merkitsee sitä'ei toimi'at 3PM - se näkyy 'ei toimi'.</li>
<li>Test tapauksissa kuin 'ei toimi' vastaan rakentaa ei ole otettu huomioon. Jos esimerkiksi merkitä tapauksessa 'pass' on rakentaa 1, ja eivät suorita se rakentaa 2, on viime tuloksena voidaan pitää 'siirrä'.</li>
</ul>

<p>Seuraavissa taulukoissa näkyvät:</p>
<ul>
<li><b>Tulokset huipputason Test Suites</b>
Listaa tulokset kunkin huipputason sviitti. Yhteensä tapauksissa hyväksytään, epäonnistunut, estetty, ei näytetä, ja prosenttia valmistunut on lueteltu. A 'valmistunut' testausta on yksi, joka on merkitty onnistunut, epäonnistui tai lukittu. Tulokset huipputason suites sisältää kaikkien ala sviittiä.</li>
<li><b>Tulokset Hakusanat</b>
Luetellaan kaikki avainsanat, jotka on osoitettu tapauksissa nykyisen testin suunnitelmassa, ja tulokset liittyvät niihin.</li>
<li><b>Tulokset omistaja</b>
Luettelot kunkin omistaja, joka on testi tapauksissa niille nykyisen testin suunnitelma. Testitapauksia, jotka eivät ole sidottuja ovat kerätään mukaan 'vapaana' -otsikon alle.</li>
</ul>

<h3>Yleisen Rakenna Status</h3>
<p>Lists the execution results for every build. For each build, the total test cases, total pass,
% pass, total fail, % fail, blocked, % blocked, not run, %not run. If a test case has been executed
twice on the same build, the most recent execution will be taken into account.</p>

<h3>Kysely Metrics</h3>
<p>Tämä raportti koostuu kyselyn muodossa sivulla, ja kyselyn tulokset sivun, joka sisältää kysyi tietoja. Kyselyn muoto Page esittää kyselyä sivun 4 valvontaa. Kukin valvonta on asetettu oletuksena joka maksimoi määrä testisivun tapauksissa ja rakentaa kyselyn olisi tehtävä vastaan Muuttamatta valvonnan avulla käyttäjä voi suodattaa tuloksia ja tuottaa raportteja erityisiä omistaja, avainsanan, sarja ja rakentaa yhdistelmiä.</p>

<ul>
<li><b>Avainsanoja</b> 0 -> 1 avainsanoja voidaan valita. Oletusarvon - avainsanatason on valittu. Jos avainsana ei ole valittu, niin kaikki testin tapauksissa pidetään riippumatta Hakusanalla toimeksiannoissa. Avainsanat luokitellaan testin eritelmän tai 'Hakusanat Management' sivua. Avainsanat annetaan testi tapauksissa span kaikki testi suunnitelmat, ja vertailukaasut kaikissa versioissa testin tapauksessa. Jos olet kiinnostunut tulokset tietyllä avainsanalla, voit muuttaa tätä valvontaa.</li>
<li><b>omistaja</b> 0 -> 1 omistajat voivat valita. Oletuksena - ei omistaja on valittu. Jos omistaja ei ole valittu, niin kaikki testin tapauksissa pidetään riippumatta omistajan assignment. Tällä hetkellä ei ole toimintoa, haun 'määrittämätön' testi tapauksissa. Omistus on sijoitettu kautta 'Määritä Test asia toteutusta' -sivu ja tehdään per testi suunnitelman pohjalta. Jos olet kiinnostunut työn erityinen testauslaitteen voit muuttaa tätä valvontaa</li>
<li><b>huipputason suite</b> 0 -> n huipputason sviittiä voidaan valita. Oletusarvon - kaikki sviitit on valittu. Vain sviittiä, jotka on valittu kyselyitä tulosta mittatietoja. Jos olet vain intested vuonna tulokset erityinen sarja voit muuttaa tätä valvontaa.</li>
<li><b>Rakentaa</b> 1 -> n rakentuu voidaan valita. Oletusarvon - kaikki rakentuu on valittu. Vain teloituksia tehtävä rakentaa valitset otetaan huomioon tuotannon lukuja. Esimerkiksi - jos haluat nähdä kuinka monta testi tapauksissa teloitettiin vuoden viimeisenä 3 rakentuu - voit muuttaa tätä valvontaa Avainsana, omistaja, ja huipputason suite valinnat sanella määrä testi tapauksissa testi-suunnitelmaa käytetään computate per sarja ja testiä kohti suunnitelma mittatietoja. Esimerkiksi, jos valitset omistaja = 'Greg ', Keyword =' Priority 1 ', ja kaikki saatavilla olevat testi sviittiä - vain Priority 1 testi asioissa että Greg otetaan huomioon. The 'Testitapaus' -kokonaissummat näet mietinnöstä on vaikuttanut näiden 3 valvontaa. Rakenna selections vaikuttaa, jos kyseessä on 'siirtää', 'ei', 'lukittu' tai 'ei toimi'. Tutustu viimeiset Testitulos säännöt kuin ne näkyvät yllä.</li>
</ul>
<p>Napsauta Lähetä-painiketta jatkaa kyselyä ja näyttämiseen sivulla.</p>

<p>Hakuraportti sivu näyttää: </p>
<ol>
<li>Kyselyn parametrit, joiden avulla luoda</li>
<li>kokonaismäärät koko testi suunnitelma</li>
<li>per sarja jakautuminen kokonaismäärät (summa / hyväksytty / hylätty / tukossa / ei näytetä) ja kaikki teloitukset suorittaa että sviitti. Jos testi on suoritettu useammin kuin kerran useita rakentuu - kaikki teloitukset näkyy, että kirjattiin vastaan valitun rakentuu Kuitenkin yhteenveto siitä suite sisältävät vain viimeiset 'Testitulos varten' valittua rakentuu.</li>
</ol>

<h3>Estetty, ei onnistunut, eikä Suorita Test Asia Raportit</h3>
<p>Nämä raportit osoittavat, kaikki tällä hetkellä pysähdyksissä, ei ole tai ei suorittamaan tapauksissa. 'Viime testin tulos' logiikka (joka on kuvattu edellä General Test Plan Metrics) on jälleen palveluksessa, onko testi tapauksessa olisi harkittava estetty, ei ole, tai ei toimi Estetyt ja epäonnistunut testi tapausselostukset näyttää liittyviä vikoja, jos käyttäjä käyttää integroitua vianseurantajärjestelmä.</p>

<h3>Testitulokset</h3>
<p>Näytä tilan jokainen testi joka rakentaa. Viimeisimmän suorittamisen tuloksena on käytettävä, jos testi on suoritettu useita kertoja samalla rakentaa. On suositeltavaa viedä tämän raportin Excel-muodossa, joka helpottaa selaamista jos suuri tietokokonaisuutta on käytössä.</p>

<h3>Listat - Yleistä Test Plan Metrics</h3>
<p>'Viime testin tulos' logiikka on käytetty kaikkien neljän kaavioita, että näet. Kaaviot ovat animoituja auttaa käyttäjä havainnollistamaan mittatietoja alkaen nykyisen testin suunnitelma. Neljä kaavioita säätää ovat:</p>
<ul><li>Ympyräkaavio yleinen hyväksytty / hylätty / tukossa / eikä suorittamaan tapauksissa</li>
<li>Pylväsdiagrammi tuloksia Hakusanat</li>
<li>Pylväsdiagrammi tuloksia Omistaja</li>
<li>Pylväsdiagrammi tuloksia Ylätaso Suite</li>
</ul>
<p>Palkit vuonna palkki kartat ovat värillisiä sellainen, että käyttäjä voi tunnistaa arvioitu määrä kulkea, epäonnistuvat, estetty, ei näytetä tapauksissa.</p>

<h3>Yhteensä Bugs jokaisessa testissä asia</h3>
<p>Tämä raportti osoittaa kunkin testin tapauksessa kaikki virheraportit arkistoida sitä vastaan koko hankkeeseen. Tämä raportti on käytettävissä vain, jos Vianjäljitysjärjestelmä on kytketty.</p>";

 

// ------------------------------------------------------------------------------------------

$TLS_htmltext_title['planAddTC'] = "Lisää / Poista testitapauksia Testisuunnitelmaan"; //testSetAdd
$TLS_htmltext['planAddTC'] = "<h2>Päämäärä</h2>
<p>Avulla käyttäjä (ja johtaa tason oikeudet) lisätä tai poistaa testi tapauksissa osaksi Test suunnitelma.</p>

<h2>Lisätä tai poistaa testitapauksia:</h2>
<ol>
<li>Klikkaa testausohjelmistoa nähdäksesi kaikki se testi sviittiä ja kaikki sen testitapaukset.</li>
<li>Kun olet valmis, klikkaa 'Lisää / Poista' Test Cases-painiketta lisätä tai poistaa testin tapauksissa. Huomautus: Ei ole mahdollista lisätä samassa testissä tapauksessa useita kertoja.</li>
</ol>";//

 

// ------------------------------------------------------------------------------------------

$TLS_htmltext_title['tc_exec_assignment'] = "Anna Testaajan testata täytäntöönpano";
$TLS_htmltext['tc_exec_assignment'] = "<h2>Päämäärä</h2>
<p>Tämän sivun avulla testi johtajia määrittää käyttäjät erityisesti testeistä Testaus suunnitelma.</p>

<h2>Aloitus</h2>
<ol>
<li>Valitse Testitapaus tai Testikansio testattavaksi.</li>
<li>Valitse suunniteltu testaaja.</li>
<li>Napsauta Tallenna-painiketta esittämään luokitukseen.</li>
<li>Avaa suorittamisen sivu tarkistaa assignment. Voit perustaa suodatin käyttäjille.</li>
</ol>";

// ------------------------------------------------------------------------------------------


$TLS_htmltext_title['planUpdateTC'] = "Päivitä Testitapaukset, Testisuunnitelmassa";
$TLS_htmltext['planUpdateTC'] = "<h2>Päämäärä</h2>
<p>Tämän sivun avulla päivittäminen Test tapauksessa uudempaan (eri) versio, jos testauseritelmä on muuttunut. On harvinaista, että joitakin toimintoja on selkeytetty testauksen aikana. . Käyttäjä muuttaa testauseritelmä, mutta muutoksia on propagoivat Test Plan liikaa. Muuten Test suunnitelma omistaa alkuperäinen versio, jotta voitaisiin varmistaa, että tulokset viittaavat oikea teksti testin tapauksessa.</p>

<h2>Aloita</h2>
<ol>
<li>Valitse Testitapaus tai Testikansio testattavaksi.</li>
<li>Valitse uusi versio combo-box-valikosta tietyn Testitapauksessa mukaan.</li>
<li>Napsauta Päivitä testisuunnitelma-painiketta esittää muutoksia.</li>
<li>Voit tarkistaa: Avaa suorittamisen sivun avulla voit katsella teksti testin tapauksessa.</li>
</ol>";
 

// ------------------------------------------------------------------------------------------

$TLS_htmltext_title['test_urgency'] = "Specify tests with high or low urgency";

$TLS_htmltext['test_urgency']   = "<h2>Purpose</h2>

<p>TestLink allows setting the urgency of a Test Suite to affect the  testing Priority of test cases.

Test priority depends on both Importance of Test cases and Urgency defined in

the Test Plan. Test leader should specify a set of test cases that could be tested

at first. It helps to ensure that testing will cover the most important tests

also under time pressure.</p>

 

<h2>Aloita</h2>

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