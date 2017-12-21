<?php
/**
 * ♔ TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later.
 *
 * German (de_DE) texts for help/instruction pages. Strings for dynamic pages
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
 * @author 		Martin Havlat, Julian Krien
 * @copyright 	2003-2009, TestLink community 
 * @version    	CVS: $Id: texts.php,v 1.14 2010/06/24 17:25:57 asimon83 Exp $
 * @link 		http://www.teamst.org/index.php
 *
 * @internal Revisions:
 * 20100517 - Julian - update of header according to en_GB texts.php
 * 
 * Edit by: devwag00\fixccey 06.05.2014
 *
 * Edit by: sschiele@mesnet.de 2014-08-19
 * Fixed String formatting errors
 **/



// --------------------------------------------------------------------------------------
$TLS_htmltext_title['error']	= "Anwendungsfehler";
$TLS_htmltext['error'] 		= "<p>Es ist ein unerwarteter Fehler aufgetreten. Bitte " .
		"überprüfen Sie den Event Viewer und/oder Log-Dateien für weitere Details." .
		"</p><p>Sie können das Problem gerne melden. Besuchen Sie hierzu bitte unsere " .

		"<a href='http://www.teamst.org'>Webseite</a>.</p>";




// --------------------------------------------------------------------------------------
$TLS_htmltext_title['assignReqs']	= "Zuweisung von Anforderungen an Testfälle";
$TLS_htmltext['assignReqs'] 		= "<h2>Zweck:</h2>
<p>Benutzer können eine Beziehung zwischen Anforderungen und Testfällen herstellen. 
Ein Testfall kann keiner, einer oder mehreren Anforderungen zugewiesen werden oder umgekehrt.
Diese Zuweisungen erlauben es später eine Aussage darüber zu treffen, welche
Anforderungen abhängig von den Testergebnissen erfolgreich umgesetzt wurden.</p>

<h2>Anweisung:</h2>
<ol>
	<li>Wählen Sie einen Testfall im Baum auf der linken Seite. Auf der rechten Seite
	erscheinen dann verfügbaren Anforderungen, denen Sie den Testfall zuweisen können.</li>
	<li>Wählen Sie die gewünschte Anforderungsspezifikation aus der Liste \"Anforderungen
	definieren\". Anschließend wird die Seite neu geladen.</li>
	<li>Es werden nun alle Anforderungen der Anforderungsspezifikation angezeigt.
	In dem Block \"Zugewiesene Anforderungen\" sehen Sie die Anforderungen, die dem Testfall
	bereits zugewiesen wurden. Im Block \"Verfügbare\" Anforderungen sehen Sie die
	Anforderungen, die dem Testfall noch nicht zugewiesen wurden. Wählen Sie die
	Anforderungen, für die Sie eine Testfallzuweisung hinzufügen bzw. entfernen möchten und
	klicken Sie anschließend den entsprechenden Button.</li>

</ol>";

// --------------------------------------------------------------------------------------
$TLS_htmltext_title['editTc']	= "Testfälle bearbeiten";
$TLS_htmltext['editTc'] 		= "<p>Die <i>Testspezifikation</i> ermöglicht das " .
		"Anzeigen und Editieren aller vorhandenen Test Suiten und Testfälle. Testfälle sind " .
		"versioniert. Alle früheren Versionen sind noch für die Einsicht und Verwaltung " .
		"verfügbar.</p>
		
