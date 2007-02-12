PHP Connector for the FCKeditor v2 File Manager
Written By Grant French, Sept 2004
grant@mcpuk.net - http://www.mcpuk.net 

FCK Editor - Written By Frederico Caldeira Knabben
http://www.fckeditor.net 

Icons supplied for file types from everaldo - 
http://www.everaldo.com

Permissions fix for upload.cgu and fix for
thumbnail background colour by -
Ben Lancaster (benlanc@ster.me.uk)

!!PLEASE READ THROUGH THIS README *BEFORE* CRYING FOR HELP!!


NOTES:
------

This software is provided as is with no guarantees or
warranties what so ever. It has been proven to work
under the following setups (many other should work):-

PHP 4.3.x / Apache 1.3.x (FreeBSD/WinXP Pro/OpenBSD)
PHP 5.x / Apache 2.x (Linux)

Reported to work under IIS with minor alterations.
(Manually define $_SERVER['DOCUMENT_ROOT'] and dont use
the upload cgi)

!!! if you specify the relative path of the connector in 
	the fckconfig.js file you may experience some unusual
	behaviour mainly in firefox, like the resource list
	failing to refresh. To avoid this specify the absolute
	path of the connector.

	
CONNECTOR CONFIGURATION
-----------------------

/FCKeditor/editor/filemanager/browser/mcpuk/connectors/php/config.php
The config.php file contains most of the configuration options for the
connector, the file is fully commented, so please take a look through it.
		
Most commonly asked question:
Can i put the userfiles outside the FCKeditor folder?

The answer is yes, the folder containing all users files is 
setup be default to be /UserFiles this can be changed 
to anything you like, such as /userdata/editorfiles this is
done by editing the $fckphp_config['UserFilesPath'] directive 
in the config.php file for the connector



FILE & FOLDER SETUP:
--------------------

1. Create the user files folder (set in the config.php)
	NOTE: you no longer need to create the Image/File/Media/Flash folders
	if you are using the 'Authentication' option in the connector.
	If you are not using the 'Authentication' option then you must create
	the Image File Media and Flash folders in the user files folder.
	
2. The user files folder must all be chmodded to 0777 (a+wrx)



Upload Progress (Optional) - Rather Buggy
-----------------------------------------
You may move the filemanager/browser/default/connectors/php/Commands/helpers/upload.cgi ,
filemanager/browser/default/connectors/php/Commands/helpers/progress.cgi and
filemanager/browser/default/connectors/php/Commands/helpers/header.cgi
to you cgi-bin folder or set the settings on the folder to allow these to be executed,
check the first line in each of these files to check that the path to perl is correct
for your setup. Open the header.cgi file, check and modify settings as required.
If you have moved these files adjust the path to them in the fckconfig lines as 
specified above and the path the progress.cgi in the config.php file (see below)



FINAL NOTE:
-----------

1. Limited support will be provided by email as and when i have time.
2. If you have any ideas share them either by mailing me or in the 
	forum for the fckeditor (http://sf.net/projects/fckeditor/forums)
3. Enjoy


LICENSE:
--------

LGPL - Lesser General Public License (see LICENSE.txt file)
