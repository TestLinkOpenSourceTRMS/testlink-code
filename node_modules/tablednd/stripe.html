<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
        "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
    <title>Table Drag and Drop jQuery plugin</title>
    <link rel="stylesheet" href="tablednd.css" type="text/css"/>
</head>
<body>
<div id="page">
<h1>Table Drag and Drop jQuery plugin</h1>
<p>This page contains documentation and tests for the TableDnD jQuery plug-in. For more information and
to post comments, please go to <a href="http://www.isocra.com/2008/02/table-drag-and-drop-jquery-plugin/">isocra.com</a>.
</p>
<p>If you have issues or bug reports, then you can post them at the <a href="http://plugins.jquery.com/project/issues/TableDnD">TableDnD plug page</a>
at plugins.jquery.com</p>

<h2>How do I use it?</h2>
<ol>
	<li>Download <a href="http://jquery.com">Download jQuery</a> (version 1.2 or above), then the <a href="/articles/jquery.tablednd.js.zip">TableDnD plugin</a> (current version 0.4).</li>
	<li>Reference both scripts in your HTML page in the normal way.</li>
	<li>In true jQuery style, the typical way to initialise the tabes is in the <code>$(document).ready</code> function. Use a selector to select your table and then call <code>tableDnD()</code>. You can optionally specify a set of properties (described below).</li>
</ol>
<div class="tableDemo">
<div id="debug" style="float:right;"></div>
<table id="table-1" cellspacing="0" cellpadding="2">
    <tr id="1"><td>1</td><td>One</td><td>some text</td></tr>
    <tr id="2"><td>2</td><td>Two</td><td>some text</td></tr>
    <tr id="3"><td>3</td><td>Three</td><td>some text</td></tr>
    <tr id="4"><td>4</td><td>Four</td><td>some text</td></tr>
    <tr id="5"><td>5</td><td>Five</td><td>some text</td></tr>
    <tr id="6"><td>6</td><td>Six</td><td>some text</td></tr>
</table>
</div>
<p>The HTML for the table is very straight forward (no Javascript, pure HTML):</p>

<pre>
&lt;table id=&quot;table-1&quot; cellspacing=&quot;0&quot; cellpadding=&quot;2&quot;&gt;
    &lt;tr id=&quot;1&quot;&gt;&lt;td&gt;1&lt;/td&gt;&lt;td&gt;One&lt;/td&gt;&lt;td&gt;some text&lt;/td&gt;&lt;/tr&gt;
    &lt;tr id=&quot;2&quot;&gt;&lt;td&gt;2&lt;/td&gt;&lt;td&gt;Two&lt;/td&gt;&lt;td&gt;some text&lt;/td&gt;&lt;/tr&gt;
    &lt;tr id=&quot;3&quot;&gt;&lt;td&gt;3&lt;/td&gt;&lt;td&gt;Three&lt;/td&gt;&lt;td&gt;some text&lt;/td&gt;&lt;/tr&gt;
    &lt;tr id=&quot;4&quot;&gt;&lt;td&gt;4&lt;/td&gt;&lt;td&gt;Four&lt;/td&gt;&lt;td&gt;some text&lt;/td&gt;&lt;/tr&gt;
    &lt;tr id=&quot;5&quot;&gt;&lt;td&gt;5&lt;/td&gt;&lt;td&gt;Five&lt;/td&gt;&lt;td&gt;some text&lt;/td&gt;&lt;/tr&gt;
    &lt;tr id=&quot;6&quot;&gt;&lt;td&gt;6&lt;/td&gt;&lt;td&gt;Six&lt;/td&gt;&lt;td&gt;some text&lt;/td&gt;&lt;/tr&gt;
