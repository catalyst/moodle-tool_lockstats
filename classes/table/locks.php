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
 * Proxy lock factory, current list table.
 *
 * @package    tool_lockstats
 * @author     Nicholas Hoobin <nicholashoobin@catalyst-au.net>
 * @copyright  2017 Catalyst IT
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace tool_lockstats\table;

if (!defined('MOODLE_INTERNAL')) {
    die('Direct access to this script is forbidden.'); // It must be included from a Moodle page.
}

use html_table;
use html_table_row;
use moodle_url;
use html_writer;

/**
 * Proxy lock factory, current list table.
 *
 * @package    tool_lockstats
 * @author     Nicholas Hoobin <nicholashoobin@catalyst-au.net>
 * @copyright  2017 Catalyst IT
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class locks extends html_table {
    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct();

        $this->attributes['class'] = 'admintable generaltable';

        $headers = [get_string('table_lock_key', 'tool_lockstats'),
            get_string('table_classname', 'tool_lockstats')];
        $rows = [];

        $records = $this->get_current_locks();
        $hosts = [];

        // Create a dynamic list of headers from the current open locks.
        foreach ($records as $record) {
            $hosts[$record->host] = $record->host;
        }

        // This is a list of hostnames, sorting them looks nice.
        sort($hosts);

        // Header is first column in table, then the sorted hostnames come next.
        foreach ($hosts as $value) {
            array_push($headers, $value);
        }

        foreach ($records as $record) {
            // The first column is the resource key.
            $url = new moodle_url("/admin/tool/lockstats/locks_detail.php", [
                'resourcekey' => $record->resourcekey,
            ]);

            $adhocid = $this::get_adhoc_id_by_task($record->resourcekey);
            if ($adhocid != null) {
                $adhocrecord = $this->get_adhoc_record($adhocid);
                $link = html_writer::link($url, $record->resourcekey);
                $data = [$link, $adhocrecord->classname];
            } else {
                $link = html_writer::link($url, $record->resourcekey);
                $data = [$link, null];
            }
            // Add null data for the number of hosts that exist.
            for ($i = 1; $i < count($headers); $i++) {
                $data[] = '';
            }

            // Locate the index that this lock belongs to.
            $key = array_search($record->host, $headers);

            // Update the data.
            if (!empty($key)) {
                $data[$key] = sprintf('%s [PID:%d]', format_time(time() - $record->gained), $record->pid);
            }

            $rows[] = new html_table_row($data);
        }

        $this->head = $headers;
        $this->data = $rows;
    }

    /**
     * Obtain an array of all the currently held locks.
     *
     * @return array
     */
    private function get_current_locks() {
        global $DB;

        // Return the longest running locks in a descending order.
        $records = $DB->get_records('tool_lockstats_locks', ['released' => null], 'gained ASC');

        return $records;
    }

    /**
     * Get adhoc record by id.
     *
     * @return object
     */
    private function get_adhoc_record($adhocid) {
        global $DB;
        return $DB->get_record('task_adhoc', array('id' => $adhocid));
    }

    /**
     * Extract id of adhoc task
     *
     * @return id of adhoc task
     */
    public static function get_adhoc_id_by_task($task) {
        preg_match(" /^adhoc_(\d+)$/", $task, $results);

        if (count($results) > 0) {
            return (int) $results[1];
        } else {
            return null;
        }

    }
}
