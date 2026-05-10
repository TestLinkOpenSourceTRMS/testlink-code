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
 * @author 		Kiss-Kálmán Dániel
 * @copyright 	2003-2009, TestLink community 
 * @version    	CVS: $Id: texts.php,v 1.29 2010/07/22 14:14:44 asimon83 Exp $
 * @link 		http://www.teamst.org/index.php
 *
 **/


// --------------------------------------------------------------------------------------
$TLS_htmltext_title['error']	= "Alkalmazáshiba";
$TLS_htmltext['error'] 		= "<p>Váratlan hiba történt. Kérjük, ellenőrizze az eseménynaplót vagy " .
		"a naplófájlokat a részletekért.Szívesen vesszük, ha jelenti a hibát. Kérjük, látogasson el</p><p>" .
		"a <a href='http://www.teamst.org'>weboldalunkra</a>.</p>";



$TLS_htmltext_title['assignReqs']    = "Követelmények hozzárendelése a tesztesethez";
$TLS_htmltext['assignReqs']          = "<h2>Cél:</h2>
<p>A felhasználók kapcsolatokat hozhatnak létre a követelmények és a tesztesetek között. Egy teszttervező
0..n - 0..n kapcsolatokat határozhat meg. Vagyis egy teszteset hozzárendelhető semennyi, egy vagy több
követelményhez, és fordítva. Az ilyen nyomonkövethetőségi mátrix segít vizsgálni a követelmények tesztelési lefedettségét,
és kideríteni, melyek buktak el a tesztelés során. Ez az elemzés megerősítésként szolgál arra, hogy minden
meghatározott elvárás teljesült.</p>

<h2>Első lépések:</h2>
<ol>
    <li>Válasszon ki egy tesztesetet a bal oldali fában. A követelményspecifikációk listáját tartalmazó legördülő menü a munkaterület tetején látható.</li>
    <li>Válasszon ki egy követelményspecifikációs dokumentumot, ha több is meg van határozva. 
    A TestLink automatikusan újratölti az oldalt.</li>
    <li>A munkaterület középső blokkja felsorolja az összes olyan követelményt (a kiválasztott specifikációból), amely kapcsolódik a tesztesethez. Az alsó, 'Elérhető követelmények' blokk felsorolja az összes olyan követelményt, amely nem kapcsolódik az aktuális tesztesethez. A tervező megjelölheti azokat a követelményeket, amelyeket ez a teszteset lefed, majd a 'Hozzárendelés' gombra kattinthat. Ezek az újonnan hozzárendelt tesztesetek a középső, 'Hozzárendelt követelmények' blokkban jelennek meg.</li>
</ol>
<h2>Figyelmeztetés:</h2>
Egy zárolt követelmény nem módosítható a lefedettség frissítése érdekében. Ennek megfelelően a zárolt követelmények listázva vannak, de a hozzájuk tartozó jelölőnégyzetek le vannak tiltva.";


// --------------------------------------------------------------------------------------
$TLS_htmltext_title['editTc']    = "Tesztspecifikáció";
$TLS_htmltext['editTc']          = "<p>A <i>Tesztspecifikáció</i> lehetővé teszi a felhasználók számára az " .
        "összes meglévő <i>Tesztkészlet</i> és <i>Teszteset</i> megtekintését és szerkesztését. " .
        "A tesztesetek verziózva vannak, és az összes korábbi verzió elérhető, " .
        "megtekinthető és kezelhető itt.</p>
		
