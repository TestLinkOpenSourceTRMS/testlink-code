// Spell Checker Plugin for HTMLArea-3.0
// Sponsored by www.americanbible.org
// Implementation by Mihai Bazon, http://dynarch.com/mishoo/
//
// (c) dynarch.com 2003.
// Distributed under the same terms as HTMLArea itself.
// This notice MUST stay intact for use (see license.txt).
//
// $Id: spell-checker.js,v 1.3 2005/08/16 18:01:21 franciscom Exp $

function SpellChecker(editor) {
	this.editor = editor;

	var cfg = editor.config;
	var tt = SpellChecker.I18N;
	var bl = SpellChecker.btnList;
	var self = this;

	// register the toolbar buttons provided by this plugin
	var toolbar = [];
	for (var i in bl) {
		var btn = bl[i];
		if (!btn) {
			toolbar.push("separator");
		} else {
			var id = "SC-" + btn[0];
			cfg.registerButton(id, tt[id], editor.imgURL(btn[0] + ".gif", "SpellChecker"), false,
					   function(editor, id) {
						   // dispatch button press event
						   self.buttonPress(editor, id);
					   }, btn[1]);
			toolbar.push(id);
		}
	}

	for (var i in toolbar) {
		cfg.toolbar[0].push(toolbar[i]);
	}
};

SpellChecker._pluginInfo = {
	name          : "SpellChecker",
	version       : "1.0",
	developer     : "Mihai Bazon",
	developer_url : "http://dynarch.com/mishoo/",
	c_owner       : "Mihai Bazon",
	sponsor       : "American Bible Society",
	sponsor_url   : "http://www.americanbible.org",
	license       : "htmlArea"
};

SpellChecker.btnList = [
	null, // separator
	["spell-check"]
	];

SpellChecker.prototype.buttonPress = function(editor, id) {
	switch (id) {
	    case "SC-spell-check":
		SpellChecker.editor = editor;
		SpellChecker.init = true;
		var uiurl = _editor_url + "plugins/SpellChecker/spell-check-ui.html";
		var win;
		if (HTMLArea.is_ie) {
			win = window.open(uiurl, "SC_spell_checker",
					  "toolbar=no,location=no,directories=no,status=no,menubar=no," +
					  "scrollbars=no,resizable=yes,width=600,height=450");
		} else {
			win = window.open(uiurl, "SC_spell_checker",
					  "toolbar=no,menubar=no,personalbar=no,width=600,height=450," +
					  "scrollbars=no,resizable=yes");
		}
		win.focus();
		break;
	}
};

// this needs to be global, it's accessed from spell-check-ui.html
SpellChecker.editor = null;
