<?php 
/**
 * @filesource  RestApiCustomExample.class.php
 *
 *
 */
$bd = dirname(__FILE__);
$ds = DIRECTORY_SEPARATOR;
$dummy = explode($ds. lib . $ds, $bd);
require_once($dummy[0] . $ds . 'config.inc.php');
require_once('common.php');

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;

use Psr\Http\Message\ResponseInterface;
use Slim\Psr7\Response;

$ds = DIRECTORY_SEPARATOR;
$dummy = explode($ds. 'custom' . $ds, $bd);
require_once($dummy[0] . $ds . 'RestApi.class.php');


/**
 * @author    Francisco Mancardi <francisco.mancardi@gmail.com>
 * @package   TestLink 
 */
class RestApiCustomExample extends RestApi
{
  public static $version = "1.0";

  /**
   */
  public function __construct() 
  {
    $this->db = new database(DB_TYPE);
    $this->db->db->SetFetchMode(ADODB_FETCH_ASSOC);
    doDBConnect($this->db,database::ONERROREXIT);
  }  



  /**
   *
   */
  public function whoAmI(Request $request, Response $response, $args)
  {    
    $msg = json_encode(array('name' => __CLASS__ . ' : You have called Get Route /whoAmI'));
    $response->getBody()->write($msg);
    return $response;
  }
  
}
