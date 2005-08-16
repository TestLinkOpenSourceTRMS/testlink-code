// TestLink Open Source Project - http://testlink.sourceforge.net/ 
// $Id: checkboxes.js,v 1.2 2005/08/16 17:59:13 franciscom Exp $ 

//This function takes a div tag and whether or not you want the checkboxes checked or not
//The function then goes through all of the elements of the div tag that is passed in and
//if they are checkboxes

// the function is used in planAddTC.php script
function box(myDiv, checkBoxStatus)
{
	var frm = document.getElementById(myDiv).getElementsByTagName('input');
	for(var i = 0; i < frm.length; i++)
	{
		var elemType = frm[i].type;		
		
		if(elemType == "checkbox")
			frm[i].checked = checkBoxStatus;
	}
}

// next two functions allows to check and uncheck all checkboxes in form
// are used in planUpdateTC.php
function checkAll(ml)
{
	checkOrUncheckAll(ml,true)	
}

function uncheckAll(ml)
{
	checkOrUncheckAll(ml,false)	
}

function checkOrUncheckAll(ml,bCheck)
{
	var ml = document.myform;
	var len = ml.elements.length;
	
	for (var i = 0; i < len; i++)
	{
		var e = ml.elements[i];
		if (e.type == "checkbox")
			e.checked = bCheck;
	}
}