<h2>Első lépések:</h2>
<ol>
    <li>Válassza ki a <i>Tesztprojektet</i> a navigációs fában (a gyökércsomópontot). <i>Kérjük, vegye figyelembe: 
    A jobb felső sarokban található legördülő listából bármikor megváltoztathatja az aktív Tesztprojektet egy másik kiválasztásával.</i></li>
    <li>Hozzon létre egy új Tesztkészletet a <b>Létrehozás</b> gombra kattintva (Tesztkészlet műveletek). A Tesztkészletek 
    struktúrát adhatnak a tesztdokumentumainak az Ön konvenciói szerint (funkcionális/nem funkcionális 
    tesztek, termékkomponensek vagy funkciók, változtatási kérelmek stb.). Egy Tesztkészlet leírása 
    tartalmazhatja a benne lévő tesztesetek hatókörét, az alapértelmezett konfigurációt, 
    kapcsolódó dokumentumokra mutató linkeket, korlátozásokat és egyéb hasznos információkat. Általánosságban 
    minden olyan megjegyzést, amely közös a gyermek tesztesetekben. A Tesztkészletek a 
    &quot;mappa&quot; metaforát követik, így a felhasználók áthelyezhetik és másolhatják a Tesztkészleteket a 
    Tesztprojekten belül. Emellett importálhatók vagy exportálhatók is (a bennük lévő tesztesetekkel együtt).</li>
    <li>A Tesztkészletek skálázható mappák. A felhasználók áthelyezhetik vagy másolhatják a Tesztkészleteket a 
    Tesztprojekten belül. A Tesztkészletek importálhatók vagy exportálhatók (tesztesetekkel együtt).
    <li>Válassza ki az újonnan létrehozott Tesztkészletet a navigációs fában, és hozzon létre 
    egy új Tesztesetet a <b>Létrehozás</b> gombra kattintva (Teszteset műveletek). Egy Teszteset meghatároz 
    egy adott tesztelési forgatókönyvet, az elvárt eredményeket és a Tesztprojektben meghatározott 
    egyedi mezőket (további információkért olvassa el a használati útmutatót). Lehetőség van továbbá 
    <b>kulcsszavak</b> hozzárendelésére a jobb nyomonkövethetőség érdekében.</li>
    <li>Navigáljon a bal oldali fanézeten keresztül és szerkessze az adatokat. Minden teszteset tárolja a saját előzményeit.</li>
    <li>Rendelje hozzá a létrehozott Tesztspecifikációt egy <span class=\"help\" onclick=
    \"javascript:open_help_window('glossary','$locale');\">Teszttervhez</span>, amikor a tesztesetei készen állnak.</li>
</ol>

<p>A TestLink segítségével Tesztkészletekbe rendezheti a Teszteseteket." .
"A Tesztkészletek más tesztkészletekbe ágyazhatók, így Tesztkészlet-hierarchiákat hozhat létre. Ezt követően kinyomtathatja ezeket az információkat a Tesztesetekkel együtt.</p>";

// ------------------------------------------------------------------------------------------
$TLS_htmltext_title['searchTc']    = "Teszteset keresési oldal";
$TLS_htmltext['searchTc']          = "<h2>Cél:</h2>

<p>Navigáció kulcsszavak és/vagy keresett karakterláncok alapján. A keresés nem
kis- és nagybetű érzékeny. Az eredmények csak az aktuális Tesztprojektben szereplő teszteseteket tartalmazzák.</p>

<h2>Keresés:</h2>
<ol>
    <li>Írja be a keresett karakterláncot a megfelelő mezőbe. A nem használt mezőket hagyja üresen az űrlapon.</li>
    <li>Válassza ki a kívánt kulcsszót, vagy hagyja az értéket 'Nincs alkalmazva' állapotban.</li>
    <li>Kattintson a Keresés gombra.</li>
    <li>Az összes feltételnek megfelelő teszteset megjelenik. A Teszteseteket a 'Cím' hivatkozáson keresztül módosíthatja.</li>
</ol>";

/* contribution by asimon for 2976 */
// requirements search
// ------------------------------------------------------------------------------------------
$TLS_htmltext_title['searchReq']    = "Követelmény keresési oldal";
$TLS_htmltext['searchReq']          = "<h2>Cél:</h2>

