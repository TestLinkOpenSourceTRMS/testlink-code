<html STYLE="width: 432px; height: 168px; ">
<head><title>Insert Table</title><head>
<style>
  html, body, button, div, input, select, td, fieldset { font-family: MS Shell Dlg; font-size: 8pt; };
</style>
<script>

// if we pass the "window" object as a argument and then set opener to
// equal that we can refer to dialogWindows and popupWindows the same way
opener = window.dialogArguments;

var _editor_url = opener._editor_url;
var objname     = location.search.substring(1,location.search.length);
var config      = opener.document.all[objname].config;
var editor_obj  = opener.document.all["_" +objname+  "_editor"];
var editdoc     = editor_obj.contentWindow.document;

function _CloseOnEsc() {
  if (event.keyCode == 27) { window.close(); return; }
}

window.onerror = HandleError

function HandleError(message, url, line) {
  var str = "An error has occurred in this dialog." + "\n\n"
  + "Error: " + line + "\n" + message;
  alert(str);
//  window.close();
  return true;
}

function Init() {
  document.body.onkeypress = _CloseOnEsc;
}

function _isValidNumber(txtBox) {
  var val = parseInt(txtBox);
  if (isNaN(val) || val < 0 || val > 9999) { return false; }
  return true;
}

function btnOKClick() {
  var curRange = editdoc.selection.createRange();

  // error checking
  var checkList = ['rows','cols','border','cellspacing','cellpadding'];
  for (var idx in checkList) {
    var fieldname = checkList[idx];
    if (document.all[fieldname].value == "") {
      alert("You must specify a value for the '" +fieldname+ "' field!");
      document.all[fieldname].focus();
      return;
    }
    else if (!_isValidNumber(document.all[fieldname].value)) {
      alert("You must specify a number between 0 and 9999 for '" +fieldname+ "'!");
      document.all[fieldname].focus();
      return;
    }
  }

  // delete selected content (if applicable)
  if (editdoc.selection.type == "Control" || curRange.htmlText) {

    if (!confirm("Overwrite selected content?")) { return; }

    curRange.execCommand('Delete');
    curRange = editdoc.selection.createRange();
  }


  // create table
  var table = '<table border="' +document.all.border.value+ '"'
            + ' cellspacing="' +document.all.cellspacing.value+ '"'
            + ' cellpadding="' +document.all.cellpadding.value+ '"'
            + ' width="' +document.all.width.value + document.all.widthExt.value+ '"'
            + ' align="' +document.all.alignment.value+ '">\n';

  for (var x=0; x<document.all.rows.value; x++) {
    table += " <tr>\n";
    for (var y=0; y<document.all.cols.value; y++) {
      table += "  <td></td>\n";
    }
    table += " </tr>\n";
  }
  table += "</table>\n";

  // insert table
  opener.editor_insertHTML(objname, table);


  // close popup window
  window.close();
}
</SCRIPT>
</HEAD>
<BODY id=bdy onload="Init()" style="background: threedface; color: windowtext; margin: 10px; BORDER-STYLE: none" scroll=no>

<table border=0 cellspacing=0 cellpadding=0 style="margin: 0 0 8 0">
 <tr>
  <td>Rows: &nbsp;</td>
  <td><input type="text" name="rows" value="4"  style="width: 50px" maxlength=4></td>
 </tr>
 <tr>
  <td>Cols:</td>
  <td><input type="text" name="cols" value="3"  style="width: 50px" maxlength=4></td>
  <td width=10>&nbsp;</td>
  <td>Width: &nbsp;</td>
  <td>
   <input type="text" name="width" value="100" style="width: 50px" maxlength=4>
   <select name="widthExt">
    <option value="">Pixels</option>
    <option value="%" selected>Percent</option>
   </select>
  </td>
 </tr>
</table>


<FIELDSET style="width: 1%; text-align: center">
<LEGEND>Layout</LEGEND>

<table border=0 cellspacing=6 cellpadding=0>
 <tr>
  <td height=21>Alignment:</td>
  <td>
   <select name="alignment" size=1>
   <option value="">Not set</option>
   <option value="left">Left</option>
   <option value="right">Right</option>
   <option value="textTop">Texttop</option>
   <option value="absMiddle">Absmiddle</option>
   <option value="baseline">Baseline</option>
   <option value="absBottom">Absbottom</option>
   <option value="bottom">Bottom</option>
   <option value="middle">Middle</option>
   <option value="top">Top</option>
   </select>
  </td>
 </tr>
 <tr>
  <td><nobr>Border Thickness:</nobr></td>
  <td><input type="text" name="border" value="1" size=4 style="width: 100%"></td>
 </tr>
</table>
</FIELDSET>


<FIELDSET style="width: 1%; text-align: center">
<LEGEND>Spacing</LEGEND>

<table border=0 cellspacing=6 cellpadding=0>
 <tr>
  <td><nobr>Cell Spacing:</nobr></td>
  <td><input type="text" name="cellspacing" value="1" style="width: 50px" maxlength=4></td>
 </tr>
 <tr>
  <td><nobr>Cell Padding:</nobr></td>
  <td><input type="text" name="cellpadding" value="2" style="width: 50px" maxlength=4></td>
 </tr>
</table>
</FIELDSET>


<div style="left: 340px; top: 16px; position: absolute">
 <BUTTON style="width: 7em; height: 2.2em; margin: 0 0 3 0" type=submit onclick="btnOKClick()">OK</BUTTON><br>
 <BUTTON style="width: 7em; height: 2.2em; " type=reset onClick="window.close();">Cancel</BUTTON>
</div>

</BODY>
</HTML>