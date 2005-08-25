<html>
<head>
	<title>Test PHP Connector</title>
</head>
<body>
<?php
	error_reporting(E_ALL);
	
	include "config.php";
	
	$resources=$fckphp_config['ResourceTypes'];
	
	$dr=$fckphp_config['basedir'];
	$actual_userfolder=str_replace("//","/",$dr."/".$fckphp_config['UserFilesPath']."/");
	
	
	//Display base directory set in the$fckphp_config
	echo "Base Dir is set to: $dr<br />\n";
	
	//Display the path to this script
	echo "PHP_SELF: ".$_SERVER['PHP_SELF']."<br />\n";
	
	
	//Seperator
	echo "\n<br /><hr /><br />\n";
	
	
	//Check if PHP has MIME-Magic Support
	echo "Checking for mime magic support: ".((function_exists("mime_content_type"))?"Yes":"No - you won't get icons by mime type")."<br />";
	
	//Check if PHP has GD Support
	echo "Checking for GD support: ".((function_exists("imagecreate"))?"Yes":"No - you won't get thumbnails for images")."<br />";
	echo "&nbsp;&nbsp;&nbsp;&nbsp;JPEG Support: ".((function_exists("imagecreatefromjpeg"))?"Yes":"No - you won't get thumbnails for jpeg images")."<br />";
	echo "&nbsp;&nbsp;&nbsp;&nbsp;PNG Support: ".((function_exists("imagecreatefrompng"))?"Yes":"No - you won't get thumbnails for png images")."<br />";
	echo "&nbsp;&nbsp;&nbsp;&nbsp;GIF Support: ".((function_exists("imagecreatefromgif"))?"Yes":"No - you won't get thumbnails for gif images")."<br />";
	echo "&nbsp;&nbsp;&nbsp;&nbsp;Image Write Support: ".((function_exists("imagegif") || function_exists("imagepng") || function_exists("imagejpeg"))?"Yes":"No - you won't get thumbnails for any images, as i cant write GIFs, PNGs or JPEGs")."<br />";
	
	
	//Seperator
	echo "\n<br /><hr /><br />\n";
	
	
	//Check if the user files folder set in the$fckphp_config exists
	echo "Checking if user files folder exists ($actual_userfolder): ";
	if (is_dir($actual_userfolder)) {
		echo "[ Passed ]<br />\n";
	} else {
		echo "[ Failed ] <br />Task: Create the user files folder in the webroot and point the$fckphp_config.php file to it.<br />\n";
		echo "Skipping all other tests, fix this one first.";
		exit(0);
	}
	
	
	//Seperator
	echo "\n<br /><hr /><br />\n";
	
	
	//Check if the File,Image,Flash,Media folders exist in the user files folder and are writeable
	echo "Checking for resource type folders under user file folder: <br />\n";
	foreach ($resources as $value) {
	
		//Does the folder exist
		$passed=false;
		echo "&nbsp;&nbsp;&nbsp;&nbsp;$value exists (".($actual_userfolder.$value).": ";	
		if (is_dir(($actual_userfolder.$value))) {
			echo "[ Passed ]<br />\n";
			$passed=true;
		} else {
			echo "[ Failed ]<br /> Task: chmod this folder to make it writeable to the php processes user.<br />\n";
		}
		
		
		//Is the folder writeable by PHP
		echo "&nbsp;&nbsp;&nbsp;&nbsp;$value writeable: ";	
		if ($passed) {
			if (is_writeable(($actual_userfolder.$value))) {
				echo "[ Passed ]<br />\n";
			} else {
				echo "[ Failed ]<br /> Task: chmod this folder to make it writeable to the php processes user.<br />\n";
			}
		} else {
			echo "[ Skipped ]<br />\n";
		}
		echo "<br />\n";
	}

	
	//Check if PHP is$fckphp_configured to use open_basedir
	echo "Checking if open_basedir restriction in place: (".((($bd=ini_get("open_basedir"))==null)?"Not Set":$bd." - This may cause you some troubles.").")<br />";
	
	//Try to open the user files folder
	echo "Trying an opendir on the user files folder: ".((opendir($actual_userfolder))?"[ Passed ]":"[ Failed ]")."<br />";
	
	
	//Seperator
	echo "\n<br /><hr /><br />\n";
	
	
	//Compose the url to the connector
		$uri="http".(((isset($_SERVER['HTTPS']))&&(strtolower($_SERVER['HTTPS'])!='off'))?"s":"")."://".$_SERVER['SERVER_NAME'].dirname($_SERVER['SCRIPT_NAME'])."/";
		
		
//--------------------------------------------------------------------------------------------------------------------------------------------------
	//Test the GetFolders Command
		echo "Requesting connector.php?Command=GetFolders&Type=Image&CurrentFolder=/ :<br />\n
			<div style=\"border-style:solid;border-width:1px;border-color:#000000\">\n";
			
		$test = implode("",file($uri."connector.php?Command=GetFolders&Type=Image&CurrentFolder=/"));
		$test=htmlentities($test);
		echo str_replace("\n","<br />",$test);
		echo "</div> Please do a sanity check on this, it should be something like: \n
			<div style=\"border-style:solid;border-width:1px;border-color:#000000\">\n";
		
		$expect="