<p>Navigáció kulcsszavak és/vagy keresett karakterláncok alapján. A keresés nem
kis- és nagybetű érzékeny. Az eredmények csak az aktuális Tesztprojektben szereplő követelményeket tartalmazzák.</p>

<h2>Keresés:</h2>

<ol>
    <li>Írja be a keresett karakterláncot a megfelelő mezőbe. A nem használt mezőket hagyja üresen az űrlapon.</li>
    <li>Válassza ki a kívánt kulcsszót, vagy hagyja az értéket 'Nincs alkalmazva' állapotban.</li>
    <li>Kattintson a 'Keresés' gombra.</li>
    <li>Az összes feltételnek megfelelő követelmény megjelenik. A követelményeket a 'Cím' hivatkozáson keresztül módosíthatja.</li>
</ol>

<h2>Megjegyzés:</h2>

<p>- Csak az aktuális projekten belüli követelmények között keres.<br>
- A keresés nem érzékeny a kis- és nagybetűkre.<br>
- Az üres mezőket figyelmen kívül hagyja.</p>";

// requirement specification search
// ------------------------------------------------------------------------------------------
$TLS_htmltext_title['searchReqSpec']    = "Követelményspecifikáció keresési oldal";
$TLS_htmltext['searchReqSpec']          = "<h2>Cél:</h2>

<p>Navigáció kulcsszavak és/vagy keresett karakterláncok alapján. A keresés nem
kis- és nagybetű érzékeny. Az eredmények csak az aktuális Tesztprojektben szereplő követelményspecifikációkat tartalmazzák.</p>

<h2>Keresés:</h2>

<ol>
    <li>Írja be a keresett karakterláncot a megfelelő mezőbe. A nem használt mezőket hagyja üresen az űrlapon.</li>
    <li>Válassza ki a kívánt kulcsszót, vagy hagyja az értéket 'Nincs alkalmazva' állapotban.</li>
    <li>Kattintson a 'Keresés' gombra.</li>
    <li>Az összes feltételnek megfelelő követelmény megjelenik. A követelményspecifikációkat a 'Cím' hivatkozáson keresztül módosíthatja.</li>
</ol>

<h2>Megjegyzés:</h2>

<p>- Csak az aktuális projekten belüli követelményspecifikációk között keres.<br>
- A keresés nem érzékeny a kis- és nagybetűkre.<br>
- Az üres mezőket figyelmen kívül hagyja.</p>";
/* end contribution */


// ------------------------------------------------------------------------------------------
$TLS_htmltext_title['printTestSpec']    = "Tesztspecifikáció nyomtatása"; //printTC.html
$TLS_htmltext['printTestSpec']             = "<h2>Cél:</h2>
<p>Innen kinyomtathat egyetlen tesztesetet, egy tesztkészlet összes tesztesetét,
vagy egy tesztprojekt vagy tesztterv összes tesztesetét.</p>
<h2>Első lépések:</h2>
<ol>
<li>
<p>Válassza ki a tesztesetek megjeleníteni kívánt részeit, majd kattintson egy tesztesetre, 
tesztkészletre vagy a tesztprojektre. Egy nyomtatható oldal fog megjelenni.</p>
</li>
<li><p>Használja a navigációs panelen található \"Megjelenítés mint\" legördülő menüt annak meghatározásához, hogy 
az információkat HTML, OpenOffice Writer vagy Microsoft Word dokumentumként kívánja-e megjeleníteni. 
További információért lásd a <span class=\"help\" onclick=\"javascript:open_help_window('printFilter',
'{$locale}');\">súgót</span>.</p>
</li>
<li><p>Használja a böngésző nyomtatási funkcióját az információk tényleges kinyomtatásához.<br />
<i>Megjegyzés: Ügyeljen arra, hogy csak a jobb oldali keretet nyomtassa ki.</i></p>
</li>
</ol>";


