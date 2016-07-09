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


// --------------------------------------------------------------------------------------
$TLS_htmltext_title['error']	= "Application error";
$TLS_htmltext['error'] 		= "<p>Unexpected error happens. Please check event viewer or " .
		"logs for details.</p><p>You are welcome to report the problem. Please visit our " .
		"<a href='http://www.teamst.org'>website</a>.</p>";



$TLS_htmltext_title['assignReqs']	= "Przypisywanie wymagań do przypadków testowych";
$TLS_htmltext['assignReqs'] 		= "<h2>Cel:</h2>
<p>Użytkownicy mogą stworzyć powiązania pomiędzy wymaganiami a przypadkami testowymi. Projektant testu może zdefiniować relacje typu 0..n do 0..n. 
Przypadek testowy może nie mieć powiązania lub być powiązany do jednego lub więcej wymagań i na odwrót. Taka macierz wspomaga prześledzenie pokrycia testów wzglęcem wymagań oraz znalezieniu wymagań, które nie przeszły testów pozytywnie. Taka
analiza stanowi potwierdzenie, że wszystkie sprecyzowane oczekiwania zostały spełnione.</p>

<h2>Jak zacząć:</h2>
<ol>
	<li>Wybierz przypadek testowy na drzewku z lewej strony. Okienko wyboru z listą wymagań jest umieszczone na górze strony.</li>
	<li>Wybierz dokument specyfikacji testowej jeżeli jest stworzony więcej niż jeden. 
	TestLink automatycznie odświeża stronę.</li>
	<li>Środkowa część okna zawiera listę wszystkich wymagań (z wybranego dokumentu specyfikacji) które są powiązane do zaznaczonego przypadku testowego. Dolna część okna zawiera listę wszystkich wymagań, które nie mają powiązania do bieżącego przypadku testowego. Projektant może zaznaczyć wymagania, które są pokryte tym przypadkiem testowym a następnie kliknąć przycisk 'Przypisz'. Wówczas te nowo przypisane przypadki testowe będą widoczne w środkowej części okna.</li>
</ol>";


// --------------------------------------------------------------------------------------
$TLS_htmltext_title['editTc']	= "Specyfikacja testowa";
$TLS_htmltext['editTc'] 		= "<p><i>Specyfikacja testowa</i> pozwala użytkownikom na wgląd " .
		"i edycje wszystkich istniejących <i>zestawów testów</i> i <i>przypadków testowych</i>. " .
		"Przypadki testowe są pogrupowane w wersjach, wszystkie poprzednie wersje są dostępne i mogą być " .
		"oglądane oraz zarządzane z tego miejsca.</p>
		
<h2>Jak zacząć:</h2>
<ol>
	<li>Zaznacz swój <i>projekt testowy</i> w nawigatorze (w głównej cześci). <i>Proszę zwróć uwagę: " .
	" Możesz zawsze zmienić aktywny projekt testowy poprzez zaznaczenie innego z " .
	"rozwijanej listy w prawym górnym rogu.</i></li>
	<li>Stwórz nowy zestaw testów poprzez kliknięcie <b>Utwórz zestaw</b> (Operacje na zestawach testów). Zestawy Testowe mogą " .
	"stworzyć strukture dla twojego dokumentu testowego w zależności od przyętej konwencji (funkcjonalny / niefunkcjonalny " .
	"testy, części produktu lub jego cechy, zmiana żądań, itp.). Opis " .
	"Zestaw testowy może zawierać zarys zawartych przypadków testowych, domyślną konfiguracje, " .
	"linki do stosownych dokumentów, ograniczeń i innych istotnych informacji. Generalnie, " .
	"wszystkie adnotacje są zawarte w Dziecku Przypadku testowego. Przypadek testowy śledzi " .
	"the &quot;folder&quot; metafore, tak więc użytkownicy moga skopiować Zestaw testów z projektu testu " .
	"z projektu testu mogą być również, zaimportowane oraz eksportowane (właczając zgromadzone przypadki testowe).</li>
	<li>Zestawy testów są wymiernymi folderami. Użytkownicy moga przenosić lub kopiować Zestawy Testów z " .
	"Projektu testu. Zestawy testów mogą być importowane lub eksportowane (właczając Przypadki testowe).
	<li>Zaznacz swój nowo stworzony Zestaw Testów w nawigatorze i utwórz " .
	"nowy Przypadek testowy poprzez kliknięcie na <b>Utwórz</b> (Dzialania na Przypadku testowym). Przypadek testowy obejmuje " .
	"określony scenariusz testowania, oczekiwane rezultaty i zdefiniowane pola użytkownika " .
	"w Projekcie testu (sprawdź w instrukcji użytkownika dla uzyskania więcej informacji). Jest możliwe " .
	"przypisać <b>słowa kluczowe</b> dla zwiększenia efektywności śledzenia.</li>
	<li>Zarządzaj poprzez drzewko  nawigatora z lewej strony i edytuj dane. Każdy przypadek testowy zawiera własną historie.</li>
	<li>Przypisz stworzoną przez siebie Specyfikacje testową	<span class=\"help\" onclick=
	\"javascript:open_help_window('glosary','$locale');\">Plan Testu</span> kiedy twój Przypadek testowy jest gotowy.</li>
