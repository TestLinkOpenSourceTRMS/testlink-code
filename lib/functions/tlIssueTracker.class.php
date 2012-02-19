<?php
/** 
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later. 
 *
 * @filesource 	tlIssueTracker.php
 * @package 	TestLink
 * @author 		franciscom
 * @copyright 	2012, TestLink community
 * @link 		http://www.teamst.org/index.php
 *
 * @internal revisions
 * @since 1.9.4
 *
**/

/**
 * 
 * @package 	TestLink
 */
class tlIssueTracker extends tlObject
{
    
	/** @var resource the database handler */
	var $db;

    var $types = array('NONE','BUGZILLA','BUGZILLAXMLRPC','EVENTUM',
					   'FOGBUGZ','GFORGE','JIRA','JIRASOAP','MANTIS',
					   'MANTISSOAP','POLARION','SEAPINE','TRACKPLUS','YOUTRACK');
    
    
	/**
	 * Class constructor
	 * 
	 * @param resource &$db reference to the database handler
	 */
	function __construct(&$db)
	{
   		parent::__construct();

		$this->db = &$db;
	}


    /**
	 * @return hash with available locatipons
	 * 
	 * 
     */
	function getTypes()
	{
        return($this->types);
    }


	/**
	 *
	 */
	function create($it)
  	{
		$debugMsg = 'Class:' . __CLASS__ . ' - Method: ' . __FUNCTION__;
	    $ret = array('status_ok' => 0, 'id' => 0, 'msg' => 'name already exists');
	
		// need to check if name already exist
		if( is_null($this->getByName($it->name,array('output' => 'id')) ))
		{
			$safeobj = $this->sanitize($it);	
			$sql = 	"/* debugMsg */ INSERT  INTO {$this->tables['issuetrackers']} " .
					" (name,cfg,type) " .
					" VALUES('" . $safeobj->name . "','" . $safeobj->cfg . "',{$safeobj->type})"; 

		   	if( $this->db->exec_query($sql) )
		  	{
		  		// at least for Postgres DBMS table name is needed.
		  	  	$itemID=$this->db->insert_id($this->tables['issuetrackers']);
		        $ret = array('status_ok' => 1, 'id' => $itemID, 'msg' => 'ok');
		    }
		    else
		    {
	    		$ret = array('status_ok' => 0, 'id' => 0, 'msg' => $this->db->error_msg());
		    }
		}
		
	    return $ret;
	}


	/**
	 *
	 */
	function update($it)
	{
		$debugMsg = 'Class:' . __CLASS__ . ' - Method: ' . __FUNCTION__ . ' - ';
		$msg = array();
		$msg['duplicate_name'] = "name %s exists for id %s";
		$msg['ok'] = "operation OK for id %s";

		$safeobj = $this->sanitize($it);
	    $ret = array('status_ok' => 1, 'id' => $it->id, 'msg' => $debugMsg);


		// check for duplicate name
		$info = $this->getByName($safeobj->name);
	    if( !is_null($info) && ($info['id'] != $it->id) )
	    {
	    	$ret['status_ok'] = 0;
	    	$ret['msg'] .= sprintf($msg['duplicate_name'], $safeobj->name, $info['id']);
	    }
		
		if( $ret['status_ok'] )		
		{
			$sql =	"UPDATE {$this->tables['issuetrackers']}  " .
				 	" SET	name = '" . $safeobj->name. "'," . 
				 	"		cfg = '" . $safeobj->cfg . "'," .
				 	"     	type = " . $safeobj->type . 
				 	" WHERE id = " . intval($it->id);
			$result = $this->db->exec_query($sql);
			$ret['msg'] .= sprintf($msg['ok'],$it->id);
		
		}
		return $ret;
		
	} //function end



