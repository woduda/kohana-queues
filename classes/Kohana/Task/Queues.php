<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Process for serving with queues.
 * Usage:
 *
 * 	php index.php queues --mode={Kohana::$environment value} --queues={list of queues served}
 *		Queues are served in the same order like given in parameter.
 *
 * @author wduda
 *
 */
class Kohana_Task_Queues extends Minion_Task {

	protected $_options = array(
		'mode' => NULL,
		'queues' => NULL,
	);

	public function build_validation(Validation $validation)
	{
		return parent::build_validation($validation)
			->rules('mode', array(
				array('not_empty'),
				array('in_array', array(':value', array('production', 'development', 'staging', 'testing'))),
			))
			->rules('queues', array(
				array('not_empty'),
			));
	}

	protected function _execute(array $params)
	{
		$mode = Arr::get($params, 'mode');
		Kohana::$environment = constant("Kohana::".strtoupper($mode));
		$queues = Arr::get($params, 'queues');

		Kohana::$log->add(Log::DEBUG, "Mode: $mode, queues: $queues");

		$qnames = explode(",", $queues);

		$_qnames = array();
		foreach ($qnames as $qname)
		{
			$_qnames[] = ucfirst(trim($qname));
		}

		$queues = ORM::factory('Queue')
			->where('name', 'in', $_qnames)
			->where('active', '=', '1')
			->find_all()
			->as_array('name');

		foreach ($_qnames as $qname)
		{
			if ( ! array_key_exists($qname, $queues))
			{
				Kohana::$log->add(Log::WARNING, "Queue $qname not found");
				continue;
			}
			try
			{
				Queue::instance($qname)->dispatch(Kohana::$environment);
			}
			catch (Kohana_Exception $ex)
			{
				Kohana::$log->add(Log::ERROR, $ex->getMessage());
				Kohana::$log->add(Log::ERROR, $ex->getTraceAsString());
			}
		}
	}

}
// End Kohana_Task_Queues
