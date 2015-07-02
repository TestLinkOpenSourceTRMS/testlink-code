<?php
/** 
 * ♔ TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later. 
 * 
 * Localization: German (de_DE) descriptions
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
 * 
 * @package 	TestLink
 * @author 		Martin Havlat, Julian Krien
 * @copyright 	2003-2009, TestLink community 
 * @version    	CVS: $Id: description.php,v 1.10 2010/09/13 09:52:42 mx-julian Exp $
 * @link 		http://www.teamst.org/index.php
 *
 * @internal Revisions:
 * Last Update according to en_GB description file with commit id 95ceb5362e5f3153fe224a0137981623077bce4f
 * 
 * Edit by: devwag00\fixccey 06.05.2014
 *
 **/


// printFilter.html
$TLS_hlp_generateDocOptions = "<h2>Optionen für das zu generierende Dokument</h2>

<p>Diese Optionen erlauben es dem Benutzer Testfälle zu filtern bevor sie angezeigt werden. Wird das 
Häkchen gesetzt, werden die Daten angezeigt. Um die angezeigten Daten zu verändern, de-/aktiveren Sie das
Feld, klicken Sie den Filter an und selektieren die gewünschte Datenstufe des Baums. </p>

<p><b>Dokument Header:</b> Benutzer können Dokument-Header-Informationen filtern. 
Dokument-Header-Informationen umfassen: Einleitungen, Inhalte, Referenzen, 
Test Methodologien und Test Einschränkungen.</p>

<p><b>Testfall-Inhalt:</b> Benutzer können den Testfall-Inhalt-Informationen filtern. Testfall-Inhalt-Informationen
umfassen: Zusammenfassungen, Testschritten, erwartete Ergebnisse und Schlüsselwörter.</p>

<p><b>Testfall Zusammenfassung:</b> Benutzer können Informationen der Testfall-Zusammenfassung aus dem Testfall-Titel filtern, 
jedoch nicht aus dem Testfall-Inhalt. Für eine kurze Zusammenfassung mit Titel (ohne Testschritte, 
erwartete Ergebnisse und Schlüsselwörter) ist die Testfall-Zusammenfassung nur Teilweise vom 
Testfall-Inhalt getrennt. Wenn der Benutzer sich entscheidet den Testfall-Inhalt
anzuschauen, wird die Testfall-Zusammenfassung mit angezeigt.</p>

<p><b>Inhaltsverzeichnis:</b> TestLink fügt eine Liste aller Titel mit internen Hypertext-Links hinzu, wenn diese 
Option aktiviert ist.</p>

<p><b>Ausgabeformat:</b>  Es gibt zwei Möglichkeiten: HTML und MS Word. Der Browser ruft MS Word im zweiten Fall auf.</p>";

// testPlan.html
$TLS_hlp_testPlan = "<h2>Test Plan</h2>

<h3>Allgemein</h3>
<p>Ein Test Plan ist ein systematischer Ansatz Software zu prüfen.
Sie können Testaktivitäten von Builds verwalten und Ergebnisse verfolgen.</p>


<h3>Testausführung</h3>
<p>In diesem Abschnitt können Benutzer Testfälle ausführen, Test Ergebnisse erzeugen 
und die Test-Suite des Test Plans drucken. Ebenfalls können Sie die Ergebnisse 
ihrer Testausführung verfolgen.</p> 

<h2>Test Plan Verwaltung</h2>
<p>In diesem Abschnitt, der nur gesondert zugänglich ist, können Test Pläne administriert werden. 
Test Plan Administratoren können Test Pläne erzeugen, bearbeiten und löschen sowie Testfälle 
hinzufügen, bearbeiten, löschen und aktualisieren. Außerdem können Builds erzeugt werden und 
Benutzern den Zugriff auf ausgewählte Test Pläne ermöglichen.<br />
Benutzer mit entsprechender Berechtigung können Priorität und Risiko bestimmen, den Besitz
an Test-Suiten (Kategorien) erwerben und Testmeilensteine erstellen.</p> 

<p>Hinweis: Es ist möglich, dass ein leeres Dropdown ohne Test Plan erscheint. 
In diesem Fall werden alle Verknüpfungen (außer die freigegebenen) getrennt. Wenn das der Fall ist,
 setzen Sie sich bitte mit einem Admin in Verbindung. Dieser kann Ihnen die nötigen 
