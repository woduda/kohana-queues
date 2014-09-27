<?php defined('SYSPATH') OR die('No direct access allowed.');
return array(
	/** Defaults settings for queue processing */
	'defaults' => array(
		'batch_size' => 100,	// limit of object taken from queue
		'timeout' => 1800,	// queue process timeout; process  is considered as "dead" after this time
		'max_retries' => (Kohana::$environment == Kohana::PRODUCTION ? 10 : 2),
		'retry_interval' => 60,	// intervals (in seconds) are geometrically increased with pow of 2 on each retry
	),
	/** Queues configs */
	'queues' => array(
		'Dummy' => array(
			/* whatever settings the queue process need
			 * default settings can be overwritten here */
			'max_retries' => 4,
			'retry_interval' => 10,
			'dummy' => 1,
		),
	),
);
