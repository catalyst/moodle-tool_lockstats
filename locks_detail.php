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
 * Proxy lock factory, locks detail page.
 *
 * @package    tool_lockstats
 * @author     John Yao <johnyao@catalyst-au.net>
 * @copyright  2019 Catalyst IT
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(dirname(__FILE__) . '/../../../config.php');
require_once($CFG->libdir . '/adminlib.php');

require_login(null, false);
require_capability('moodle/site:config', context_system::instance());

admin_externalpage_setup('tool_lockstats');

$resoucekey = required_param('resourcekey', PARAM_TEXT);
$sessionkey = sesskey();

$action = optional_param('action', '', PARAM_TEXT);

if ($action == 'try') {
    releaselock($resoucekey, $sessionkey);
}

$download = optional_param('download', '', PARAM_ALPHA);

$baseurl = new moodle_url("/admin/tool/lockstats/locks_detail.php", ['resourcekey' => $resoucekey]);

$PAGE->navbar->add(get_string('h1_detail', 'tool_lockstats'));

$detail = new tool_lockstats\table\locks_detail($baseurl, $resoucekey);

echo $OUTPUT->header();

echo html_writer::tag('h1', get_string('h1_detail', 'tool_lockstats'));

$url = new moodle_url("/admin/tool/lockstats/locks_detail.php",
    array('resourcekey' => $resoucekey, 'sesskey' => sesskey(), 'action' => 'try'));

echo $OUTPUT->single_button($url, get_string('release_lock', 'tool_lockstats'));

if ($action == 'warn') {
    echo $OUTPUT->notification(get_string('lock_in_use', 'tool_lockstats'), 'info');
}

$detail->out(50, false);
echo $OUTPUT->footer();


function releaselock($resourcekey, $sesskey) {
    if (isset($resourcekey) && confirm_sesskey($sesskey)) {
        $cronlockfactory = \core\lock\lock_config::get_lock_factory('cron');
        $lock = $cronlockfactory->get_lock($resourcekey, 0);

        if ($lock) {
            $cronlockfactory->release_lock($lock);
            $baseurl = new moodle_url('/admin/tool/lockstats/');
        } else {
            $baseurl = new moodle_url('/admin/tool/lockstats/locks_detail.php', array('resourcekey' => $resourcekey, 'action' => 'warn'));
        }
        redirect($baseurl);
    }
}
