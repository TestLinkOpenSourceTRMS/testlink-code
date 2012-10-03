<?php
/**
 * Demonstration of FogBugz API
 *
 * Run this as:
 *   php sample.php --user username@example.com --pass password --url http://example.fogbugz.com
 *
 */
error_reporting(E_ALL | E_STRICT);
require_once __DIR__ . '/lib/api.php';

// collect the user and password from the command line
$options = (object) getopt('', array('user:', 'pass:', 'url:'));
if (empty($options->user) || empty($options->pass) || empty($options->url)) {
  exit(
    "This script needs a user, password and url " .
    "set via --user [user] --pass [password] --url [url]\n"
  );
}

  // init our fogbugz api with the user and pass from the command line,
  // and the url from the var above
  $fogbugz = new FogBugz(
      $options->user,
      $options->pass,
      $options->url
  );

// fogbugz will throw exceptions, so we catch them here
try {

  $fogbugz->logon();

  // You can call any FogBugz API method directly by using it's
  // name as a method name on the $fogbugz object.
  // It will turn the method name in to the command,
  // ?cmd={method_name} and it will add the array to the
  // get request automatically
  $xml = $fogbugz->listFilters();

  // this returns a SimpleXMLElement object, so 
  // remember to treat it as such
  print "Fogbugz filter list for current user:\n";
  foreach ($xml->filters->children() as $filter) {
    print sprintf(
        "[%s] %s\n",
        $filter['type'],
        (string) $filter
    );
  }
  
  // or perhaps and example with parameters,
  // note the array syntax there
  /*
  $fogbugz->startWork(array(
      'ixBug' => 1234
  ));
  //*/

}
catch (Exception $e) {
  print sprintf(
      "FogBugz Error : [Code %d] %s\n",
      $e->getCode(),
      $e->getMessage()
  );
  exit(1);
}


exit(0);