<h2>Anweisung:</h2>
<ol>
	<li>Wähle das <i>Test Projekt</i> in der Baum Navigation (oberster Knoten). <i>Bitte beachten: " .
	"Sie können über die Drop-Down Liste in der Ecke oben rechts das aktive Projekt ändern.</i></li>
	<li>Mit einem Klick auf <b>Neue Testsuite</b> erzeugen Sie eine neue Test Suite. Test Suiten " .
	"strukturieren Test Dokumente entsprechend Ihrer Konventionen (funktionale/ nicht-funktionale " .
	"Tests, Produktkomponenten oder Funktionen, Änderungswünsche etc.). Die Beschreibung einer " .
	"Test Suite kann den Inhalt der eingefügten Testfälle, die Standard Konfiguration ".
	"Verknüpfungen zu relevanten Dokumenten, Beschränkungen und andere nützliche Informationen beinhalten. Im Allgemeinen, " .
	" alle Anmerkungen die Gemeinsamkeiten haben mit den Testfall-Kindknoten. Test Suiten folgen " .
	"dem &quot;Datei/Ornder&quot; System, sodass Nutzer Test Suiten im Test Projekt " .
	"verschieben und kopieren können. Sie können auch importiert und exportiert werden (mit den Testfällen).</li>
	<li>Test Suiten sind skalierbare Ordner. Nutzer können Test Suiten im Test Projekt   " .
	"verschieben und kopieren. Sie können importiert und exportiert werden (mit den Testfällen).
	<li>Durch Auswahl der neu erstellten Test Suite in dem Navigations-Baum können neue Testfälle mit ".
	"<b>Testfälle erstellen</b> erstellt werden. Ein Testfall in einem Test Projekt spezifiziert ein bestimmtes Testszenario, " .
	"die erwarteten Ergebnisse und benutzerdefinierte Felder (siehe Benutzeranleitung). " .
	"Für eine verbesserte Nachvollziehbarkeit ".  
	"(Traceability) können den Testfällen <b>Stichwörter</b> zugewiesen werden.</li>
	<li>In der Baumansicht auf der linken Seite können Sie die Daten bearbeiten. Jeder Testfall speichert den eigenen Verlauf.</li>
	<li>Die erstellte Test Spezifikation kann einem <span class=\"help\" onclick=
	\"javascript:open_help_window('glosary','$locale');\">Test Plan</span> zugewiesen werden, wenn die Testfälle fertig sind.</li>
</ol>

<p>Mit TestLink können Testfälle in Test Suiten gegliedert werden." .
"Test Suiten können in Test Suiten verschachtelt werden, sodass hierarchische Einstufungen möglich sind.
 Diese Information kann dann mit den Testfällen ausgedruckt werden.</p>";


// ------------------------------------------------------------------------------------------
$TLS_htmltext_title['searchTc']	= "Testfälle suchen";
$TLS_htmltext['searchTc'] 		= "<h2>Zweck:</h2>

<p>Suche von Testfällen anhand frei definierbaren Parametern.</p>
<h2>Anweisung:</h2>
<ol>
	<li>Suchbegriff in die entsprechenden Felder der Suchmaske eingeben.</li>
	<li>Stichwörter/Benutzerdefinierte Felder/... wählen.</li>
	<li>'Suchen' Button klicken.</li>
	<li>Testfälle, die den Suchkriterien entsprechen werden angezeigt. Über die Verknüpfung des
	Testfall-Titels können Sie den Testfall ansehen und editieren.</li>
</ol>

<h2>Hinweis:</h2>

<p>- Es werden nur Testfälle innerhalb des aktuellen Testprojekts durchsucht.<br>
- Die Suche ist unabhängig von \"Groß- und Kleinschreibung\".<br>
- Leere Felder der Suchmaske werden nicht berücksichtigt.</p>";





/* contribution by asimon for 2976 */
// requirements search
// ------------------------------------------------------------------------------------------
$TLS_htmltext_title['searchReq']	= "Anforderungen suchen";
$TLS_htmltext['searchReq'] 		= "<h2>Zweck:</h2>

<p>Suche von Anforderungen anhand frei-definierbare Parametern.</p>

<h2>Anweisung:</h2>
<ol>
	<li>Suchbegriff in die entsprechenden Felder der Suchmaske eingeben.</li>
	<li>Status/Typ/Beziehungstyp/Benutzerdefinierte Felder/... wählen.</li>
	<li>Suchen Button klicken.</li>
	<li>Anforderungen, die den Suchkriterien entsprechen werden angezeigt. Über die Verknüpfung des
	Anforderungs-Titels können Sie die Anforderung ansehen und editieren.</li>
</ol>

<h2>Hinweis:</h2>

<p>- Es werden nur Anforderungen innerhalb des aktuellen Testprojekts durchsucht.<br>
- Die Suche ist unabhängig von \"Groß- und Kleinschreibung\".<br>
- Leere Felder der Suchmaske werden nicht berücksichtigt.</p>";

// requirement specification search
// ------------------------------------------------------------------------------------------
$TLS_htmltext_title['searchReqSpec']	= "Anforderungsspezifikationen suchen";
$TLS_htmltext['searchReqSpec'] 		= "<h2>Zweck:</h2>

<p>Suche von Anforderungsspezifikationen anhand frei definierbaren Parametern.</p>

<h2>Anweisung:</h2>

