/*  
TestLink Open Source Project - http://testlink.sourceforge.net/
@filesource cfield_validation.js

functions to validate custom field contents

regular expressions was taken from: 

Really easy field validation with Prototype
http://tetlaw.id.au/view/javascript/really-easy-field-validation
Andrew Tetlaw
Version 1.5.4.1 (2007-01-05)
   
IMPORTANT
Needs EXT-JS due to use of trim()

Global Dependencies:  cfChecks,cfMessages 
                      declared and initialized in inc_jsCfieldsValidation.tpl   
    
@internal revisions
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
      	checkStatus.status_ok=!/[^\d]/.test(cfield_value.trim());
		  break; 

		  case 'float':
      	checkStatus.status_ok=(!isNaN(cfield_value) && !/^\s+$/.test(cfield_value.trim()));
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

/**
  function: checkRequiredCustomFields 
            For every custom field, do check (get class name) to understand
            if is a REQUIRED field.
            IMPORTANT NOTICE: At first validation failure, processing is aborted

  args: cfields_inputs: set of html inputs used to manage the custom fields.
  
  returns: object -> obj.status_ok: true if all check passed
                     obj.msg_id: not used, maintained for compatobility with validateCustomFields()
                                 
                     obj.cfield_label: label of offending custom field, used on user's feedback
                     
 */
function checkRequiredCustomFields(cfields_inputs)
{
	var CFIELD_TYPE_IDX=2;
	var cfields_container='';
	var custom_field_types = new Array();
	var checkStatus = {status_ok: true, msg_id: null, cfield_label: null};
	var whitespace = " \t\n\r";
 	var lbl;
 	var pivot;
 	var cachedStatus = new Array();

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

 	if( cfields_inputs.length > 0 )
 	{
 		pivot = cfields_inputs[0].name;
 	}
 	
	for(var idx = 0; idx < cfields_inputs.length; idx++)
	{
		// Important:
		// elemName format for custom fields -> custom_field_<cfield_type>_<cfield_id>[_<testcase_id>]
		var elemName = cfields_inputs[idx].name;		
		var elemID = cfields_inputs[idx].id;		
		
		var nameParts=elemName.split("_");
		var cfield_type=custom_field_types[nameParts[CFIELD_TYPE_IDX]];
		var cfield_value=cfields_inputs[idx].value;

	    if(cfields_inputs[idx].className == 'required')
	    {
			if(cachedStatus[elemName] == undefined)
			{
				cachedStatus[elemName] = {status_ok: false, msg_id: null, cfield_label: null};
				cachedStatus[elemName].cfield_label = document.getElementById('label_'+elemID).firstChild.nodeValue;
			}
		
			switch(cfield_type)
			{
			    case 'checkbox':
					cachedStatus[elemName].status_ok |= cfields_inputs[idx].checked;
			    break; 

			    case 'radio':
					cachedStatus[elemName].status_ok |= cfields_inputs[idx].checked;
			    break; 
	
				default:
	      			checkStatus.status_ok = !(cfield_value.length == 0);
					cachedStatus[elemName].status_ok |= checkStatus.status_ok;
	      			if(checkStatus.status_ok)
	      			{
	      			    // check each character for whitespace now!
	      			    for (var z = 0; z < cfield_value.length; z++) 
	      			    {
	      			        checkStatus.status_ok = false;
	      			        // Check that current character isn't whitespace.
	      			        var c = cfield_value.charAt(z);
	      			        if (whitespace.indexOf(c) == -1) 
	      			        {
	      			            // if I found at leat a char not present into whitespace set this means 
	      			            // it will not be a String full of whitespaces 
	      			            checkStatus.status_ok = true;
	      			            break;  
	      			        }
	      			    }
	      			}
			    break; 
	        }  
	
	    }
	    else
	    {
			cachedStatus[elemName] = {status_ok: true, msg_id: null, cfield_label: null};
	    }

		if(pivot != elemName)
		{
			  if( !cachedStatus[pivot].status_ok )
		      {
		         checkStatus.status_ok = cachedStatus[pivot].status_ok
		         checkStatus.cfield_label = cachedStatus[pivot].cfield_label;
	
				 // USELESS, created just to maintain compatibility with validateCustomFields()
		         checkStatus.msg_id='required_cf';  
		         
		         break;  // exit from for loop
		      }
		      pivot = elemName;
		      
		}
	} /* end for */
	
	return checkStatus;
}


/**
  function: checkCustomFields

  Global Dependencies:  cfChecks,cfMessages 
                        declared and initialized in inc_jsCfieldsValidation.tpl   


	all html elements INSIDE container OID provided (normally will be a DIV ID) with one of
	following characteristics
	
	is an input OR is a textarea OR is a select
	
	will be considered Custom Fields.
	
	Then for each custom field, do check (get class name) to understand if is a REQUIRED field.
	IMPORTANT NOTICE: At first check failure, processing is aborted
	
	If REQUIRED CHECK is passed FOR ALL custom fields, then a new loop is done to do this time
	validation on Custom Field value.
	Again -> IMPORTANT NOTICE: At first check failure, processing is aborted
 
  returns: object -> obj.status_ok: true if all check passed
                     obj.msg_id: not used, maintained for compatobility with validateCustomFields()
                                 
                     obj.cfield_label: label of offending custom field, used on user's feedback
                     
*/
function checkCustomFields(cfContainerOID,alertBoxTitle,reqCFWarningMsg)
{
 	var tags4required = new Array("input","textarea","select");
 	var tags4validate = new Array("input","textarea");
	var matrioska = document.getElementById(cfContainerOID);
	var cfieldSet;
 
	var tdx;
	var checkOp;

	// Required Checks
	if( matrioska == null)
	{
		return true;  // >>---> brute force exit
	}

	
	for(tdx=0; tdx < tags4required.length; tdx++) 
	{ 
		cfieldSet = matrioska.getElementsByTagName(tags4required[tdx]);
		checkOp = checkRequiredCustomFields(cfieldSet);
		if(!checkOp.status_ok)
	  {
	  	alert_message(alertBoxTitle,reqCFWarningMsg.replace(/%s/, checkOp.cfield_label));
	    return false;
		}
	}

	// Checks on validity (example email value, integer,etc) custom field values, 
	for(tdx=0; tdx < tags4validate.length; tdx++) 
	{ 
		cfieldSet = matrioska.getElementsByTagName(tags4validate[tdx]);
   	checkOp = validateCustomFields(cfieldSet);

		if(!checkOp.status_ok)
	  {
	  	var msg = cfMessages[checkOp.msg_id];
	    alert_message(alertBoxTitle,msg.replace(/%s/, checkOp.cfield_label));
	    return false;
		}
	}
	
	return true;
}