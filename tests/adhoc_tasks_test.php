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
 * Unit tests for adhoc tasks table class.
 *
 * @package    tool_lockstats
 * @author     Peter Burnett <peterburnett@catalyst-au.net>
 * @copyright  2020 Catalyst IT
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


if (!defined('MOODLE_INTERNAL')) {
    die('Direct access to this script is forbidden.'); // It must be included from a Moodle page.
}

/**
 * Unit tests for adhoc tasks table class.
 *
 * @package    tool_lockstats
 * @author     Peter Burnett <peterburnett@catalyst-au.net>
 * @copyright  2020 Catalyst IT
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class adhoc_tasks_table_testcase extends advanced_testcase {
    public function test_history_table_constructor() {
        // This function tests the constructor for the history table for Cross DB compatability.
        // This test contains no assertions, but ensures there are no exceptions.
        $table = new \tool_lockstats\table\adhoc_tasks();
    }
}