</ol>

<p>Poprzez TestLink mozesz zorganizować  Przypadki testowe w  Zestawy testowe." .
"Zestawy testowe mogą odnosić się do innych zestawów testowych, umożliwiając Ci tworzenie hierachii Zestawów testowych.
 Możesz wydrukować tą informacje razem z Przypadkami testowymi.</p>";


// ------------------------------------------------------------------------------------------
$TLS_htmltext_title['searchTc']	= "Strona wyszukiwania przypadku testowego";
$TLS_htmltext['searchTc'] 		= "<h2>Cel:</h2>

<p>Nawigacja poprzez słowa kluczowe lub/ i przeszukiwane ciągi. Podczas wyszukiwania nie są rozróżnione wielkości liter.
Resultaty zawierają tylko przypadki testowe z aktualnego Projektu testu.</p>

<h2>W celu wyszukiwania:</h2>

<ol>
	<li>Wpisz wyszukiwany ciąg w odpowiedniej rubryce. Pola, które są nieużywane w formularzu należy pozostawić puste.</li>
	<li>Wybierz odpowiednie słowa kluczowe, pozostałe wartości nie mają znaczenia.</li>
	<li>Kliknij na przycisk 'wyszukaj'.</li>
	<li>Wszystkie podlegające selekcji przypadki testowe zostaną pokazane. Możesz modyfikować Przypadki testowe poprzez link w tytule.</li>
</ol>";

/* contribution by asimon for 2976 */
// requirements search
// ------------------------------------------------------------------------------------------
$TLS_htmltext_title['searchReq']	= "Strona wyszukiwania wymagań";
$TLS_htmltext['searchReq'] 		= "<h2>Cel:</h2>

<p>Nawigacja poprzez słowa kluczowe i/lub przeszukiwane ciągi. Podczas wyszukiwania nie są rozróżnione wielkości liter.
Rezultaty zawierają tylko wymagania dla bieżącego projektu.</p>

<h2>W celu wyszukiwania:</h2>

<ol>
	<li>Wpisz wyszukiwany ciąg w odpowiedniej rubryce. Pola, które są nieużywane w formularzu powinny być puste.</li>
	<li>Wybierz odpowiednie słowa kluczowe lub pozostaw wartość 'bez zastosowania'.</li>
	<li>Kliknij na przycisk 'wyszukuj'.</li>
	<li>Wszystkie podlegające selekcji przypadki testowe zostaną pokazane. Możesz modyfikować Przypadki testowe poprzez link w tytule.</li>
</ol>

<h2>Uwaga:</h2>

<p>- Tylko, wymagania zawarte w bieżącym projecie będą wyszukiwane.<br>
- Wyszykiwanie nie rozróżnia wielkich i małych liter.<br>
- Puste pola nie sa brane pod uwagę.</p>";

// requirement specification search
// ------------------------------------------------------------------------------------------
$TLS_htmltext_title['searchReqSpec']	= "Strona wyszukiwania specyfikacji wymagań";
$TLS_htmltext['searchReqSpec'] 		= "<h2>Cel:</h2>

<p>Nawigacja poprzez słowa kluczowe i/lub przeszukiwane ciągi. To wyszukiwanie nie rozróżnia
wielkich i małych liter. Rezultaty zawierają tylko specyfikacje wymagań dla aktualnego Projektu testu.</p>

<h2>W celu wyszukiwania:</h2>