<ol>
	<li>Suchbegriff in die entsprechenden Felder der Suchmaske eingeben.</li>
	<li>Typ/Benutzerdefinierte Felder/... wählen.</li>
	<li>Suchen Button klicken.</li>
	<li>Anforderungsspezifikationen, die den Suchkriterien entsprechen werden angezeigt.
	Über den Link des Anforderungsspezifikationstitels können Sie die Anforderungsspezifikationen
	einsehen und editieren.</li>
</ol>

<h2>Hinweis:</h2>

<p>- Es werden nur Anforderungsspezifikationen innerhalb des aktuellen Testprojekts durchsucht.<br>
- Die Suche ist unabhängig von \"Groß- und Kleinschreibung\".<br>
- Leere Felder der Suchmaske werden nicht berücksichtigt.</p>";
/* end contribution */



// ------------------------------------------------------------------------------------------
$TLS_htmltext_title['printTestSpec']	= "Testspezifikation erstellen"; //printTC.html
$TLS_htmltext['printTestSpec'] 			= "<h2>Zweck:</h2>
<p>Hier können alle Testfälle eines Test Projekts, einer Test Suite oder ein einzelner
Testfall ausgedruckt werden.</p>

<h2>Anweisung:</h2>
<ol>
<li>
<p>Wählen Sie die Teile eines Testfalls aus, die Sie angezeigt haben wollen und klicken Sie
dann auf einen Testfall, eine Test Suite oder ein Test Projekt. Eine druckbare Seitenansicht wird angezeigt.</p>
</li>
<li><p>Ändern Sie über die  \"Zeige als\" drop-box in der Navigationsleiste den Anzeigemodus als
HTML, OpenOffice oder einem Microsoft Word Dokument. 
Die <span class=\"help\" onclick=\"javascript:open_help_window('printFilter',
'{$locale}');\">Hilfe</span> bietet weitere Informationen.</p>
</li>
<li><p>Über den Browser können die Daten dann gedruckt werden.<br />
<i>Hinweis: Achten Sie darauf nur den Rahmen auf der rechten Seite zu drucken.</i></p>
</li>
</ol>";

// ------------------------------------------------------------------------------------------
$TLS_htmltext_title['reqSpecMgmt']	= "Anforderungen definieren"; //printTC.html
$TLS_htmltext['reqSpecMgmt'] 		= "<p>Sie können Anforderungsspezifikations Dokumente verwalten.</p>

<h3>Anforderungspezifikation</h3>
<p>Anforderungen sind nach dem Dokument 'Anforderungspezifikation', welches mit dem Test Plan 
verbunden ist, gruppiert. <br /> TestLink unterstützt noch keine Versionen für beide, die Anf.Spezifikation und 
der Anforderung selbst. Also sollte die Version des Dokuments erst nach der Spezifikation eingefügt werden.
<b>Titel</b>.
Der Nutzer kann in dem Feld <b>Inhalt</b> eine kurze Beschreibung oder Notiz hinzufügen.</p> 



<p><b><a name='total_count'>Überschriebene Anzahl von Anf. </a></b> Dient der Evaluation der Anf. 
Abdeckung, falls nicht alle Anforderungen importiert wurden.  
Der Wert <b>0</b> bedeutet, dass die aktuelle Anzahl der Anf. für Metriken genutzt wird.</p> 
<p><i>Beispielsweise SRS beinhaltet 200 Anforderungen aber nur 50 sind in TestLink hinzugefügt worden. Die Test 
Abdeckung ist 25% (falls alle importierten Anforderungen getestet werden).</i></p>



<h2><a name='req'>Anforderungen</a></h2>

<p>Klicken Sie auf den Namen einer vorhandenen Anforderungs Spezifikation. Falls keine existiert kann mit einem Klick auf den " .
		"Projekt Knoten eine neue erstellt werden. Es können Anforderungen erstellt bearbeitet, gelöscht oder importiert werden.
Jede Anforderung hat einen Titel, Inhalt und Status.
Der Status sollte 'Normal' oder 'nicht testbar' sein. Nicht testbare Anforderungen gehen nicht in die Metriken 
ein. Dieser Parameter kann für noch nicht implementierte Funktionen und falsch entworfene Anforderungen genutzt werden.</p> 

<p>Mit der Nutzung von Multi Aktionen mit abgehakten Anforderungen auf der Spezifikations Umgebung, 
können neue Testfälle für Anforderungen erstellt werden. Diese Testfälle werden der Test Suite mit dem 
konfigurierten Namen  <i>(Standard ist: \$tlCfg->req_cfg->default_testsuite_name =
'Test Suite erstellt über Anforderung - Auto';)</i> hinzugefügt. Titel und Inhalt
werden in die Testfälle kopiert.</p>";

