//
// htmlArea v2.03 - Copyright (c) 2002 interactivetools.com, inc.
// This copyright notice MUST stay intact for use (see license.txt).
//
// A free WYSIWYG editor replacement for <textarea> fields.
// For full source code and docs, visit http://www.interactivetools.com/
//

// write out styles for UI buttons
document.write('<style type="text/css">\n');
document.write('.btn     { width: 22px; height: 22px; border: 1px solid buttonface; margin: 0; padding: 0; }\n');
document.write('.btnOver { width: 22px; height: 22px; border: 1px outset; }\n');
document.write('.btnDown { width: 22px; height: 22px; border: 1px inset; background-color: buttonhighlight; }\n');
document.write('.btnNA   { width: 22px; height: 22px; border: 1px solid buttonface; filter: alpha(opacity=25); }\n');
document.write('.cMenu     { background-color: threedface; color: menutext; cursor: Default; font-family: MS Sans Serif; font-size: 8pt; padding: 2 12 2 16; }');
document.write('.cMenuOver { background-color: highlight; color: highlighttext; cursor: Default; font-family: MS Sans Serif; font-size: 8pt; padding: 2 12 2 16; }');
document.write('.cMenuDivOuter { background-color: threedface; height: 9 }');
document.write('.cMenuDivInner { margin: 0 4 0 4; border-width: 1; border-style: solid; border-color: threedshadow threedhighlight threedhighlight threedshadow; }');
document.write('</style>\n');


/* ---------------------------------------------------------------------- *\
  Function    : editor_defaultConfig
  Description : default configuration settings for wysiwyg editor
\* ---------------------------------------------------------------------- */

function editor_defaultConfig(objname) {

this.version = "2.03"

this.width =  "auto";
this.height = "auto";
this.bodyStyle = 'background-color: #FFFFFF; font-family: "Verdana"; font-size: x-small;';
this.imgURL = _editor_url + 'images/';
this.debug  = 0;

this.replaceNextlines = 0; // replace nextlines from spaces (on output)
this.plaintextInput = 0;   // replace nextlines with breaks (on input)

this.toolbar = [
    ['fontname'],
    ['fontsize'],
//    ['fontstyle'],
//    ['linebreak'],
    ['bold','italic','underline','separator'],
    ['strikethrough','subscript','superscript','separator'],
    ['justifyleft','justifycenter','justifyright','separator'],
    ['OrderedList','UnOrderedList','Outdent','Indent','separator'],
    ['forecolor','backcolor','separator'],
    ['HorizontalRule','Createlink','htmlmode','separator'],
//  ['custom1','custom2','custom3','separator'],
    ['popupeditor','inserttable','word']];

this.fontnames = {
    "Arial":           "arial, helvetica, sans-serif",
    "Courier New":     "courier new, courier, mono",
    "Georgia":         "Georgia, Times New Roman, Times, Serif",
    "Tahoma":          "Tahoma, Arial, Helvetica, sans-serif",
    "Times New Roman": "times new roman, times, serif",
    "Verdana":         "Verdana, Arial, Helvetica, sans-serif",
    "impact":          "impact",
    "WingDings":       "WingDings"};

this.fontsizes = {
    "1 (8 pt)":  "1",
    "2 (10 pt)": "2",
    "3 (12 pt)": "3",
    "4 (14 pt)": "4",
    "5 (18 pt)": "5",
    "6 (24 pt)": "6",
    "7 (36 pt)": "7"
  };

//this.stylesheet = "http://www.domain.com/sample.css"; // full URL to stylesheet

this.fontstyles = [     // make sure these exist in the header of page the content is being display as well in or they won't work!
//  { name: "headline",     className: "headline",  classStyle: "font-family: arial black, arial; font-size: 28px; letter-spacing: -2px;" },
 // { name: "arial red",    className: "headline2", classStyle: "font-family: arial black, arial; font-size: 12px; letter-spacing: -2px; color:red" },
 //  { name: "verdana blue", className: "headline4", classStyle: "font-family: verdana; font-size: 18px; letter-spacing: -2px; color:blue" },
];

this.btnList = {
    // buttonName:    commandID,               title,                onclick,                   image,             
    "bold":           ['Bold',                 'Bold',               'editor_action(this.id)',  'ed_format_bold.gif'],
    "italic":         ['Italic',               'Italic',             'editor_action(this.id)',  'ed_format_italic.gif'],
    "underline":      ['Underline',            'Underline',          'editor_action(this.id)',  'ed_format_underline.gif'],
    "strikethrough":  ['StrikeThrough',        'Strikethrough',      'editor_action(this.id)',  'ed_format_strike.gif'],
    "subscript":      ['SubScript',            'Subscript',          'editor_action(this.id)',  'ed_format_sub.gif'],
    "superscript":    ['SuperScript',          'Superscript',        'editor_action(this.id)',  'ed_format_sup.gif'],
    "justifyleft":    ['JustifyLeft',          'Justify Left',       'editor_action(this.id)',  'ed_align_left.gif'],
    "justifycenter":  ['JustifyCenter',        'Justify Center',     'editor_action(this.id)',  'ed_align_center.gif'],
    "justifyright":   ['JustifyRight',         'Justify Right',      'editor_action(this.id)',  'ed_align_right.gif'],
    "orderedlist":    ['InsertOrderedList',    'Ordered List',       'editor_action(this.id)',  'ed_list_num.gif'],
    "unorderedlist":  ['InsertUnorderedList',  'Bulleted List',      'editor_action(this.id)',  'ed_list_bullet.gif'],
    "outdent":        ['Outdent',              'Decrease Indent',    'editor_action(this.id)',  'ed_indent_less.gif'],
    "indent":         ['Indent',               'Increase Indent',    'editor_action(this.id)',  'ed_indent_more.gif'],
    "forecolor":      ['ForeColor',            'Font Color',         'editor_action(this.id)',  'ed_color_fg.gif'],
    "backcolor":      ['BackColor',            'Background Color',   'editor_action(this.id)',  'ed_color_bg.gif'],
    "horizontalrule": ['InsertHorizontalRule', 'Horizontal Rule',    'editor_action(this.id)',  'ed_hr.gif'],
    "createlink":     ['CreateLink',           'Insert Web Link',    'editor_action(this.id)',  'ed_link.gif'],
    "insertimage":    ['InsertImage',          'Insert Image',       'editor_action(this.id)',  'ed_image.gif'],
    "inserttable":    ['InsertTable',          'Insert Table',       'editor_action(this.id)',  'insert_table.gif'],
    "htmlmode":       ['HtmlMode',             'View HTML Source',   'editor_setmode(\''+objname+'\')', 'ed_html.gif'],
    "popupeditor":    ['popupeditor',          'Enlarge Editor',     'editor_action(this.id)',  'fullscreen_maximize.gif'],
    "about":          ['about',                'About this editor',  'editor_about(\''+objname+'\')',  'ed_about.gif'],
	"word": ['word', 'Cleanup MS-WORD tags" ', 'editor_action(this.id)', 'ed_image.gif'], 
    // end: custom buttons

    "help":           ['showhelp',             'Help using editor',  'editor_action(this.id)',  'ed_help.gif']};


};

