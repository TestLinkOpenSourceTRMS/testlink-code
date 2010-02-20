<?php
/** 
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later. 
 * 
 * Management and assignment of project inventory (servers, switches, etc.)
 *
 * @package 	TestLink
 * @author 		Martin Havlat
 * @copyright 	2009, TestLink community 
 * @version    	CVS: $Id: tlInventory.class.php,v 1.4 2010/02/20 13:47:48 franciscom Exp $
 * @filesource	http://testlink.cvs.sourceforge.net/viewvc/testlink/testlink/lib/functions/tlInventory.class.php?view=markup
 * @link 		http://www.teamst.org/index.php
 * @since 		TestLink 1.9
 * 
 * @todo		ability to reserve machine for an user per dates
 *
 **/

/** parenthal classes */
require_once('object.class.php');


/**
 * Logic code for Inventory functionality
 * @package 	TestLink
 * @author 		Martin Havlat
 * @since 		TestLink 1.9
 */ 
class tlInventory extends tlObjectWithDB
{
	/** @var integer the test project ID the server belongs to */
	protected $testProjectID;

	/** 
	 * @var array with a machine data; by default include keys:
	 * 		purpose, notes, spec
	 */
	protected $inventoryContent = array();

	/** @var integer the server owner ID */
	protected $inventoryId;

	/** @var integer the item (server/machine) ID */
	protected $ownerId = 0;

	/** @var string the host-name of the server/machine */
	protected $name;

	/** @var string IP address of the server */
	protected $ipAddress = '';

	/** error codes */
	const E_NAMEALREADYEXISTS = -1;
	const E_NAMELENGTH = -2;
	const E_IPALREADYEXISTS = -4;
	const E_DBERROR = -8;
	//const E_WRONGFORMAT = -16;
	
	
	/**
	 * Class constructor
	 * 
	 * @param integer $inputTestProjectID the current Test Project identifier
	 * @param integer $db the database connection identifier
	 */
	function __construct($inputTestProjectID, &$dbID = null)
	{
		parent::__construct($dbID);
		$this->testProjectID = $inputTestProjectID;
	}
	
	/**
	 * Class destructor
	 */
	function __destruct()
	{
		parent::__destruct();
		$this->testProjectID = null;
	}

	
	/**
	 * Initializes the inventory object
	 * @param array $inputData the name of the server
	 */
	protected function initInventoryData($inputData)
	{
		$this->inventoryId = intval($inputData->machineID);
		$this->name = $inputData->machineName;
		$this->ipAddress = $inputData->machineIp;
		$this->ownerId = $inputData->machineOwner;
		$this->inventoryContent['notes'] = $inputData->machineNotes;
		$this->inventoryContent['purpose'] = $inputData->machinePurpose;
		$this->inventoryContent['hardware'] = $inputData->machineHw;
	}


	/**
	 * Get the current array
	 * @return array data record
	 */
	public function getCurrentData()
	{
		$out = new stdClass();
		$out->machineID = $this->inventoryId;
		$out->machineName = $this->name;
		$out->machineIp = $this->ipAddress;
		$out->machineOwner = $this->ownerId;
		$out->machineNotes = $this->inventoryContent['notes'];
		$out->machinePurpose = $this->inventoryContent['purpose'];
		$out->machineHw = $this->inventoryContent['hardware'];
		
		return $out;
	}


