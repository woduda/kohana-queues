<?php defined('SYSPATH') OR die('No direct script access.');

class Kohana_Model_Queue extends ORM {

	protected $_has_many = array(
		'objects' => array(
			'model' => 'Queue_Object',
		),
		'processes' => array(
			'model' => 'Queue_Process',
		),
	);

	public function get_batch($mode, $size = 20, $max_retries = 10)
	{
		return $this->objects
			->where('mode', '=', (string)$mode)
			->where('status', '=', Model_Queue_Object::NOT_PROCESSED)
			->where('planned', '<=', time())
			->where('retries', '<', $max_retries)
			->order_by('planned', 'ASC')
			->order_by('id', 'ASC')
			->limit($size)
			->find_all();
	}

} // End Kohana_Model_Queue