Zugriffsrechte gewähren oder einen Test Plan für Sie erstellen.</p>"; 

















// custom_fields.html
$TLS_hlp_customFields = "<h2>Benutzerdefiniertes Feld</h2>
<p>Einige Fakten über die Implementierung von benutzerdefinierten Feldern:</p>
<ul>
<li>Benutzerdefinierte Felder sind System übergreifend definiert.</li>
<li>Benutzerdefinierte Felder sind mit Elementen (Test Suite, Testfall) verknüpft.</li>
<li>Benutzerdefinierte Felder können mit mehreren Test-Projekten verknüpft werden.</li>
<li>Die Sequenz von benutzerdefinierten Feldern kann sich per Test Projekt unterscheiden.</li>
<li>Benutzerdefinierte Felder können für spezifische Test Projekte deaktiviert werden.</li>
<li>Die Anzahl von benutzerdefinierten Feldern ist unbegrenzt.</li>
</ul>

<p>Benutzerdefinierten Felder haben die folgenden logischen Attribute:</p>
<ul>
<li>Name des benutzerdefinierten Feldes</li>
<li>Titel/Variablen Name (z.B: Das ist der Wert der an lang_get() API übermittelt oder angezeigt wird, wenn keine Übersetzung gefunden wurde.).</li>
<li>Typ des benutzerdefinierten Feldes (string, numeric, float, enum, email)</li>
<li>Mögliche Werte der Aufzählung (z.B.: ROT|GELB|BLAU) , anwendbar auf die Liste, Multiselektions Liste 
und auf Kombotypen.<br />
<i>Um mögliche Werte der Aufzählung zu trennen, kann der senkrechte Strich ('|') benutzt werden. Eine leere Zeichenfolge 
gilt als möglicher Wert.</i>
</li>
<li>Standard-Wert: NOCH NICHT UMGESETZT</li>
<li>Minimale/Maximale Länge des Werts für das benutzerdefinierte Feld (0 zum Deaktivieren). (NOCH NICHT UMGESETZT)</li>
<li>Regular Expression zum Prüfen der Benutzereingabe
(nutze <a href=\"http://au.php.net/manual/en/function.ereg.php\">ereg()</a>
syntax). <b>(NOCH NICHT UMGESETZT)</b></li>
<li>Alle benutzerdefinierten Felder sind zurzeit in einem Feld des Datentyps VARCHAR(255) in der Datenbank gespeichert.</li>
<li>Zeige in der Testspezifikation.</li>
<li>Aktiviere in der Testspezifikation. Der Wert kann beim Entwurf der Testspezifikation noch geändert werden</li>
<li>Zeige bei Testausführung.</li>
<li>Aktiviere bei Testausführung. Der Wert kann während der Testausführung noch geändert werden.</li>
<li>Zeige beim Test Plan Entwurf.</li>
<li>Aktiviere beim Test Plan Entwurf. Der Wert kann beim Entwurf des Test Plans noch geändert werden (füge Testfälle dem Test Plan hinzu).</li>
<li>Verfügbar für: Nutzer wählt aus, welcher Art von Item das Feld folgt.</li>
</ul>
";

// execMain.html
$TLS_hlp_executeMain = "<h2>Testfall Ausführung</h2>
<p>Erlaubt es Nutzern Testfälle auszuführen. Die Ausführung selbst ist lediglich 
das Zuweisen eines Ergebnisses (OK, Fehlgeschlagen, Blockiert) an einen Testfall im ausgewählten Build.</p>
<p>Zugang zu einem Bug Tracking System kann konfiguriert werden. Nutzer kann neue BUGs hinzufügen und 
existierende durchsuchen. Der Installationsanleitung können weitere Informationen entnommen werden.</p>";

//bug_add.html
$TLS_hlp_btsIntegration = "<h2>Füge den Testfällen BUGs hinzu.</h2>
<p><i>(falls konfiguriert)</i>
TestLink hat nur eine einfache Integration in ein Bug Tracking Systems (BTS),
es ist nicht möglich eine Anfrage zum Erstellen eines BUGs an das BTs zu schicken. 
Ebenfalls ist es nicht möglich die BUG ID zu bekommen.  
Die Integration wird durch Verknüpfungen der BTS-Webseiten ermöglicht. 
Die folgenden Funktionen können aufgerufen werden:
<ul>
	<li>Füge neuen BUG ein.</li>
	<li>Zeige BUG Informationen. </li>
