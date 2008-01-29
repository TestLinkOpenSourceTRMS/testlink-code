h<?php
/*
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * $Id: APIKey.php,v 1.5 2008/01/29 20:50:23 franciscom Exp $
 * 
 * Class that deals with API keys
 *
 * rev:
 *     20080129 - franciscom - generateKey
 *                refactoring
 *
 */
require_once(dirname(__FILE__) . "/../../config.inc.php");
require_once('common.php');

class APIKey extends tlObjectWithDB
{	
  private $object_table="users";


	public function __construct()
	{		
		$db = null;
		doDBConnect($db);				
		parent::__construct($db);	
	}
	
	/*
    function: addKeyForUser

    args: userid
    
    returns: tl::OK / tl::ERROR

  */
	public function addKeyForUser($userID)
	{
		$query = "UPDATE {$this->object_table} " .
		         " SET script_key='" . $this->generateKey() . "' " .
		         " WHERE id='".intval($userID)."'"; 
		$result = $this->db->exec_query($query);
		
		if ($result)
			$this->dbID = $this->db->insert_id();
		
		return $result ? tl::OK : tl::ERROR;
	}

	/*
    function: generateKey

    args: -
    
    returns: key

  */
	private function generateKey()
	{
		$key = '';
		
		for($i=0; $i<8; $i++)
		  $key .= mt_rand();
		
		return md5($key);
	}

	/*
    function: getAPIKey

    args: -
    
    returns: key

  */
	public function getAPIKey($userID)
	{
	    $key=null;
	    $key_map=$this->getAPIKeys($userID);
	    
	    if( !is_null($key_map) )
	    {
	        $key=$key_map[$userID];  
	    }
	    		
		  return $key;
	}


	/*
    function: getAPIKeys

    args: [userID]=default null => all APIkeys
    
    returns: map
             associative array[userID]=script_key

  */
  public function getAPIKeys($userID=null)
  {
      $query = "SELECT id, script_key " .
               " FROM {$this->object_table} " ;
               
      if( is_null($userID) )
      {
          $whereClause = " WHERE script_key IS NOT NULL";    
      }         
      else
      {
          $whereClause = " WHERE id=" . intval($userID);
      }         
      $query .= $whereClause;        
               
      $rs = $this->db->fetchColumnsIntoMap($query, 'id', 'script_key');
      return $rs;
  }
  
}



		

?>