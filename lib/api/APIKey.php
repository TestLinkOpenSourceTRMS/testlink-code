<?php
/*
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * $Id: APIKey.php,v 1.1 2008/01/12 02:39:33 asielb Exp $
 * 
 * Class that deals with API keys
 */
require_once(dirname(__FILE__) . "/../../config.inc.php");
require_once('common.php');

class APIKey extends tlObjectWithDB
{	
	public function __construct()
	{		
		$db = null;
		doDBConnect($db);				
		parent::__construct($db);	
	}
	
	public function addKeyForUser($userid)
	{
		$query = "INSERT INTO api_developer_keys(user_id, developer_key) VALUES($userid, '" . 
					$this->generate_key() . "')"; 
		$result = $this->db->exec_query($query);
		if ($result)
			$this->dbID = $this->db->insert_id();
		
		return $result ? tl::OK : tl::ERROR;
	}

	/**
	 *  very simple key generation 
	*/
	public function generate_key()
	{
		$key = '';
		
		for($i=0; $i<8; $i++)
		  $key .= mt_rand();
		
		return md5($key) . "\n";
	}
	
	public static function getAPIKeys($db)
	{
		$query = "SELECT user_id,developer_key FROM api_developer_keys";
		$result = $db->fetchColumnsIntoMap($query, "user_id", "developer_key");
		// deal with the case where there are no API keys yet
		if(sizeof($result)==0)
		{
			return array();
		}
		else
		{
			return $result;
		}
	}	
}


?>