<?php
/** 
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later. 
 *
 * @filesource  tlIssueTracker.php
 * @package     TestLink
 * @author      franciscom
 * @copyright   2012,2013 TestLink community
 * @link        http://testlink.sourceforge.net/
 *
 * @internal revisions
 * @since 1.9.10
 *
**/

/**
 * 
 * @package   TestLink
 */
class tlIssueTracker extends tlObject
{
    
  /** @var resource the database handler */
  var $db;

  var $types = null;

  // IMPORTANT NOTICE
  // array index is used AS CODE that will be written to DB
  // if you need to add a new item start on 200, to avoid crash with standard ID
  //  
  var $systems = array( 1 =>  array('type' => 'bugzilla', 'api' => 'xmlrpc', 'enabled' => true, 'order' => -1),
                        2 =>  array('type' => 'bugzilla', 'api' => 'db', 'enabled' => true, 'order' => -1),
                        3 =>  array('type' => 'mantis', 'api' =>'soap', 'enabled' => true, 'order' => -1),
                        4 =>  array('type' => 'mantis', 'api' =>'db', 'enabled' => true, 'order' => -1),
                        5 =>  array('type' => 'jira', 'api' =>'soap', 'enabled' => true, 'order' => -1),
                        6 =>  array('type' => 'jira', 'api' =>'db', 'enabled' => true, 'order' => -1),
                        7 =>  array('type' => 'jira', 'api' =>'rest', 'enabled' => true, 'order' => -1),
                        8 =>  array('type' => 'fogbugz','api' =>'rest','enabled' => true, 'order' => -1),
                        9 =>  array('type' => 'fogbugz','api' =>'db','enabled' => true, 'order' => -1),
                       10 =>  array('type' => 'gforge','api' =>'soap','enabled' => false, 'order' => -1),
                       11 =>  array('type' => 'gforge','api' =>'db','enabled' => false, 'order' => -1),
                       12 =>  array('type' => 'eventum','api' =>'db', 'enabled' => false, 'order' => -1),
                       13 =>  array('type' => 'polarion', 'api' =>'soap', 'enabled' => false, 'order' => -1),
                       14 =>  array('type' => 'youtrack','api' =>'rest','enabled' => true, 'order' => -1),
                       15 =>  array('type' => 'redmine','api' =>'rest','enabled' => true, 'order' => -1),
                       16 =>  array('type' => 'redmine','api' =>'db','enabled' => false, 'order' => -1),
                       17 =>  array('type' => 'seapine','api' =>'soap','enabled' => false, 'order' => -1),
                       18 =>  array('type' => 'seapine','api' =>'db','enabled' => false, 'order' => -1),
                       19 =>  array('type' => 'trac','api' =>'xmlrpc','enabled' => true, 'order' => -1),
                       20 =>  array('type' => 'trackplus','api' =>'soap','enabled' => false, 'order' => -1),
                       21 =>  array('type' => 'trackplus','api' =>'db','enabled' => false, 'order' => -1),
                       22 =>  array('type' => 'gitlab','api' =>'rest','enabled' => true, 'order' => -1)
                     );
  
    
  var $entitySpec = array('name' => 'string','cfg' => 'string','type' => 'int');
    
  /**
   * Class constructor
   * 
   * @param resource &$db reference to the database handler
   */
  function __construct(&$db)
  {
    parent::__construct();

    // populate types property
    $this->getTypes();
    $this->db = &$db;
  }



