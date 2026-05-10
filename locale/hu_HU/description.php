<?php
/** 
 * ♔ TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later. 
 * 
 * Localization: Hungarian (hu_HU) texts
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
 * @author 		Kiss-Kálmán Dániel
 * @copyright 	2003-2009, TestLink community 
 * @version    	CVS: $Id: description.php,v 1.17 2010/09/13 09:52:42 mx-julian Exp $
 * @link 		http://www.teamst.org/index.php
 *
 * @internal Revisions:
 * 20100409 - eloff - BUGID 3050 - Update execution help text
 **/


// printFilter.html
$TLS_hlp_generateDocOptions = "<h2>Opciók a generált dokumentumhoz</h2>

<p>Ez a táblázat lehetővé teszi a felhasználó számára a tesztesetek szűrését a megtekintés előtt. Ha
be van jelölve, az adatok megjelennek. A megjelenített adatok
módosításához jelölje be vagy törölje a jelölést, kattintson a Szűrőre, majd válassza ki a kívánt
adatszintet a fanézetből.</p>

<p><b>Dokumentum fejléce:</b> A felhasználók kiszűrhetik a dokumentum fejléc információit. 
A dokumentum fejléce a következőket tartalmazza: Bevezetés, Hatókör, Referenciák, 
Tesztmódszertan és Tesztkorlátozások.</p>

<p><b>Teszteset törzse:</b> A felhasználók kiszűrhetik a teszteset törzsére vonatkozó információkat. A teszteset törzse
a következőket tartalmazza: Összegzés, Lépések, Elvárt eredmények és Kulcsszavak.</p>

<p><b>Teszteset összegzése:</b> A felhasználók kiszűrhetik a teszteset összegzését a teszteset címéből, 
azonban nem szűrhetik ki a teszteset összegzését a teszteset 
törzséből. A teszteset összegzése csak részben lett elkülönítve a teszteset 
törzsétől annak érdekében, hogy támogassa a címek megtekintését rövid összefoglalóval a 
Lépések, Elvárt eredmények és Kulcsszavak hiányában. Ha a felhasználó úgy dönt, hogy megtekinti a teszteset 
törzsét, a teszteset összegzése mindig belekerül.</p>

<p><b>Tartalomjegyzék:</b> A TestLink beilleszti az összes cím listáját belső hiperhivatkozásokkal, ha be van jelölve.</p>

<p><b>Kimeneti formátum:</b> Két lehetőség van: HTML és MS Word. A böngésző az MS Word összetevőt hívja meg 
a második esetben.</p>";

// testPlan.html
$TLS_hlp_testPlan = "<h2>Tesztterv</h2>

<h3>Általános</h3>
<p>A tesztterv egy szisztematikus megközelítés egy rendszer, például egy szoftver tesztelésére. Megszervezheti a tesztelési tevékenységet a 
termék konkrét buildjeivel az időben, és nyomon követheti az eredményeket.</p>

<h3>Teszt végrehajtása</h3>
<p>Ebben a részben a felhasználók végrehajthatják a teszteseteket (teszteredményeket rögzíthetnek) és 
kinyomtathatják a tesztterv teszteset-készletét. Ebben a szakaszban a felhasználók nyomon követhetik 
teszteseteik végrehajtásának eredményeit.</p> 

<h2>Tesztterv kezelése</h2>
<p>Ez a szakasz, amelyhez csak tesztvezetői szintű hozzáféréssel rendelkezők férhetnek hozzá, lehetővé teszi a felhasználók számára a teszttervek adminisztrálását. 
A teszttervek adminisztrálása magában foglalja a tervek létrehozását/szerkesztését/törlését, 
a tesztesetek hozzáadását/szerkesztését/törlését/frissítését a tervekben, buildek létrehozását, valamint annak meghatározását, hogy ki 
láthatja az adott tervet.<br />
A vezetői jogosultságokkal rendelkező felhasználók beállíthatják a teszteset-készletek (kategóriák) prioritását/kockázatát és tulajdonosát, 
valamint tesztelési mérföldköveket hozhatnak létre.</p> 

<p>Megjegyzés: Előfordulhat, hogy a felhasználók nem látnak tesztterveket tartalmazó legördülő menüt. 
Ebben a helyzetben minden hivatkozás (kivéve a vezetői jogosultsághoz kötötteket) inaktív lesz. Ha 
ebben a helyzetben van, kapcsolatba kell lépnie egy vezetővel vagy adminisztrátorral, hogy megadja Önnek a megfelelő 
projekthez való jogokat, vagy hozzon létre egy teszttervet az Ön számára.</p>"; 