// ------------------------------------------------------------------------------------------
$TLS_htmltext_title['reqSpecMgmt']    = "Követelményspecifikáció tervezése"; //printTC.html
$TLS_htmltext['reqSpecMgmt']             = "<p>Itt kezelheti a követelményspecifikációs dokumentumokat.</p>

<h2>Követelményspecifikáció</h2>

<p>A követelmények <b>követelményspecifikációs dokumentumokba</b> vannak csoportosítva, amelyek a 
Tesztprojekthez kapcsolódnak.<br /> A TestLink (még) nem támogatja a verziókezelést sem a követelményspecifikációk, 
sem maguk a követelmények esetében. Ezért a dokumentum verzióját a specifikáció <b>Cím</b> mezője után célszerű hozzáadni. 
A felhasználó egyszerű leírást vagy megjegyzéseket fűzhet a <b>Hatókör (Scope)</b> mezőhöz.</p>

<p>A <b><a name='total_count'>Követelmények felülírt száma</a></b> a 
követelmény-lefedettség értékelésére szolgál abban az esetben, ha nem minden követelményt vittek fel a TestLinkbe. 
A <b>0</b> érték azt jelenti, hogy a metrikákhoz a követelmények aktuális számát kell használni.</p>
<p><i>Példa: Az SRS (szoftverkövetelmény-specifikáció) 200 követelményt tartalmaz, de csak 50 van felvéve a TestLinkbe. 
A tesztlefedettség így 25% (feltételezve, hogy az 50 felvett követelmény ténylegesen tesztelve lesz).</i></p>

<h2><a name='req'>Követelmények</a></h2>

<p>Kattintson egy meglévő követelményspecifikáció címére. Ha még nem létezik ilyen, 
kattintson a projekt csomópontjára egy új létrehozásához. Létrehozhat, szerkeszthet, törölhet 
vagy importálhat követelményeket a dokumentumhoz. Minden követelmény rendelkezik címmel, hatókörrel és állapottal. 
Az állapot lehet 'Normál' vagy 'Nem tesztelhető'. A nem tesztelhető követelmények nem számítanak bele 
a metrikákba. Ezt a paramétert mind a még meg nem valósított funkciók, mind a 
hibásan tervezett követelmények esetén használni kell.</p>

<p>A specifikációs képernyőn a követelmények kijelölésével és a tömeges műveletek (multi action) használatával új teszteseteket hozhat létre a követelményekhez. Ezek a tesztesetek abba a tesztkészletbe kerülnek, 
amelynek neve a konfigurációban van meghatározva <i>(alapértelmezett: \$tlCfg->req_cfg->default_testsuite_name = 
'Test suite created by Requirement - Auto';)</i>. A Cím és a Hatókör mezők átmásolódnak ezekbe a tesztesetekbe.</p>";


// ------------------------------------------------------------------------------------------
$TLS_htmltext_title['printReqSpec'] = "Követelményspecifikáció nyomtatása"; //printReq
$TLS_htmltext['printReqSpec'] = "<h2>Cél:</h2>
<p>Dokumentumot generálhat egy adott követelményspecifikációban szereplő követelményekről, 
vagy a tesztprojektben található összes követelményről.</p>
<h2>Első lépések:</h2>
<ol>
<li>
<p>Válassza ki a követelmények azon részeit, amelyeket meg szeretne jeleníteni, majd kattintson egy 
követelményspecifikációra vagy a tesztprojektre. Egy nyomtatható oldal fog megjelenni.</p>
</li>
<li><p>Használja a navigációs panelen található \"Megjelenítés mint\" legördülő menüt annak meghatározásához, hogy 
az információkat HTML vagy pszeudo-Microsoft Word dokumentumként kívánja-e megjeleníteni. 
További információért lásd a <span class=\"help\" onclick=\"javascript:open_help_window('printFilter',
'{$locale}');\">súgót</span>.</p>
</li>
<li><p>Használja a böngésző nyomtatási funkcióját az információk tényleges kinyomtatásához.<br />
<i>Megjegyzés: Ügyeljen arra, hogy csak a jobb oldali keretet nyomtassa ki.</i></p>
</li>
</ol>";


