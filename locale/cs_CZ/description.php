<?php
/** 
 * ♔ TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later.
 * 
 * Filename $RCSfile: description.php,v $
 * @version $Revision: 1.4 $
 * @modified $Date: 2010/06/24 17:25:52 $ by $Author: asimon83 $
 * @author Martin Havlat
 * @author Pavel Kalian
 *
 * LOCALIZATION:
 * === English (en_GB) strings === - default development localization (World-wide English)
 *
 * @ABSTRACT
 * The file contains global variables with html text. These variables are used as 
 * HELP or DESCRIPTION. To avoid override of other globals we are using "Test Link String" 
 * prefix '$TLS_hlp_' or '$TLS_txt_'. This must be a reserved prefix.
 * 
 * Contributors:
 * Add your localization to TestLink tracker as attachment to update the next release
 * for your language.
 *
 * No revision is stored for the the file - see CVS history
 *
 * ----------------------------------------------------------------------------------- */

//If this file is coded in UTF-8 then enable the following line
$TLS_STRINGFILE_CHARSET = "UTF-8";

// printFilter.html
$TLS_hlp_generateDocOptions = "<h2>Možnosti vytvoření dokumentu</h2>

<p>Tato tabulka umožnuje uživatelům zvolit testovací případy, které mají být zobrazeny. Pouze
zvolené testovací sady budou zobrazeny dle požadovaného obsahu. Pokud budete chtít změnit obsah nebo zvolit jinou
testovací sadu, proveďte změnu obsahu části navigace, popřípadě zvolte ze stromové struktury jinou 
testovací sadu.</p>

<p><b>Hlavička dokumentu:</b> Uživatelé mohou zvolit, které informace budou zobrazeny v hlavičce dokumentu. 
Informace v hlavičce dokumentu mohou obsahovat: Úvod, Obsah, Odkazy, 
Metodologii testování a omezení testů.</p>

<p><b>Detaily testovacího případu:</b> Uživatelé mohou zvolit, které informace budou zobrazeny v popisu testovacích případů. Informace v popisu testovacích případů mohou obsahovat: přehled o testovacím případu, kroky testovacího skriptu, očekávané výsledky a klíčová slova.</p>

<p><b>Stručný obsah testovacího případu:</b> Uživatelé nemohou vypnout zobrazení shrnutí testovacího případu, pokud se rozhodli zobrazit jeho detail,
jelikož je standartně jeho součástí. Pokud nebude vybrán k zobrazení detail testovacího případu, bude umožněno zobrazení pouze jeho shrnutí v případné 
kombinaci s dalšími možnostmi.</p>

<p><b>Možnosti rejstříku obsahu:</b> Pokud je zvoleno uživatelem, TestLink vloží do rejstřiku obsahu seznam všech názvů testovacích připadů s interním hypertextovým odkazem.</p>

<p><b>Výstupní formát:</b> Můžete zvolit ze dvou možností: HTML nebo MS Word. V druhém případě Váš prohlížeč spustí komponentu MS wordu.</p>";

// testPlan.html
$TLS_hlp_testPlan = "<h2>Testovací Plán</h2>

<h3>Obecný popis</h3>
<p>Tvorba testovacího plánu je systematickým přístupem k testovaní systémů (například software). Dovoluje Vám organizování testovacích aktivit
do příslušných sestavení produktu v daném čase a sledování jeho výsledků.</p>

<h3>Provádění testů</h3>
<p>Tato sekce umožňuje uživatelům provádět testovací případy (zapsat jejich výsledek) a 
případně vytisknout si testovací sadu z aktuálního testovacího plánu. V této sekci mohou uživatelé také sledovat výsledky
prováděných testovacích případů.</p> 

<h2>Správa testovacích plánů</h2>
<p>Tato sekce, dostupná pouze uživatelům v roli 'Vedoucí', umožňuje spravovat testovací plány. 
Správa testovacích plánů zahrnuje jejich vytváření, mazání, nebo úpravu. Dále přidávání, mazání, nebo úpravu testovacích připadů v rámci testovacího plánu, vytváření sestavení, nebo nastavení oprávnění přístupu k plánu.<br />
Uživatelé v roli 'Vedoucí' mohou také nastavovat prioritu/rizika a vlastnictví pro sady testovacích případů nebo vytvářet testovací milníky.</p> 

