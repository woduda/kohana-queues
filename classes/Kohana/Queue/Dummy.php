<?php defined('SYSPATH') OR die('No direct script access.');

class Kohana_Queue_Dummy extends Queue {

	protected function process(Model_Queue_Object & $object, & $log_data)
	{
		$this->debug("Processing data:\n".print_r($object->data, TRUE));

		// passing some info to logs:
		$log_data['result'] = 'dummy';

		// Always successful :)

		return TRUE;
	}

} // End Kohana_Queue_Dummy
