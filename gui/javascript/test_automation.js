// TestLink Open Source Project - http://testlink.sourceforge.net/ 
// This script is distributed under the GNU General Public License 2 or later. 
//
// $Id: test_automation.js,v 1.8 2010/09/26 07:56:43 franciscom Exp $ 
//
// Code contributed by:
//
// 20100821 - franciscom - openImportResult() interface changes to solve BUGID 3470 reopened 

/*
  function: openImportResult

  args: windows_title
        test project id
        test plan id
        build id
        platform id

  returns: A pop-up window which facilitates XML import

*/
function openImportResult(windows_title,tproject_id,tplan_id,build_id,platform_id) 
{
  var wargs = "tprojectID=" + tproject_id + "&tplanID=" + tplan_id + "&buildID=" + build_id + "&platformID=" + platform_id;
  var wref = window.open(fRoot+"lib/results/resultsImport.php?"+wargs,
	                   windows_title,"menubar=no,width=650,height=500,toolbar=no,scrollbars=yes");
  wref.focus();
}

/**
*  Start execution of a testcase through AJAX call to tcexecute.php page
*  @param node_id, node_type
*  @return html text response received from the php page
*/
function startExecution(node_id,node_type){
	xmlHttp = GetXmlHttpObject();
	if (xmlHttp==null){
		alert("Browser does not support XMLHTTP Request");
		return;
	}
	var url="lib/testcases/tcexecute.php?level="+node_type+"&"+node_type+"_id="+node_id;
	url=url+"&sid="+Math.random()
	xmlHttp.onreadystatechange = stateChanged
	xmlHttp.open("GET",url,true)
	xmlHttp.send(null)
}

/**
*  Create an XMLHttpObject
*  @param none
*  @return XmlHttpObject
*/
function GetXmlHttpObject(){
	var xmlHttp=null;
	try{
		// Firefox, Opera 8.0+, Safari
		xmlHttp=new XMLHttpRequest();
	}
	catch (e){
		//Internet Explorer
		try{
			xmlHttp=new ActiveXObject("Msxml2.XMLHTTP");
		}
		catch (e){
			xmlHttp=new ActiveXObject("Microsoft.XMLHTTP");
		}
	}
	return xmlHttp;
}

/**
* function: stateChanged
* @param  none
* @return Nothing. but modifies div to display response sent by php page 
* (tcexecute.php in this case)
* 
*/
function stateChanged(){

  // if the readyState code is 4 (Completed)
  // and http status is 200 (OK) we go ahead and get the responseText
  // other readyState codes:
  // 0=Uninitialised 1=Loading 2=Loaded 3=Interactive
  //
	if (xmlHttp.readyState==1 || xmlHttp.readyState=="loading"){
		document.getElementById("inProgress").innerHTML = "<BR><blink>Executing. Please Wait.</blink></BR>"
	}
	else if (xmlHttp.readyState==4 || xmlHttp.readyState=="complete" || xmlHttp.readyState=="4"){
		document.getElementById("inProgress").innerHTML = ""
		document.getElementById("executionResults").innerHTML = xmlHttp.responseText
	}
}