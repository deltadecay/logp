<?php

namespace logp;

require_once(__DIR__."/logger.php");

class FileLogger extends Logger 
{

	protected $numLogFileCopiesToKeep = 5;
	protected $fileSizeLimit = 1048576; // 1 Mb
	protected $logFileName = 'logger.log';
	protected $rows = [];

	protected $maxRowsToCache = 100;
	protected $maxMessageLength = 1000;
    protected $logTime = true;

	public function __construct($logFileName) 
    {
		$this->logFileName = $logFileName;
        $this->setEchoLogging(false);
		register_shutdown_function([$this, '__destruct']);
	}

	public function __destruct() 
    {
		$this->save();
	}

	/**
	 * Save the log to the log file.
	 */
	public function save() 
    {
		if (count($this->rows) == 0)
			return;

		if (is_file($this->logFileName)) 
        {
            clearstatcache(true, $this->logFileName);
			$fileSize = filesize($this->logFileName);
			if ($fileSize && ($fileSize > $this->fileSizeLimit)) 
            {

				for ($num=$this->numLogFileCopiesToKeep; $num>=1; $num--) 
                {
					$backupFile = $this->logFileName.".".$num;
					$prevBackupFile = $this->logFileName.".".($num + 1);
					if (is_file($backupFile)) 
                    {
						if ($num == $this->numLogFileCopiesToKeep) 
                        {
							// It's the last file, we won't keep it. It will be deleted
							unlink($backupFile);
						} 
                        else 
                        {
							rename($backupFile, $prevBackupFile);
						}
					}
				}
				// The existing file is larger than limit, we rename it to xxxx.1
				rename($this->logFileName, $this->logFileName.".1");
			}
		}

		$fp = fopen($this->logFileName, "a+");
		if ($fp) 
        {
			foreach ($this->rows as $row) 
            {
				$formattedTime = date(\DateTimeInterface::ISO8601, $row['time']);
                $tstr = '';
                if($this->logTime)
                {
                    $tstr .= $formattedTime.' ';
                }
				fwrite($fp, $tstr.$row['type'].': '.$row['message']."\n");
			}
			fclose($fp);
			unset($this->rows);
			$this->rows = [];
		}
	}


	public function internal_log($message, $type) 
    {
		parent::internal_log($message, $type);

		// Respect the set max length of the message. Anything longer will be cut and (...) added.
		if (strlen($message) > $this->maxMessageLength)
        {
			$message = substr($message, 0, $this->maxMessageLength-3)."...";
		}
		$this->rows[] = ['message' => $message, 'time' => time(), 'type' => $type];

		if (count($this->rows) > $this->maxRowsToCache) 
        {
			$this->save();
		}
	}

	/**
	 * Set the log file size limit in bytes.
	 * @param int $fileSizeLimit The file size in bytes.
	 */
	public function setFileSizeLimit($fileSizeLimit)
	{
		if (!is_numeric($fileSizeLimit)) 
        {
			$fileSizeLimit = 1048576;
		}
		$fileSizeLimit = (int)$fileSizeLimit;

		if ($fileSizeLimit < 512) 
        {
			$fileSizeLimit = 512;
		}

		$this->fileSizeLimit = $fileSizeLimit;
	}

	/**
	 * Get the size limit for a log file. Once it is above, the next time the log is saved the log is renamed
	 * and a new empty log file is created.
	 * @return int The log file size limit in bytes.
	 */
	public function getFileSizeLimit()
	{
		return $this->fileSizeLimit;
	}

	/**
	 * Get the name of the log file.
	 * @return string Name of the log file
	 */
	public function getLogFileName()
	{
		return $this->logFileName;
	}


	/**
	 * Set the number of historical log files to keep.
	 * @param int $numLogFileCopiesToKeep
	 */
	public function setNumLogFileCopiesToKeep($numLogFileCopiesToKeep)
	{
		if (!is_numeric($numLogFileCopiesToKeep)) 
        {
			$numLogFileCopiesToKeep = 0;
		}
		$numLogFileCopiesToKeep = (int)$numLogFileCopiesToKeep;

		if ($numLogFileCopiesToKeep < 0) 
        {
			$numLogFileCopiesToKeep = 0;
		}
		if ($numLogFileCopiesToKeep > 100) 
        {
			$numLogFileCopiesToKeep = 100;
		}
		$this->numLogFileCopiesToKeep = $numLogFileCopiesToKeep;
	}

	/**
	 * Get the number of historical log files that is being kept.
	 * @return int Number of historical log files
	 */
	public function getNumLogFileCopiesToKeep()
	{
		return $this->numLogFileCopiesToKeep;
	}



	/**
	 * Set the max number of rows to cache before they are saved to file.
	 * If value is zero, then no caching is done, every call to the log functions will result in immediate
	 * save to file. This is not recommended as it will result in lots of file writing.
	 * @param int $maxRowsToCache
	 */
	public function setMaxRowsToCache($maxRowsToCache)
	{
		if (!is_numeric($maxRowsToCache)) 
        {
			$maxRowsToCache = 0;
		}
		$maxRowsToCache = (int)$maxRowsToCache;

		if ($maxRowsToCache < 0) 
        {
			$maxRowsToCache = 0;
		}
		$this->maxRowsToCache = $maxRowsToCache;
	}

	/**
	 * Get the max number of rows that are being cached before saved to file.
	 * @return int Max rows to cache
	 */
	public function getMaxRowsToCache()
	{
		return $this->maxRowsToCache;
	}

	/**
	 * Set the max message length to log. Message longer will be truncated and an ellipsis added (...)
	 * @param int $maxMessageLength max message length
	 */
	public function setMaxMessageLength($maxMessageLength)
	{
		if (!is_numeric($maxMessageLength)) 
        {
			$maxMessageLength = 1000;
		}
		$maxMessageLength = (int)$maxMessageLength;

		if ($maxMessageLength < 40) 
        {
			$maxMessageLength = 40;
		}
		$this->maxMessageLength = $maxMessageLength;
	}

	/**
	 * Get the max message length being logged.
	 * @return int Max message length
	 */
	public function getMaxMessageLength()
	{
		return $this->maxMessageLength;
	}


    /**
     * Get if time is logged
     * @return bool True if time is logged, false otherwise.
     */
    public function getLogTime()
    {
        return $this->logTime;
    }

    /**
     * Set if time should be logged. Default is true.
     * @param bool $logTime
     */
    public function setLogTime($logTime)
    {
        $this->logTime = !!$logTime;
    }
}

