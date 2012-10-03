<?php
namespace YouTrack;
require_once("requirements.php");
/**
 * Unit test for the youtrack issue class.
 *
 * @author Jens Jahnke <jan0sch@gmx.net>
 * Created at: 08.04.11 13:55
 */
class YouTrackIssueTest extends \PHPUnit_Framework_TestCase {
  private $filename = "test/issue.xml";

  public function test___construct01() {
    $xml = simplexml_load_file($this->filename);
    $issue = new Issue($xml);
    $this->assertEquals(3, count($issue->__get('links')));
  }

  public function test___construct02() {
    $xml = simplexml_load_file($this->filename);
    $issue = new Issue($xml);
    $this->assertEquals(3, count($issue->__get('attachments')));
  }

  public function test_has_assignee() {
    $xml = simplexml_load_file($this->filename);
    $issue = new Issue($xml);
    $this->assertTrue($issue->has_assignee());
  }
}
