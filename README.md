Travis integration: [![Build Status](https://travis-ci.org/catalyst/moodle-tool_lockstats.svg?branch=master)](https://travis-ci.org/catalyst/moodle-tool_lockstats)

# tool_lockstats

A lock statistics admin tool, specifically tailored to report on cron task timings.

This tool exposes which tasks are currently running and where, and also shows a detailed
history of how long each task has taken in the past.

# How it works

It implements a proxy lock factory which adds instrumentation around the real lock factory.


# Installation

Install the plugin the same as any standard moodle plugin either via the Moodle plugin directory, or you can use git to clone it into your source:

git clone git@github.com:catalyst/moodle-tool_lockstats.git admin/tool/lockstats

# Configuration

This is an example of using the Postgres lock factory, add this to your config.php:

```
$CFG->lock_factory = "\\tool_lockstats\\proxy_lock_factory";
$CFG->proxied_lock_factory = "\\core\\lock\\postgres_lock_factory";

$CFG->phpunit_lock_factory = "\\tool_lockstats\\proxy_lock_factory";
$CFG->phpunit_proxied_lock_factory = "\\core\\lock\\postgres_lock_factory";
```
