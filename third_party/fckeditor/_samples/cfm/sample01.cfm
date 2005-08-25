<cfsetting enablecfoutputonly="true" showdebugoutput="false">
<!--- @Packager.Header
<FileDescription>
	Sample page for ColdFusion.
</FileDescription>
<Author name="Hendrik Kramer" email="hk@lwd.de" />
--->

<!--- ::
	  * You must set the url path to the base directory for your media files (images, flash, files)
	  * The best position for this variable is in your Application.cfm file
	  * 
	  * Possible variable scopes are:
	  * <cfset APPLICATION.userFilesPath = "/UserFiles/">
	  * OR:
	  * <cfset SERVER.userFilesPath = "/UserFiles/">
	  *
	  * Note #1: Do _not_ set the physical directory on your server, only a path relative to your current webroot
	  * Note #2: Directories will be automatically created
	  :: --->
<cflock scope="Application" type="exclusive" timeout="5">
	<cfset APPLICATION.userFilesPath = "/UserFiles/">
</cflock>

<cfoutput>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
	<title>FCKeditor - Sample</title>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<meta name="robots" content="noindex, nofollow">
	<link href="../sample.css" rel="stylesheet" type="text/css" />
</head>

<body>

<h1>FCKeditor - ColdFusion - Sample 1</h1>
	
This sample displays a normal HTML form with a FCKeditor with full features enabled; invoked by a ColdFusion Custom Tag / Module.<br>
ColdFusion is a registered trademark and product of <a href="http://www.macromedia.com/software/coldfusion/" target="_blank">Macromedia, Inc</a>.
<hr>
<form method="POST" action="#cgi.script_name#">
</cfoutput>

<cfif listFirst( server.coldFusion.productVersion ) LT 6>
	<cfoutput><br><em style="color: red;">This sample work only with a ColdFusion MX server and higher, because it uses some advantages of this version.</em></cfoutput>
	<cfabort>
</cfif>

<cfmodule 
	template="../../fckeditor.cfm"
	basePath="/fckeditor/"
	instanceName="myEditor"
	value='This is some sample text. You are using <a href="http://fckeditor.net/" target="_blank">FCKeditor</a>.'
	width="100%"
	height="200"
>
<cfoutput>
<br />
<input type="submit" value="Submit">
<br />
</form>
</cfoutput>

<cfif isDefined( 'FORM.fieldnames' )>
	<cfoutput>
	<style>
	<!--
		td, th { font: 11px Verdana, Arial, Helv, Helvetica, sans-serif; }
	-->
	</style>
	<table border="1" cellspacing="0" cellpadding="2" bordercolor="darkblue" bordercolordark="darkblue" bordercolorlight="darkblue">
	<tr>
		<th colspan="2" bgcolor="darkblue"><font color="white"><strong>Dump of FORM Variables</strong></font></th>
	</tr>
	<tr>
		<td bgcolor="lightskyblue">FieldNames</td>
		<td>#FORM.fieldNames#</td>
	</tr>
	<cfloop list="#FORM.fieldnames#" index="key">
	<tr>
		<td valign="top" bgcolor="lightskyblue">#key#</td>
		<td>#HTMLEditFormat(evaluate("FORM.#key#"))#</td>
	</tr>
	</cfloop>
	</table>
	</cfoutput>
</cfif>

</body>
</html>
<cfsetting enablecfoutputonly="false">