	/** 
	 * Returns a query which can be used to read one or multiple items from a db
	 * 
	 * @param mixed $ids integer or array of integer - ID of inventory items
	 */
	protected function executeQuery($ids = null)
	{
		$debugMsg = 'Class:' . __CLASS__ . ' - Method: ' . __FUNCTION__;
		$query = "/* $debugMsg */ SELECT * FROM {$this->tables['inventory']} " .
				 " WHERE  testproject_id={$this->testProjectID}";
		
		$clauses = null;
		if (!is_null($ids))
		{
			if (!is_array($ids))
			{
				$clauses[] = "id = {$ids}";
			}
			else
			{		
				$clauses[] = "id IN (".implode(",",$ids).")";
			}	
		}
		if ($clauses)
		{
			$query .= " AND " . implode(" AND ",$clauses);
		}
		
		$recordset = $this->db->get_recordset($query);
		if(!is_null($recordset))
		{
			// unserialize text parameters
			foreach ($recordset as $key => $item)
			{
				$tmpArray = unserialize($recordset[$key]['content']);
				$recordset[$key]['content'] = null;
				$recordset[$key]['notes'] = isset($tmpArray['notes']) ? $tmpArray['notes'] : '';
				$recordset[$key]['purpose'] = isset($tmpArray['purpose']) ? $tmpArray['purpose'] : '';
				$recordset[$key]['hardware'] = isset($tmpArray['hardware']) ? $tmpArray['hardware'] : '';
			}
		}
		return $recordset;
	}

	
	/** 
	 * Writes an keyword into the database
	 * 
	 * @param resource $db [ref] the database connection
	 * @return integer returns tl::OK on success, tl::ERROR else
	 */
	protected function writeToDB(&$db)
	{
		$auditData = $this->getAuditData();
		$auditData = current($auditData);
	
		$debugMsg = 'Class:' . __CLASS__ . ' - Method: ' . __FUNCTION__;
		$name = $db->prepare_string($this->name);
		$ip = $db->prepare_string($this->ipAddress);
		$this->inventoryContent['hardware'] = $db->prepare_string($this->inventoryContent['hardware']);
		$this->inventoryContent['notes'] = $db->prepare_string($this->inventoryContent['notes']);
		$this->inventoryContent['purpose'] = $db->prepare_string($this->inventoryContent['purpose']);
		$data_serialized = serialize($this->inventoryContent);

		if (is_null($this->inventoryId) || ($this->inventoryId == 0))
		{
			$query = "/* $debugMsg */ INSERT INTO {$this->tables['inventory']} (name," .
					 " testproject_id,content,ipaddress,owner_id,creation_ts) " .
					 " VALUES ('" . $name .	"'," . $this->testProjectID . ",'" . 
					$data_serialized . "','" . $ip . "'," . $this->ownerId . "," . 
					$this->db->db_now() . ")";
				
			$result = $this->db->exec_query($query);
			if ($result)
			{
				$this->inventoryId = $db->insert_id($this->tables['inventory']);
				logAuditEvent(TLS("audit_inventory_created",$this->name,$auditData['tproject_name']),
				              "CREATE",$this->name,"inventory");
				$this->userFeedback = langGetFormated('inventory_create_success',$this->name);
			}
			else
			{
				$this->userFeedback = langGetFormated('inventory_create_fails',$this->name);
				tLog('Internal error: An inventory device "'.$this->name.'" was not created.', 'ERROR');
			}	
		}
		else
		{
			$query = "/* $debugMsg */UPDATE {$this->tables['inventory']} " .
					 " SET name='{$name}', content='{$data_serialized}', " .
				     " ipaddress='{$ip}', modification_ts=" . $this->db->db_now() .
				     ", testproject_id={$this->testProjectID}, owner_id=" . $this->ownerId .
					 " WHERE id={$this->inventoryId}";
			$result = $this->db->exec_query($query);
			if ($result)
			{
				tLog('A device "'.$this->name.'" was not updated.', 'INFO');
				$this->userFeedback = langGetFormated('inventory_update_success',$this->name);
			}
			else
			{
				$this->setUserFeedback(langGetFormated('inventory_update_fails',$this->name));
				tLog('Internal error: An inventory device "'.$this->name.'" was not updated.', 'ERROR');
			}	
		}

		return $result ? tl::OK : self::E_DBERROR;
	}


	/** 
	 * DB request to delete a device from the database
	 *  
	 * @return integer returns tl::OK on success, tl:ERROR else
	 */
	protected function deleteFromDB()
	{
		$debugMsg = 'Class:' . __CLASS__ . ' - Method: ' . __FUNCTION__;
		$sql = "/* $debugMsg */ DELETE FROM {$this->tables['inventory']} WHERE id = " . $this->inventoryId;
		$result = $this->db->exec_query($sql);
		return $result ? tl::OK : tl::ERROR;	
	}

