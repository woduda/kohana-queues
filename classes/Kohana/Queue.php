<?php defined('SYSPATH') OR die('No direct script access.');

class Kohana_Queue {

	protected $_name = 'empty';

	// Model_Queue_Process:
	protected $process = NULL;

	public $settings = array();

	protected $_model = NULL;

	protected static $_instances = array();

	public static function instance($queue_name)
	{
		if (array_key_exists($queue_name, self::$_instances))
			return self::$_instances[$queue_name];

		$queue_name = ucfirst($queue_name);

		// Set the class name
		$class = "Queue_$queue_name";

		$instance = new $class($queue_name);

		self::$_instances[$queue_name] = $instance;

		return $instance;
	}

	public function __construct($name)
	{
		$this->name($name);

		// Start with finding Queue ORM Model:
		$this->_model = ORM::factory('Queue')
			->where('name', '=', $name)
			->where('active', '=', '1')
			->find();

		if ( ! $this->_model->loaded())
			throw new Kohana_Exception("Queue $name not found");

		// Configs
		$queues_config = Kohana::$config->load('queues');
		$this->settings = Arr::merge($queues_config->get('defaults', array()), Arr::get($queues_config->get('queues'), $name, array()));
	}

	public function name($name = NULL)
	{
		if ($name === NULL)
			return $this->_name;

		$this->_name = $name;

		return $this;
	}

	/**
	 * Puts new item on queue
	 * @param any $data any type of data, it will be saved as JSON in queue_objects
	 * @param string $planned timestamp of planned fetching from queue
	 * @param string $mode value of Kohana::$environment; actual Kohana::$environment will be used if not provided
	 */
	public function enqueue($data = NULL, $planned = NULL, $mode = NULL)
	{
		$object = ORM::factory('Queue_Object');
		$object->queue = $this->_model;
		$object->status = Model_Queue_Object::NOT_PROCESSED;
		$object->created = time();
		$object->planned = ($planned === NULL) ? 0 : $planned;
		$object->retries = 0;
		$object->mode = ($mode === NULL ? Kohana::$environment : $mode);

		$object->data = $data;

		$object->save();

		return $object;
	}

	public function dispatch($mode)
	{
		$this->process = ORM::factory('Queue_Process')
			->start($this->_model, $mode, Arr::get($this->settings, 'timeout', 1800));

		if ($this->process instanceof Model_Queue_Process)
		{
			$batch_size = Arr::get($this->settings, 'batch_size', 20);

			$i = 1;
			$dispatched = 0;
			do
			{
				$batch = $this->_model->get_batch($mode, $batch_size);
				$size = count($batch);

				if ($size > 0)
				{
					$this->info("Batch no. $i, size = $size");
					foreach ($batch as $object)
					{
						$this->dispatch_object($object, $process);
						$dispatched++;
					}
					$this->check();
				}
				$i++;
			}
			while ($size > 0);	// process until no new queue object returned

			$this->process->finish();

			if ($dispatched > 0)
			{
				$this->info("$dispatched objects dispatched.");
				Kohana::$log->write();
			}
		}
		else
		{
			$this->info("Dispatching not started.");
			Kohana::$log->write();
		}
	}

	public function dispatch_object(Model_Queue_Object & $object, Model_Queue_Process & $process = NULL)
	{
		if ($process !== NULL)
		{
			$object->process_hash = $this->process->hash;
		}
		$log_data = array();
		try
		{
			$success = $this->process($object, $log_data);
		}
		catch (Exception $ex)
		{
			$this->error($ex->getMessage());
			$this->error($ex->getTraceAsString());
			$log_data = array(
				'exception' => array(
					'code' => $ex->getCode(),
					'message' => $ex->getMessage(),
				),
			);
			$success = FALSE;
		}
		if ($success)
		{
			$object->processed($log_data);
		}
		else
		{
			$retry_interval = Arr::get($this->settings, 'retry_interval', 60);
			$max_retries = Arr::get($this->settings, 'max_retries', 10);

			$object->defer($retry_interval, $max_retries, $log_data);
		}
		return $this;
	}

	/**
	 * Method to overwrite in child class.
	 * It processes queue $object
	 *
	 * @param Model_Queue_Object $object
	 * @param mixed $log_data log data
	 * @return bool TRUE for success, FALSE for fail
	 */
	protected function process(Model_Queue_Object & $object, & $log_data)
	{
		throw new Kohana_Exception("Kohana_Queue: parent process method was called but this should be overwritten in child class!");
	}

	protected function check()
	{
		if ($this->process->loaded())
		{
			$this->process->checked = time();
			$this->process->save();
		}
		return $this;
	}

	protected function log($value, $level = Log::DEBUG)
	{
		$process_hash = '';
		if ($this->process instanceof Model_Queue_Process
			AND $this->process->loaded())
		{
			$process_hash = "[{$this->process->hash}] ";
		}
		if (is_scalar($value))
		{
			Kohana::$log->add($level, "$process_hash{$this->_name}, ".$value);
		}
		else
		{
			Kohana::$log->add($level, "$process_hash{$this->_name}, ".print_r($value, TRUE));
		}
		return $this;
	}

	protected function debug($value)
	{
		$this->log($value, Log::DEBUG);
	}
	protected function error($value)
	{
		$this->log($value, Log::ERROR);
	}
	protected function warn($value)
	{
		$this->log($value, Log::WARNING);
	}
	protected function info($value)
	{
		$this->log($value, Log::INFO);
	}

} // End Kohana_Queue