// ------------------------------------------------------------------------------------------
$TLS_htmltext_title['printReqSpec'] = "Anforderungsspezifikation erstellen"; //printReq
$TLS_htmltext['printReqSpec'] = "<h2>Zweck:</h2>
<p>Hier können alle Anforderungen eines Test Projekts, einer Anforderungsspezifikation oder einzelne
Anforderungen ausgedruckt werden.</p>


<h2>Anweisung:</h2>
<ol>
<li>
<p>Wählen Sie die Teile einer Anforderung aus die Sie angezeigt haben wollen und klicken 
dann auf eine Anforderungen, eine Anforderungsspezifikation oder ein Test Projekt.
 Eine druckbare Seitenansicht wird angezeigt.</p>

</li>
<li><p>Ändern Sie über die \"Zeige als\" drop-box in der Navigationsleiste den Anzeigemodus als
HTML, OpenOffice Writer oder einem Microsoft Word Dokument. 
Die <span class=\"help\" onclick=\"javascript:open_help_window('printFilter',
'{$locale}');\">Hilfe</span> bietet weitere Informationen.</p>
</li>
<li><p>Über den Browser können die Daten dann gedruckt werden.<br />
<i>Hinweis: Achten Sie bitte darauf nur den Rahmen auf der rechten Seite zu drucken.</i></p>
</li>
</ol>";

// ------------------------------------------------------------------------------------------
$TLS_htmltext_title['keywordsAssign']	= "Stichwörter zuweisen";
$TLS_htmltext['keywordsAssign'] 			= "<h2>Zweck:</h2>
<p>Auf der Stichwort zuweisen Seite können Nutzer stapelweise Stichwörter den vorhandenen 
Testfällen und Suiten zuweisen.</p>

<h2>Um Stichwörter zuzuweisen:</h2>
<ol>
	<li>Wählen Sie zuerst links in der Baumansicht eine Test Suite oder ein Testfall aus.</li>
	<li>Auf der rechten Seite erscheinen Boxen mit verfügbaren und 
		zugewiesenen Stichwörtern. Damit ist eine schnelle Zuweisung 
		an einzelne Testfälle möglich.</li>
	<li>Die Auswahl auf der unteren Seite erlaubt ein detailliertes Zuweisen 
		an einen Testfall.</li>
</ol>

<h2>Wichtiger Hinweis zu Stichwort Zuweisungen bei Test Plänen:</h2>
<p>Stichwort Zuweisungen, die in der Test Spezifikation gemacht werden, haben nur Auswirkungen 
auf Testfälle des Test Plans und nur wenn der Test Plan die neuste Version des Testfalls
enthält. Wenn der Test Plan eine ältere Version eines Testfalls enthält, erscheinen die gemachten Zuweisungen nicht 
in dem Test Plan.
</p>
<p>Das ist in TestLink so umgesetzt, damit ältere Testfall Versionen im Test Plan nicht von Stichwort
Zuweisungen neuerer Testfall Versionen betroffen sind. 
Testfälle des Test Plans können durch ein klick auf Testfälle aktualisieren aktualisiert werden. Dies 
sollte möglichst vor dem Zuweisen von Stichwörtern geschehen.</p>";


// ------------------------------------------------------------------------------------------
$TLS_htmltext_title['executeTest']	= "Testfälle ausführen";
$TLS_htmltext['executeTest'] 		= "<h2>Zweck:</h2>

<p>Erlaubt es dem Nutzer Testfälle auszuführen. Der Nutzer kann das Testfall Ergebnis 
für ein bestimmtes Build zuweisen. In der Hilfe stehen weitere Informationen über Filter und
Einstellungen. " .
		"(Klicken Sie bitte auf das Fragezeichen Symbol).</p>
<h2>Anweisung:</h2>
<ol>
	<li>Es muss vorher für den Test Plan ein Build definiert sein.</li>
	<li>Wählen Sie bitte ein Build über die Drop-Down Box aus.</li>
	<li>Über Filter-Optionen können Sie die Baumansicht auf einige wenige
		Testfälle reduzieren. Änderungen der Filter-Optionen müssen über 
		die Schaltfläche 'Anwenden' gespeichert werden.</li>	
	<li>Wählen Sie, durch einen Klick, einen Testfall aus der Baumansicht.</li>
	<li>Tragen Sie das Testergebnis und ggf. Notizen oder BUGs ein.</li>
	<li>Speichern</li>
