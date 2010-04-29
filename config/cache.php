<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Configuration array for Kohana Cache. The configuration is
 * separated into groups. This allows the use of multiple
 * instances of the same cache engine type- allowing two
 * unique instances of Xcache.
 *
 * The default group is highly recommended. Without its
 * presence, a configuration group will need to be defined
 * whenever a new instance is requested.
 *
 * Each configuration must have the following properties :-
 *  - {string}        driver          the Kohana_Cache driver to use
 *
 * Optional setting
 *  - {int}           default_expire  the default cache lifetime
 *
 * Each driver requires additional unique settings
 *
 * MEMCACHE
 *  [required]
 *  - {array}         servers         an array of available servers
 *    - {string}      host            the hostname of the memcache server
 *    - {int}         port            the port memcache is running on
 *    - {bool}        persistent      maintain a persistent connection
 *  [optional]
 *  - {bool}          compression     use compression
 *
 *  SQLITE
 *  [required]
 *  - {string}        database        the location of the db
 *  - {string}        schema          the initialisation schema
 *
 *  FILE
 *  [optional]
 *  - {string}        cache_dir       the location of the cache directory
 */
return array
(
	'default'  => array
	(
		'driver'             => 'file',
		'cache_dir'          => 'cache/.kohana_cache',
		'default_expire'     => 3600,
	),
	// 'memcache' => array
	// (
	// 	'driver'             => 'memcache',
	// 	'default_expire'     => 3600,
	// 	'compression'        => FALSE,              // Use Zlib compression (can cause issues with integers)
	// 	'servers'            => array
	// 	(
	// 		array
	// 		(
	// 			'host'             => 'localhost',  // Memcache Server
	// 			'port'             => 11211,        // Memcache port number
	// 			'persistent'       => FALSE,        // Persistent connection
	// 		),
	// 	),
	// ),
	// 'apc'      => array
	// (
	// 	'driver'             => 'apc'
	// )
	// 'sqlite'   => array
	// (
	// 	'driver'             => 'sqlite',
	// 	'default_expire'     => 3600,
	// 	'database'           => APPPATH.'cache/kohana-cache.sql3',
	// 	'schema'             => 'CREATE TABLE caches(id VARCHAR(127) PRIMARY KEY, tags VARCHAR(255), expiration INTEGER, cache TEXT)',
	// ),
	// 'xcache'   => array
	// (
	// 	'driver'             => 'xcache'
	// ),
	// 	'file'    => array
	// (
	// 	'driver'             => 'file',
	// 	'cache_dir'          => 'cache/.kohana_cache',
	// ),
	//'memcached' => array
	//(
	//	'driver'             => 'memcached',
	//	'default_expire'     => 3600,         // Default amount of time a key will be cached if none is provided
	//	'persistent'         => FALSE,        // Make persisten connections
	//	'persistent_id'      => NULL,         // ID given to persistent connection
	//	'options'            => array
	//	(
	//		'compression'          => FALSE,        // Use compression
	//		'serializer'           => 'php',        // Data serializer
	//		'hash'                 => 'default',    // Hash to use on item keys
	//		'prefix_key'           => '',           // Automatic prefix to item keys
	//		'distribution'         => 'modula',     // Method of distributing item keys
	//		'libketama_compatible' => TRUE,         // Compatibility with libketama-like behavior
	//		'buffer_writes'        => FALSE,        // Enables or disables buffered I/O
	//		'binary_protocol'      => FALSE,        // Enable the use of the binary protocol
	//		'no_block'             => FALSE,        // Enables or disables asynchronous I/O
	//		'tcp_nodelay'          => FALSE,        // Enables or disables the no-delay feature for connecting sockets
	//		'connect_timeout'      => 1000,         // In non-blocking mode this set the value of the timeout during socket connection, in milliseconds
	//		'retry_timeout'        => 0,            // The amount of time, in seconds, to wait until retrying a failed connection attempt
	//		'send_timeout'         => 0,            // Socket sending timeout, in microseconds
	//		'recv_timeout'         => 0,            // Socket reading timeout, in microseconds
	//		'poll_timeout'         => 1000,         // Timeout for connection polling, in milliseconds
	//		'cache_lookups'        => FALSE,        // Enables or disables caching of DNS lookups
	//		'server_failure_limit' => 0,            // Specifies the failure limit for server connection attempts
	//	),
	//	'servers'            => array
	//	(
	//		array
	//		(
	//			'host'       => 'localhost',  // Memcache Server
	//			'port'       => 11211,        // Memcache port number
	//			'weight'     => 0             // Server weight
	//		),
	//	),
	//),
);