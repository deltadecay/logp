<?php

namespace logp;



class Logger 
{
	protected $echoLogging = true;

	protected function internal_log($message, $type) 
	{
		if ($this->echoLogging) 
		{
			echo "$type: ".$message, PHP_EOL;
		}
	}

	/**
	 * Log an error message.
	 * @param string $message the message to log
	 */
	public function error($message) 
	{
		$this->internal_log($message, 'ERROR');
	}

	/**
	 * Log a warning message.
	 * @param string $message the message to log
	 */
	public function warn($message) 
	{
		$this->internal_log($message, 'WARNING');
	}

	/**
	 * Log a message.
	 * @param string $message the message to log
	 */
	public function log($message) 
	{
		$this->internal_log($message, 'LOG');
	}

	/**
	 * Set whether the logging should echo to screen.
	 * @param bool $echoLogging set to true if logging should echo to stdout.
	 */
	public function setEchoLogging($echoLogging)
	{
		$this->echoLogging = $echoLogging;
	}

	/**
	 * Get whether logging is echoed to stdout.
	 * @return bool true if logging is echoed to stdout
	 */
	public function getEchoLogging()
	{
		return $this->echoLogging;
	}
}
