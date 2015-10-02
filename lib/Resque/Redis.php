<?php
namespace resque\lib\Resque;
use resque\lib\Credis\Credis_Client;
class RedisApi extends \Redis
{
	private static $defaultNamespace = 'resque:';

	public $password = null;

	public function __construct($host, $database = 0, $password = null, $timeout = 60)
	{
		parent::__construct();

		$server = explode(':', $host);

		$this->host = $server[0];
		$this->port = $server[1];
		$this->password = $password;
		$this->timeout = $timeout;

		$this->establishConnection();
	}

	function establishConnection()
	{
		$this->connect($this->host, (int) $this->port, (int) $this->timeout);

        if (isset($this->password) && !empty($this->password)) {
            if ($this->auth($this->password) === false) {
                throw new \Exception('Resque failed to authenticate with redis!');
            }
        }

		$this->setOption(\Redis::OPT_PREFIX, self::$defaultNamespace);
	}

	public function prefix($namespace)
	{
		if (empty($namespace)) $namespace = self::$defaultNamespace;
		if (strpos($namespace, ':') === false) {
			$namespace .= ':';
		}
		self::$defaultNamespace = $namespace;
                      
		$this->setOption(\Redis::OPT_PREFIX, self::$defaultNamespace);
	}
}

class Redis extends RedisApi {}