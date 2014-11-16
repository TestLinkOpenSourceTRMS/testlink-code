<?php
/** -------------------------------------------------------------------------------------
 * ♔ TestLink Open Source Project - http://testlink.sourceforge.net/
 * 
 * Filename $RCSfile: description.php,v $
 * @version $Revision: 1.3 $
 * @modified $Date: 2010/06/24 17:25:55 $ $Author: asimon83 $
 * @author Martin Havlat
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
 * The initial data are based on help files stored in gui/help/<lang>/ directory. 
 * This directory is obsolete now. It serves as source for localization contributors only. 
 *
 * ----------------------------------------------------------------------------------- */

// printFilter.html
$TLS_hlp_generateDocOptions = "<h2>Opcje dokumentu</h2>

<p> Ta tabela, umożliwia użytkownikowi selekcje przypadków testowych przed ich wyświetleniem. 
Znaczone pola będą pokazane w wygenerowanym dokumencie.
W celu zmiany wyświetlanych informacji, zaznacz lub odznacz wybrane pola, następnie kliknij przycisk Filtruj, 
w dalszej kolejności na drzewie, w dolnej części okna zaznacz odpowiedni poziom szczegółów zawartych w dokumencie. 
</p>

<p><b>Nagłówek dokumentu:</b> Użytkownik ma możliwość przefiltrowania nagłówków dokumentu.
Nagłówek powinien zawierać informacje takie jak: wprowadzenie, zakres, odnośniki do dokumentów, 
metodologie testów, ograniczenia testów.</p>

<p><b>Budowa przypadków testowych:</b> Użytkownik może przefiltrować informacje wchodzące w skład budowy głównej części przypadku testowego. 
W skład budowy przypadku testowego wchodzą: Cel przypadku testowego, kroki i oczekiwane rezultaty, 
autor przypadku, pola niestandardowe, powiązane słowa kluczowe i wymagania.</p>

<p><b>Podsumowanie testu:</b> Użytkownik ma możliwość przefiltrowania samego celu testu poprzez tytuł przypadku testowego, 
ale nie zostaną w ten sposób wyłączone pozostałe informacje z budowy głównej części przypadku testowego.
Podsumowanie testu zostało tylko częściowo oddzielone od budowy przypadku testowego, tak aby była możliwość wyświetlania przypadków 
tylko w formie tytułu i celu testu (bez wyświetlania kroków). 
Jeśli użytkownik wyświetli tylko główną część testu, będzie w nim zawarty cel testu .</p>

<p><b>Spis treści:</b> Opcja ta tworzy listę wszystkich tytułów przypadków testowych 
z wewnętrznymi linkami w postaci hipertekstu (niezależne fragmenty tekstu połączone hiperlinkami).</p>

<p><b>Format wyjściowy:</b> Istnieją dwie możliwości: HTML i MS Word.  
Przeglądarki odwołują sie do komponentu MS Word w drugiej kolejności.</p>";

// testPlan.html
$TLS_hlp_testPlan = "<h2>Plan testu</h2>

<h3>Ogólne zasady działania</h3>
<p> Plan testu jest systemowym podejściem do testowania oprogramowania. Możesz ułożyć testowanie poszczególnych struktur produktów pod względem czasu i osiągniętych rezultatów.</p>

<h3>Przeprowadzanie testu</h3>
<p>Fragment ten jest tym gdzie użytkownik, może przeprowadzić przypadki testowe (opisać rezultaty testu) i 
wydrukować  strukture przypadku testowego jako plan testu. Fragment ten  umożliwia  użytkownikowi śledzenie rezultatów przeprowadzanych przez niego przypadków testowych. 
</p> 

<h2>Zarządzanie planem testu</h2>
<p>Ten fragment, który jest dostępny tylko dla zarzadzających projektem, pozwala użytkownikowi na administrowanie planem testu. 
Administrowanie planem testu zawiera tworzenie/edytowanie/usuwanie planów, 
dodawanie/edytowanie/usuwanie/aktualizacje przypadków testowych w planie testu, tworzenie struktur tak samo jak określenie, kto może obejrzeć który plan.<br />
Użytkownicy z pozwoleniem  zarzącającego projektem mogą także  określić wagę/ryzyko i  przynależność  
struktury  przypadku testowego (kategorie) oraz  tworzenie kroków przypadku testowego.</p> 

