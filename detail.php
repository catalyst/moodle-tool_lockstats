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
 * Proxy lock statistics, detail page.
 *
 * @package    tool_lockstats
 * @author     Nicholas Hoobin <nicholashoobin@catalyst-au.net>
 * @copyright  2017 Catalyst IT
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


require_once(dirname(__FILE__) . '/../../../config.php');
require_once($CFG->libdir . '/adminlib.php');

require_login(null, false);
require_capability('moodle/site:config', context_system::instance());

admin_externalpage_setup('tool_lockstats');

$taskid = required_param('task', PARAM_ALPHANUM);

$download = optional_param('download', '', PARAM_ALPHA);

$baseurl = new moodle_url("/admin/tool/lockstats/detail.php", ['task' => $taskid]);

$PAGE->navbar->add(get_string('h1_detail', 'tool_lockstats'));

$detail = new tool_lockstats\table\detail($baseurl, $taskid);

if ($detail->is_downloading($download, 'tool_lockstats_detail', 'tool_lockstats_detail')) {
    $detail->download();
}

echo $OUTPUT->header();

echo html_writer::tag('h1', get_string('h1_detail', 'tool_lockstats'));
$detail->out(50, false);

echo $OUTPUT->footer();
