<?php

namespace logp;

require_once(__DIR__."/src/logger.php");
require_once(__DIR__."/src/filelogger.php");

$_LOG = new Logger();
$_LOG->setEchoLogging(true);

function setlogger($logger)
{
    global $_LOG;
    if($logger != null)
    {
        $_LOG = $logger;
    }
}

function getlogger()
{
    global $_LOG;
    return $_LOG;
}

function log($message)
{
    global $_LOG;
    if($_LOG != null)
    {
        $_LOG->log($message);
    }
}

function warn($message)
{
    global $_LOG;
    if($_LOG != null)
    {
        $_LOG->warn($message);
    }
}

function error($message)
{
    global $_LOG;
    if($_LOG != null)
    {
        $_LOG->error($message);
    }
}

