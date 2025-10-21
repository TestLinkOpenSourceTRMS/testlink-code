<?php

/**
 * bare bones REST PHP API
 *
 * Bare bones implementation, to be reused
 * Copied and adpated from work on YouTrack API interface
 * by Jens Jahnke <jan0sch@gmx.net>
 *
 * @author  Francisco Mancardi <vinoron@yandex.ru>
 *
 */

/**
 */
class bareBonesRestAPI
{

    public $api;

    public $url;

    /**
     *
     * @var string Some systems i.e. trello need both
     */
    public $apikey = '';

    public $apitoken = '';

    /**
     * Curl interface with specific settings
     *
     * @var string
     */
    public $curl = '';

    /**
     * Curl Header
     * changes according the system
     *
     * @var []
     */
    public $curlHeader = [];

    /**
     * properties
     * host
     * port
     * login
     * password
     */
    public $proxy;

    public $cfg;

    /**
     * Constructor
     *
     *
     * @return void
     */
    public function __construct()
    {}

    /**
     */
    public function initCurl($cfg = null)
    {
        $agent = "TestLink " . TL_VERSION_NUMBER;
        try {
            $this->curl = curl_init();
        } catch (Exception $e) {
            var_dump($e);
        }

        // set the agent, forwarding, and turn off ssl checking
        // Timeout in Seconds
        $curlCfg = [
            CURLOPT_USERAGENT => $agent,
            CURLOPT_VERBOSE => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_AUTOREFERER => true,
            CURLOPT_TIMEOUT => 60,
            CURLOPT_SSL_VERIFYPEER => false
        ];

        if (! is_null($this->proxy)) {
            $doProxyAuth = false;
            $curlCfg[CURLOPT_PROXYTYPE] = 'HTTP';

            foreach ($this->proxy as $prop => $value) {
                switch ($prop) {
                    case 'host':
                        $curlCfg[CURLOPT_PROXY] = $value;
                        break;

                    case 'port':
                        $curlCfg[CURLOPT_PROXYPORT] = $value;
                        break;

                    case 'login':
                    case 'password':
                        $doProxyAuth = true;
                        break;
                }
            }

            if ($doProxyAuth && ! is_null($this->proxy->login) &&
                ! is_null($this->proxy->password)) {
                $curlCfg[CURLOPT_PROXYUSERPWD] = $this->proxy->login . ':' .
                    $this->proxy->password;
            }
        }

        curl_setopt_array($this->curl, $curlCfg);
    }

    /**
     *
     * @internal notice
     *           copied and adpated from work on YouTrack API interface by Jens Jahnke <jan0sch@gmx.net>
     */
    protected function _get($cmd)
    {
        // GET must returns a JSON object ALWAYS
        return $this->_request_json('GET', $cmd);
    }

    /**
     * Use it when the API called will return
     * - response
     * - JSON content
     */
    protected function _postWithContent($cmd, $body = null)
    {
        return $this->_request_json('POST', $cmd, $body);
    }

    /**
     * Use it when the API called will return
     * - response
     */
    protected function _post($cmd, $body = null)
    {
        return $this->_request('POST', $cmd, $body);
    }

    /**
     *
     * @param unknown $method
     * @param unknown $cmd
     * @param unknown $body
     * @param number $ignore_status
     * @param unknown $reporter
     * @return mixed
     * @internal notice
     *           copied and adpated from work on YouTrack API interface by Jens Jahnke <jan0sch@gmx.net>
     */
    protected function _request_json($method, $cmd, $body = null,
        $ignore_status = 0, $reporter = null)
    {
        $r = $this->_request($method, $cmd, $body, $ignore_status, $reporter);
        $response = $r['response'];

        $content = json_decode($r['content']);
        if (json_last_error() == JSON_ERROR_NONE) {
            return $content;
        }

        // Oh no!!!
        $msg = 'Bad Response!!';
        if (null != $response && isset($response['http_code'])) {
            $msg = "http_code:" . $response['http_code'];
        }
        $msg = "Error Parsing JSON In TESTLINK -> " . $msg .
            " -> Give a look to TestLink Event Viewer";

        throw new Exception($msg, 1);
    }

    /**
     *
     * @param unknown $method
     * @param unknown $cmd
     * @param unknown $body
     * @param number $ignoreStatusCode
     * @param unknown $reporter
     * @return array
     *
     * @internal notice
     *           copied and adpated from work on YouTrack API interface by Jens Jahnke <jan0sch@gmx.net>
     */
    protected function _request($method, $cmd, $body = null,
        $ignoreStatusCode = 0, $reporter = null)
    {

        // this is the minimal test
        if (empty($this->apikey)) {
            throw new exception(__METHOD__ . " Can not work without apikey");
        }

        // this can happens because if I save object on _SESSION PHP is not able to
        // save resources.
        if (! is_resource($this->curl)) {
            $this->initCurl();
        }

        $additional = '';
        if (property_exists($this, 'api')) {
            $additional = trim($this->api);
        }
        $url = $this->url . $additional . $cmd;

        curl_setopt($this->curl, CURLOPT_URL, $url);
        curl_setopt($this->curl, CURLOPT_DNS_USE_GLOBAL_CACHE, false);
        curl_setopt($this->curl, CURLOPT_DNS_CACHE_TIMEOUT, 2);
        curl_setopt($this->curl, CURLOPT_HEADER, 0);

        if (! empty($this->curlHeader)) {
            curl_setopt($this->curl, CURLOPT_HTTPHEADER, $this->curlHeader);
        }

        switch ($method) {
            case 'GET':
                curl_setopt($this->curl, CURLOPT_HTTPGET, true);
                break;

            case 'POST':
            case 'PATCH':
                curl_setopt($this->curl, CURLOPT_POST, true);
                if (! empty($body)) {
                    curl_setopt($this->curl, CURLOPT_POSTFIELDS,
                        json_encode($body));
                }
                break;

            default:
                throw new exception("Unknown method {$method}!");
                break;
        }

        $content = curl_exec($this->curl);
        $response = curl_getinfo($this->curl);
        $curlError = curl_error($this->curl);
        $httpCode = (int) $response['http_code'];
        if ($httpCode != 200 && $httpCode != 201 &&
            $httpCode != $ignoreStatusCode) {
            throw new exception(
                __METHOD__ . "url:$this->url - response:" .
                json_encode($response) . ' - content: ' . json_encode($content));
        }

        return [
            'content' => $content,
            'response' => $response,
            'curlError' => $curlError
        ];
    }

    /**
     */
    public function __destruct()
    {}
}
