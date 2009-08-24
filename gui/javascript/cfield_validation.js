/*  
TestLink Open Source Project - http://testlink.sourceforge.net/
$Id: cfield_validation.js,v 1.4 2009/08/24 07:37:41 franciscom Exp $

functions to validate custom field contents

regular expressions was taken from: 
    Really easy field validation with Prototype
    http://tetlaw.id.au/view/javascript/really-easy-field-validation
    Andrew Tetlaw
    Version 1.5.4.1 (2007-01-05)
   
IMPORTANT
Global Dependencies:  cfChecks,cfMessages 
                      declared and initialized in inc_jsCfieldsValidation.tpl   
    
rev:
    20090823 - franciscom - changed logic to for BUGID 2414
    20090421 - franciscom - BUGID 2414 - check for text area character qty.
    20090101 - franciscom - changes email_check regexp with one taken from EXT-JS Vtypes.js
*/

/*
  function: validateCustomFields 
            For every custom field, do checks using custom field type.
            At first validation failure, processing is aborted

  args: cfields_inputs: set of html inputs used to manage the custom fields.
  
  returns: object -> obj.status_ok: true if all check passed
                     obj.msg_id: point to warning message to display
                     obj.cfield_label: label of offending custom field, used on user's feedback
                     

*/
function validateCustomFields(cfields_inputs)
{
  
  var CFIELD_TYPE_IDX=2;
  var cfields_container='';
  var custom_field_types = new Array();
  var checkStatus = {status_ok: true, msg_id: null, cfield_label: null};

  // Developer notes:
  // If new custom field types are added in PHP code, you need to add it also here
  // Not all types declared here will be validated.
  custom_field_types[0]='string';
  custom_field_types[1]='numeric';
  custom_field_types[2]='float';
  custom_field_types[4]='email';
  custom_field_types[5]='checkbox';
  custom_field_types[6]='list';
  custom_field_types[7]='multiselection list';
  custom_field_types[8]='date';
  custom_field_types[9]='radio';
  custom_field_types[10]='datetime';
	custom_field_types[20]='text area';
	custom_field_types[500]='script';
	custom_field_types[501]='server';

	for(var idx = 0; idx < cfields_inputs.length; idx++)
	{
	  // Important:
	  // elemName format for custom fields -> custom_field_<cfield_type>_<cfield_id>[_<testcase_id>]
		var elemName = cfields_inputs[idx].name;		
		var elemID = cfields_inputs[idx].id;		
		
    var nameParts=elemName.split("_");
		var cfield_type=custom_field_types[nameParts[CFIELD_TYPE_IDX]];
		var cfield_value=cfields_inputs[idx].value;
		
		switch(cfield_type)
		{
		    case 'string':
		        checkStatus.status_ok=true;
		    break; 
		
		    case 'numeric':
            checkStatus.status_ok=!/[^\d]/.test(cfield_value);
		    break; 

		    case 'float':
            checkStatus.status_ok=(!isNaN(cfield_value) && !/^\s+$/.test(cfield_value));
		    break; 
		    
		    case 'email':
		        // mail empty is ok
            var doNextCheck=!((cfield_value == null) || (cfield_value.length == 0));
            if(doNextCheck)
            {		    
                checkStatus.status_ok=cfChecks.email.test(cfield_value);
            }    
		    break; 
		    
		    case 'text area':
		        // check qty of characters
		        checkStatus.status_ok=true;
            if( cfChecks.textarea_length > 0 )
            {
              checkStatus.status_ok=(cfield_value.length <= cfChecks.textarea_length );
            }
		    break; 
		    
		    
		    
		} /* end switch */
		
		if( !checkStatus.status_ok )
    {
       // get label
       var cfield_label=document.getElementById('label_'+elemID).firstChild.nodeValue;
       checkStatus.msg_id='warning_' + cfield_type.replace(/ /,'_')+'_cf';
       checkStatus.cfield_label=cfield_label;
       break;  // exit from for loop
    }
	} /* end for */
	
	return checkStatus;
}