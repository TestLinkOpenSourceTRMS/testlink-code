<?php

/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later.
 *
 * @filesource  tlCodeTracker.php
 * @author      uwe_kirst@mentor.com
 *
 *
 **/

/**
 *
 * @package TestLink
 */
class tlCodeTracker extends tlObject
{

    /** @var resource the database handler */
    protected $db;

    private $types;

    // IMPORTANT NOTICE
    // array index is used AS CODE that will be written to DB
    // if you need to add a new item start on 200, to avoid crash with standard ID
    //
    private $systems = array(
        1 => array(
            'type' => 'stash',
            'api' => 'rest',
            'enabled' => true,
            'order' => - 1
        )
    );

    private $entitySpec = array(
        'name' => 'string',
        'cfg' => 'string',
        'type' => 'int'
    );

    /**
     * Class constructor
     *
     * @param
     *            resource &$db reference to the database handler
     */
    public function __construct(&$db)
    {
        parent::__construct();

        // populate types property
        $this->getTypes();
        $this->db = &$db;
    }

    /**
     *
     * @return array
     *
     *
     */
    private function getSystems($opt = null)
    {
        $my = array(
            'options' => null
        );
        $my['options']['status'] = 'enabled'; // enabled,disabled,all
        $my['options'] = array_merge($my['options'], (array) $opt);

        switch ($my['options']['status']) {
            case 'enabled':
                $tval = true;
                break;

            case 'disabled':
                $tval = false;
                break;

            default:
                $tval = null;
                break;
        }

        $ret = array();
        foreach ($this->systems as $code => $elem) {
            if ($tval == null || $elem['enabled'] == $tval) {
                $ret[$code] = $elem;
            }
        }
        return $ret;
    }

    /**
     *
     * @return array
     *
     *
     */
    public function getTypes()
    {
        if (is_null($this->types)) {
            foreach ($this->systems as $code => $spec) {
                if ($spec['enabled']) {
                    $this->types[$code] = $spec['type'] .
                        " (Interface: {$spec['api']})";
                }
            }
        }
        return $this->types;
    }

    /**
     *
     * @return
     *
     *
     */
    public function getImplementationForType($codeTrackerType)
    {
        $spec = $this->systems[$codeTrackerType];
        return $spec['type'] . $spec['api'] . 'Interface';
    }

    /**
     *
     * @return array
     *
     *
     */
    public function getEntitySpec()
    {
        return $this->entitySpec;
    }

    /**
     */
    public function create($it)
    {
        $ret = array(
            'status_ok' => 0,
            'id' => 0,
            'msg' => 'name already exists'
        );

        // Critic we need to do this before sanitize, because $it is changed
        $xlmCfg = trim($it->cfg);

        // allow empty config
        if (strlen($xlmCfg) > 0) {
            $ret = $this->checkXMLCfg($xlmCfg);
            if (! $ret['status_ok']) {
                return $ret; // >>>---> Bye!
            }
        }

        $safeobj = $this->sanitize($it);
        // empty name is not allowed
        if (is_null($safeobj->name)) {
            $ret['msg'] = 'empty name is not allowed';
            return $ret; // >>>---> Bye!
        }

        // need to check if name already exist
        if (is_null($this->getByName($it->name, array(
            'output' => 'id'
        )))) {
            $sql = "/* debugMsg */ INSERT  INTO {$this->tables['codetrackers']} " .
                " (name,cfg,type) " . " VALUES('" . $safeobj->name . "','" .
                $safeobj->cfg . "',{$safeobj->type})";

            if ($this->db->exec_query($sql)) {
                // at least for Postgres DBMS table name is needed.
                $itemID = $this->db->insert_id($this->tables['codetrackers']);
                $ret = array(
                    'status_ok' => 1,
                    'id' => $itemID,
                    'msg' => 'ok'
                );
            } else {
                $ret = array(
                    'status_ok' => 0,
                    'id' => 0,
                    'msg' => $this->db->error_msg()
                );
            }
        }

        return $ret;
    }

