// I18N constants

// LANG: "en", ENCODING: UTF-8 | ISO-8859-1
// Author: Mihai Bazon, http://dynarch.com/mishoo

// FOR TRANSLATORS:
//
//   1. PLEASE PUT YOUR CONTACT INFO IN THE ABOVE LINE
//      (at least a valid email address)
//
//   2. PLEASE TRY TO USE UTF-8 FOR ENCODING;
//      (if this is not possible, please include a comment
//       that states what encoding is necessary.)

HTMLArea.I18N = {

	// the following should be the filename without .js extension
	// it will be used for automatically load plugin language.
	lang: "en",

	tooltips: {
		bold:           "Bold",
		italic:         "Italic",
		underline:      "Underline",
		strikethrough:  "Strikethrough",
		subscript:      "Subscript",
		superscript:    "Superscript",
		justifyleft:    "Justify Left",
		justifycenter:  "Justify Center",
		justifyright:   "Justify Right",
		justifyfull:    "Justify Full",
		orderedlist:    "Ordered List",
		unorderedlist:  "Bulleted List",
		outdent:        "Decrease Indent",
		indent:         "Increase Indent",
		forecolor:      "Font Color",
		hilitecolor:    "Background Color",
		horizontalrule: "Horizontal Rule",
		createlink:     "Insert Web Link",
		insertimage:    "Insert/Modify Image",
		inserttable:    "Insert Table",
		htmlmode:       "Toggle HTML Source",
		popupeditor:    "Enlarge Editor",
		about:          "About this editor",
		showhelp:       "Help using editor",
		textindicator:  "Current style",
		undo:           "Undoes your last action",
		redo:           "Redoes your last action",
		cut:            "Cut selection",
		copy:           "Copy selection",
		paste:          "Paste from clipboard",
		lefttoright:    "Direction left to right",
		righttoleft:    "Direction right to left"
	},

	buttons: {
		"ok":           "OK",
		"cancel":       "Cancel"
	},

	msg: {
		"Path":         "Path",
		"TEXT_MODE":    "You are in TEXT MODE.  Use the [<>] button to switch back to WYSIWYG.",

		"IE-sucks-full-screen" :
		// translate here
		"The full screen mode is known to cause problems with Internet Explorer, " +
		"due to browser bugs that we weren't able to workaround.  You might experience garbage " +
		"display, lack of editor functions and/or random browser crashes.  If your system is Windows 9x " +
		"it's very likely that you'll get a 'General Protection Fault' and need to reboot.\n\n" +
		"You have been warned.  Please press OK if you still want to try the full screen editor."
	},

	dialogs: {
		"Cancel"                                            : "Cancel",
		"Insert/Modify Link"                                : "Insert/Modify Link",
		"New window (_blank)"                               : "New window (_blank)",
		"None (use implicit)"                               : "None (use implicit)",
		"OK"                                                : "OK",
		"Other"                                             : "Other",
		"Same frame (_self)"                                : "Same frame (_self)",
		"Target:"                                           : "Target:",
		"Title (tooltip):"                                  : "Title (tooltip):",
		"Top frame (_top)"                                  : "Top frame (_top)",
		"URL:"                                              : "URL:",
		"You must enter the URL where this link points to"  : "You must enter the URL where this link points to"
	}
};