</ol>
<p><i>Hinweis: Sie können Problem-Reports direkt über die Oberfläche erstellen/verfolgen möchten. Dazu muss TestLink 
vorher konfiguriert werden, damit es mit Ihrem BUG-Tracker arbeitet.</i></p>";

// ------------------------------------------------------------------------------------------
$TLS_htmltext_title['showMetrics']	= "Testberichte und Metriken";
$TLS_htmltext['showMetrics'] 		= "<p>Reports sind mit dem Test Plan verbunden" .
		"(definiert in dem oberen Navigator). Dieser Test Plan kann sich vom aktuellen Test Plan 
der Ausführung unterscheiden. Sie können ebenfalls ein Reportformat auswählen:</p>

<ul>
<li><b>Normal</b> - Report wird in der Webseite gezeigt</li>
<li><b>OpenOffice Writer</b> - Report wird im Format OpenOffice Writer importiert</li>
<li><b>OpenOffice Calc</b> - Report wird im Format OpenOffice Calc importiert</li>
<li><b>MS Excel</b> - Report wird im Format Microsoft Excel importiert</li>
<li><b>HTML Email</b> - Report wird an die Email Adresse versendet</li>
<li><b>Diagramme</b> - Report mit Grafiken (Flash Technologie)</li>
</ul>

<p> Die Schaltfläche 'Drucken' druckt nur den Report (ohne Navigation) aus.</p>
<p> Es gibt eine Auswahl von verschieden Reports, deren Zweck und Funktion werden unten erklärt.</p>

<h3>Test Plan</h3>
<p>In dem Dokument 'Test Plan' können Inhalt und Dokumentenstruktur definiert werden. </p>

<h3>Test Report</h3>
<p>In dem Dokument 'Test Report' können Inhalt und Dokumentenstruktur definiert werden. 
Testfälle mit den Ergebnissen sind mit eingeschlossen.</p>

<h3>Allgemeine Test Plan Metriken</h3>
<p>Diese Seite zeigt den neuesten Status eines Test Plans nach Test Suite, Besitzer und Stichwort.
Der 'neueste Status' wird durch das neueste Build, auf dem die Testfälle ausgeführt wurden, bestimmt.
Wenn bspw. ein Testfall auf mehrere Builds ausgeführt wurde, dann wird nur das letzte Ergebnis berücksichtigt.</p>

<p>'Letztes Test Ergebnis' wird in vielen Reports genutzt und ist wie folgt festgelegt:</p>
<ul>
<li>Die Reihenfolge in der Build einem Test Plan hinzugefügt werden, bestimmt welches Build das neueste ist.
Die Ergebnisse des neuesten Builds haben Vorrang vor älteren Builds. Wenn bspw. im Build 1 ein Testfall zuerst als
'Fehlgeschlagen' markiert wird und im Build 2 als 'OK', dann ist das neueste Ergebnis 'OK'.</li>
<li>Wenn ein Testfall mehrmals auf dem selben Build ausgeführt wird, so hat die letzte Ausführung vorrang.
Wenn bspw. das Build 3 dem Team und Tester freigegeben wird und Tester 1 es um 14 Uhr als 'OK' markiert
und Tester 2 um 15 Uhr als 'Fehlgeschlagen' markiert, so erscheint das Ergebnis als 'Fehlgeschlagen'.</li>
<li>Testfälle die als 'nicht getestet' markiert sind werden nicht berücksichtigt. Wenn z.B ein Testfall in 
Build 1 als 'OK' markiert wird und in Build 2 nicht ausgeführt wird, so wird das zuletzt markierte Ergebnis 'OK'
übernommen.</li>
</ul>
<p>Die folgenden Tabellen werden gezeigt:</p>
<ul>
	<li><b>Ergebnis nach Top-Level Test Suiten</b>
	Listet die Ergebnisse von Suites höchster Ebene. Es werden aufgelistet: Alle Testfälle, 'OK', 'Fehlgeschlagen', 
	'Blockiert', 'nicht getestet' und (x-Prozent) vollständig. Vollständige Testfälle sind Testfälle die als 'OK',
	'Fehlgeschlagen' oder 'Blockiert' markiert wurden. 
	Ergebnisse von Suites höchster Ebener beinhalten alle erbenden Kind-Suites.</li>
	<li><b>Ergebnisse nach Schlüsselwörter</b>
	Listet alle Schlüsselwörter und zugehörigen Ergebnisse des aktuellen Test Plans,
	die den Testfällen zugewiesen sind.</li>
	<li><b>Ergebnisse nach Besitzer</b>
	Listet alle Besitzer des aktuellen Test Plans, denen Testfälle zugewiesen wurden.
	Testfälle die nicht zugewiesen wurden erscheinen unter 'nicht zugewiesen'.</li>
