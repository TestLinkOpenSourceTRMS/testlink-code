<?php
/**
* tuleap PHP API
*
* Bare bones implementation, just to cover TestLink needs
*
* @internal revisions
* @since 1.9.14
 */
class tuleap
{
  /**
   * Url to api, http:[yoursite].xxxx.com
   * @var string
   */
  public $url = '';

  /**
   * @var string
   */
  public $username = '';

  /**
   * @var string
   */
  public $password = '';

  /**
   * Curl interface with FugB specific settings
   * @var string
   */
  public $curl = '';


  /**
   * @var int
   */
  public $userId;

  /**
   * @var string
   */
  public $token = '';

  /**
   * Constructor
   *
   *
   * @return void
   */
  public function __construct($url, $username, $password)
  {
      // if the values are not empty, we'll assign them to our matching properties
      $args = array('url', 'username', 'password');
      foreach ($args as $arg)
      {
          if (!empty($$arg))
          {
              $this->$arg = $$arg;
          }
      }

  }


  /**
   *
   *
   */
  public function initCurl()
  {
    $agent = "TestLink 1.9.14";
    try
    {
      $this->curl = curl_init();
    }
    catch (Exception $e)
    {
      var_dump($e);
    }

    // set the agent, forwarding, and turn off ssl checking
    // Timeout in Seconds
    curl_setopt_array($this->curl,array(CURLOPT_USERAGENT => $agent,
                                        CURLOPT_VERBOSE => 0,
                                        CURLOPT_FOLLOWLOCATION => TRUE,
                                        CURLOPT_RETURNTRANSFER => TRUE,
                                        CURLOPT_AUTOREFERER => TRUE,
                                        CURLOPT_TIMEOUT => 60,
                                        CURLOPT_SSL_VERIFYPEER => FALSE

    ));
  }

  public function Connect(){
      try {
          $response = $this->_postJson($this->url."/tokens", json_encode(array( "username" =>  $this->username, "password" => $this->password) ));

          if(is_object($response)){
              $this->token = $response->token;
              $this->userId =  $response->user_id;

              return isset($response->user_id);

          }else{
            return false;
           }

      }catch (\InvalidArgumentException $e) {
          if(gettype($this->curl) == 'resource') curl_close($this->curl);
          throw $e;
      } catch (\Exception $e) {
          if(gettype($this->curl) == 'resource') curl_close($this->curl);
          throw $e;
      }
  }

  /**
   *
   *
   */
  function getArtifactById($id)
  {
    $item = $this->_getJson($this->url."/artifacts/{$id}");
    $ret = is_object($item) ? $item : null;
    return $ret;
  }


  /**
   * @param string $id Tracker ID
   * @return unknown Tracker definition
   */
  public function getTrackerById($id)
  {
    $item = $this->_getJson($this->url."/trackers/{$id}");
    $ret = is_object($item) ? $item : null;
    return $ret;
  }

