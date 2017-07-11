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
 * Proxy lock factory, task list table.
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

use core_component;
use html_table;
use html_table_cell;
use html_table_row;
use html_writer;
use moodle_url;
use stdClass;

/**
 * Proxy lock factory, task list table.
 *
 * @package    tool_lockstats
 * @author     Nicholas Hoobin <nicholashoobin@catalyst-au.net>
 * @copyright  2017 Catalyst IT
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class tasks extends html_table {
    /**
     * Constructor
     */
    public function __construct() {
        global $CFG;

        parent::__construct();

        $this->head = [
            get_string('name'),
            get_string('component', 'tool_task'),
            get_string('lastruntime', 'tool_task'),
            get_string('nextruntime', 'tool_task'),
            get_string('faildelay', 'tool_task')
        ];

        $this->attributes['class'] = 'admintable generaltable';

        $data = [];

        $tasks = \core\task\manager::get_all_scheduled_tasks();

        usort($tasks, function($a, $b) {
            return $b->get_last_run_time() - $a->get_last_run_time();
        });

        $never = get_string('never');
        $asap = get_string('asap', 'tool_task');
        $disabledstr = get_string('taskdisabled', 'tool_task');
        $plugindisabledstr = get_string('plugindisabled', 'tool_task');

        foreach ($tasks as $task) {
            $key = '\\' . get_class($task);

            $history = $this->task_has_history($key);

            if ($history) {
                $url = new moodle_url("/admin/tool/lockstats/detail.php", ['task' => $history->taskid]);
                $link = html_writer::link($url, $task->get_name());
            } else {
                $link = $task->get_name();
            }

            $namecell = new html_table_cell($link . "\n" . html_writer::tag('span', '\\'.get_class($task), ['class' => 'task-class']));
            $namecell->header = true;

            $component = $task->get_component();
            $plugininfo = null;

            list($type, $plugin) = core_component::normalize_component($component);

            if ($type === 'core') {
                $componentcell = new html_table_cell(get_string('corecomponent', 'tool_task'));
            } else {
                if ($plugininfo = \core_plugin_manager::instance()->get_plugin_info($component)) {
                    $plugininfo->init_display_name();
                    $componentcell = new html_table_cell($plugininfo->displayname);
                } else {
                    $componentcell = new html_table_cell($component);
                }
            }

            $lastrun = $task->get_last_run_time() ? userdate($task->get_last_run_time(), '%a, %e %b %G %l:%M %p') : $never;
            $nextrun = $task->get_next_run_time();
            $disabled = false;

            if ($plugininfo && $plugininfo->is_enabled() === false && !$task->get_run_if_component_disabled()) {
                $disabled = true;
                $nextrun = $plugindisabledstr;
            } else if ($task->get_disabled()) {
                $disabled = true;
                $nextrun = $disabledstr;
            } else if ($nextrun > time()) {
                $nextrun = userdate($nextrun, '%a, %e %b %G %l:%M %p');
            } else {
                $nextrun = $asap;
            }

            $row = new html_table_row(array(
                $namecell,
                $componentcell,
                new html_table_cell($lastrun),
                new html_table_cell($nextrun),
                new html_table_cell($task->get_fail_delay())));

            if ($disabled) {
                $row->attributes['class'] = 'disabled';
            }
            $data[] = $row;
        }

        $this->data = $data;
    }

    /**
     * Returns a record of the if history exists for this task.
     *
     * @param string $task
     * @return false|stdClass
     */
    private function task_has_history($task) {
        global $DB;

        $params = ['task' => $task];
        $sql = "SELECT *
                  FROM {tool_lockstats_history} his
                 WHERE task = :task
                 LIMIT 1";

        $record = $DB->get_record_sql($sql, $params);

        return $record;
    }
}
