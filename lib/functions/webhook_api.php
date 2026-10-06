<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later.
 *
 * Outgoing webhook notifications.
 *
 * Configuration (custom_config.inc.php):
 *
 *   $tlCfg->webhooks = array(
 *     array('url' => 'https://hooks.example.com/testlink',
 *           'events' => array('execution.created'),   // or array('*')
 *           'secret' => 'shared-secret-or-empty'),
 *   );
 *
 * Each matching webhook receives a JSON POST:
 *   {"event": "...", "timestamp": "...", "data": {...}}
 *
 * When 'secret' is non-empty the request carries an
 * X-TestLink-Signature header: sha256=<HMAC-SHA256 hex of the body>,
 * so receivers can authenticate the payload (GitHub-style).
 *
 * Delivery is best-effort fire-and-forget with a short timeout so a
 * slow or dead receiver can not block the user-facing operation.
 *
 * @package     TestLink
 * @filesource  webhook_api.php
 */

/**
 * Send $event with $data to every configured webhook subscribed to it.
 *
 * @param string $event dotted event name, e.g. 'execution.created'
 * @param array  $data  event payload, must be JSON-encodable
 *
 * @return void  errors are logged, never thrown
 */
function webhook_notify($event, $data)
{
  $hooks = config_get('webhooks');
  if (empty($hooks) || !is_array($hooks)) {
    return;
  }

  $body = json_encode(
    array('event' => $event,
          'timestamp' => date('c'),
          'data' => $data));

  foreach ($hooks as $hook) {
    if (empty($hook['url'])) {
      continue;
    }
    $events = isset($hook['events']) ? (array)$hook['events'] : array('*');
    if (!in_array('*', $events) && !in_array($event, $events)) {
      continue;
    }

    $headers = array('Content-Type: application/json',
                     'User-Agent: TestLink-Webhook');
    if (!empty($hook['secret'])) {
      $headers[] = 'X-TestLink-Signature: sha256=' .
                   hash_hmac('sha256', $body, $hook['secret']);
    }

    $ch = curl_init($hook['url']);
    curl_setopt_array($ch, array(
      CURLOPT_POST => true,
      CURLOPT_POSTFIELDS => $body,
      CURLOPT_HTTPHEADER => $headers,
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_CONNECTTIMEOUT => 2,
      CURLOPT_TIMEOUT => 3,
    ));
    if (curl_exec($ch) === false) {
      tLog('webhook_notify: delivery to ' . $hook['url'] .
           ' failed: ' . curl_error($ch), 'WARNING');
    }
    curl_close($ch);
  }
}