// ------------------------------------------------------------------------------------------
$TLS_htmltext_title['keywordsAssign']    = "Kulcsszó hozzárendelése";
$TLS_htmltext['keywordsAssign']             = "<h2>Cél:</h2>
<p>A Kulcsszó hozzárendelése oldalon a felhasználók kötegelten (tömegesen) 
rendelhetnek kulcsszavakat a meglévő tesztkészletekhez vagy tesztesetekhez.</p>

<h2>Kulcsszavak hozzárendelése:</h2>
<ol>
    <li>Válasszon ki egy tesztkészletet vagy tesztesetet a bal oldali 
        fanézetben.</li>
    <li>A jobb oldalon megjelenő legfelső mező lehetővé teszi, hogy a rendelkezésre álló 
        kulcsszavakat minden egyes tesztesethez 
        hozzárendelje.</li>
    <li>Az alatta lévő választási lehetőségek lehetővé teszik a hozzárendelést 
        részletesebb (granulárisabb) szinten is.</li>
</ol>

<h2>Fontos információk a teszttervekben szereplő kulcsszó-hozzárendelésekről:</h2>
<p>A specifikációban elvégzett kulcsszó-hozzárendelések csak akkor lesznek hatással a teszttervekben 
szereplő tesztesetekre, ha a tesztterv a teszteset legfrissebb verzióját tartalmazza. 
Ellenkező esetben, ha egy tesztterv egy teszteset régebbi verzióit tartalmazza, a most elvégzett 
módosítások NEM fognak megjelenni a teszttervben.
</p>
<p>A TestLink ezt a megközelítést alkalmazza annak érdekében, hogy a teszttervekben szereplő korábbi verziójú 
teszteseteket ne befolyásolják a legfrissebb verzióhoz rendelt kulcsszavak. Ha azt szeretné, hogy a 
teszttervben szereplő tesztesetek frissüljenek, először ellenőrizze, hogy naprakészek-e a 'Módosított 
tesztesetek frissítése' funkcióval, MIELŐTT elvégezné a kulcsszavak hozzárendelését.</p>";


// ------------------------------------------------------------------------------------------
$TLS_htmltext_title['executeTest']    = "Teszteset végrehajtása";
$TLS_htmltext['executeTest']         = "<h2>Cél:</h2>

<p>Lehetővé teszi a felhasználó számára a tesztesetek végrehajtását. A felhasználó teszteredményt 
rendelhet egy adott Teszttervben szereplő Tesztesethez egy konkrét Buildre vonatkozóan. A szűrőkről 
és beállításokról további információt a súgóban talál (kattintson a kérdőjel ikonra).</p>

<h2>Első lépések:</h2>

<ol>
    <li>A felhasználónak meg kell határoznia egy Buildet a Teszttervhez.</li>
    <li>Válasszon ki egy Buildet a legördülő listából.</li>
    <li>Ha a teljes fa helyett csak néhány tesztesetet szeretne látni, 
        kiválaszthatja a kívánt szűrőket. A szűrők módosítása után 
        kattintson az \"Alkalmaz\" gombra.</li>    
    <li>Kattintson egy tesztesetre a fanézetben.</li>
    <li>Töltse ki a teszteset eredményét, valamint a kapcsolódó megjegyzéseket vagy hibákat.</li>
    <li>Mentse az eredményeket.</li>
</ol>
<p><i>Megjegyzés: A TestLinket konfigurálni kell a hibakövető rendszerrel való együttműködéshez, 
ha közvetlenül a felületről szeretne hibajelentést létrehozni vagy nyomon követni.</i></p>";

// ------------------------------------------------------------------------------------------
$TLS_htmltext_title['showMetrics']    = "Tesztjelentések és metrikák leírása";
$TLS_htmltext['showMetrics']         = "<p>A jelentések egy Teszttervhez kapcsolódnak " .
        "(a navigációs panel tetején van meghatározva). Ez a Tesztterv eltérhet a 
