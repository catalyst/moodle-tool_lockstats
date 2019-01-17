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
 * DB upgrades for tool_lockstats.
 *
 * @package    tool_lockstats
 * @author     Trisha Milan <trishamilan@catalyst-au.net>
 * @copyright  2019 Catalyst IT
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

function xmldb_tool_lockstats_upgrade($oldversion) {
    global $DB;

    $dbman = $DB->get_manager();

    if ($oldversion < 2019011600) {
        $lockstable = new xmldb_table('tool_lockstats_locks');
        $historytable = new xmldb_table('tool_lockstats_history');

        $field = new xmldb_field('task');
        $field->set_attributes(XMLDB_TYPE_CHAR, '255', null, null, null, null);
        // Update task column to use CHAR instead of TEXT.
        $dbman->change_field_type($lockstable, $field, $continue = true, $feedback = true);
        $dbman->change_field_type($historytable, $field, $continue = true, $feedback = true);

        $index = new xmldb_index('task', XMLDB_INDEX_NOTUNIQUE, array('task'));
        // Conditionally launch add index.
        if (!$dbman->index_exists($lockstable, $index)) {
            $dbman->add_index($lockstable, $index);
        }
        upgrade_plugin_savepoint(true, 2019011600, 'tool', 'lockstats');
    }

    return true;
}
