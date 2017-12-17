<?php
/**
 * ♔ TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later.
 *
 * Filename $RCSfile: texts.php,v $
 * @version $Revision: 1.5 $
 * @modified $Date: 2010/06/24 17:25:52 $ by $Author: asimon83 $
 * @author Martin Havlat
 * @author Pavel Kalian
 *
 *
 * Scope:
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
 * ------------------------------------------------------------------------------------ */

//If this file is coded in UTF-8 then enable the following line
$TLS_STRINGFILE_CHARSET = "UTF-8";

$TLS_htmltext_title['assignReqs']	= "Přiřazení požadavků k testovacím případům";
$TLS_htmltext['assignReqs'] 		= "<h2>Účel</h2>
<p>Uživatel může nastavit přiřazení mezi požadavky a testovacími případy. Přiřazení může být definováno jako vazba 0..n až 0..n. tzn. Jeden požadavek může být přiřazen k žádnému, jednomu, popřípadě k více testovacím případům a naopak. Tato přiřazení pomáhají zjistit pokrytí požadavků testovacími případy 
a k určení testovacích případů, jejichž provádění nebylo dokončeno z důvodu chyby. 
Tyto informace pak mohou sloužit jako podklad pro další plánování testů.</p>

<h2>Jak na to</h2>
<ol>
	<li>Vyberte testovací případ ze stromové struktury v navigačním panelu na levé straně. Rozbalovací seznam s dokumenty specifikace 
  požadavků bude zobrazen v horní časti pracovní plochy</li>
	<li>V případě, že je dostupno více dokumentů se specifikacemi požadavků, zvolte jeden z nich. 
	TestLink automaticky obnoví obsah stránky.</li>
	<li>Ve střední části pracovní plochy se zobrazí všechny požadavky (ze zvoleného dokumentu se specifikacemi požadavků), které
	jsou již k testovacímu případu přiřazeny. Ve spodní části 'Dostupné požadavky' se zobrazí všechny
	požadavky, které k testovacímu případu 
	přiřazeny nejsou. Uživatel může označit požadavky, které se vztahují ke zvolenému testovacímu případu 
	a stisknout tlačítko 'Přiřadit'. Označené požadavky se poté zobrazí ve střední časti pracovní plochy 'Přiřazené požadavky'.</li>
</ol>";


// --------------------------------------------------------------------------------------
$TLS_htmltext_title['editTc']	= "Specifikace testů";
$TLS_htmltext['editTc'] 		= "<h2>Účel</h2>
<p><i>Specifikace testovacích případů</i> umožňuje uživatelům zobrazení a úpravu všech existujících " .
		"<i>testovacích sad</i> a <i>testovacích případů</i>. Testovací případy jsou verzovány a všechny " .
		"předcházející verze jsou zde dostupné. Je možné je zobrazit a dále s nimi pracovat.</p>

<h2>Jak na to</h2>
<ol>
	<li>Zvolte testovací projekt ze stromové struktury v navigačním panelu na levé straně (hlavní uzel stromové struktury). <i>Poznámka: " .
	"Změnu aktivního testovacího projektu můžete kdykoliv provést pomocí rozbalovacího " .
	"seznamu v pravém horním rohu.</i></li>
	<li>Kliknutím na tlačítko <b>Nová navázaná testovací sada</b> vytvořte novou testovací sadu. Testovací sady Vám pomohou" .
	"vytvořit strukturu vašich testovacích dokumentů dle Vašich zvyklostí (funkční/nefunkční " .
	"testy, komponenty produktu nebo funkčnosti, změnové požadavky, apod.). Popis " .
	"testovací sady by měl obsahovat popis obsahu vložených testovacích případů, základní nastavení, " .
	"odkazy na související dokumentaci, popis omezení popřípadě dalších užitečných informací v rámci testovací sady. Obecně definováno, " .
	"všechny poznámky, které se vztahují k testovacím případům přiřazených do popisovaných testovacích sad. Testovací sady vycházejí z " .
	"filozofie adresářů. V rámci aktuálního testovacího projektu tak mohou mezi nimi uživatelé přesunovat nebo kopírovat testovací případy, popřípadě samotné testovací sady. " .
	". Testovací sady mohou být také importovány, nebo exportovány (včetně testovacích případů, které obsahují).</li> " .
	"<li>V panelu navigace zvolte ve stromové struktuře vytvořenou testovací sadu a stisknutím " .
	"tlačítka <b>Vytvořit testovací případ</b> vytvořte nový testovací případ. Testovací případ popisuje " .
	"scénář testu, očekávané výsledky a obsah uživatelsky definovaných polí " .
	" (další informace jsou dostupné v uživatelském manuálu). Pro zvýšení přehledu  " .
	"je možné přiřadit k testovacímu případu <b>klíčová slova</b>.</li>
	<li>Testovací případy můžete procházet a editovat za pomoci stromové struktury navigačního panelu na levé straně obrazovky. Pro každý testovací případ 
  se ukládá jeho historie.</li>
	<li>Po dokončení definice testovacích případů, přiřaďte testovací sadu do požadovaného <span class=\"help\" onclick=
	\"javascript:open_help_window('glosary','$locale');\">testovacího plánu</span>.</li>