végrehajtáshoz használt aktuális Teszttervtől. Kiválaszthatja a jelentés formátumát is:</p>
<ul>
<li><b>HTML</b> - a jelentés weboldalként jelenik meg</li>
<li><b>Pszeudo MS Word</b> - a jelentés Microsoft Wordbe importálható</li>
<li><b>Email (HTML)</b> - a jelentést a rendszer elküldi a felhasználó e-mail címére</li>
</ul>

<p>A nyomtatás gomb csak a jelentés nyomtatását aktiválja (navigáció nélkül).</p>
<p>Számos különálló jelentés közül választhat, ezek célját és funkcióját az alábbiakban ismertetjük.</p>

<h3>Tesztterv</h3>
<p>A 'Tesztterv' dokumentumnál megadhatók a tartalomra és a dokumentum szerkezetére vonatkozó beállítások.</p>

<h3>Tesztjelentés</h3>
<p>A 'Tesztjelentés' dokumentumnál megadhatók a tartalomra és a dokumentum szerkezetére vonatkozó beállítások. 
Tartalmazza a teszteseteket a teszteredményekkel együtt.</p>

<h3>Általános tesztterv metrikák</h3>
<p>Ez az oldal egy tesztterv legfrissebb állapotát mutatja tesztkészlet, felelős és kulcsszó szerint. 
A 'legfrissebb állapotot' az a legutóbbi build határozza meg, amelyen a teszteseteket végrehajtották. 
Például, ha egy tesztesetet több builden is végrehajtottak, csak a legutóbbi eredményt veszi figyelembe a rendszer.</p>

<p>A 'Legutóbbi teszteredmény' egy több jelentésben is használt fogalom, meghatározása a következő:</p>
<ul>
<li>A buildek teszttervhez adásának sorrendje határozza meg, melyik build a legfrissebb. A legfrissebb 
build eredményei élveznek elsőbbséget a régebbi buildekkel szemben. Például, ha egy tesztet 
'sikertelennek' jelöl az 1. buildben, majd 'sikeresnek' a 2. buildben, a legutóbbi eredménye 'sikeres' lesz.</li>
<li>Ha egy tesztesetet többször hajtanak végre ugyanazon a builden, a legutóbbi végrehajtás élvez 
elsőbbséget. Például, ha a 3. buildet kiadják a csapatnak, és az 1. tesztelő délután 2-kor 'sikeresnek', 
a 2. tesztelő pedig délután 3-kor 'sikertelennek' jelöli – akkor 'sikertelenként' fog megjelenni.</li>
<li>A buildhez 'nem futtatottként' listázott teszteseteket a rendszer nem veszi figyelembe. Például, ha egy 
esetet 'sikeresnek' jelöl az 1. buildben, de nem hajtja végre a 2. buildben, a legutóbbi eredménye 
'sikeres' marad.</li>
</ul>
<p>A következő táblázatok jelennek meg:</p>
<ul>
    <li><b>Eredmények a legfelső szintű tesztkészletek szerint</b>
    Felsorolja az egyes legfelső szintű készletek eredményeit. Megjeleníti az összes esetet, a sikeres, sikertelen, 
    blokkolt, nem futtatott esetek számát és a befejezettség százalékát. Egy teszteset akkor tekinthető 'befejezettnek', 
    ha sikeres, sikertelen vagy blokkolt jelölést kapott. A legfelső szintű készletek eredményei tartalmazzák 
    az összes alatta lévő (gyermek) készletet is.</li>
    <li><b>Eredmények kulcsszó szerint</b>
    Felsorolja az aktuális teszttervben az esetekhez rendelt összes kulcsszót és a hozzájuk kapcsolódó eredményeket.</li>
    <li><b>Eredmények felelős szerint</b>
    Felsorolja az összes felelőst, akihez tesztesetek vannak rendelve az aktuális teszttervben. A hozzá nem rendelt 
    tesztesetek a 'nincs felelős' (unassigned) fejléc alatt összesítve jelennek meg.</li>