</ul>
</p>  

<h3>Wie man einen BUG hinzufügt</h3>
<p>
   <ul>
   <li>Schritt 1: Nutzen Sie die Verknüpfung um das BTS zu öffnen und einen neuen BUG zu erstellen. </li>
   <li>Schritt 2: Notiere Sie die BUGID die vom BTS zugewiesen wurde.</li>
   <li>Schritt 3: Schreibe Sie die BUGID in das Feld.</li>
   <li>Schritt 4: Nutzen Sie die Schaltfläche 'BUG hinzufügen'.</li>
   </ul>  
Die relevanten BUG Daten werden auf der Ausführung Seite angezeigt, nachdem die 'BUG hinzufügen'
Seite geschlossen wurde.
</p>";

// execFilter.html
$TLS_hlp_executeFilter = "<h2>Einstellungen</h2>

<p>Die Einstellungen erlauben es einen Test Plan, Build oder eine Plattform (falls vorhanden) auszuwählen um sie auszuführen.</p>

<h3>Test Plan</h3>
<p>
Der geforderte Test Plan kann ausgewählt werden. Nach Auswahl des Test Plans 
werden die entsprechenden Builds angezeigt und die Filter zurückgesetzt.</p>

<h3>Plattform</h3>
<p>Wenn die Plattform-Funktion genutzt wird, muss die entsprechende Plattform vor der Ausführung ausgewählt werden.</p>

<h3>Auszuführendes-Build</h3>
<p>Das gewünschte Build, auf dem die Testfälle ausgeführt werden sollen, kann ausgewählt werden.</p>

<h2>Filter</h2>
<p> Filter bieten die Möglichkeit die Testfälle nach eigenen Wünschen anzuzeigen bevor sie ausgeführt werden. 
Mit bestimmten Filtern bzw. drücken der Schaltfläche 'Anwenden' kann die Anzahl der Testfall-Sätze reduziert werden.</p>

<p> Erweiterte Filter erlauben es schon angewandte Filter mit einer Reihe von Werten zu spezifizieren. 
Das wird mit einem STRG-Klick in der Multi-Select ListBox erreicht.</p>


<h3>Stichwort-Filter</h3>
<p>Nach Stichworten von Testfällen kann gefiltert werden. Mutliple Stichwörte " .
"können über den STRG-Klick ausgewählt werden " .
"Bei mehrfach ausgewählten Stichworten können die Testfälle angezeigt werden, ". 
"die alle (Optionsfeld \"UND\") oder mindestens eins (Optionsfeld \"ODER\") ".  
"dieser Stichworte beinhalten.</p>

<h3>Prioritäts-Filter</h3>
<p>Testfälle können nach Test Prioritäten gefiltert werden. Die Test Prioritäten lauten \"Testfall Wichtung\" " .
"und \"Test Dringlichkeit\" in dem aktuellen Test Plan.</p> 

<h3>Benutzer-Filter</h3>
<p> Es kann nach Testfällen gefiltert werden, die zugewiesen an \"jemand\" oder nicht " .
"zugewiesen an \"niemand\" sind. Es kann auch nach bestimmten Testern gefiltert werden. ".
"Falls nach einem Tester gefiltert wird, können zusätzlich auch die Testfälle angezeigt werden, ".
"die nicht zugewiesen sind (erweiterte Filter sind anwählbar). </p>

<h3>Ergebnis-Filter</h3>
<p> Testfälle können nach ihren Ergebnissen gefiltert werden (erweiterte Filter sind anwählbar). 
Es kann gefiltert werden nach dem Ergebnis \"des ausgewählten Builds für die Ausführung\", ".
" \"der letzten Ausführung\", \"ALLER Builds\", " .
"\"BELIEBIGES Build\" und \"eines bestimmten Builds\". Falls ein \"bestimmtes Build\" ausgewählt wurde" .
"muss das Build bestimmt werden. </p>";


// newest_tcversions.html
$TLS_hlp_planTcModified = "<h2>Neuere Versionen von verknüpften Testfällen</h2>
<p>Alle, dem Test Plan verknüpfte Testfälle, werden analysiert und gelistet, die die neuesten Versionen
der Testfälle anzeigt (im Vergleich zum aktuellen Satz des Test Plans).
</p>";


