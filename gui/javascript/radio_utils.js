// TestLink Open Source Project - http://testlink.sourceforge.net/ 
// $Id: radio_utils.js,v 1.1 2006/05/29 06:39:12 franciscom Exp $ 
//
// 20060502 - franciscom
function check_all_radios(value)
{
	var all_inputs = document.forms[0].getElementsByTagName('input');
	for(var idx = 0; idx < all_inputs.length; idx++)
	{
		var elem_type = all_inputs[idx].type;		
		if(elem_type == "radio" && 
		   all_inputs[idx].value == value &&
		   !all_inputs[idx].disabled)
		{
			all_inputs[idx].checked = true;
		}	
	}
} // end function