<ol>
	<li>Wpisz wyszukiwany ciąg w odpowiedniej rubryce. Pola, które są nieużywane w formularzu powinny być puste.</li>
	<li>Wybierz odpowiednie słowa kluczowe lub pozostaw wartość 'bez zastosowania'.</li>
	<li>Kliknij na przycisk 'wyszukaj'.</li>
	<li>Wszystkie podlegające selekcji przypadki testowe zostaną pokazane. Możesz modyfikować Przypadki testowe poprzez link w tytule.</li>
</ol>

<h2>Uwaga:</h2>

<p>- Tylko, wymagania zawarte w bieżącym projecie będą wyszukiwane.<br>
- Wyszykiwanie nie rozróżnia wielkich i małych liter.<br>
- Puste pola nie sa brane pod uwagę.</p>";
/* end contribution */


// ------------------------------------------------------------------------------------------
$TLS_htmltext_title['printTestSpec']	= "Wydrukuj dpecyfikacje testową"; //printTC.html
$TLS_htmltext['printTestSpec'] 			= "<h2>Cel:</h2>
<p>Z tego punktu możesz wydrukować pojedyńczy przypadek testowy, wszystkie przypadki testowe w zestawie testowym,
lub wszystkie przypadki testowe w projekcie testowym lub planie testu.</p>
<h2>Jak zacząć:</h2>
<ol>
<li>
<p>Zaznacz części Przypadku testowego który chcesz obejrzeć następnie kliknij na przypadek testowy,  
zestaw testowy lub projekt testowy.  Zostanie wyświetlona strona do druku.</p>
</li>
<li><p>Użyj opcji \"pokaż jako\" drop-box w nawigatorze w celu sprecyzowania jakie chcesz aby informacje  
były wyświetlone w postaci HTML, OpenOffice Writer lub w postaci dokumentu Micosoft Word. 
See <span class=\"help\" onclick=\"javascript:open_help_window('printFilter',
'{$locale}');\">help</span> w celu uzyskania więcej informacji.</p>
</li>
<li><p>Użyj funkcjonalności swojej przeglądarki w celu wydrukowania informacji.<br />
<i>Uwaga: Upewnij się, żeby wydrukować ramkę z prawej strony.</i></p>
</li>
</ol>";


// ------------------------------------------------------------------------------------------
$TLS_htmltext_title['reqSpecMgmt']	= "Projekt dokumentu specyfikacji wymagań"; //printTC.html
$TLS_htmltext['reqSpecMgmt'] 			= "<p>Możesz zarządzać dokumentami specyfikacji wymagań.</p>

<h2>Specyfikacja wymagań</h2>

<p>Wymagania są zebrane w <b>Dokumencie Specyfikacja Wymagań</b>, które jest powiązane z 
Projektem Testu.<br /> TestLink nie realizuje (jeszcze) jednocześnie Specyfikacji Wymagań
i  Wymagań samych w sobie. Więc, wersja dokumentu powinna być dodana po Specyfikacji <b>Tytuł</b>.
Użytkownik może dodać prosty opis lub notatkę <b>Szkic</b> pola.</p>

<p><b><a name='total_count'> Przypisana liczba wymagań </a></b> służy do
oceny pokrycia wymagań w przypadku jeżeli nie wszystkie wymagania są zawarte w TestLinku.
Wartość <b>0</b> oznacza bieżącą liczne wymagań, która jest użyta dla metryki.</p>
<p><i>Na przykład: SRS zawiera 200 wymagań, ale tylko 50 jest dodane do TestLink. Pokrycie testu jest 
 25% (podsumowując 50 dodanych wymagań będzie testowane).</i></p>

<h2><a name='req'>Wymagania</a></h2>

<p>Kliknij na tytule Specyfikacji Wymagań. Jeżeli żaden nie istnieje, " .
	"kliknij na  oknie projektu w celu utworzenia. Możesz tworzyć, edytować, usuwać,
lub importować wymagania dla dokumentu. Każde z wymagań ma tytuł, szkic i status.
Status powinien być 'Normalny' lub  'Nietestowalny'. Nietestowalne wymagania nie są wliczane do metryki. 
Ten parametr powinien być użyty dla zarówno cech wymagań które nie zostały zaimplementowane jak i źle zaprojektowane.</p>

<p> Możesz stworzyć nowy przypadek testowy dla wymagań poprzez zaznaczenie wymagań w oknie specyfikacji. 
Te Przypadki testowe są stworzone w Zestawie testowym. 
z nazwą zdefiniowaną w konfiguracji<i>(default is: \$tlCfg->req_cfg->default_testsuite_name =
'Zestaw testowy stworzony według wymagań - Auto';)</i>. Tytuł i Szkic sa kopiowane do tych Przypadków testowych.</p>";


