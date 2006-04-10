// ===================================================================
// Author: Matt Kruse <matt@mattkruse.com>
// WWW: http://www.mattkruse.com/
//
// NOTICE: You may use this code for any purpose, commercial or
// private, without any further permission from the author. You may
// remove this notice from your final code if you wish, however it is
// appreciated by the author if at least my web site address is kept.
//
// You may *NOT* re-distribute this code in any way except through its
// use. That means, you can include it in your product, or your web
// site, or any other form where the code is actually being used. You
// may not put the plain javascript up on your site for download or
// include it in your javascript libraries for download. 
// If you wish to share this code with others, please just point them
// to the URL instead.
// Please DO NOT link directly to my .js files from your site. Copy
// the files to your server and use them there. Thank you.
// ===================================================================

/* SOURCE FILE: selectbox.js */
function hasOptions(obj){if(obj!=null && obj.options!=null){return true;}return false;}
function selectUnselectMatchingOptions(obj,regex,which,only){if(window.RegExp){if(which == "select"){var selected1=true;var selected2=false;}else if(which == "unselect"){var selected1=false;var selected2=true;}else{return;}var re = new RegExp(regex);if(!hasOptions(obj)){return;}for(var i=0;i<obj.options.length;i++){if(re.test(obj.options[i].text)){obj.options[i].selected = selected1;}else{if(only == true){obj.options[i].selected = selected2;}}}}}
function selectMatchingOptions(obj,regex){selectUnselectMatchingOptions(obj,regex,"select",false);}
function selectOnlyMatchingOptions(obj,regex){selectUnselectMatchingOptions(obj,regex,"select",true);}
function unSelectMatchingOptions(obj,regex){selectUnselectMatchingOptions(obj,regex,"unselect",false);}
function sortSelect(obj){var o = new Array();if(!hasOptions(obj)){return;}for(var i=0;i<obj.options.length;i++){o[o.length] = new Option( obj.options[i].text, obj.options[i].value, obj.options[i].defaultSelected, obj.options[i].selected) ;}if(o.length==0){return;}o = o.sort(
function(a,b){if((a.text+"") <(b.text+"")){return -1;}if((a.text+"") >(b.text+"")){return 1;}return 0;});for(var i=0;i<o.length;i++){obj.options[i] = new Option(o[i].text, o[i].value, o[i].defaultSelected, o[i].selected);}}
function selectAllOptions(obj){if(!hasOptions(obj)){return;}for(var i=0;i<obj.options.length;i++){obj.options[i].selected = true;}}
function moveSelectedOptions(from,to){if(arguments.length>3){var regex = arguments[3];if(regex != ""){unSelectMatchingOptions(from,regex);}}if(!hasOptions(from)){return;}for(var i=0;i<from.options.length;i++){var o = from.options[i];if(o.selected){if(!hasOptions(to)){var index = 0;}else{var index=to.options.length;}to.options[index] = new Option( o.text, o.value, false, false);}}for(var i=(from.options.length-1);i>=0;i--){var o = from.options[i];if(o.selected){from.options[i] = null;}}if((arguments.length<3) ||(arguments[2]==true)){sortSelect(from);sortSelect(to);}from.selectedIndex = -1;to.selectedIndex = -1;}
function copySelectedOptions(from,to){var options = new Object();if(hasOptions(to)){for(var i=0;i<to.options.length;i++){options[to.options[i].value] = to.options[i].text;}}if(!hasOptions(from)){return;}for(var i=0;i<from.options.length;i++){var o = from.options[i];if(o.selected){if(options[o.value] == null || options[o.value] == "undefined" || options[o.value]!=o.text){if(!hasOptions(to)){var index = 0;}else{var index=to.options.length;}to.options[index] = new Option( o.text, o.value, false, false);}}}if((arguments.length<3) ||(arguments[2]==true)){sortSelect(to);}from.selectedIndex = -1;to.selectedIndex = -1;}
function moveAllOptions(from,to){selectAllOptions(from);if(arguments.length==2){moveSelectedOptions(from,to);}else if(arguments.length==3){moveSelectedOptions(from,to,arguments[2]);}else if(arguments.length==4){moveSelectedOptions(from,to,arguments[2],arguments[3]);}}
function copyAllOptions(from,to){selectAllOptions(from);if(arguments.length==2){copySelectedOptions(from,to);}else if(arguments.length==3){copySelectedOptions(from,to,arguments[2]);}}
function swapOptions(obj,i,j){var o = obj.options;var i_selected = o[i].selected;var j_selected = o[j].selected;var temp = new Option(o[i].text, o[i].value, o[i].defaultSelected, o[i].selected);var temp2= new Option(o[j].text, o[j].value, o[j].defaultSelected, o[j].selected);o[i] = temp2;o[j] = temp;o[i].selected = j_selected;o[j].selected = i_selected;}
function moveOptionUp(obj){if(!hasOptions(obj)){return;}for(i=0;i<obj.options.length;i++){if(obj.options[i].selected){if(i != 0 && !obj.options[i-1].selected){swapOptions(obj,i,i-1);obj.options[i-1].selected = true;}}}}
function moveOptionDown(obj){if(!hasOptions(obj)){return;}for(i=obj.options.length-1;i>=0;i--){if(obj.options[i].selected){if(i !=(obj.options.length-1) && ! obj.options[i+1].selected){swapOptions(obj,i,i+1);obj.options[i+1].selected = true;}}}}
function removeSelectedOptions(from){if(!hasOptions(from)){return;}for(var i=(from.options.length-1);i>=0;i--){var o=from.options[i];if(o.selected){from.options[i] = null;}}from.selectedIndex = -1;}
function removeAllOptions(from){if(!hasOptions(from)){return;}for(var i=(from.options.length-1);i>=0;i--){from.options[i] = null;}from.selectedIndex = -1;}
function addOption(obj,text,value,selected){if(obj!=null && obj.options!=null){obj.options[obj.options.length] = new Option(text, value, false, selected);}}