    /**
     */
    public function update($it)
    {
        $msg = array();
        $msg['duplicate_name'] = "Update can not be done - name %s already exists for id %s";
        $msg['ok'] = "operation OK for id %s";

        // Critic we need to do this before sanitize, because $it is changed
        $xlmCfg = trim($it->cfg);

        $safeobj = $this->sanitize($it);
        $ret = array(
            'status_ok' => 1,
            'id' => $it->id,
            'msg' => ''
        );

        // allow empty config
        if (strlen($xlmCfg) > 0) {
            $ret = $this->checkXMLCfg($xlmCfg);
        }

        // check for duplicate name
        if ($ret['status_ok']) {
            $info = $this->getByName($safeobj->name);
            if (! is_null($info) && ($info['id'] != $it->id)) {
                $ret['status_ok'] = 0;
                $ret['msg'] .= sprintf($msg['duplicate_name'], $safeobj->name,
                    $info['id']);
            }
        }

        if ($ret['status_ok']) {
            $sql = "UPDATE {$this->tables['codetrackers']}  " . " SET name = '" .
                $safeobj->name . "'," . "     cfg = '" . $safeobj->cfg . "'," .
                "     type = " . $safeobj->type . " WHERE id = " .
                intval($it->id);
            $this->db->exec_query($sql);
            $ret['msg'] .= sprintf($msg['ok'], $it->id);
        }
        return $ret;
    }

    /**
     * delete can be done ONLY if ID is not linked to test project
     */
    public function delete($id)
    {
        $debugMsg = 'Class:' . __CLASS__ . ' - Method: ' . __FUNCTION__ . ' - ';

        $msg = array();
        $msg['linked'] = "Failure - id %s is linked to: ";
        $msg['tproject_details'] = " testproject '%s' with id %s %s";
        $msg['syntax_error'] = "Syntax failure - id %s seems to be an invalid value";
        $msg['ok'] = "operation OK for id %s";

        $ret = array(
            'status_ok' => 1,
            'id' => $id,
            'msg' => $debugMsg
        );
        if (is_null($id) || ($safeID = intval($id)) <= 0) {
            $ret['status_ok'] = 0;
            $ret['id'] = $id;
            $ret['msg'] .= sprintf($msg['syntax_error'], $id);
            return $ret; // >>>-----> Bye!
        }

        // check if ID is linked
        $links = $this->getLinks($safeID);
        if (is_null($links)) {
            $sql = " /* {$debugMsg} */ DELETE FROM {$this->tables['codetrackers']}  " .
                " WHERE id = " . intval($safeID);
            $this->db->exec_query($sql);
            $ret['msg'] .= sprintf($msg['ok'], $safeID);
        } else {
            $ret['status_ok'] = 0;
            $dummy = sprintf($msg['linked'], $safeID);
            $sep = ' / ';
            foreach ($links as $item) {
                $dummy .= sprintf($msg['tproject_details'],
                    $item['testproject_name'], $item['testproject_id'], $sep);
            }
            $ret['msg'] .= rtrim($dummy, $sep);
        }
        return $ret;
    }

    /**
     */
    public function getByID($id, $options = null)
    {
        return $this->getByAttr(array(
            'key' => 'id',
            'value' => $id
        ), $options);
    }

    /**
     */
    private function getByName($name, $options = null)
    {
        return $this->getByAttr(array(
            'key' => 'name',
            'value' => $name
        ), $options);
    }

    /**
     */
    private function getByAttr($attr, $options = null)
    {
        $my['options'] = array(
            'output' => 'full'
        );
        $my['options'] = array_merge($my['options'], (array) $options);

        $sql = "/* debugMsg */ SELECT ";
        switch ($my['options']['output']) {
            case 'id':
                $sql .= " id ";
                break;

            case 'full':
            default:
                $sql .= " * ";
                break;
        }

        switch ($attr['key']) {
            case 'id':
                $where = " WHERE id = " . intval($attr['value']);
                break;

            case 'name':
            default:
                $where = " WHERE name = '" .
                    $this->db->prepare_string($attr['value']) . "'";
                break;
        }

        $sql .= " FROM {$this->tables['codetrackers']} " . $where;
        $rs = $this->db->get_recordset($sql);
        if (! is_null($rs)) {
            $rs = $rs[0];
            $rs['implementation'] = $this->getImplementationForType($rs['type']);
        }
        return $rs;
    }

