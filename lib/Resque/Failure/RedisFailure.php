<?php
namespace resque\lib\Resque\Failure;

/**
 * Redis backend for storing failed Resque jobs.
 *
 * @package		Resque/Failure
 * @author		Chris Boulton <chris@bigcommerce.com>
 * @license		http://www.opensource.org/licenses/mit-license.php
 */

class RedisFailure implements ResqueFailureInterface
{
	/**
	 * Initialize a failed job class and save it (where appropriate).
	 *
	 * @param object $payload Object containing details of the failed job.
	 * @param object $exception Instance of the exception that was thrown by the failed job.
	 * @param object $worker Instance of Resque_Worker that received the job.
	 * @param string $queue The name of the queue the job was fetched from.
	 */
	public function __construct($payload, $exception, $worker, $queue)
	{
		$data = array();
		$data['failed_at'] = strftime('%a %b %d %H:%M:%S %Z %Y');
		$data['payload'] = $payload;
		$data['exception'] = get_class($exception);
		$data['error'] = $exception->getMessage();
		$data['backtrace'] = explode("\n", $exception->getTraceAsString());
		$data['worker'] = (string)$worker;
		$data['queue'] = $queue;
		\resque\lib\Resque::Redis()->setex('failed:'.$payload['id'], 3600*14, serialize($data));
	}

	static public function get($jobId)
	{
		$data = \resque\lib\Resque::Redis()->get('failed:' . $jobId);
		return unserialize($data);
	}
}
?>