	/**
	 * delete can be done ONLY if ID is not linked to test project
	 */
	function delete($id)
	{
		$debugMsg = 'Class:' . __CLASS__ . ' - Method: ' . __FUNCTION__ . ' - ';

		$msg = array();
		$msg['linked'] = "Failure - id %s is linked to: ";
		$msg['tproject_details'] = " testproject '%s' with id %s %s";
		$msg['syntax_error'] = "Syntax failure - id %s seems to be an invalid value";
		$msg['ok'] = "operation OK for id %s";
		
	    $ret = array('status_ok' => 1, 'id' => $id, 'msg' => $debugMsg);
		if(is_null($id) || ($safeID = intval($id)) <= 0)
		{
	    	$ret['status_ok'] = 0;
	    	$ret['id'] = $id;
			$ret['msg'] .= sprintf($msg['syntax_error'],$id);
			return $ret;   // >>>-----> Bye!
        }


		// check if ID is linked
		$links = $this->getLinks($safeID);
		if( is_null($links) )
		{
			$sql =	" /* $debugMsg */ DELETE FROM {$this->tables['issuetrackers']}  " .
				 	" WHERE id = " . intval($safeID);
			$result = $this->db->exec_query($sql);
			$ret['msg'] .= sprintf($msg['ok'],$safeID);
		
		}
		else
		{
			$ret['status_ok'] = 0;
			$dummy = sprintf($msg['linked'],$safeID);
			$sep = ' / ';
			foreach($links as $item)
			{
				$dummy .= sprintf($msg['tproject_details'],$item['testproject_name'],$item['testproject_id'],$sep);
			}
			$ret['msg'] .= rtrim($dummy,$sep);
			
		}
		return $ret;
		
	} //function end





	/**
	 *
	 */
	function getByID($id, $options=null)
	{
		$debugMsg = 'Class:' . __CLASS__ . ' - Method: ' . __FUNCTION__;
	    return $this->getByAttr(array('key' => 'id', 'value' => $id),$options);
	}


	/**
	 *
	 */
	function getByName($name, $options=null)
	{
		$debugMsg = 'Class:' . __CLASS__ . ' - Method: ' . __FUNCTION__;
	    return $this->getByAttr(array('key' => 'name', 'value' => $name),$options);
	}


	/**
	 *
	 */
	function getByAttr($attr, $options=null)
	{
		$debugMsg = 'Class:' . __CLASS__ . ' - Method: ' . __FUNCTION__;
	
		$my['options'] = array('output' => 'full');
		$my['options'] = array_merge($my['options'], (array)$options);
	
		$sql = "/* debugMsg */ SELECT ";
	 	switch($my['options']['output'])
	 	{
	 		case 'id':
				 $sql .= " id ";
	 		break;
	
	 		case 'full':
	 		deafult:
				 $sql .= " * ";
	 		break;
	 		
	 	}
	 	 
	 	switch($attr['key'])
	 	{
	 		case 'id':
			     $where = " WHERE id = " . intval($attr['value']);
	 		break;
	
	 		case 'name':
	 		default:
			     $where = " WHERE name = '" . $this->db->prepare_string($attr['value']) . "'";
	 		break;
	 	}
	 	 
	 	 
		$sql .= " FROM {$this->tables['issuetrackers']} " . $where;
		$rs = $this->db->get_recordset($sql);
	    return !is_null($rs) ? $rs[0] : null; 
	}



	/*
	 *
	 *	keys	name	-> trim will be applied
     *	   		type	-> intval() wil be applied
     *	   		cfg		
     *
	 */
	function sanitize($obj)
	{
		$sobj = $obj;
		
		// remove the standard set of characters considered harmful
		// "\0" - NULL, "\t" - tab, "\n" - new line, "\x0B" - vertical tab
		// "\r" - carriage return
		// and spaces
		// fortunatelly this is trim standard behaviour
		$k2san = array('name');
		foreach($k2san as $key)
		{	
			$sobj->$key = $this->db->prepare_string(trim($obj->$key));
		}	    
		
		// seems here is better do not touch.
	    $sobj->cfg = $this->db->prepare_string($obj->cfg);
	    $sobj->type = intval($obj->type);
		
		return $sobj;
	}	



	/*
	 *
     *
	 */
	function link($id,$tprojectID)
	{
		$debugMsg = 'Class:' . __CLASS__ . ' - Method: ' . __FUNCTION__;

		if(is_null($id))
		{
			return;
        }
        
        // Check if link exist for test project ID, in order to INSERT or UPDATE
        $statusQuo	= $this->getLinkedTo($tprojectID);
        
        if( is_null($statusQuo) )
        {
			$sql = "/* $debugMsg */ INSERT INTO {$this->tables['testproject_issuetracker']} " .
				   " (testproject_id,issuetracker_id) " .
				   " VALUES(" . intval($tprojectID) . "," . intval($id) . ")";
		}
		else
		{
			$sql = "/* $debugMsg */ UPDATE {$this->tables['testproject_issuetracker']} " .
				   " SET issuetracker_id = " . intval($id) .
				   " WHERE testproject_id = " . intval($tprojectID);
		}
		echo $sql;
		$this->db->exec_query($sql);
	}