/* SOURCE FILE: OptionTransfer.js */

function OT_transferLeft(){moveSelectedOptions(this.right,this.left,this.autoSort,this.staticOptionRegex);this.update();}
function OT_transferRight(){moveSelectedOptions(this.left,this.right,this.autoSort,this.staticOptionRegex);this.update();}
function OT_transferAllLeft(){moveAllOptions(this.right,this.left,this.autoSort,this.staticOptionRegex);this.update();}
function OT_transferAllRight(){moveAllOptions(this.left,this.right,this.autoSort,this.staticOptionRegex);this.update();}
function OT_saveRemovedLeftOptions(f){this.removedLeftField = f;}
function OT_saveRemovedRightOptions(f){this.removedRightField = f;}
function OT_saveAddedLeftOptions(f){this.addedLeftField = f;}
function OT_saveAddedRightOptions(f){this.addedRightField = f;}
function OT_saveNewLeftOptions(f){this.newLeftField = f;}
function OT_saveNewRightOptions(f){this.newRightField = f;}
function OT_update(){var removedLeft = new Object();var removedRight = new Object();var addedLeft = new Object();var addedRight = new Object();var newLeft = new Object();var newRight = new Object();for(var i=0;i<this.left.options.length;i++){var o=this.left.options[i];newLeft[o.value]=1;if(typeof(this.originalLeftValues[o.value])=="undefined"){addedLeft[o.value]=1;removedRight[o.value]=1;}}for(var i=0;i<this.right.options.length;i++){var o=this.right.options[i];newRight[o.value]=1;if(typeof(this.originalRightValues[o.value])=="undefined"){addedRight[o.value]=1;removedLeft[o.value]=1;}}if(this.removedLeftField!=null){this.removedLeftField.value = OT_join(removedLeft,this.delimiter);}if(this.removedRightField!=null){this.removedRightField.value = OT_join(removedRight,this.delimiter);}if(this.addedLeftField!=null){this.addedLeftField.value = OT_join(addedLeft,this.delimiter);}if(this.addedRightField!=null){this.addedRightField.value = OT_join(addedRight,this.delimiter);}if(this.newLeftField!=null){this.newLeftField.value = OT_join(newLeft,this.delimiter);}if(this.newRightField!=null){this.newRightField.value = OT_join(newRight,this.delimiter);}}
function OT_join(o,delimiter){var val;var str="";for(val in o){if(str.length>0){str=str+delimiter;}str=str+val;}return str;}
function OT_setDelimiter(val){this.delimiter=val;}
function OT_setAutoSort(val){this.autoSort=val;}
function OT_setStaticOptionRegex(val){this.staticOptionRegex=val;}
function OT_init(theform){this.form = theform;if(!theform[this.left]){alert("OptionTransfer init(): Left select list does not exist in form!");return false;}if(!theform[this.right]){alert("OptionTransfer init(): Right select list does not exist in form!");return false;}this.left=theform[this.left];this.right=theform[this.right];for(var i=0;i<this.left.options.length;i++){this.originalLeftValues[this.left.options[i].value]=1;}for(var i=0;i<this.right.options.length;i++){this.originalRightValues[this.right.options[i].value]=1;}if(this.removedLeftField!=null){this.removedLeftField=theform[this.removedLeftField];}if(this.removedRightField!=null){this.removedRightField=theform[this.removedRightField];}if(this.addedLeftField!=null){this.addedLeftField=theform[this.addedLeftField];}if(this.addedRightField!=null){this.addedRightField=theform[this.addedRightField];}if(this.newLeftField!=null){this.newLeftField=theform[this.newLeftField];}if(this.newRightField!=null){this.newRightField=theform[this.newRightField];}this.update();}
function OptionTransfer(l,r){this.form = null;this.left=l;this.right=r;this.autoSort=true;this.delimiter=",";this.staticOptionRegex = "";this.originalLeftValues = new Object();this.originalRightValues = new Object();this.removedLeftField = null;this.removedRightField = null;this.addedLeftField = null;this.addedRightField = null;this.newLeftField = null;this.newRightField = null;this.transferLeft=OT_transferLeft;this.transferRight=OT_transferRight;this.transferAllLeft=OT_transferAllLeft;this.transferAllRight=OT_transferAllRight;this.saveRemovedLeftOptions=OT_saveRemovedLeftOptions;this.saveRemovedRightOptions=OT_saveRemovedRightOptions;this.saveAddedLeftOptions=OT_saveAddedLeftOptions;this.saveAddedRightOptions=OT_saveAddedRightOptions;this.saveNewLeftOptions=OT_saveNewLeftOptions;this.saveNewRightOptions=OT_saveNewRightOptions;this.setDelimiter=OT_setDelimiter;this.setAutoSort=OT_setAutoSort;this.setStaticOptionRegex=OT_setStaticOptionRegex;this.init=OT_init;this.update=OT_update;}