<?xml version=\"1.0\" encoding=\"utf-8\" ?>
<Connector command=\"GetFolders\" resourceType=\"Image\">
    <CurrentFolder path=\"/\" url=\"/data/resources/Image/\" />
    <Folders>
    </Folders>
</Connector>";
	
		
		$expect=htmlentities($expect);
		$expect=str_replace(array("\n","\r"),"<br />",$expect);
		echo "<b>EXAMPLE RESPONSE</b>".$expect;
		?>
		</div>
		<br /><br />
		<?php
//--------------------------------------------------------------------------------------------------------------------------------------------------
		
	//Seperator
		echo "\n<br /><hr /><br />\n";
		
		
//--------------------------------------------------------------------------------------------------------------------------------------------------
	//Test GetFoldersAndFiles Command
		echo "Requesting connector.php?Command=GetFoldersAndFiles&Type=Image&CurrentFolder=/ :<br />\n
			<div style=\"border-style:solid;border-width:1px;border-color:#000000\">\n";
			
		$test = implode("",file($uri."connector.php?Command=GetFoldersAndFiles&Type=Image&CurrentFolder=/"));
		$test=htmlentities($test);
		echo str_replace("\n","<br />",$test);
		echo "</div> Please do a sanity check on this, it should be something like: \n
			<div style=\"border-style:solid;border-width:1px;border-color:#000000\">\n";
		
		$expect="
<?xml version=\"1.0\" encoding=\"utf-8\" ?>
<Connector command=\"GetFoldersAndFiles\" resourceType=\"Image\">
<CurrentFolder path=\"/\" url=\"/data/resources/Image/\" />
<Folders>
</Folders>
<Files>
</Files>
</Connector>";
	
		
		$expect=htmlentities($expect);
		$expect=str_replace(array("\n","\r"),"<br />",$expect);
		echo "<b>EXAMPLE RESPONSE</b>".$expect;
		?>
		</div>
		<br /><br />
		<?php
//--------------------------------------------------------------------------------------------------------------------------------------------------		
		
		//Seperator
		echo "\n<br /><hr /><br />\n";
		
		
//--------------------------------------------------------------------------------------------------------------------------------------------------	
	//Test the CreateFolder command
		echo "Requesting connector.php?Command=CreateFolder&Type=Image&CurrentFolder=/&NewFolderName=TestFolder99 :<br />\n
			<div style=\"border-style:solid;border-width:1px;border-color:#000000\">\n";
			
		$test = implode("",file($uri."connector.php?Command=CreateFolder&Type=Image&CurrentFolder=/&NewFolderName=TestFolder99"));
		$test=htmlentities($test);
		echo str_replace("\n","<br />",$test);
		echo "</div> Please do a sanity check on this, it should be something like: \n
			<div style=\"border-style:solid;border-width:1px;border-color:#000000\">\n";
		
		$expect="
<?xml version=\"1.0\" encoding=\"utf-8\" ?>
<Connector command=\CreateFolder\" resourceType=\"Image\">
<CurrentFolder path=\"/\" url=\"/data/resources/Image/\" />
	<Error number=\"0\" />
</Connector>";
	
		
		$expect=htmlentities($expect);
		$expect=str_replace(array("\n","\r"),"<br />",$expect);
		echo "<b>EXAMPLE RESPONSE</b>".$expect;
		?>
		</div>
		<br /><br />
		<?php	
//--------------------------------------------------------------------------------------------------------------------------------------------------

		
	//Seperator
		echo "\n<br /><hr /><br />\n";
	
		
//--------------------------------------------------------------------------------------------------------------------------------------------------
	//Test the RenameFolder Command
		echo "Requesting connector.php?Command=RenameFolder&Type=Image&CurrentFolder=/&FolderName=TestFolder99&NewName=TestFolder90 :<br />\n
			<div style=\"border-style:solid;border-width:1px;border-color:#000000\">\n";
			
		$test = implode("",file($uri."connector.php?Command=RenameFolder&Type=Image&CurrentFolder=/&FolderName=TestFolder99&NewName=TestFolder90"));
		$test=htmlentities($test);
		echo str_replace("\n","<br />",$test);
		echo "</div> Please do a sanity check on this, it should be something like: \n
			<div style=\"border-style:solid;border-width:1px;border-color:#000000\">\n";
		
		$expect="
<?xml version=\"1.0\" encoding=\"utf-8\" ?>
<Connector command=\"RenameFolder\" resourceType=\"Image\">
    <CurrentFolder path=\"/\" url=\"/data/resources/Image/\" />
    <Error number=\"0\" />
</Connector>";
	
		
		$expect=htmlentities($expect);
		$expect=str_replace(array("\n","\r"),"<br />",$expect);
		echo "<b>EXAMPLE RESPONSE</b>".$expect;
		?>
		</div>
		<br /><br />
		<?php
//--------------------------------------------------------------------------------------------------------------------------------------------------

		
	//Seperator
		echo "\n<br /><hr /><br />\n";
	
		