// ------------------------------------------------------------------------------------------
$TLS_htmltext_title['printReqSpec'] = "Wydrukuj specyfikacje wymagań"; //printReq
$TLS_htmltext['printReqSpec'] = "<h2>Cel:</h2>
<p> Z tego punktu możesz wydrukować pojedyńcze wymaganie, wszystkie wymagania z specyfikacja wymagań
lub wszystkie wymagania w projekcie testu.</p>
<h2>Jak zacząć:</h2>
<ol>
<li>
<p>Zaznacz częśc wymagań, które chcesz wyświetlić i kliknij na wymaganiu, 
specyfikacji wymagań lub na projekcie testu. Będzie pokazana wersja do druku strony.</p>
</li>
<li><p>Użyj opcji \"pokaż jako\" drop-box w nawigatorze w celu sprecyzowania jakie chcesz aby informacje  
były wyświetlone w postaci HTML, OpenOffice Writer lub w postaci dokumentu Micosoft Word. 
See <span class=\"help\" onclick=\"javascript:open_help_window('printFilter',
'{$locale}');\">help</span> for more information.</p>
</li>
<li><p>Użyj funkcjonalności swojej przeglądarki w celu wydrukowania informacji.<br />
<i>Uwaga: Upewnij się, żeby wydrukować ramkę z prawej strony.</i></p>
</li>
</ol>";


// ------------------------------------------------------------------------------------------
$TLS_htmltext_title['keywordsAssign']	= "Przypisywanie słów kluczowych";
$TLS_htmltext['keywordsAssign'] 			= "<h2>Cel:</h2>
<p>To jest stroną gdzie można obserwować  powiązanie słów kluczowych do istniejących
 przypadeków testowych i Zestawów Testowych</p>

<h2>W celu przypisania słów kluczowych:</h2>
<ol>
	<li>Zaznacz Zestaw Testów lub przypadek testowy na drzewku 
	    z lewej strony.</li>
	<li>Rubryka, która pojawia się w prawym górnym rogu pozwala na przypisanie 
	    dowolnego słowa kluczowego do każdego przypadku testowego.</li>
	<li>Selekcja poniżej pozwoli przypisać przypadeku testowy z 
	    uwzględnieniem bardziej szczegółowego poziomu.</li>
</ol>

<h2> Ważne informacje dotyczące przypisywania słów kluczowych w Planie testu:</h2>
<p> Przypisywanie słów kluczowych, którego dokonujesz w specyfikacji dotyczy wyłacznie przypadków testowych, 
w twoich planach testów wtedy i tylko wtedy gdy plan testu zawiera najnowszą wersje przypadku testowego. 
W przeciwnym wypadku, plan testu, który zawiera starszą wersje przypadku testowego poprzez  przypisanie którego dokonujesz,
nie pojawi się w planie testu.</p>
<p>To zastosowanie ma w Testlinku ma takie znaczenie, że przypisania słów kluczowych dokonane w planach testu
będą  dotyczyć wyłącznie najnowszych wersji przypadków testowych, a nie starszych przypadków testowych.
Jeżeli chcesz, aby twój przypadek testowy był uaktualniony w planie testu, wcześniej sprawdź czy przypadki testowe zostały uaktualnione 
poprzez opcje 'uaktualnij modyfikowane przypadki testowe' przed dokonaniem przypisania słów kluczowych.</p>";


// ------------------------------------------------------------------------------------------
$TLS_htmltext_title['executeTest']	= "Wykonanie Przypadku Testowego";
$TLS_htmltext['executeTest'] 		= "<h2>Cel:</h2>

<p>Pozwala użytkownikowi na przeprowadzanie przypadków testowych. Użytkownik może przypisać wynik testu 
do przypadku testowego dla struktury. Sprawdź pomoc w celu uzyskania więcej informacji o filtrach i ustawieniach. " .
"(kliknij na ikone znaku zapytania).</p>
                                               
<h2>Jak zacząć:</h2>