// requirementsCoverage.html
$TLS_hlp_requirementsCoverage = "<h3>Anforderungsabdeckung</h3>
<br />
<p>Diese Funktion erlaubt das Aufstellen einer Abdeckung von Benutzer- oder Systemanforderungen mit
Testfällen. Auf der Hauptseite kann über \"Anforderungsspezifikation\" navigiert werden.</p>

<h3>Anforderungsspezifikation</h3>
<p>Anforderungen sind nach dem Dokument 'Anforderung Spezifikation', welches mit dem Test Plan 
verbunden ist, gruppiert. <br /> TestLink unterstützt nicht beide Versionen für die Anf.Spezifikation und 
der Anforderung selbst. Also sollte die Version des Dokuments erst nach der Spezifikation eingefügt werden.
 <b>Titel</b>.
Der Nutzer kann dem Feld <b>Inhalt</b> eine kurze Beschreibung oder Notiz hinzufügen.</p> 

<p><b><a name='total_count'>Überschrieben Anzahl von Anf. </a></b> Dient der Evaluation der Anf. 
Abdeckung, falls nicht alle Anforderungen importiert wurden.  
Der Wert <b>0</b> bedeutet, dass die aktuelle Anzahl der Anf. für Metriken genutzt wird.</p> 
<p><i>Beispielsweise SRS beinhaltet 200 Anforderungen aber nur 50 sind in TestLink hinzugefügt worden. Die Test 
Abdeckung ist 25% (falls alle importierten Anforderungen getestet werden).</i></p>

<h3><a name=\"req\">Anforderungen</a></h3>
<p>Mit einem Klick auf den Titel der erstellten Anf. Spezifikation kann für das Dokument Anforderungen erstellt, 
bearbeitet, gelöscht oder importiert werden. Jede Anforderung hat einen Titel, Inhalt und Status.
Der Status sollte \"Normal\" oder \"Nicht testbar\" sein. Nicht testbare Anforderungen gehen in die Metriken 
nicht ein. Dieser Parameter kann für noch nicht implementierte Funktionen und 
falsch entworfenen Anforderungen genutzt werden.</p> 

