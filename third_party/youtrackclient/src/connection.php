<?php
namespace YouTrack;
/**
 * A class for connecting to a youtrack instance.
 *
 * @internal revision
 * 20120318 - francisco.mancardi@gmail.com
 * new method get_global_issue_states()
 * Important Notice
 * REST API documentation for version 3.x this method is not documented.
 * REST API documentation for version 2.x this method is DOCUMENTED.
 * (http://confluence.jetbrains.net/display/YTD2/Get+Issue+States)
 *
 * new method get_state_bundle()
 *
 * @author Jens Jahnke <jan0sch@gmx.net>
 * Created at: 29.03.11 16:13
 */
class Connection {
  private $http = NULL;
  private $url = '';
  private $base_url = '';
  private $headers = array();
  private $cookies = array();
  private $debug_verbose = FALSE; // Set to TRUE to enable verbose logging of curl messages.
  private $user_agent = 'Mozilla/5.0'; // Use this as user agent string.
  private $verify_ssl = FALSE;

  public function __construct($url, $login, $password) {
    $this->http = curl_init();
    $this->url = $url;
    $this->base_url = $url . '/rest';
    $this->_login($login, $password);
  }

  /**
   * Loop through the given array and remove all entries
   * that have no value assigned.
   *
   * @param array &$params The array to inspect and clean up.
   */
  private function _clean_url_parameters(&$params) {
    if (!empty($params) && is_array($params)) {
      foreach ($params as $key => $value) {
	if (empty($value)) {
	  unset($params["$key"]);
	}
      } // foreach
    }
  }

  protected function _login($login, $password) {
    curl_setopt($this->http, CURLOPT_POST, TRUE);
    curl_setopt($this->http, CURLOPT_HTTPHEADER, array('Content-Length: 0')); //FIXME This doesn't work if youtrack is running behind lighttpd! @see http://redmine.lighttpd.net/issues/1717
    curl_setopt($this->http, CURLOPT_URL, $this->base_url . '/user/login?login='. urlencode($login) .'&password='. urlencode($password));
    curl_setopt($this->http, CURLOPT_RETURNTRANSFER, TRUE);
    curl_setopt($this->http, CURLOPT_HEADER, TRUE);
    curl_setopt($this->http, CURLOPT_SSL_VERIFYPEER, $this->verify_ssl);
    curl_setopt($this->http, CURLOPT_USERAGENT, $this->user_agent);
    curl_setopt($this->http, CURLOPT_VERBOSE, $this->debug_verbose);
    $content = curl_exec($this->http);
    $response = curl_getinfo($this->http);
    if ((int) $response['http_code'] != 200) {
      throw new YouTrackException('/user/login', $response, $content);
    }
    $cookies = array();
    preg_match_all('/^Set-Cookie: (.*?)=(.*?)$/sm', $content, $cookies, PREG_SET_ORDER);
    foreach($cookies as $cookie) {
      $parts = parse_url($cookie[0]);
      $this->cookies[] = $parts['path'];
    }
    $this->headers[CURLOPT_HTTPHEADER] = array('Cache-Control: no-cache');
    curl_close($this->http);
  }