/* ---------------------------------------------------------------------- *\
  Function    : editor_generate
  Description : replace textarea with wysiwyg editor
  Usage       : editor_generate("textarea_id",[height],[width]);
  Arguments   : objname - ID of textarea to replace
                w       - width of wysiwyg editor
                h       - height of wysiwyg editor
\* ---------------------------------------------------------------------- */


function editor_generate(objname,userConfig) {

  // Default Settings
  var config = new editor_defaultConfig(objname);
  if (userConfig) { 
    for (var thisName in userConfig) {
      if (userConfig[thisName]) { config[thisName] = userConfig[thisName]; }
    }
  }
  document.all[objname].config = config;                  // store config settings

  // set size to specified size or size of original object
  var obj    = document.all[objname];
  if (!config.width || config.width == "auto") {
    if      (obj.style.width) { config.width = obj.style.width; }      // use css style
    else if (obj.cols)        { config.width = (obj.cols * 8) + 22; }  // col width + toolbar
    else                      { config.width = '100%'; }               // default
  }
  if (!config.height || config.height == "auto") {
    if      (obj.style.height) { config.height = obj.style.height; }   // use css style
    else if (obj.rows)         { config.height = obj.rows * 17 }       // row height
    else                       { config.height = '200'; }              // default
  }

  var tblOpen  = '<table border=0 cellspacing=0 cellpadding=0 style="float: left;"  unselectable="on"><tr><td style="border: none; padding: 1 0 0 0"><nobr>';
  var tblClose = '</nobr></td></tr></table>\n';

  // build button toolbar

  var toolbar = '';
  var btnGroup, btnItem, aboutEditor;
  for (var btnGroup in config.toolbar) {

    // linebreak
    if (config.toolbar[btnGroup].length == 1 &&
        config.toolbar[btnGroup][0].toLowerCase() == "linebreak") {
      toolbar += '<br clear="all">';
      continue;
    }

    toolbar += tblOpen;
    for (var btnItem in config.toolbar[btnGroup]) {
      var btnName = config.toolbar[btnGroup][btnItem].toLowerCase();

      // fontname
      if (btnName == "fontname") {
        toolbar += '<select id="_' +objname+ '_FontName" onChange="editor_action(this.id)" unselectable="on" style="margin: 1 2 0 2; font-size: 12px;">';
        for (var fontname in config.fontnames) {
          toolbar += '<option value="' +config.fontnames[fontname]+ '">' +fontname+ '</option>'
        }
        toolbar += '</select>';
        continue;
      }

      // fontsize
      if (btnName == "fontsize") {
        toolbar += '<select id="_' +objname+ '_FontSize" onChange="editor_action(this.id)" unselectable="on" style="margin: 1 2 0 0; font-size: 12px;">';
        for (var fontsize in config.fontsizes) {
          toolbar += '<option value="' +config.fontsizes[fontsize]+ '">' +fontsize+ '</option>'
        }
        toolbar += '</select>\n';
        continue;
      }

      // font style
      if (btnName == "fontstyle") {
        toolbar += '<select id="_' +objname+ '_FontStyle" onChange="editor_action(this.id)" unselectable="on" style="margin: 1 2 0 0; font-size: 12px;">';
        + '<option value="">Font Style</option>';
        for (var i in config.fontstyles) {
          var fontstyle = config.fontstyles[i];
          toolbar += '<option value="' +fontstyle.className+ '">' +fontstyle.name+ '</option>'
        }
        toolbar += '</select>';
        continue;
      }

      // separator
      if (btnName == "separator") {
        toolbar += '<span style="border: 1px inset; width: 1px; font-size: 16px; height: 16px; margin: 0 3 0 3"></span>';
        continue;
      }

      // buttons
      var btnObj = config.btnList[btnName];
      if (btnName == 'linebreak') { alert("htmlArea error: 'linebreak' must be in a subgroup by itself, not with other buttons.\n\nhtmlArea wysiwyg editor not created."); return; }
      if (!btnObj) { alert("htmlArea error: button '" +btnName+ "' not found in button list when creating the wysiwyg editor for '"+objname+"'.\nPlease make sure you entered the button name correctly.\n\nhtmlArea wysiwyg editor not created."); return; }
      var btnCmdID   = btnObj[0];
      var btnTitle   = btnObj[1];
      var btnOnClick = btnObj[2];
      var btnImage   = btnObj[3];
      toolbar += '<button title="' +btnTitle+ '" id="_' +objname+ '_' +btnCmdID+ '" class="btn" onClick="' +btnOnClick+ '" onmouseover="if(this.className==\'btn\'){this.className=\'btnOver\'}" onmouseout="if(this.className==\'btnOver\'){this.className=\'btn\'}" unselectable="on"><img src="' +config.imgURL + btnImage+ '" border=0 unselectable="on"></button>';


    } // end of button sub-group
    toolbar += tblClose;
  } // end of entire button set

  // build editor

  var editor = '<span id="_editor_toolbar"><table border=0 cellspacing=0 cellpadding=0 bgcolor="buttonface" style="padding: 1 0 0 2" width=' + config.width + ' unselectable="on"><tr><td>\n'
  + toolbar
  + '</td></tr></table>\n'
  + '</td></tr></table></span>\n'
  + '<textarea ID="_' +objname + '_editor" style="width:' +config.width+ '; height:' +config.height+ '; margin-top: -1px; margin-bottom: -1px;" wrap=soft></textarea>';

  // add context menu
  editor += '<div id="_' +objname + '_cMenu" style="position: absolute; visibility: hidden;"></div>';

  //  hide original textarea and insert htmlarea after it
  if (!config.debug) { document.all[objname].style.display = "none"; }

  if (config.plaintextInput) {     // replace nextlines with breaks
    var contents = document.all[objname].value;
    contents = contents.replace(/\r\n/g, '<br>');
    contents = contents.replace(/\n/g, '<br>');
    contents = contents.replace(/\r/g, '<br>');
    document.all[objname].value = contents;
  }

  // insert wysiwyg
  document.all[objname].insertAdjacentHTML('afterEnd', editor)

  // convert htmlarea from textarea to wysiwyg editor
  editor_setmode(objname, 'init');

  // call filterOutput when user submits form
  for (var idx=0; idx < document.forms.length; idx++) {
    var r = document.forms[idx].attachEvent('onsubmit', function() { editor_filterOutput(objname); });
    if (!r) { alert("Error attaching event to form!"); }
  }

return true;

}

