<?php namespace Bootie;

class App {

	static $filters = array();
	static $routes = array();
	static $shared = array();
	static $connections = array();
	static $layout = null;
	static $mime_allow = array('html','xml');
	static $missing_page = 'errors/missing.php';
	static $request_methods = array('GET', 'POST', 'PUT', 'DELETE', 'OPTIONS', 'HEAD');

	/**
	 * Determine which controller gets the route.
	 */
	function run($uri)
	{
	    foreach(self::$routes as $path => $route) 
	    {
	    	$request_method = $route->request_method ? : 'GET';

	        if(preg_match("~^$path$~", $uri, $match) AND strtoupper($request_method) == REQUEST_METHOD )
	        {
				return $this->dispatch($route,array_slice($match,1));
	        }
	    }

		throw new \Exception('Missing Route.');
	}

	/**
	 * Load database connection
	 */
	public function load_database($key = 'default')
	{
		if( array_key_exists($key,self::$connections))
		{
			$db = self::$connections[$key];
			\Bootie\ORM::$db = $db;

			return $db;
		}

		// Load database
		$db = new \Bootie\Database(config()->database['connections'][$key]);
		\Bootie\ORM::$db = $db;

		self::$connections[$key] = $db;

		return $db;
	}

	public function close_database_connections()
	{
		foreach(self::$connections as $key => $connection)
		{
			self::$connections[$key] = null;
		}
	}

	/**
	 * Collects a route instruction
	 */
	static function route( $uri, $route = null )
	{
	    if($route)
	    {
	    	return self::$routes[$uri] = self::compile($route);
	    } 
	}

	/**
	 * Dispatches a controller
	 */
	function dispatch( $route, $match )
	{
		$controller = new $route->class;

		if( ! in_array(REQUEST_METHOD, self::$request_methods) OR REQUEST_METHOD !== strtoupper($route->request_method) OR ! method_exists($controller, $route->method))
		{
			throw new \Exception('Invalid Request Method.');
		}
		
		if( isset($route->before) AND is_callable( $filter = self::$filters[$route->before] ))
		{
			call_user_func($filter);
		}

    	if(isset($controller::$layout))
    	{
    	 	self::$layout = $controller::$layout;
    	}
				
		$result = call_user_func_array([$controller,$route->method], $match);

		if( isset($route->after) AND is_callable( $filter = self::$filters[$route->after] ))
		{
			call_user_func($filter);
		}

		if(AJAX_REQUEST)
		{
			return self::ajax($result);
		} 

		self::close_database_connections();

		return $result;
	}

	/**
	 * Compiles route data
	 */
	private function compile($route){
		return (object) array(
			'class' => strstr($route['uses'],'@',true),
			'method' => substr($route['uses'],strrpos($route['uses'],'@')+1),
			'request_method' => isset($route['method']) ? strtoupper($route['method']) : 'GET',
			'before' => isset($route['before'])?$route['before']:null,
			'after' => isset($route['after'])?$route['after']:null,
		);
	}

	/**
	 * Apply filters
	 */
	static public function filter($filter,$closure)
	{
		return static::$filters[$filter] = $closure;
	}

	/**
	 * Collects data to be shared through all application layers
	 */
	static function share( $share, $data = null )
	{
		return static::$shared[$share] = $data;
	}

	/**
	 * Returns ajax
	 */
	static public function ajax($data = array())
	{
		if(is_array($data)) headers_sent() OR header('Content-Type: application/json',true);
		return json_encode($data);
	}

	/**
	 * Display the results
	 */
	static public function view($view, $data = array(), $layout = null, $skip_layout = false)
	{

		@extract($data);
		@extract(static::$shared);

		$ext = pathinfo($view, PATHINFO_EXTENSION);
		$path = SP . 'app/views/';

		$view = str_replace(".","/",$view);

		if( ! $layout && self::$layout)
		{
			$layout = self::$layout;
		}

    	if( ! in_array( $ext, self::$mime_allow ))
    	{
    		$view = $view . EXT;
    	}
    	else
    	{
    		$view = str_replace("/$ext",".$ext",$view);
    	}

    	if ( ! file_exists($path . $view))
    	{
    		$view = self::$missing_page;
    	}

		ob_start();

		require $path . $view;

		$content = ob_get_clean();

		if( $layout AND ! $skip_layout ) 
		{
			return include $path . 'layouts/' . $layout . EXT;	
		}

    	return print $content;
	}
}