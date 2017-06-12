<?php

namespace StashApi;

class RestRequest
{

    public $username;
    public $password;
    public $proxy;

    protected $url;
    protected $verb;
    protected $requestBody;
    protected $requestLength;
    protected $acceptType;
    protected $responseBody;
    protected $responseInfo;
    protected $ch;


    public function openConnect($url = null, $verb = 'GET', $requestBody = null, $filename = null)
    {
        $this->url               = $url;
        $this->verb              = $verb;
        $this->requestBody       = $requestBody;
        $this->requestLength     = 0;
        $this->acceptType        = 'application/json';
        $this->responseBody      = null;
        $this->responseInfo      = null;
        $this->filename          = $filename;
        $this->contentType       = 'Content-Type: application/json';

        if ($this->requestBody !== null || $this->filename !== null)
        {    
            $this->buildPostBody();
        }    
    }

    public function flush()
    {
        $this->requestBody       = null;
        $this->requestLength     = 0;
        $this->verb              = 'GET';
        $this->responseBody      = null;
        $this->responseInfo      = null;
    }

    public function execute($keepConnection=false)
    {
        if(is_null($this->ch))
        {
          $this->ch = curl_init();
          $this->setAuth();
        }

        try {
            switch (strtoupper($this->verb)) {
                case 'GET':
                    $this->executeGet();
                    break;
                case 'POST':
                    $this->executePost();
                    break;
                case 'PUT':
                    $this->executePut();
                    break;
                case 'DELETE':
                    $this->executeDelete();
                    break;
                default:
                    curl_close($this->ch);
                    $this->ch = null;
                    throw new \InvalidArgumentException('Current verb (' . $this->verb . ') is an invalid REST verb.');
            }
            if(!$keepConnection)
            {
              curl_close($this->ch);
              $this->ch = null;
            }
        } catch (\InvalidArgumentException $e) {
            curl_close($this->ch);
            $this->ch = null;
            throw $e;
        } catch (\Exception $e) {
            curl_close($this->ch);
            $this->ch = null;
            throw $e;
        }
    }

    public function buildPostBody($data = null)
    {
        if ($data == null) {
            if ($this->filename !== null) {
                $fileContents = file_get_contents($this->filename);
                $boundary = "----------------------------".substr(md5(rand(0,32000)), 0, 12);

                $data = "--".$boundary."\r\n";
                $data .= "Content-Disposition: form-data; name=\"file\"; filename=\"".basename($this->filename)."\"\r\n";
                $data .= "Content-Type: ".mime_content_type($this->filename)."\r\n";
                $data .= "\r\n";
                $data .= $fileContents."\r\n";
                $data .= "--".$boundary."--";

                $this->requestBody = $data;
                $this->contentType = 'Content-Type: multipart/form-data; boundary='.$boundary;
            }
            else
            {    
                $this->requestBody = json_encode($this->requestBody);
            }    
        }
        else
        {    
            $this->requestBody = json_encode($data);
        }    
    }

    public function getResponseBody()
    {
        return $this->responseBody;
    }

    public function getResponseInfo()
    {
        return $this->responseInfo;
    }

    public function lastRequestStatus()
    {
        $result = $this->getResponseInfo();

        if (isset($result['http_code']) && ($result['http_code'] >= 200 && $result['http_code'] < 300)) {
            return true;
        }

        return false;
    }

    protected function executeGet()
    {
        $this->doExecute();
    }

    protected function executePost()
    {
        curl_setopt($this->ch, CURLOPT_POST, true);
        curl_setopt($this->ch, CURLOPT_POSTFIELDS, $this->requestBody);

        $this->doExecute();
    }

    protected function executePut()
    {
        curl_setopt($this->ch, CURLOPT_POST, true);
        curl_setopt($this->ch, CURLOPT_CUSTOMREQUEST, "PUT");
        curl_setopt($this->ch, CURLOPT_POSTFIELDS, $this->requestBody);

        $this->doExecute();
    }

    protected function executeDelete()
    {
        curl_setopt($this->ch, CURLOPT_CUSTOMREQUEST, "DELETE");

        $this->doExecute();
    }

    protected function doExecute()
    {
        $this->setCurlOpts();
        $this->responseBody = curl_exec($this->ch);
        $this->responseInfo = curl_getinfo($this->ch);
    }

    protected function setCurlOpts()
    {
        curl_setopt($this->ch, CURLOPT_TIMEOUT, 60);
        curl_setopt($this->ch, CURLOPT_URL, $this->url);
        curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, true);
        //curl_setopt($ch, CURLOPT_HEADER, true); //displays header in output.
        curl_setopt(
            $this->ch,
            CURLOPT_HTTPHEADER,
            array(
                'Accept: ' . $this->acceptType,
                $this->contentType, 'X-Atlassian-Token: nocheck'
            )
        );
        curl_setopt($this->ch, CURLOPT_SSL_VERIFYPEER, false); // ignore self signed certs
        //curl_setopt($ch, CURLOPT_VERBOSE, true); // set to true for CURL debug output

        if( !is_null($this->proxy) && !is_null($this->proxy->host) )
        {
          $k2l = array('host' => CURLOPT_PROXY,
                       'port' => CURLOPT_PROXYPORT);
          foreach($k2l as $fi => $vx)
          {
            if(!is_null($this->proxy->$fi))
            {
              curl_setopt($this->ch, $vx, $this->proxy->$fi);
            }
          }  

          // authentication: 'login:password';
          if( !is_null($this->proxy->login) && 
              !is_null($this->proxy->password) )
          {
            $auth = $this->proxy->login . ':' . $this->proxy->password;
            curl_setopt($this->ch, CURLOPT_PROXYUSERPWD, $auth);
          }  
        }
    }

    protected function setAuth()
    {
        if ($this->username !== null && $this->password !== null) {
            curl_setopt($this->ch, CURLOPT_USERPWD, $this->username . ':' . $this->password);
        }
    }
}