</ol>

<p>V Testlinku jsou testovací případy organizovány za pomoci testovacích sad." .
"Testovací sady mohou být vnořené v jiných testovacích sadách a je tak umožněno vytvářet si strukturu testovacích sad.
 Informace o struktuře testovacich sad mohou být vytištěny společně s testovacími případy.</p>";


// ------------------------------------------------------------------------------------------
$TLS_htmltext_title['searchTc']	= "Vyhledávání testovacích případů";
$TLS_htmltext['searchTc'] 		= "<h2>Účel</h2>

<p>Vyhledání testovacích případů v závislosti na klíčových slovech a/nebo zadaných textových řetězců. Vyhledávání nerozlišuje velká/malá písmena. Výsledky vyhledávání budou obsahovat pouze testovací případy z aktuálního testovacího projektu.</p>

<h2>Jak vyhledávat</h2>

<ol>
	<li>Textový řetězec podle kterého chcete vyhledávat vložte do patřičného pole. Pole, které nechcete pro vyhledávání použít nechte prázdné.</li>
	<li>Zvolte požadované klíčové slovo, popřípadě hodnotu 'Nepoužito', pokud nechcete podle klíčového slova vyhledávat.</li>
	<li>Stiskněte tlačítko 'Vyhledat'.</li>
	<li>Všechny testovací případy, které odpovídají parametrům vyhledávání budou zobrazeny. Testovací případy mohou být následně editovány, pokud využijete odkazu v názvu testovacího případu.</li>
</ol>";


// ------------------------------------------------------------------------------------------
$TLS_htmltext_title['printTestSpec']	= "Vytištění specifikace testů"; //printTC.html
$TLS_htmltext['printTestSpec'] 			= "<h2>Účel</h2>
<p>Uživatel zde může vytisknout jednotlivé testovací případy, všechny testovací případy v testovací sadě, nebo všechny testovací případy v projektu.</p>
<h2>Jak na to</h2>
<ol>
<li>
<p>Zvolte, které informace chcete z testovacího případu zobrazit, a vyberte testovací případ, testovací sadu, popřípadě testovací projekt. Zobrazí se Vám stránka formátovaná pro přímý tisk.</p>
</li>
<li><p>Použijte rozbalovací seznam \"Zobrazit jako\" v panelu navigace pro zvolení formátu (HTML nebo dokument formátu Microsoft Word), ve kterém budou informace zobrazeny. Pro více informací použijte <span class=\"help\" onclick=\"javascript:open_help_window('printFilter',
'{$locale}');\">nápovědu</span>.</p>
</li>
<li><p>Pro vytisknutí zobrazených informací využijte možnosti tisku vašeho prohlížeče.<br />
 <i>Upozornění: Ujistěte se, že jste pro tisk zvolili pravý rámec HTML stránky.</i></p></li>
</ol>";


// ------------------------------------------------------------------------------------------
$TLS_htmltext_title['reqSpecMgmt']	= "Návrh specifikace požadavků"; //printTC.html
$TLS_htmltext['reqSpecMgmt'] 			= "<p>V této sekci můžete spravovat dokumenty specifikací požadavků.</p>

<h2>Specifikace požadavků</h2>

