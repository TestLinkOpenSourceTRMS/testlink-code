<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later.
 *
 * @package     TestLink
 * @author      Erik Eloff
 * @copyright   2006-2009, TestLink community
 * @version     CVS: $Id: tlPlatform.class.php,v 1.22 2010/09/12 15:15:53 franciscom Exp $
 * @link        http://www.teamst.org/index.php
 *
 * @internal Revision:
 *                                 
 *	20100912 - franciscom - BUGID 3771 - getAll() MS SQL Query problem
 *	20100711 - franciscom - BUGID 3564: TestCases added via tl.addTestCaseToTestPlan won't show up for execution
 *	20100705 - franciscom - getLinkedToTestplan() - interface changes
 *	20100225 - eloff - rename platformVisibleForTestplan() to platformsActiveForTestplan()
 *	20100202 - franciscom - create() - changed return type
 *	20100201 - franciscom - linkToTestplan(), unlinkFromTestplan() - refactoring to manage null	as $id
 *		                    deleteByTestProject() - new method.
 *  20100124 - franciscom - fixed bug on getAll() - filter by active test project is not more there.
 *  20091201 - Eloff - added options to getAll() to include linked_count
 *                     Use positive logic in getAll()
 *                     Rewrite SQL queries to coding conventions (no newline in string)
 *  20091118 - franciscom - getID() - fixed added testproject id in where clause
 *	20091031 - franciscom - getAll(),getAllAsMap(),getLinkedToTestplanAsMap() - added orderBy
 *	20090807 - franciscom - added check on empty name with exception (throwIfEmptyName())
 *                          linkToTestplan(),unlinkFromTestplan() interface changes
 *	20090805 - Eloff - Updated code according to guidelines
 */

/**
 * Class for handling platforms
 * @author Eloff
 **/
class tlPlatform extends tlObjectWithDB
{
	protected $tproject_id;

	const E_NAMENOTALLOWED = -1;
	const E_NAMELENGTH = -2;
	const E_NAMEALREADYEXISTS = -4;
	const E_DBERROR = -8;
	const E_WRONGFORMAT = -16;


	/**
	 * @param $db database object
	 * @param $tproject_id to work on. If null (default) the project in session
	 *                     is used
     * DO NOT USE this kind of code is not accepted have this kind of global coupling
     * for lazy users
	 */
	public function __construct(&$db, $tproject_id = null)
	{
		parent::__construct($db);
		$this->tproject_id = $tproject_id;
	}

	/**
	 * 
	 * 
	 */
	public function setTestProjectID($tproject_id)
	{
		$this->tproject_id = $tproject_id;	
	}


	/**
	 * Creates a new platform.
	 * @return tl::OK on success otherwise E_DBERROR;
	 */
	public function create($name, $notes=null)
	{
		$op = array('status' => self::E_DBERROR, 'id' => -1);
		$safeName = $this->throwIfEmptyName($name);
		$alreadyExists = $this->getID($name);
		if ($alreadyExists)
		{
			$op = array('status' => self::E_NAMEALREADYEXISTS, 'id' => -1);
		}
		else
		{
			$sql = "INSERT INTO {$this->tables['platforms']} " .
				   "(name, testproject_id, notes) " .
				   " VALUES ('" . $this->db->prepare_string($safeName) . 
				   "', $this->tproject_id, '".$this->db->prepare_string($notes)."')";
			$result = $this->db->exec_query($sql);

            if( $result )
            {
				$op['status'] = tl::OK;
				$op['id'] = $this->db->insert_id($this->tables['platforms']);
            } 
		}
		return $op;
	}

	/**
	 * Gets all info of a platform
	 *
	 * @return array with keys id, name and notes
	 */
	public function getByID($id)
	{
		$sql =  " SELECT id, name, notes " .
				" FROM {$this->tables['platforms']} " .
				" WHERE id = {$id}";
		return $this->db->fetchFirstRow($sql);
	}
	
	/**
	 * Gets all info of a platform
	 * @return array with keys id, name and notes
     * @TODO remove - francisco
	 */
    public function getPlatform($id)
    {
    	return $this->getByID($id);
    }

	/**
	 * Updates values of a platform in database.
	 * @param $id the id of the platform to update
	 * @param $name the new name to be set
	 * @param $notes new notes to be set
	 *
	 * @return tl::OK on success, otherwise E_DBERROR
	 */
	public function update($id, $name, $notes)
	{
		$safeName = $this->throwIfEmptyName($name);
		$sql = " UPDATE {$this->tables['platforms']} " .
		       " SET name = '" . $this->db->prepare_string($name) . "' " .
		       ", notes =  '". $this->db->prepare_string($notes) . "' " .
			   " WHERE id = {$id}";
		$result =  $this->db->exec_query($sql);
		return $result ? tl::OK : self::E_DBERROR;
	}

