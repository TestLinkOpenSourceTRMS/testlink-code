<?php
namespace YouTrack;
require("connection.php");
/**
 * This file holds all youtrack related classes regarding data types.
 *
 * @author Jens Jahnke <jan0sch@gmx.net>
 * Created at: 29.03.11 16:29
 */

/**
 * A simple exception that should be raised if a function is not yet implemented.
 */
class NotImplementedException extends \Exception {
  /**
   * Constructor
   *
   * @param string $function_name The name of the function.
   */
  public function __construct($function_name) {
    $code = 0;
    $previous = NULL;
    $message = 'This function is not yet implemented: "'. $function_name .'"!';
    parent::__construct($message, $code, $previous);
  }
}

/**
 * A class extending the standard php exception.
 */
class YouTrackException extends \Exception {
  /**
   * Constructor
   *
   * @param string $url The url that triggered the error.
   * @param array $response The output of <code>curl_getinfo($resource)</code>.
   * @param array $content The content returned from the url.
   */
  public function __construct($url, $response, $content) {
    $code = (int)$response['http_code'];
    $previous = NULL;
    $message = "Error for '" . $url . "': " . $response['http_code'];
    if (!empty($response['content_type']) && !preg_match('/text\/html/', $response['content_type'])) {
      $xml = simplexml_load_string($content);
      $error = new YouTrackError($xml);
      $message .= ": " . $error->__get("error");
    }
    parent::__construct($message, $code, $previous);
  }
}

/**
 * A class describing a youtrack object.
 */
class YouTrackObject {
  protected $youtrack = NULL;
  protected $attributes = array();

  public function __construct(\SimpleXMLElement $xml = NULL, Connection $youtrack = NULL) {
    $this->youtrack = $youtrack;
    if (!empty($xml)) {
      if (!($xml instanceof \SimpleXMLElement)) {
        throw new \Exception("An instance of SimpleXMLElement expected!");
      }
      $this->_update_attributes($xml);
      $this->_update_children_attributes($xml);
    }
  }

  public function __get($name) {
    if (!empty($this->attributes["$name"])) {
      return $this->attributes["$name"];
    }
    return NULL;
  }

  public function __set($name, $value) {
    $this->attributes["$name"] = $value;
  }

  protected function _update_attributes(\SimpleXMLElement $xml) {
    foreach ($xml->xpath('/*') as $node) {
      foreach ($node->attributes() as $key => $value) {
        $this->attributes["$key"] = (string)$value;
      }
    }
  }

  protected function _update_children_attributes(\SimpleXMLElement $xml) {
    foreach ($xml->children() as $node) {
      foreach ($node->attributes() as $key => $value) {
        if ($key == 'name') {
          $this->__set($value, (string)$node->value);
        }
        else {
          $this->__set($key, (string)$value);
        }
      }
    }
  }
}

/**
 * A class describing a youtrack error.
 */
class YouTrackError extends YouTrackObject {
  public function __construct(\SimpleXMLElement $xml = NULL, Connection $youtrack = NULL) {
    parent::__construct($xml, $youtrack);
  }

  protected function _update_attributes(\SimpleXMLElement $xml) {
    foreach ($xml->xpath('/error') as $node) {
      $this->attributes['error'] = (string) $node;
    }
  }
}

/**
 * A class describing a youtrack issue.
 */
class Issue extends YouTrackObject {
  private $links = array();
  private $attachments = array();
  private $comments = array();

  public function __construct(\SimpleXMLElement $xml = NULL, Connection $youtrack = NULL) {
    parent::__construct($xml, $youtrack);
    if ($xml) {
      if (!empty($this->attributes['links'])) {
        $links = array();
        foreach($xml->xpath('//field[@name="links"]') as $node) {
          foreach($node->children() as $link) {
            $links[(string)$link] = array(
              'type' => (string)$link->attributes()->type,
              'role' => (string)$link->attributes()->role,
            );
          }
        }
        $this->__set('links', $links);
      }
      if (!empty($this->attributes['attachments'])) {
        $attachments = array();
        foreach($xml->xpath('//field[@name="attachments"]') as $node) {
          foreach($node->children() as $attachment) {
            $attachments[(string)$attachment] = array(
              'url' => (string)$attachment->attributes()->url,
            );
          }
        }
        $this->__set('attachments', $attachments);
      }
    }
  }

  public function get_reporter() {
    return $this->youtrack->get_user($this->__get('reporterName'));
  }

  public function has_assignee() {
    $name = $this->__get('assigneeName');
    return !empty($name);
  }

  public function get_assignee() {
    return $this->youtrack->get_user($this->__get('assigneeName'));
  }

  public function get_updater() {
    return $this->youtrack->get_user($this->__get('updaterName'));
  }

  public function get_comments() {
    if (empty($this->comments)) {
      $this->comments = $this->youtrack->get_comments($this->__get('id'));
    }
    return $this->comments;
  }

  public function get_attachments() {
    if (empty($this->attachments)) {
      $this->attachments = $this->youtrack->get_attachments($this->__get('id'));
    }
    return $this->attachments;
  }