</ul>

<h3>Der gesamte Build Status</h3>
<p>Listet die Ergebnisse der Ausführung aller Builds. Jeweils für jedes Build: die Anzahl aller Testfälle,
Anzahl aller Testfälle mit 'OK', % 'OK', Anzahl aller Testfälle mit 'Fehlgeschlagen', % 'Fehlgeschlagen',
 % 'Blockiert' , 'nicht getestet' und % 'nicht getestet'. Wenn ein Testfall mehrmals auf dem selben Build 
ausgeführt wurde, so wird das zuletzt markierte Ergebnis übernommen.</p>

<h3>Query Metriken</h3>
<p>Dieser Report besteht aus einer Query Form Seite und einer Query Ergebnis Seite, die die befragten Daten enthält.
Die Query Form Seite ist eine Query Seite mit vier Bedienelementen. Jedem Bedienelement ist ein Standardwert
zugeordnet, der die Anzahl an angefragt Testfällen und Builds maximiert. Die Änderung der 
Bedienelemnte erlaubt es dem Nutzer die Ergebnisse zu filtern und spezifische Reports für bestimmte Besitzer,
Stichwörter, Suiten und Build Kombinationen zu generieren.</p>

<ul>
<li><b>Stichwörter</b> 0->1 Stichwörter können ausgewählt werden. Standardmäßig sind keine Stichwörter ausgewählt. 
Ist ein Stichwort nicht ausgewählt, werden alle Testfälle unabhängig von Stichwort Zuweisungen berücksichtigt.
Stichwörter sind in der Test Spezifikation oder Stichwort Verwaltung zugeordnet. Stichwort Zuweisungen bei Testfällen
umfassen alle Test Pläne und alle Testfall-Versionen. Wenn Sie an Testergebnissen für ein bestimmtes Stichwort interessiert sind, 
dann können Sie dieses Bedienelement ändern.</li>
<li><b>Besitzer</b> 0->1 Besitzer können ausgewählt werden. Standardmäßig sind keine Besitzer ausgewählt. 
Ist kein Besitzer ausgewählt, werden alle Testfälle unabhängig von Besitzer Zuweisungen berücksichtigt.
Zurzeit gibt es keine Funktion um nach nicht zugewiesenen Testfällen zu suchen. Besitzern werden Testfälle per Test Plan 
über die 'Testfälle an Benutzer zuweisen' Seite zugewiesen. Wenn Sie an Testergebnissen eines bestimmtes Testers interessiert sind, 
dann können Sie dieses Bedienelement ändern.</li>
<li><b>Top-Level Suite</b> 0->n Suiten höchster Ebene können ausgewählt werden. Standardmäßig sind alle Suiten
ausgewählt. Nur Suiten die selektiert sind, werden für die Ergebnis-Metriken abgefragt. Wenn Sie nur an einer 
bestimmten Suite interessiert sind, dann sollte Sie dieses Bedienelement geändert werden.</li>
<li><b>Builds</b> 1->n Builds können ausgewählt werden. Standardmäßig sind alle ausgewählt. Für Metriken werden nur 
die selektierten Ausführungen eines Builds berücksichtigt. 
Wenn Sie bspw. wissen möchten wieviele Testfälle auf den letzten 3 Builds ausgeführt wurden, sollte dieses 
Bedienelement geändert werden. 
Die Auswahl der Stichwörter, Besitzer und Top Level Suite schreibt die Anzahl der Testfälle eines Test Plans vor, 
womit die Metrik per Suite oder Metrik per Test Plan berechnet werden.
Wenn z.B der Besitzer 'Greg', Stichwort='Priorität 1', und alle wählbaren Test Suiten ausgewählt sind, werden nur Priorität 1 Testfälle,
die Greg zugewiesen sind berücksichtigt. Die Anzahl der Testfälle im Test Report werden durch diese 3 Bedienelemente
beeinflusst. 
Die Auswahl der Builds beeinflusst ob ein Testfall als 'OK', 'Fehlgeschlagen', 'Blockiert', oder 'nicht getestet' gilt. 
Bitte sehen Sie nach unter 'Letztes Test Ergebnis'.</li>
</ul>
<p>Klicken Sie das Element 'Abschicken' an um mit der Anfrage fortzufahren und die Ausgabe zu betrachten.</p>