<ol>
	<li>Użytkownik musi mieć określoną strukture dla planu testu.</li>
	<li>Zaznacz strukture z listy down box</li>
	<li>Jeżeli chcesz zobaczyć tylko kilka przypadków testowych zamist całego drzewka
		możesz wybrać stosowne filtry. Kliknij na przycisk \"Zastosuj\"-
		po zmianie filtrów.</li>	
	<li>Kiliknij na przypadku testowym na drzewku.</li>
	<li>Uzupełnij wynik przypadku testowego i wszystkie odpowiednie notatki i błędy.</li>
	<li>Zapisz rezultat.</li>
</ol>
<p><i>Uwaga:  TestLink musi być tak stworzony aby współdziałac z twoim  systemem śledzenia błędów BTS 
Jeżeli chcesz stworzyć/ śledzić problem bezpośrednio z  GUI.</i></p>";

// ------------------------------------------------------------------------------------------
$TLS_htmltext_title['showMetrics']	= "Opis raportów testu i metryki";
$TLS_htmltext['showMetrics'] 		= "<p>Raporty są połączone z Planem Testu " .
		"(zdefiniowane na górze nawigatora). Ten Plan Testu może się różnić 
od Planu Testu do wykonania. Możesz wybrac odpowiedni format raportu:</p>
<ul>
<li><b>Normal</b> - raport jest wyświetlany na stronie</li>
<li><b>OpenOffice Writer</b> - raport jest zaimportowany do OpenOffice Writer</li>
<li><b>OpenOffice Calc</b> - raport jest zaimportowany do OpenOffice Calc</li>
<li><b>MS Excel</b> -raport jest zaimportowany do Microsoft Excel</li>
<li><b>HTML Email</b> - raport jest wysyłany emailem na udres użytkownika</li>
<li><b>Wykresy</b> - raport zawiera wykresy (flash)</li>
</ul>

<p>Przycisk drukuj aktywuje drukowanie wyłacznie raportu (bez nawigacji).</p>
<p> Jest kilka różnych form raportów do wyboru, ich cel i funkcjonalność jest opisana poniżej. </p>

<h3>Plan Testu</h3>
<p>Dokument 'Plan testu' ma możliwość określenia zawartości struktury dokumentu.</p>

<h3>Raport Testu</h3>
<p>Dokument 'Report Testu' ma możliwość określenia zawartości struktury dokumentu.
Zalicza się do tego przypadki testowe wraaz z rezultatami.</p>

<h3>Ogólna macierz planu testu</h3>
<p> Ta strona pokazuje tylko najbardziej bieżący status przypadku testowego według zestawów testu, właściciela i słów kluczowych.
Jako status najbardziej 'bierzący' jest określana najnowsza struktura na której był przeprowadzany przypadek testowy.  Dla
przykładu, jeżeli przypadek testowy był przeprowadzany na wielu strukturach, tylko ostatni wynik jest brany pod uwagę.</p>

<p>'Wynik ostaniego testu' określenie używane w wielu raportach i rozumiane jako:</p>
<ul>
<li> Polecenie w którym struktury są dodawane do planu testu określa, które struktury są najczęstsze. Wyniki
z najnowszych struktur mają pierwszeństwo przed starszymi wersjami struktur. Dla przykładu, jezeli zaznaczysz test jako 'Niepoprawny'
 w strukturze 1 i zaznaczysz jako 'poprawny' w strukturze 2,najnowsza wersja będzie 'poprawny'.</li>
<li>Jeżeli przypadek testowy jest przeprowadzany wielokrotnie na tej samej strukturze to ostani przeprowadzony test jest ważniejszy. 
Dla przykładu, jeżeli struktura 3 jest przypisana do twojego zespołu i  tester 1 zaznaczy test jako 'poprawny' o drugiej popołudniu,
a tester 2 oznaczy jako 'niepoprawny' o 3 popołudniu - test jest oznaczany jako 'niepoprawny'.</li>
<li>Przypadki testowe wymienine jako 'nie przeprowadzone' w przeciwieństwie do struktury nie są brane pod uwagę. Na przykład, jeżeli zaznaczysz przypadek 
jako 'poprawny' w strukturze 1 i nie przeprowdzisz go w strukturze 2, ostatni wynik będzie określany jako 'poprawny'.</li>
</ul>
<p> Następujące tabale są wyświetlane:</p>
<ul>
	<li><b> Wyniki według najwyższego poziomu zestawów testowych</b>
	Lista wyników, każdego z najważszego poziomu zestawów. Wszystkie przypadki, poprawne, niepoprawne, nieprzeprowadzone i procent ukończonych
	jest wymieniony. 'Wykonane' przypadki testowe to te które zostały oznaczone jako 'poprawne', 'niepoprawne' lub 'zablokowne'.
	Rezultaty dla najwyższego poziomu zestawów zawierają wszystkie zestawy dzieci.</li>
	<li><b>Rezultaty według słów kluczowych</b>
    Lista wszystkich wszystkich słów kluczowych przypisanych do bierzacego planu testu i powiązanych z nim rezultatów. </li>
	<li><b>Rezultaty według właścicela</b>
	Lista według, której każdy właścicel ma przypadki testowe przypisane do swojego planu testu. Przypadki testowe, które 
	są nieprzydzielone są zebrane i określone jako 'nieprzydzielone'.</li>
