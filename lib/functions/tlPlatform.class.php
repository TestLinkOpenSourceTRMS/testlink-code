<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later.
 *
 * @filesource  tlPlatform.class.php
 * @package     TestLink
 * @author      Erik Eloff
 * @copyright   2006-2022, TestLink community
 * @link        http://www.testlink.org
 *
 */

/**
 * Class for handling platforms
 * @author Eloff
 **/
class tlPlatform extends tlObjectWithDB
{
  protected $tproject_id;
  protected $stdFields;

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
  public function __construct(&$db, $tproject_id = null) {
    parent::__construct($db);
    $this->tproject_id = $tproject_id;
    $this->stdFields = "id, name, notes, testproject_id,
                        enable_on_design,enable_on_execution,is_open";
  }

  /**
   * 
   * 
   */
  public function setTestProjectID($tproject_id) {
    $this->tproject_id = intval($tproject_id);  
  }


  /**
   * Creates a new platform.
   * @return tl::OK on success otherwise E_DBERROR;
   */
  public function create($platform) {

    $op = array('status' => self::E_DBERROR, 'id' => -1);
    $safeName = $this->throwIfEmptyName($platform->name);
    $alreadyExists = $this->getID($safeName);

    if ($alreadyExists) {
      $op = array('status' => self::E_NAMEALREADYEXISTS, 'id' => -1);
    } else {
      $sql = "INSERT INTO {$this->tables['platforms']} 
              (name, testproject_id, notes, 
               enable_on_design,enable_on_execution,is_open) 
              VALUES (" .
              "'" . $this->db->prepare_string($safeName) . "'" .
              "," . $this->tproject_id .
              ",'" . $this->db->prepare_string($platform->notes) . "'" .
              "," . ($platform->enable_on_design ? 1 : 0) . 
              "," . ($platform->enable_on_execution ? 1 : 0);

      if (property_exists($platform, 'is_open')) {
        $sql .= "," . ($platform->is_open ? 1 : 0);
      } else {
        $sql .= ",1";        
      }
      $sql .= ")";

      $result = $this->db->exec_query($sql);

      if( $result ) {
        $op['status'] = tl::OK;
        $op['id'] = $this->db->insert_id($this->tables['platforms']);
      } 
    }
    return $op;
  }

  /**
   * Gets info by ID
   *
   * @return array 
   */
  public function getByID($id,$opt=null) {
    $idSet = implode(',',(array)$id);
    $options = array('fields' => $this->stdFields,
                     'accessKey' => null);
    $options = array_merge($options,(array)$opt);
    
    $sql =  " SELECT {$options['fields']}
              FROM {$this->tables['platforms']} 
              WHERE id IN ($idSet) ";
    
    switch ($options['accessKey']) {
      case 'id':
      case 'name':
        $accessKey = $options['accessKey'];
      break;

      default:
        if (count((array)$id) == 1) {
          return $this->db->fetchFirstRow($sql);
        }
        $accessKey = 'id';
      break;
    }          
    return $this->db->fetchRowsIntoMap($sql,$accessKey);
  }


  /**
   *
   */
  public function getByName($name)
  {
    $val = trim($name);
    $sql =  " SELECT {$this->stdFields} 
              FROM {$this->tables['platforms']} 
              WHERE name = '" . 
              $this->db->prepare_string($val) . "'" .
            " AND testproject_id = " . intval($this->tproject_id);
    
    $ret = $this->db->fetchFirstRow($sql);
    return is_array($ret) ? $ret : null;        
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
  public function update($id, $name, $notes, $enable_on_design=null, $enable_on_execution=null, $is_open=1)
  {
    $safeName = $this->throwIfEmptyName($name);
    $sql = " UPDATE {$this->tables['platforms']} " .
           " SET name = '" . $this->db->prepare_string($name) . "' " .
           ", notes =  '". $this->db->prepare_string($notes) . "' ";

    /* Optional */       
    if (!is_null($enable_on_design)) {
      $sql .= ", enable_on_design =  " . ( (($enable_on_design > 0) || $enable_on_design) ? 1 : 0 ); 
    }       
    if (!is_null($enable_on_execution)) {
      $sql .= ", enable_on_execution =  " . ( (($enable_on_execution > 0) || $enable_on_execution) ? 1 : 0 );
    }

    $sql .= ", is_open =  ". ($is_open > 0 ? 1 : 0);
    /* ---------------------------- */   

    $sql .= " WHERE id = {$id}";
    
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
    if ( !is_null($id) ) {
      $idSet = (array)$id;
      foreach ($idSet as $platform_id) {
        $sql = 
            " INSERT INTO {$this->tables['testplan_platforms']} " .
            " (testplan_id, platform_id) " .
            " VALUES ($testplan_id, $platform_id)";
        $result = $this->db->exec_query($sql);
        if (!$result) {
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
    $sql = " SELECT id FROM {$this->tables['platforms']} 
             WHERE name = '" . $this->db->prepare_string($name) . "'" .
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
   */
  public function getAll($options = null) {
    $debugMsg = 'Class:' . __CLASS__ . ' - Method: ' . __FUNCTION__;
    $default = array('include_linked_count' => false,
                     'enable_on_design' => false,
                     'enable_on_execution' => true,
                     'is_open' => true);
    $options = array_merge($default, (array)$options);
    
    $tproject_filter = " WHERE PLAT.testproject_id = {$this->tproject_id} ";

    $filterEnableOn = "";
    $enaSet = array('enable_on_design','enable_on_execution','is_open');
    foreach ($enaSet as $ena) {
      if (null == $options[$ena]) {
        continue;
      }
      if (is_bool($options[$ena]) || is_int($options[$ena])) {
        $filterEnableOn .= " AND $ena = " . ($options[$ena] ? 1 : 0);
      }                  
    }
    
    $sql =  " SELECT {$this->stdFields} 
              FROM {$this->tables['platforms']} PLAT 
              {$tproject_filter} {$filterEnableOn}
              ORDER BY name";

    $rs = $this->db->get_recordset($sql);
    if (!is_null($rs) && $options['include_linked_count']) {
      // At least on MS SQL Server 2005 you can not do GROUP BY 
      // fields of type TEXT
      // notes is a TEXT field
      // $sql =  " SELECT PLAT.id,PLAT.name,PLAT.notes, " .
      //     " COUNT(TPLAT.testplan_id) AS linked_count " .
      //     " FROM {$this->tables['platforms']} PLAT " .
      //     " LEFT JOIN {$this->tables['testplan_platforms']} TPLAT " .
      //     " ON TPLAT.platform_id = PLAT.id " . $tproject_filter .
      //     " GROUP BY PLAT.id, PLAT.name, PLAT.notes";
      //
      $sql =  " SELECT PLAT.id, COUNT(TPLAT.testplan_id) AS linked_count 
                FROM {$this->tables['platforms']} PLAT
                LEFT JOIN {$this->tables['testplan_platforms']} TPLAT 
                ON TPLAT.platform_id = PLAT.id {$tproject_filter}
                GROUP BY PLAT.id ";
      $figures = $this->db->fetchRowsIntoMap($sql,'id');   
      
      $loop2do = count($rs);
      for ($idx=0; $idx < $loop2do; $idx++) {
        $rs[$idx]['linked_count'] = 
          $figures[$rs[$idx]['id']]['linked_count'];        
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
  public function getAllAsMap($opt=null)
  {
    $options = array('accessKey' => 'id',
                     'output' => 'columns',
                     'orderBy' => ' ORDER BY name ',
                     'enable_on_design' => true,
                     'enable_on_execution' => true,
                     'is_open' => true);

    $options = array_merge($options,(array)$opt);
    $accessKey = $options['accessKey'];
    $output = $options['output'];
    $orderBy = $options['orderBy'];

    $filterEnableOn = "";
    $enaSet = [
      'enable_on_design',
      'enable_on_execution',
      'is_open'
    ];
    
    foreach ($enaSet as $ena) {
      if (null == $options[$ena]) {
        continue;
      }
      if (is_bool($options[$ena]) || is_int($options[$ena])) {
        $filterEnableOn .= " AND $ena = " . ($options[$ena] ? 1 : 0);
      }                  
    }

    
    $debugMsg = 'Class:' . __CLASS__ . ' - Method: ' . __FUNCTION__;
    $sql =  "/* $debugMsg */  
             SELECT {$this->stdFields}
             FROM {$this->tables['platforms']} 
             WHERE testproject_id = {$this->tproject_id} 
             {$filterEnableOn}
             {$orderBy}";
    if( $output == 'columns' ) {
      $rs = $this->db->fetchColumnsIntoMap($sql, $accessKey, 'name');
    } else {
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
    $my['options'] = array('outputFormat' => 'array', 
                           'orderBy' => ' ORDER BY name ',
                           'active' => -1);
    $my['options'] = array_merge($my['options'], (array)$options);
    
    $debugMsg = 'Class:' . __CLASS__ . ' - Method: ' . __FUNCTION__;
    $rs = null;

    if ( intval($my['options']['active']) < 0) {
      $active = '';
    } else {
      $active = ' AND TP.active = ' . 
                ($my['options']['active'] ? 1 : 0);
    }

    
    $sql = "/* $debugMsg */ 
            SELECT P.id, P.name, P.notes,
                   P.enable_on_design,
                   P.enable_on_execution,
                   P.is_open
            FROM {$this->tables['platforms']} P 
            JOIN {$this->tables['testplan_platforms']} TP 
            ON P.id = TP.platform_id 
            WHERE  TP.testplan_id = {$testplanID} {$active}
                   {$my['options']['orderBy']}";
    
    switch ($my['options']['outputFormat']) {
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
   * @return array Returns all platforms associated 
   *               to a given testplan
   *         output format: $id => $name
   */
  public function getLinkedToTestplanAsMap($testplanID,$opt=null)
  {
    // null -> any
    $options = array('orderBy' => ' ORDER BY name ',
                     'enable_on_design' => null,
                     'enable_on_execution' => true);

    $options = array_merge($options,(array)$opt);

    $orderBy = $options['orderBy'];

    $filterEnableOn = "";
    $enaSet = array('enable_on_design','enable_on_execution');
    foreach ($enaSet as $ena) {
      if ($options[$ena] == null) {
        // do not filter
        continue;
      }
      
      if (is_bool($options[$ena]) || is_int($options[$ena])) {
        $filterEnableOn .= " AND $ena = " . ($options[$ena] ? 1 : 0);
      }                  
    }

    $debugMsg = 'Class:' . __CLASS__ . ' - Method: ' . __FUNCTION__;
    $sql =  "/* $debugMsg */ SELECT P.id, P.name, P.is_open " .
            " FROM {$this->tables['platforms']} P " .
            " JOIN {$this->tables['testplan_platforms']} TP " .
            " ON P.id = TP.platform_id " .
            " WHERE  TP.testplan_id = {$testplanID} 
              {$filterEnableOn} {$orderBy}";

    $pset = (array)$this->db->fetchRowsIntoMap($sql, 'id');
    $itemSet = [];
    foreach($pset as $pid => $elem) {
      $pname = $elem['name'];
      if ($elem['is_open'] == 0) {
        $pname = "**closed for exec** " . $pname;
      }
      $itemSet[$pid] = $pname; 
    }
    return $itemSet;
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


  /**
   *
   * @internal revisions
   * @since 1.9.4
   */
  public function testProjectCount($opt=null)
  {
    $debugMsg = '/* Class:' . __CLASS__ . ' - Method: ' . __FUNCTION__ . '*/ ';
    $my['opt'] = array('range' => 'tproject');
    $my['opt'] = array_merge($my['opt'],(array)$opt);
    
    
    // HINT: COALESCE(COUNT(PLAT.id),0)
    //       allows to get 0 on platform_qty
    //
    $sql = $debugMsg . " SELECT COALESCE(COUNT(PLAT.id),0) AS platform_qty, TPROJ.id AS tproject_id " .
           " FROM {$this->tables['testprojects']} TPROJ " .
           " LEFT OUTER JOIN {$this->tables['platforms']} PLAT ON PLAT.testproject_id = TPROJ.id ";
    
    switch($my['opt']['range'])
    {
      case 'tproject':
        $sql .= " WHERE TPROJ.id = " . $this->tproject_id ;
      break;
    }
    $sql .= " GROUP BY TPROJ.id ";
    return ($this->db->fetchRowsIntoMap($sql,'tproject_id'));        
  }

  public function belongsToTestProject($id,$tproject_id = null)
  {
    $debugMsg = '/* Class:' . __CLASS__ . ' - Method: ' . __FUNCTION__ . '*/ ';
    $pid = intval(is_null($tproject_id) ? $this->tproject_id : $tproject_id);
    
    $sql = " SELECT id FROM {$this->tables['platforms']} " .
           " WHERE id = " . intval($id) . " AND testproject_id=" . $pid;
    $dummy =  $this->db->fetchRowsIntoMap($sql,'id');
    return isset($dummy['id']);
  }  

  public function isLinkedToTestplan($id,$testplan_id)
  {
    $debugMsg = 'Class:' . __CLASS__ . ' - Method: ' . __FUNCTION__;
    $sql = " SELECT platform_id FROM {$this->tables['testplan_platforms']} " .
           " WHERE testplan_id = " . intval($testplan_id) .
           " AND platform_id = " . intval($id);
    $rs = $this->db->fetchRowsIntoMap($sql,'platform_id');
    return !is_null($rs);
  }

  /**
   *
   */
  function initViewGUI(&$userObj,$context) {
    list($add2args,$gaga) = initUserEnv($this->db,$context);

    $gaga->activeMenu['projects'] = 'active';
    
    $gaga->tproject_id = $this->tproject_id;
    // NEED TO CHECK $gaga->tplan_id = $argsObj->tplan_id;

  
    
    $cfg = getWebEditorCfg('platform');
    $gaga->editorType = $cfg['type'];
    $gaga->user_feedback = null;
    $gaga->user_feedback = array('type' => 'INFO', 'message' => '');

    $opx = array('include_linked_count' => true,
                 'enable_on_design' => null, 
                 'enable_on_execution' => null,
                 'is_open' => null);
    $gaga->platforms = $this->getAll($opx);

    $rx = array('canManage' => 'platform_management', 
                'mgt_view_events' => 'mgt_view_events');
    foreach($rx as $prop => $right) {
      // 'yes' or null
      $gaga->$prop = $userObj->hasRight($this->db->db,$right,
                                        $this->tproject_id);
    }

    return $gaga;
  }

 /**
   * @return array Returns all platforms associated to a given testplan
   *               on the form $platform_id => $platform_name
   */
  public function getActiveLinkedToTestplanAsMap($testplanID,$opt=null){
    $debugMsg = 'Class:' . __CLASS__ . ' - Method: ' . __FUNCTION__;

    $options = array('orderBy' => ' ORDER BY name ');
    $options = array_merge($options,(array)$opt);
    $orderBy = $options['orderBy'];

    $sql =  "/* $debugMsg */ SELECT P.id, P.name
             FROM {$this->tables['platforms']} P 
             JOIN {$this->tables['testplan_platforms']} TP
             ON P.id = TP.platform_id 
             WHERE  TP.testplan_id = {$testplanID} 
             AND TP.active = 1 {$orderBy}";
    return $this->db->fetchColumnsIntoMap($sql, 'id', 'name');
  }

  
  /**
   *
   */
  public function toggleActive($tplan_id,$platformSet)
  {
    $debugMsg = 'Class:' . __CLASS__ . ' - Method: ' . __FUNCTION__;
    if (count($platformSet)>0) {
      $safeID = intval($tplan_id);
      $inClause = implode(',',$platformSet);
      $sql = " /* $debugMsg */
               UPDATE {$this->tables['testplan_platforms']} 
               SET active = NOT active
               WHERE testplan_id = $safeID
               AND platform_id IN ($inClause)";

      $rs = $this->db->exec_query($sql);
    }
  }

  
  /**
   *
   */
  function enableDesign($id) 
  {
    $sql = "UPDATE {$this->tables['platforms']} 
            SET enable_on_design = 1
            WHERE id = $id";
    $this->db->exec_query($sql);   
  }

  /**
   *
   */
  function disableDesign($id) 
  {
    $sql = "UPDATE {$this->tables['platforms']}
            SET enable_on_design = 0
            WHERE id = $id";
    $this->db->exec_query($sql);
  }


  /**
   *
   */
  function enableExec($id) 
  {
    $sql = "UPDATE {$this->tables['platforms']}
            SET enable_on_execution = 1
            WHERE id = $id";
    $this->db->exec_query($sql);
  }

  /**
   *
   */
  function disableExec($id) 
  {
    $sql = "UPDATE {$this->tables['platforms']}
            SET enable_on_execution = 0
            WHERE id = $id";
    $this->db->exec_query($sql);
  }


  /**
   *
   */
  function openForExec($id) 
  {
    $sql = "UPDATE {$this->tables['platforms']}
            SET is_open = 1
            WHERE id = $id";
    $this->db->exec_query($sql);
  }

  /**
   *
   */
  function closeForExec($id) 
  {
    $sql = "UPDATE {$this->tables['platforms']}
            SET is_open = 0
            WHERE id = $id";
    $this->db->exec_query($sql);
  }


    function getAsXMLString($tproject_id)
  {
    $debugMsg = 'Class:' . __CLASS__ . ' - Method: ' . __FUNCTION__;
    $tables = tlObjectWithDB::getDBTables(array('platforms'));
    $adodbXML = new ADODB_XML("1.0", "UTF-8");

    $sql = "/* $debugMsg */ 
            SELECT name,notes,enable_on_design,
            enable_on_execution 
            FROM {$tables['platforms']} PLAT 
            WHERE PLAT.testproject_id=" . intval($tproject_id);
    
    $adodbXML->setRootTagName('platforms');
    $adodbXML->setRowTagName('platform');
    $content = $adodbXML->ConvertToXMLString($db->db, $sql);
    downloadContentsToFile($content,$filename);
    exit();
  }


}