<p>Query Report Seite zeigt an: </p>
<ol>
<li>Die Anfrage Parameter die genutzt wurden, um den Bericht zu erstellen </li>
<li>Summe für den gesamten Test Plan</li>
<li>ein per Test Suite Abbau aller (Summe / bestandenen / fehlgeschlagenen / blockierten / nicht getesten)
und aller Ausführungen. Wenn eine Testfall auf mehreren Builds ausgeführt wurde werden alle Ausführungen der
ausgewählten Builds angezeigt. Allerdings, die Zusammenfassung der Suite beinhaltet nur das Letzte Testergebnis 
des ausgewählten Builds.</li>
</ol>

<h3>Blockierte, fehlgeschlagene und nicht getestete Testfall Reports</h3>
<p>Diese Reports zeigen alle zurzeit blockierten, fehlerhaften oder nicht getesteten Testfälle an. Die 
'Letztes Test Ergebnis' Logik (welches oben unter 'Allgemeine Test Plan Metriken' beschrieben ist) 
wird angewendet um zu bestimmen ob  ein Testfall als 'Fehlgeschlagen', 'Blockiert', oder 'nicht getestet' 
betrachtet werden soll. Testergebnisse von fehlgeschlagenen und blockierten Testfällen zeigen 
die zugehörigen BUGs an, falls ein BUG Tracking System genutzt wird.</p>

<h3>Test Reports</h3>
<p>Zeigt den Status von allen Testfällen der Builds. Wenn mehrere Testfälle auf dem selben Build ausgeführt 
wurde dann wird das aktuellste Test Ergebnis angezeigt. Es wird empfohlen den Bericht in das Excel-Format 
zu exportieren, um bei großen Datenmengen die Daten einfacher durchzusehen.</p>

<h3>Charts - Allgemeine Test Plan Metriken</h3>
<p>Die 'Letztes Test Ergebnis' Logik wird bei allen vier angezeigten Charts angewendet
Die Grafiken sind animiert, um sich die Metriken des aktuellen Test Plans zu einfach vorzustellen zu können.
Die vier Charts sind:</p>
<ul><li>Kreisdiagramm insgesamt von bestandenen / fehlgeschlagenen / blockierten / nicht getesteten Testfällen</li>
<li>Balkendiagramm der Ergebnisse nach Stichwörtern</li>
<li>Balkendiagramm der Ergebnisse nach Besitzer</li>
<li>Balkendiagramm der Ergebnisse nach Suiten höchster Ebene</li>
</ul>
<p>Die Balken des Balkendiagramms sind farblich markiert, sodass der Nutzer die ungefähre Anzahl von
bestandenen, fehlgeschlagenen, blockierten, nicht getesteten Testfällen erkennen kann.</p>

<h3>Gesamtanzahl der Bugs für jeden Testfall</h3>
<p> Dieser Bericht zeigt den jeden Testfall mit allen für das ganze Projekt geordneten BUGs. Dieser 
Bericht ist nur verfügbar wenn ein BUG Tracking System verbunden ist.</p>";


// ------------------------------------------------------------------------------------------
$TLS_htmltext_title['planAddTC']	= "Testfälle hinzufügen / entfernen"; // testSetAdd
$TLS_htmltext['planAddTC'] 			= "<h2>Zweck:</h2>
<p>Nutzer mit entsprechenden Rechten können dem Testplan Testfälle hinzufügen oder Testfälle
aus dem Testplan entfernen.</p>

<h2>Anweisung:</h2>
<ol>
	<li>Wählen Sie im Baum auf der linken Seite eine Test Suite aus, um alle in der Test Suite 
	enthaltenen Testfälle angezeigt zu bekommen.</li>
	<li>Wählen Sie alle Testfälle, die Sie hinzufügen bzw. entfernen wollen und klicken Sie
	auf den \"Hinzufügen / Entfernen der ausgewählten Testfälle\" Button</li>
</ol>";

