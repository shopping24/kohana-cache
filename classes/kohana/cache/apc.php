<?php defined('SYSPATH') or die('No direct script access.');
/**
 * [Kohana Cache](api/Kohana_Cache) APC driver. Provides an opcode based
 * driver for the Kohana Cache library.
 * 
 * ### Configuration example
 * 
 * Below is an example of an _apc_ server configuration.
 * 
 *     return array(
 *          'apc' => array(                          // Driver group
 *                  'driver'         => 'apc',         // using APC driver
 *           ),
 *     )
 * 
 * In cases where only one cache group is required, if the group is named `default` there is
 * no need to pass the group name when instantiating a cache instance.
 * 
 * #### General cache group configuration settings
 * 
 * Below are the settings available to all types of cache driver.
 * 
 * Name           | Required | Description
 * -------------- | -------- | ---------------------------------------------------------------
 * driver         | __YES__  | (_string_) The driver type to use
 * 
 * ### System requirements
 * 
 * *  Kohana 3.0.x
 * *  PHP 5.2.4 or greater
 * *  APC PHP extension
 * 
 * @package    Kohana/Cache
 * @category   Base
 * @author     Kohana Team
 * @copyright  (c) 2009-2010 Kohana Team
 * @license    http://kohanaphp.com/license
 */
class Kohana_Cache_Apc extends Cache {

	
	private static $local_cache;
	private $_config_hash;
	
	/**
	 * Check for existence of the APC extension This method cannot be invoked externally. The driver must
	 * be instantiated using the `Cache::instance()` method.
	 *
	 * @param  array     configuration
	 * @throws Kohana_Cache_Exception
	 */
	protected function __construct(array $config)
	{
		if ( ! extension_loaded('apc'))
		{
			throw new Kohana_Cache_Exception('PHP APC extension is not available.');
		}
		$this->_config_hash = md5(serialize($config));
		parent::__construct($config);
	}
	
	public function __destruct()
	{
		if(Request::is_valid() AND is_array(self::$local_cache[$this->_config_hash]))
		{
			foreach (self::$local_cache[$this->_config_hash] as $id => $value){
				if (isset($value['lifetime'])){
					apc_store($id, $value['data'], $value['lifetime']);
				}
			}
			self::$local_cache[$this->_config_hash] = null;
		}
	}

	/**
	 * Retrieve a cached value entry by id.
	 * 
	 *     // Retrieve cache entry from apc group
	 *     $data = Cache::instance('apc')->get('foo');
	 * 
	 *     // Retrieve cache entry from apc group and return 'bar' if miss
	 *     $data = Cache::instance('apc')->get('foo', 'bar');
	 *
	 * @param   string   id of cache to entry
	 * @param   string   default value to return if cache miss
	 * @return  mixed
	 * @throws  Kohana_Cache_Exception
	 */
	public function get($id, $default = NULL, $group = NULL)
	{		
		$id = $this->_sanitize_id($id);
		
		if ($group != NULL)
		{
			if (isset(self::$local_cache[$this->_config_hash][$group]))
			{
				if(self::$local_cache[$this->_config_hash][$group]['data'] === FALSE)
				{
					return $default;
				}
					
				$res = self::$local_cache[$this->_config_hash][$group]['data'];
			}
			else
			{
				$res = apc_fetch($group);
				self::$local_cache[$this->_config_hash][$group] = array('data' => $res);
				
				// debug
                //DebugInfo::$cache_count_get += 1;
			}
 			
 			
			if ($res !== FALSE AND isset($res[$id]))
			{
				return $res[$id];
			}

			return $default;
		}

		if (isset(self::$local_cache[$this->_config_hash][$id]))
		{
			if(self::$local_cache[$this->_config_hash][$id]['data'] === FALSE)
			return $default;
				
			return self::$local_cache[$this->_config_hash][$id]['data'];
		}
		$res = apc_fetch($id);

		// debug
        //DebugInfo::$cache_count_get += 1;

		self::$local_cache[$this->_config_hash][$id] = array('data' => $res);

		return $res === FALSE ? $default : $res;

		
		
	}

	/**
	 * Set a value to cache with id and lifetime
	 * 
	 *     $data = 'bar';
	 * 
	 *     // Set 'bar' to 'foo' in apc group, using default expiry
	 *     Cache::instance('apc')->set('foo', $data);
	 * 
	 *     // Set 'bar' to 'foo' in apc group for 30 seconds
	 *     Cache::instance('apc')->set('foo', $data, 30);
	 *
	 * @param   string   id of cache entry
	 * @param   string   data to set to cache
	 * @param   integer  lifetime in seconds
	 * @return  boolean
	 */
	public function set($id, $data, $lifetime = NULL, $group = NULL)
	{
		
		$id = $this->_sanitize_id($id);
		if ($lifetime === NULL)
		{
			$lifetime = Arr::get($this->_config, 'default_expire', Cache::DEFAULT_EXPIRE);
		}

		// debug
		//DebugInfo::$cache_count_set += 1;
		
		$cache = &self::$local_cache[$this->_config_hash];
		
		if ($group != NULL)
		{
			if (!isset($cache[$group]))
			{
				$cache[$group] = array('data' => array(), 'lifetime' => $lifetime);
			}
			
			if (!isset($cache[$group]['lifetime']) OR $cache[$group]['lifetime'] < $lifetime)
			{
				$cache[$group]['lifetime'] = $lifetime;
				
			}			
			$cache = &$cache[$group]['data'];
			if(!isset($cache[$id]) OR (serialize($data) !== serialize($cache[$id])))
			{
				$cache[$id] = $data;
			}
		}
		else {
			if(!isset($cache[$id]) OR (serialize($data) !== serialize($cache[$id]['data'])))
			{
				$cache[$id] = array('data' => $data, 'lifetime' => $lifetime);
			}
		}


	}

	/**
	 * Delete a cache entry based on id
	 * 
	 *     // Delete 'foo' entry from the apc group
	 *     Cache::instance('apc')->delete('foo');
	 *
	 * @param   string   id to remove from cache
	 * @return  boolean
	 */
	public function delete($id)
	{
		return apc_delete($this->_sanitize_id($id));
	}

	/**
	 * Delete all cache entries.
	 * 
	 * Beware of using this method when
	 * using shared memory cache systems, as it will wipe every
	 * entry within the system for all clients.
	 * 
	 *     // Delete all cache entries in the apc group
	 *     Cache::instance('apc')->delete_all();
	 *
	 * @return  boolean
	 */
	public function delete_all()
	{
		return apc_clear_cache('user');
	}
}