<p>Uwaga: Jest możliwe, że użytkownicy  mogą nie widzieć  listy dropdown zawierającej wszystkie plany testów. 
W takiej systuacji wszystkie linki  (za wyjątkiem tych, które zostałe aktywowane przez zarządzajacego projektem) będą nie aktywne. Jeżeli jesteś w takiej sytuacji skontaktuj się z zarządzajacym projektem 
lub administratorem  w celu udostępnienia  odpowiednich praw do projektu lub stworzenia planu  test dla ciebie.</p>"; 

// custom_fields.html
$TLS_hlp_customFields = "<h2>pola niestandardowe</h2>
<p> Klika faktów odnośnie wprowadzania pól niestandardowych:</p>
<ul>
<li>pola niestandardowe  są definiowane  na przestrzeni całego systemu</li>
<li>pola niestandardowe  są powiązane do  typu elementu  (struktury testu , przypadku testowego)</li>
<li>pola niestandardowe mogą być powiązane do wielu  planów testów.</li>
<li>Kolejność pojawiania się pól niestandardowych może być różna w zależności od projektu testu.</li>
<li>pola niestandardowe mogą być nieaktywne dla poszczególnych przypadków testowych.</li>
<li>Liczba pól niestandardowych jest nieograniczona</li>
</ul>

<p>Definicja pola niestandardowe składa się z poszczególnych właściwości:</p>
<ul>
<li>Nazwa pola uzytkownika</li>
<li>Nagłówek,  zmiennej nazwy ( np. To jest wartość według formatu lang_get() API lub inna dowolna jeżeli nie jest odnaleziona w pliku tekstowym).</li>
<li>Typ pola ustawień użytkownika (string, numeryczne, email, enum, float)</li>
<li>Wartości pól numerycznych ( np. CZERWONY|ŻÓŁTY|NIEBIESKI) odpowiednio do listy, pole wielokrotnego wyboru i typy mieszane.<br />
<i>Użyj znaku pipe '|'  aby
 odseparować poszczególne wartości według ponumerowania. Możliwe, że to będzie puste pole.</i>
</li>
<li>Wartość domyślna ' NIE ZOSTAŁA JESZCZE WPROWADZONA'</li>
<li>Maksymalna/minimalna długość ustawień pola niestandardowe ( wartość '0' jest używana do deaktywacji pola). (NIE ZOSTAŁA JESZCZE WPROWADZONA)</li>
<li>Standardowe określenie w celu walidacji wartości wprowadzonych przez użytkownika
(use <a href=\"http://au.php.net/manual/en/function.ereg.php\">ereg()</a>
syntax). <b>(NIE ZOSTAŁA JESZCZE WPROWADZONA)</b></li>
<li>Wszystkie pola ustawień użytkwnika są obecnie zapisane w bazie danych jako VARCHAR(255).</li>
<li>Wyświetlanie specyfikacji testu.</li>
<li>Uruchomienie specyfikacji testu. Użytkownik może zmienić wartość w trakcie tworzenia specyfikacji przypadku testowego.</li>
<li>Wyświetlanie procedury jak przeprowadzany jest test.</li>
<li>Uruchomienie procedury według której przeprowadzany jest test. Użytkownik może zmienić wartość w trakcie przeprowadzania przypadku testowego.</li>
<li>Wyświetlanie projektu planu testu.</li>
<li>Uruchomienie projektu planu testu. Użytkownik może zmienić wartość w trakcie tworzenia planu testu ( dodawać przypadek testowy do planu testu)</li>
<li>Dostępność dla. Użytkownik wybiera  rodzaju pola do którego przynależy wartość.</li>
</ul>
";

// execMain.html
$TLS_hlp_executeMain = "<h2>Przeprowadzanie przypadku testowego</h2>
<p>Przeprowadzanie przypadku testowego. Samo przeprowadzanie przypadku testowego jest w niewielkim stopniu
 powiązane od rezultatu przypadku testowego (poprawny, niepoprawny, wstrzymany) w przeciwieństwie do wyznaczonego elementu budowy.</p>
<p>Można stworzyć dostęp do systemu śledzenie błędu. Użytkownik może bezpośrednio dodawać zgłoszenia o nowych błędach
 i wyszukiwać zgłoszenia o starych.</p>";

