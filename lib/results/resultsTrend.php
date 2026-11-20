<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later.
 *
 * Execution trend & flaky test report for a test plan.
 *
 * - Daily pass/fail/blocked counters rendered as a server-side SVG
 *   chart (no external JS library needed).
 * - Flaky ranking: test case versions whose recent executions flip
 *   between pass and fail; ordered by number of status flips.
 *
 * @filesource resultsTrend.php
 */
require('../../config.inc.php');
require_once('../../vendor/autoload.php');
require_once('common.php');
require_once('displayMgr.php');

$templateCfg = templateConfiguration();

/**
 * standard results-page access check, called by testlinkInitPage()
 */
function checkRights(&$db, &$user, $context = null)
{
  if (is_null($context)) {
    $context = new stdClass();
    $context->tproject_id = $context->tplan_id = null;
    $context->getAccessAttr = false;
  }
  return $user->hasRightOnProj($db, 'testplan_metrics',
    $context->tproject_id, $context->tplan_id, $context->getAccessAttr);
}

list($tplan_mgr, $args) = initArgsForReports($db);
if (null == $tplan_mgr) {
  $tplan_mgr = new testplan($db);
}

$gui = new stdClass();
$gui->title = lang_get('title_results_trend');

$tprojectMgr = new testproject($db);
$dummy = $tprojectMgr->get_by_id($args->tproject_id);
$gui->tproject_name = $dummy['name'];

$info = $tplan_mgr->get_by_id($args->tplan_id);
$gui->tplan_name = $info['name'];
$gui->tplan_id = intval($args->tplan_id);

$tables = tlObjectWithDB::getDBTables(array('executions', 'nodes_hierarchy', 'tcversions'));

// -----------------------------------------------------------------
// Daily status counters
// -----------------------------------------------------------------
$sql = " SELECT DATE(execution_ts) AS execday, status, COUNT(*) AS qty " .
       " FROM {$tables['executions']} " .
       " WHERE testplan_id = " . intval($args->tplan_id) .
       " GROUP BY execday, status ORDER BY execday ";

$rows = (array)$db->get_recordset($sql);
$daily = array();
foreach ($rows as $row) {
  $day = $row['execday'];
  if (!isset($daily[$day])) {
    $daily[$day] = array('p' => 0, 'f' => 0, 'b' => 0, 'other' => 0);
  }
  $statusKey = isset($daily[$day][$row['status']]) ? $row['status'] : 'other';
  $daily[$day][$statusKey] += $row['qty'];
}

$gui->trendSVG = buildTrendSVG($daily);
$gui->trendDays = $daily;

// -----------------------------------------------------------------
// Flaky ranking: count pass<->fail flips per tcversion, newest execs
// -----------------------------------------------------------------
$sql = " SELECT E.tcversion_id, E.status, NHTC.name, NHTC.id AS tcase_id " .
       " FROM {$tables['executions']} E " .
       " JOIN {$tables['nodes_hierarchy']} NHTCV ON NHTCV.id = E.tcversion_id " .
       " JOIN {$tables['nodes_hierarchy']} NHTC ON NHTC.id = NHTCV.parent_id " .
       " WHERE E.testplan_id = " . intval($args->tplan_id) .
       " AND E.status IN ('p','f') " .
       " ORDER BY E.tcversion_id, E.execution_ts ";

$rows = (array)$db->get_recordset($sql);
$flips = array();
$prev = array();
foreach ($rows as $row) {
  $key = $row['tcversion_id'];
  if (!isset($flips[$key])) {
    $flips[$key] = array('name' => $row['name'],
                         'tcase_id' => $row['tcase_id'],
                         'flips' => 0, 'total' => 0);
  }
  $flips[$key]['total']++;
  if (isset($prev[$key]) && $prev[$key] != $row['status']) {
    $flips[$key]['flips']++;
  }
  $prev[$key] = $row['status'];
}
$flips = array_filter($flips, function ($item) {
  return $item['flips'] > 0;
});
uasort($flips, function ($a, $b) {
  return $b['flips'] <=> $a['flips'];
});
$gui->flaky = array_slice($flips, 0, 50, true);
$gui->flakyAnalyzed = count($prev);

$smarty = new TLSmarty();
$smarty->assign('gui', $gui);
$smarty->display($templateCfg->template_dir . $templateCfg->default_template);

/**
 * Render daily status counters as a self-contained SVG stacked chart.
 *
 * @param array $daily map: 'YYYY-MM-DD' => array(p =>, f =>, b =>, other =>)
 * @return string svg markup, '' when there is nothing to show
 */
function buildTrendSVG($daily)
{
  if (count($daily) == 0) {
    return '';
  }

  $w = 900;
  $h = 260;
  $padL = 50;
  $padB = 40;
  $padT = 16;
  $plotW = $w - $padL - 10;
  $plotH = $h - $padT - $padB;

  $maxTotal = 1;
  foreach ($daily as $counts) {
    $maxTotal = max($maxTotal, array_sum($counts));
  }

  $barGap = 4;
  $n = count($daily);
  $barW = max(4, min(60, intval($plotW / $n) - $barGap));

  $colors = array('p' => '#6bbf59', 'f' => '#d9534f',
                  'b' => '#f0ad4e', 'other' => '#999999');

  $svg = '<svg xmlns="http://www.w3.org/2000/svg" width="' . $w .
         '" height="' . $h . '" font-family="sans-serif" font-size="11">';
  // y axis: 0 / mid / max
  foreach (array(0, 0.5, 1) as $frac) {
    $y = $padT + $plotH - intval($plotH * $frac);
    $svg .= '<line x1="' . $padL . '" y1="' . $y . '" x2="' . ($padL + $plotW) .
            '" y2="' . $y . '" stroke="#dddddd"/>';
    $svg .= '<text x="' . ($padL - 6) . '" y="' . ($y + 4) .
            '" text-anchor="end">' . intval($maxTotal * $frac) . '</text>';
  }

  $x = $padL + $barGap;
  foreach ($daily as $day => $counts) {
    $y = $padT + $plotH;
    foreach ($colors as $statusKey => $color) {
      $qty = $counts[$statusKey];
      if ($qty == 0) {
        continue;
      }
      $hh = intval($plotH * $qty / $maxTotal);
      $y -= $hh;
      $svg .= '<rect x="' . $x . '" y="' . $y . '" width="' . $barW .
              '" height="' . $hh . '" fill="' . $color . '">' .
              '<title>' . htmlspecialchars($day) . ' ' . $statusKey . ': ' .
              $qty . '</title></rect>';
    }
    // day label, rotated to survive dense charts
    $svg .= '<text x="' . ($x + intval($barW / 2)) . '" y="' . ($h - $padB + 12) .
            '" text-anchor="end" transform="rotate(-40 ' .
            ($x + intval($barW / 2)) . ' ' . ($h - $padB + 12) . ')">' .
            htmlspecialchars(substr($day, 5)) . '</text>';
    $x += $barW + $barGap;
    if ($x > $padL + $plotW) {
      break;
    }
  }

  // legend
  $lx = $padL;
  foreach ($colors as $statusKey => $color) {
    $svg .= '<rect x="' . $lx . '" y="' . ($h - 12) .
            '" width="10" height="10" fill="' . $color . '"/>';
    $svg .= '<text x="' . ($lx + 14) . '" y="' . ($h - 3) . '">' .
            $statusKey . '</text>';
    $lx += 60;
  }

  return $svg . '</svg>';
}