	/** 
	 * Deletes a server from the database
	 *  
	 * @param resource &$db [ref] database connection
	 * @return integer returns tl::OK on success, tl:ERROR else
	 */
	public function deleteInventory($itemID)
	{
		$auditData = $this->getAuditData();
		$auditData = current($auditData);
		$this->inventoryId = $itemID;

		// check existence / get name of the record		
		$recordset = $this->executeQuery($this->inventoryId);
		if(!is_null($recordset))
		{
			$this->name = $recordset[0]['name'];
			$result = $this->deleteFromDB();

			if ($result == tl::OK)
			{
				logAuditEvent(TLS("audit_inventory_deleted",$this->name,$auditData['tproject_name']),
				              "DELETE",$this->name,"inventory");
				$this->userFeedback = langGetFormated('inventory_delete_success',$this->name);
			}
			else
			{
				$this->userFeedback = langGetFormated('inventory_delete_fails',$this->name);
				tLog('Internal error: The device "'.$this->name.'" was not deleted.', 'ERROR');
			}	
		}
		else
		{
			$this->userFeedback = lang_get('inventory_no_device').' ID='.$this->inventoryId;
			tLog('Internal error: The device "'.$this->name.'" was not deleted.', 'ERROR');
		}

		return $result;	
	}


	/**
	 * create or update an inventory
	 * 
	 * @param array $data list of parameters 
	 * @return boolean result of action 	 
	 **/
	public function setInventory($data)
	{
		$this->initInventoryData($data);
		$result = $this->checkInventoryData();
		if ($result == tl::OK)
		{
			$result = $this->writeToDB($this->db);
		}
		return $result;
	}


	/**
	 * Get all inventory data for the project
	 * 
	 * @return array 
	 */
	public function getAll()
	{
		$data = self::executeQuery(); 
		return $data;
	}


	/**
	 * checks a server name and IP for a certain testproject already exists in the database
	 * some checks are valid for create only
	 * 
	 * @return integer return tl::OK if the keyword is found, else tlKeyword::E_NAMEALREADYEXISTS 
	 */
	protected function checkInventoryData()
	{
		$debugMsg = 'Class:' . __CLASS__ . ' - Method: ' . __FUNCTION__;
		$result = tl::OK;
		$name = $this->db->prepare_string(strtoupper($this->name));
		$ipAddress = $this->db->prepare_string(strtoupper($this->ipAddress));

		if (strlen($name) == 0)
		{
			$result = self::E_NAMELENGTH;
			$this->userFeedback = langGetFormated('inventory_name_empty',$name);
		}

		if ($result == tl::OK)
		{
			
			$query = "/* $debugMsg */ SELECT id FROM {$this->tables['inventory']} " .
					 " WHERE name='" . $name.
			         "' AND testproject_id={$this->testProjectID}";
			         
			if ($this->inventoryId > 0) // for update
			{
				$query .= ' AND NOT id='.$this->inventoryId;
			}
			tlog($query . ' ok ' . $this->inventoryId,'ERROR');
			if ($this->db->fetchFirstRow($query))
			{
				$result = self::E_NAMEALREADYEXISTS;
				$this->userFeedback = langGetFormated('inventory_name_exists',$this->name);
			}
		}

		if ($result == tl::OK && !empty($ipAddress))
		{
			$query = "/* $debugMsg */ SELECT id FROM {$this->tables['inventory']} " .
					 " WHERE ipaddress='" . $ipAddress . 
		    	     "' AND testproject_id={$this->testProjectID}";

			if ($this->inventoryId > 0) // for update
			{
				$query .= ' AND NOT id='.$this->inventoryId;
			}
			if ($this->db->fetchFirstRow($query))
			{
				$result = self::E_IPALREADYEXISTS;
				$this->userFeedback = langGetFormated('inventory_ip_exists',$ipAddress);
			}
		}
		
		return $result;
	}
	
	/**
	 * 
 	 *
     */
	protected function getAuditData()
	{
		$debugMsg = 'Class:' . __CLASS__ . ' - Method: ' . __FUNCTION__;
		$sql = " /* $debugMsg */ " .
		       " SELECT id, name AS tproject_name FROM {$this->tables['nodes_hierarchy']} " .
		       " WHERE id = {$this->testProjectID} ";
  		$info = $this->db->fetchRowsIntoMap($sql,'id');
		return $info;        
	}

	
}
?>