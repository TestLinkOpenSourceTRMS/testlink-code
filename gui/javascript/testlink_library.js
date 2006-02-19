// TestLink Open Source Project - http://testlink.sourceforge.net/ 
// This script is distributed under the GNU General Public License 2 or later. 
//
// $Id: testlink_library.js,v 1.5 2006/02/19 13:03:32 schlundus Exp $ 
//
// Javascript functions commonly used through the GUI
// This library is automatically loaded with inc_header.tpl
//
// DO NOT ADD FUNCTIONS FOR ONE USING
//


// help popup
function open_popup(page) 
{
	window.open(page, "_blank", "left=350,top=50,screenX=350,screenY=50,fullscreen=no,resizable=yes,toolbar=no,status=no,menubar=no,scrollbars=yes,directories=no,location=no,width=400,height=650")
	return true;
}

// middle window (information, TC)
function open_top(page) 
{
	window.open(page, "_blank", "left=350,top=50,screenX=350,screenY=50,fullscreen=no,resizable=yes,toolbar=no,status=no,menubar=no,scrollbars=yes,directories=no,location=no,width=600,height=400")
	return true;
}


// test specification related functions
function ST(id)
{
	parent.workframe.location = fRoot+'/'+menuUrl+"&level=testcase&id="+id;
}

function SCO(id)
{
	parent.workframe.location = fRoot+'/'+menuUrl+"&level=component&id="+id;
}

function SC(id)
{
	parent.workframe.location = fRoot+'/'+menuUrl+"&level=category&id="+id;
}

function SP()
{
	parent.workframe.location = fRoot+'/'+SP_html_help_file;
}

function EP(id)
{
	parent.workframe.location = fRoot+menuUrl+"?edit=product&data="+id+args;
}
function ECO(id)
{
	parent.workframe.location = fRoot+menuUrl+"?edit=component&data="+id+args;
}

function EC(id)
{
	parent.workframe.location = fRoot+menuUrl+"?edit=category&data="+id+args;
}

function ET(id)
{
	parent.workframe.location = fRoot+menuUrl+"?edit=testcase&data="+id+args;
}

function PTP(id)
{
	parent.workframe.location = fRoot+'/'+SP_html_help_file;
}

function PCO(id)
{
	parent.workframe.location = fRoot+menuUrl+"?level=component&data="+id+args;
}

function PC(id)
{
	parent.workframe.location = fRoot+menuUrl+"?level=category&data="+id+args;
}


function PT(id)
{
	parent.workframe.location = fRoot+menuUrl+"?level=tc&data="+id+args;
}


//==========================================
// Set DIV ID to hide
//==========================================
function my_hide_div(itm)
{
	if (!itm)
		return;

	itm.style.display = "none";
}

//==========================================
// Set DIV ID to show
//==========================================
function my_show_div(itm)
{
	if (!itm)
		return;

	itm.style.display = "";
}

// MHT: TODO move it to validate.js
// 20051007 - am - removed build name
function deleteBuild_onClick(buildID)
{
	if (confirm(warning_delete_build))
	{
		var f = document.getElementById('deleteBuildForm');
		if (f)
		{
			var field = document.getElementById('buildID');
			if (field)
				field.value = buildID;
			f.submit();
		}
	}
	
}

function modifyRoles_warning()
{
	if (confirm(warning_modify_role))
	{
		return true;
	}
	return false;
}

function changeTestPlan(feature)
{
	var tmp = document.getElementById('testPlanSel');
	if (!tmp)
		return;
	var tpID = tmp.value;	
	if(tpID)
		location = fRoot+"lib/usermanagement/usersassign.php?feature="+feature+"&featureID="+tpID;
}