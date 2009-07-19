// TestLink Open Source Project - http://testlink.sourceforge.net/ 
// $Id: checkboxes.js,v 1.10 2009/07/19 19:22:29 franciscom Exp $ 
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
	for(var idx = 0; idx < frm.length; idx++)
	{
		var elemType = frm[idx].type;		
		
		if(elemType == "checkbox")
		{
			frm[idx].checked = checkBoxStatus;
		}	
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
	// var ml = document.myform;
	var my_form=document.getElementById(ml);
	
	alert(ml);
	alert(my_form);
	
	var len = my_form.elements.length;
	
	for (var idx = 0; idx < len; idx++)
	{
		var e = my_form.elements[idx];
		if (e.type == "checkbox")
		{
			e.checked = bCheck;
		}	
	}
}

/*
  function: checkbox_count_checked 
            given a container id, will return how many checkboxes are checked.
            
  args : container_id
  
  returns: number

  rev :
        20070102 - franciscom
*/
function checkbox_count_checked(container_id)
{
  var container=document.getElementById(container_id);
	var all_inputs = container.getElementsByTagName('input');
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


/*
  function:  cs_all_checkbox_in_div
             Change Status of all checkboxes with a id prefix
             on a div.

  args :
        div_id: id of the div container of checkboxs
 
        cb_id_prefix: checkbox id prefix
        
        memory_id: id of hidden input used to hold old check value.
        
  
        
  returns:  - 

*/
function cs_all_checkbox_in_div(div_id, cb_id_prefix,memory_id)
{
	var inputs = document.getElementById(div_id).getElementsByTagName('input');
	var memory = document.getElementById(memory_id);
		
	for(var idx = 0; idx < inputs.length; idx++)
	{
		var elemType = inputs[idx].type;		
		
		if(inputs[idx].type == "checkbox" && 
		  (inputs[idx].id.indexOf(cb_id_prefix)==0) )
		{
      inputs[idx].checked = (memory.value == "1") ? false : true;
		}	
	} // for
	
	memory.value = (memory.value == "1") ? "0" : "1";
}