    /**
   * @return hash
   * 
   * 
     */
  function getSystems($opt=null)
  {
    $my = array('options' => null);
    $my['options']['status'] = 'enabled'; // enabled,disabled,all
    $my['options'] = array_merge($my['options'],(array)$opt);
        
    switch($my['options']['status']) 
    {
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
    foreach($this->systems as $code => $elem)
    {
      $idx = 0;
      if($tval== null || $elem['enabled'] == $tval)
      {
        $ret[$code] = $elem;
      }
    }
      return $ret;
    }

  /**
   * @return hash
   * 
   * 
   */
  function getTypes()
  {
    if( is_null($this->types) )
    {
      foreach($this->systems as $code => $spec)
      {
        if($spec['enabled'])
        {  
          $this->types[$code] = $spec['type'] . " (Interface: {$spec['api']})";
        }  
      }
    }
    return $this->types;
  }


  /**
   * @return 
   * 
   * 
   */
  function getImplementationForType($issueTrackerType)
  {
    $spec = $this->systems[$issueTrackerType];
    return $spec['type'] . $spec['api'] . 'Interface';
  }

    /**
   * @return hash 
   * 
   * 
     */
  function getEntitySpec()
  {
        return $this->entitySpec;
  }


  /**
   *
   */
  function create($it)
  {
    $debugMsg = 'Class:' . __CLASS__ . ' - Method: ' . __FUNCTION__;
    $ret = array('status_ok' => 0, 'id' => 0, 'msg' => 'name already exists');

    // Critic we need to do this before sanitize, because $it is changed
    $xlmCfg = trim($it->cfg); 

    // allow empty config
    if(strlen($xlmCfg) > 0)
    {  
      $ret = $this->checkXMLCfg($xlmCfg);
      if(!$ret['status_ok'])
      {  
        return $ret;  // >>>---> Bye!
      }  
    }


    $safeobj = $this->sanitize($it);  
    // empty name is not allowed
    if( is_null($safeobj->name) )
    {
      $ret['msg'] = 'empty name is not allowed';
      return $ret;  // >>>---> Bye!
    }

    // need to check if name already exist
    if( is_null($this->getByName($it->name,array('output' => 'id')) ))
    {
      $sql =  "/* debugMsg */ INSERT  INTO {$this->tables['issuetrackers']} " .
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
    $msg['duplicate_name'] = "Update can not be done - name %s already exists for id %s";
    $msg['ok'] = "operation OK for id %s";

    // Critic we need to do this before sanitize, because $it is changed
    $xlmCfg = trim($it->cfg); 

    $safeobj = $this->sanitize($it);
    $ret = array('status_ok' => 1, 'id' => $it->id, 'msg' => '');

    // allow empty config
    if(strlen($xlmCfg) > 0)
    {  
      $ret = $this->checkXMLCfg($xlmCfg);
    }

    // check for duplicate name
    if( $ret['status_ok'] )   
    {
      $info = $this->getByName($safeobj->name);
      if( !is_null($info) && ($info['id'] != $it->id) )
      {
        $ret['status_ok'] = 0;
        $ret['msg'] .= sprintf($msg['duplicate_name'], $safeobj->name, $info['id']);
      }
    }

    if( $ret['status_ok'] )   
    {
      $sql =  "UPDATE {$this->tables['issuetrackers']}  " .
              " SET name = '" . $safeobj->name. "'," . 
              "     cfg = '" . $safeobj->cfg . "'," .
              "     type = " . $safeobj->type . 
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
      $sql =  " /* $debugMsg */ DELETE FROM {$this->tables['issuetrackers']}  " .
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
      default:
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
    if( !is_null($rs) )
    {
      $rs = $rs[0];
      $rs['implementation'] = $this->getImplementationForType($rs['type']);
    }
      return $rs; 
  }



  /*
   * Sanitize and do minor checks
   *
   * Sanitize Operations
   * keys name  -> trim will be applied
     *      type  -> intval() wil be applied
     *      cfg   
     *
     *    For strings also db_prepare_string() will be applied
     *
     *
     * Check Operations
   * keys name  -> if '' => will be set to NULL
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
      $value = trim($obj->$key);
      switch($key)
      {
        case 'name':    
          $sobj->$key = ($value == '') ? null : $value;
        break;  
      }
      
      if( !is_null($sobj->$key) )
      {
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
  function link($id,$tprojectID)
  {
    $debugMsg = 'Class:' . __CLASS__ . ' - Method: ' . __FUNCTION__;

    if(is_null($id))
    {
      return;
        }
        
        // Check if link exist for test project ID, in order to INSERT or UPDATE
        $statusQuo  = $this->getLinkedTo($tprojectID);
        
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
  function getLinks($id, $opt=null)
  {
    $debugMsg = 'Class:' . __CLASS__ . ' - Method: ' . __FUNCTION__;

    $my = array('opt' => array('getDeadLinks' => false));
    $my['opt'] = array_merge($my['opt'], (array)$opt);
    
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
    
    if($my['opt']['getDeadLinks'])
    {
      $sql .= ' AND NHTPR.id IS NULL AND NHTPR.name IS NULL ';  
    }
         
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
  function getAll($options=null)
  {
    $debugMsg = 'Class:' . __CLASS__ . ' - Method: ' . __FUNCTION__;
    $my['options'] = array('output' => null, 'orderByField' => 'name', 'checkEnv' => false);
    $my['options'] = array_merge($my['options'], (array)$options);

    $add_fields = '';
    if( $my['options']['output'] == 'add_link_count' )
    {
      $add_fields = ", 0 AS link_count ";
    }

    $orderByClause = is_null($my['options']['orderByField']) ? '' : 'ORDER BY ' . $my['options']['orderByField']; 
    
    $sql = "/* debugMsg */ SELECT * {$add_fields} ";
    $sql .= " FROM {$this->tables['issuetrackers']} {$orderByClause} ";
    $rs = $this->db->fetchRowsIntoMap($sql,'id');

    $lc = null;
    if( !is_null($rs) )
    {
    
      if( $my['options']['output'] == 'add_link_count' )
      {
        $sql = "/* debugMsg */ SELECT COUNT(0) AS lcount, ITD.id";
        $sql .= " FROM {$this->tables['issuetrackers']} ITD " .
                " JOIN {$this->tables['testproject_issuetracker']} " .
                " ON issuetracker_id = ITD.id " .
                " GROUP BY ITD.id ";
        $lc = $this->db->fetchRowsIntoMap($sql,'id');
      }
    
      
      foreach($rs as &$item)
      {
        $item['verbose'] = $item['name'] . " ( {$this->types[$item['type']]} )" ;
        $item['type_descr'] = $this->types[$item['type']];
        $item['env_check_ok'] = true;
        $item['env_check_msg'] = '';
        $item['connection_status'] = '';
         
        if( $my['options']['checkEnv'] )
        {
           $impl = $this->getImplementationForType($item['type']);
           $dummy = $impl::checkEnv();
           $item['env_check_ok'] = $dummy['status'];
           $item['env_check_msg'] = $dummy['msg'];
        }

        
        if( !is_null($lc) )
        {
          if( isset($lc[$item['id']]) )
          {
            $item['link_count'] = intval($lc[$item['id']]['lcount']);
          } 
        }
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
    if( !is_null($ret) )
    { 
      $ret = $ret[0];
      $ret['verboseType'] = $this->types[$ret['type']];
      $spec = $this->systems[$ret['type']];
      $ret['api'] = $spec['api'];
    }
    
    return $ret;
  }


  /**
   *
   *
   */
  function getInterfaceObject($tprojectID)
  {
    $issueT = $this->getLinkedTo($tprojectID);
    $name = $issueT['issuetracker_name'];
    $goodForSession = ($issueT['api'] != 'db');

    if($goodForSession && isset($_SESSION['its'][$name]))
    {
      return $_SESSION['its'][$name]; 
    }  

    try
    {
      if( !is_null($issueT)  )
      {
        $itd = $this->getByID($issueT['issuetracker_id']);
        $iname = $itd['implementation'];

        if($goodForSession)
        {
          $_SESSION['its'][$name] = new $iname($iname,$itd['cfg'],$itd['name']);
        }
        else
        {
          $ixx = new $iname($iname,$itd['cfg'],$itd['name']);
          return $ixx;
        }  
      }
      else
      {
        $_SESSION['its'][$name] = null;
      }
      return $_SESSION['its'][$name];
    }
    catch (Exception $e)
    {
      echo('Probably there is some PHP Config issue regarding extension<b>');
      echo($e->getMessage().'<pre>'.$e->getTraceAsString().'</pre>');   
    }
  }

  /*
   *
   *
   */
  function checkConnection($its)
  {
    $xx = $this->getByID($its);
    $class2create = $xx['implementation'];
    $its = new $class2create($xx['type'],$xx['cfg'],$xx['name']);

    $op = $its->isConnected();
    
    // because I've added simple cache on $_SESSION
    // IMHO is better to update cache after this check
    $_SESSION['its'][$xx['name']] = $its;

    return $op;
  }

  /**
   *
   */
  function checkXMLCfg($xmlString)
  {
    $signature = 'Source:' . __METHOD__;
    $op = array('status_ok' => true, 'msg' => '');

    $xmlCfg = "<?xml version='1.0'?> " . trim($xmlString);
    libxml_use_internal_errors(true);
    try 
    {
      $cfg = simplexml_load_string($xmlCfg);
      if (!$cfg) 
      {
        $op['status_ok'] = false;
        $op['msg'] = $signature . " - Failure loading XML STRING\n";
        foreach(libxml_get_errors() as $error) 
        {
          $op['msg'] .= "\t" . $error->message;
        }
      }
    }
    catch(Exception $e)
    {
      $op['status_ok'] = false;
      $op['msg'] = $signature . " - Exception loading XML STRING\n" . 'Message: ' .$e->getMessage();
    }

    return $op;
  }  

} // end class