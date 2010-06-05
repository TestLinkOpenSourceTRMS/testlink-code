// TestLink Open Source Project - http://testlink.sourceforge.net/ 
// This script is distributed under the GNU General Public License 2 or later. 
//
// $Id: test_automation.js,v 1.3 2010/06/05 10:29:16 franciscom Exp $ 
//
// This library is automatically loaded with inc_header.tpl
//
// Code contributed by:
//

/*
  function: openImportResult

  args :

  returns: A pop-up window which facilitates XML import

*/
function openImportResult(windows_title) {
	wref = window.open(fRoot+"lib/results/resultsImport.php",
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