<?php
/** 
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later. 
 * 
 * Management and assignment of project infrastructure (servers, switches, etc.)
 *
 * @package 	TestLink
 * @author 		Martin Havlat
 * @copyright 	2009, TestLink community 
 * @version    	CVS: $Id: tlInfrastructure.class.php,v 1.1 2010/02/12 00:20:12 havlat Exp $
 * @filesource	http://testlink.cvs.sourceforge.net/viewvc/testlink/testlink/lib/functions/tlInfrastructure.class.php?view=markup
 * @link 		http://www.teamst.org/index.php
 * @since 		TestLink 1.9
 * 
 * @todo		ability to reserve machine for an user per dates
 *
 **/

/** parenthal classes */
require_once('object.class.php');


/**
 * Logic for Infrastructure feature
 * @package 	TestLink
 */ 
class tlInfrastructure extends tlObjectWithDB
{
	/** @var integer the test project ID the server belongs to */
	protected $testProjectID;
	/** 
	 * @var array with a machine data; by default include keys:
	 * 		purpose, notes, spec
	 */
	protected $infrastructureContent = array();
	/** @var integer the server owner ID */
	protected $infrastructureID;
	/** @var integer the item (server/machine) ID */
	protected $ownerID = 0;
	/** @var string the host-name of the server/machine */
	protected $name;
	/** @var string IP address of the server */
	protected $ipAddress = '';

	/** error codes */
	const E_NAMEALREADYEXISTS = -1;
	//const E_NAMELENGTH = -2;
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
	 * Initializes the infrastructure object
	 * @param array $inputData the name of the server
	 */
	public function initInfrastructureData($inputData)
	{
		$this->infrastructureId = intval($inputData->machineID);
		$this->name = $inputData->machineName;
		$this->ipAddress = $inputData->machineIp;
		$this->ownerID = $inputData->machineOwner;
		$this->infrastructureContent['notes'] = $inputData->machineNotes;
		$this->infrastructureContent['purpose'] = $inputData->machinePurpose;
		$this->infrastructureContent['hardware'] = $inputData->machineHw;
	}

	/**
	 * Get the current array
	 * @return array data record
	 */
	public function getCurrentData()
	{
		$out = new stdClass();
		$out->machineID = $this->infrastructureId;
		$out->machineName = $this->name;
		$out->machineIp = $this->ipAddress;
		$out->machineOwner = $this->ownerID;
		$out->machineNotes = $this->infrastructureContent['notes'];
		$out->machinePurpose = $this->infrastructureContent['purpose'];
		$out->machineHw = $this->infrastructureContent['hardware'];
		
		return $out;
	}

