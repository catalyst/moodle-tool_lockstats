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
 * Proxy lock factory.
 *
 * @package    tool_lockstats
 * @author     Nicholas Hoobin <nicholashoobin@catalyst-au.net>
 * @copyright  2017 Catalyst IT
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


namespace tool_lockstats;

use core\lock\lock;
use core\lock\lock_config;
use core\lock\lock_factory;
use stdClass;

if (!defined('MOODLE_INTERNAL')) {
    die('Direct access to this script is forbidden.'); // It must be included from a Moodle page.
}

/**
 * Proxy lock factory.
 *
 * @package    tool_lockstats
 * @author     Nicholas Hoobin <nicholashoobin@catalyst-au.net>
 * @copyright  2017 Catalyst IT
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class proxy_lock_factory implements lock_factory {

    /** @var lock_factory $proxiedlockfactory - The real lock factory object. */
    protected $proxiedlockfactory;

    /** @var string $type - The type of lock, e.g. cache, cron, session. */
    protected $type;

    /** @var array $openlocks - An array of locks that have been obtained. */
    protected $openlocks = [];

    /** @var boolean $debug - Debug logging. */
    private $debug;

    /**
     * Define the constructor signature required by the lock_config class.
     *
     * @param string $type - The type this lock is used for (e.g. cron, cache)
     */
    public function __construct($type) {
        global $CFG;

        $this->reset_running_tasks();

        $this->debug = get_config('tool_lockstats', 'debug');

        $this->type = $type;

        $lockfactory = $CFG->lock_factory;

        $proxiedfactory = $CFG->proxied_lock_factory;

        // Set the real lock factory.
        $CFG->lock_factory = $proxiedfactory;

        // Obtain a proxy object of the real lock factory.
        $this->proxiedlockfactory = lock_config::get_lock_factory($type);

        // Set the value back to our string.
        $CFG->lock_factory = $lockfactory;

        \core_shutdown_manager::register_function(array($this, 'auto_release'));
    }

    /**
     * Return information about the blocking behaviour of the locks on this platform.
     *
     * @return boolean - False if attempting to get a lock will block indefinitely.
     */
    public function supports_timeout() {
        return $this->proxiedlockfactory->supports_timeout();
    }

    /**
     * Will this lock be automatically released when the process ends.
     * This should never be relied upon in code - but is useful in the case of
     * fatal errors. If a lock type does not support this auto release,
     * the max lock time parameter must be obeyed to eventually clean up a lock.
     *
     * @return boolean - True if this lock type will be automatically released when the current process ends.
     */
    public function supports_auto_release() {
        return $this->proxiedlockfactory->supports_auto_release();
    }

    /**
     * Supports recursion.
     *
     * @return boolean - True if attempting to get 2 locks on the same resource will "stack"
     */
    public function supports_recursion() {
        return $this->proxiedlockfactory->supports_recursion();
    }

    /**
     * Is available.
     *
     * @return boolean - True if this lock type is available in this environment.
     */
    public function is_available() {
        return $this->proxiedlockfactory->is_available();
    }

    /**
     * Get a lock within the specified timeout or return false.
     *
     * @param string $task - The identifier for the lock. Should use frankenstyle prefix.
     * @param int $timeout - The number of seconds to wait for a lock before giving up.
     *                       Not all lock types will support this.
     * @param int $maxlifetime - The number of seconds to wait before reclaiming a stale lock.
     *                       Not all lock types will use this - e.g. if they support auto releasing
     *                       a lock when a process ends.
     * @return lock|boolean - An instance of \core\lock\lock if the lock was obtained, or false.
     */
    public function get_lock($resourcekey, $timeout, $maxlifetime = 86400) {
        $lock = $this->proxiedlockfactory->get_lock($resourcekey, $timeout, $maxlifetime);

        if ($lock) {
            $proxylock = new lock($resourcekey, $this);

            $this->openlocks[$proxylock->get_key()][] = $lock;

            $this->log_lock($proxylock->get_key());

            if ($this->debug) {
                mtrace('tool_lockstats [lock obtained]: ' . $proxylock->get_key());
            }

            return $proxylock;
        }

        return false;
    }

    /**
     * Release a lock that was previously obtained with @lock.
     *
     * @param lock $proxylock - The lock to release.
     * @return boolean - True if the lock is no longer held (including if it was never held).
     */
    public function release_lock(lock $proxylock) {
        $resourcekey = $proxylock->get_key();

        $this->openlocks[$proxylock->get_key()];

        $lock = array_pop($this->openlocks[$proxylock->get_key()]);

        $lock->release();

        if ($this->debug) {
            mtrace('tool_lockstats [lock released]: ' . $resourcekey);
        }

        $this->log_unlock($resourcekey);

        unset($lock);

        return true;
    }

    /**
     * Extend the timeout on a held lock.
     *
     * @param lock $lock - lock obtained from this factory
     * @param int $maxlifetime - new max time to hold the lock
     * @return boolean - True if the lock was extended.
     */
    public function extend_lock(lock $lock, $maxlifetime = 86400) {
        return $this->proxiedlockfactory->extend_lock($lock, $maxlifetime);
    }

    /**
     * Auto release any open locks on shutdown.
     * This is required, because we may be using persistent DB connections.
     */
    public function auto_release() {
        // Called from the shutdown handler. Must release all open locks.
        foreach ($this->openlocks as $id => $locks) {
            foreach ($locks as $task => $unused) {
                $lock = new lock($task, $this);

                $this->log_unlock($task);

                $lock->release();
            }
        }
    }

    /**
     * Log information from the $resourcekey to the current lock table. This is for when a lock is gained.
     *
     * @param string $resourcekey
     * @return false|stdClass
     */
    private function log_lock($resourcekey) {
        global $DB;

        $params = ['resourcekey' => $resourcekey];

        $select = $DB->sql_compare_text('resourcekey') . ' = ' . $DB->sql_compare_text(':resourcekey');

        $record = $DB->get_record_select('tool_lockstats_locks', $select, $params);

        preg_match(" /^adhoc_(\d+)$/", $resourcekey, $adhoc);
        if (count($adhoc) > 0) {
            $timequeued = $DB->get_record('task_adhoc', array('id' => $adhoc[1]), 'nextruntime');
        }

        if (empty($record)) {
            $record = new stdClass();
            $record->resourcekey = $resourcekey;
            $record->gained = time();
            $record->host = gethostname();
            $record->pid = posix_getpid();
            $record = $this->fill_more_for_tasks($record, $resourcekey);
            if (isset($timequeued)) {
                $record->latency = $record->gained - $timequeued->nextruntime;
            }
            $DB->insert_record('tool_lockstats_locks', $record);
        } else {
            $record->gained = time();
            $record->released = null;
            $record->host = gethostname();
            $record->pid = posix_getpid();
            if (isset($timequeued)) {
                $record->latency = $record->gained - $timequeued->nextruntime;
            }
            $DB->update_record('tool_lockstats_locks', $record);
        }

        return $record;
    }

    /**
     * Log information from the $resourcekey to the current lock table. This is for when a lock is released.
     *
     * @param string $resourcekey
     * @return bool
     */
    private function log_unlock($resourcekey) {
        global $DB;

        $select = $DB->sql_compare_text('resourcekey') . ' = ' . $DB->sql_compare_text(':resourcekey');

        $params = ['resourcekey' => $resourcekey];

        $record = $DB->get_record_select('tool_lockstats_locks', $select, $params);

        if ($record) {
            $delta = time() - $record->gained;

            $record->released = time();
            $record->duration = $delta;

            $DB->update_record('tool_lockstats_locks', $record);

            // Prevent logging tasks that exist in the blacklist.
            $blacklist = get_config('tool_lockstats', 'blacklist');
            foreach (explode(PHP_EOL, $blacklist) as $item) {
                if ($item == $resourcekey) {
                    if ($this->debug) {
                        mtrace('tool_lockstats [history blacklist]: ' . $item);
                    }
                    return false;
                }
            }

            if ($delta > get_config('tool_lockstats', 'threshold')) {
                // The record is duration is higher than the threshold. Create a new record.
                $this->log_history($record);
            } else {
                // Lets update the lock count instead.
                $this->log_update_count($record);
            }

        }
    }

    /**
     * When a lock is released, log the $record into the history table.
     *
     * @param stdClass $record
     */
    private function log_history($record) {
        global $DB;

        $record->duration = $record->released - $record->gained;
        $record->lockcount = 1;
        $record->taskid = $record->id;
        $DB->insert_record('tool_lockstats_history', $record);
    }

    /**
     * If the threshold has not been met, update the lock count for the most recent history entry.
     *
     * @param stdClass $record
     */
    private function log_update_count($record) {
        global $DB;

        $history = $this->get_recent_history($record->id);

        $threshold = get_config('tool_lockstats', 'threshold');

        // Only aggregate the previous log if the total duration is less than the threshold.
        // Otherwise we will make a new
        if ($history && $history->duration < $threshold) {
            $history->lockcount += 1;
            $history->gained = $record->gained;
            $history->released = time();
            $history->duration += $record->duration;

            preg_match(" /^adhoc_(\d+)$/", $record->resourcekey, $adhoc);
            if (count($adhoc) > 0) {
                $history->latency += $history->latency;
            }

            $DB->update_record('tool_lockstats_history', $history);
        } else {
            $this->log_history($record);
        }

    }

    /**
     * Obtain the most recent history entry for the taskid specified.
     *
     * @param int $taskid
     * @return stdClass $record
     */
    private function get_recent_history($taskid) {
        global $DB;

        $sql = "SELECT *
                  FROM {tool_lockstats_history}
                 WHERE taskid = :taskid0
                 AND id = (SELECT MAX(id)
                             FROM {tool_lockstats_history}
                            WHERE taskid = :taskid1)";

        $params = [
            'taskid0' => $taskid,
            'taskid1' => $taskid
        ];

        $record = $DB->get_record_sql($sql, $params);

        return $record;
    }

    /**
     * If the database has been refreshed from another instance, existing running tasks may be orphaned.
     *
     * This compares the current wwwroot with a saved value, if they differ then it will reset the current tasks.
     */
    private function reset_running_tasks() {
        global $CFG, $DB;

        $wwwroot = get_config('tool_lockstats', 'wwwroot');

        if ($CFG->wwwroot !== $wwwroot) {
            $DB->delete_records('tool_lockstats_locks');
            set_config('wwwroot', $CFG->wwwroot, 'tool_lockstats');
        }

    }

    /**
     * Fill in more data for adhoc and scheduled tasks
     * @param stdClass $record
     * @param stdClass $record
     * @return stdClass $records to insert for adhoc and scheduled tasks
     */
    private function fill_more_for_tasks($record, $resourcekey) {
        global $DB;

        $adhocid = \tool_lockstats\table\locks::get_adhoc_id_by_task($resourcekey);
        if ($adhocid != null) {
            $adhocparams = ['id' => $adhocid];
            $adhocselect = $DB->sql_compare_text('id') . ' = ' . $DB->sql_compare_text(':id');
            $adhocrecord = $DB->get_record_select('task_adhoc', $adhocselect, $adhocparams);
            $record->classname = $adhocrecord->classname;
            $record->customdata = $adhocrecord->customdata;
        } else {
            $scheduledparams = ['classname' => $resourcekey];
            $scheduledselect = $DB->sql_compare_text('classname') . ' = ' . $DB->sql_compare_text(':classname');
            $scheduledrecord = $DB->get_record_select('task_scheduled', $scheduledselect, $scheduledparams);
            if (!empty($scheduledrecord)) {
                $record->classname = $resourcekey;
                $record->component = $scheduledrecord->component;
            }
        }

        return $record;
    }

}
