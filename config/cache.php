<?php defined('SYSPATH') or die('No direct script access.');
return array
(
	'memcache' => array
	(
		'driver'             => 'memcache',
		'default_expire'     => 3600,
		'compression'        => FALSE,              // Use Zlib compression (can cause issues with integers)
		'servers'            => array
		(
			array
			(
				'host'             => 'localhost',  // Memcache Server
				'port'             => 11211,        // Memcache port number
				'persistent'       => FALSE,        // Persistent connection
			),
		),
		'default_expire'     => 3600,
	),
	'memcached' => array
	(
		'driver'             => 'memcached',
		'default_expire'     => 3600,         // Default amount of time a key will be cached if none is provided
		'persistent'         => FALSE,        // Make persisten connections
		'persistent_id'      => NULL,         // ID given to persistent connection
		'options'            => array
		(
			'compression'          => FALSE,        // Use compression
			'serializer'           => 'php',        // Data serializer
			'hash'                 => 'default',    // Hash to use on item keys
			'prefix_key'           => '',           // Automatic prefix to item keys
			'distribution'         => 'modula',     // Method of distributing item keys
			'libketama_compatible' => TRUE,         // Compatibility with libketama-like behavior
			'buffer_writes'        => FALSE,        // Enables or disables buffered I/O
			'binary_protocol'      => FALSE,        // Enable the use of the binary protocol
			'no_block'             => FALSE,        // Enables or disables asynchronous I/O
			'tcp_nodelay'          => FALSE,        // Enables or disables the no-delay feature for connecting sockets
			'connect_timeout'      => 1000,         // In non-blocking mode this set the value of the timeout during socket connection, in milliseconds
			'retry_timeout'        => 0,            // The amount of time, in seconds, to wait until retrying a failed connection attempt
			'send_timeout'         => 0,            // Socket sending timeout, in microseconds
			'recv_timeout'         => 0,            // Socket reading timeout, in microseconds
			'poll_timeout'         => 1000,         // Timeout for connection polling, in milliseconds
			'cache_lookups'        => FALSE,        // Enables or disables caching of DNS lookups
			'server_failure_limit' => 0,            // Specifies the failure limit for server connection attempts
		),
	),
	'memcachetag' => array
	(
		'driver'             => 'memcachetag',
		'default_expire'     => 3600,
		'compression'        => FALSE,              // Use Zlib compression (can cause issues with integers)
		'servers'            => array
		(
			array
			(
				'host'             => 'localhost',  // Memcache Server
				'port'             => 11211,        // Memcache port number
				'persistent'       => FALSE,        // Persistent connection
				'weight'           => 1,
				'timeout'          => 1,
				'retry_interval'   => 15,
				'status'           => TRUE,
			),
		),
		'instant_death'      => TRUE,
	),
	'apc'      => array
	(
		'driver'             => 'apc',
		'default_expire'     => 3600,
	),
	'sqlite'   => array
	(
		'driver'             => 'sqlite',
		'default_expire'     => 3600,
		'database'           => APPPATH.'cache/kohana-cache.sql3',
		'schema'             => 'CREATE TABLE caches(id VARCHAR(127) PRIMARY KEY, tags VARCHAR(255), expiration INTEGER, cache TEXT)',
	),
	'eaccelerator'           => array
	(
		'driver'             => 'eaccelerator',
	),
	'xcache'   => array
	(
		'driver'             => 'xcache',
		'default_expire'     => 3600,
	),
	'file'    => array
	(
		'driver'             => 'file',
		'cache_dir'          => APPPATH.'cache',
		'default_expire'     => 3600,
	)
);