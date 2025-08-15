<?php
/*
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 *
 * Class that deals with API keys
 *
 * @filesource APIKey.class.php
 * @package TestLink
 * @author TestLink community
 * @copyright 2004-2011, TestLink community
 * @link http://www.teamst.org/index.php
 *
 * @internal revisions
 */
require_once dirname(__FILE__) . '/../../config.inc.php';
require_once 'common.php';

class APIKey extends tlObjectWithDB
{

    private $object_table = "";

    public function __construct()
    {
        $db = null;
        doDBConnect($db);
        parent::__construct($db);
        $this->object_table = $this->tables["users"];
    }

    /**
     *
     * @param int $userID
     * @return mixed tl::OK / tl::ERROR
     */
    public function addKeyForUser($userID)
    {
        $query = "UPDATE {$this->object_table} " . " SET script_key='" .
            $this->generateKey() . "' " . " WHERE id='" . intval($userID) . "'";
        $result = $this->db->exec_query($query);

        if ($result) {
            $this->dbID = $this->db->insert_id();
        }
        return $result ? tl::OK : tl::ERROR;
    }

    /**
     *
     * @return string md5 hash of a string
     */
    private function generateKey()
    {
        $key = '';

        for ($i = 0; $i < 8; $i ++) {
            $key .= mt_rand();
        }

        return md5($key);
    }

    /**
     *
     * @param int $userID
     * @return $key
     */
    public function getAPIKey($userID)
    {
        $key = null;
        $key_map = $this->getAPIKeys($userID);

        if (! is_null($key_map)) {
            $key = $key_map[$userID];
        }

        return $key;
    }

    /**
     *
     * @param int $userID
     *            [userID]=default null => all APIkeys
     * @return array associative array[userID]=script_key
     */
    public function getAPIKeys($userID = null)
    {
        $query = "SELECT id, script_key " . " FROM {$this->object_table} ";

        if (is_null($userID)) {
            $whereClause = " WHERE script_key IS NOT NULL";
        } else {
            $whereClause = " WHERE id=" . intval($userID);
        }
        $query .= $whereClause;

        $rs = $this->db->fetchColumnsIntoMap($query, 'id', 'script_key');
        return $rs;
    }
}
?>