  /**
   * Execute a request with the given parameters and return the response.
   *
   * @throws \Exception|YouTrackException An exception is thrown if an error occurs.
   * @param string $method The http method (GET, PUT, POST).
   * @param string $url The request url.
   * @param string $body Data that should be send or the filename of the file if PUT is used.
   * @param int $ignore_status Ignore the given http status code.
   * @return array An array holding the response content in 'content' and the response status
   * in 'response'.
   */
  protected function _request($method, $url, $body = NULL, $ignore_status = 0) {
    $this->http = curl_init($this->base_url . $url);
    $headers = $this->headers;
    if ($method == 'PUT' || $method == 'POST') {
      $headers[CURLOPT_HTTPHEADER][] = 'Content-Type: application/xml; charset=UTF-8';
      $headers[CURLOPT_HTTPHEADER][] = 'Content-Length: '. mb_strlen($body);
    }
    switch ($method) {
      case 'GET':
        curl_setopt($this->http, CURLOPT_HTTPGET, TRUE);
        break;
      case 'PUT':
        $handle = NULL;
        $size = 0;
        // Check if we got a file or just a string of data.
        if (file_exists($body)) {
          $size = filesize($body);
          if (!$size) {
            throw new \Exception("Can't open file $body!");
          }
          $handle = fopen($body, 'r');
        }
        else {
          $size = mb_strlen($body);
          $handle = fopen('data://text/plain,' . $body,'r');
        }
        curl_setopt($this->http, CURLOPT_PUT, TRUE);
        curl_setopt($this->http, CURLOPT_INFILE, $handle);
        curl_setopt($this->http, CURLOPT_INFILESIZE, $size);
        break;
      case 'POST':
        curl_setopt($this->http, CURLOPT_POST, TRUE);
        if (!empty($body)) {
          curl_setopt($this->http, CURLOPT_POSTFIELDS, $body);
        }
        break;
      default:
        throw new \Exception("Unknown method $method!");
    }
    curl_setopt($this->http, CURLOPT_HTTPHEADER, $headers[CURLOPT_HTTPHEADER]);
    curl_setopt($this->http, CURLOPT_USERAGENT, $this->user_agent);
    curl_setopt($this->http, CURLOPT_RETURNTRANSFER, TRUE);
    curl_setopt($this->http, CURLOPT_SSL_VERIFYPEER, $this->verify_ssl);
    curl_setopt($this->http, CURLOPT_VERBOSE, $this->debug_verbose);
    curl_setopt($this->http, CURLOPT_COOKIE, implode(';', $this->cookies));
    $content = curl_exec($this->http);
    $response = curl_getinfo($this->http);
    curl_close($this->http);
    if ((int) $response['http_code'] != 200 && (int) $response['http_code'] != 201 && (int) $response['http_code'] != $ignore_status) {
      throw new YouTrackException($url, $response, $content);
    }

    return array(
      'content' => $content,
      'response' => $response,
    );
  }

  protected function _request_xml($method, $url, $body = NULL, $ignore_status = 0) {
    $r = $this->_request($method, $url, $body, $ignore_status);
    $response = $r['response'];
    $content = $r['content'];
    if (!empty($response['content_type'])) {
      if (preg_match('/application\/xml/', $response['content_type']) || preg_match('/text\/xml/', $response['content_type'])) {
        return simplexml_load_string($content);
      }
    }
    return $content;
  }

  protected function _get($url) {
    return $this->_request_xml('GET', $url);
  }

  protected function _put($url) {
    return $this->_request_xml('PUT', $url, '<empty/>\n\n');
  }

  public function get_issue($id) {
    $issue = $this->_get('/issue/' . $id);
    return new Issue($issue);
  }

  public function create_issue($project, $assignee, $summary, $description, $priority, $type, $subsystem, $state, $affectsVersion, $fixedVersion, $fixedInBuild) {
    $params = array(
      'project' => (string)$project,
      'assignee' => (string)$assignee,
      'summary' => (string)$summary,
      'description' => (string)$description,
      'priority' => (string)$priority,
      'type' => (string)$type,
      'subsystem' => (string)$subsystem,
      'state' => (string)$state,
      'affectsVersion' => (string)$affectsVersion,
      'fixedVersion' => (string)$fixedVersion,
      'fixedInBuild' => (string)$fixedInBuild,
    );
    $issue = $this->_request_xml('POST', '/issue?'. http_build_query($params));
    return new Issue($issue);
  }

  public function get_comments($id) {
    $comments = array();
    $req = $this->_request('GET', '/issue/'. urlencode($id) .'/comment');
    $xml = simplexml_load_string($req['content']);
    foreach($xml->children() as $node) {
      $comments[] = new Comment($node);
    }
    return $comments;
  }

  public function get_attachments($id) {
    $attachments = array();
    $req = $this->_request('GET', '/issue/'. urlencode($id) .'/attachment');
    $xml = simplexml_load_string($req['content']);
    foreach($xml->children() as $node) {
      $attachments[] = new Comment($node);
    }
    return $attachments;
  }

  public function get_attachment_content($url) {
    //TODO Switch to curl for better error handling.
    $file = file_get_contents($url);
    if ($file === FALSE) {
      throw new \Exception("An error occured while trying to retrieve the following file: $url");
    }
    return $file;
  }

  public function create_attachment_from_attachment($issue_id, Attachment $attachment) {
    throw new NotImplementedException("create_attachment_from_attachment(issue_id, attachment)");
  }

  public function create_attachment($issue_id, $name, $content, $author_login = '', $content_type = NULL, $content_length = NULL, $created = NULL, $group = '') {
    throw new NotImplementedException("create_attachment(issue_id, name, content, ...)");
  }