</ul>

<h3>Build összesített állapota</h3>
<p>Felsorolja a végrehajtási eredményeket minden egyes buildre vonatkozóan. Minden buildnél megjeleníti: 
összes teszteset, összes sikeres, sikeres %, összes sikertelen, sikertelen %, blokkolt, blokkolt %, nem futtatott, nem futtatott %. 
Ha egy tesztesetet kétszer hajtottak végre ugyanazon a builden, a legutóbbi végrehajtás kerül figyelembevételre.</p>


<h3>Blokkolt, sikertelen és nem futtatott tesztesetek jelentései</h3>
<p>Ezek a jelentések az összes jelenleg blokkolt, sikertelen vagy nem futtatott tesztesetet mutatják. 
A 'Legutóbbi teszteredmény' logikája (amelyet fentebb, az Általános tesztterv metrikáknál ismertettünk) 
szolgál annak meghatározására, hogy egy teszteset blokkoltnak, sikertelennek vagy nem futtatottnak minősül-e. 
A blokkolt és sikertelen tesztesetek jelentései megjelenítik a kapcsolódó hibákat is, amennyiben a felhasználó 
integrált hibakövető rendszert használ.</p>

<h3>Tesztjelentés</h3>
<p>Megtekintheti minden egyes teszteset állapotát minden egyes builden. Ha egy tesztesetet többször is 
végrehajtottak ugyanazon a builden, a legutóbbi eredmény lesz látható. Nagy adatállomány esetén javasolt 
ezt a jelentést Excel formátumba exportálni a könnyebb böngészés érdekében.</p>

<h3>Grafikonok - Általános tesztterv metrikák</h3>
<p>A 'Legutóbbi teszteredmény' logikája érvényes mind a négy látható grafikonra. A grafikonok animáltak, 
hogy segítsék a felhasználót az aktuális tesztterv metrikáinak vizualizálásában. A négy elérhető grafikon:</p>
<ul><li>Kördiagram az összesített sikeres / sikertelen / blokkolt / és nem futtatott tesztesetekről</li>
<li>Oszlopdiagram az eredményekről kulcsszavak szerint</li>
<li>Oszlopdiagram az eredményekről felelősök szerint</li>
<li>Oszlopdiagram az eredményekről a legfelső szintű készletek szerint</li>
</ul>
<p>Az oszlopdiagramok színei segítenek a felhasználónak beazonosítani a sikeres, sikertelen, blokkolt 
és nem futtatott esetek hozzávetőleges számát.</p>

<h3>Összes hiba tesztesetenként</h3>
<p>Ez a jelentés minden tesztesetet megjelenít a hozzá bejelentett összes hibával együtt a teljes projektre 
vonatkozóan. Ez a jelentés csak akkor érhető el, ha van csatlakoztatva hibakövető rendszer.</p>";


// ------------------------------------------------------------------------------------------
$TLS_htmltext_title['planAddTC']    = "Tesztesetek hozzáadása / eltávolítása a Teszttervhez"; // testSetAdd
$TLS_htmltext['planAddTC']             = "<h2>Cél:</h2>
<p>Lehetővé teszi a felhasználó (tesztvezetői jogosultsággal) számára, hogy teszteseteket adjon hozzá a Teszttervhez, vagy távolítson el onnan.</p>

<h2>Tesztesetek hozzáadása vagy eltávolítása:</h2>
<ol>
    <li>Kattintson egy tesztkészletre az összes hozzá tartozó al-tesztkészlet és teszteset megtekintéséhez.</li>
    <li>Ha végzett, kattintson a 'Tesztesetek hozzáadása / eltávolítása' gombra a művelet végrehajtásához.
        Megjegyzés: Ugyanazt a tesztesetet nem lehet többször hozzáadni.</li>
