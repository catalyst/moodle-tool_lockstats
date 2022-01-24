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

namespace tool_lockstats\test;

/**
 * Unit tests for Proxy lock factory.
 *
 * @package    tool_lockstats
 * @author     Peter Burnett <peterburnett@catalyst-au.net>
 * @copyright  Catalyst IT
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class stale_lock_testcase extends \advanced_testcase {
    public function test_lock_staleness() {
        global $DB;
        $this->resetAfterTest();

        // No locks, should be result::OK.
        $check = new \tool_lockstats\check\stale_lock();
        $result = $check->get_result();
        $this->assertEquals(\core\check\result::OK, $result->get_status());

        // Add a lock with a timestamp of 5 minutes ago. Should still be OK.
        $DB->insert_record('tool_lockstats_locks', [
            'resourcekey' => 'Test',
            'component' => 'tool_lockstats',
            'classname' => 'stale_lock_testcase',
            'gained' => time() - (5 * MINSECS)
        ]);
        $result = $check->get_result();
        $this->assertEquals(\core\check\result::OK, $result->get_status());

        // Insert a 2 day old lock that was released.
        $DB->insert_record('tool_lockstats_locks', [
            'resourcekey' => 'Test',
            'component' => 'tool_lockstats',
            'classname' => 'stale_lock_testcase',
            'gained' => time() - (2 * DAYSECS),
            'released' => time() - (2 * DAYSECS) + (5 * MINSECS)
        ]);
        $result = $check->get_result();
        $this->assertEquals(\core\check\result::OK, $result->get_status());

        // Now a 2 day old lock still not released.
        // Insert a 2 day old lock that was released.
        $DB->insert_record('tool_lockstats_locks', [
            'resourcekey' => 'Test',
            'component' => 'tool_lockstats',
            'classname' => 'stale_lock_testcase',
            'gained' => time() - (2 * DAYSECS),
        ]);
        $result = $check->get_result();
        $this->assertEquals(\core\check\result::ERROR, $result->get_status());
    }
}
