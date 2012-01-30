<?php

class wp_logger {
	/**
	 * properties for the Logger
	 */
	private $logfile = 'sugarcrm';
	private $ext = '.log';
	private $dateFormat = '%c';
	private $logSize = '10MB';
	private $maxLogs = 10;
	private $filesuffix = "";
	private $log_dir = '.';

	
	/**
	 * used for config screen
	 */
	public static $filename_suffix = array(
	    "%m_%Y"    => "Month_Year", 
	    "%w_%m"    => "Week_Month",
	    "%m_%d_%y" => "Month_Day_Year",
	    );
	
	/**
	 * Let's us know if we've initialized the logger file
	 */
    private $initialized = false;
    
    /**
     * Logger file handle
     */
    private $fp = false;
    
    public function __get(
        $key
        )
    {
        return $this->$key;
    }
	
    /**
     * Used by the diagnostic tools to get Logger log file information
     */
    public function getLogFileNameWithPath()
    {
        return $this->full_log_file;
    }
	
    /**
     * Used by the diagnostic tools to get Logger log file information
     */
    public function getLogFileName()
    {
        return ltrim($this->full_log_file, "./");
    }
    
    /**
     * Constructor
     *
     * Reads the config file for logger settings
     */
 /**   public function __construct() 
    {
        $this->ext = '.log';
        $this->logfile = 'wpr';
        $this->dateFormat = '%c';
        $this->logSize = '10MB';
        $this->maxLogs = 10;
        $this->filesuffix = '%m_%Y';
        //$log_dir = $config->get('log_dir' , $this->log_dir); 
        $this->log_dir = logger_manager::$log_dir;
        $this->_doInitialization();
        logger_manager::setLogger('default','wp_logger');
	}
	
	
	 * Handles the Logger initialization
	 */
    private function _doInitialization() 
    {
        $this->full_log_file = $this->log_dir . $this->logfile . $this->ext;
        $this->initialized = $this->_fileCanBeCreatedAndWrittenTo();
        $this->rollLog();
    }

    /**
	 * Checks to see if the Logger file can be created and written to
	 */
    private function _fileCanBeCreatedAndWrittenTo() 
    {
        $this->_attemptToCreateIfNecessary();
        return file_exists($this->full_log_file) && is_writable($this->full_log_file);
    }

    /**
	 * Creates the Logger file if it doesn't exist
	 */
    private function _attemptToCreateIfNecessary() 
    {
        if (file_exists($this->full_log_file)) {
            return;
        }
        @touch($this->full_log_file);
    }
    
    /**
     * Main method for handling logging a message to the logger
     *
     * @param string $level logging level for the message
     * @param string $message
     */
	public function log(
	    $level,
	    $message
	    ) 
	{
        if (!$this->initialized) {
            return;
        }
		//lets get the current user id or default to -none- if it is not set yet
		$userID = (!empty($GLOBALS['current_user']->id))?$GLOBALS['current_user']->id:'-none-';

		//if we haven't opened a file pointer yet let's do that
		if (! $this->fp)$this->fp = fopen ($this->full_log_file , 'a' );

		
		// change to a string if there is just one entry
	    if ( is_array($message) && count($message) == 1 )
	        $message = array_shift($message);
	    // change to a human-readable array output if it's any other array
	    if ( is_array($message) )
		    $message = print_r($message,true);
		
		//write out to the file including the time in the dateFormat the process id , the user id , and the log level as well as the message
		fwrite($this->fp, 
		    strftime($this->dateFormat) . ' [' . getmypid () . '][' . strtoupper($level) . '] ' . $message . "\n" 
		    );
	}
	
	/**
	 * rolls the logger file to start using a new file
	 */
	private function rollLog(
	    $force = false
	    ) 
	{
        if (!$this->initialized || empty($this->logSize)) {
            return;
        }
		// lets get the number of megs we are allowed to have in the file
		$megs = substr ( $this->logSize, 0, strlen ( $this->logSize ) - 2 );
		//convert it to bytes
		$rollAt = ( int ) $megs * 1024 * 1024;
		//check if our log file is greater than that or if we are forcing the log to roll
		if ($force || filesize ( $this->full_log_file ) >= $rollAt) {
			//now lets move the logs starting at the oldest and going to the newest
			for($i = $this->maxLogs - 2; $i > 0; $i --) {
				if (file_exists ( $this->log_dir . $this->logfile . $i . $this->ext )) {
					$to = $i + 1;
					$old_name = $this->log_dir . $this->logfile . $i . $this->ext;
					$new_name = $this->log_dir . $this->logfile . $to . $this->ext;
					//nsingh- Bug 22548  Win systems fail if new file name already exists. The fix below checks for that.
					//if/else branch is necessary as suggested by someone on php-doc ( see rename function ).
					sugar_rename($old_name, $new_name);

					//rename ( $this->logfile . $i . $this->ext, $this->logfile . $to . $this->ext );
				}
			}
			//now lets move the current .log file
			sugar_rename ($this->full_log_file, $this->log_dir . $this->logfile . '1' . $this->ext);

		}
	}
	
	/**
	 * Destructor
	 *
	 * Closes the Logger file handle
     */
	public function __destruct() 
	{
		if ($this->fp)
			fclose($this->fp);
	}
}