	/*
	 *
     *
	 */
	function unlink($id,$tprojectID)
	{
		$debugMsg = 'Class:' . __CLASS__ . ' - Method: ' . __FUNCTION__;

		if(is_null($id))
		{
			return;
        }
		$sql = "/* $debugMsg */ DELETE FROM {$this->tables['testproject_issuetracker']} " .
			   	   " WHERE testproject_id = " . intval($tprojectID) . 
			   	   " AND issuetracker_id = " . intval($id);
		$this->db->exec_query($sql);
	}


	/*
	 *
     *
	 */
	function getLinks($id)
	{
		$debugMsg = 'Class:' . __CLASS__ . ' - Method: ' . __FUNCTION__;

		if(is_null($id))
		{
			return;
        }
		$sql = "/* $debugMsg */ " .
			   " SELECT TPIT.testproject_id, NHTPR.name AS testproject_name " .
			   " FROM {$this->tables['testproject_issuetracker']} TPIT" .
			   " LEFT OUTER JOIN {$this->tables['nodes_hierarchy']} NHTPR " .
			   " ON NHTPR.id = TPIT.testproject_id " . 
			   " WHERE TPIT.issuetracker_id = " . intval($id);
			   
		$ret = $this->db->fetchRowsIntoMap($sql,'testproject_id');
		return $ret;
	}



	/*
	 *
     *
	 */
	function getLinkSet()
	{
		$debugMsg = 'Class:' . __CLASS__ . ' - Method: ' . __FUNCTION__;
		
		$sql = "/* $debugMsg */ " .
			   " SELECT TPIT.testproject_id, NHTPR.name AS testproject_name, TPIT.issuetracker_id " .
			   " FROM {$this->tables['testproject_issuetracker']} TPIT" .
			   " LEFT OUTER JOIN {$this->tables['nodes_hierarchy']} NHTPR " .
			   " ON NHTPR.id = TPIT.testproject_id ";
			   
		$ret = $this->db->fetchRowsIntoMap($sql,'testproject_id');
		return $ret;
	}

	/*
	 *
     *
	 */
	function getAll()
	{
		$debugMsg = 'Class:' . __CLASS__ . ' - Method: ' . __FUNCTION__;
		$sql = "/* debugMsg */ SELECT * ";
		$sql .= " FROM {$this->tables['issuetrackers']} ORDER BY NAME ";
		$rs = $this->db->fetchRowsIntoMap($sql,'id');
		
		if( !is_null($rs) )
		{
			foreach($rs as &$item)
			{
				$item['verbose'] = $item['name'] . " ( {$this->types[$item['type']]} )" ;
			}
		}
	    return $rs;
	}


	/*
	 *
     *
	 */
	function getLinkedTo($tprojectID)
	{
		$debugMsg = 'Class:' . __CLASS__ . ' - Method: ' . __FUNCTION__;

		if(is_null($tprojectID))
		{
			return;
        }
		$sql = "/* $debugMsg */ " .
			   " SELECT TPIT.testproject_id, NHTPR.name AS testproject_name, " .
			   " TPIT.issuetracker_id,ITRK.name AS issuetracker_name, ITRK.type" .
			   " FROM {$this->tables['testproject_issuetracker']} TPIT" .
			   " JOIN {$this->tables['nodes_hierarchy']} NHTPR " .
			   " ON NHTPR.id = TPIT.testproject_id " . 
			   " JOIN {$this->tables['issuetrackers']} ITRK " .
			   " ON ITRK.id = TPIT.issuetracker_id " . 
			   " WHERE TPIT.testproject_id = " . intval($tprojectID);
			   
		$ret = $this->db->get_recordset($sql);
		return !is_null($ret) ? $ret[0] : null;
	}

} // end class
?>
