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
 * Proxy lock factory, adhoc task summary table.
 *
 * @package    tool_lockstats
 * @author     Ilya Tregubov <ilyatregubov@catalyst-au.net>
 * @copyright  2019 Catalyst IT
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

define ('LOCKSTAT_UNKNOWN', 0);
define ('LOCKSTAT_ADHOC', 1);
define ('LOCKSTAT_SCHEDULED', 2);

/**
 * Proxy lock factory, adhoc task summary table.
 *
 * @package    tool_lockstats
 * @author     Ilya Tregubov <ilyatregubov@catalyst-au.net>
 * @copyright  2019 Catalyst IT
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class adhoc_tasks extends html_table {
    /**
     * Constructor
     */
    public function __construct() {
        global $DB;

        parent::__construct();

        $this->head = [
            get_string('name'),
            get_string('component',         'tool_task'),
            get_string('table_queuedup',    'tool_lockstats'),
            get_string('table_running',     'tool_lockstats'),
            get_string('table_processed',   'tool_lockstats'),
            get_string('table_failed',      'tool_lockstats'),
            get_string('table_latencyavg',  'tool_lockstats'),
            get_string('table_latencymax',  'tool_lockstats'),
        ];

        $this->attributes['class'] = 'admintable generaltable';

        $data = [];

        $latencyavgsubquery = "
           SELECT avg(latency/lockcount)
             FROM {tool_lockstats_history}
            WHERE classname = classes.classname";

        $latencymaxsubquery = "
           SELECT max(latency/lockcount)
             FROM {tool_lockstats_history}
            WHERE classname = classes.classname";

        $processedsubquery = "
           SELECT COUNT(lockcount)
             FROM {tool_lockstats_history}
            WHERE classname = classes.classname";

        $concat = $DB->sql_concat("'adhoc_'", 'ta.id');
        $faildelaysubquery = "
           SELECT COUNT(ta.*) AS faildelay
             FROM {task_adhoc} ta
             JOIN {tool_lockstats_locks} tll ON tll.resourcekey = $concat
            WHERE ta.classname = classes.classname
                  AND ta.faildelay > 0";

        $runningsubquery = "
           SELECT COUNT(ta.*)
             FROM {task_adhoc} ta
             JOIN {tool_lockstats_locks} tll ON tll.resourcekey = $concat
            WHERE ta.classname = classes.classname
              AND released IS NULL";

        $queuedupsubquery = "
           SELECT COUNT(*)
             FROM {task_adhoc}
            WHERE classname = classes.classname";

        $type = LOCKSTAT_ADHOC;
        $classessubquery = "
           SELECT classname,
                  component
             FROM {task_adhoc}
         GROUP BY classname,
                  component
            UNION
           SELECT classname,
                  component
             FROM {tool_lockstats_history}
            WHERE type = {$type}
                  AND classname IS NOT NULL
         GROUP BY classname,
                  component";

        $sql = "
           SELECT DISTINCT ON (classes.classname)
                  classes.classname,
                  classes.component,
                  ($queuedupsubquery  ) queuedup,
                  ($runningsubquery   ) running,
                  ($processedsubquery ) processed,
                  ($faildelaysubquery ) failed,
                  ($latencyavgsubquery) latencyavg,
                  ($latencymaxsubquery) latencymax
             FROM ($classessubquery) classes
         GROUP BY classes.classname,
                  classes.component";

        $classes = $DB->get_records_sql($sql);

        foreach ($classes as $class) {

            $classname = explode("\\", $class->classname);
            $link = ucwords(str_replace("_", " ", end($classname)));
            $text = $link . "\n" . html_writer::tag('span', $class->classname, ['class' => 'task-class']);

            $namecell = new html_table_cell($text);
            $namecell->header = true;

            $component = $class->component;
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

            $row = new html_table_row(array(
                $namecell,
                $componentcell,
                new html_table_cell($class->queuedup),
                new html_table_cell($class->running),
                new html_table_cell($class->processed),
                new html_table_cell($class->failed),
                new html_table_cell(format_time(floor($class->latencyavg))),
                new html_table_cell(format_time(floor($class->latencymax))),
            ));

            $data[] = $row;
        }

        $this->data = $data;
    }
}
