// TestLink Open Source Project - http://testlink.sourceforge.net/
// $Id: validate.js,v 1.9 2009/08/24 16:42:15 franciscom Exp $
//
// Functions for validation of input on client side
//
// rev: 20080210 - franciscom - validatePassword() refactoring
//
//
// rev:
// 20090824 - franciscom - fixed typo error on textCounter()
// 20061228 - franciscom - added function get from Eventum Open Source Project
//

// Function validate length of field
// field = e.g. document.forms[0].login
function valTextLength(field, maxLength,  minLength)
{
	if (!field)
		return false;
	var fieldValue  = field.value;
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
        field.focus();
		field.style.backgroundColor = '#F99';
	}
	return bSuccess;
}


// Validate two values of a new password
function validatePassword(newpassword,newpassword_confirm)
{
  var oid_p=document.getElementById(newpassword);
  var oid_pconfirm=document.getElementById(newpassword_confirm);

	if (oid_p.value != oid_pconfirm.value)
	{
		oid_p.value = "";
		oid_pconfirm.value = "";
		oid_p.focus();
		return false ;
	}

	// OK
	return true ;
}


// ---------------------------------------------------------------------------------------------
// This code is part of eventum Open Source Project
//
function selectField(f, field_name, old_onchange)
{
    for (var i = 0; i < f.elements.length; i++) {
        if (f.elements[i].name == field_name) {
            if (f.elements[i].type != 'hidden') {
                f.elements[i].focus();
            }
            errorDetails(f, field_name, true);
            if (isWhitespace(f.name)) {
                return false;
            }
            f.elements[i].onchange = new Function('e', 'checkErrorCondition(e, \'' + f.name + '\', \'' + field_name + '\', ' + old_onchange + ');');
            if (f.elements[i].select) {
                f.elements[i].select();
            }
        }
    }
}

function errorDetails(f, field_name, show)
{
    var field = getFormElement(f, field_name);
    var icon = getPageElement('error_icon_' + field_name);
    if (icon == null) {
        return false;
    }
    if (show) {
        field.style.backgroundColor = '#FF9999';
        icon.style.visibility = 'visible';
        icon.width = 14;
        icon.height = 14;
    } else {
        field.style.backgroundColor = '#FFFFFF';
        icon.style.visibility = 'hidden';
        icon.width = 1;
        icon.height = 1;
    }
}

function isWhitespace(s)
{
    var whitespace = " \t\n\r";

    if (s.length == 0) {
        // empty field!
        return true;
    } else {
        // check for whitespace now!
        for (var z = 0; z < s.length; z++) {
            // Check that current character isn't whitespace.
            var c = s.charAt(z);
            if (whitespace.indexOf(c) == -1) return false;
        }
        return true;
    }
}

function checkErrorCondition(e, form_name, field_name, old_onchange)
{
    var f = getForm(form_name);
    var field = getFormElement(f, field_name);
    if ((field.type == 'text') || (field.type == 'textarea') || (field.type == 'password')) {
        if (!isWhitespace(field.value)) {
            errorDetails(f, field_name, false);
            if (old_onchange != undefined) {
                field.onchange = old_onchange;
                eval('trash = ' + old_onchange + '(e)');
            }
        }
    } else if (field.type == 'select-one') {
        if (getSelectedOption(f, field_name) != '-1') {
            errorDetails(f, field_name, false);
            if (old_onchange != undefined) {
                field.onchange = old_onchange;
                eval('trash = ' + old_onchange + '(e)');
            }
        }
    } else if (field.type == 'select-multiple') {
        if (hasOneSelected(f, field_name)) {
            errorDetails(f, field_name, false);
            if (old_onchange != undefined) {
                field.onchange = old_onchange;
                eval('trash = ' + old_onchange + '(e)');
            }
        }
    }
}

function hasOneSelected(f, field_name)
{
    for (var i = 0; i < f.elements.length; i++) {
        if (f.elements[i].name == field_name) {
            var multi = f.elements[i];
            for (var y = 0; y < multi.options.length; y++) {
                if (multi.options[y].selected) {
                    return true;
                }
            }
        }
    }
    return false;
}

function getSelectedOption(f, field_name)
{
    for (var i = 0; i < f.elements.length; i++) {
        if (f.elements[i].name == field_name) {
            if (f.elements[i].options.length > 0) {
                if (f.elements[i].selectedIndex == -1) {
                    return -1;
                }
                return f.elements[i].options[f.elements[i].selectedIndex].value;
            } else {
                return -1;
            }
        }
    }
}

function getSelectedOptionObject(f, field_name)
{
    for (var i = 0; i < f.elements.length; i++) {
        if (f.elements[i].name == field_name) {
            return f.elements[i].options[f.elements[i].selectedIndex];
        }
    }
}

function getFormElement(f, field_name, num)
{
    var elements = document.getElementsByName(field_name);
    var y = 0;
    for (var i = 0; i < elements.length; i++) {
        if (f != elements[i].form) {
            continue;
        }
        if (num != null) {
            if (y == num) {
                return elements[i];
            }
            y++;
        } else {
            return elements[i];
        }
    }
    return false;
}

function getPageElement(id)
{
    if (document.getElementById) {
        return document.getElementById(id);
    } else if (document.all) {
        return document.all[id];
    }
}

function getForm(form_name)
{
    for (var i = 0; i < document.forms.length; i++) {
        if (document.forms[i].name == form_name) {
            return document.forms[i];
        }
    }
}

// check textarea lenght (CF allows 255 only)
// Usage: <textarea name=myField
// 		onKeyDown="textCounter(this.form.message,this.form.remLen,125);" 
//		onKeyUp="textCounter(this.form.message,this.form.remLen,125);"></textarea>
function textCounter(field, counterField, maxLimit) 
{
	if (field.value.length > maxLimit) {
		field.value = field.value.substring(0, maxLimit); // if too long...trim it
	} else {
		// otherwise, update 'characters left' counter
		counterField.innerHTML = maxLimit - field.value.length;
	}
}

// ------------------------------------------------------------------------------