// TestLink Open Source Project - http://testlink.sourceforge.net/ 
// $Id: checkboxes.js,v 1.5 2007/01/26 08:10:33 franciscom Exp $ 
//
//
// rev :
//      20070125 - francisco- - new function set_checkbox()
//      20070120 - franciscom - new function set_combo_if_checkbox()
//      20070102 - franciscom - new function checkbox_count_checked()
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



/*
  function: set_combo_if_checkbox

  args : oid
  
  returns: 

  rev :
        20070118 - franciscom
*/
function set_combo_if_checkbox(oid,combo_id_prefix,value_to_assign)
{
  var f=document.getElementById(oid);
	var all_inputs = f.getElementsByTagName('input');
	var input_element;
	var check_id='';
	var apieces='';
	var combo_id_suffix='';
	var cb_id= new Array();
	var jdx=0;
	var idx=0;
		
	// Build an array with the html select ids
	//	
	for(idx = 0; idx < all_inputs.length; idx++)
	{
	  input_element=all_inputs[idx];		
		if(input_element.type == "checkbox" &&  
		   input_element.checked  &&
		   !input_element.disabled)
		{
      check_id=input_element.id;
      
      // Consider the id a list with '_' as element separator
      //    
      apieces=check_id.split("_");
      combo_id_suffix=apieces[apieces.length-1];
      cb_id[jdx]=combo_id_prefix + combo_id_suffix;
      jdx++;
		}	
	}
	
	// now set the combos
	for(idx = 0; idx < cb_id.length; idx++)
	{
	   // debug - alert(cb_id[idx] + " will be" + value_to_assign);
	   input_element=document.getElementById(cb_id[idx]);
	   input_element.value=value_to_assign;
	}
}

/*
  function: set_checkbox 

  args : oid
         value_to_assign
  
  returns: 

  rev :
        20070118 - franciscom
*/
function set_checkbox(oid,value_to_assign)
{
  var cb=document.getElementById(oid);
	cb.checked=value_to_assign;
}


/*
  function: checkbox_get_checked 

  args : oid
  
  returns: 

  rev :
        20070118 - franciscom
*/
function checkbox_get_checked(oid)
{
  var f=document.getElementById(oid);
	var all_inputs = f.getElementsByTagName('input');
	var input_element;
	for(var idx = 0; idx < all_inputs.length; idx++)
	{
	  input_element=all_inputs[idx];		
		if(input_element.type == "checkbox" &&  
		   input_element.checked  &&
		   !input_element.disabled)
		{

			alert("checkbox found. " + input_element.id);
		}	
	}
}