<p>Mit der Nutzung von Multi Aktionen mit abgehakten Anforderungen auf der Spezifikations Umgebung, 
können neue Testfälle für Anforderungen erstellt werden. Diese Testfälle werden der Test Suite mit dem 
konfigurierten Namen  <i>(Standard ist: &#36;tlCfg->req_cfg->default_testsuite_name = 
\"Test Suite erstellt über Anforderung - Auto\";)</i>. hinzugefügt. Titel und Inhalt
werden in die Testfälle kopiert.</p>
";

$TLS_hlp_req_coverage_table = "<h3>Abdeckung:</h3>
Ein Wert von bspw. \"40% (8/20)\" bedeutet das 20 Testfälle für diese Anforderung noch zu erstellen sind,
um sie vollständig abzudecken. Die 8 Testfälle, die schon erstellt und mit dieser Anforderung verknüpft 
wurden, ergeben eine Abdeckung von 40 Prozent.
";


// req_edit
$TLS_hlp_req_edit = "<h3>Interne Links im Inhalt:</h3>
<p>Interne Links können genutzt werden um Links zu anderen Anforderungen/Anforderungsspezifikation 
mit einer speziellen Syntax zu erstellen. 
Das Verhalten der internen Links kann über die Konfigurationsdatei angepasst werden.
<br /><br />
<b>Benutzung:</b>
<br />
Link zu einer Anforderung: [req]Anf_Dokument_ID[/req]<br />
Link zu einer Anforderungsspezifikation: [req_spec]Anf_Spez_Dokument_ID[/req_spec]</p>

<p>Das Testprojekt und ein Anker der zu verlinkenden Anforderung kann ebenfalls angegeben werden:<br />
[req tproj=&lt;tproj_prefix&gt; anchor=&lt;anker_name&gt;]Anf_Dokument_ID[/req]<br />
Diese Syntax funktioniert auch für Anforderungsspezifikationen.</p>

<h3>Änderungsprotokoll/Revisionierung:</h3>
<p>Immer wenn eine Änderung an einer Anforderung vorgenommen wird fragt Testlink nach einer Protokollierung der Änderung. 
Das Änderungsprotokoll dient der Rückverfolgbarkeit (Traceability).</p>
<p>Wenn sich nur der Inhalt der Anforderung ändert, steht es dem Autor frei, 
ob er eine neue Revision erstellen möchte oder nicht. Sollte sich etwas außer dem Inhalt ändern ist der Autor gezwungen, 
eine neue Revision zu erstellen.</p>
";


// req_view
$TLS_hlp_req_view = "<h3>Direkte Links:</h3>
<p>Um Anforderungsdokumente so leicht wie möglich mit anderen teilen zu können 
bietet TestLink die Möglichkeit einen direkten Link zu diesem Dokument zu erzeugen. 
Klicken Sie dazu das Globus Icon an.</p>

<h3>Verlauf anzeigen:</h3>
<p>Dieses Feature erlaubt es Revisionen/Versionen von Anforderungen zu vergleichen, 
sofern mehr als eine Revision/Version der Anforderung existiert.
Die Übersicht zeigt das Änderungsprotokoll, das Datum und den Autor der letzten Änderung für jede Revision/Version.</p>
















<h3>Abdeckung:</h3>
<p>Zeigt alle Testfälle, die mit der Anforderung verknüpft wurden.</p>

<h3>Beziehungen:</h3>
<p>Beziehungen werden benutzt um Beziehungen zwischen Anforderungen zu modellieren. 
Benutzerdefinierte Beziehungen und die Möglichkeit Beziehungen zwischen Anforderungen 
in verschiedenen Projekten herstellen zu können, werden in der Konfigurationsdatei konfiguriert.</p>
<p>Setzt man die Beziehung \"Anforderung A ist Vater von Anforderung B\", 
wird Testlink die Beziehung \"Anforderung B ist Kind von Anforderung A\" implizit setzen.</p>
";


// req_spec_edit
$TLS_hlp_req_spec_edit = "<h3>Interne Links im Inhalt:</h3>
<p>Interne Links können genutzt werden um Links zu anderen Anforderungen/Anforderungsspezifikation 
mit einer speziellen Syntax zu erstellen. 
Das Verhalten der internen Links kann über die Konfigurationsdatei angepasst werden.
<br /><br />
<b>Benutzung:</b>
<br />
Link zu einer Anforderung: [req]Anf_Dokument_ID[/req]<br />
Link zu einer Anforderungsspezifikation: [req_spec]Anf_Spez_Dokument_ID[/req_spec]</p>

<p>Das Testprojekt und ein Anker der zu verlinkenden Anforderung kann ebenfalls angegeben werden:<br />
[req tproj=&lt;tproj_prefix&gt; anchor=&lt;anker_name&gt;]Anf_Dokument_ID[/req]<br />
Diese Syntax funktioniert auch für Anforderungsspezifikationen.</p>
";


$TLS_hlp_req_coverage_table = "<h3>Abdeckung:</h3>
Ein Wert von z.B. \"40% (8/20)\" bedeutet, dass 20 Testfälle erstellt werden müssen um die Anforderung
komplett durch Testfälle abzudecken. Acht dieser Testfälle wurden bereits erstellt und der Anforderung
zugewiesen, was einer Abdeckung von 40% entspricht.
";


// planAddTC_m1.tpl
$TLS_hlp_planAddTC = "<h2>Bezüglich 'Speichere Benutzerdefinierte Felder'</h2>
Falls zum Test Project definiert und zugewiesen,<br /> 
Benutzerdefinierte Felder mit:<br />
 'Zeige bei der Test Plan Entwurf=Wahr' und <br />
 'Aktiviere beim Test Plan Entwurf=Wahr'<br />

Es werden auf der Seite NUR Testfälle angezeigt die mit dem Test Plan verknüpft sind.
";


// resultsByTesterPerBuild.tpl
$TLS_hlp_results_by_tester_per_build_table = "<b>Zusatzinformationen über Tester:</b><br />
Bei einem Klick auf den Tester Namen in dieser Tabelle öffnet sich eine detaillierte Übersicht
über alle dem jew. Tester zugewiesene Testfälle und dessen Testprozess.<br /><br />
<b>Hinweis:</b><br />
Diese Übersicht zeigt die Testfälle, die einem bestimmten Nutzer zugewiesen sind und 
basierend auf dem jeweiligen aktiven Build ausgeführt wurden. Auch wenn der Testfall von einem anderen
Nutzer ausgeführt wurde, erscheint der Testfall bei dem zugewiesenen Nutzer als Ausgeführt.
";


// xxx.html
//$TLS_hlp_xxx = "";

// ----- END ------------------------------------------------------------------
?>
