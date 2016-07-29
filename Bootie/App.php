<?php namespace Bootie;

class App {

	static $filters = array();
	static $routes = array();
	static $shared = array();
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
	        if(preg_match("~^$path$~", strtolower($request_method).':'.$uri, $match) AND strtoupper($request_method) == REQUEST_METHOD ){
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
		if( array_key_exists($key,\Bootie\ORM::$connections))
		{
			$db = \Bootie\ORM::$connections[$key];

			if(\Bootie\ORM::$db)
			{
				\Bootie\ORM::$db = null;	
			}

			\Bootie\ORM::$db = $db;

			return $db;
		}

		// Load database
		$db = new \Bootie\Database($key);

		\Bootie\ORM::$db = $db;
		\Bootie\ORM::$connections[$key] = $db;

		return $db;
	}

	public function close_database_connections()
	{
		foreach(\Bootie\ORM::$connections as $key => $connection)
		{
			\Bootie\ORM::$connections[$key] = null;
		}
	}

	/**
	 * Collects a route instruction
	 */
	static function route( $uri, $route = null )
	{
	    if($route)
	    {
	    	$method = !empty($route['method'])?$route['method']: 'GET';
	    	return self::$routes[strtolower($method).':'.$uri] = self::compile($uri,$route);
	    } 
	}

	/**
	 * Collects a set of routes instructions
	 */
	
	static function resource( $resource, $route = null )
	{
	    if($resource)
	    {
	    	$before =!empty($route['before'])?$route['before']:null;
	    	$after = !empty($route['after'])?$route['after']:null;
			App::route($resource, [ 'uses' => $route['uses'] . '@index','before' => $before, 'after' => $after]);
			App::route($resource, [ 'uses' => $route['uses'] . '@update','before' => $before, 'after' => $after,'method' => 'post']);
			App::route($resource . '/(\d+)', [ 'uses' => $route['uses'] . '@edit','before' => $before, 'after' => $after]);
			App::route($resource . '/(\d+)', [ 'uses' => $route['uses'] . '@update','before' => $before, 'after' => $after,'method' => 'put']);
			App::route($resource . '/(\d+)', [ 'uses' => $route['uses'] . '@delete','before' => $before, 'after' => $after,'method' => 'delete']);
	    } 
	}

	/**
	 * Dispatches a controller
	 */
	function dispatch( $route, $match )
	{

		$controller = null;
		$result = null;
		
		if(strlen($route->class)){
			$controller = new $route->class;
		}

		if( ! in_array(REQUEST_METHOD, self::$request_methods) OR REQUEST_METHOD !== strtoupper($route->request_method) OR ! method_exists($controller, $route->method) AND is_callable($route->closure) === 0)
		{
			throw new \Exception('Invalid Request Method.');
		}

		if( isset($route->before) AND is_callable( $filter = self::$filters[$route->before] ))
		{
			call_user_func($filter);
		}

    	if($controller AND isset($controller::$layout))
    	{
    	 	self::$layout = $controller::$layout;
    	}
			
		if($controller AND method_exists($controller, $route->method)) {
			$result = call_user_func_array(array($controller,$route->method), $match);
		} else if(is_callable($route->closure)){
			call_user_func($route->closure);
		}

		if( isset($route->after) AND is_callable( $filter = self::$filters[$route->after] ))
		{
			call_user_func($filter);
		}

		if(AJAX_REQUEST)
		{
			return self::json($result);
		} 

		self::close_database_connections();

		return $result;
	}

	/**
	 * Compiles route data
	 */
	static private function compile($key,$route){
		return (object) array(
			'uri' => @substr($key,strrpos($key,':')),
			'closure' => $route['uses'],
			'class' => @strstr($route['uses'],'@',true),
			'method' => @substr($route['uses'],strrpos($route['uses'],'@')+1),
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
	 * Returns json
	 */
	static public function json($data = array())
	{
		if(is_array($data)) 
		{
			headers_sent() OR header('Content-Type: application/json',true);
			return print json_encode($data);
		}
		
		return $data;
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

		if( !AJAX_REQUEST AND $layout AND ! $skip_layout) 
		{
			return include $path . 'layouts/' . str_replace(".","/",$layout) . EXT;	
		}

    	return print $content;
	}
}