  protected function _getJson($url)
  {
    try {
        $this->initCurl();
        $header = array("X-Auth-Token: {$this->token}", "X-Auth-UserId: {$this->userId}");

        curl_setopt($this->curl, CURLOPT_URL, $url);
        curl_setopt($this->curl, CURLOPT_DNS_USE_GLOBAL_CACHE, false );
        curl_setopt($this->curl, CURLOPT_DNS_CACHE_TIMEOUT, 2 );

        curl_setopt($this->curl, CURLOPT_HEADER, 0);
        curl_setopt($this->curl, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
        curl_setopt($this->curl, CURLOPT_HTTPHEADER, $header);
        curl_setopt($this->curl, CURLOPT_HTTPGET, TRUE);

        $responseBody = curl_exec($this->curl);
        $responseInfo = curl_getinfo($this->curl);
        curl_close($this->curl);

        return json_decode($responseBody);

    }catch (\InvalidArgumentException $e) {
        if(gettype($this->curl) == 'resource') curl_close($this->curl);
        throw $e;
    } catch (\Exception $e) {
        if(gettype($this->curl) == 'resource') curl_close($this->curl);
        throw $e;
    }

  }

  protected function _putJson($url, $data){
      try {

          $this->initCurl();
          curl_setopt($this->curl, CURLOPT_HTTPHEADER, array("X-Auth-Token: {$this->token}", "X-Auth-UserId: {$this->userId}", "Accept: application/json", "Content-Type: application/json", "Content-length: " . mb_strlen($data) ));
          curl_setopt($this->curl, CURLOPT_URL, $url);
          curl_setopt($this->curl, CURLOPT_DNS_USE_GLOBAL_CACHE, false );
          curl_setopt($this->curl, CURLOPT_DNS_CACHE_TIMEOUT, 2 );
          curl_setopt($this->curl, CURLOPT_HEADER, 0);
          curl_setopt($this->curl, CURLOPT_POST, true);
          curl_setopt($this->curl, CURLOPT_CUSTOMREQUEST, "PUT");
          curl_setopt($this->curl, CURLOPT_POSTFIELDS, $data);

          $content = curl_exec($this->curl);
          $response = curl_getinfo($this->curl);
          $curlError =  curl_error($this->curl);
          $httpCode = (int)$response['http_code'];
          curl_close($this->curl);

          if ($httpCode != 200 && $httpCode != 201 )
          {
              throw new exception(__METHOD__ . "url:$url - response:" .
                  json_encode($response) . ' - content: ' . json_encode($content) );
          }

          $rr = array('content' => $content,'response' => $response,'curlError' => $curlError);
          return $rr;

      }catch (\InvalidArgumentException $e) {
          if(gettype($this->curl) == 'resource') curl_close($this->curl);
          throw $e;
      } catch (\Exception $e) {
          if(gettype($this->curl) == 'resource') curl_close($this->curl);
          throw $e;
      }
  }

  public function addTrackerArtifactMessage($id, $noteText){
      $data = array();
      // values is required (but may be empty)
      $data['values'] = array();
      $data['comment'] = array("body"=>$noteText, "format"=>"text");

      $items = $this->_putJson($this->url."/artifacts/{$id}", json_encode($data));
      return $items;
  }


  public function createIssue($id, $summary, $description){
      $values_by_field = array();
      if($summary!= null && $summary!=""){
          $values_by_field['summary'] = array("value"=>$summary,"type"=>"string");
      }

      if($description!= null && $description!=""){
          $values_by_field['details'] = array("value"=>$description, "type"=>"text");
      }
      $data = array();
      $data["tracker"] = array("id"=>$id);
      $data["values_by_field"] = $values_by_field;

      $item = $this->_postJson($this->url."/artifacts", json_encode($data));

      $ret = is_object($item) ? $item : null;
      return $ret;
  }

  protected function _postJson($url, $data){
      try{
          $this->initCurl();
          $header = array();
          if( trim($this->token) != '')
          {
              $header[] = "X-Auth-Token: {$this->token}";
              $header[] = "X-Auth-UserId: {$this->userId}";
          }
          $header[] = "Content-Type: application/json";
          $header[] = "Accept: application/json";
          $header[] = "Content-length: " . mb_strlen($data);

          curl_setopt($this->curl, CURLOPT_URL, $url);
          curl_setopt($this->curl, CURLOPT_HTTPHEADER, $header);
          curl_setopt($this->curl, CURLOPT_POST, true);

          if (!empty($data)){
            curl_setopt($this->curl, CURLOPT_POSTFIELDS, $data);
          }

          $content = curl_exec($this->curl);
          $response = curl_getinfo($this->curl);
          $httpCode = (int)$response['http_code'];

          curl_close($this->curl);

          if ($httpCode != 200 && $httpCode != 201 )
          {
              throw new exception(__METHOD__ . "url:$url - response:" .
                  json_encode($response) . ' - content: ' . json_encode($content) );

              return null;
          }else{
              return json_decode($content);
          }

      }catch (\InvalidArgumentException $e) {
          if(gettype($this->curl) == 'resource') curl_close($this->curl);
          throw $e;
      } catch (\Exception $e) {
          if(gettype($this->curl) == 'resource') curl_close($this->curl);
          throw $e;
      }


  }
} // Class end
?>