	/**
	 * Removes a platform from the database.
	 * @TODO: remove all related data to this platform?
	 *        YES!
	 * @param $id the platform_id to delete
	 *
	 * @return tl::OK on success, otherwise E_DBERROR
	 */
	public function delete($id)
	{
		$sql = "DELETE FROM {$this->tables['platforms']} WHERE id = {$id}";
		$result = $this->db->exec_query($sql);
		
		return $result ? tl::OK : self::E_DBERROR;
	}

	/**
	 * links one or more platforms to a testplan
	 *
	 * @return tl::OK if successfull otherwise E_DBERROR
	 */
	public function linkToTestplan($id, $testplan_id)
	{
		$result = true;
		if( !is_null($id) )
		{
			$idSet = (array)$id;
			foreach ($idSet as $platform_id)
			{
				$sql = " INSERT INTO {$this->tables['testplan_platforms']} " .
						" (testplan_id, platform_id) " .
						" VALUES ($testplan_id, $platform_id)";
				$result = $this->db->exec_query($sql);
				if(!$result)
				{
					break;
				}	
			}
		}
		return $result ? tl::OK : self::E_DBERROR;
	}

	/**
	 * Removes one or more platforms from a testplan
	 * @TODO: should this also remove testcases and executions?
	 *
	 * @return tl::OK if successfull otherwise E_DBERROR
	 */
	public function unlinkFromTestplan($id,$testplan_id)
	{
		$result = true;
		if( !is_null($id) )
		{
			$idSet = (array)$id;
			foreach ($idSet as $platform_id)
			{
				$sql = " DELETE FROM {$this->tables['testplan_platforms']} " .
					   " WHERE testplan_id = {$testplan_id} " .
					   " AND platform_id = {$platform_id} ";
			    
			    $result = $this->db->exec_query($sql);
				if(!$result)
				{
					break;
				}	
			}	   
		}
		return $result ? tl::OK : self::E_DBERROR;
	}

	/**
	 * Gets the id of a platform given by name
	 *
	 * @return integer platform_id
	 */
	public function getID($name)
	{
		$sql = " SELECT id FROM {$this->tables['platforms']} " .
			   " WHERE name = '" . $this->db->prepare_string($name) . "'" .
			   " AND testproject_id = {$this->tproject_id} ";
		return $this->db->fetchOneValue($sql);
	}

	/**
	 * get all available platforms on active test project
	 *
	 * @options array $options Optional params
	 *                         ['include_linked_count'] => adds the number of
	 *                         testplans this platform is used in
	 *                         
	 * @return array 
	 *
	 * @internal revisions
	 * 20100912 - franciscom - BUGID 3771 
	 */
	public function getAll($options = null)
	{
		$debugMsg = 'Class:' . __CLASS__ . ' - Method: ' . __FUNCTION__;
		$default = array('include_linked_count' => false);
		$options = array_merge($default, (array)$options);
		
		$tproject_filter = " WHERE PLAT.testproject_id = {$this->tproject_id} ";
		
		$sql =  " SELECT id, name, notes  FROM {$this->tables['platforms']} PLAT {$tproject_filter} " .
				" ORDER BY name";

		$rs = $this->db->get_recordset($sql);
		if( !is_null($rs) && $options['include_linked_count'])
		{
			// 20100912 - franciscom
			// At least on MS SQL Server 2005 you can not do GROUP BY fields of type TEXT
			// notes is a TEXT field
			// $sql =  " SELECT PLAT.id,PLAT.name,PLAT.notes, " .
			// 		" COUNT(TPLAT.testplan_id) AS linked_count " .
			// 		" FROM {$this->tables['platforms']} PLAT " .
			// 		" LEFT JOIN {$this->tables['testplan_platforms']} TPLAT " .
			// 		" ON TPLAT.platform_id = PLAT.id " . $tproject_filter .
			// 		" GROUP BY PLAT.id, PLAT.name, PLAT.notes";
			
			$sql =  " SELECT PLAT.id, COUNT(TPLAT.testplan_id) AS linked_count " .
					" FROM {$this->tables['platforms']} PLAT " .
					" LEFT JOIN {$this->tables['testplan_platforms']} TPLAT " .
					" ON TPLAT.platform_id = PLAT.id " . $tproject_filter .
					" GROUP BY PLAT.id ";
			$figures = $this->db->fetchRowsIntoMap($sql,'id');   
			
			$loop2do = count($rs);
			for($idx=0; $idx < $loop2do; $idx++)
			{
				$rs[$idx]['linked_count'] = $figures[$rs[$idx]['id']]['linked_count'];				
			} 			   
		}
		
		return $rs;
	}