</ol>";

// ------------------------------------------------------------------------------------------
$TLS_htmltext_title['tc_exec_assignment']    = "Tesztek végrehajtásának hozzárendelése tesztelőkhöz";
$TLS_htmltext['tc_exec_assignment']         = "<h2>Cél</h2>
<p>Ez az oldal lehetővé teszi a tesztvezetők számára, hogy felhasználókat rendeljenek hozzá a Teszttervben szereplő konkrét tesztekhez.</p>

<h2>Első lépések</h2>
<ol>
    <li>Válasszon ki egy tesztelendő tesztesetet vagy tesztkészletet.</li>
    <li>Válassza ki a tervezett tesztelőt.</li>
    <li>Kattintson a 'Mentés' gombra a hozzárendelés beküldéséhez.</li>
    <li>Nyissa meg a végrehajtási (execution) oldalt a hozzárendelés ellenőrzéséhez. Itt beállíthat szűrőt a felhasználókra vonatkozóan.</li>
</ol>";

// ------------------------------------------------------------------------------------------
$TLS_htmltext_title['planUpdateTC']    = "Tesztesetek frissítése a teszttervben";
$TLS_htmltext['planUpdateTC']         = "<h2>Cél</h2>
<p>Ez az oldal lehetővé teszi egy teszteset frissítését egy újabb (eltérő) verzióra, ha a 
tesztspecifikáció megváltozik. Gyakran előfordul, hogy egyes funkciók a tesztelés során tisztázódnak." .
        " A felhasználó módosítja a tesztspecifikációt, de a változtatásokat a teszttervben is át kell vezetni. 
Ellenkező esetben a tesztterv az eredeti verziót őrzi meg, biztosítva, hogy az eredmények a teszteset 
megfelelő szövegére hivatkozzanak.</p>

<h2>Első lépések</h2>
<ol>
    <li>Válasszon ki egy tesztelendő tesztesetet vagy tesztkészletet.</li>
    <li>Válasszon ki egy új verziót a legördülő menüből az adott tesztesethez.</li>
    <li>Kattintson a 'Tesztterv frissítése' gombra a módosítások beküldéséhez.</li>
    <li>Ellenőrzés: Nyissa meg a végrehajtási oldalt a teszteset(ek) szövegének megtekintéséhez.</li>
</ol>";


// ------------------------------------------------------------------------------------------
$TLS_htmltext_title['test_urgency']    = "Teszt sürgősségének meghatározása";
$TLS_htmltext['test_urgency']         = "<h2>Cél</h2>
<p>A TestLink lehetővé teszi a tesztkészletek sürgősségének beállítását, amely befolyásolja a tesztesetek 
tesztelési prioritását. A prioritás a tesztesetek fontosságától és a teszttervben meghatározott 
sürgősségtől egyaránt függ. A tesztvezetőnek érdemes meghatároznia azon tesztesetek körét, amelyeket 
legelőször kell tesztelni. Ez segít biztosítani, hogy a tesztelés a legfontosabb tesztekre kiterjedjen 
időnyomás alatt is.</p>

<h2>Első lépések</h2>
<ol>
    <li>Válasszon ki egy tesztkészletet a bal oldali navigációs sávban egy termékfunkció vagy komponens 
    sürgősségének beállításához.</li>
    <li>Válasszon sürgősségi szintet (magas, közepes vagy alacsony). Alapértelmezett a közepes. 
    Csökkentheti a prioritást a termék érintetlen részeinél, és növelheti a jelentős változásokon 
    átesett komponenseknél.</li>
    <li>Kattintson a 'Mentés' gombra a módosítások beküldéséhez.</li>
</ol>
<p><i>Például egy magas fontosságú teszteset egy alacsony sürgősségű tesztkészletben 
közepes prioritást fog kapni.</i>";


// ------------------------------------------------------------------------------------------

?>
