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
 * @version    	CVS: $Id: tlInventory.class.php,v 1.11 2010/10/17 08:33:41 franciscom Exp $
 * @filesource	http://testlink.cvs.sourceforge.net/viewvc/testlink/testlink/lib/functions/tlInventory.class.php?view=markup
 * @link 		http://www.teamst.org/index.php
 * @since 		TestLink 1.9
 * 
 * @todo		ability to reserve machine for an user per dates
 *
 * @internal revisions
 * 20101017 - franciscom - BUGID 3888: Inventory fields are erased if any line break is entered (MySQL ONLY)
 * 20100516 - franciscom - readDB(),getAll() - interface changes
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
	
	
	/**
	 * Class constructor
	 * 
	 * @param integer $testProjectID test Project identifier
	 * @param integer $dbHandler the database connection handler
	 */
	function __construct($testProjectID, &$dbHandler = null)
	{
		parent::__construct($dbHandler);
		$this->testProjectID = $testProjectID;
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
	 * returns inventory data
	 * 
	 * @param mixed $ids integer or array of integer - ID of inventory items
	 */
	protected function readDB($ids = null, $options=null)
	{
		$debugMsg = 'Class:' . __CLASS__ . ' - Method: ' . __FUNCTION__;

	    $my['options'] = array('detailLevel' => null, 'accessKey' => null);
	    $my['options'] = array_merge($my['options'], (array)$options);

		$doUnserialize = true;
		switch($my['options']['detailLevel'])
		{
			case 'minimun':
				$fields2get = ' id ';
				$doUnserialize = false;
			break;
			
			default:
				$fields2get = ' * ';
			break;
		} 
		$sql = "/* $debugMsg */ SELECT {$fields2get} FROM {$this->tables['inventory']} " .
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
			$sql .= " AND " . implode(" AND ",$clauses);
		}
		
		
		if( is_null($my['options']['accessKey']) )
		{
			$recordset = $this->db->get_recordset($sql);
		}
		else
		{
			$recordset = $this->db->fetchRowsIntoMap($sql,$my['options']['accessKey']);
		}
		
		
		if(!is_null($recordset) && $doUnserialize)
		{
			// unserialize text parameters
			foreach ($recordset as $key => $item)
			{
				$dummy = unserialize($recordset[$key]['content']);
				$recordset[$key]['content'] = null;  // used for ? who knows?
				$recordset[$key]['notes'] = isset($dummy['notes']) ? $dummy['notes'] : '';
				$recordset[$key]['purpose'] = isset($dummy['purpose']) ? $dummy['purpose'] : '';
				$recordset[$key]['hardware'] = isset($dummy['hardware']) ? $dummy['hardware'] : '';  
			}
		}
		return $recordset;
	}

	
	/** 
	 * Writes a device into the database 
	 * (both create and update request are supported - based on $this->inventoryId)
	 * 
	 * @param integer $db [ref] the database connection
	 * @return integer returns tl::OK on success, tl::E_DBERROR else
	 *
	 * @internal revisions
	 * 20101017 - franciscom - BUGID 3888
	 */
	protected function writeToDB(&$db)
	{
		$auditData = $this->getAuditData();
		$auditData = current($auditData);
	
		$debugMsg = 'Class:' . __CLASS__ . ' - Method: ' . __FUNCTION__;
		$name = $db->prepare_string($this->name);
		$ip = $db->prepare_string($this->ipAddress);
		$data_serialized = $db->prepare_string(serialize($this->inventoryContent)); // BUGID 3888
		if (is_null($this->inventoryId) || ($this->inventoryId == 0))
		{
			$sql = "/* $debugMsg */ INSERT INTO {$this->tables['inventory']} (name," .
					 " testproject_id,content,ipaddress,owner_id,creation_ts) " .
					 " VALUES ('" . $name .	"'," . $this->testProjectID . ",'" . 
					$data_serialized . "','" . $ip . "'," . $this->ownerId . "," . 
					$this->db->db_now() . ")";
				
			$result = $this->db->exec_query($sql);
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
			$sql = "/* $debugMsg */UPDATE {$this->tables['inventory']} " .
					 " SET name='{$name}', content='{$data_serialized}', " .
				     " ipaddress='{$ip}', modification_ts=" . $this->db->db_now() .
				     ", testproject_id={$this->testProjectID}, owner_id=" . $this->ownerId .
					 " WHERE id={$this->inventoryId}";
			$result = $this->db->exec_query($sql);
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
	 * Deletes item from inventory on db
	 *  
	 * @param int $itemID
	 * @return integer returns tl::OK on success, tl:ERROR else
	 */
	public function deleteInventory($itemID)
	{
		$auditData = $this->getAuditData();
		$auditData = current($auditData);
		$this->inventoryId = $itemID;

		// check existence / get name of the record		
		$recordset = $this->readDB($this->inventoryId);
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
	 * @param string $options:
	 *				 detailLevel - optional - indicates data you want to have
	 *				 			 null -> all columns
	 *               			minimun -> just the id, useful when you need to delete all inventories
	 *									   for a test project
	 *				 accessKey: field name, it's value will be used as accessKey
	 *
	 * @return array 
	 */
	public function getAll($options=null)
	{
		$data = self::readDB(null,$options); 
		return $data;
	}


	/**
	 * Checks a server name and IP for a certain testproject already exists in the database
	 * Checking works for both create and update request
	 * 
	 * @return integer return tl::OK on success, else error code like 
	 * 			is tlInventory::E_NAMEALREADYEXISTS 
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
			
			$sql = "/* $debugMsg */ SELECT id FROM {$this->tables['inventory']} " .
					 " WHERE name='" . $name.
			         "' AND testproject_id={$this->testProjectID}";
			         
			if ($this->inventoryId > 0) // for update
			{
				$sql .= ' AND NOT id='.$this->inventoryId;
			}

			if ($this->db->fetchFirstRow($sql))
			{
				$result = self::E_NAMEALREADYEXISTS;
				$this->userFeedback = langGetFormated('inventory_name_exists',$this->name);
			}
		}

		if ($result == tl::OK && !empty($ipAddress))
		{
			$sql = "/* $debugMsg */ SELECT id FROM {$this->tables['inventory']} " .
					 " WHERE ipaddress='" . $ipAddress . 
		    	     "' AND testproject_id={$this->testProjectID}";

			if ($this->inventoryId > 0) // for update
			{
				$sql .= ' AND NOT id='.$this->inventoryId;
			}
			if ($this->db->fetchFirstRow($sql))
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