//bug_add.html
$TLS_hlp_btsIntegration = "<h2>Dodawanie informacji o błędzie do przypadku testowego</h2>
<p><i>(tylko jeżeli jest skonfigurowane)</i>
TestLink ma bardzo proste powiądzenie z systemem śledzenia błędu Bug Tracking Systems (BTS),
jednakże Test Link nie może  stworzyć zgłoszenia o błędzie oraz nie może uzyskać informacji o ID zgłoszonego błędu.
Powiązanie jest zrobione poprzez linki do strony BTS odwołujących się do poszczególnych funkcji:
<ul>
	<li>Umieść nowy błąd.</li>
	<li>Wyświetl informacje o istniejącym błędzie. </li>
</ul>
</p>  

<h3>Procedura dodania błędu</h3>
<p>
   <ul>
   <li>Krok 1: otworzyć BTS przy pomocy linku, w celu umieszczenia nowego błędu </li>
   <li>Krok 2: zapisać BUGID/ numer błędu nadany przez BTS.</li>
   <li>Krok 3: wpisać  BUGID w rubryce.</li>
   <li>Krok 4: użyć przycisku dodaj błąd.</li>
   </ul>  

Po zamknięciu strony z dodawaniem nowego błędu, zobaczysz odpowiednie informacje o błędzie.
</p>";

// execFilter.html
$TLS_hlp_executeFilter = "<h2> Tworzenie filtra i budowy dla przeprowadzenia testu.</h2>

<p>Lewe okienko zawiera opcje zarządzania przypisanymi przypadkami testowymi" .
"Plan testu i tabela z ustawieniami oraz filtrem. Filtry, które są udostepniane użytkownikowi " .
"w celu ulepszenia oferowanego zestawu " .
"Ustaw swój filtr, przycisnij 'Zatwierdź' i  zaznacz odpowiedni przypadek testowy " .
"z menu drzewa .</p>

<h3>Struktura</h3>
<p>Użytkownicy muszą wybrać strukture, która będzie powiązana z wynikami testu. " .
"struktury są podstawowym komponentem dla bieżącego planu testu. Każdy przypadek testowy " .
"może być przeprowadzony więcej niż jeden raz jeżeli to wynika, z jego struktury. Jednakże liczy się tylko ostatni wynik. 
<br />Struktura może być tworzona poprzez zarzadzajacego projektem przez strone umożliwiającą utworzenie nowej struktury.</p>

<h3>Filtr Numeru ID</h3>
<p>Użytkownicy mogą filtrować przypadek testowy poprez indywidualny numer ID. Numer ID jest nadawany automatycznie w czasie tworzenia przypadku testowego. Pusty pasek filtra oznacza, że test nie jest obowiązujący.</p> 

<h3> Filtr Wagi</h3>
<p>Użytkownicy moga filtrować przypadek testowy  w zależności od jego wagi. Waga testu składa się z kilku zmiennych" .
"to jak test jest pilny wynika z bieżacego planu testu.  Na przykład pryioryet 'WYSOKI' przypadku testowego" .
"Na przykład pryioryet 'WYSOKI' " . " jest wyznaczony w przypadkach gdy pilność lub ważność oznaczona jako 'WYSOKA', a drugi czynnik oznaczony przynajmniej 'ŚREDNI'.</p> 

<h2>Filtrowanie rezultatów</h2>
<p>Użytkownicy mogą filtrować przypadki testowe poprzez rezultaty. Rezultaty są wynikem z wykonania określonej struktury przypadku testowego.
Przypadki testowe moga być (poprawne, niepoprawne, zablokowane, nieprzeprowadzone)." .
"Ten filtr jest domyślnie wyłączony.</p>