<p>Požadavky jsou združené v rámci <b>dokumentu specifikace požadavků</b>, které se vztahují k
zvolenému testovacímu projektu.<br /> TestLink aktuálně nepodporuje verzování pro dokument specifikace požadavků ani pro požadavky
samotné. Verze dokumentu specifikace požadavků by proto měla být uvedena v <b>názvu</b> dokumentu, nebo <b>názvu</b> požadavku.
Uživatel může také přidat jednoduchý popis, popřípadě poznámku do definice <b>obsahu</b>.</p>

<p><b><a name='total_count'>Počet importovaných požadavků</a></b> slouží pro vyhodnocení pokrytí požadavků v případě, že některé z požadavků nebyly přidány (importovány).
Hodnota <b>0</b> vyjadřuje aktuální počet požadavků zahrnutých do metrik.</p>
<p><i>Například SRS obsahuje 200 požadavků, ale pouze 50 jich je přidáno do testlinku. Pouze 25% požadavků pak může být pokryto testy (za předpokladu, že budou všechny přidané požadavky zahrnuty do testování).</i></p>

<h2><a name='req'>Požadavky</a></h2>

<p>Klikněte na jméno dokumentu specifikace požadavků. Pokud zatím nebyl dokument specifikace vytvořen, klikněte na projekt ve stromové struktuře navigačního panelu, 
a dokument specifikace požadavků vytvořte. Pro dokument pak můžete vytvářet, upravovat, mazat, nebo importovat požadavky. Každý požadavek má jméno, specifikaci a stav.
Stav může být nastaven na \"Normalní\" nebo \"Netestovatelné\". Netestovatelné požadavky nejsou zahrnuty do výpočtu metrik. Tento parametr může být použit pro neimplementované prvky i pro špatně specifikované požadavky.</p>

<p>Můžete vytvořit nové testovací případy přímo na stránce specifikace požadavků pro vybrané požadavky použitím automatické funkce. Testovací případy budou vytvořeny v testovací sadě, jejíž jméno je definováno konfigurací Testlinku <i>(standartní nastavení je: &#36;tlCfg->req_cfg->default_testsuite_name = 
'Test suite created by Requirement - Auto';)</i>. Titulek a obsah jsou zkopírovány do těchto testovacích případů.</p>";


// ------------------------------------------------------------------------------------------
$TLS_htmltext_title['keywordsAssign']	= "Přiřazení klíčových slov";
$TLS_htmltext['keywordsAssign'] 			= "<h2>Účel</h2>
<p>Na stránce přiřazení klíčových slov si mohou uživatelé dávkově přiřadit k existující 
testovací sadě nebo testovacímu případu klíčové slova</p>

<h2>Jak na to</h2>
<ol>
	<li>Vyberte testovací sadu, nebo testovací případ ze stromové struktury navigačního panelu.</li>
	<li>Ze seznamu, které se objeví na pravé straně můžete přířadit dostupná klíčová slova k jednotlivým testovacím případům, testovací sadě nebo projektu.</li>
	<li>Stisknutím tlačítka \"Uložit\" potvrdíte změnu v přiřazení klíčových slov.</li>
</ol>

<h2>Důležité informace ohledně přiřazení kličových slov k testovacímu plánu</h2>
<p>Přiřazená klíčová slova k definici testovacích případů se projeví v testovacích plánech pouze a jen v případě, že testovací plán obsahuje
poslední verzi testovacích případů. Pokud testovací plán obsahuje starší verze testovacích případů, přiřazení klíčových slov se v něm NEPROJEVÍ.</p>
<p>TestLink používá tento postup k tomu, aby starší verze testovacích případů v testovacím plánu nebyly dotčeny přiřazením, které provedete nad 
jejich novějšími verzemi. Pokud budete chtít provést aktualizaci testovacích případů ve vašem testovacím plánu, 
proveďte PŘED přiřazením klíčových slov nejdříve kontrolu testovacích případů za 
pomoci funkce 'Aktualizovat upravené testovací případy'.</p>";


// ------------------------------------------------------------------------------------------
$TLS_htmltext_title['executeTest']	= "Provedení testovacího případu";
$TLS_htmltext['executeTest'] 		= "<h2>Účel</h2>

