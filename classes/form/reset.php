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

namespace tool_lockstats\form;

if (!defined('MOODLE_INTERNAL')) {
    die('Direct access to this script is forbidden.'); // It must be included from a Moodle page.
}

use html_writer;
use moodleform;

require_once($CFG->libdir . '/formslib.php');

/**
 * Form for resetting the history.
 *
 * @package    tool_lockstats
 * @author     Nicholas Hoobin <nicholashoobin@catalyst-au.net>
 * @copyright  2017 Catalyst IT
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class reset extends moodleform {
    /**
     * Form definition.
     */
    protected function definition() {
        global $OUTPUT;
        $mform    = $this->_form;

        $warningmsg = get_string('form_reset_warning', 'tool_lockstats');

        $html  = html_writer::start_div('warning');
        $html .= $OUTPUT->notification($warningmsg, 'warning');
        $html .= html_writer::empty_tag('br');
        $html .= html_writer::end_div();

        $mform->addElement('html', $html);

        $buttonstr = get_string('form_reset_button', 'tool_lockstats');

        $this->add_action_buttons(true, $buttonstr);

    }
}