	/** 
	 * Returns a query which can be used to read one or multiple items from a db
	 * 
	 * @param mixed $ids integer or array of integer - ID of infrastructure items
	 */
	public function executeQuery($ids = null)
	{
		$query = " SELECT * FROM {$this->tables['infrastructure']} ";
		
		$clauses = null;
		if (!is_null($ids))
		{
			if (!is_array($ids))
				$clauses[] = "id = {$ids}";
			else		
				$clauses[] = "id IN (".implode(",",$ids).")";
		}
		if ($clauses)
		{
			$query .= " WHERE " . implode(" AND ",$clauses);
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
		$name = $db->prepare_string($this->name);
		$ip = $db->prepare_string($this->ipAddress);
		$this->infrastructureContent['hardware'] = $db->prepare_string($this->infrastructureContent['hardware']);
		$this->infrastructureContent['notes'] = $db->prepare_string($this->infrastructureContent['notes']);
		$this->infrastructureContent['purpose'] = $db->prepare_string($this->infrastructureContent['purpose']);
		$data_serialized = serialize($this->infrastructureContent);

		if (is_null($this->infrastructureId) || ($this->infrastructureId == 0))
		{
			$query = " INSERT INTO {$this->tables['infrastructure']} (name," .
					"testproject_id,content,ipaddress,owner_id,creation_ts) " .
					" VALUES ('" . $name .	"'," . $this->testProjectID . ",'" . 
					$data_serialized . "','" . $ip . "'," . $this->ownerID . "," . 
					$this->db->db_now() . ")";
				
			$result = $this->db->exec_query($query);
			if ($result)
			{
				$this->infrastructureId = $db->insert_id($this->tables['infrastructure']);
				logAuditEvent(TLS("audit_infrastructure_created",$this->name),"CREATE",$this->name,"infrastructure");
				$this->userFeedback = langGetFormated('infrastructure_create_success',$this->name);
			}
			else
			{
				$this->userFeedback = langGetFormated('infrastructure_create_fails',$this->name);
				tLog('Internal error: An infrastructure device "'.$this->name.'" was not created.', 'ERROR');
			}	
		}
		else
		{
			$query = "UPDATE {$this->tables['infrastructure']} " .
					" SET name='{$name}', content='{$data_serialized}', " .
				    " ipaddress='{$ip}', modification_ts=" . $this->db->db_now() .
				    ", testproject_id={$this->testProjectID}, owner_id=" . $this->ownerID .
					" WHERE id={$this->infrastructureId}";
			$result = $this->db->exec_query($query);
			if ($result)
			{
				tLog('An infrastructure device "'.$this->name.'" was not updated.', 'INFO');
				$this->userFeedback = langGetFormated('infrastructure_update_success',$this->name);
			}
			else
			{
				$this->setUserFeedback(langGetFormated('infrastructure_update_fails',$this->name));
				tLog('Internal error: An infrastructure device "'.$this->name.'" was not updated.', 'ERROR');
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
		$sql = "DELETE FROM {$this->tables['infrastructure']} WHERE id = " . $this->infrastructureId;
		$result = $this->db->exec_query($sql);
		return $result ? tl::OK : tl::ERROR;	
	}

	/** 
	 * Deletes a server from the database
	 *  
	 * @param resource &$db [ref] database connection
	 * @return integer returns tl::OK on success, tl:ERROR else
	 */
	public function deleteInfrastructure($itemID)
	{
		$this->infrastructureId = $itemID;
		$result = $this->deleteFromDB();

		if ($result == tl::OK)
		{
			logAuditEvent(TLS("audit_infrastructure_deleted",$this->name),"DELETE",$this->name,"infrastructure");
			$this->userFeedback = langGetFormated('infrastructure_delete_success',$this->name);
		}
		else
		{
			$this->userFeedback = langGetFormated('infrastructure_update_fails',$this->name);
			tLog('Internal error: An infrastructure device "'.$this->name.'" was not deleted.', 'ERROR');
		}	

		return $result;	
	}


	/**
	 * create or update an infrastructure
	 * 
	 * @param array $data list of parameters 
	 * @return boolean result of action 	 
	 **/
	public function setInfrastructure($data)
	{
		$this->initInfrastructureData($data);
		$result = $this->checkInfrastructureData();
		if ($result == tl::OK)
		{
			$result = $this->writeToDB($this->db);
		}
		return $result ? tl::OK : tl::ERROR;
	}


	/**
	 * 
	 * @param resource $db [ref] the database connection
	 * @param array $ids the database identifiers of the keywords
	 * @param integer $detailLevel an optional detaillevel, any combination of TLOBJ_O_GET_DETAIL Flags
	 * 
	 * @return array returns the created keywords (tlKeyword) on success, or null else
	 */
/*	static public function getByIDs(&$db,$ids,$detailLevel = self::TLOBJ_O_GET_DETAIL_FULL)
	{
		return tlDBObject::createObjectsFromDB($db,$ids,__CLASS__,false,$detailLevel);
	}
*/

	/**
	 * currently not implemented
	 * 
	 * @param resource $db 
	 * @param string $whereClause
	 * @param string $column
	 * @param string $orderBy
	 * @param integer $detailLevel
	 * @return integer returns tl::E_NOT_IMPLEMENTED
	 */
	public function getAll()
	{
		$data = self::executeQuery(); 
		return $data;
	}
	//END interface iDBSerialization

	/**
	 * checks a server name and IP for a certain testproject already exists in the database
	 * 
	 * @return integer return tl::OK if the keyword is found, else tlKeyword::E_NAMEALREADYEXISTS 
	 */
	private function checkInfrastructureData()
	{
		$result = tl::OK;
		$name = $this->db->prepare_string(strtoupper($this->name));
		$ipAddress = $this->db->prepare_string(strtoupper($this->ipAddress));

		if (is_null($this->infrastructureId) || ($this->infrastructureId == 0))
		{
			$query = " SELECT id FROM {$this->tables['infrastructure']} " .
					 " WHERE name='" . $name.
			         "' AND testproject_id={$this->testProjectID}";
			if ($this->db->fetchFirstRow($query))
			{
				$result = self::E_NAMEALREADYEXISTS;
				$this->userFeedback = langGetFormated('infrastructure_name_exists',$this->name);
			}

			if ($result && !empty($ipAddress))
			{
				$query = " SELECT id FROM {$this->tables['infrastructure']} " .
						 " WHERE ipaddress='" . $ipAddress . 
			    	     "' AND testproject_id={$this->testProjectID}";
				if ($this->db->fetchFirstRow($query))
				{
					$result = self::E_IPALREADYEXISTS;
					$this->userFeedback = langGetFormated('infrastructure_ip_exists',$this->name);
				}
			}
		}
		
		return $result;
	}

}
?>