/* ---------------------------------------------------------------------- *\
  Function    : editor_action
  Description : perform an editor command on selected editor content
  Usage       :
  Arguments   : button_id - button id string with editor and action name
\* ---------------------------------------------------------------------- */

function editor_action(button_id) {

  // split up button name into "editorID" and "cmdID"
  var BtnParts = Array();
  BtnParts = button_id.split("_");
  var objname    = button_id.replace(/^_(.*)_[^_]*$/, '$1');
  var cmdID      = BtnParts[ BtnParts.length-1 ];
  var button_obj = document.all[button_id];
  var editor_obj = document.all["_" +objname + "_editor"];
  var config     = document.all[objname].config;

//Built by Chad Rosen 9/30/03
//The code was having a problem opening the popups because it was using absolute referencing instead
//of relative referencing.. So, in order to get it to work correctly I needed to get the base href which I
//define in the header file and append it to all of the window popups

var base = document.getElementsByTagName("base"); //grab the base href from the object named base

//Check to see if it's length is undefined or null and that it has length

if(base!=null && base.length!= undefined && base.length>0) 
	{
	
		base=base[0].href; //if it is then set the base variable to the href
	}else
	{
		base = ""; //else set to blank.. This is important so that the maximized window viewer works
	}


//end new code
  
//Changed by Chad for base href 9/30/03
  // help popup
  if (cmdID == 'showhelp') {
    window.open(base + _editor_url + "popups/editor_help.html", 'EditorHelp');
    return;
  }


//Changed by Chad for base href 9/30/03
  // popup editor
  if (cmdID == 'popupeditor') {
    window.open(base + _editor_url + "popups/fullscreen.html?"+objname,
               'FullScreen',
               'toolbar=no,location=no,directories=no,status=yes,menubar=no,scrollbars=yes,resizable=yes,width=640,height=480');

		

    return;
  }

//_editor_url + 

  // check editor mode (don't perform actions in textedit mode)
  if (editor_obj.tagName.toLowerCase() == 'textarea') { return; }

  var editdoc = editor_obj.contentWindow.document;
  editor_focus(editor_obj);

  // get index and value for pulldowns
  var idx = button_obj.selectedIndex;
  var val = (idx != null) ? button_obj[ idx ].value : null;

  if (0) {}   // use else if for easy cutting and pasting

  //
  // CUSTOM BUTTONS START HERE
  //
/*
  // Custom1
  else if (cmdID == 'custom1') {
    alert("Hello, I am custom button 1!");
  }

  // Custom2
  else if (cmdID == 'custom2') {  // insert some text from a popup window
    var myTitle = "This is a custom title";
    var myText = showModalDialog(_editor_url + "popups/custom2.html",
                                 myTitle,      // str or obj specified here can be read from dialog as "window.dialogArguments"
                                 "resizable: yes; help: no; status: no; scroll: no; ");
    if (myText) { editor_insertHTML(objname, myText); }
  }

  // Custom3
  else if (cmdID == 'custom3') {  // insert some text
    editor_insertHTML(objname, "It's easy to add buttons that insert text!");
  }
*/


  else if (cmdID == 'word') {  
  var oTags = editdoc.all.tags("SPAN");  	
  if (oTags != null) {  		
  for (var i = oTags.length - 1; i >= 0; i--) {  			
  oTags[i].outerHTML = oTags[i].innerHTML;  		
  }  	
}    	

oTags = editdoc.all.tags("FONT");  	
if (oTags != null) {  	
for (var i = oTags.length - 1; i >= 0; i--) {  	
oTags[i].outerHTML = oTags[i].innerHTML;  	
}  	
}

  oTags = editdoc.all.tags("P");  	
  if (oTags != null) {  		
  for (var i = oTags.length - 1; i >= 0; i--) {  			
  cleanEmptyTag(oTags[i]);  		
  }  	
}  
  oTags = editdoc.all.tags("H1");  	
  if (oTags != null) {  		
  for (var i = oTags.length - 1; i >= 0; i--) {  			
  cleanEmptyTag(oTags[i]); 		
}  
}  
  oTags = editdoc.all.tags("H2");  	
  if (oTags != null) {  		
  for (var i = oTags.length - 1; i >= 0; i--) {  			
  cleanEmptyTag(oTags[i]); 		
}  
}
  oTags = editdoc.all.tags("H3");  	
  if (oTags != null) {  		
  for (var i = oTags.length - 1; i >= 0; i--) {  			
  cleanEmptyTag(oTags[i]); 		
}  
}
  oTags = editdoc.all.tags("H4");  	
  if (oTags != null) {  		
  for (var i = oTags.length - 1; i >= 0; i--) {  			
  cleanEmptyTag(oTags[i]); 		
}  
}	
  oTags = editdoc.all.tags("OL");  	
  if (oTags != null) {  		
  for (var i = oTags.length - 1; i >= 0; i--) {  			
  cleanEmptyTag(oTags[i]); 		
}  
}    	
  oTags = editdoc.all.tags("UL");  	
  if (oTags != null) {  		
  for (var i = oTags.length - 1; i >= 0; i--) {  			
  cleanEmptyTag(oTags[i]);  		}  	
}  
}


  //
  // END OF CUSTOM BUTTONS
  //

  // FontName
  else if (cmdID == 'FontName' && val) {
    editdoc.execCommand(cmdID,0,val);
  }

  // FontSize
  else if (cmdID == 'FontSize' && val) {
    editdoc.execCommand(cmdID,0,val);
  }

  // FontStyle (change CSS className)
  else if (cmdID == 'FontStyle' && val) {
    editdoc.execCommand('RemoveFormat');
    editdoc.execCommand('FontName',0,'636c6173734e616d6520706c616365686f6c646572');
    var fontArray = editdoc.all.tags("FONT");
    for (i=0; i<fontArray.length; i++) {
      if (fontArray[i].face == '636c6173734e616d6520706c616365686f6c646572') {
        fontArray[i].face = "";
        fontArray[i].className = val;
        fontArray[i].outerHTML = fontArray[i].outerHTML.replace(/face=['"]+/, "");
        }
    }
    button_obj.selectedIndex =0;
  }

//Changed by Chad for base href 9/30/03
  // fgColor and bgColor
  else if (cmdID == 'ForeColor' || cmdID == 'BackColor') {
    var oldcolor = _dec_to_rgb(editdoc.queryCommandValue(cmdID));
    var newcolor = showModalDialog(base + _editor_url + "popups/select_color.html", oldcolor, "resizable: no; help: no; status: no; scroll: no;");
    if (newcolor != null) { editdoc.execCommand(cmdID, false, "#"+newcolor); }
  }

  // execute command for buttons - if we didn't catch the cmdID by here we'll assume it's a
  // commandID and pass it to execCommand().   See http://msdn.microsoft.com/workshop/author/dhtml/reference/commandids.asp
  else {
    // subscript & superscript, disable one before enabling the other
    if (cmdID.toLowerCase() == 'subscript' && editdoc.queryCommandState('superscript')) { editdoc.execCommand('superscript'); }
    if (cmdID.toLowerCase() == 'superscript' && editdoc.queryCommandState('subscript')) { editdoc.execCommand('subscript'); }

    // insert link
    if (cmdID.toLowerCase() == 'createlink'){
      editdoc.execCommand(cmdID,1);
    }

//Changed by Chad for base href 9/30/03
    // insert image
    else if (cmdID.toLowerCase() == 'insertimage'){
      showModalDialog(base + _editor_url + "popups/insert_image.html", editdoc, "resizable: no; help: no; status: no; scroll: no; ");
    }

//Changed by Chad for base href 9/30/03
    // insert table
    else if (cmdID.toLowerCase() == 'inserttable'){
      showModalDialog(base + _editor_url + "popups/insert_table.html?"+objname,
                                 window,
                                 "resizable: yes; help: no; status: no; scroll: no; ");
    }

    // all other commands microsoft Command Identifiers
    else { editdoc.execCommand(cmdID); }
  }

  editor_event(objname);
}

/* ---------------------------------------------------------------------- *\
  Function    : editor_event
  Description : called everytime an editor event occurs
  Usage       : editor_event(objname, runDelay, eventName)
  Arguments   : objname - ID of textarea to replace
                runDelay: -1 = run now, no matter what
                          0  = run now, if allowed
                        1000 = run in 1 sec, if allowed at that point
\* ---------------------------------------------------------------------- */

function editor_event(objname,runDelay) {
  var config = document.all[objname].config;
  var editor_obj  = document.all["_" +objname+  "_editor"];       // html editor object
  if (runDelay == null) { runDelay = 0; }
  var editdoc;
  var editEvent = editor_obj.contentWindow ? editor_obj.contentWindow.event : event;

  // catch keypress events
    if (editEvent && editEvent.keyCode) {
      var ord       = editEvent.keyCode;    // ascii order of key pressed
      var ctrlKey   = editEvent.ctrlKey;
      var altKey    = editEvent.altKey;
      var shiftKey  = editEvent.shiftKey;

      if (ord == 16) { return; }  // ignore shift key by itself
      if (ord == 17) { return; }  // ignore ctrl key by itself
      if (ord == 18) { return; }  // ignore alt key by itself


       // cancel ENTER key and insert <BR> instead
//       if (ord == 13 && editEvent.type == 'keypress') {
//         editEvent.returnValue = false;
//         editor_insertHTML(objname, "<br>");
//         return;
//       }

      if (ctrlKey && (ord == 122 || ord == 90)) {     // catch ctrl-z (UNDO)
//      TODO: Add our own undo/redo functionality
//        editEvent.cancelBubble = true;
        return;
      }
      if ((ctrlKey && (ord == 121 || ord == 89)) ||
          ctrlKey && shiftKey && (ord == 122 || ord == 90)) {     // catch ctrl-y, ctrl-shift-z (REDO)
//      TODO: Add our own undo/redo functionality
        return;
      }
    }

  // setup timer for delayed updates (some events take time to complete)
  if (runDelay > 0) { return setTimeout(function(){ editor_event(objname); }, runDelay); }

  // don't execute more than 3 times a second (eg: too soon after last execution)
  if (this.tooSoon == 1 && runDelay >= 0) { this.queue = 1; return; } // queue all but urgent events
  this.tooSoon = 1;
  setTimeout(function(){
    this.tooSoon = 0;
    if (this.queue) { editor_event(objname,-1); };
    this.queue = 0;
    }, 333);  // 1/3 second


  editor_updateOutput(objname);
  editor_updateToolbar(objname);

}

/* ---------------------------------------------------------------------- *\
  Function    : editor_updateToolbar
  Description : update toolbar state
  Usage       :
  Arguments   : objname - ID of textarea to replace
                action  - enable, disable, or update (default action)
\* ---------------------------------------------------------------------- */

function editor_updateToolbar(objname,action) {
  var config = document.all[objname].config;
  var editor_obj  = document.all["_" +objname+  "_editor"];

  // disable or enable toolbar

  if (action == "enable" || action == "disable") {
    var tbItems = new Array('FontName','FontSize','FontStyle');                           // add pulldowns
    for (var btnName in config.btnList) { tbItems.push(config.btnList[btnName][0]); } // add buttons

    for (var idxN in tbItems) {
      var cmdID = tbItems[idxN].toLowerCase();
      var tbObj = document.all["_" +objname+ "_" +tbItems[idxN]];
      if (cmdID == "htmlmode" || cmdID == "about" || cmdID == "showhelp" || cmdID == "popupeditor") { continue; } // don't change these buttons
      if (tbObj == null) { continue; }
      var isBtn = (tbObj.tagName.toLowerCase() == "button") ? true : false;

      if (action == "enable")  { tbObj.disabled = false; if (isBtn) { tbObj.className = 'btn' }}
      if (action == "disable") { tbObj.disabled = true;  if (isBtn) { tbObj.className = 'btnNA' }}
    }
    return;
  }

  // update toolbar state

  if (editor_obj.tagName.toLowerCase() == 'textarea') { return; }   // don't update state in textedit mode
  var editdoc = editor_obj.contentWindow.document;

  // Set FontName pulldown
  var fontname_obj = document.all["_" +objname+ "_FontName"];
  if (fontname_obj) {
    var fontname = editdoc.queryCommandValue('FontName');
    if (fontname == null) { fontname_obj.value = null; }
    else {
      var found = 0;
      for (i=0; i<fontname_obj.length; i++) {
        if (fontname.toLowerCase() == fontname_obj[i].text.toLowerCase()) {
          fontname_obj.selectedIndex = i;
          found = 1;
        }
      }
      if (found != 1) { fontname_obj.value = null; }     // for fonts not in list
    }
  }

  // Set FontSize pulldown
  var fontsize_obj = document.all["_" +objname+ "_FontSize"];
  if (fontsize_obj) {
    var fontsize = editdoc.queryCommandValue('FontSize');
    if (fontsize == null) { fontsize_obj.value = null; }
    else {
      var found = 0;
      for (i=0; i<fontsize_obj.length; i++) {
        if (fontsize == fontsize_obj[i].value) { fontsize_obj.selectedIndex = i; found=1; }
      }
      if (found != 1) { fontsize_obj.value = null; }     // for sizes not in list
    }
  }

  // Set FontStyle pulldown
  var classname_obj = document.all["_" +objname+ "_FontStyle"];
  if (classname_obj) {
    var curRange = editdoc.selection.createRange();

    // check element and element parents for class names
    var pElement;
    if (curRange.length) { pElement = curRange[0]; }              // control tange
    else                 { pElement = curRange.parentElement(); } // text range
    while (pElement && !pElement.className) { pElement = pElement.parentElement; }  // keep going up

    var thisClass = pElement ? pElement.className.toLowerCase() : "";
    if (!thisClass && classname_obj.value) { classname_obj.value = null; }
    else {
      var found = 0;
      for (i=0; i<classname_obj.length; i++) {
        if (thisClass == classname_obj[i].value.toLowerCase()) {
          classname_obj.selectedIndex = i;
          found=1;
        }
      }
      if (found != 1) { classname_obj.value = null; }     // for classes not in list
    }
  }

  // update button states
  var IDList = Array('Bold','Italic','Underline','StrikeThrough','SubScript','SuperScript','JustifyLeft','JustifyCenter','JustifyRight','InsertOrderedList','InsertUnorderedList');
  for (i=0; i<IDList.length; i++) {
    var btnObj = document.all["_" +objname+ "_" +IDList[i]];
    if (btnObj == null) { continue; }
    var cmdActive = editdoc.queryCommandState( IDList[i] );

    if (!cmdActive)  {                                  // option is OK
      if (btnObj.className != 'btn') { btnObj.className = 'btn'; }
      if (btnObj.disabled  != false) { btnObj.disabled = false; }
    } else if (cmdActive)  {                            // option already applied or mixed content
      if (btnObj.className != 'btnDown') { btnObj.className = 'btnDown'; }
      if (btnObj.disabled  != false)   { btnObj.disabled = false; }
    }
  }
}

/* ---------------------------------------------------------------------- *\
  Function    : editor_updateOutput
  Description : update hidden output field with data from wysiwg
\* ---------------------------------------------------------------------- */

function editor_updateOutput(objname) {
  var config     = document.all[objname].config;
  var editor_obj  = document.all["_" +objname+  "_editor"];       // html editor object
  var editEvent = editor_obj.contentWindow ? editor_obj.contentWindow.event : event;
  var isTextarea = (editor_obj.tagName.toLowerCase() == 'textarea');
  var editdoc = isTextarea ? null : editor_obj.contentWindow.document;

  // get contents of edit field
  var contents;
  if (isTextarea) { contents = editor_obj.value; }
  else            { contents = editdoc.body.innerHTML; }

  // check if contents has changed since the last time we ran this routine
  if (config.lastUpdateOutput && config.lastUpdateOutput == contents) { return; }
  else { config.lastUpdateOutput = contents; }

  // update hidden output field
  document.all[objname].value = contents;

}

/* ---------------------------------------------------------------------- *\
  Function    : editor_filterOutput
  Description :
\* ---------------------------------------------------------------------- */

function editor_filterOutput(objname) {
  editor_updateOutput(objname);
  var contents = document.all[objname].value;
  var config   = document.all[objname].config;

  // ignore blank contents
  if (contents.toLowerCase() == '<p>&nbsp;</p>') { contents = ""; }

  // filter tag - this code is run for each HTML tag matched
  var filterTag = function(tagBody,tagName,tagAttr) {
    tagName = tagName.toLowerCase();
    var closingTag = (tagBody.match(/^<\//)) ? true : false;

    // fix placeholder URLS - remove absolute paths that IE adds
    if (tagName == 'img') { tagBody = tagBody.replace(/(src\s*=\s*.)[^*]*(\*\*\*)/, "$1$2"); }
    if (tagName == 'a')   { tagBody = tagBody.replace(/(href\s*=\s*.)[^*]*(\*\*\*)/, "$1$2"); }

    // add additional tag filtering here

    // convert to vbCode
//    if      (tagName == 'b' || tagName == 'strong') {
//      if (closingTag) { tagBody = "[/b]"; } else { tagBody = "[b]"; }
//    }
//    else if (tagName == 'i' || tagName == 'em') {
//      if (closingTag) { tagBody = "[/i]"; } else { tagBody = "[i]"; }
//    }
//    else if (tagName == 'u') {
//      if (closingTag) { tagBody = "[/u]"; } else { tagBody = "[u]"; }
//    }
//    else {
//      tagBody = ""; // disallow all other tags!
//    }

    return tagBody;
  };

  // match tags and call filterTag
  RegExp.lastIndex = 0;
    var matchTag = /<\/?(\w+)((?:[^'">]*|'[^']*'|"[^"]*")*)>/g;   // this will match tags, but still doesn't handle container tags (textarea, comments, etc)

  contents = contents.replace(matchTag, filterTag);

  // remove nextlines from output (if requested)
  if (config.replaceNextlines) { 
    contents = contents.replace(/\r\n/g, ' ');
    contents = contents.replace(/\n/g, ' ');
    contents = contents.replace(/\r/g, ' ');
  }

  // update output with filtered content
  document.all[objname].value = contents;

}

/* ---------------------------------------------------------------------- *\
  Function    : editor_setmode
  Description : change mode between WYSIWYG and HTML editor
  Usage       : editor_setmode(objname, mode);
  Arguments   : objname - button id string with editor and action name
                mode      - init, textedit, or wysiwyg
\* ---------------------------------------------------------------------- */

function editor_setmode(objname, mode) {
  var config     = document.all[objname].config;
  var editor_obj = document.all["_" +objname + "_editor"];

  // wait until document is fully loaded
  if (document.readyState != 'complete') {
    setTimeout(function() { editor_setmode(objname,mode) }, 25);
    return;
  }

  // define different editors
  var TextEdit   = '<textarea ID="_' +objname + '_editor" style="width:' +editor_obj.style.width+ '; height:' +editor_obj.style.height+ '; margin-top: -1px; margin-bottom: -1px;"></textarea>';
  var RichEdit   = '<iframe ID="_' +objname+ '_editor"    style="width:' +editor_obj.style.width+ '; height:' +editor_obj.style.height+ ';"></iframe>';

 // src="' +_editor_url+ 'popups/blank.html"

  //
  // Switch to TEXTEDIT mode
  //

  if (mode == "textedit" || editor_obj.tagName.toLowerCase() == 'iframe') {
    config.mode = "textedit";
    var editdoc = editor_obj.contentWindow.document;
    var contents = editdoc.body.createTextRange().htmlText;
    editor_obj.outerHTML = TextEdit;
    editor_obj = document.all["_" +objname + "_editor"];
    editor_obj.value = contents;
    editor_event(objname);

    editor_updateToolbar(objname, "disable");  // disable toolbar items

    // set event handlers
    editor_obj.onkeydown   = function() { editor_event(objname); }
    editor_obj.onkeypress  = function() { editor_event(objname); }
    editor_obj.onkeyup     = function() { editor_event(objname); }
    editor_obj.onmouseup   = function() { editor_event(objname); }
    editor_obj.ondrop      = function() { editor_event(objname, 100); }     // these events fire before they occur
    editor_obj.oncut       = function() { editor_event(objname, 100); }
    editor_obj.onpaste     = function() { editor_event(objname, 100); }
    editor_obj.onblur      = function() { editor_event(objname, -1); }

    editor_updateOutput(objname);
    editor_focus(editor_obj);
  }

  //
  // Switch to WYSIWYG mode
  //

  else {
    config.mode = "wysiwyg";
    var contents = editor_obj.value;
    if (mode == 'init') { contents = document.all[objname].value; } // on init use original textarea content

    // create editor
    editor_obj.outerHTML = RichEdit;
    editor_obj = document.all["_" +objname + "_editor"];

    // get iframe document object

    // create editor contents (and default styles for editor)
    var html = "";
    html += '<html><head>\n';
    if (config.stylesheet) {
      html += '<link href="' +config.stylesheet+ '" rel="stylesheet" type="text/css">\n';
    }
    html += '<style>\n';
    html += 'body {' +config.bodyStyle+ '} \n';
    for (var i in config.fontstyles) {
      var fontstyle = config.fontstyles[i];
      if (fontstyle.classStyle) {
        html += '.' +fontstyle.className+ ' {' +fontstyle.classStyle+ '}\n';
      }
    }
    html += '</style>\n'
      + '</head>\n'
      + '<body contenteditable="true" topmargin=1 leftmargin=1'

// still working on this
//      + ' oncontextmenu="parent.editor_cMenu_generate(window,\'' +objname+ '\');"'
      +'>'
      + contents
      + '</body>\n'
      + '</html>\n';

    // write to editor window
    var editdoc = editor_obj.contentWindow.document;

    editdoc.open();
    editdoc.write(html);
    editdoc.close();

    editor_updateToolbar(objname, "enable");  // enable toolbar items

    // store objname under editdoc
    editdoc.objname = objname;

    // set event handlers
    editdoc.onkeydown      = function() { editor_event(objname); }
    editdoc.onkeypress     = function() { editor_event(objname); }
    editdoc.onkeyup        = function() { editor_event(objname); }
    editdoc.onmouseup      = function() { editor_event(objname); }
    editdoc.body.ondrop    = function() { editor_event(objname, 100); }     // these events fire before they occur
    editdoc.body.oncut     = function() { editor_event(objname, 100); }
    editdoc.body.onpaste   = function() { editor_event(objname, 100); }
    editdoc.body.onblur    = function() { editor_event(objname, -1); }

    // bring focus to editor
    if (mode != 'init') {             // don't focus on page load, only on mode switch
      editor_focus(editor_obj);
    }

  }

  // Call update UI
  if (mode != 'init') {             // don't update UI on page load, only on mode switch
    editor_event(objname);
  }

}

/* ---------------------------------------------------------------------- *\
  Function    : editor_focus
  Description : bring focus to the editor
  Usage       : editor_focus(editor_obj);
  Arguments   : editor_obj - editor object
\* ---------------------------------------------------------------------- */

function editor_focus(editor_obj) {

  // check editor mode
  if (editor_obj.tagName.toLowerCase() == 'textarea') {         // textarea
    var myfunc = function() { editor_obj.focus(); };
    setTimeout(myfunc,100);                                     // doesn't work all the time without delay
  }

  else {                                                        // wysiwyg
    var editdoc = editor_obj.contentWindow.document;            // get iframe editor document object
    var editorRange = editdoc.body.createTextRange();           // editor range
    var curRange    = editdoc.selection.createRange();          // selection range

    if (curRange.length == null &&                              // make sure it's not a controlRange
        !editorRange.inRange(curRange)) {                       // is selection in editor range
      editorRange.collapse();                                   // move to start of range
      editorRange.select();                                     // select
      curRange = editorRange;
    }
  }

}

/* ---------------------------------------------------------------------- *\
  Function    : editor_about
  Description : display "about this editor" popup
\* ---------------------------------------------------------------------- */

function editor_about(objname) {
  showModalDialog(_editor_url + "popups/about.html", window, "resizable: yes; help: no; status: no; scroll: no; ");
}

/* ---------------------------------------------------------------------- *\
  Function    : _dec_to_rgb
  Description : convert dec color value to rgb hex
  Usage       : var hex = _dec_to_rgb('65535');   // returns FFFF00
  Arguments   : value   - dec value
\* ---------------------------------------------------------------------- */

function _dec_to_rgb(value) {
  var hex_string = "";
  for (var hexpair = 0; hexpair < 3; hexpair++) {
    var myByte = value & 0xFF;            // get low byte
    value >>= 8;                        // drop low byte
    var nybble2 = myByte & 0x0F;          // get low nybble (4 bits)
    var nybble1 = (myByte >> 4) & 0x0F;   // get high nybble
    hex_string += nybble1.toString(16); // convert nybble to hex
    hex_string += nybble2.toString(16); // convert nybble to hex
  }
  return hex_string.toUpperCase();
}

/* ---------------------------------------------------------------------- *\
  Function    : editor_insertHTML
  Description : insert string at current cursor position in editor.  If
                two strings are specifed, surround selected text with them.
  Usage       : editor_insertHTML(objname, str1, [str2], reqSelection)
  Arguments   : objname - ID of textarea
                str1 - HTML or text to insert
                str2 - HTML or text to insert (optional argument)
                reqSelection - (1 or 0) give error if no text selected
\* ---------------------------------------------------------------------- */

function editor_insertHTML(objname, str1,str2, reqSel) {
  var config     = document.all[objname].config;
  var editor_obj = document.all["_" +objname + "_editor"];    // editor object
  if (str1 == null) { str1 = ''; }
  if (str2 == null) { str2 = ''; }

  // for non-wysiwyg capable browsers just add to end of textbox
  if (document.all[objname] && editor_obj == null) {
    document.all[objname].focus();
    document.all[objname].value = document.all[objname].value + str1 + str2;
    return;
  }

  // error checking
  if (editor_obj == null) { return alert("Unable to insert HTML.  Invalid object name '" +objname+ "'."); }

  editor_focus(editor_obj);

  var tagname = editor_obj.tagName.toLowerCase();
  var sRange;

 // insertHTML for wysiwyg iframe
  if (tagname == 'iframe') {
    var editdoc = editor_obj.contentWindow.document;
    sRange  = editdoc.selection.createRange();
    var sHtml   = sRange.htmlText;

    // check for control ranges
    if (sRange.length) { return alert("Unable to insert HTML.  Try highlighting content instead of selecting it."); }

    // insert HTML
    var oldHandler = window.onerror;
    window.onerror = function() { alert("Unable to insert HTML for current selection."); return true; } // partial table selections cause errors
    if (sHtml.length) {                                 // if content selected
      if (str2) { sRange.pasteHTML(str1 +sHtml+ str2) } // surround
      else      { sRange.pasteHTML(str1); }             // overwrite
    } else {                                            // if insertion point only
      if (reqSel) { return alert("Unable to insert HTML.  You must select something first."); }
      sRange.pasteHTML(str1 + str2);                    // insert strings
    }
    window.onerror = oldHandler;
  }

  // insertHTML for plaintext textarea
  else if (tagname == 'textarea') {
    editor_obj.focus();
    sRange  = document.selection.createRange();
    var sText   = sRange.text;

    // insert HTML
    if (sText.length) {                                 // if content selected
      if (str2) { sRange.text = str1 +sText+ str2; }  // surround
      else      { sRange.text = str1; }               // overwrite
    } else {                                            // if insertion point only
      if (reqSel) { return alert("Unable to insert HTML.  You must select something first."); }
      sRange.text = str1 + str2;                        // insert strings
    }
  }
  else { alert("Unable to insert HTML.  Unknown object tag type '" +tagname+ "'."); }

  // move to end of new content
  sRange.collapse(false); // move to end of range
  sRange.select();        // re-select

}

/* ---------------------------------------------------------------------- *\
  Function    : editor_getHTML
  Description : return HTML contents of editor (in either wywisyg or html mode)
  Usage       : var myHTML = editor_getHTML('objname');
\* ---------------------------------------------------------------------- */

function editor_getHTML(objname) {
  var editor_obj = document.all["_" +objname + "_editor"];
  var isTextarea = (editor_obj.tagName.toLowerCase() == 'textarea');

  if (isTextarea) { return editor_obj.value; }
  else            { return editor_obj.contentWindow.document.body.innerHTML; }
}

/* ---------------------------------------------------------------------- *\
  Function    : editor_setHTML
  Description : set HTML contents of editor (in either wywisyg or html mode)
  Usage       : editor_setHTML('objname',"<b>html</b> <u>here</u>");
\* ---------------------------------------------------------------------- */

function editor_setHTML(objname, html) {
  var editor_obj = document.all["_" +objname + "_editor"];
  var isTextarea = (editor_obj.tagName.toLowerCase() == 'textarea');

  if (isTextarea) { editor_obj.value = html; }
  else            { editor_obj.contentWindow.document.body.innerHTML = html; }
}

/* ---------------------------------------------------------------------- *\
  Function    : editor_appendHTML
  Description : append HTML contents to editor (in either wywisyg or html mode)
  Usage       : editor_appendHTML('objname',"<b>html</b> <u>here</u>");
\* ---------------------------------------------------------------------- */

function editor_appendHTML(objname, html) {
  var editor_obj = document.all["_" +objname + "_editor"];
  var isTextarea = (editor_obj.tagName.toLowerCase() == 'textarea');

  if (isTextarea) { editor_obj.value += html; }
  else            { editor_obj.contentWindow.document.body.innerHTML += html; }
}

/* ---------------------------------------------------------------- */

function _isMouseOver(obj,event) {       // determine if mouse is over object
  var mouseX    = event.clientX;
  var mouseY    = event.clientY;

  var objTop    = obj.offsetTop;
  var objBottom = obj.offsetTop + obj.offsetHeight;
  var objLeft   = obj.offsetLeft;
  var objRight  = obj.offsetLeft + obj.offsetWidth;

  if (mouseX >= objLeft && mouseX <= objRight &&
      mouseY >= objTop  && mouseY <= objBottom) { return true; }

  return false;
}

/* ---------------------------------------------------------------- */

function editor_cMenu_generate(editorWin,objname) {
  var parentWin = window;
  editorWin.event.returnValue = false;  // cancel default context menu

  // define content menu options
  var cMenuOptions = [ // menu name, shortcut displayed, javascript code
    ['Cut', 'Ctrl-X', function() {}],
    ['Copy', 'Ctrl-C', function() {}],
    ['Paste', 'Ctrl-C', function() {}],
    ['Delete', 'DEL', function() {}],
    ['---', null, null],
    ['Select All', 'Ctrl-A', function() {}],
    ['Clear All', '', function() {}],
    ['---', null, null],
    ['About this editor...', '', function() {
      alert("about this editor");
    }]];
    editor_cMenu.options = cMenuOptions; // save options

  // generate context menu
  var cMenuHeader = ''
    + '<div id="_'+objname+'_cMenu" onblur="editor_cMenu(this);" oncontextmenu="return false;" onselectstart="return false"'
    + '  style="position: absolute; visibility: hidden; cursor: default; width: 167px; background-color: threedface;'
    + '         border: solid 1px; border-color: threedlightshadow threeddarkshadow threeddarkshadow threedlightshadow;">'
    + '<table border=0 cellspacing=0 cellpadding=0 width="100%" style="width: 167px; background-color: threedface; border: solid 1px; border-color: threedhighlight threedshadow threedshadow threedhighlight;">'
    + ' <tr><td colspan=2 height=1></td></tr>';

  var cMenuList = '';

  var cMenuFooter = ''
    + ' <tr><td colspan=2 height=1></td></tr>'
    + '</table></div>';

  for (var menuIdx in editor_cMenu.options) {
    var menuName = editor_cMenu.options[menuIdx][0];
    var menuKey  = editor_cMenu.options[menuIdx][1];
    var menuCode = editor_cMenu.options[menuIdx][2];

    // separator
    if (menuName == "---" || menuName == "separator") {
      cMenuList += ' <tr><td colspan=2 class="cMenuDivOuter"><div class="cMenuDivInner"></div></td></tr>';
    }

    // menu option
    else {
      cMenuList += '<tr class="cMenu" onMouseOver="editor_cMenu(this)" onMouseOut="editor_cMenu(this)" onClick="editor_cMenu(this, \'' +menuIdx+ '\',\'' +objname+ '\')">';
      if (menuKey) { cMenuList += ' <td align=left class="cMenu">' +menuName+ '</td><td align=right class="cMenu">' +menuKey+ '</td>'; }
      else         { cMenuList += ' <td colspan=2 class="cMenu">' +menuName+ '</td>'; }
      cMenuList += '</tr>';
    }
  }

  var cMenuHTML = cMenuHeader + cMenuList + cMenuFooter;


  document.all['_'+objname+'_cMenu'].outerHTML = cMenuHTML;

  editor_cMenu_setPosition(parentWin, editorWin, objname);

  parentWin['_'+objname+'_cMenu'].style.visibility = 'visible';
  parentWin['_'+objname+'_cMenu'].focus();

}

/* ---------------------------------------------------------------- */

function editor_cMenu_setPosition(parentWin, editorWin, objname) {      // set object position that won't overlap window edge
  var event    = editorWin.event;
  var cMenuObj = parentWin['_'+objname+'_cMenu'];
  var mouseX   = event.clientX + parentWin.document.all['_'+objname+'_editor'].offsetLeft;
  var mouseY   = event.clientY + parentWin.document.all['_'+objname+'_editor'].offsetTop;
  var cMenuH   = cMenuObj.offsetHeight;
  var cMenuW   = cMenuObj.offsetWidth;
  var pageH    = document.body.clientHeight + document.body.scrollTop;
  var pageW    = document.body.clientWidth + document.body.scrollLeft;

  // set horzontal position
  if (mouseX + 5 + cMenuW > pageW) { var left = mouseX - cMenuW - 5; } // too far right
  else                            { var left = mouseX + 5; }

  // set vertical position
  if (mouseY + 5 + cMenuH > pageH) { var top = mouseY - cMenuH + 5; } // too far down
  else                            { var top = mouseY + 5; }

  cMenuObj.style.top = top;
  cMenuObj.style.left = left;

}

/* ---------------------------------------------------------------- */

function editor_cMenu(obj,menuIdx,objname) {
  var action = event.type;
  if      (action == "mouseover" && !obj.disabled && obj.tagName.toLowerCase() == 'tr') {
    obj.className = 'cMenuOver';
    for (var i=0; i < obj.cells.length; i++) { obj.cells[i].className = 'cMenuOver'; }
  }
  else if (action == "mouseout" && !obj.disabled && obj.tagName.toLowerCase() == 'tr')  {
    obj.className = 'cMenu';
    for (var i=0; i < obj.cells.length; i++) { obj.cells[i].className = 'cMenu'; }
  }
  else if (action == "click" && !obj.disabled) {
    document.all['_'+objname+'_cMenu'].style.visibility = "hidden";
    var menucode = editor_cMenu.options[menuIdx][2];
    menucode();
  }
  else if (action == "blur") {
    if (!_isMouseOver(obj,event)) { obj.style.visibility = 'hidden'; }
    else {
      if (obj.style.visibility != "hidden") { obj.focus(); }
    }
  }
  else { alert("editor_cMenu, unknown action: " + action); }
}


// WME: MS-Word clean-up (begin)
/* ---------------------------------------------------------------------- *\
  Function    : MS-Word clean-up 
  Description : replace textarea with wysiwyg editor
  Usage       : editor_generate("textarea_id",[height],[width]);
  Arguments   : objname - ID of textarea to replace
                w       - width of wysiwyg editor
                h       - height of wysiwyg editor
\* ---------------------------------------------------------------------- */
function cleanEmptyTag(oElem) {  	
if (oElem.hasChildNodes) {  		
var tmp = oElem  		
for (var k = tmp.children.length; k >= 0; k--) {  			
if (tmp.children[k] != null) {  				
cleanEmptyTag(tmp.children[k]);  			
}  		
}  
	
} 
 	
var oAttribs = oElem.attributes;  	
if (oAttribs != null) {  		
for (var j = oAttribs.length - 1; j >=0; j--) {  			
var oAttrib = oAttribs[j];  			
if (oAttrib.nodeValue != null) {  				
oAttribs.removeNamedItem('class')  			
}  	
	}  
	}  
	oElem.style.cssText = '';  	
if (oElem.innerHTML == '' || oElem.innerHTML == '&nbsp;') {  		
oElem.outerHTML = '';  	} 
}

function cleanTable(oElem) {  	
	oElem.style.cssText = '';  	
	var oAttribs = oElem.attributes;  	
	if (oAttribs != null) {  		
		for (var j = oAttribs.length - 1; j >=0; j--) {  			
			var oAttrib = oAttribs[j];  			
			if (oAttrib.nodeValue != null) {  				
				oAttribs.removeNamedItem('class')  			
			}  		
		}  	
	}    	
	var oTR = oElem.rows;  	
	if (oTR != null) {  		
		for (var r = oTR.length - 1; r >= 0; r--) {  			
			oTR[r].style.cssText = '';  		
		}  	
	}    	
	var oTD = oElem.cells;  	
	if (oTD != null) {  		
		for (var t = oTD.length - 1; t >= 0; t--) {  			
			oTD[t].style.cssText = '';  		
		}  	
	}  
}

