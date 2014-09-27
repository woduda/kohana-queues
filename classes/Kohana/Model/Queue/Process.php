<?php defined('SYSPATH') or die('No direct script access.');

class Kohana_Model_Queue_Process extends ORM {

	protected $_belongs_to = array(
		'queue' => array(),
	);

	public function start(Model_Queue $queue, $mode, $timeout = 3600)
	{
		if ($this->loaded())
		{
			Kohana::$log->add(Log::WARNING, "Queue_Process on queue :queue already loaded", array(':queue' => $queue->name));
			return FALSE;
		}

		$now = time();

		$process = $queue->processes
			->where('mode', '=', (string)$mode)
			->where('active', '=', '1')
			->find();

		if ($process->loaded())
		{
			$values = array(
				':queue' => $queue->name,
				':started' => date('Y-m-d H:i:s', $process->started),
				':checked' => date('Y-m-d H:i:s', $process->checked),
				':timeout' => $timeout,
			);
			if (($now - $process->checked) > $timeout)
			{
				Kohana::$log->add(Log::WARNING, "Finishing timeouted Queue_Process on queue :queue. Started at: :started, last check: :checked (configured timeout is: :timeout).", $values);
			}
			else
			{
				Kohana::$log->add(Log::WARNING, "Queue_Process on queue :queue is still running. Started at: :started, last check: :checked (configured timeout is: :timeout).", $values);
				return FALSE;
			}
		}
		else
		{
			// Try to use first "free" record with dispatched = 0
			$process = $queue->processes
				->where('mode', '=', (string)$mode)
				->where('active', '=', '0')
				->find();
			if ( ! $process->loaded())
			{
				$process = $this;
			}
		}

		$process->values(array(
				'active' => '1',
				'hash' => Text::random('alnum', 8),
				'started' => $now,
				'checked' => $now,
				'finished' => NULL,
			));

		if ( ! $process->loaded())
		{
			$process->queue = $queue;
			$process->mode = (string)$mode;
		}

		return $process->save();
	}

	public function finish()
	{
		if ($this->loaded())
		{
			$this->finished = time();
			$this->active = '0';
			$this->save();
		}
		else
		{
			Kohana::$log->add(Log::WARNING, "Can't finish not loaded Queue_Process");
		}
	}

} // End Kohana_Model_Queue_Process