// ------------------------------------------------------------------------------------------
$TLS_htmltext_title['tc_exec_assignment']	= "Testfälle an Benutzer zuweisen";
$TLS_htmltext['tc_exec_assignment'] 		= "<h2>Zweck:</h2>
<p>Diese Seite erlaubt es Leitern den Nutzern bestimmte Tests im Test Plan zuzuweisen.</p>

<h2>Anweisung:</h2>
<ol>
	<li>Wählen Sie eine zu testende Test Suite oder Testfall aus.</li>
	<li>Wählen Sie einen Tester aus.</li>
	<li>Über die Schaltfläche 'Speichern' wird die Zuweisung übernommen.</li>
	<li>Öffnen Sie die Ausführungs-Seite um die Zuweisung zu verifizieren. Es ist möglich nach 
		Nutzern zu Filtern.</li>
</ol>

<h2>Entziehen aller Testfälle:</h2>
<ol>
	<li>Klicken Sie den obersten Knoten in der Baumansicht (Test Projekt) an.</li>
	<li>Sind Testfälle zugewiesen, erscheint eine Schaltfläche worüber die Zuweisung der Testfällen
		entzogen werden kann.
		Nach einem Klick auf die Schaltfläche sind alle Testfälle nicht mehr zugewiesen.</li>
</ol>";

// ------------------------------------------------------------------------------------------
$TLS_htmltext_title['planUpdateTC']	= "Verlinkte Testfälle aktualisieren";
$TLS_htmltext['planUpdateTC'] 		= "<h2>Zweck</h2>
<p>Diese Seite erlaubt das Aktualisieren von Testfällen auf eine neue (andere) Version, wenn die Test Spezifikation
sich geändert hat. Oft klären sich Funktionalitäten während dem Testen." .
		" Der Nutzer ändert die Test Spezifikation, jedoch müssen Änderungen im Test Plans übernommen werden. Andernfalls" .
		" wird die originale Version im Test Plan behalten, um den richtigen Bezug der Testergebnisse auf den korrekten ". 
		"Text eines Testfalls sicherzustellen.</p>

<h2>Anweisung:</h2>
<ol>
	<li>Wählen Sie eine zu testende Test Suite oder Testfall aus.</li>
	<li>Wählen Sie die neue Version des bestimmten Testfalls über die Kombo-Box aus.</li>
	<li>Klicken Sie auf 'Aktualisiere Test Plan' um die Änderungen zu übernehmen.</li>
	<li>Um zu prüfen: Öffnen Sie die Ausführungs-Seite, um die Texte der Testfälle zu betrachten.</li>
</ol>";

// ------------------------------------------------------------------------------------------
$TLS_htmltext_title['test_urgency']	= "Dringlichkeit der Tests bestimmen";
$TLS_htmltext['test_urgency'] 		= "<h2>Zweck:</h2>
<p>Um die Testprioritäten von Testfällen vorzugeben, ist es in TestLink möglich die Dringlichkeit einer Test Suite zu setzen.
		Die Testpriorität hängt sowohl von der Wichtigkeit der Testfälle als auch von der im Test Plan definierten 
		Dringlichkeit ab. Der Test Leiter sollte einen Satz von Testfällen spezifizieren, die als erstes
		getestet werden können. Das hilft sicherzustellen, dass auch beim Testen unter Zeitdruck die wichtigsten Tests
		berücksichtigt werden.</p>

<h2>Anweisung:</h2>
<ol>
	<li>Wählen Sie im Navigator auf der linken Fensterseite eine Test Suite aus, um die Dringlichkeit
		eines Produkts/Bauteilmerkmals zu setzen. </li>
	<li>Wählen Sie ein Dringlichkeits Niveau (hoch, mittel oder niedrig) aus. Mittel ist der Standardwert.
	Sie können die Priorität für unberührte Teile Produkts vermindern und für Bauteile mit 
	signifikanten Änderungen steigern.</li>
	<li>Klicken Sie auf 'Speichern', um die Änderungen zu übernehmen.</li>
</ol>
<p><i>Zum Beispiel: Ein Testfall mit einer hohen Wichtigkeit in einer Test Suite mit niedriger Dringlichkeit " .
		"bekommt mittlere Priorität.</i>";

// ------------------------------------------------------------------------------------------
$TLS_htmltext_title['planAddTcDocumentation'] = "Plan add testcase documentation";
$TLS_htmltext['planAddTcDocumentation'] = "<h2>@TODO Plan add testcase documentation</h2>";

?>
