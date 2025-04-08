<?php

namespace LogpTests;

require_once(__DIR__."/../logp.php");
require_once(__DIR__."/../../pest/pest.php");

use function \pest\test;
use function \pest\expect;

test("logger defaults", function() {
    $alogger = new \logp\Logger();

    expect($alogger->getEchoLogging())->toBeTruthy();
});

test("Call log with a message outputs message", function() {
    ob_start();
    \logp\log("Log a message!");
    $out = ob_get_clean();
    expect($out)->toMatch("/LOG: Log a message!/i");
});


test("Call warn with a message outputs message", function() {
    ob_start();
    \logp\warn("Warning message!");
    $out = ob_get_clean();
    expect($out)->toMatch("/WARNING: Warning message!/i");
});

test("Call error with a message outputs message", function() {
    ob_start();
    \logp\error("Error message!");
    $out = ob_get_clean();
    expect($out)->toMatch("/ERROR: Error message!/i");
});


test("set and get logger", function() {
    $alogger = new \logp\Logger();
    \logp\setlogger($alogger);
    $current = \logp\getlogger();
    expect($current)->toBeEqual($alogger);
});

test("file logger defaults", function() {
    $tmpfile = tempnam(__DIR__, "log_");
    //unlink($tmpfile);
    // The FileLogger saves the file when it goes out of scope
    // so we must register shutdown which removes the tmp file 
    register_shutdown_function("unlink", $tmpfile);

    //echo $tmpfile;
    expect($tmpfile)->not()->toBeFalsy();
    expect(strlen($tmpfile))->toBeGreaterThan(0);

    $flogger = new \logp\FileLogger($tmpfile);

    expect($flogger->getEchoLogging())->toBeFalsy();
    expect($flogger->getNumLogFileCopiesToKeep())->toBe(5);
    expect($flogger->getMaxRowsToCache())->toBeGreaterThan(0);
    expect($flogger->getMaxRowsToCache())->toBe(100);
    expect($flogger->getLogFileName())->toBe($tmpfile);
    expect($flogger->getFileSizeLimit())->toBe(1048576);
    expect($flogger->getMaxMessageLength())->toBe(1000);
    expect($flogger->getLogTime())->toBeTruthy();


});

test("file logger", function() {
    // Create a tmp log file and a FileLogger
    // Test logging two rows

    $tmpfile = tempnam(__DIR__, "log_");
    // The FileLogger saves the file when it goes out of scope
    // so we must register shutdown which removes the tmp file 
    register_shutdown_function("unlink", $tmpfile);

    $flogger = new \logp\FileLogger($tmpfile);
    expect($flogger->getEchoLogging())->toBeFalsy();
    expect($flogger->getMaxRowsToCache())->toBeGreaterThan(2);

    // Do not echo to stdout (default)
    $flogger->setEchoLogging(false);
    // Cache 100 lines (default) before written to file
    $flogger->setMaxRowsToCache(100);

    // Set the default logger
    \logp\setlogger($flogger);
    $current = \logp\getlogger();
    expect($current)->toBeEqual($flogger);

    ob_start();
    \logp\log("Log 1st message!");
    $out = ob_get_clean();
    // Default is to not echo, so we should not get anything
    expect($out)->not()->toMatch("/LOG: Log 1st message!/i");
    expect($out)->toBe("");

    // Turn on echo
    $flogger->setEchoLogging(true);
    ob_start();
    \logp\log("Log 2nd message!");
    $out = ob_get_clean();
    expect($out)->toMatch("/LOG: Log 2nd message!/i");

    // Read contents of logfile, but since we cache 100 lines, it should be empty
    $logcontents = file_get_contents($tmpfile);
    expect($logcontents)->toBe("");

    // Call explicit save, so the cached lines are written to file
    $flogger->save();

    // Now read contents and we should have two log messages
    $logcontents = file_get_contents($tmpfile);
    expect($logcontents)->not()->toBe("");

    $lines = explode("\n", $logcontents);

    expect($lines[0])->toMatch("/LOG: Log 1st message!/i");
    expect($lines[1])->toMatch("/LOG: Log 2nd message!/i");
});

test("log file history", function() {
    // Create a tmp log file and a FileLogger
    // Write enough logs so that one history log file is created

    $logfile = tempnam(__DIR__, "log_");
    // The FileLogger saves the file when it goes out of scope
    // so we must register shutdown which removes the log files 
    register_shutdown_function(function() use($logfile) {
        unlink($logfile);
        if(file_exists($logfile.".1"))
            unlink($logfile.".1");
    });

    $flogger = new \logp\FileLogger($logfile);
    $flogger->setMaxRowsToCache(0);

    // Set file size limit, including timestamp
    // Note! Depending on the cache size and log message size
    // the file can get bigger. The test on filesize is only done on save
    $flogger->setFileSizeLimit(1000);

    \logp\setlogger($flogger);

    $nmessages = 15;
    while($nmessages-- > 0)
    {
        $msg = str_repeat("A Log Msg ", 10);
        \logp\log($msg);
    }

    // Flush
    $flogger->save();

    // Make sure there are two log files
    expect(file_exists($logfile))->toBeTruthy();
    // The older file has a number appended to it
    expect(file_exists($logfile.".1"))->toBeTruthy();
});