// custom_fields.html
$TLS_hlp_customFields = "<h2>Egyéni mezők</h2>
<p>Az alábbiakban néhány tény olvasható az egyéni mezők megvalósításáról:</p>
<ul>
<li>Az egyéni mezők rendszerszinten vannak meghatározva.</li>
<li>Az egyéni mezők egy elem típusához kapcsolódnak (tesztkészlet, teszteset).</li>
<li>Az egyéni mezők több tesztprojekthez is kapcsolhatók.</li>
<li>Az egyéni mezők megjelenítésének sorrendje tesztprojektenként eltérő lehet.</li>
<li>Az egyéni mezők inaktívvá tehetők egy adott tesztprojektben.</li>
<li>Az egyéni mezők száma nincs korlátozva.</li>
</ul>

<p>Egy egyéni mező meghatározása a következő logikai 
attribútumokat tartalmazza:</p>
<ul>
<li>Egyéni mező neve</li>
<li>Felirat változó neve (pl.: ez az az érték, amely átadódik a lang_get() API-nak, 
vagy eredeti formájában jelenik meg, ha nem található a nyelvifájlban).</li>
<li>Egyéni mező típusa (karakterlánc, numerikus, lebegőpontos, felsorolás, e-mail)</li>
<li>Felsorolás lehetséges értékei (pl.: PIROS|SÁRGA|KÉK), listákra, többválasztós listákra 
és combo típusokra vonatkozik.<br />
<i>Használja a cső ('|') karaktert a 
felsorolás lehetséges értékeinek elválasztásához. Az egyik lehetséges érték 
lehet üres karakterlánc is.</i>
</li>
<li>Alapértelmezett érték: MÉG NINCS MEGVALÓSÍTVA</li>
<li>Egyéni mező értékének minimális/maximális hossza (használjon 0-t a kikapcsoláshoz). (MÉG NINCS MEGVALÓSÍTVA)</li>
<li>Felhasználói bevitel érvényesítéséhez használt reguláris kifejezés 
(használja az <a href=\"http://au.php.net/manual/en/function.ereg.php\">ereg()</a> 
szintaxist). <b>(MÉG NINCS MEGVALÓSÍTVA)</b></li>
<li>Minden egyéni mező jelenleg VARCHAR(255) típusú mezőbe kerül mentésre az adatbázisban.</li>
<li>Megjelenítés a tesztspecifikációban.</li>
<li>Engedélyezés a tesztspecifikációban. A felhasználó módosíthatja az értéket a teszteset-specifikáció tervezése során.</li>
<li>Megjelenítés a teszt végrehajtásakor.</li>
<li>Engedélyezés a teszt végrehajtásakor. A felhasználó módosíthatja az értéket a teszteset végrehajtása során.</li>
<li>Megjelenítés a tesztterv tervezésekor.</li>
<li>Engedélyezés a tesztterv tervezésekor. A felhasználó módosíthatja az értéket a tesztterv tervezése során (tesztesetek hozzáadása a teszttervhez).</li>
<li>Elérhető: A felhasználó kiválaszthatja, hogy a mező milyen típusú elemhez tartozik.</li>
</ul>
";

// execMain.html
$TLS_hlp_executeMain = "<h2>Tesztesetek végrehajtása</h2>
<p>Lehetővé teszi a felhasználók számára a tesztesetek 'végrehajtását'. Maga a végrehajtás csupán 
egy teszteredmény (sikeres, sikertelen, blokkolt) hozzárendelését jelenti a kiválasztott buildhez képest.</p>
<p>A hibakövető rendszerhez való hozzáférés konfigurálható. A felhasználó közvetlenül hozzáadhat új hibákat, 
és böngészheti a meglévőket. További információért lásd a telepítési kézikönyvet.</p>";

