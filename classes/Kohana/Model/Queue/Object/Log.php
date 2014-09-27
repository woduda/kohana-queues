<?php defined('SYSPATH') or die('No direct script access.');

class Kohana_Model_Queue_Object_Log extends ORM {

	protected $_belongs_to = array(
		'queue_object' => array(),
	);

}  // End Kohana_Model_Queue_Object_Log
