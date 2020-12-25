<?php
/**
 * Mantis PHP API
 *
 * Bare bones implementation, just to cover TestLink needs
 *
 *
 */

/**
 *
 */
class mantis extends bareBonesRestAPI 
{
  /**
   * Url to site, 
   * @var string 
   */
  public $url = '';
  
  /**
   * @var string 
   */
  public $apikey = '';
  
  /**
   * Curl interface with specific settings
   * @var string 
   */
  public $curl = '';

  public $proxy = null;
  
  /** 
   */
  public $summaryLengthLimit = 1024;
  public $cfg;  

  /**
   * Constructor
   * 
   * context: map
   *          url
   *          apikey
   *          project
   *          category
   *          priority
   *          severity
   *
   * cfg: map
   *      proxy map
   *      
   *
   * @return void
   */
  public function __construct($context,$cfg=null)  
  {

    // if the values are not empty, 
    // we'll assign them to our matching properties
    foreach ($context as $arg => $val) {
      if (!empty($val)) {
        $this->$arg = $val;
      }
    }
    
    if(!is_null($cfg)) {
      if(!is_null($cfg['proxy'])) {
        $this->proxy = (object)['port' => null, 'host' => null,
                                'login' => null, 'password' => null];
        foreach($cfg['proxy'] as $prop => $value) {
          if(isset($cfg['proxy']->$prop)) {
            $this->proxy->$prop = $value; 
          }  
        }  
      }  

      if (isset($cfg['cfg']) && !is_null($cfg['cfg'])) {
        $this->cfg = $cfg['cfg'];
      }  
    }  

    // declared in parent Class
    // @used-by parent::_request() & parent::_request_json()
    $this->curlHeader = [ "Authorization: {$this->apikey}",       
                          "Content-Type: application/json",
                          "Agent-Name: testlink",
                          "Agent-Version: " . TL_VERSION_NUMBER ];
    $this->initCurl();
  }


  /**
   * 
   *
   */
  function getIssue($issueID) 
  {
    try {
      $item = $this->_get("/api/rest/issues/{$issueID}");    
      $ret = is_object($item) ? $item : null;
      return $ret;
    }
    catch(Exception $e) {
      return null;
    }
  } 

  /**
   * 
   *
   */
  public function addIssue($title, $descr, $opt=null) 
  {

    // Limit title length
    $ellipsis = '...';
    $safeTitle = $title;
    $titleLen = strlen($title);
    if( $titleLen > $this->summaryLengthLimit ) {
      $safeTitle = $ellipsis . 
        substr($title, -1*($this->summaryLengthLimit + strlen($ellipsis)));
    }
    
    $body = ["summary" => $safeTitle,
              "description" => $descr,
              "project" => ["name" => $this->project],
              "category" => ["name" => $this->category],
              "priority" => ["name" => $this->priority],
              "severity" => ["name" => $this->severity]
             ];

    $cmd = '/api/rest/issues';
    $ret = $this->_postWithContent($cmd,$body);


    /*         
    {
    "summary": "Sample REST issue",
    "description": "Description for sample REST issue.",
    "additional_information": "More info about the issue",
    "project": {
        "id": 1,
        "name": "mantisbt"
    },
    "category": {
        "id": 5,
        "name": "bugtracker"
    },
    "handler": {
        "name": "vboctor"
    },
    "view_state": {
        "id": 10,
        "name": "public"
    },
    "priority": {
        "name": "normal"
    },
    "severity": {
        "name": "trivial"
    },
    "reproducibility": {
        "name": "always"
    },
    "sticky": false,
    "custom_fields": [
        {
            "field": {
                "id": 4,
                "name": "The City"
            },
            "value": "Seattle"
        }
    ],
    "tags": [
        {
            "name": "mantishub"
        }
    ]
}
'*/

  }

  /**
   * 
   *
   */
  public function addNote($issueID, $noteText) 
  {
  }
  
  /**
   * 
   *
   */
  function addExternalLinks($cardID, $links) 
  {
  }

  /**
   * 
   *
   */
  function addTags($cardID, $tags) 
  {
  }

  /**
   *
   */
  public function getMyUserInfo() {   
    $items = $this->_get("/api/rest/users/me");
    return $items;
  }                                                   

  
  /**
   * 
   */
  public function __destruct() 
  {
  }

}