  public function get_links($id , $outward_only = FALSE) {
    $links = array();
    $req = $this->_request('GET', '/issue/'. urlencode($id) .'/link');
    $xml = simplexml_load_string($req['content']);
    foreach($xml->children() as $node) {
      if (($node->attributes()->source != $id) || !$outward_only) {
        $links[] = new Link($node);
      }
    }
    return $links;
  }

  public function get_user($login) {
    return new User($this->_get('/admin/user/'. urlencode($login)));
  }

  public function create_user($user) {
    $this->import_users(array($user));
  }

  public function create_user_detailed($login, $full_name, $email, $jabber) {
    $this->import_users(array(array('login' => $login, 'fullName' => $full_name, 'email' => $email, 'jabber' => $jabber)));
  }

  public function import_users($users) {
    if (count($users) <= 0) {
      return;
    }
    $xml = "<list>\n";
    foreach ($users as $user) {
      $xml .= "  <user";
      foreach ($user as $key => $value) {
        $xml .= " $key=". urlencode($value);
      }
      $xml .= " />\n";
    }
    $xml .= "</list>";
    return $this->_request_xml('PUT', '/import/users', $xml, 400);
  }

  public function import_issues_xml($project_id, $assignee_group, $xml) {
    throw new NotImplementedException("import_issues_xml(project_id, assignee_group, xml)");
  }

  public function import_links($links) {
    throw new NotImplementedException("import_links(links)");
  }

  public function import_issues($project_id, $assignee_group, $issues) {
    throw new NotImplementedException("import_issues(project_id, assignee_group, issues)");
  }

  public function get_project($project_id) {
    return new Project($this->_get('/admin/project/'. urlencode($project_id)));
  }

  public function get_project_assignee_groups($project_id) {
    $xml = $this->_get('/admin/project/'. urlencode($project_id) .'/assignee/group');
    $groups = array();
    foreach ($xml->children() as $group) {
      $groups[] = new Group(new \SimpleXMLElement($group->asXML()));
    }
    return $groups;
  }

  public function get_group($name) {
    return new Group($this->_get('/admin/group/'. urlencode($name)));
  }

  public function get_user_groups($login) {
    $xml = $this->_get('/admin/user/'. urlencode($login) .'/group');
    $groups = array();
    foreach ($xml->children() as $group) {
      $groups[] = new Group(new \SimpleXMLElement($group->asXML()));
    }
    return $groups;
  }

  public function set_user_group($login, $group_name) {
    $r = $this->_request('POST', '/admin/user/'. urlencode($login) .'/group/'. urlencode($group_name));
    return $r['response'];
  }

  public function create_group(Group $group) {
    $r = $this->_put('/admin/group/'. urlencode($group->name) .'?description=noDescription&autoJoin=false');
    return $r['response'];
  }

  public function get_role($name) {
    return new Role($this->_get('/admin/role/'. urlencode($name)));
  }

  public function get_subsystem($project_id, $name) {
    return new Subsystem($this->_get('/admin/project/'. urlencode($project_id) .'/subsystem/'. urlencode($name)));
  }

  public function get_subsystems($project_id) {
    $xml = $this->_get('/admin/project/'. urlencode($project_id) .'/subsystem');
    $subsystems = array();
    foreach ($xml->children() as $subsystem) {
      $subsystems[] = new Subsystem(new \SimpleXMLElement($subsystem->asXML()));
    }
    return $subsystems;
  }

  public function get_versions($project_id) {
    $xml = $this->_get('/admin/project/'. urlencode($project_id) .'/version?showReleased=true');
    $versions = array();
    foreach ($xml->children() as $version) {
      $versions[] = new Version(new \SimpleXMLElement($version->asXML()));
    }
    return $versions;
  }

  public function get_version($project_id, $name) {
    return new Version($this->_get('/admin/project/'. urlencode($project_id) .'/version/'. urlencode($name)));
  }

  public function get_builds($project_id) {
    $xml = $this->_get('/admin/project/'. urlencode($project_id) .'/build');
    $builds = array();
    foreach ($xml->children() as $build) {
      $builds[] = new Build(new \SimpleXMLElement($build->asXML()));
    }
    return $builds;
  }

  public function get_users($q = '') {
    $users = array();
    $q = trim((string)$q);
    $params = array(
      'q' => $q,
    );
    $this->_clean_url_parameters($params);
    $xml = $this->_get('/admin/user/?'. http_build_query($params));
    if (!empty($xml) && is_object($xml)) {
      foreach ($xml->children() as $user) {
        $users[] = new User(new \SimpleXMLElement($user->asXML()));
      }
    }
    return $users;
  }

