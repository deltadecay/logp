# Logp

Logp is a simple logger in php


# Usage

```php
require_once("logp.php");

// Use the default logger which just echoes to stdout

\logp\log("a log message");
// LOG: a log message

\logp\warn("a warning message");
// WARNING: a warning message

\logp\error("an error message");
// ERROR: an error message
```

Use a **FileLogger** to log to file. It will log up to configurable number of files with a set size. 

```php
$logfilename = "myapplog.log";

$flogger = \logp\FileLogger($logfilename);
// also output to stdout, default is false
$flogger->setEchoLogging(true);

// Following are the defaults
// Number of messages to cache before flushing to file
$flogger->setMaxRowsToCache(100);
// Set number of historical log files to keep
$flogger->setNumHistoryFilesToKeep(5);
// Compress the historical log files
$flogger->setCompressHistoryFiles(true);

// Set size of a log file to 1Mb
$flogger->setFileSizeLimit(1024*1024);
// Set max length of a single message
$flogger->setMaxMessageLength(1000);
// Log the timestamp also
$flogger->setLogTime(true);

// Set the default logger to the above file logger
\logp\setlogger($flogger);

// Now call the log functions as before
\logp\log("a log message!");

// You can also call log on the instance
$flogger->error("more to log");

// you can call explicitly save
$flogger->save();

// or when script terminates, it will save to the log file
```

Example log message written to the log file:
```
2025-04-07T14:36:59+0000 LOG: a log message!
2025-04-07T14:36:59+0000 ERROR: more to log
```