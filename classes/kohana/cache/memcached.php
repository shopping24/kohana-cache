<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Kohana Cache Memcached Driver
 *
 * @package    Kohana
 * @category   Cache
 * @author     Kohana Team
 * @copyright  (c) 2009-2010 Kohana Team
 * @license    http://kohanaphp.com/license
 */
class Kohana_Cache_Memcached extends Cache {

	/**
	 * Memcached resource
	 *
	 * @var Memcached
	 */
	protected $_memcached;

	/**
	 * Driver Options Map
	 * @var array
	 */
	protected $_options_map = array
	(
		'compression'          => Memcached::OPT_COMPRESSION,
		'serializer'           => Memcached::OPT_SERIALIZER,
		'hash'                 => Memcached::OPT_HASH,
		'prefix_key'           => Memcached::OPT_PREFIX_KEY,
		'distribution'         => Memcached::OPT_DISTRIBUTION,
		'libketama_compatible' => Memcached::OPT_LIBKETAMA_COMPATIBLE,
		'buffer_writes'        => Memcached::OPT_BUFFER_WRITES,
		'binary_protocol'      => Memcached::OPT_BINARY_PROTOCOL,
		'no_block'             => Memcached::OPT_NO_BLOCK,
		'tcp_nodelay'          => Memcached::OPT_TCP_NODELAY,
		'connect_timeout'      => Memcached::OPT_CONNECT_TIMEOUT,
		'retry_timeout'        => Memcached::OPT_RETRY_TIMEOUT,
		'send_timeout'         => Memcached::OPT_SEND_TIMEOUT,
		'recv_timeout'         => Memcached::OPT_RECV_TIMEOUT,
		'poll_timeout'         => Memcached::OPT_POLL_TIMEOUT,
		'cache_lookups'        => Memcached::OPT_CACHE_LOOKUPS,
		'server_failure_limit' => Memcached::OPT_SERVER_FAILURE_LIMIT,
	);

	/**
	 * Serializer Option Map
	 *
	 * @var array
	 */
	protected $_serializer_map = array
	(
		'php'      => Memcached::SERIALIZER_PHP,
		'json'     => Memcached::SERIALIZER_JSON,
		'igbinary' => Memcached::SERIALIZER_IGBINARY,
	);

	/**
	 * Hash Algorithm Option Map
	 *
	 * @var array
	 */
	protected $_hash_map = array
	(
		'default'  => Memcached::HASH_DEFAULT,
		'md5'      => Memcached::HASH_MD5,
		'crc'      => Memcached::HASH_CRC,
		'fnv1_64'  => Memcached::HASH_FNV1_64,
		'fnv1a_64' => Memcached::HASH_FNV1A_64,
		'fnv1_32'  => Memcached::HASH_FNV1_32,
		'fnv1a_32' => Memcached::HASH_FNV1A_32,
		'murmur'   => Memcached::HASH_MURMUR,
	);

	/**
	 * Distribution Option Map
	 * @var array
	 */
	protected $_distribution_map = array
	(
		'modula'     => Memcached::DISTRIBUTION_MODULA,
		'consistent' => Memcached::DISTRIBUTION_CONSISTENT,
	);

	private static $local_cache;
	private $_config_hash;
	
