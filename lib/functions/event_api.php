<?php

/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later.
 *
 * @filesource  event_api.php
 * @package     TestLink
 * @copyright   2015-2016, TestLink community
 * @link        http://www.testlink.org/
 *
 **/

require_once('events_inc.php');

/**
 * function event_declare
 * @param string $p_name Event name.
 * @param integer $p_type Event type.
 * @return void
 */
function event_declare($p_name, $p_type = EVENT_TYPE_DEFAULT) {
  global $g_event_cache;

  if( !isset( $g_event_cache[$p_name] ) ) {
    $g_event_cache[$p_name] = array(
      'type' => $p_type,
      'callbacks' => array()
    );
  }
}

/**
 * function event_declare_many
 * @param array $p_events Events.
 * @return void
 */
function event_declare_many(array $p_events) {
  foreach ($p_events as $t_name => $t_type) {
    event_declare($t_name, $t_type);
  }
}

/**
 * function event_hook
 * @param string $p_name Event name.
 * @param string $p_callback Callback function.
 * @param string $p_plugin Plugin basename.
 * @return void
 */
function event_hook($p_name, $p_callback, $p_plugin) {
  global $g_event_cache;
  if (!isset($g_event_cache[$p_name])) {
    // TODO: Handle error case.
    trigger_error('Error in event_hook: ' . $p_name, E_USER_NOTICE);
    return null;
  }
  $g_event_cache[$p_name]['callbacks'][$p_plugin][] = $p_callback;
}

/**
 * function event_hook_many
 * @param array $p_hooks Event name/callback pairs.
 * @param string $p_plugin Plugin basename.
 * @return void
 */
function event_hook_many(array $p_hooks, $p_plugin) {
  foreach ($p_hooks as $t_name => $t_callbacks) {
    if (!is_array($t_callbacks)) {
      event_hook($t_name, $t_callbacks, $p_plugin);
      continue;
    }

    foreach ($t_callbacks as $t_callback) {
      event_hook($t_name, $t_callback, $p_plugin);
    }
  }
}

/**
 * function event_clear_callbacks
 * @param void
 * @return void
 */
function event_clear_callbacks() {
  global $g_event_cache;
  foreach ($g_event_cache as $t_name => $t_event_info) {
    $g_event_cache[$t_name]['callbacks'] = array();
  }
}

/**
 * function event_signal
 * @param string $p_name Event name.
 * @param mixed $p_params Event parameters.
 * @return mixed - null if event undeclared, appropriate return value otherwise.
 */
function event_signal($p_name, $p_params = null) {
  global $g_event_cache;
  if (!isset($g_event_cache[$p_name])) {
    trigger_error('Event undeclared for: ' . $p_name, E_USER_NOTICE);
    return null;
  }

  $t_callbacks = $g_event_cache[$p_name]['callbacks'];
  $t_type = $g_event_cache[$p_name]['type'];

  switch ($t_type) {
    case EVENT_TYPE_CREATE:
    case EVENT_TYPE_UPDATE:
    case EVENT_TYPE_DELETE:
      tLog('Received event signal for: ' . $p_name . '.', 'DEBUG');
      event_type_execute($p_name, $t_callbacks, $p_params);
      return null;

    case EVENT_TYPE_OUTPUT:
      tLog('Received output event signal for: ' . $p_name . '.', 'DEBUG');
      return event_type_output( $p_name, $t_callbacks, $p_params );
    
    default:
      trigger_error('Unknown type: ' . $t_type, E_USER_NOTICE);
      return null;
  }
}

/**
 * function event_callback
 * @param string $p_event Event name.
 * @param string $p_callback Callback name.
 * @param string $p_plugin Plugin basename.
 * @param mixed $p_params Parameters for event callback.
 * @return mixed null if callback not found, value from callback otherwise.
 */
function event_callback($p_event, $p_callback, $p_plugin, $p_params = null) {
  $t_value = null;
  if (!is_array($p_params)) {
    $p_params = array($p_params,);
  }

  if ($p_plugin !== 0) {
    global $g_plugin_cache;
    plugin_push_current( $p_plugin );
    if (method_exists($g_plugin_cache[$p_plugin], $p_callback)) {
      $t_value = call_user_func_array(array($g_plugin_cache[$p_plugin], $p_callback), array_merge(array($p_event), $p_params));
    }
    plugin_pop_current();

  }

  return $t_value;
}

/**
 * function event_type_execute
 * @param string $p_event Event name.
 * @param array $p_callbacks Array of plugin base name key/value pairs.
 * @param array $p_params Callback parameters.
 * @return void.
 */
function event_type_execute($p_event, array $p_callbacks, $params = null) {
  foreach ($p_callbacks as $t_plugin => $t_callbacks) {
    foreach ($t_callbacks as $t_callback) {
      event_callback($p_event, $t_callback, $t_plugin, $params);
    }
  }
}

function event_type_output($p_event, $p_callbacks, $p_params = null)
{
  $t_output = array();
  foreach ($p_callbacks as $t_plugin => $t_callbacks) {
    foreach ($t_callbacks as $t_callback) {
      $t_output[] = event_callback($p_event, $t_callback, $t_plugin, $p_params);
    }
  }
  return $t_output;
}
?>
