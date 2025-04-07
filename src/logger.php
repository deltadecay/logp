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
	 * @param string $message
	 */
	public function error($message) 
    {
		$this->internal_log($message, 'ERROR');
	}

	/**
	 * Log a warning message.
	 * @param string $message
	 */
	public function warn($message) 
    {
		$this->internal_log($message, 'WARNING');
	}

	/**
	 * Log a message.
	 * @param string $message
	 */
	public function log($message) 
    {
		$this->internal_log($message, 'LOG');
	}

	/**
	 * Set whether the logging should echo to screen.
	 * @param bool $echoLogging
	 */
	public function setEchoLogging($echoLogging)
	{
		$this->echoLogging = $echoLogging;
	}

	/**
	 * Get whether logging is echoed to screen.
	 * @return bool
	 */
	public function getEchoLogging()
	{
		return $this->echoLogging;
	}
}
