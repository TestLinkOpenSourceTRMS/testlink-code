// TestLink Open Source Project - http://testlink.sourceforge.net/ 
// $Id: checkboxes.js,v 1.3 2007/01/02 22:02:32 franciscom Exp $ 
//
//
// rev :
//      20070102 - francisco.mancardi@gruppotesi.com - new function checkbox_count_checked()
//

/*
  function: 
           Takes a div tag and whether or not you want the checkboxes checked or not
           Then goes through all of the elements of the div tag that is passed in 
           and if they are checkboxes.
           used in planAddTC.php script

  args :
  
  returns: 

*/
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
/*
  function: 

  args :
  
  returns: 

*/
function checkAll(ml)
{
	checkOrUncheckAll(ml,true)	
}

/*
  function: 

  args :
  
  returns: 

*/
function uncheckAll(ml)
{
	checkOrUncheckAll(ml,false)	
}

/*
  function: 

  args :
  
  returns: 

*/
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

/*
  function: checkbox_count_checked 

  args : form_id
  
  returns: number

  rev :
        20070102 - franciscom
*/
function checkbox_count_checked(form_id)
{
  var f=document.getElementById(form_id);
	var all_inputs = f.getElementsByTagName('input');
	var input_element;
	var count_checked=0;
	for(var idx = 0; idx < all_inputs.length; idx++)
	{
	  input_element=all_inputs[idx];		
		if(input_element.type == "checkbox" &&  
		   input_element.checked  &&
		   !input_element.disabled)
		{
			count_checked++;
		}	
	}
  return(count_checked);
}