</ul>

<h3>Ogólny status struktury</h3>
<p> Lista wyników dla przeprowadzonych realizacji każdej struktury. 
Dla każdej struktury, wszystkich przypadków testowych, wszystkich poprawnych,
% poprawnych, wszystkich niepoprawnych, % niepoprawnych, zablokowanych, % zablokowanych, nieprzeprowadzonych, %nieprzeprowadzonych.  Jeżeli test byl przeprowadzony dwukrotnie
na tej samej strukturze, wynik ostatniego przeprowadzonego testu będzie brany pod uwagę.</p>

<h3>Metryka Zapytania </h3>
<p> Ten raport zawiera formularz strony zapytania i stronę wyników zapytania z danymi zapytania.
formularz strony zapytania prezentuje strone zapytania z czteroma czynnikami kontrolnymi.
Każdy czynnik kontrolny jest ustawiony domyślnie zwiększając liczbę przypadków testowych i struktur 
na podstawie których wykonywane są zapytania. Czynniki alarmujące
umożliwiają użytkownikowi filtrowanie rezultatów, generowanie określonych raportów dla określonych własciceli, słów kluczowych, zestawów 
i układów struktur.</p>

<ul>
<li><b>słowa kluczowe</b> 0->1 słowa kluczowe mogą być zaznaczone. Domyślnie, żadne słowo kluczowe nie jest zaznaczone. Jeżeli żadne słowo kluczowo nie jest zaznaczone, 
 wszystkie przypadki testowe będą rozważane bez względu na przypisanie do słowa kluczowego. Słowa kluczowe są przypisane w
specyfikacji testu lub stronach zarządzania słowami kluczowymi. Słowa kluczowe przypisane do przypadków testowych rozciągają się na wszystkie plany testów,
i rozciągają  się na wszystkie wersje przypadku testowego. Jeżeli jesteś zainteresowany rezultatammi dla określonego słowa kluczowego użyjesz tej kontrolki.</li>
<li><b>właściciel</b> 0->1 właściciele mogą byc zaznaczeni. Domyślnie, żaden właścicel nie jest zaznaczony. Jeżeli żaden z właściceli, nie jest zaznaczony
wówczas wszystkie przypadki testowe będą rozpatrywane niezależnie od właściciela.  Obecnie nie ma takiej funkcjonalności jak
wyszukiwanie 'nieprzypisanych' przypadków testowych. Posiadanie jest przypisane poprzez strone 'Przypisz przeprowadzenie przypadku testowego',
i jest wykonane na podstawie podstaw planu testu.  Jeżeli jesteś zainteresowany pracą wykonaną przez określonego testera, wykorzystasz tą opcje kontroli.</li>
<li><b>zestaw najwyższego poziomu</b> 0->n zestawy najwyższego poziomu mogą być zaznaczane. Domyślnie - wszystkie zestawy są zaznaczone.
Tylko zestawy, które zostały zaznaczone będą zapytywane dla uzyskania wyników metryki. Jeżeli jesteś zainteresowany w rezultatach 
dla określonego zestawu użyjesz tej opcji kontroli.</li>
<li><b>Struktury</b> struktury 1->n  mogą zostać wybrane. Domyślnie - wszystkie struktury są zaznaczone. Tylko wykonania 
przeprowadzone na strukturach zaznaczonych przez ciebie będą brane pod uwagę podczas tworzenia metryki.  Na przykład - jeżeli chcesz 
zobaczyć ile przypadków testowych zostało przeprowadzone na ostanich trzech strukturach - użyjesz tej opcji kontroli.
Wybór słowa kluczowego, właściciela i zestaw najwyższego poziomu określi liczbę przypadków testowych dla twojego planu testu
są używane do obliczenia poprzez zestaw i poprzez metrykę planu. Na przyklad, jeżeli określisz wlaściciel  = 'Greg',
Słowo kluczowe='Prioritet 1', i wszystkie dostępne zestawy testowe, które maja pryiorytet 1 przypadki testowe przypisane do Grega będą brane pod uwagę.  
Liczba'# przypadków testowych'liczba, która zobaczysz w raporcie będzie zmienną trzech czynników.
Wybór struktury będzie miał wpływ jeżeli przypadek jest rozważany jako 'poprawny', 'niepoprawny', 'zablokowany' lub 'nieprzeprowadzony' 
Proszę odnieś się do zasad 'Wyników ostatniego testu' takich jak pojawiają się powyżej. </li>
</ul>
<p>Kliknij na przycisk 'Zastosuj' aby kontynuować z zapytaniem i wyświetlić strone produkcyjną. </p>