//bug_add.html
$TLS_hlp_btsIntegration = "<h2>Hibák hozzáadása a tesztesethez</h2>
<p><i>(csak ha konfigurálva van)</i>
A TestLink egy nagyon egyszerű integrációval rendelkezik a hibakövető rendszerekkel (BTS), 
amely nem képes hibalétrehozási kérést küldeni a BTS-nek, sem visszakapni a hibaazonosítót (bug id). 
Az integráció a BTS oldalaira mutató linkeken keresztül történik, amelyek a következő funkciókat hívják meg:
<ul>
    <li>Új hiba beszúrása.</li>
    <li>Létező hiba információinak megjelenítése. </li>
</ul>
</p>  

<h3>A hiba hozzáadásának folyamata</h3>
<p>
   <ul>
   <li>1. lépés: használja a linket a BTS megnyitásához az új hiba beszúrásához. </li>
   <li>2. lépés: jegyezze fel a BTS által hozzárendelt BUGID-t (hibaazonosítót).</li>
   <li>3. lépés: írja be a BUGID-t a beviteli mezőbe.</li>
   <li>4. lépés: használja a hiba hozzáadása (add bug) gombot.</li>
   </ul>  

A hiba hozzáadása oldal bezárása után a releváns hibaadatokat a végrehajtási oldalon fogja látni.
</p>";

// execFilter.html
$TLS_hlp_executeFilter = "<h2>Beállítások</h2>

<p>A Beállítások lehetővé teszik a végrehajtandó tesztterv, build és platform (ha elérhető) 
kiválasztását.</p>

<h3>Tesztterv</h3>
<p>Kiválaszthatja a kívánt teszttervet. A választott tesztterv alapján a megfelelő 
buildek jelennek meg. A tesztterv kiválasztása után a szűrők alaphelyzetbe állnak.</p>

<h3>Platform</h3>
<p>Ha a platformok funkciót használják, a végrehajtás előtt ki kell választania a megfelelő platformot.</p>

<h3>Végrehajtandó build</h3>
<p>Kiválaszthatja azt a buildet, amelyhez végrehajtani kívánja a teszteseteket.</p>

<h2>Szűrők</h2>
<p>A szűrők lehetőséget adnak a megjelenített tesztesetek körének további befolyásolására 
a végrehajtás előtt. A szűrők megadásával és az \"Alkalmaz\" gombra kattintva csökkentheti 
a megjelenített tesztesetek számát.</p>

<p>A Speciális szűrők lehetővé teszik az értékek készletének meghatározását a vonatkozó szűrőkhöz a 
többválasztós listában a CTRL-kattintás használatával.</p>