  public function create_build() {
    throw new NotImplementedException("create_build()");
  }

  public function create_builds() {
    throw new NotImplementedException("create_builds()");
  }

  public function create_project($project) {
    return $this->create_project_detailed($project->id, $project->name, $project->description, $project->leader);
  }

  public function create_project_detailed($project_id, $project_name, $project_description, $project_lead_login, $starting_number = 1) {
    $params = array(
      'projectName' => (string)$project_name,
      'description' => (string)$project_description,
      'projectLeadLogin' => (string)$project_lead_login,
      'lead' => (string)$project_lead_login,
      'startingNumber' => (string)$starting_number,
    );
    return $this->_put('/admin/project/'. urlencode($project_id) .'?'. http_build_query($params));
  }

  public function create_subsystems($project_id, $subsystems) {
    foreach ($subsystems as $subsystem) {
      $this->create_subsystem($project_id, $subsystem);
    }
  }

  public function create_subsystem($project_id, $subsystem) {
    return $this->create_subsystem_detailed($project_id, $subsystem->name, $subsystem->isDefault, $subsystem->defaultAssignee);
  }

  public function create_subsystem_detailed($project_id, $name, $is_default, $default_assignee_login) {
    $params = array(
      'isDefault' => (string)$is_default,
      'defaultAssignee' => (string)$default_assignee_login,
    );
    $this->_put('/admin/project/'. urlencode($project_id). '/subsystem/'. urlencode($name) .'?'. http_build_query($params));
    return 'Created';
  }

  public function delete_subsystem($project_id, $name) {
    return $this->_request_xml('DELETE', '/admin/project/'. urlencode($project_id) .'/subsystem/'. urlencode($name));
  }

  public function create_versions($project_id, $versions) {
    foreach ($versions as $version) {
      $this->create_version($project_id, $version);
    }
  }

  public function create_version($project_id, $version) {
    return $this->create_version_detailed($project_id, $version->name, $version->isReleased, $version->isArchived, $version->releaseDate, $version->description);
  }

  public function create_version_detailed($project_id, $name, $is_released, $is_archived, $release_date = NULL, $description = '') {
    $params = array(
      'description' => (string)$description,
      'isReleased' => (string)$is_released,
      'isArchived' => (string)$is_archived,
    );
    if (!empty($release_date)) {
      $params['releaseDate'] = $release_date;
    }
    return $this->_put('/admin/project/'. urldecode($project_id) .'/version/'. urlencode($name) .'?'. http_build_query($params));
  }

  public function get_issues($project_id, $filter, $after, $max) {
    $params = array(
      'after' => (string)$after,
      'max' => (string)$max,
      'filter' => (string)$filter,
    );
    $this->_clean_url_parameters($params);
    $xml = $this->_get('/project/issues/'. urldecode($project_id) .'?'. http_build_query($params));
    $issues = array();
    foreach ($xml->children() as $issue) {
      $issues[] = new Issue(new \SimpleXMLElement($issue->asXML()));
    }
    return $issues;
  }

  public function execute_command($issue_id, $command, $comment = NULL, $group = NULL) {
    $params = array(
      'command' => (string)$command,
    );
    if (!empty($comment)) {
      $params['comment'] = (string)$comment;
    }
    if (!empty($group)) {
      $params['group'] = (string)$group;
    }
    $r = $this->_request('POST', '/issue/'. urlencode($issue_id) .'/execute?'. http_build_query($params));
    return 'Command executed';
  }

  public function get_custom_field($name) {
    return new CustomField($this->_get('/admin/customfield/field/'. urlencode($name)));
  }

  public function get_custom_fields() {
    $xml = $this->_get('/admin/customfield/field');
    $fields = array();
    foreach ($xml->children() as $field) {
      $fields[] = new CustomField(new \SimpleXMLElement($field->asXML()));
    }
    return $fields;
  }

  public function create_custom_fields($fields) {
    foreach ($fields as $field) {
      $this->create_custom_field($field);
    }
  }

  public function create_custom_field($field) {
    return $this->create_custom_field_detailed($field->name, $field->type, $field->isPrivate, $field->visibleByDefault);
  }

  public function create_custom_field_detailed($name, $type_name, $is_private, $default_visibility) {
    $params = array(
      'typeName' => (string)$type_name,
      'isPrivate' => (string)$is_private,
      'defaultVisibility' => (string)$default_visibility,
    );
    $this->_put('/admin/customfield/field/'. urlencode($name) .'?'. http_build_query($params));
    return 'Created';
  }

