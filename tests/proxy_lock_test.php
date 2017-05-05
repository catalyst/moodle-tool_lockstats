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
 * Unit tests for Proxy lock factory.
 *
 * @package    tool_lockstats
 * @author     Nicholas Hoobin <nicholashoobin@catalyst-au.net>
 * @copyright  2017 Catalyst IT
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


if (!defined('MOODLE_INTERNAL')) {
    die('Direct access to this script is forbidden.'); // It must be included from a Moodle page.
}

/**
 * Unit tests for Proxy lock factory.
 *
 * @package    tool_lockstats
 * @author     Nicholas Hoobin <nicholashoobin@catalyst-au.net>
 * @copyright  2017 Catalyst IT
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class proxy_lock_testcase extends advanced_testcase {
    /**
     * Clean up the database.
     */
    protected function setUp() {
        global $CFG, $DB;

        $dbtype = clean_param($DB->get_dbfamily(), PARAM_ALPHA);

        $lockfactoryclass = "\\core\\lock\\${dbtype}_lock_factory";
        if (!class_exists($lockfactoryclass)) {
            $lockfactoryclass = '\core\lock\file_lock_factory';
        }

        $CFG->proxied_lock_factory = $lockfactoryclass;
        $CFG->lock_factory = "\\tool_lockstats\\proxy_lock_factory";

        $this->resetAfterTest(true);
    }

    /**
     * Run a suite of tests on a lock factory.
     * @param \core\lock\lock_factory $lockfactory - A lock factory to test
     */
    protected function run_on_lock_factory(\core\lock\lock_factory $lockfactory) {

        if ($lockfactory->is_available()) {
            // This should work.
            $lock1 = $lockfactory->get_lock('abc', 2);
            $this->assertNotEmpty($lock1, 'Get a lock');

            if ($lockfactory->supports_timeout()) {
                if ($lockfactory->supports_recursion()) {
                    $lock2 = $lockfactory->get_lock('abc', 2);
                    $this->assertNotEmpty($lock2, 'Get a stacked lock');
                    $this->assertTrue($lock2->release(), 'Release a stacked lock');
                } else {
                    // This should timeout.
                    $lock2 = $lockfactory->get_lock('abc', 2);
                    $this->assertFalse($lock2, 'Cannot get a stacked lock');
                }
            }

            // Release the lock.
            $this->assertTrue($lock1->release(), 'Release a lock');
            // Get it again.
            $lock3 = $lockfactory->get_lock('abc', 2);

            $this->assertNotEmpty($lock3, 'Get a lock again');
            // Release the lock again.
            $this->assertTrue($lock3->release(), 'Release a lock again');
            // Release the lock again (shouldn't hurt).
            $this->assertFalse($lock3->release(), 'Release a lock that is not held');
            if (!$lockfactory->supports_auto_release()) {
                // Test that a lock can be claimed after the timeout period.
                $lock4 = $lockfactory->get_lock('abc', 2, 2);
                $this->assertNotEmpty($lock4, 'Get a lock');
                sleep(3);

                $lock5 = $lockfactory->get_lock('abc', 2, 2);
                $this->assertNotEmpty($lock5, 'Get another lock after a timeout');
                $this->assertTrue($lock5->release(), 'Release the lock');
                $this->assertTrue($lock4->release(), 'Release the lock');
            }
        }
    }

    /**
     * Tests the testable lock factories.
     * @return void
     */
    public function test_proxy_lock() {
        $lockfactory = \core\lock\lock_config::get_lock_factory('test');
        $this->run_on_lock_factory($lockfactory);
    }
}