	/**
	 * Constructs the memcached object
	 *
	 * @param   array     configuration
	 * @throws  Kohana_Cache_Exception
	 */
	public function __construct(array $config)
	{
		// Check that memcached is loaded
		if ( ! extension_loaded('memcached'))
		{
			throw new Kohana_Cache_Exception('Memcached extension is not loaded');
		}

		parent::__construct($config);

		// Check whether this is a persistent connection
		if ($config['persistent'] == FALSE)
		{
			// Setup a non-persistent memcached connection
			$this->_memcached = new Memcached;
		}
		else
		{
			// Setup a persistent memcached connection
			$this->_memcached = new Memcached($this->_config['persistent_id']);
		}

		// Load servers from configuration
		$servers = Arr::get($this->_config, 'servers', NULL);

		if ( ! $servers)
		{
			// Throw exception if no servers found in configuration
			throw new Kohana_Cache_Exception('No Memcache servers defined in configuration');
		}

		// Add memcache servers
		foreach($servers as $server)
		{
			if ( ! $this->_memcached->addServer($server['host'], $server['port'], $server['weight']))
			{
				throw new Kohana_Cache_Exception('Could not connect to memcache host at \':host\' using port \':port\'', array(':host' => $server['host'], ':port' => $server['port']));
			}
			// set sasl authorization
			if (array_key_exists('sasl_username',$server) && array_key_exists('sasl_password',$server))
			{
				$this->_memcached->configureSasl($server['sasl_username'], $server['sasl_password']);
			}
		}
		
		// Load memcached options from configuration
		$options = Arr::get($this->_config, 'options', NULL);

		// Make sure there are options to set
		if ($options != NULL)
		{
			// Set the options
			foreach($options as $key => $value)
			{
				// Special cases for a few options
				switch ($key)
				{
					case 'serializer':
						$value = $this->_serializer_map[$value];
					break;

					case 'hash':
						$value = $this->_hash_map[$value];
					break;

					case 'distribution':
						$value = $this->_distribution_map[$value];
					break;

					case 'prefix_key':
						// Throw exception is key prefix is greater than 128 characters
						if (strlen($value) > 128)
						{
							throw new Kohana_Cache_Exception('Memcached prefix key cannot exceed 128 characters');
						}
					break;

					default:
					break;
				}

				$this->_memcached->setOption($this->_options_map[$key], $value);
			}
		}
		
		$this->_config_hash = md5(serialize($config));
	}

	public function __destruct()
	{
		if(is_array(self::$local_cache[$this->_config_hash]))
		{
			foreach (self::$local_cache[$this->_config_hash] as $id => $value){
				if (isset($value['lifetime'])){
					$this->_memcached->set($id, $value['data'], $value['lifetime']);
				}
			}
			self::$local_cache[$this->_config_hash] = null;
		}
	}
	
	/**
	 * Retrieve a value based on an id
	 *
	 * @param   string   id
	 * @param   mixed    default [Optional] Default value to return if id not found
	 * @return  mixed
	 */
	public function get($id, $default = NULL)
	{		
		$id = $this->_sanitize_id($id);
		if (isset(self::$local_cache[$this->_config_hash][$id]))
		{
			if(self::$local_cache[$this->_config_hash][$id]['data'] === FALSE)
			return $default;
				
			return self::$local_cache[$this->_config_hash][$id]['data'];
		}
		
		// debug
		if(isset(Request::initial()->cache_count_get)) {
			Request::initial()->cache_count_get += 1;
		}
		
		$res = $this->_memcached->get($this->_sanitize_id($id));
		self::$local_cache[$this->_config_hash][$id] = array('data' => $res);
		
		
		return $res === FALSE ? $default : $res;
	}

	/**
	 * Set a value based on an id.
	 *
	 * @param   string   id
	 * @param   mixed    data
	 * @param   integer  lifetime [Optional]
	 * @return  boolean
	 */
	public function set($id, $data, $lifetime = NULL)
	{
		if ($lifetime == NULL)
		{
			// Normalise the lifetime
			$lifetime = 0;
		}
		else
		{
			$lifetime += time();
		}	
		
		$id = $this->_sanitize_id($id);
		
		// debug
		if(isset(Request::initial()->cache_count_set)) {
			Request::initial()->cache_count_set += 1;
		}
		
		
		if(!isset(self::$local_cache[$this->_config_hash][$id]) ||
		(isset(self::$local_cache[$this->_config_hash][$id]) && serialize($data) !== serialize(self::$local_cache[$this->_config_hash][$id]['data']))
		)
		{
			self::$local_cache[$this->_config_hash][$id] = array('data' => $data, 'lifetime' => $lifetime);
		}
	}

	/**
	 * Delete a cache entry based on id
	 *
	 * @param   string   id
	 * @param   integer  timeout [Optional]
	 * @return  boolean
	 */
	public function delete($id, $timeout = 0)
	{
		return $this->_memcached->delete($this->_sanitize_id($id), $timeout);
	}

	/**
	 * Delete all cache entries
	 *
	 * @return  boolean
	 */
	public function delete_all()
	{
		return $this->_memcached->flush();
	}
	
	/**
	 * retrieves return code for the last operation
	 *
	 * @return int
	 */
	public function get_result_code()
	{
		return $this->_memcached->getResultCode();
	}	
}
