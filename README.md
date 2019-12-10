Travis integration: [![Build Status](https://travis-ci.org/catalyst/moodle-tool_lockstats.svg?branch=master)](https://travis-ci.org/catalyst/moodle-tool_lockstats)

# tool_lockstats

A lock statistics admin tool, specifically tailored to report on cron task timings.

This tool exposes which tasks are currently running and where, and also shows a detailed
history of how long each task has taken in the past.

# How it works

It implements a proxy lock factory which adds instrumentation around the real lock factory.
It will log details about each cron task when a lock is obtained and released.
This is the data that is obtained:

- Task name
- Duration
- Hostname
- Time gained
- Time released
- PID

Most of the time, most cron tasks are quick and finish in seconds. These typically are not the
tasks you are interesting in the history off. So this plugin compresses the history quick tasks
so you still get overall stats for all tasks, and detailed stats for slower bigger tasks, and
without bloating out the database with too much data. Old stats can be removed after a set
time period too.

# Installation

Install the plugin the same as any standard moodle plugin either via the Moodle plugin directory:

https://moodle.org/plugins/tool_lockstats

https://docs.moodle.org/en/Installing_plugins

OR you can use git to clone it into your source:

```bash
git clone git@github.com:catalyst/moodle-tool_lockstats.git admin/tool/lockstats
```

# Configuration

This is an example of using the Postgres lock factory, add this to your config.php:

```php
$CFG->lock_factory = "\\tool_lockstats\\proxy_lock_factory";
$CFG->proxied_lock_factory = "auto";

# If you want to be explicit you can do this:
$CFG->proxied_lock_factory = "\\core\\lock\\postgres_lock_factory";

// To allow unit tests to pass.
$CFG->phpunit_lock_factory = "\\tool_lockstats\\proxy_lock_factory";
$CFG->phpunit_proxied_lock_factory = "\\core\\lock\\postgres_lock_factory";
```

Using the UI you can configure additional settings at,

`Site administration > Plugins > Admin tools > Lock statistics`

The values you can configure are,

- Blacklist (Default: core_cron)

This allows you to prevent logging the history for specific tasks.

- History threshold (Default: 60)

If the task exceeds this value in seconds then a new history entry will be logged.

- Cleanup history (Default: 30)

A task exists that will clean up history entries that exceed this value in days.

- Debug (Default: No)

Provides additional debugging messages in the cron.log for when the locks are obtained and released.

# Usage

You can view the current locked tasks, lock history and details via the UI at,

`Site administration > Server > Lock statistics`

The list of current locks is also exposed via a cli script:

```sh
$ php admin/tool/lockstats/cli/list_locks.php 
    PID HOST       TYPE    TIME     KEY                  NAME                                    
  10806 zebrafish  adhoc   00:00:06 adhoc_65943          \tool_testtasks\task\timed_adhoc_task   
  10810 zebrafish  adhoc   00:00:05 adhoc_65945          \tool_testtasks\task\timed_adhoc_task   
  10808 zebrafish  adhoc   00:00:05 adhoc_65944          \tool_testtasks\task\timed_adhoc_task   

Found 3 lock(s)
```

And you can watch this for a dynamic list of processes:

```sh
watch -n 1 php admin/tool/lockstats/cli/list_locks.php
```
