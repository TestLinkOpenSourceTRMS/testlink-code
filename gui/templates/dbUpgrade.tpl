{* TestLink Open Source Project - http://testlink.sourceforge.net/ *}
{* $Id: dbUpgrade.tpl,v 1.2 2005/08/16 17:59:13 franciscom Exp $ *}
{* Purpose: smarty template - shows form allowing users to upgrade their testlink db *}
{include file="inc_head.tpl"}
<body>

{if $submit ne "true"}		
<form name="dbInfo" method="post" action="{$self}">
<div style="color: 	orange;
	background: black;
	margin: 12px 12px 12px 12px;
	padding: 12px 12px 12px 12px;
	text-align: left;
	width: 550px;
	font-family : Arial, Helvetica, sans-serif;
	font-size : smaller;"
>
	Testlink upgrade requirements:<br />
	1. A new empty mysql database should be created for existing the data to be moved to.<br />
	2. A mysql user who at a minimum has read rights to the old db and write rights to the new db.<br />	
	3. mysqldump is required and should be installed with a typical server installation of mysql.<br />
	4. A temporary file path is required that the web server process can write to while the upgrade is being performed.</br >
	<p>Be aware that this upgrade may possibly take a fair amount of time so only click submit once!</p>
</div>

<hr width="80%">

<table border="0" cellpadding="3">
	<tr>
  		<td>
  			Database Host:
  		</td>
  		<td>	
  			<input type="text" name="dbHost" value="localhost" size="40" maxlength="40"/>
  		</td>
  	</tr>
	<tr>
  		<td>
  			Database User:
  		</td>
  		<td>	
  			<input type="text" name="dbUser" value="" size="40" maxlength="40"/>
  		</td>
  	</tr>
  	<tr>
  		<td>
  			Password:
  		</td>
  		<td>  		
  			<input type="password" name="dbPass" value="" size="40" maxlength="40"/>
  		</td>
  	</tr>
  	<tr>
  		<td>
  			Old Database name:
  		</td>
  		<td>  		
  			<input type="text" name="oldDBName" value="" size="40" maxlength="40"/>
  		</td>
  	</tr>
  	<tr>
  		<td>
  			New Database Name:
  		</td>
  		<td>
 			<input type="text" name="newDBName" value="" size="40" maxlength="40"/>
 		</td>
 	</tr>
 	<tr>
 		<td>
 			mysqldump path:
 		</td>
 		<td> 		
  			<input type="text" name="myDumpPath" value=
				{if $os eq "WINNT" or $os eq "WIN32"}
  					"c:\mysql\bin"
  				{else}
  					"/usr/bin"
  				{/if}
  				size="40" maxlength="40"/>
  		</td>
  	</tr>
  	<tr>
  		<td>
  			temporary file path:
  		</td>
  		<td>  		
  			<input type="text" name="tmpPath" value=
				{if $os eq "WINNT" or $os eq "WIN32"}
  				  	"c:\WINNT\TEMP"
  				{else}
  					"/tmp"
  				{/if}
	  			size="40" maxlength="40"/>
  		</td>
	</tr> 
	<tr>
		<td colspan="2">
			<input type="submit" name="upgrade" value="upgrade data"/>
		</td>
	</tr>
		   	   
</table>

</form>

{elseif $submit eq "true"}
	<div style="text-align: center; font-weight: bold;">upgraded data successfully!</div>

{/if}

</body>
</html>
