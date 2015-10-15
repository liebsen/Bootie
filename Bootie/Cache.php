<?php namespace Bootie;

/*
 * A rudimenary cache system.
 */

class Cache {

	static $cache_ext  = '.html'; //file extension
	static $cache_time     = 3600;  //Cache file expires afere these seconds (1 hour = 3600 sec)
	static $cache_folder   = '/storage/cache/'; //folder to store Cache files
	static $ignore_pages   = array('', '');
	static $ignore   = false;
	static $cache_file   = '';

	/**
	 * Determine if page was grabbed before and stored in cache
	 * if it does
	 * then Your Website Content Ends here
	 */

	static public function init($config)
	{

		if($config)
		{
			self::$cache_ext  = $config['cache_ext']?:self::$cache_ext;
			self::$cache_time     = $config['cache_time']?:self::$cache_time;
			self::$cache_folder   = $config['cache_folder']?:self::$cache_folder;
			self::$ignore_pages   = $config['ignore_pages']?:self::$ignore_pages;
		}

		return self::check();
	}

	static public function check()
	{

		$dynamic_url    = 'http://'.$_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] . $_SERVER['QUERY_STRING']; // requested dynamic page (full url)
		self::$cache_file = self::$cache_folder.md5($dynamic_url).self::$cache_ext; // construct a cache file
		self::$ignore = (in_array($dynamic_url,self::$ignore_pages))?true:false; //check if url is in ignore list

		if ( ! self::$ignore && file_exists(self::$cache_file) && time() - self::$cache_time < filemtime(self::$cache_file)) { //check Cache exist and it's not expired.
		    ob_start('ob_gzhandler'); //Turn on output buffering, "ob_gzhandler" for the compressed page with gzip.
		    readfile(self::$cache_file); //read Cache file
		    echo '<!-- cached page - '.date('l jS \of F Y h:i:s A', filemtime(self::$cache_file)).', Page : '.$dynamic_url.' -->';
		    ob_end_flush(); //Flush and turn off output buffering
		    exit(); //no need to proceed further, exit the flow.
		}

		//Turn on output buffering with gzip compression.
		ob_start('ob_gzhandler');
	}

	/**
	 * Store a page in cache
	 */

	static public function store()
	{
		if (!is_dir(self::$cache_folder)) { //create a new folder if we need to
		    mkdir(self::$cache_folder);
		}
		if(!$ignore){
		    $fp = fopen(self::$cache_file, 'w');  //open file for writing
		    fwrite($fp, ob_get_contents()); //write contents of the output buffer in Cache file
		    fclose($fp); //Close file pointer
		}

		ob_end_flush(); //Flush and turn off output buffering
	}
}