<p>Na stronie raportu zapytania będzie pokazane: </p>
<ol>
<li>parametry zapytania użyte do stworzenia raportu </li>
<li>podsumowanie całego planu testu</li>
<li>przypadających na strukture podsumowanie wszystkich niepowodzeń  (całości / poprawnych / niepoprawnych / zablokowanych / nieprzeprowadzonych) i wszystich przeprowadzonych testów
na tej strukturze.  Jeżeli test został przeprowadzony więcej niż jeden raz na wielu strukturach - wszystkie wyniki przeprowadzonych testów będą pokazane 
tam gdzie zostały zapisane w przeciwieństwie do struktury. Jednakże, podsumowanie dla zestawu testów będzie 
zawierało jedynie 'wynik ostatniego testu' dla zaznaczonych struktur.</li>
</ol>

<h3>Raporty o Zablokowanych, Niepoprawnych, Nieprzeprowadzonych przypadkach testowych</h3>
<p>Takie raporty pokazują wszystkie zablokowane, niepoprawne lub nieprzeprowadzone przypadki testowe. Logika 'Ostatnich Wyników testu'
(która jest opisana jako ogólna metryka planu testu) jest ponownie stosowana w celu określenia czy
 przypadek testowy jest rozważany jako  zablokowany, niepoprawny lub nieprzeprowadzony. Raporty o przypadkach testowych oznaczonych jako zablokowanych i niepoprawnych będą 
wyświetlane w połaczeniu z błędami jeżeli użytkownik używa zintegrowanego systemu śledzenia błędu.</p>

<h3>Raport testu</h3>
<p>Pokazuje status każdego przypadku testowego na każdej strukturze. Jeżeli na tej samej strukturze wielokrtonie przeprowadzany ten sam przypadek testowy 
to ostani wynik będzie obowiązujacy. W przypadku gdy będzie użyta duża ilośc danych, 
zalecane jest weksportowanie raportu do formatu excel w celu zaprwnienia łatwości przeglądania.</p>

<h3>Wykresy - Ogólna Metryka Planu Testu</h3>
<p>Logika 'Wynik ostatniego testu' jest stosowana dla wszystkich czterech wykresów, które zobaczysz. Wykresy są animacją, która jest stosowana w celu pomocy użytkownikowi 
wizualizacji metryki obecnego planu testu. Cztery wykresy możliwe do zastosowania to :</p>
<ul><li>Wykres kołowy ogólny poprawnych / niepoprawny / zablokowany/ przypadków testowych nieprzeprowadzonych</li>
<li>Wykres słupkowy rezultatów zrealizowany według słowa kluczowego</li>
<li>Wykres słupkowy rezultatów zrealizowany według właścicela</li>
<li>Wykres słupkowy rezultatów zrealizowany według Najwyższego poziomu zestawu</li>
</ul>
<p>Słupki w wykresie słupkowym są pokolorowane tak aby użytkownik mógł szybko oszacować liczbe przypadków testowych 
poprawnych, niepoprawnych, zablokowanych, nieprzeprowadzonych.</p>

<h3>Suma błedów dla przypadku testowego</h3>
<p>Ten raport pokazuje każdy przypadek testowy ze wszystkimi błędami jakie pojawiają się w całym projekcie testu.
Ten raport jest dostępny tylko wtedy gdy jest podłaczony system śledzenia błędu.</p>";


// ------------------------------------------------------------------------------------------
$TLS_htmltext_title['planAddTC']	= "Dodaj /Usuń przypadek testowy z Planu Testu"; // testSetAdd
$TLS_htmltext['planAddTC'] 			= "<h2>Cel:</h2>
<p>Pozwala użytkownikowi (z odpowiednim poziomem dostępu) dodawać lub usuwać przypadki testowe z Planu Testu.</p>

