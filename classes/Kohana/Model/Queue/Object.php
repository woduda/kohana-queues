<?php defined('SYSPATH') or die('No direct script access.');

class Kohana_Model_Queue_Object extends ORM {

	const NOT_PROCESSED = '0';
	const PROCESSED = '1';
	const DISCARDED = '2';

	protected $_belongs_to = array(
		'queue' => array(),
	);

	protected $_has_many = array(
		'logs' => array(
			'model' => 'Queue_Log',
			'foreign_key' => 'queue_object_id',
		),
	);

	public function filters()
	{
		return array(
			'data' => array(
				array('json_encode'),
			)
		);
	}

	private $_json_data = NULL;

	public function __get($column)
	{
		if ($column === 'data' AND $this->_json_data !== NULL)
			return $this->_json_data;

		$value = $this->get($column);
		if ($column === 'data')
		{
			$value = $this->_json_data = json_decode($value, TRUE);
		}
		return $value;
	}

	public function processed($log_data = NULL)
	{
		if ( ! $this->loaded())
			return;

		$this->retries++;
		$this->status = Model_Queue_Object::PROCESSED;
		$this->planned = time();

		$this->save();

		if ($log_data !== NULL)
		{
			$this->log($log_data);
		}
	}

	public function defer($retry_interval = 60, $max_retries = 10, $log_data = NULL)
	{
		if ( ! $this->loaded())
			return;

		if ($this->retries >= $max_retries)
		{
			$this->status = Model_Queue_Object::DISCARDED;
		}
		else
		{
			$this->status = Model_Queue_Object::NOT_PROCESSED;
			$this->planned = time() + pow(2, ($this->retries - 1)) * $retry_interval;
		}
		$this->save();

		if ($log_data !== NULL)
		{
			$this->log($log_data);
		}
	}

	public function log($log_data)
	{
		$log = ORM::factory('Queue_Object_Log');
		$log->queue_object = $this;

		$log->values(array(
				'created' => time(),
				'status' => $this->status,
				'process_hash' => $this->process_hash,
				'data' => json_encode($log_data),
			))
			->save();
	}

}  // End Kohana_Model_Queue_Object
