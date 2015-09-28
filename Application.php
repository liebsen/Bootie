<?php 

include SP . 'config/routes.php';

class App {

	static $ajax = 0;
	static $filters = [];
	static $routes = [];
	static $layout = "default";
	static $missing_page = 'errors/missing.php';
	static $request_methods = array('GET', 'POST', 'PUT', 'DELETE', 'OPTIONS', 'HEAD');

	/**
	 * Load database connection
	 */
	public function load_database($name = 'database')
	{
		// Load database
		$db = new \App\Database(config()->$name);

		// Set default ORM database connection
		if(empty(\App\ORM::$db))
		{
			\App\ORM::$db = $db;
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

				return $this->run($route,$match);

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

		$route = self::compile($route);

		if( ! in_array(REQUEST_METHOD, self::$request_methods) OR REQUEST_METHOD !== strtoupper($route->request_method) OR ! is_file ($route->path))
		{
			throw new \Exception('Invalid Request Method.');
		}
		
		if( isset($route->before) AND is_callable( $filter = self::$filters[$route->before] ))
		{
			call_user_func($filter);
		}

		/* hydratation */

		require $route->path;

    	$controller = new $route->namespace;

    	if ( ! method_exists($controller, $route->method))
    	{
    		throw new \Exception('Invalid Class Method.');
    	}

    	$this->load_database();

    	if(isset($controller::$layout))
    	{
    	 	self::$layout = $controller::$layout;
    	}
				
		$result = call_user_func_array([$route->namespace,$route->method], $match);

		if( isset($route->after) AND is_callable( $filter = self::$filters[$route->after] ))
		{
			call_user_func($filter);
		}

		if(AJAX_REQUEST)
		{
			headers_sent() OR header('Content-Type: application/json',true);
			return (print json_encode($result));
		} 

		return $result;
	}

	/**
	 * Compiles route data
	 */
	private function compile($route){
		return (object) [
			'path' => SP . strtolower(str_replace("\\",DS,substr($route['uses'],0,strrpos($route['uses'],'\\')+1))) . strstr(substr($route['uses'],strrpos($route['uses'],'\\')+1),'@',true) . EXT,
			'namespace' => strstr($route['uses'],'@',true),
			'class' => strstr(substr($route['uses'],strrpos($route['uses'],'\\')+1),'@',true),
			'method' => substr($route['uses'],strrpos($route['uses'],'@')+1),
			'before' => isset($route['before']) ? $route['before'] : null,
			'after' => isset($route['after']) ? $route['after'] : null,
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
	static public function view($view, $data = array(), $layout = null){

		@extract($data);

		$segments = array_values(array_filter(explode('/',PATH)));
		$path_views = SP . 'app/views/';
		$view = str_replace(".","/",$view) . EXT;

    	if ( ! file_exists($path_views . $view)){
    		$view = self::$missing_page;
    	}

		ob_start();

		require $path_views . $view;

		$content = ob_get_clean();

    	include $path_views . 'layouts/' . ($layout ? $layout : self::$layout) . EXT;

    	return '';
	}
}