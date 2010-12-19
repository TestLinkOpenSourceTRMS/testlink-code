<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 *
 * Migrate from 1.8.x tp 1.9.0
 *
 * tasks:
 * - create records on node_hierarchy for req_version
 *   getting new IDs.
 * - create records on node_hierarchy for tcsteps
 *   getting new IDs.
 * - Update IDs on ....
 * - Update project options
 * - ....
 *  
 * Included on installNewDB.php
 *
 * $Id: migrate_19_to_20.php,v 1.1 2010/12/19 17:16:19 franciscom Exp $
 * Author: franciscom
 * 
 * @internal rev:
 *	20101219 - franciscom - 
 */

// over this qty, the process will take a lot of time
define('CRITICAL_TC_SPECS_QTY',2000);
define('FEEDBACK_STEP',2500);
define('FULL_FEEDBACK',FALSE);
define('DBVERSION4MIG', 'DB 1.2');


/**
 * 
 *
 */
function migrate_19_to_20(&$dbHandler,$tableSet)
{
	// Need To Add Some Feedback
	echo '<b><br>-------------------------------------<br>'; 
	echo 'Data Migration Process STARTED<br>'; 
	echo '-------------------------------------<br></b>'; 

	if( $dbHandler->dbType == 'mssql')
	{
		echo "<b><br>**********************************************************************************<br>";
		echo "IMPORTANT NOTICE FOR MSSQL USERS<br>";
		echo "**********************************************************************************<br>";
		echo "Some updates to DB SCHEMA HAS TO BE DONE manually due to <br>";
		echo "MSSQL Restrictions<br>";
		echo "ALTER TABLE /*prefix*/requirements ALTER req_doc_id VARCHAR(64)<br>";
		echo "ALTER TABLE /*prefix*/custom_fields ALTER COLUMN possible_values varchar(4000)<br>";
		echo "ALTER TABLE /*prefix*/custom_fields ALTER COLUMN default_value varchar(4000)<br>";
		echo "**********************************************************************************<br></b>";
	}
	echo '<br>-------------------------------------<br>'; 
	echo 'Data Migration Process Finished<br>'; 
	echo '-------------------------------------<br><br><br>'; 
}
?>