<p>Umožňuje uživatelům provádět testovací případy. Uživatel může v rámci aktuálního sestavení nastavit 
výsledek testu. Pro více informací o nastavení a možnostech filtrovaní se podívejte do nápovědy " .
		"(klikněte na ikonu otazníku).</p>

<h2>Jak na to</h2>

<ol>
	<li>Uživatel musí mít pro aktuální testovací plán definováno sestavení.</li>
	<li>V navigačním panelu vyberte sestaveni z rozbalovacího menu a stiskněte tlačítko \"Použít\".</li>
	<li>Ve stromovém menu klikněte na konkrétní testovací případ.</li>
	<li>Vyplňte výsledky pro testovací případ společně s jakýmikoliv poznámkami, nebo nalezenými chybami.</li>
	<li>Uložte výsledky.</li>
</ol>
<p><i>Poznámka: TestLink musí být nastaven na spolupráci s vaším nástrojem pro správu chyb, 
pokud chcete vytvořit nebo sledovat stav chyby přímo z prostředí Testlinku.</i></p>";

// ------------------------------------------------------------------------------------------
$TLS_htmltext_title['showMetrics']	= "Popis testovacích přehledů a metrik";
$TLS_htmltext['showMetrics'] 		= "<p>Přehledy se vztahují k testovacímu projektu " .
		"(nastavenému v horní části navigačního panelu). Testovací projekt nastavený pro generování přehledů se může lišit
od aktuálně zvoleného testovacího plánu pro provádění testů. Pro přehled můžete zvolit formát ve které bude vygenerován:</p>
<ul>
<li><b>Normal</b> - přehled je zobrazen v prohlížeči</li>
<li><b>MS Excel</b> - přehled bude exportován do souboru Micosoft Excel</li>
<li><b>HTML Email</b> - přehled bude odeslán na emailovou adresu uživatele</li>
<li><b>Grafy</b> - přehled bude obsahovat grafy (za pomoci flash technologie)</li>
</ul>

<p>Tlačítko vytisknout provede pouze vytištění přehledu (bez navigace).</p>
<p>Lze zvolit z několik samostatných přehledů, jejichž význam a funkce bude dále popsána.</p>

<h3>Celkový přehled testovacího plánu</h3>
<p>Na této stránce se zobrazí nejaktuálnější stav testovacího plánu s ohledem na testovací sadu, vlastníka testovacích případů a jejich prioritu.
'Nejaktuálnější status' je stanoven na základě posledního provedení testovacího případu v aktuálním sestavení. V případě
, že byl testovací případ proveden v rámci více testovacích sad, použije se výsledek posledního provedení.</p>

<p>'Poslední výsledek testu' je výraz použitý v mnoha přehledech, jeho význam je následující:</p>
<ul>
<li>Podle pořadí, ve kterém jsou sestavení přidána do testovacího plánu, se stanoví které sestavení je poslední aktuální. Výsledky 
z posledního aktuálního sestavení budou nadřazené nad výsledky starších sestavení. Například, pokud označíte testovací případ jako
'Neúspěšný' v sestavení 1, a dále ho označíte jako 'úspěšný' v sestavení 2, bude posledním výsledkem 'úspěšný'.</li>
<li>Pokud je testovací případ proveden v rámci jednoho sestavení vícekrát, poslední aktuální provedení bude
nadřazeno všem ostatním provedením v sestavení.  Například, pokud je sestavení 3 použito ve vašem týmu, tester 1 označí testovací případ jako 
'úspěšný' ve dvě hodiny a tester 2 ho označí jako 'neúspěšný' ve tři hodiny, bude zobrazen jako 'neúspěšný'.</li>
<li>Testovací případy zobrazené jako 'nespuštěné' v rámci sestavení se neberou v úvahu. Například, pokud označíte
testovací případ jako 'úspěšný' v sestavení 1, nebudete ho provádět v sestavení 2, bude jeho poslední výsledek označen jako 'úspěšný'.</li>
</ul>
<p>Následující tabulka bude zobrazena:</p>
<ul>
	<li><b>Výsledky hlavních testovacích sad</b>
	Zobrazí výsledky pro každou z hlavních testovacích sad. Zobrazené informace zahrnují celkový počet testovacích případů, počet úspěšných, neúspěšných,
   blokovaných, neprovedených a procento dokončených testovacích případů. 'Dokončené' testovací případy zahrnují stavy úspěšné, neúspěšné, nebo blokované.
	pro hlavní testovací sady včetně všech k nim navázaných testovacích sad.</li>
	<li><b>Výsledky dle klíčového slova</b>
	Zobrazí všechna klíčová slova se souvisejícími výsledky přiřazená testovacím případům v aktuálním testovacím plánu.</li>
	<li><b>Výsledky dle vlastníka testovacích případů</b>
	Zobrazí všechny vlastníky, kteří mají přiřazeny testovací případy v aktuálním testovacím plánu. Testovací případy, které
	nemají přiřazeného vlastníka jsou zobrazeny ve sloupci 'Nepřiřazené'.</li>
