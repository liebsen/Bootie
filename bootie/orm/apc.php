<?php
/**
 * APC orm
 *
 * Provides orm result caching using APC.
 *
 * @package		AppMVC
 * @author		David Pennington
 * @copyright	(c) 2011 AppMVC Framework
 * @license		http://micromvc.com/license
 ********************************** 80 Columns *********************************
 */
namespace bootie\orm;

class apc extends \bootie\orm
{

	public static function cache_set($key, $value)
	{
		apc_store($key, $value, static::$cache);
	}


	public static function cache_get($key)
	{
		return apc_fetch($key);
	}


	public static function cache_delete($key)
	{
		return apc_delete($key);
	}


	public static function cache_exists($key)
	{
		return apc_exists($key);
	}

}

// END
