<?php

namespace LogpTests;

require_once(__DIR__."/../logp.php");
require_once(__DIR__."/../../pest/pest.php");

use function \pest\test;
use function \pest\expect;


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


test("file logger", function() {
    // Create a tmp log file and a FileLogger
    // Test logging two rows

    $tmpfile = tempnam(__DIR__, "log_");
    expect($tmpfile)->not()->toBeFalsy();
    expect(strlen($tmpfile))->toBeGreaterThan(0);

    //echo $tmpfile;
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

    //unlink($tmpfile);
    // The FileLogger saves the file when it goes out of scope
    // so we must register shutdown which removes the tmp file 
    register_shutdown_function("unlink", $tmpfile);
});