<h3>Filtr użytkownika</h3>
<p>Użytkownicy mogą filtrować przypadek testowy pod wględem komu zostały przypisane. Check-box przewiduje także opcję " .  
"\" nieprzpisane\" testy, które w rezultatach są umieszczane jako dodatkowe.</p>";
/*
<h2>Najczęstsze wyniki</h2>
<p> 'Najczęstsze wyniki' według  ustawień domyślnych ta opcja jest odznaczona check-boxem,  wówczas drzewko będzie ułożone 
zgodnie ze strukturą wybraną z listy dropdown box. W takiej sytuacji drzewko będzie ułożone 
ze względu status przypadku testowego. 
<br />Przykład: Użytkownik, zaznaczył strukture 2 z listy dropdown box i jednocześnie zaznaczy checkbox opcje
'Najczęstsze wyniki'. Wszystkie  przypadki testowe są wyświetlane uwzględniając ich status w strukturze 2. 
Więc, jeżeli przypadek testowy 1 będzie określony jako poprawny w strukturze 2 będzie zaznaczony na zielono.
<br />Jeżeli użytkownik zdecyduje się zaznaczyć 'najbardziej bieżące' poprzez checkbox drzewko będzie 
oznaczone kolorem takim jakim oznaczone są najcześciej przypadki testowe.
<br />Przykład: Użytkownik zaznaczył strukture 2 z dropdown box i zaznaczył  poprzez checkbox opcje
 'najbardziej bierzące' przypadki testowe. Wtedy zostaną pokazane wszystkie przypadki testowe ze statusem   
'najbardziej bierzące'. Więc, jeżeli przypadek testowy 1 jest poprawny w strukturze 3, wtedy jeżeli użytkownik zaznaczy przypadek w strukturze 2
, to przypadek będzie oznaczony na zielono.</p>
 */


// newest_tcversions.html
$TLS_hlp_planTcModified = "<h2>Najnowsza wersja powiązanych przypadków testowych</h2>
<p> Jest analizowany cały zestaw przypadków testowych powiązanych do planu testu i lista najnowszych przypadków testowych 
 (w zakresie  bieżącego zestawu planu testu).
</p>";


// requirementsCoverage.html
$TLS_hlp_requirementsCoverage = "<h3>Zaspokojenie wymagań</h3>
<br />
<p>Własność systemu, która pozwala na dokumentacje pokrycia wymagań systemu lub użytkownika, przez przypadki testowe.
. Zarzadzane poprzez link \"Wymagania\" w głównym oknie.</p>

<h3>Zestawy wymagań</h3>
<p>Wymagania są zebrane w 'Zestawach wymagań' dokumencie powiązanym z
projektem testu.<br /> TestLink nie obejmuje jednocześnie zestawu wymagań,  
a także wymagań samych w sobie. Więc, wersja dokumentu  powinna być dodana po 
specyfikacji <b>Tytuł</b>.
Użytkownik może dodać prosty opis notatki do <b>szkicu</b> pola.</p> 

<p><b><a name='total_count'>Przypisana liczba wymagań</a></b> służy do
oceny pokrycia wymagań,w przypadku kiedy nie wszystkie wymagania są dodane (zaimportowane). 
Wartość <b>0</b> oznacza bieżącą liczbę wymagań używaną  w celu  określenia metryki.</p> 
<p><i>Przykład SRS zawiera 200 wymagań ale tylko 50 jest dodane do TestLink. Pokrycie testu jest 25%
 (jeżeli wszystkie te wymagania będą dodane).</i></p>

<h3><a name=\"req\">Wymagania</a></h3>
<p>Kliknij na tytule w celu stworzenia specyfikacji wymagań. Możesz tworzyć, edytować, usuwać
lub importować zestaw wymagań dla dokumentu. Każde z wymagań ma tytuł, szkicu i status.
Status powinien być  \"Normalny\" lub \"Nietestowalny\". Nie testowalne wymagania nie są włączane do metryki.
Ten parametr powinien być użyty dla niezastosowanych cech i żle zaprojektowanych wymagań.</p> 

<p> Możesz stworzyć nowy przypadek testowy. Możesz stworzyć nowe przypadeki testowe dla wymagań poprzez użycie  wielo wątkowej akcji z  zaznaczonymi
wymaganiami w oknie specyfikacji. Te przypadki testowe są stworzone w schemacie testu
z nazwą określoną w definicji <i>(default is: &#36;tlCfg->req_cfg->default_testsuite_name = 
\" Zestaw testowy stworzony według wymagań  - Auto\";)</i>. Tytuł i szkic są kopiowane do tych przypadków testowych.</p>
";


// planAddTC_m1.tpl
$TLS_hlp_planAddTC = "<h2>Odnośnie 'Zapisz pola niestandardowe'</h2>
Jeżeli masz zdefiniowane i przypisane do Projektu Testu,<br /> 
pola niestandardowe z:<br />
 'Display on test plan design=true' and <br />
 'Enable on test plan design=true'<br />
Zobaczysz tą strone tylko dla tych przypadków testowych, które są przypisane do planu testu.
";

// xxx.html
//$TLS_hlp_xxx = "";

// ----- END ------------------------------------------------------------------
?>