  public function get_links() {
    if (empty($this->links)) {
      $this->links = $this->youtrack->get_links($this->__get('id'));
    }
    return $this->links;
  }
}

/**
 * A class describing a youtrack comment.
 */
class Comment extends YouTrackObject {
  public function __construct(\SimpleXMLElement $xml = NULL, Connection $youtrack = NULL) {
    parent::__construct($xml, $youtrack);
  }

  public function get_author() {
    return $this->youtrack->get_user($this->__get('author'));
  }
}

/**
 * A class describing a youtrack link.
 */
class Link extends YouTrackObject {
  public function __construct(\SimpleXMLElement $xml = NULL, Connection $youtrack = NULL) {
    parent::__construct($xml, $youtrack);
  }
}

/**
 * A class describing a youtrack attachment.
 */
class Attachment extends YouTrackObject {
  public function __construct(\SimpleXMLElement $xml = NULL, Connection $youtrack = NULL) {
    parent::__construct($xml, $youtrack);
  }

  public function get_content() {
    return $this->youtrack->get_attachment_content($this->__get('url'));
  }
}

/**
 * A class describing a youtrack user.
 * @todo Add methods for hashing and comparison.
 */
class User extends YouTrackObject {
  public function __construct(\SimpleXMLElement $xml = NULL, Connection $youtrack = NULL) {
    parent::__construct($xml, $youtrack);
  }
}

/**
 * A class describing a youtrack group.
 */
class Group extends YouTrackObject {
  public function __construct(\SimpleXMLElement $xml = NULL, Connection $youtrack = NULL) {
    parent::__construct($xml, $youtrack);
  }
}

/**
 * A class describing a youtrack role.
 */
class Role extends YouTrackObject {
  public function __construct(\SimpleXMLElement $xml = NULL, Connection $youtrack = NULL) {
    parent::__construct($xml, $youtrack);
  }
}

/**
 * A class describing a youtrack project.
 */
class Project extends YouTrackObject {
  public function __construct(\SimpleXMLElement $xml = NULL, Connection $youtrack = NULL) {
    parent::__construct($xml, $youtrack);
  }

  public function get_subsystems() {
    return $this->youtrack->get_subsystems($this->id);
  }

  public function create_subsystem($name, $is_default, $default_assignee_login) {
    return $this->youtrack->create_subsystem($this->id, $name, $is_default, $default_assignee_login);
  }
}

/**
 * A class describing a youtrack subsystem.
 */
class Subsystem extends YouTrackObject {
  public function __construct(\SimpleXMLElement $xml = NULL, Connection $youtrack = NULL) {
    parent::__construct($xml, $youtrack);
  }
}

/**
 * A class describing a youtrack version.
 */
class Version extends YouTrackObject {
  public function __construct(\SimpleXMLElement $xml = NULL, Connection $youtrack = NULL) {
    parent::__construct($xml, $youtrack);
    $check = $this->__get('description');
    if (empty($check)) {
      $this->__set('description', '');
    }
    $check = $this->__get('releaseDate');
    if (empty($check)) {
      $this->__set('releaseDate', NULL);
    }
  }
}

/**
 * A class describing a youtrack build.
 */
class Build extends YouTrackObject {
  public function __construct(\SimpleXMLElement $xml = NULL, Connection $youtrack = NULL) {
    parent::__construct($xml, $youtrack);
  }
}

/**
 * A class describing a youtrack issue link type.
 */
class IssueLinkType extends YouTrackObject {
  public function __construct(\SimpleXMLElement $xml = NULL, Connection $youtrack = NULL) {
    parent::__construct($xml, $youtrack);
  }
}

/**
 * A class describing a youtrack custom field.
 */
class CustomField extends YouTrackObject {
  public function __construct(\SimpleXMLElement $xml = NULL, Connection $youtrack = NULL) {
    parent::__construct($xml, $youtrack);
  }
}

/**
 * A class describing a youtrack project custom field.
 */
class ProjectCustomField extends YouTrackObject {
  public function __construct(\SimpleXMLElement $xml = NULL, Connection $youtrack = NULL) {
    parent::__construct($xml, $youtrack);
  }

  protected function _update_children_attributes(\SimpleXMLElement $xml) {
    throw new NotImplementedException("_update_children_attributes(xml)");
  }
}

class EnumBundle extends YouTrackObject {
  private $name = '';
  private $values = array();

  public function __construct(\SimpleXMLElement $xml = NULL, Connection $youtrack = NULL) {
    parent::__construct($xml, $youtrack);
  }

  protected function _update_attributes(\SimpleXMLElement $xml) {
    $this->name = (string)$xml->attributes()->name;
  }

  protected function _update_children_attributes(\SimpleXMLElement $xml) {
    foreach ($xml->children() as $node) {
      $this->values[] = (string)$node;
    }
  }

  public function toXML() {
    $xml = '<enumeration name="'. $this->name .'">';
    foreach ($this->values as $v) {
      $xml .= '<value>'. $v .'</value>';
    }
    $xml .= '</enumeration>';

    return $xml;
  }
}