</ul>

<h3>Celkový přehled stavu sestavení</h3>
<p>Zobrazí výsledky provedení pro každé sestavení. Pro každé sestavení jsou zobrazeny informace - celkový počet, celkový počet úspěšných,
% úspěšných, neúspěšných, % neúspěšných, blokovaných, % blokovaných, neprovedených, % neprovedených testovacích případů. Pokud byl testovací případ
proveden v jednom sestaveni vícekrát, poslední aktuální provedení testovacího případu bude použito.</p>

<h3>Parametrizovaný přehled</h3>
<p>Tento přehled je složen ze stránky pro nastavení parametrů přehledu a stránky, která zobrazí výsledky na základě nastavených parametrů.
Stránka s nastavením parametrů obsahuje parametry, které můžete použít k řízení obsahu přehledu, který bude zobrazen. Všechny parametry mají základní
nastavení, které maximalizuje počet testovacích případů a sestavení, ze kterých bude přehled sestaven. Změnou parametrů může uživatel filtrovat 
výsledky a tím vygenerovat specifický přehled dle kombinace vlastníka testovacích případů, klíčového slova, 
testovacích sad, nebo sestavení.</p>

<ul>
<li><b>Klíčové slovo</b> Může být vybráno maximalně jedno klíčové slovo. Standartně není vybráno žádné klíčové slovo. Pokud není 
zvoleno klíčové slovo, nebude se brát na klíčová slova a jejich přiřazení ve vyhodnocení ohled. Klíčová slova můžete přiřadit
na stránce správy klíčových slov nebo specifikace testů. Klíčová slova jsou přiřazená k testovacím případům pro všechny jejich verze 
a všechny testovací plány. Pokud Vás zajímají pouze testovací případy pro specifické klíčové
slovo, nastavte adekvátně tento parametr.</li>
<li><b>Vlastník testovacích případů</b> Může být vybrán maximalně jeden vlastník. Standartně není vybrán žádný vlastník. Pokud není
vlastník vybrán, nebude se brát na přiřazení vlastníka k testovacím případům a jeho vyhodnocení ohled. Aktuálně není možné 
vyhledávat 'nepřiřazené' testovací případy. Vlastníky pro zvolený testovací plán je možné přiřadit na stránce 'Přiřazení provedení 
testovacího případu'. Pokud Vás zajímají pouze testovací případy provedené určitým testerem, 
 nastavte adekvátně tento parametr.</li>
<li><b>Testovací sada</b> Vybráno může být více testovacích sad najednou. Standartně jsou vybrány všechny testovací sady.
Pouze data z vybraných testovacích sad budou použity v přehledu. Pokud Vás zajímají pouze výsledky pro specifickou testovací sadu(y) 
nastavte adekvátně tento parametr.</li>
<li><b>Sestavení</b> Vybráno může být jedno nebo více sestavení. Standartně jsou vybrány všechna sestavení. Pouze provedení testovacích
případů z vybraných sestavení budou použity v přehledu. Například, pokud chcete zobrazit kolik testovacích případů bylo provedeno 
v posledních třech sestavení, adektvátně nastavte tento parametr.
Kombinace vybraného klíčového slova, vlastníka testovacích případů a testovacích sad má přímou souvislost s testovacími případy z vybraného 
testovacího plánu, které budou použity pro výpočet metrik v rámci testovací sady a testovacího plánu. Například, pokud vyberete přiřazen = 'Greg',
klíčové slovo='Priorita 1', a všechny dostupné testovací sady, budou v rámci přehledu zobrazeny pouze testovací případy
s prioritou 1 a s vlastníkem Greg. Celkový počet testovacích případů zobrazený v přehledu '# Testovacích případů' bude ovlivněno těmito 
parametry. Výběr sestavení může ovlivňit zda bude testovací případ považován za 'úspěšný', 'neúspěšný', 'blokovaný', nebo 'neprovedený'. Pro 
další informace se podívejte na definici výrazu 'Poslední výsledek testu'.</li>
</ul>
<p>Stiskněte tlačítko 'Odeslat' pro odeslání dotazu a zobrazení stránky s výsledkem.</p>