<h3>Kulcsszó szűrő</h3>
<p>Szűrheti a teszteseteket a hozzárendelt kulcsszavak alapján. A CTRL-kattintással 
több kulcsszót is kiválaszthat. Ha egynél több kulcsszót választott, 
eldöntheti, hogy csak azok a tesztesetek jelenjenek meg, amelyekhez az összes választott kulcsszó hozzá van rendelve 
(\"And\" rádiógomb), vagy legalább az egyik választott kulcsszóval rendelkeznek (\"Or\" rádiógomb).</p>

<h3>Prioritás szűrő</h3>
<p>Szűrheti a teszteseteket a teszt prioritása alapján. A teszt prioritása a \"teszteset fontossága\" 
és a \"teszt sürgőssége\" kombinációja az aktuális teszttervben.</p> 

<h3>Felhasználó szűrő</h3>
<p>Szűrheti azokat a teszteseteket, amelyek nincsenek hozzárendelve (\"Senki\"), vagy hozzá vannak rendelve \"Valakihez\". 
Szűrheti az egy adott tesztelőhöz rendelt teszteseteket is. Ha konkrét 
tesztelőt választott, lehetősége van a hozzárendelt tesztesetek mellett a hozzá nem rendelt tesztesetek 
megjelenítésére is (speciális szűrők elérhetők). </p>

<h3>Eredmény szűrő</h3>
<p>Szűrheti a teszteseteket az eredmény alapján (speciális szűrők elérhetők). Szűrhet 
eredmény szerint a \"kiválasztott builden a végrehajtáshoz\", a \"legutóbbi végrehajtáson\", az \"ÖSSZES builden\", 
\"BÁRMELYIK builden\" és egy \"konkrét builden\". Ha a \"konkrét build\" van kiválasztva, akkor 
megadhatja a buildet. </p>";


// newest_tcversions.html
$TLS_hlp_planTcModified = "<h2>Összekapcsolt tesztesetek legújabb verziói</h2>
<p>A rendszer elemzi a teszttervhez kapcsolt tesztesetek teljes körét, és megjeleníti azon tesztesetek listáját, 
amelyeknek van újabb verziója (a teszttervben szereplő aktuális készlethez képest).
</p>";


// requirementsCoverage.html
$TLS_hlp_requirementsCoverage = "<h3>Követelmény-lefedettség</h3>
<br />
<p>Ez a funkció lehetővé teszi a felhasználói vagy rendszerkövetelmények tesztesetekkel való lefedettségének feltérképezését. 
Navigáljon a főképernyőn a \"Követelményspecifikáció\" hivatkozáson keresztül.</p>

<h3>Követelményspecifikáció</h3>
<p>A követelmények 'Követelményspecifikáció' dokumentumba vannak csoportosítva, amely a 
Tesztprojekthez kapcsolódik.<br /> A TestLink nem támogatja a verziókezelést sem a követelményspecifikációk, 
sem maguk a követelmények esetében. Ezért a dokumentum verzióját a specifikáció <b>Cím</b> 
mezője után célszerű hozzáadni. A felhasználó egyszerű leírást vagy megjegyzéseket fűzhet a <b>Hatókör (Scope)</b> mezőhöz.</p> 

<p>A <b><a name='total_count'>Követelmények felülírt száma</a></b> a 
követelmény-lefedettség értékelésére szolgál abban az esetben, ha nem minden követelményt vittek fel (importáltak). 
A <b>0</b> érték azt jelenti, hogy a metrikákhoz a követelmények aktuális számát kell használni.</p> 
<p><i>Példa: Az SRS 200 követelményt tartalmaz, de csak 50 van felvéve a TestLinkbe. 
A tesztlefedettség így 25% (ha az összes felvett követelmény tesztelve lesz).</i></p>

<h3><a name=\"req\">Követelmények</a></h3>
<p>Kattintson egy létrehozott követelményspecifikáció címére. Létrehozhat, szerkeszthet, törölhet 
vagy importálhat követelményeket a dokumentumhoz. Minden követelmény rendelkezik címmel, hatókörrel és állapottal. 
Az állapot lehet \"Normál\" vagy \"Nem tesztelhető\". A nem tesztelhető követelmények nem számítanak bele 
a metrikákba. Ezt a paramétert mind a még meg nem valósított funkciók, mind a 
hibásan tervezett követelmények esetén használni kell.</p> 

<p>A specifikációs képernyőn a bejelölt követelményekkel és a tömeges műveletek használatával új teszteseteket hozhat létre a követelményekhez. Ezek a tesztesetek abba a tesztkészletbe kerülnek, 
amelynek neve a konfigurációban van meghatározva <i>(alapértelmezett: &#36;tlCfg->req_cfg->default_testsuite_name = 
\"Test suite created by Requirement - Auto\";)</i>. A Cím és a Hatókör mezők átmásolódnak ezekbe a tesztesetekbe.</p>
";

$TLS_hlp_req_coverage_table = "<h3>Lefedettség:</h3>
Egy érték, például a \"40% (8/20)\" azt jelenti, hogy ehhez a követelményhez összesen 20 tesztesetet kell létrehozni 
a teljes körű teszteléshez. Ebből 8 már létrejött és kapcsolódik ehhez a követelményhez, ami 
40 százalékos lefedettséget eredményez.
";


// req_edit
$TLS_hlp_req_edit = "<h3>Belső hivatkozások a hatókörben:</h3>
<p>A belső hivatkozások más követelményekhez/követelményspecifikációkhoz való kapcsolódást szolgálják 
speciális szintaxissal. A belső hivatkozások viselkedése a konfigurációs fájlban módosítható.
<br /><br />
<b>Használat:</b>
<br />
Hivatkozás követelményekre: [req]req_doc_id[/req]<br />
Hivatkozás követelményspecifikációkra: [req_spec]req_spec_doc_id[/req_spec]</p>

<p>A követelmény / követelményspecifikáció tesztprojektje, egy verzió és egy horgony (anchor) 
is megadható az ugráshoz:<br />
[req tproj=&lt;tproj_prefix&gt; anchor=&lt;anchor_name&gt; version=&lt;version_number&gt;]req_doc_id[/req]<br />
Ez a szintaxis a követelményspecifikációk esetében is működik (a verzió attribútumnak nincs hatása).<br />
Ha nem ad meg verziót, a teljes követelmény megjelenik az összes verziójával együtt.</p>

<h3>Naplóüzenet a változtatásokhoz:</h3>
<p>Amikor módosítás történik, a Testlink naplóüzenetet kér. Ez a naplóüzenet a nyomonkövethetőséget szolgálja.
Ha csak a követelmény hatóköre (scope) változott, eldöntheti, hogy létrehoz-e új revíziót vagy sem. 
Bármilyen egyéb változtatás esetén kötelező új revíziót létrehoznia.</p>
";


// req_view
$TLS_hlp_req_view = "<h3>Közvetlen linkek:</h3>
<p>A dokumentum másokkal való egyszerű megosztásához kattintson a dokumentum tetején található földgömb ikonra egy közvetlen link létrehozásához.</p>

<h3>Előzmények megtekintése:</h3>
<p>Ez a funkció lehetővé teszi a követelmény revízióinak/verzióinak összehasonlítását, ha több revízió/verzió létezik. 
Az áttekintés tartalmazza az egyes revíziókhoz/verziókhoz tartozó naplóüzenetet, az időbélyeget és az utolsó módosítást végző szerzőt.</p>

<h3>Lefedettség:</h3>
<p>Megjeleníti az ehhez a követelményhez kapcsolódó összes tesztesetet.</p>

<h3>Kapcsolatok:</h3>
<p>A követelmény-kapcsolatok a követelmények közötti összefüggések modellezésére szolgálnak. 
Az egyéni kapcsolatok és a különböző tesztprojektek követelményei közötti kapcsolatok engedélyezése 
a konfigurációs fájlban állítható be. 
Ha beállítja az \"A követelmény a szülője a B követelménynek\" kapcsolatot, 
a Testlink implicit módon beállítja a \"B követelmény a gyermeke az A követelménynek\" kapcsolatot is.</p>
";


// req_spec_edit
$TLS_hlp_req_spec_edit = "<h3>Belső hivatkozások a hatókörben:</h3>
<p>A belső hivatkozások más követelményekhez/követelményspecifikációkhoz való kapcsolódást szolgálják 
speciális szintaxissal. A belső hivatkozások viselkedése a konfigurációs fájlban módosítható.
<br /><br />
<b>Használat:</b>
<br />
Hivatkozás követelményekre: [req]req_doc_id[/req]<br />
Hivatkozás követelményspecifikációkra: [req_spec]req_spec_doc_id[/req_spec]</p>

<p>A követelmény / követelményspecifikáció tesztprojektje, egy verzió és egy horgony 
is megadható az ugráshoz:<br />
[req tproj=&lt;tproj_prefix&gt; anchor=&lt;anchor_name&gt; version=&lt;version_number&gt;]req_doc_id[/req]<br />
Ez a szintaxis a követelményspecifikációk esetében is működik (a verzió attribútumnak nincs hatása).<br />
Ha nem ad meg verziót, a teljes követelmény megjelenik az összes verziójával együtt.</p>
";


// planAddTC_m1.tpl
$TLS_hlp_planAddTC = "<h2>Az 'Egyéni mezők mentése' kapcsán</h2>
Ha meghatározott és a Tesztprojekthez rendelt 
egyéni mezőket a következőkkel:<br /> 
 'Megjelenítés a tesztterv tervezésekor=true' és <br />
 'Engedélyezés a tesztterv tervezésekor=true'<br />
akkor ezeket ezen az oldalon CSAK a teszttervhez kapcsolt teszteseteknél fogja látni.
";


// resultsByTesterPerBuild.tpl
$TLS_hlp_results_by_tester_per_build_table = "<b>További információk a tesztelőkről:</b><br />
Ha rákattint egy tesztelő nevére ebben a táblázatban, részletesebb áttekintést kap 
az adott felhasználóhoz rendelt összes tesztesetről és a tesztelési folyamatáról.<br /><br />
<b>Megjegyzés:</b><br />
Ez a jelentés azokat a teszteseteket mutatja be, amelyek egy adott felhasználóhoz vannak rendelve, és végre lettek hajtva 
minden aktív build alapján. Még ha egy tesztesetet nem is a hozzárendelt felhasználó hajtott végre, 
a teszteset végrehajtottként fog megjelenni a hozzárendelt felhasználónál.";

// xxx.html
//$TLS_hlp_xxx = "";

// ----- END ------------------------------------------------------------------
?>
