<cfsetting enablecfoutputonly="true" showdebugoutput="false">
<!--- @Packager.Header
<FileDescription>
	This is the File Browser Connector for ColdFusion.
	
	Notice: 
	FCKeditor needs an UTF-8 encoded XML packet. 
	Only CFMX can encode in UTF-8. If this browser doesn't work in CF 4.0/4.5/5,
	please send me a notice. We then can use an more object oriented approach (CFC)
	to clean up this code :)
	
	Please declare the base path (e.g. /UserFiles/) as an Application or Server variable
	Directory structure (with optional subdirectories built by the user). 
	The "Type" subdirectory is automatically submitted by FCKeditor:
	/UserFiles/Image/
	/UserFiles/Flash/
	/UserFiles/File/
	/UserFiles/Media/

</FileDescription>
<Author name="Hendrik Kramer" email="hk@lwd.de" />
--->
<cfparam name="URL.Command" type="string">
<cfparam name="URL.Type" type="string">
<cfparam name="URL.CurrentFolder" type="string">

<!--- ::
	* Get base url path to the userfiles directory (may be set in Application.cfm or anywhere else)
	:: --->
<cfif isDefined('APPLICATION.userFilesPath')>
	<cflock scope="Application" type="readonly" timeout="3">
		<cfset sUserFilesURL = APPLICATION.userFilesPath>
	</cflock>
<cfelseif isDefined('SERVER.userFilesPath')>
	<cflock scope="SERVER" type="readonly" timeout="3">
		<cfset sUserFilesURL = SERVER.userFilesPath>
	</cflock>
<cfelse>
	<!--- :: then use default :: --->
	<cfset sUserFilesURL = "/UserFiles/">
</cfif>

<cfif not len( sUserFilesURL )>
	<cfthrow type="fckeditor.connector" message="You must supply a full path to the userFiles base URL in APPLICATION or SERVER Scope.">
</cfif>

