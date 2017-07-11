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
 * Form for resetting the history.
 *
 * @package    tool_lockstats
 * @author     Nicholas Hoobin <nicholashoobin@catalyst-au.net>
 * @copyright  2017 Catalyst IT
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


require_once(dirname(__FILE__) . '/../../../config.php');
require_once($CFG->dirroot .'/admin/tool/crawler/lib.php');
require_once($CFG->libdir . '/adminlib.php');

require_login(null, false);
require_capability('moodle/site:config', context_system::instance());

admin_externalpage_setup('tool_lockstats');

$form = new \tool_lockstats\form\reset(null);

$baseurl = new moodle_url('/admin/tool/lockstats/');

if ($data = $form->get_data()) {

    if ($form->is_submitted()) {
        global $DB;

        $DB->delete_records('tool_lockstats_locks');
        $DB->delete_records('tool_lockstats_history');

        redirect($baseurl);
    }
} else if ($form->is_cancelled()) {
    redirect($baseurl);
}

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('reset_header', 'tool_lockstats'));
echo $form->display();
echo $OUTPUT->footer();
