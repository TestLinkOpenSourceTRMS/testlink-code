// TestLink Open Source Project - http://testlink.sourceforge.net/ 
// $Id: validate.js,v 1.2 2005/08/16 17:59:13 franciscom Exp $ 
//
// Functions for validation of input on client side
//


// Function validate length of field
// fieldName = e.g. document.forms[0].login
function valTextLength(fieldName, maxLength,  minLength) 
{ 
    var fieldValue  = fieldName.value; 
    var fieldLength = fieldValue.length; 
 
    var err03 = warning_enter_at_least1 + " " + minLength + " "+warning_enter_at_least2; 
    var err04 = warning_enter_less1 + " " + maxLength + " " + warning_enter_less2; 
 
 	var bSuccess = false;
    if ( fieldLength < minLength)
        alert( err03 ); 
	else if (( fieldLength > maxLength ) && ( maxLength > 0 ))
        alert( err04 ); 
	else
		bSuccess = true; 
		
    if (!bSuccess)
	{
        fieldName.focus(); 
		fieldName.style.backgroundColor = '#F99'; 
	}
	return bSuccess;	
}


// Validate two values of a new password
function validatePassword(form)
{
	if (form.new1.value == "")
	{
		alert(warning_empty_pwd);
		form.new1.focus();
		return false ;
	}
	
	if (form.new1.value != form.new2.value)
	{
		alert(warning_different_pwd);
		form.new1.value = "";
		form.new2.value = "";
		form.new1.focus();
		return false ;
	}
	
	// OK
	return true ;
}