<cfscript>
	/**
	  * We must extract the physical server directory for the webroot of this project to append the base url path
   	  * e.g. 
      * PATH=c:\inetpub\wwwroot\project1\fckeditor\editor\filemanager\browser\default\connectors\cfm\connector.cfm 
      * URL =/fckeditor/editor/filemanager/browser/default/connectors/cfm/connector.cfm 
      * ROOT=c:\inetpub\wwwroot\project1\
      *
      * This may fail if you use an symbolic link inside your webroot
      */
	sRootDir = replace( getBaseTemplatePath(), "\", "/", "ALL");
	iLen = listLen( cgi.script_name, '/' );
	for( i=iLen; i GTE 1; i=i-1 )
	{
		iPos = listFindNoCase( sRootDir, listGetAt( cgi.script_name, i, '/' ), '/' );
		if( iPos GT 0 )
			sRootDir = listDeleteAt( sRootDir, iPos, '/' );
	}

	// replace backslashes in URL with normal slashes
	sUserFilesURL = replace( sUserFilesURL, "\", "/", "ALL");

	// Check the base folder syntax (must end with a slash).
	if( compare( right( sUserFilesURL, 1), "/" ) )
		sUserFilesURL = sUserFilesURL & "/";

	// Create the physical path to the media root directory
	sUserFilesPath = sRootDir & sUserFilesURL;
	sUserFilesPath = replace( sUserFilesPath, '//', '/', 'ALL' );

	// Check the current folder syntax (must begin and start with a slash).
	if( compare( left( URL.CurrentFolder, 1), "/" ) )
		URL.CurrentFolder = "/" & URL.CurrentFolder;
	if( compare( right( URL.CurrentFolder, 1), "/" ) )
		URL.CurrentFolder = URL.CurrentFolder & "/";
	
	/**
	  * Prepare the XML Header and Footer
	  */
	sXMLHeader = '<?xml version="1.0" encoding="utf-8" ?><Connector command="#URL.Command#" resourceType="#URL.Type#">';
	sXMLHeader = sXMLHeader & '<CurrentFolder path="#URL.CurrentFolder#" url="#sUserFilesURL##URL.Type##URL.CurrentFolder#" />';
	sXMLFooter = '</Connector>';
	sXMLContent = '';
</cfscript>

<!--- :: Make sure that current base path exists as a directory :: --->
<cfif not directoryExists( sUserFilesPath & URL.Type & URL.CurrentFolder )>
	<cfdirectory 
		action="create" 
		directory="#sUserFilesPath##URL.Type##URL.CurrentFolder#"
	>
</cfif>

<!--- :: Switch command arguments :: --->
<cfswitch expression="#URL.Command#">
<cfcase value="FileUpload">

	<cfset sFileName = "">
	<cfset sFileExt = "">

	<cftry>
		<!--- :: first upload the file with an unique filename :: --->
		<cffile action="UPLOAD"
			fileField="NewFile"
			destination="#sUserFilesPath##URL.Type##URL.CurrentFolder#"
			nameConflict="MAKEUNIQUE"
		>

		<cfscript>
		sErrorNumber = 0;
		sFileName = CFFILE.ClientFileName;
		sFileExt = CFFILE.ServerFileExt;

		/**
		  * Validate filename for html download. Only a-z, 0-9, _, - and . are allowed.
		  */
		if( reFind("[^A-Za-z0-9_\-\.]", sFileName) )
		{
			sFilename = reReplace(sFilename, "[^A-Za-z0-9\-\.]", "_", "ALL");
			sFilename = reReplace(sFilename, "_{2,}", "_", "ALL");
			sFilename = reReplace(sFilename, "([^_]+)_+$", "\1", "ALL");
			sFilename = reReplace(sFilename, "$_([^_]+)$", "\1", "ALL");
		}

		// When the original filename already exists, add numbers (0), (1), (2), ... at the end of the filename.
		if( compare( CFFILE.ServerFileName, sFileName ) )
		{
			iCounter = 0;
			sTmpFileName = sFileName;
			while( fileExists('#sUserFilesPath##URL.Type##URL.CurrentFolder##sFilename#.#sFileExt#') )
			{
			  	iCounter=iCounter+1;
				sFileName = sTmpFileName & '(#iCounter#)';
			}
		}
		</cfscript>
		
		<!--- :: Rename the uploaded file, if neccessary --->
		<cfif compare( CFFILE.ServerFileName, sFileName )>
			<cfset sErrorNumber = "201">
			<cffile
				action="RENAME"
				source="#sUserFilesPath##URL.Type##URL.CurrentFolder##CFFILE.ServerFileName#.#CFFILE.ServerFileExt#"
				destination="#sUserFilesPath##URL.Type##URL.CurrentFolder##sFileName#.#sFileExt#"
				mode="644"
				attributes="normal"
			>
		</cfif>

		<cfcatch type="Any">
			<cfset sErrorNumber = "202">
		</cfcatch>
	</cftry>
	
	<cfif sErrorNumber eq 201>
		<!--- :: file was changed (201), submit the new filename :: --->
		<cfoutput>
		<script type="text/javascript">
		window.parent.frames['frmUpload'].OnUploadCompleted(#sErrorNumber#,'#replace( sFileName & "." & sFileExt, "'", "\'", "ALL")#');
		</script>
		</cfoutput>
	<cfelse>
		<!--- :: file was uploaded succesfully(0) or an error occured(202). Submit only the error code. :: --->
		<cfoutput>
		<script type="text/javascript">
		window.parent.frames['frmUpload'].OnUploadCompleted(#sErrorNumber#);
		</script>
		</cfoutput>
	</cfif>

	<cfabort>

</cfcase>
<cfcase value="GetFolders">

	<!--- :: Sort directories first, name ascending :: --->
	<cfdirectory 
		action="LIST" 
		directory="#sUserFilesPath##URL.Type##URL.CurrentFolder#" 
		name="qDir"
		sort="type,name"
	>
	
	<cfscript>
		iLen = qDir.recordCount;	
		i=1;
		sFolders = '';
		
		while( i LTE iLen )
		{
			if( not compareNoCase( qDir.type[i], "FILE" ))
				break;
			if( not listFind(".,..", qDir.name[i]) )
				sFolders = sFolders & '<Folder name="#qDir.name[i]#" />';
			i=i+1;
		}

		sXMLContent = sXMLContent & '<Folders>' & sFolders & '</Folders>';
	</cfscript>

</cfcase>
<cfcase value="GetFoldersAndFiles">

	<!--- :: Sort directories first, name ascending :: --->
	<cfdirectory 
		action="LIST" 
		directory="#sUserFilesPath##URL.Type##URL.CurrentFolder#" 
		name="qDir"
		sort="type,name"
	>
	<cfscript>
		iLen = qDir.recordCount;
		i=1;
		sFolders = '';
		sFiles = '';
		
		while( i LTE iLen )
		{
			if( not compareNoCase( qDir.type[i], "DIR" ) and not listFind(".,..", qDir.name[i]) )
			{
				sFolders = sFolders & '<Folder name="#qDir.name[i]#" />';
			}
			else if( not compareNoCase( qDir.type[i], "FILE" ) )
			{
				iFileSize = int( qDir.size[i] / 1024 );
				sFiles = sFiles & '<File name="#qDir.name[i]#" size="#IIf( iFileSize GT 0, DE( iFileSize ), 1)#" />';
			}
			i=i+1;
		}

		sXMLContent = sXMLContent & '<Folders>' & sFolders & '</Folders>';
		sXMLContent = sXMLContent & '<Files>' & sFiles & '</Files>';
	</cfscript>

</cfcase>
<cfcase value="CreateFolder">

	<cfparam name="URL.NewFolderName" default="">

	<cfif not len( URL.NewFolderName ) or len( URL.NewFolderName ) GT 255>
		<cfset iErrorNumber = 102>	
	<cfelseif directoryExists( sUserFilesPath & URL.Type & URL.CurrentFolder & URL.NewFolderName )>
		<cfset iErrorNumber = 101>
	<cfelseif reFind( "^\.\.", URL.NewFolderName )>
		<cfset iErrorNumber = 103>
	<cfelse>
		<cfset iErrorNumber = 0>

		<cftry>
			<cfdirectory
				action="CREATE"
				directory="#sUserFilesPath##URL.Type##URL.CurrentFolder##URL.NewFolderName#"
			>
			<cfcatch>
				<!--- ::
					* Not resolvable ERROR-Numbers in ColdFusion:
					* 102 : Invalid folder name. 
					* 103 : You have no permissions to create the folder. 
					:: --->
				<cfset iErrorNumber = 110>
			</cfcatch>
		</cftry>
	</cfif>
	
	<cfset sXMLContent = sXMLContent & '<Error number="#iErrorNumber#" />'>

</cfcase>
<cfdefaultcase>
	<cfthrow type="fckeditor.connector" message="Illegal command: #URL.Command#">
</cfdefaultcase>
</cfswitch>

<!--- ::
  	* output XML (no content caching) 
	:: --->
<cfheader name="Pragma" value="no-cache">
<cfheader name="Cache-Control" value="no-cache, no-store, must-revalidate">
<cfcontent reset="true" type="text/xml; charset=UTF-8">
<cfoutput>#sXMLHeader##sXMLContent##sXMLFooter#</cfoutput>

<cfsetting enablecfoutputonly="false">