&lt;/table&gt;
</pre>
<p>To add in the "draggability" all we need to do is add a line to the <code>$(document).ready(...)</code> function
as follows:</p>
<pre>
<span class="comment">&lt;script type=&quot;text/javascript&quot;&gt;</span>
$(document).ready(function() {
    <span class="comment">// Initialise the table</span>
    $(&quot;#table-1&quot;).tableDnD();
});
<span class="comment">&lt;/script&gt;</span>
</pre>
<p>In the example above we're not setting any parameters at all so we get the default settings. There are a number
	of parameters you can set in order to control the look and feel of the table and also to add custom behaviour
	on drag or on drop. The parameters are specified as a map in the usual way and are described below:</p>

<dl>
	<dt>onDragStyle</dt>
	<dd>This is the style that is assigned to the row during drag. There are limitations to the styles that can be
		associated with a row (such as you can't assign a border&mdash;well you can, but it won't be
		displayed). (So instead consider using <code>onDragClass</code>.) The CSS style to apply is specified as
		a map (as used in the jQuery <code>css(...)</code> function).</dd>
	<dt>onDropStyle</dt>
	<dd>This is the style that is assigned to the row when it is dropped. As for onDragStyle, there are limitations
		to what you can do. Also this replaces the original style, so again consider using onDragClass which
		is simply added and then removed on drop.</dd>
	<dt>onDragClass</dt>
	<dd>This class is added for the duration of the drag and then removed when the row is dropped. It is more
		flexible than using onDragStyle since it can be inherited by the row cells and other content. The default
		is class is <code>tDnD_whileDrag</code>. So to use the default, simply customise this CSS class in your
		stylesheet.</dd>
	<dt>onDrop</dt>
	<dd>Pass a function that will be called when the row is dropped. The function takes 2 parameters: the table
	    and the row that was dropped. You can work out the new order of the rows by using
	    <code>table.tBodies[0].rows</code>.</dd>
	<dt>onDragStart</dt>
	<dd>Pass a function that will be called when the user starts dragging. The function takes 2 parameters: the
		table and the row which the user has started to drag.</dd>
	<dt>scrollAmount</dt>
	<dd>This is the number of pixels to scroll if the user moves the mouse cursor to the top or bottom of the
		window. The page should automatically scroll up or down as appropriate (tested in IE6, IE7, Safari, FF2,
		FF3 beta)</dd>
</dl>
<p>This second table has has an onDrop function applied as well as an onDragClass. The javascript to set this up is
as follows:</p>
<pre>
$(document).ready(function() {

	// Initialise the first table (as before)
	$("#table-1").tableDnD();

	// Make a nice striped effect on the table
	$("#table-2 tr:even').addClass('alt')");

	// Initialise the second table specifying a dragClass and an onDrop function that will display an alert
	$("#table-2").tableDnD({
	    onDragClass: "myDragClass",
	    onDrop: function(table, row) {
            var rows = table.tBodies[0].rows;
            var debugStr = "Row dropped was "+row.id+". New order: ";
            for (var i=0; i&lt;rows.length; i++) {
                debugStr += rows[i].id+" ";
            }
	        $(#debugArea).html(debugStr);
	    },
		onDragStart: function(table, row) {
			$(#debugArea).html("Started dragging row "+row.id);
		}
	});
});
</pre>
<div class="tableDemo">
<div id="debugArea" style="float: right">&nbsp;</div>
<table id="table-2" cellspacing="0" cellpadding="2">
    <tr id="2.1"><td>1</td><td>One</td><td><input type="text" name="one" value="one"/></td></tr>
    <tr id="2.2"><td>2</td><td>Two</td><td><input type="text" name="two" value="two"/></td></tr>
    <tr id="2.3"><td>3</td><td>Three</td><td><input type="text" name="three" value="three"/></td></tr>
    <tr id="2.4"><td>4</td><td>Four</td><td><input type="text" name="four" value="four"/></td></tr>
    <tr id="2.5"><td>5</td><td>Five</td><td><input type="text" name="five" value="five"/></td></tr>
    <tr id="2.6"><td>6</td><td>Six</td><td><input type="text" name="six" value="six"/></td></tr>
    <tr id="2.7"><td>7</td><td>Seven</td><td><input type="text" name="seven" value="7"/></td></tr>
    <tr id="2.8"><td>8</td><td>Eight</td><td><input type="text" name="eight" value="8"/></td></tr>
    <tr id="2.9"><td>9</td><td>Nine</td><td><input type="text" name="nine" value="9"/></td></tr>
    <tr id="2.10"><td>10</td><td>Ten</td><td><input type="text" name="ten" value="10"/></td></tr>
    <tr id="2.11"><td>11</td><td>Eleven</td><td><input type="text" name="eleven" value="11"/></td></tr>
    <tr id="2.12"><td>12</td><td>Twelve</td><td><input type="text" name="twelve" value="12"/></td></tr>
    <tr id="2.13"><td>13</td><td>Thirteen</td><td><input type="text" name="thirteen" value="13"/></td></tr>
    <tr id="2.14"><td>14</td><td>Fourteen</td><td><input type="text" name="fourteen" value="14"/></td></tr>
</table>
</div>
<h2>What to do afterwards?</h2>
<p>Generally once the user has dropped a row, you need to inform the server of the new order. To do this, we've
	added a method called <code>serialise()</code>. It takes no parameters but knows the current table from the
	context. The method returns a string of the form <code><i>tableId</i>[]=<i>rowId1</i>&amp;<i>tableId</i>[]=<i>rowId2</i>&amp;<i>tableId</i>[]=<i>rowId3</i>...</code>
	You can then use this as part of an Ajax load.
</p>
<p>This third table demonstrates calling the serialise function inside onDrop (as shown below). It also
	demonstrates the "nodrop" class on row 3 and "nodrag" class on row 5, so you can't pick up row 5 and
	you can't drop any row on row 3 (but you can drag it).</p>
<pre>
    $('#table-3').tableDnD({
        onDrop: function(table, row) {
            alert($.tableDnD.serialize());
        }
    });
</pre>
<div class="tableDemo">
<div id="AjaxResult" style="float: right; width: 250px; border: 1px solid silver; padding: 4px; font-size: 90%">
	<h3>Ajax result</h3>
	<p>Drag and drop in this table to test out serialise and using JQuery.load()</p>
</div>
<table id="table-3" cellspacing="0" cellpadding="2">
    <tr id="3.1"><td>1</td><td>One</td><td><input type="text" name="one" value="one"/></td></tr>
    <tr id="3.2"><td>2</td><td>Two</td><td><input type="text" name="two" value="two"/></td></tr>
    <tr id="3.3" class="nodrop"><td>3</td><td>Three (Can't drop on this row)</td><td><input type="text" name="three" value="three"/></td></tr>
    <tr id="3.4"><td>4</td><td>Four</td><td><input type="text" name="four" value="four"/></td></tr>
    <tr id="3.5" class="nodrag"><td>5</td><td>Five (Can't drag this row)</td><td><input type="text" name="five" value="five"/></td></tr>
    <tr id="3.6"><td>6</td><td>Six</td><td><input type="text" name="six" value="six"/></td></tr>
</table>
</div>
<p>This table has multiple TBODYs. The functionality isn't quite working properly. You can only drag the rows inside their
own TBODY, you can't drag them outside it. Now this might or might not be what you want, but unfortunately if you then drop a row outside its TBODY you get a Javascript error because inserting after a sibling doesn't work. This will be fixed in the next version. The header rows all have the classes "nodrop" and "nodrag" so that they can't be dragged or dropped on.</p>
<div class="tableDemo">
<table id="table-4" cellspacing="0" cellpadding="2">
	<tbody>
		<tr id="4.0" class="nodrop nodrag"><th>H1</th><th>H2</th><th>H3</th></tr>
        <tr id="4.1"><td>4.1</td><td>One</td><td><input type="text" name="one" value="one"/></td></tr>
        <tr id="4.2"><td>4.2</td><td>Two</td><td><input type="text" name="two" value="two"/></td></tr>
        <tr id="4.3"><td>4.3</td><td>Three</td><td><input type="text" name="three" value="three"/></td></tr>
        <tr id="4.4"><td>4.4</td><td>Four</td><td><input type="text" name="four" value="four"/></td></tr>
        <tr id="4.5"><td>4.5</td><td>Five</td><td><input type="text" name="five" value="five"/></td></tr>
        <tr id="4.6"><td>4.6</td><td>Six</td><td><input type="text" name="six" value="six"/></td></tr>
	</tbody>
	<tbody>
		<tr id="5.0" class="nodrop nodrag"><th>H1</th><th>H2</th><th>H3</th></tr>
        <tr id="5.1"><td>5.1</td><td>One</td><td><input type="text" name="one" value="one"/></td></tr>
        <tr id="5.2"><td>5.2</td><td>Two</td><td><input type="text" name="two" value="two"/></td></tr>
        <tr id="5.3"><td>5.3</td><td>Three</td><td><input type="text" name="three" value="three"/></td></tr>
        <tr id="5.4"><td>5.4</td><td>Four</td><td><input type="text" name="four" value="four"/></td></tr>
        <tr id="5.5"><td>5.5</td><td>Five</td><td><input type="text" name="five" value="five"/></td></tr>
        <tr id="5.6"><td>5.6</td><td>Six</td><td><input type="text" name="six" value="six"/></td></tr>
	</tbody>
	<tbody>
		<tr id="6.0" class="nodrop nodrag"><th>H1</th><th>H2</th><th>H3</th></tr>
        <tr id="6.1"><td>6.1</td><td>One</td><td><input type="text" name="one" value="one"/></td></tr>
        <tr id="6.2"><td>6.2</td><td>Two</td><td><input type="text" name="two" value="two"/></td></tr>
        <tr id="6.3"><td>6.3</td><td>Three</td><td><input type="text" name="three" value="three"/></td></tr>
        <tr id="6.4"><td>6.4</td><td>Four</td><td><input type="text" name="four" value="four"/></td></tr>
        <tr id="6.5"><td>6.5</td><td>Five</td><td><input type="text" name="five" value="five"/></td></tr>
        <tr id="6.6"><td>6.6</td><td>Six</td><td><input type="text" name="six" value="six"/></td></tr>
	</tbody>
</table>
</div>
<p>
The following table demonstrates the use of the default regular expression. The rows have IDs of the
form table5-row-1, table5-row-2, etc., but the regular expression is <code>/[^\-]*$/</code> (this is the same
as used in the <a href="http://plugins.jquery.com/project/NestedSortable">NestedSortable</a> plugin for consistency).
This removes everything before and including the last hyphen, so the serialised string just has 1, 2, 3 etc.
You can replace the regular expression by setting the <code>serializeRegexp</code> option, you can also just set it
to null to stop this behaviour.
</p>
<pre>
    $('#table-5').tableDnD({
        onDrop: function(table, row) {
            alert($.tableDnD.serialize());
        },
        dragHandle: "dragHandle"
    });
</pre>
<div class="tableDemo">
<table id="table-5" cellspacing="0" cellpadding="2">
    <tr id="table5-row-1"><td class="dragHandle">&nbsp;</td><td>1</td><td>One</td><td>some text</td></tr>
    <tr id="table5-row-2"><td class="dragHandle">&nbsp;</td><td>2</td><td>Two</td><td>some text</td></tr>
    <tr id="table5-row-3"><td class="dragHandle">&nbsp;</td><td>3</td><td>Three</td><td>some text</td></tr>
    <tr id="table5-row-4"><td class="dragHandle">&nbsp;</td><td>4</td><td>Four</td><td>some text</td></tr>
    <tr id="table5-row-5"><td class="dragHandle">&nbsp;</td><td>5</td><td>Five</td><td>some text</td></tr>
    <tr id="table5-row-6"><td class="dragHandle">&nbsp;</td><td>6</td><td>Six</td><td>some text</td></tr>
</table>
</div>
<p>In fact you will notice that I have also set the dragHandle on this table. This has two effects: firstly only
the cell with the drag handle class is draggable and secondly it doesn't automatically add the <code>cursor: move</code>
style to the row (or the drag handle cell), so you are responsible for setting up the style as you see fit.</p>
<p>Here I've actually added an extra effect which adds a background image to the first cell in the row whenever
you enter it using the jQuery <code>hover</code> function as follows:</p>
<pre>
    $("#table-5 tr").hover(function() {
          $(this.cells[0]).addClass('showDragHandle');
    }, function() {
          $(this.cells[0]).removeClass('showDragHandle');
    });
</pre>
<p>This provides a better visualisation of what you can do to the row and where you need to go to drag it (I hope).</p>
<h2>Version History</h2>
<table class="versionHistory">
	<tr><td>0.2</td><td>2008-02-20</td><td>First public release</td></tr>
	<tr><td>0.3</td><td>2008-02-27</td><td>Added onDragStart option<br/>Made the scroll amount configurable (default is 5 as before)</td></tr>
	<tr><td>0.4</td><td>2008-03-28</td><td>Fixed the scrollAmount so that if you set this to zero then it switches off this functionality<br/>Fixed the auto-scrolling in IE6 thanks to Phil<br/>Changed the NoDrop attribute to the class "nodrop" (so any row with this class won't allow dropping)<br/>Changed the NoDrag attribute to the class "nodrag" (so any row with this class can't be dragged)<br/>Added support for multiple TBODYs--though it's still not perfect<br/>Added onAllowDrop to allow the developer to customise this behaviour<br/>Added a serialize() method to return the order of the rows in a form suitable for POSTing back to the server</td></tr>
    <tr><td>0.5</td><td>2008-06-04</td><td>Changed so that if you specify a dragHandle class it doesn't make the whole row<br/>draggable<br/>Improved the serialize method to use a default (and settable) regular expression.<br/>Added tableDnDupate() and tableDnDSerialize() to be called when you are outside the table</td></tr>
</table>
</div>
<script type="text/javascript" src="//ajax.googleapis.com/ajax/libs/jquery/1.8.2/jquery.min.js"></script>
<script type="text/javascript" src="js/jquery.tablednd.js"></script>
<script type="text/javascript">
    $(document).ready(function() {
        alert("Here I am");
        // Initialise the first table (as before)
        $("#table-1").tableDnD();
        // Make a nice striped effect on the table
        $("#table-2 tr:even").addClass("alt");
        // Initialise the second table specifying a dragClass and an onDrop function that will display an alert
        $("#table-2").tableDnD({
            onDragClass: "myDragClass",
            onDrop: function(table, row) {
                var rows = table.tBodies[0].rows;
                var debugStr = "Row dropped was "+row.id+". New order: ";
                for (var i=0; i<rows.length; i++) {
                    debugStr += rows[i].id+" ";
                }
                $("#debugArea").html(debugStr);
            },
            onDragStart: function(table, row) {
                $("#debugArea").html("Started dragging row "+row.id);
            }
        });

        $('#table-3').tableDnD({
            onDrop: function(table, row) {
                alert("Result of $.tableDnD.serialise() is "+$.tableDnD.serialize());
                $('#AjaxResult').load("server/ajaxTest.php?"+$.tableDnD.serialize());
            }
        });

        //$('#table-4').tableDnD(); // no options currently
        $('#table-4 tr:even').css('background', '#ecf6fc');

        $('#table-5').tableDnD({
            onDrop: function(table, row) {
                alert($('#table-5').tableDnDSerialize());
            },
            dragHandle: "dragHandle"
        });

        $("#table-5 tr").hover(function() {
            $(this.cells[0]).addClass('showDragHandle');
        }, function() {
            $(this.cells[0]).removeClass('showDragHandle');
        });
    });
</script>
</body>
</html>