//--------------------------------------------------------------------------------------------------------------------------------------------------
	//Test the DeleteFolder Command
		echo "Requesting connector.php?Command=DeleteFolder&Type=Image&CurrentFolder=/&FolderName=TestFolder90 :<br />\n
			<div style=\"border-style:solid;border-width:1px;border-color:#000000\">\n";
			
		$test = implode("",file($uri."connector.php?Command=DeleteFolder&Type=Image&CurrentFolder=/&FolderName=TestFolder90"));
		$test=htmlentities($test);
		echo str_replace("\n","<br />",$test);
		echo "</div> Please do a sanity check on this, it should be something like: \n
			<div style=\"border-style:solid;border-width:1px;border-color:#000000\">\n";
		
		$expect="
<?xml version=\"1.0\" encoding=\"utf-8\" ?>
<Connector command=\"DeleteFolder\" resourceType=\"Image\">
    <CurrentFolder path=\"/\" url=\"/data/resources/Image/\" />
    <Error number=\"0\" />
</Connector>";
	
		
		$expect=htmlentities($expect);
		$expect=str_replace(array("\n","\r"),"<br />",$expect);
		echo "<b>EXAMPLE RESPONSE</b>".$expect;
		?>
		</div>
		<br /><br />
		<?php
//--------------------------------------------------------------------------------------------------------------------------------------------------
		
		
	//Seperator
		echo "\n<br /><hr /><br />\n";
	
		
//--------------------------------------------------------------------------------------------------------------------------------------------------
	//Test the Progress.cgi Command
		echo "Requesting ".$fckphp_config['uploadProgressHandler']."?iTotal=0&iRead=0&iStatus=1&sessionid=92823&dtnow=".time()."&dtstart=".time()." :<br />\n
			<div style=\"border-style:solid;border-width:1px;border-color:#000000\">\n";
			
		$test = implode("",file($fckphp_config['uploadProgressHandler']."?iTotal=0&iRead=0&iStatus=1&sessionid=92823&dtnow=".time()."&dtstart=".time()));
		$test=htmlentities($test);
		echo str_replace("\n","<br />",$test);
		echo "</div> Please do a sanity check on this, it should be something like: \n
			<div style=\"border-style:solid;border-width:1px;border-color:#000000\">\n";
		
		$expect="
<UploadProgress sessionID=\"92823\">
<RefreshURL><![CDATA[".$fckphp_config['uploadProgressHandler']."?iTotal=0&iRead=0&iStatus=1&sessionid=92823&dtnow=1098477167&dtstart=1098477166]]></RefreshURL>
<TotalBytes>0</TotalBytes>
<ReadBytes>0</ReadBytes>
<Status>1</Status>
<Speed>0</Speed>
<TimeRemaining>00:00:00</TimeRemaining>
<TimeElapsed>00:00:01</TimeElapsed>
</UploadProgress>";
	
		
		$expect=htmlentities($expect);
		$expect=str_replace(array("\n","\r"),"<br />",$expect);
		echo "<b>EXAMPLE RESPONSE</b>".$expect;
		?>
		</div>
		<br /><br />
		<?php
//--------------------------------------------------------------------------------------------------------------------------------------------------

		
	//Seperator
		echo "\n<br /><hr /><br />\n";
	
		
//--------------------------------------------------------------------------------------------------------------------------------------------------
	//Test the GetUploadProgress Command
		echo "Requesting connector.php?Command=GetUploadProgress&Type=File&CurrentFolder=/Docs/&uploadID=19382&refreshURL=".$fckphp_config['uploadProgressHandler']."?uploadID=19382&amp;read=30&amp;total=100 :<br />\n
			<div style=\"border-style:solid;border-width:1px;border-color:#000000\">\n";
			
		$test = implode("",file($uri."connector.php?Command=GetUploadProgress&Type=File&CurrentFolder=/Docs/&uploadID=19382&refreshURL=".$fckphp_config['uploadProgressHandler']."?uploadID=19382&amp;read=30&amp;total=100"));
		$test=str_replace("&amp;","&",$test);
		$test=htmlentities($test);
		echo str_replace("\n","<br />",$test);
		echo "</div> Please do a sanity check on this, it should be something like: \n
			<div style=\"border-style:solid;border-width:1px;border-color:#000000\">\n";
		
		$expect="
<?xml version=\"1.0\" encoding=\"utf-8\" ?>
<Connector command=\"GetUploadProgress\" resourceType=\"File\">
<CurrentFolder path=\"/Docs/\" url=\"/data/resources/File/Docs/\" />
<Progress max=\"0\" value=\"0\" />
<RefreshURL url=\"".$fckphp_config['uploadProgressHandler']."?iTotal=&iRead=&iStatus=&sessionid=&dtnow=1098478692&dtstart=\" />
</Connector>";
	
		
		$expect=htmlentities($expect);
		$expect=str_replace(array("\n","\r"),"<br />",$expect);
		echo "<b>EXAMPLE RESPONSE</b>".$expect;
		?>
		</div>
		<br /><br />
		<?php
//--------------------------------------------------------------------------------------------------------------------------------------------------
		

?>
</body>
</html> 