    /*
     * Sanitize and do minor checks
     *
     * Sanitize Operations
     * keys name -> trim will be applied
     * type -> intval() wil be applied
     * cfg
     *
     * For strings also db_prepare_string() will be applied
     *
     *
     * Check Operations
     * keys name -> if '' => will be set to NULL
     *
     */
    private function sanitize($obj)
    {
        $sobj = $obj;

        // remove the standard set of characters considered harmful
        // "\0" - NULL, "\t" - tab, "\n" - new line, "\x0B" - vertical tab
        // "\r" - carriage return
        // and spaces
        // fortunatelly this is trim standard behaviour
        $k2san = array(
            'name'
        );
        foreach ($k2san as $key) {
            $value = trim($obj->$key);
            switch ($key) {
                case 'name':
                    $sobj->$key = ($value == '') ? null : $value;
                    break;
            }

            if (! is_null($sobj->$key)) {
                $sobj->$key = $this->db->prepare_string($obj->$key);
            }
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
    public function link($id, $tprojectID)
    {
        $debugMsg = 'Class:' . __CLASS__ . ' - Method: ' . __FUNCTION__;

        if (is_null($id)) {
            return;
        }

        // Check if link exist for test project ID, in order to INSERT or UPDATE
        $statusQuo = $this->getLinkedTo($tprojectID);

        if (is_null($statusQuo)) {
            $sql = "/* {$debugMsg} */ INSERT INTO {$this->tables['testproject_codetracker']} " .
                " (testproject_id,codetracker_id) " . " VALUES(" .
                intval($tprojectID) . "," . intval($id) . ")";
        } else {
            $sql = "/* {$debugMsg} */ UPDATE {$this->tables['testproject_codetracker']} " .
                " SET codetracker_id = " . intval($id) .
                " WHERE testproject_id = " . intval($tprojectID);
        }
        $this->db->exec_query($sql);
    }

    /*
     *
     *
     */
    public function unlink($id, $tprojectID)
    {
        $debugMsg = 'Class:' . __CLASS__ . ' - Method: ' . __FUNCTION__;

        if (is_null($id)) {
            return;
        }
        $sql = "/* {$debugMsg} */ DELETE FROM {$this->tables['testproject_codetracker']} " .
            " WHERE testproject_id = " . intval($tprojectID) .
            " AND codetracker_id = " . intval($id);
        $this->db->exec_query($sql);
    }

    /*
     *
     *
     */
    public function getLinks($id, $opt = null)
    {
        $debugMsg = 'Class:' . __CLASS__ . ' - Method: ' . __FUNCTION__;

        $my = array(
            'opt' => array(
                'getDeadLinks' => false
            )
        );
        $my['opt'] = array_merge($my['opt'], (array) $opt);

        if (is_null($id)) {
            return;
        }

        $sql = "/* {$debugMsg} */ " .
            " SELECT TPCT.testproject_id, NHTPR.name AS testproject_name " .
            " FROM {$this->tables['testproject_codetracker']} TPCT" .
            " LEFT OUTER JOIN {$this->tables['nodes_hierarchy']} NHTPR " .
            " ON NHTPR.id = TPCT.testproject_id " .
            " WHERE TPCT.codetracker_id = " . intval($id);

        if ($my['opt']['getDeadLinks']) {
            $sql .= ' AND NHTPR.id IS NULL AND NHTPR.name IS NULL ';
        }

        return $this->db->fetchRowsIntoMap($sql, 'testproject_id');
    }

    /*
     *
     *
     */
    private function getLinkSet()
    {
        $debugMsg = 'Class:' . __CLASS__ . ' - Method: ' . __FUNCTION__;

        $sql = "/* {$debugMsg} */ " .
            " SELECT TPCT.testproject_id, NHTPR.name AS testproject_name, TPCT.codetracker_id " .
            " FROM {$this->tables['testproject_codetracker']} TPCT" .
            " LEFT OUTER JOIN {$this->tables['nodes_hierarchy']} NHTPR " .
            " ON NHTPR.id = TPCT.testproject_id ";

        return $this->db->fetchRowsIntoMap($sql, 'testproject_id');
    }

    /*
     *
     *
     */
    public function getAll($options = null)
    {
        $my['options'] = array(
            'output' => null,
            'orderByField' => 'name',
            'checkEnv' => false
        );
        $my['options'] = array_merge($my['options'], (array) $options);

        $add_fields = '';
        if ($my['options']['output'] == 'add_link_count') {
            $add_fields = ", 0 AS link_count ";
        }

        $orderByClause = is_null($my['options']['orderByField']) ? '' : 'ORDER BY ' .
            $my['options']['orderByField'];

        $sql = "/* debugMsg */ SELECT * {$add_fields} ";
        $sql .= " FROM {$this->tables['codetrackers']} {$orderByClause} ";
        $rs = $this->db->fetchRowsIntoMap($sql, 'id');

        $lc = null;
        if (! is_null($rs)) {

            if ($my['options']['output'] == 'add_link_count') {
                $sql = "/* debugMsg */ SELECT COUNT(0) AS lcount, CTD.id";
                $sql .= " FROM {$this->tables['codetrackers']} CTD " .
                    " JOIN {$this->tables['testproject_codetracker']} " .
                    " ON codetracker_id = CTD.id " . " GROUP BY CTD.id ";
                $lc = $this->db->fetchRowsIntoMap($sql, 'id');
            }

            foreach ($rs as &$item) {
                $item['verbose'] = $item['name'] .
                    " ( {$this->types[$item['type']]} )";
                $item['type_descr'] = $this->types[$item['type']];
                $item['env_check_ok'] = true;
                $item['env_check_msg'] = '';
                $item['connection_status'] = '';

                if ($my['options']['checkEnv']) {
                    $impl = $this->getImplementationForType($item['type']);
                    $dummy = $impl::checkEnv();
                    $item['env_check_ok'] = $dummy['status'];
                    $item['env_check_msg'] = $dummy['msg'];
                }

                if (! is_null($lc) && isset($lc[$item['id']])) {
                    $item['link_count'] = intval($lc[$item['id']]['lcount']);
                }
            }
        }
        return $rs;
    }

    /*
     *
     *
     */
    public function getLinkedTo($tprojectID)
    {
        $debugMsg = 'Class:' . __CLASS__ . ' - Method: ' . __FUNCTION__;

        if (is_null($tprojectID)) {
            return;
        }
        $sql = "/* {$debugMsg} */ " .
            " SELECT TPCT.testproject_id, NHTPR.name AS testproject_name, " .
            " TPCT.codetracker_id,CTRK.name AS codetracker_name, CTRK.type" .
            " FROM {$this->tables['testproject_codetracker']} TPCT" .
            " JOIN {$this->tables['nodes_hierarchy']} NHTPR " .
            " ON NHTPR.id = TPCT.testproject_id " .
            " JOIN {$this->tables['codetrackers']} CTRK " .
            " ON CTRK.id = TPCT.codetracker_id " .
            " WHERE TPCT.testproject_id = " . intval($tprojectID);

        $ret = $this->db->get_recordset($sql);
        if (! is_null($ret)) {
            $ret = $ret[0];
            $ret['verboseType'] = $this->types[$ret['type']];
            $spec = $this->systems[$ret['type']];
            $ret['api'] = $spec['api'];
        }

        return $ret;
    }

    /**
     */
    public function getInterfaceObject($tprojectID)
    {
        $codeT = $this->getLinkedTo($tprojectID);
        $name = $codeT['codetracker_name'];
        $goodForSession = ($codeT['api'] != 'db');

        if ($goodForSession && isset($_SESSION['cts'][$name])) {
            return $_SESSION['cts'][$name];
        }

        try {
            if (! is_null($codeT)) {
                $ctd = $this->getByID($codeT['codetracker_id']);
                $cname = $ctd['implementation'];

                if ($goodForSession) {
                    $_SESSION['cts'][$name] = new $cname($cname, $ctd['cfg'],
                        $ctd['name']);
                } else {
                    return new $cname($cname, $ctd['cfg'], $ctd['name']);
                }
            } else {
                $_SESSION['cts'][$name] = null;
            }
            return $_SESSION['cts'][$name];
        } catch (Exception $e) {
            echo 'Probably there is some PHP Config issue regarding extension<b>';
            echo $e->getMessage() . '<pre>' . $e->getTraceAsString() . '</pre>';
        }
    }

    /*
     *
     *
     */
    public function checkConnection($cts)
    {
        $xx = $this->getByID($cts);
        $class2create = $xx['implementation'];
        $cts = new $class2create($xx['type'], $xx['cfg'], $xx['name']);

        $op = $cts->isConnected();

        // because I've added simple cache on $_SESSION
        // IMHO is better to update cache after this check
        $_SESSION['cts'][$xx['name']] = $cts;

        return $op;
    }

    /**
     */
    private function checkXMLCfg($xmlString)
    {
        $signature = 'Source:' . __METHOD__;
        $op = array(
            'status_ok' => true,
            'msg' => ''
        );

        $xmlCfg = "<?xml version='1.0'?> " . trim($xmlString);
        libxml_use_internal_errors(true);
        try {
            $cfg = simplexml_load_string($xmlCfg);
            if (! $cfg) {
                $op['status_ok'] = false;
                $op['msg'] = $signature . " - Failure loading XML STRING\n";
                foreach (libxml_get_errors() as $error) {
                    $op['msg'] .= "\t" . $error->message;
                }
            }
        } catch (Exception $e) {
            $op['status_ok'] = false;
            $op['msg'] = $signature . " - Exception loading XML STRING\n" .
                'Message: ' . $e->getMessage();
        }

        return $op;
    }
}
