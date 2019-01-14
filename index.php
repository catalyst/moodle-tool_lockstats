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
 * Proxy lock statistics.
 *
 * @package    tool_lockstats
 * @author     Nicholas Hoobin <nicholashoobin@catalyst-au.net>
 * @copyright  2017 Catalyst IT
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


require_once(dirname(__FILE__) . '/../../../config.php');
require_once($CFG->libdir . '/adminlib.php');

require_capability('moodle/site:config', context_system::instance());
admin_externalpage_setup('tool_lockstats');

$download = optional_param('download', '', PARAM_ALPHA);

// Return the longest running locks in a descending order.
$records = $DB->get_records('tool_lockstats_locks', ['released' => null], 'gained DESC');

$current = new tool_lockstats\table\locks();
$history = new tool_lockstats\table\history(new moodle_url("/admin/tool/lockstats/index.php"));
$tasks = new tool_lockstats\table\tasks();

if ($history->is_downloading($download, 'tool_lockstats_history', 'tool_lockstats_history')) {
    $history->download();
}

echo $OUTPUT->header();

if ($CFG->lock_factory != "\\tool_lockstats\\proxy_lock_factory") {
    echo $OUTPUT->notification(get_string('errornolockfactory', 'tool_lockstats'), 'error');
}

// Current locks.
echo html_writer::tag('h1', get_string('h1_current', 'tool_lockstats'));
echo html_writer::table($current);
echo html_writer::empty_tag('br');

// The heaviest locks.
$a = get_config('tool_lockstats', 'threshold');
echo html_writer::tag('h1', get_string('h1_slowest', 'tool_lockstats', $a));
$history->out(10, false);
echo html_writer::empty_tag('br');

// A list of tasks with history, the ability to select one and see its history filtered.
echo html_writer::tag('h1', get_string('h1_nexttask', 'tool_lockstats'));
echo html_writer::table($tasks);

$reseturl = new moodle_url("/admin/tool/lockstats/historyreset.php");
$resettext = get_string('reset_text', 'tool_lockstats');

echo html_writer::empty_tag('br');
echo html_writer::link($reseturl, $resettext);

echo $OUTPUT->footer();