	/**
	 * get all available platforms in the active testproject ($this->tproject_id)
	 * @param string $orderBy
	 * @return array Returns 
	 *               as array($platform_id => $platform_name)
	 */
	public function getAllAsMap($accessKey='id',$output='columns',$orderBy=' ORDER BY name ')
	{
		$debugMsg = 'Class:' . __CLASS__ . ' - Method: ' . __FUNCTION__;
		$sql =  "/* $debugMsg */  SELECT id, name " .
				" FROM {$this->tables['platforms']} " .
				" WHERE testproject_id = {$this->tproject_id} {$orderBy}";
		if( $output == 'columns' )
		{
			$rs = $this->db->fetchColumnsIntoMap($sql, $accessKey, 'name');
		}
		else
		{
			$rs = $this->db->fetchRowsIntoMap($sql, $accessKey);
		}	
		return $rs;
	}

	/**
	 * Logic to determine if platforms should be visible for a given testplan.
	 * @return bool true if the testplan has one or more linked platforms;
	 *              otherwise false.
	 */
	public function platformsActiveForTestplan($testplan_id)
	{
		$debugMsg = 'Class:' . __CLASS__ . ' - Method: ' . __FUNCTION__;
		$sql = "/* $debugMsg */ SELECT COUNT(0) AS num " .
			   " FROM {$this->tables['testplan_platforms']} " .
			   " WHERE testplan_id = {$testplan_id}";
		$num_tplans = $this->db->fetchOneValue($sql);
		return ($num_tplans > 0);
	}

	/**
	 * @param map $options
	 * @return array Returns all platforms associated to a given testplan
	 *
	 * @internal revision
	 * 20100705 - franciscom - interface - BUGID 3564
	 *
	 */
	public function getLinkedToTestplan($testplanID, $options = null)
	{
		// output:
		// array => indexed array
		// mapAccessByID => map access key: id
		// mapAccessByName => map access key: name
		$my['options'] = array('outputFormat' => 'array', 'orderBy' => ' ORDER BY name ');
	    $my['options'] = array_merge($my['options'], (array)$options);
		
		$debugMsg = 'Class:' . __CLASS__ . ' - Method: ' . __FUNCTION__;
		$rs = null;
		$sql = "/* $debugMsg */ SELECT P.id, P.name, P.notes " .
			   " FROM {$this->tables['platforms']} P " .
			   " JOIN {$this->tables['testplan_platforms']} TP " .
			   " ON P.id = TP.platform_id " .
			   " WHERE  TP.testplan_id = {$testplanID} {$my['options']['orderBy']}";
		
		switch($my['options']['outputFormat'])
		{
			case 'array':
				$rs = $this->db->get_recordset($sql);
			break;
			
			case 'mapAccessByID':
				$rs = $this->db->fetchRowsIntoMap($sql,'id');
			break;
			
			case 'mapAccessByName':
				$rs = $this->db->fetchRowsIntoMap($sql,'name');
			break;
		}	   
		return $rs;
	}


	/**
	 * @param string $orderBy
	 * @return array Returns all platforms associated to a given testplan
	 *	             on the form $platform_id => $platform_name
	 */
	public function getLinkedToTestplanAsMap($testplanID,$orderBy=' ORDER BY name ')
	{
		$debugMsg = 'Class:' . __CLASS__ . ' - Method: ' . __FUNCTION__;
		$sql =  "/* $debugMsg */ SELECT P.id, P.name " .
				" FROM {$this->tables['platforms']} P " .
				" JOIN {$this->tables['testplan_platforms']} TP " .
				" ON P.id = TP.platform_id " .
				" WHERE  TP.testplan_id = {$testplanID} {$orderBy}";
		return $this->db->fetchColumnsIntoMap($sql, 'id', 'name');
	}


   
	/**
	 * @return 
	 *	       
	 */
	public function throwIfEmptyName($name)
	{
		$safeName = trim($name);
		if (tlStringLen($safeName) == 0)
		{
			$msg = "Class: " . __CLASS__ . " - " . "Method: " . __FUNCTION__ ;
			$msg .= " Empty name ";
			throw new Exception($msg);
	    }
        return $safeName;
    }


	/**
	 * 
 	 *
 	 */
	public function deleteByTestProject($tproject_id)
	{
		$sql = "DELETE FROM {$this->tables['platforms']} WHERE testproject_id = {$tproject_id}";
		$result = $this->db->exec_query($sql);
		
		return $result ? tl::OK : self::E_DBERROR;
	}


}
