<?php
namespace YouTrack;
require_once("requirements.php");
/**
 * A helper class for connection testing.
 */
class TestConnection extends Connection {
  protected function _login($login, $password) {
    //Do nothing here.
  }
}
/**
 * Unit test for the connection class.
 *
 * @author Jens Jahnke <jan0sch@gmx.net>
 * Created at: 31.03.11 12:35
 */
class ConnectionTest extends \PHPUnit_Framework_TestCase {
  private $url = "http://example.com";
  private $login = "guest";
  private $password = "guest";

  public function test_get_issue() {
    $con = new TestConnection($this->url, $this->login, $this->password);
  }
}
