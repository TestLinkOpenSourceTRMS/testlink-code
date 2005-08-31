/*
	Feel free to use your custom jtree/icons/ for the tree. Make sure they are all of the same size.
	User jtree/icons/ collections are welcome, we'll publish them giving all regards.
*/

var TREE_TPL = {
	'target'  : '_self',	// name of the frame links will be opened in
							// other possible values are: _blank, _parent, _search, _self and _top

	'icon_e'  : 'third_party/jtree/icons/empty.gif', // empty image
	'icon_l'  : 'third_party/jtree/icons/line.gif',  // vertical line
	
	//20050831 - scs - added two item in the case where a product doesnt contain anything
	'icon_32' : 'third_party/jtree/icons/base.gif',   // root icon normal
	'icon_36' : 'third_party/jtree/icons/base.gif',   // root icon selected
	
	'icon_48' : 'third_party/jtree/icons/base.gif',   // root icon normal
	'icon_52' : 'third_party/jtree/icons/base.gif',   // root icon selected
	'icon_56' : 'third_party/jtree/icons/base.gif',   // root icon opened
	'icon_60' : 'third_party/jtree/icons/base.gif',   // root icon selected
	
	'icon_16' : 'third_party/jtree/icons/folder.gif', // node icon normal
	'icon_20' : 'third_party/jtree/icons/folderopen.gif', // node icon selected
	'icon_24' : 'third_party/jtree/icons/folder.gif', // node icon opened
	'icon_28' : 'third_party/jtree/icons/folderopen.gif', // node icon selected opened

	'icon_0'  : 'third_party/jtree/icons/page.gif', // leaf icon normal
	'icon_4'  : 'third_party/jtree/icons/page.gif', // leaf icon selected
	'icon_8'  : 'third_party/jtree/icons/page.gif', // leaf icon opened
	'icon_12' : 'third_party/jtree/icons/page.gif', // leaf icon selected
	
	'icon_2'  : 'third_party/jtree/icons/joinbottom.gif', // junction for leaf
	'icon_3'  : 'third_party/jtree/icons/join.gif',       // junction for last leaf
	'icon_18' : 'third_party/jtree/icons/plusbottom.gif', // junction for closed node
	'icon_19' : 'third_party/jtree/icons/plus.gif',       // junctioin for last closed node
	'icon_26' : 'third_party/jtree/icons/minusbottom.gif',// junction for opened node
	'icon_27' : 'third_party/jtree/icons/minus.gif'       // junctioin for last opended node
};