<p>Upozornění: Je možné, že uživatelé nemusí mít dostupný seznam testovacích plánů. 
V tomto případě budou odkazy v úvodním okně nefunkční (neplatí pro uživatele s oprávněním 'Vedoucí' nebo vyšší). Pokud se  
dostanete do takovéto situace, kontaktujte uživatele s rolí 'Vedoucí' popřípadě 'Administrátor', který vám nastaví oprávnění, nebo pro Vás vytvoří testovací plán.</p>"; 

// custom_fields.html
$TLS_hlp_customFields = "<h2>Uživatelská pole</h2>
<p>Fakta o možnostech využití uživatelských polí:</p>
<ul>
<li>Uživatelská pole jsou definována přes celý systém.</li>
<li>Uživatelská pole jsou navázána na komponenty Testlinku (testovací sada, testovací případ, ...)</li>
<li>Uživatelská pole mohou být navázána na vícero testovacích projektů současně.</li>
<li>Pořadí zobrazení uživatelských polí může být odlišné v každém testovacím projektu.</li>
<li>Uživatelská pole mohou být vypnuta pro jakýkoliv testovací projekt.</li>
<li>Počet uživatelských polí není omezen.</li>
</ul>

<p>Definice uživatelského pole obsahuje následující atributy:</p>
<ul>
<li>Jméno uživatelského pole</li>
<li>Název popisku proměnné (například: Tato hodnota je předána funkci lang_get() API , nebo zobrazena v případě, že pro ni neexistuje překlad).</li>
<li>Typ uživatelského pole (řetězec, celé číslo, desetiné číslo, výčet, email)</li>
<li>Výčet možných hodnot (například: ČERVENÁ|ŽLUTÁ|MODRÁ), vztahuje se k seznamu s možností vícenásobného výběru, 
nebo rozbalovacímu seznamu.<br />
<i>Použijte oddělovací znak ('|') pro oddělení položek ve výčtu možností. Prázdný řetězec může být použit jako jedna z položek ve výčtu.</i>
</li>
<li>Přednastavená hodnota: NENÍ IMPLEMENTOVÁNO</li>
<li>Minimální/maximální délka hodnoty uživatelského pole (použijte 0 pro vypnutí). (NENÍ IMPLEMENTOVÁNO)</li>
<li>Regulární výrazy pro validaci uživatelského vstupu
(použijte  <a href=\"http://au.php.net/manual/en/function.ereg.php\">ereg()</a>
syntaxi). <b>(NENÍ IMPLEMENTOVÁNO)</b></li>
<li>Všechny hodnoty uživatelských polí jsou aktuálně ukládany v databázi jako řetezec VARCHAR(255).</li>
<li>Zobrazit při specifikaci testovacích případů.</li>
<li>Povolí uživatelské pole pro zobrazení a editaci v rámci specifikace testovacích případů. Uživatel může měnit hodnotu uživatelského pole při návrhu specifikace testovacího případu</li>
<li>Zobrazit při provádění testovacích případů.</li>
<li>Povolí uživatelské pole pro zobrazení a editaci v rámci provádění testovacích případů. Uživatel může měnit hodnotu uživatelského pole při provádění testovacích případů</li>
<li>Zobrazit při návrhu testovacího plánu.</li>
<li>Povolí uživatelské pole pro zobrazení a editaci v rámci návrhu testovaciho plánu.. Uživatel může měnit hodnotu uživatelského pole při návrhu testovacího plánu. (přiřazení testovacích případů do testovacího plánu)</li>
<li>Dostupné pro: Uživatel zvolí k jaké komponentě Testlinku se pole vztahuje.</li>
</ul>
";

