<?php namespace Bootie;

class App {

	static $filters = [];
	static $routes = [];
	static $layout = null;
	static $missing_page = 'errors/missing.php';
	static $request_methods = array('GET', 'POST', 'PUT', 'DELETE', 'OPTIONS', 'HEAD');

	/**
	 * Load database connection
	 */
	public function load_database($name = 'database')
	{
		// Load database
		$db = new \Bootie\Database(config()->$name);

		// Set default ORM database connection
		if(empty(\Bootie\ORM::$db))
		{
			\Bootie\ORM::$db = $db;
		}

		return $db;
	}

	/**
	 * Determine which controller gets the route.
	 */
	function dispatch($uri)
	{
	    foreach(self::$routes as $path => $route) 
	    {
	    	$request_method = isset($route['method']) ? $route['method'] : 'GET';

	        if(preg_match("~^$path$~", $uri, $match) AND strtoupper($request_method) == REQUEST_METHOD )
	        {
				return $this->run(self::compile($route),array_slice($match,1));
	        }
	    }

		throw new \Exception('Missing Route.');
	}

	/**
	 * Collects a route instruction
	 */
	static function route( $uri, $route = null )
	{
	    if($route)
	    {
	    	return self::$routes[$uri] = $route;	
	    } 
	}

	/**
	 * Runs a controller
	 */
	function run( $route, $match )
	{
		global $db;
		
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
			headers_sent() OR header('Content-Type: application/json',true);
			return (print json_encode($result));
		} 

		$db = null;
		return $result;
	}

	/**
	 * Compiles route data
	 */
	private function compile($route){
		return (object) [
			'class' => strstr($route['uses'],'@',true),
			'method' => substr($route['uses'],strrpos($route['uses'],'@')+1),
			'request_method' => isset($route['method']) ? $route['method'] : 'GET'
		];
	}

	/**
	 * Apply filters
	 */

	static public function filter($filter,$closure){
		return static::$filters[$filter] = $closure;
	}

	/**
	 * Display the results
	 */
	static public function view($view, $data = array(), $layout = null, $skip_layout = false){

		@extract($data);

		if( ! $layout && self::$layout)
		{
			$layout = self::$layout;
		}

		$path_views = SP . 'app/views/';
		$view = str_replace(".","/",$view) . EXT;
		$segments = array_values(array_filter(explode('/',PATH)));

    	if ( ! file_exists($path_views . $view))
    	{
    		$view = self::$missing_page;
    	}

		ob_start();

		require $path_views . $view;

		$content = ob_get_clean();

		if( $layout AND ! $skip_layout ) 
		{
			return include $path_views . 'layouts/' . $layout . EXT;	
		}

    	return print $content;
	}
}