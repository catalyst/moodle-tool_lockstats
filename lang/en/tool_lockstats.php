<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Language en for 'tool_lockstats'
 *
 * @package    tool_lockstats
 * @author     Nicholas Hoobin <nicholashoobin@catalyst-au.net>
 * @copyright  2017 Catalyst IT
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


if (!defined('MOODLE_INTERNAL')) {
    die('Direct access to this script is forbidden.'); // It must be included from a Moodle page.
}

$string['blacklist'] = 'History blacklist';
$string['blacklistdesc'] = 'These are identified by their classpath, eg. \tool_crawler\task\crawl_task';
$string['cleanup'] = 'Cleanup history';
$string['cleanupdesc'] = 'Automatically prune the history table after this value.';
$string['debug'] = 'Debug';
$string['debugdesc'] = 'Print additional helpful debug output in the cron.log';
$string['enable'] = 'Enable';
$string['enabledesc'] = 'Enable lock statistics';
$string['errornolockfactory'] = 'Error: The lock_factory is not configured properly in config.php:<br>$CFG->lock_factory = \'\\tool_lockstats\\proxy_lock_factory\';';
$string['errornotenabled'] = 'Warning: lockstats plugin is not enabled. Please enable it on <a href="{$a}">plugin settings page</a>';
$string['form_reset_button'] = 'Reset lock history';
$string['form_reset_warning'] = 'Warning. You are about to reset the lock statistics history. Are you sure you want to do this?';
$string['h1_adhoctask'] = 'Adhoc tasks summary';
$string['h1_current'] = 'Current locks';
$string['h1_detail'] = 'Task Details';
$string['h1_slowest'] = 'Slowest tasks this week with a duration > {$a} seconds';
$string['h1_nexttask'] = 'Next running tasks';
$string['lock_in_use'] = 'Lock still in use by another process';
$string['pluginname'] = 'Cron Lock statistics';
$string['privacy:metadata'] = 'Cron Lock statistics plugin does not store any personal data.';
$string['release_all_locks'] = 'Release all stale locks';
$string['release_lock'] = 'Release lock';
$string['reset_header'] = 'Reset lock statistics history';
$string['reset_text'] = 'Reset the lock stastics history.';
$string['table_customdata'] = 'Custom data';
$string['table_duration'] = 'Average Duration';
$string['table_gained'] = 'Time gained';
$string['table_host'] = 'Last Host';
$string['table_latencyavg'] = 'Latency Avg';
$string['table_latencymax'] = 'Latency Max';
$string['table_lockcount'] = 'Count';
$string['table_pid'] = 'PID';
$string['table_released'] = 'Time released';
$string['table_classname'] = 'Class name';
$string['table_lock_key'] = 'Lock key';
$string['table_task'] = 'Task';
$string['table_processed'] = 'Processed';
$string['table_queuedup'] = 'Queued';
$string['table_running'] = 'Running';
$string['table_failed'] = 'Failed';
$string['task_cleanup'] = 'Cleanup lockstats history';
$string['table_latency'] = 'Latency';
$string['threshold'] = 'History threshold';
$string['thresholddesc'] = 'Only log new history entries when the cron task time exceeds this value.';
