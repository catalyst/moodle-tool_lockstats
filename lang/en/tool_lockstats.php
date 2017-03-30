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
$string['form_reset_button'] = 'Reset lock history';
$string['form_reset_warning'] = 'Warning. You are about to reset the lock statistics history. Are you sure you want to do this?';
$string['h1_current'] = 'Current running tasks';
$string['h1_detail'] = 'Task Details';
$string['h1_history'] = 'Recent tasks with a duration > {$a} seconds';
$string['h1_nexttask'] = 'Next running tasks';
$string['pluginname'] = 'Lock statistics';
$string['reset_header'] = 'Reset lock statistics history';
$string['reset_text'] = 'Reset the lock stastics history.';
$string['table_duration'] = 'Average Duration';
$string['table_gained'] = 'Time gained';
$string['table_host'] = 'Last Host';
$string['table_lockcount'] = 'Count';
$string['table_pid'] = 'PID';
$string['table_released'] = 'Time released';
$string['table_resource'] = 'Resource';
$string['table_task'] = 'Task';
$string['task_cleanup'] = 'Cleanup lockstats history';
$string['threshold'] = 'History threshold';
$string['thresholddesc'] = 'Only log new history entries when the cron task time exceeds this value.';