<p>Na stránce s výsledky parametrizovaného přehledu může být zobrazeno: </p>
<ol>
<li>Parametry použité při výběru dat pro přehled</li>
<li>Celkové součty za celý testovací plán</li>
<li>Rozpad jednotlivých testovacích sad na celkové součty (# Testovacích případů / úspěšné / neúspěšné / blokované / neprovedené) a seznam
provedení testovacích případů v dané testovací sadě. Pokud byl testovací případ proveden vícekrát v několika sestavení, budou zobrazena 
všechna provedení pro zvolené sestavení. Celkové součty však budou pro zvolené sestavení a testovací sady zahrnovat pouze 'Poslední výsledek testů'.</li>
</ol>

<h3>Přehledy blokovaných, neúspěšných a neprovedených testů</h3>
<p>Tyto přehledy zobrazují všechny aktuálně blokované, neúspěšné nebo neprovedené testovací případy.  'Poslední výsledek testu'
výraz (který byl popsán v popisu Celkového přehledu testovacího plánu) je opět použit ke stanovení, zda 
může být testovací případ považován za blokovaný, neúspěšný, nebo neprovedený. V případě, že je testlink konfigurován pro použití s nástrojem pro
správu chyb, budou chyby zobrazeny v přehledech blokovaných a neúspěšných testovacích případů.</p>

<h3>Testovací přehled</h3>
<p>Zobrazí stav každého testovacího případu pro každé sestavení. V případě, že byl v rámci jednoho sestavení proveden testovací případ vícekrát, 
poslední aktuální výsledek provedení bude zobrazen. Pokud pracujete s velkým počtem údajů, doporučuje se exportovat tento přehled
do formátu MS Excel pro jeho snažší využití.</p>

<h3>Grafy - Celkový přehled testovacího plánu</h3>
<p>'Poslední výsledek testu' je výraz, který je použit pro všechny čtyři zobrazené grafy. Pro usnadnění vizualizace metrik aktuálního 
testovacího plánu jsou grafy animovány. Následující čtyři grafy budou zobrazeny :</p>
<ul><li>Koláčový graf s celkovým počtem úspěšných / neúspěšných / blokovaných / a neprovedených testovacích případů</li>
<li>Sloupce grafu s výsledky dle klíčového slovav</li>
<li>Sloupce grafu s výsledky dle vlastníka testovacích případů</li>
<li>Sloupce grafu s výsledky dle hlavní testovací sady</li>
</ul>
<p>Sloupce v grafech jsou barevně rozlišeny, aby mohl uživatel rozlišit přibližný počet 
úspěšných, neúspěšných, blokovaných a neprovedených testovacích případů.</p>
<p><i>Stránka tohoto přehledu vyžaduje, aby měl Váš prohlížeč nainstalováno flash rozšíření (http://www.maani.us), aby byly výsledky zobrazeny
v grafické podobě.</i></p>

<h3>Celkový počet chyb pro každý testovací případ</h3>
<p>Tento přehled zobrazuje každý testovací případ, ke kterému jsou přiřazeny jakékoliv chyby v rámci celého testovacího projektu.
Tento přehled je dostupný pouze v případě, že je připojen nástroj pro správu chyb.</p>";


// ------------------------------------------------------------------------------------------
$TLS_htmltext_title['planAddTC']	= "Přidat / Odebrat testovací případy v rámci testovacího plánu"; // testSetAdd
$TLS_htmltext['planAddTC'] 			= "<h2>Účel</h2>
<p>Umožňuje uživatelům (v roli 'Vedoucí' nebo vyšší) přidávat nebo odebírat testovací případy v rámci aktuálního testovacího plánu.</p>

<h2>Jak na to</h2>
<ol>
	<li>Klikněte na testovací sadu pro zobrazení všech jejich vnořených testovacích sad a k nim přiřazených testovacích případů.</li>
	<li>Po zvolení vybraných testovacích případů klikněte na tlačítko 'Přidat / Odebrat testovací případ' pro jejich přidání nebo odebrání. 
  Poznámka: Do testovací sady není možné přidat testovací případy, které již obsahuje.</li>
</ol>";

// ------------------------------------------------------------------------------------------
$TLS_htmltext_title['tc_exec_assignment']	= "Přiřazení uživatelů k provádění testů";
$TLS_htmltext['tc_exec_assignment'] 		= "<h2>Účel</h2>
<p>Tato stránka umožňuje vedoucím testů přiřadit uživatele ke konkrétnímu testu v rámci testovacího plánu.</p>

<h2>Jak na to</h2>
<ol>
	<li>Zvolte testovací případ nebo testovací sadu.</li>
	<li>Vyberte uživatele k přiřazení.</li>
	<li>Stiskněte tlačítko pro potvrzení přiřazení.</li>
	<li>Otevřete stránku provedení testovacích případů pro ověření přiřazení. Můžete využít nastavení filtru na uživatele.</li>
</ol>";


// ------------------------------------------------------------------------------------------
$TLS_htmltext_title['planUpdateTC']	= "Aktualizace testovacích případů v testovacím plánu.";
$TLS_htmltext['planUpdateTC'] 		= "<h2>Účel</h2>
<p>Tato stránka umožňuje aktualizovat testovací případ na novější (nebo jinou než aktuální) verzi v případě, že se specifikace
testovacího případu změnila. Často se stává, že se během testování změní definice funkčnosti." .
		" Uživatel změní specifikaci testovacího případu, která je poté potřeba také zpropagovat do testovacího plánu. V opačném případě" .
		" bude testovací plán obsahovat původní verzi testovacího případu, aby se zajistila konzistence verze testovacího případu a jeho výsledku.</p>

<h2>Jak na to</h2>
<ol>
	<li>Zvolte testovací případ nebo testovací sadu.</li>
	<li>Zvolte novou verzi z rozbalovacího menu pro zvolený testovací případ.</li>
	<li>Stiskněte tlačítko 'Aktualizovat testovací plán' pro odeslání změn.</li>
	<li>Ověření aktualizace: Otevřete stránku provedení testovacích případů, kde bude zobrazen text testovacího případu/prípadů.</li>
</ol>";


// ------------------------------------------------------------------------------------------
$TLS_htmltext_title['test_urgency']	= "Nastavení vysoké nebo nízké urgentnosti pro testovací případy a sady";
$TLS_htmltext['test_urgency'] 		= "<h2>Účel</h2>
<p>TestLink umožnuje nastavení urgentnosti testovací sady k ovlivnění priority testovacích případů. 
		Priorita testovacích případů v rámci testovacího plánu závisí na jejich důležitosti a urgentnosti.
     Vedoucí testů by měl nastavit sadu testovacích případů, které se budou prioritně provádět. V časové tísni by pak mělo být zajištěno povedení 
     těch nejdůležitějších testů.</p>

<h2>Jak na to</h2>
<ol>
	<li>V navigačním panelu na levé straně zvolte testovací sadu.</li>
	<li>Vyberte úroveň urgentnosti (vysoká, střední nebo nízká). Přednastavena je střední úroveň. Můžete
	snížit prioritu u testovacích případů, u kterých nebude důležitost změněna, popřípadě zvýšit v případě 
  jejich větších změn.</li>
	<li>Stiskněte tlačítko 'Uložit' pro odeslání změn.</li>
</ol>
<p><i>Pro příklad, u testovacího případu s vysokou důležitostí v testovací sadě s nízkou urgentností bude " .
		"výsledkem střední priorita.</i>";


// ------------------------------------------------------------------------------------------
$TLS_htmltext_title['planAddTcDocumentation'] = "Plan add testcase documentation";
$TLS_htmltext['planAddTcDocumentation'] = "<h2>@TODO Plan add testcase documentation</h2>";
?>
