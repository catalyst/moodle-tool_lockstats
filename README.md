# tool_lockstats

A lock statistics admin tool, specifically tailored to report on cron task timings.

This tool exposes which tasks are currently running and where, and also shows a detailed
history of how long each task has taken in the past.

# How it works

It implemets a proxy lock factory which adds instrumentation around the real lock factory.


# Installation

Install the plugin TBA

Add this to your config.php:

```
$CFG->lock_factory = '\\tool_lockstats\\proxy';
```