// execMain.html
$TLS_hlp_executeMain = "<h2>Provádění testovacích případů</h2>
<p>Umožňuje uživatelům 'provádět' testovací případy. Samotné provedení testovacího připadu v ramci aplikace Testlink spočívá v nastavení jeho stavu (úspěšný, neúspěšný, ...).</p>
<p>Aplikace Testlink umožňuje přístup do nástrojů pro správu chyb (závisí na konfiguraci). Uživatelé pak mohou k testovacím případům přiřadit chyby a sledovat jejich stav.</p>";

//bug_add.html
$TLS_hlp_btsIntegration = "<h2>Přiřazení chyby k testovacímu případu</h2>
<p><i>(pouze v případě, že je nastaveno propojení s nástrojem pro správu chyb)</i>
TestLink poskytuje jednoduchou integraci s nástroji pro správu chyb (BTS). Neumožňuje však vložit chybu v rámci aplikace Testlink do BTS ani automatické předávaní ID chyby mezi BTS a Testlink.
Integrace s BTS je provedena na úrovni odkazu, který umožní provést následující činnosti:
<ul>
	<li>Vložit novou chybu.</li>
	<li>Zobrazit informace o již existující chybě. </li>
</ul>
</p>  

<h3>Postup pro vložení chyby</h3>
<p>
   <ul>
   <li>Krok 1: použijte odkaz pro otevření BTS a vložte novou chybu. </li>
   <li>Krok 2: poznamenejte si ID chyby v rámci BTS.</li>
   <li>Krok 3: napište ID chyby do vstupního pole v Testlinku.</li>
   <li>Krok 4: použijte tlačítko Přidat chybu.</li>
   </ul>  

Po uzavření stránky 'Přidat chybu' uvidíte informace o chybě navázané na testovací případ, pro který byla chyba přidána.
</p>";

// execFilter.html
$TLS_hlp_executeFilter = "<h2>Nastavení sestavení a filtrů pro zobrazení výsledků provedení testů</h2>

<p>Levý ovládací panel se skládá ze seznamu testovacích případů navázaných na aktuální " .
"testovací plán a tabulku s možnostmi nastavení a filtrování. Tyto filtry umožňují uživateli " .
"omezit nabízenou sadu testovacích případů před tím, než budou provedeny." .
"Nastavte filtr, stiskněte tlačítko \"Použít\" a zvolte příslušný testovací případ " .
"z menu.</p>

<h3>Sestavení</h3>
<p>Uživatelé musí zvolit, pro které sestavení se jim budou výsledky zobrazovat. " .
"Sestavení jsou základní komponentou pro aktuální testovací plán. Každý testovací případ " .
"může být v rámci jednoho sestavení proveden vícekrát. Pro filtrování ale bude použit pouze poslední výsledek. 
<br />Sestavení mohou být vytvořena uživateli s rolí 'Vedoucí' nebo vyšší na stránce Správa sestavení.</p>

<h3>Filtrování dle ID testovacího případu</h3>
<p>Uživatelé mohou filtrovat testovací případy podle jejich unikátního označení (ID). Toto ID je automaticky generováno při vytváření testovacího případu. Pokud bude vstupní pole u filtru ID prázdné, nebude tento filtr aplikován.</p> 

<h3>Filtrování dle priority</h3>
<p>Uživatelé mohou filtrovat testovací případy dle jejich priority. Důležitost každého testovacího případu ve zvoleném testovacím plánu je kombinována s jeho urgentností. Například testovací případ s prioritou 'Vysoká' " .
"je zobrazen v případě, že důležitost je nastavena na úroveň VYSOKÁ a urgentnost je nastavena na úroveň STŘEDNÍ, popřípadě obráceně.</p> 

<h2>Filtrování dle výsledků</h2>
<p>Uživatelé mohou filtrovat testovací případy dle jejich výsledků. Výsledkem se myslí stav testovacího případu ve zvoleném sestavení. Testovací případ může být například ve stavu: úspěšný, neúspěšný, blokován, nebo neproveden." .
"Tento filtr je standartně vypnut.</p>