  public function get_enum_bundle($name) {
    return new EnumBundle($this->_get('/admin/customfield/bundle/'. urlencode($name)));
  }

  public function create_enum_bundle(EnumBundle $bundle) {
    return $this->_request_xml('PUT', '/admin/customfield/bundle', $bundle->toXML(), 400);
  }

  public function delete_enum_bundle($name) {
    $r = $this->_request('DELETE', '/admin/customfield/bundle/'. urlencode($name), '');
    return $r['content'];
  }

  public function add_value_to_enum_bundle($name, $value) {
    return $this->_put('/admin/customfield/bundle/'. urlencode($name) .'/'. urlencode($value));
  }

  public function add_values_to_enum_bundle($name, $values) {
    foreach ($values as $value) {
      $this->add_value_to_enum_bundle($name, $value);
    }
    return implode(', ', $values);
  }

  public function get_project_custom_field($project_id, $name) {
    return new CustomField($this->_get('/admin/project/'. urlencode($project_id) .'/customfield/'. urlencode($name)));
  }

  public function get_project_custom_fields($project_id) {
    $xml = $this->_get('/admin/project/'. urlencode($project_id) .'/customfield');
    $fields = array();
    foreach ($xml->children() as $cfield) {
      $fields[] = new CustomField(new \SimpleXMLElement($cfield->asXML()));
    }
    return $fields;
  }

  public function create_project_custom_field($project_id, CustomField $pcf) {
    return $this->create_project_custom_field_detailed($project_id, $pcf->name, $pcf->emptyText, $pcf->params);
  }

  private function create_project_custom_field_detailed($project_id, $name, $empty_field_text, $params = array()) {
    $_params = array(
      'emptyFieldText' => (string)$empty_field_text,
    );
    if (!empty($params)) {
      $_params = array_merge($_params, $params);
    }
    return $this->_put('/admin/project/'. urlencode($project_id) .'/customfield/'. urlencode($name) .'?'. http_build_query($_params));
  }

  public function get_issue_link_types() {
    $xml = $this->_get('/admin/issueLinkType');
    $lts = array();
    foreach ($xml->children() as $node) {
      $lts[] = new IssueLinkType(new \SimpleXMLElement($node->asXML()));
    }
    return $lts;
  }

  public function create_issue_link_types($lts) {
    foreach ($lts as $lt) {
      $this->create_issue_link_type($lt);
    }
  }

  public function create_issue_link_type($ilt) {
    return $this->create_issue_link_type_detailed($ilt->name, $ilt->outwardName, $ilt->inwardName, $ilt->directed);
  }

  public function create_issue_link_type_detailed($name, $outward_name, $inward_name, $directed) {
    $params = array(
      'outwardName' => (string)$outward_name,
      'inwardName' => (string)$inward_name,
      'directed' => (string)$directed,
    );
    return $this->_put('/admin/issueLinkType/'. urlencode($name) .'?'. http_build_query($params));
  }

  public function get_verify_ssl() {
    return $this->verify_ssl;
  }

  /**
   * Use this method to enable or disable the ssl_verifypeer option of curl.
   * This is usefull if you use self-signed ssl certificates.
   *
   * @param bool $verify_ssl
   * @return void
   */
  public function set_verify_ssl($verify_ssl) {
    $this->verify_ssl = $verify_ssl;
  }

  /**
   * get pairs (state,revolved attribute) in hash.
   * same info is get online on: 
   * Project Fields › States (Click to change bundle name) 
   * 
   * @return hash key: state string 
   *              value: true is resolved attribute set to true	
   */
  public function get_global_issue_states() {
    $xml = $this->_get('/project/states');
	$states = null;
    foreach($xml->children() as $node) {
      $states[(string)$node['name']] = ((string)$node['resolved'] == 'true');
    }
    return $states;
  }

  /**
   * useful when you have configured different states for different projects
   * in this cases you will create bundles with name with global scope,
   * i.e. name can not be repeated on youtrack installation.
   *
   * @param string $name
   * @return hash key: state string
   *			  value: hash('description' => string, 'isResolved' => boolean) 
   */
  public function get_state_bundle($name) {

	$cmd = '/admin/customfield/stateBundle/' . urlencode($name);
    $xml = $this->_get($cmd);
	$bundle = null;
    foreach($xml->children() as $node) {
       $bundle[(string)$node] = array('description' => (isset($node['description']) ? (string)$node['description'] : ''),
      								 'isResolved' => ((string)$node['isResolved']=='true'));
    }
    return $bundle;
  }

}