<h2>Dodawanie lub usuwanie przypadków testowych:</h2>
<ol>
	<li>Kliknij na zestawie testowym aby zobaczyć wszystkie zestawy testowe i wszystkie wszystkie przypadki testowe w nim zawarte.</li>
	<li>Kiedy skończysz kliknij na przycisku 'Dodaj / Usuń Przypadek Testowy' w celu dodania lub usunięcia przypadku testowego.
		Uwaga: Nie można wielokrotnie dodać tego samego przypadku testowego.</li>
</ol>";

// ------------------------------------------------------------------------------------------
$TLS_htmltext_title['tc_exec_assignment']	= "Przypisywanie testerów do przeprowadzanych testów";
$TLS_htmltext['tc_exec_assignment'] 		= "<h2>Cel</h2>
<p>Ta strona pozwala kierownikowi testów na przypisanie użytkowników do poszczególnych testów według Planu Testu.</p>

<h2>Rozpoczęcie</h2>
<ol>
	<li>Wybierz przypadek testowy lub zestaw testów do przetestowania.</li>
	<li>Zaznacz zaplanowanego testera.</li>
	<li>Kliknij na przycisk 'Zapisz' w celu zastosowania zmian.</li>
	<li> Otwórz strone wykonania testów w celu weryfikacji. Możesz stworzyć filtr dla użytkowników.</li>
</ol>";

// ------------------------------------------------------------------------------------------
$TLS_htmltext_title['planUpdateTC']	= "Zaktualizuj Przypadek Testowy w Planie Testu";
$TLS_htmltext['planUpdateTC'] 		= "<h2>Cel</h2>
<p>Ta strona pozwala użytkownikowi na uaktalnienie Przypadku Testowego do nowszej (innej) wersji jeżeli Specyfikacja Testu
została zmieniona. Często zdarza się, że pewne funkcjonalności zostają wyjaśnione podczas testowania." .
		" Użytkownicy zmieniają specyfikacje testową, ale plany testowania musza być także przeniesione na Plan Testu. W przeciwnym wypadku Plan" .
		" testu zawiera pierwotną wersje, rezultaty odnoszą się do pierwotnej treści przypadku testowego.</p>

<h2>Rozpoczęcie</h2>
<ol>
	<li>Wybierz przypadek testowy do przetestowania.</li>
	<li>Wybierz nową wersje z menu combo-box dla konkretnego przypadku testowego.</li>
	<li>Kliknij przycisk 'Zaktualizuj Plan Testu' w celu zastosowania zmian.</li>
	<li>W celu weryfikacji: Otwórz strone przeprowadzenia testu, aby zobaczyć treść przypadku testowego.</li>
</ol>";


// ------------------------------------------------------------------------------------------
$TLS_htmltext_title['test_urgency']	= "Określanie testów według wysokiej lub niskiej pilności";
$TLS_htmltext['test_urgency'] 		= "<h2>Cel</h2>
<p>TestLink pozwala  użytkownikowi na ustawnie pilności zestawu testów w celu ustanowienia pierwszeństwa w testowania przypadków testowych.
		Priorytet testu zależy zarówno od ważności przypadku testowego tak samo jak pilności określonej
		w Planie Testu.  Kierownik testu powinien określić zestaw przypadków testowych, które powinny być testowane 
		jako pierwsze. To rozwiązanie pozwala zapewnić, że najbardziej istotne testy zostaną przeprowadzone nie zależnie od stopnia realizacji testów w czasie.</p>

<h2>Rozpoczęcie</h2>
<ol>
	<li> w nawigatorze z lewej strony okna wybierz Zestaw testowy dla cechy danego produktu lub komponentu w celu ustanowienia pilności.</li>
	<li>Wybierz stopień pilności (wysoki, średni lub niski). Średni jest domyślny. Możesz obniżyć priorytet dla nietkniętych części produktu lub
podnieść dla znaczących części.</li>
	<li>Kliknij przycisk 'Zapisz' w celu zapisu zmian.</li>
</ol>
<p><i>Na przykład przypadek testowy z wysoką ważnościa w zestawie testowym oznaczonym jako niski " .
		"będzie miał ważność średnią.</i>";


// ------------------------------------------------------------------------------------------

?>