<h3>Uživatelské filtry</h3>
<p>Uživatelé mohou filtrovat testovací případy dle jim přiřazeného uživatele. Do výsledků je také možné zahrnout " .
"\"nepřiřazené\" testovací případy zaškrtnutím příslušného pole.</p>";
/*
<h2>Most Current Result</h2>
<p>By default or if the 'most current' checkbox is unchecked, the tree will be sorted 
by the build that is chosen from the dropdown box. In this state the tree will display 
the test cases status. 
<br />Example: User selects build 2 from the dropdown box and doesn't check the 'most 
current' checkbox. All test cases will be shown with their status from build 2. 
So, if test case 1 passed in build 2 it will be colored green.
<br />If the user decideds to check the 'most current' checkbox the tree will be 
colored by the test cases most recent result.
<br />Ex: User selects build 2 from the dropdown box and this time checks 
the 'most current' checkbox. All test cases will be shown with most current 
status. So, if test case 1 passed in build 3, even though the user has also selected 
build 2, it will be colored green.</p>
 */


// newest_tcversions.html
$TLS_hlp_planTcModified = "<h2>Nejnovější verze testovacích případů v aktuálním testovacím plánu</h2>
<p>Všechny testovací případy navázané do testovacího plánu budou analyzovány a zobrazí se seznam testovacích případů, které jsou dostupné v novější verzi.</p>";


// requirementsCoverage.html
$TLS_hlp_requirementsCoverage = "<h3>Pokrytí požadavků</h3>
<br />
<p>Tato funkčnost umožnuje mapovat pokrytí testovacích případů uživateli nebo specifikacemi požadavků. K této funkčnosti se dostanete odkazem \"Specifikace požadavků\" na úvodní obrazovce.</p>

<h3>Specifikace požadavků</h3>
<p>Požadavky jsou uspořádány v rámci dokumentu specifikace požadavků, který je definována v rámci 
testovacího projektu.<br /> TestLink nepodporuje verzování dokumentu specifikace požadavků ani samotných požadavků. Verze dokumentu nebo požadavků by tak měla být zaznamenána v jejich <b>názvu</b>.
Uživatel může také připadně přidat popis nebo poznámku v rámci jejich b>obsahu</b>.</p> 

<p><b><a name='total_count'>Počet importovaných požadavků</a></b> slouží pro vyhodnocení pokrytí požadavků v případě, že některé z požadavků nebyly přidány (importovány). 
Hodnota <b>0</b> vyjadřuje aktuální počet požadavků zahrnutých do metrik.</p> 
<p><i>Například SRS obsahuje 200 požadavků, ale pouze 50 je jich přidano do testlinku. Pokrytí testy je pak z 25% (za předpokladu, že budou všechny přidané požadavky zahrnuty do testování).</i></p>

<h3><a name=\"req\">Požadavky</a></h3>
<p>Klikněte na jméno vytvořeného dokumentu specifikace požadavků. Pro dokument pak můžete vytvářet, upravovat, mazat, nebo importovat požadavky. Každý požadavek má jméno, specifikaci a stav.
Stav může být \"Normální\" or \"Netestovatelné\". Netestovatelné požadavky nejsou zahrnuty do výpočtu metrik. Tento parametr může být použit pro neimplementované prvky i pro špatně definované požadavky.</p> 

<p>Můžete vytvořit nové testovací případy přímo na stránce specifikace požadavků pro vybrané požadavky použitím automatické funkce. Tyto testovací případy budou vytvořeny v testovací sadě, jejíž jméno je definováno konfigurací Testlinku <i>(standartní nastavení je: &#36;tlCfg->req_cfg->default_testsuite_name = 
'Test suite created by Requirement - Auto';)</i>. Titulek a obsah jsou zkopírovány do těchto testovacích případů.</p>
";


// planAddTC_m1.tpl
$TLS_hlp_planAddTC = "<h2>Možnost 'Uložit uživatelská pole'</h2>
Pokud nadefinujete a přiřadite uživatelská pole do testovacího projektu s nastavením:<br />
 'Zobrazit v testovacím plánu=ano' a <br />
 'Povolit v testovacím plánu=ano'<br />
budou tyto pole dostupné POUZE pro testovací případy navázané do testovacího plánu.
";

// xxx.html
//$TLS_hlp_xxx = "";

// ----- END ------------------------------------------------------------------
?>
