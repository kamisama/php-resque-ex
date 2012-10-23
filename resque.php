<?php
	$QUEUE = getenv('QUEUE');
	if (empty($QUEUE))
	{
		die("Set QUEUE env var containing the list of queues to work.\n");
	}

	if (!defined('DS'))
	{
		define('DS', DIRECTORY_SEPARATOR);
	}

	// The library is the root library
	if (file_exists(__DIR__ . DS . 'vendor' . DS . 'autoload.php'))
	{
		require_once __DIR__ . DS . 'vendor' . DS . 'autoload.php';
	}
	// The library is a dependency of another library
	elseif (file_exists(dirname(dirname(__DIR__)) . DS . 'autoload.php'))
	{
		require_once dirname(dirname(__DIR__)) . DS . 'autoload.php';
	}

	$REDIS_BACKEND = getenv('REDIS_BACKEND');
	$REDIS_DATABASE = getenv('REDIS_DATABASE');
	$REDIS_NAMESPACE = getenv('REDIS_NAMESPACE');

	$LOG_HANDLER = getenv('LOGHANDLER');
	$LOG_HANDLER_TARGET = getenv('LOGHANDLERTARGET');

	$logger = new MonologInit\MonologInit($LOG_HANDLER, $LOG_HANDLER_TARGET);

	if (!empty($REDIS_BACKEND))
	{
		Resque::setBackend($REDIS_BACKEND, $REDIS_DATABASE, $REDIS_NAMESPACE);
	}

	$logLevel = 0;
	$LOGGING = getenv('LOGGING');
	$VERBOSE = getenv('VERBOSE');
	$VVERBOSE = getenv('VVERBOSE');
	if (!empty($LOGGING) || !empty($VERBOSE))
	{
		$logLevel = Resque_Worker::LOG_NORMAL;
	}
	else if (!empty($VVERBOSE))
	{
		$logLevel = Resque_Worker::LOG_VERBOSE;
	}

	$APP_INCLUDE = getenv('APP_INCLUDE');
	if ($APP_INCLUDE)
	{
		if (!file_exists($APP_INCLUDE))
		{
			die('APP_INCLUDE (' . $APP_INCLUDE . ") does not exist.\n");
		}

		require_once $APP_INCLUDE;
	}

	$interval = 5;
	$INTERVAL = getenv('INTERVAL');
	if (!empty($INTERVAL))
	{
		$interval = $INTERVAL;
	}

	$count = 1;
	$COUNT = getenv('COUNT');
	if (!empty($COUNT) && $COUNT > 1)
	{
		$count = $COUNT;
	}

	if ($count > 1)
	{
		for ($i = 0; $i < $count; ++$i)
		{
			$pid = pcntl_fork();
			if ($pid == -1)
			{
				die("Could not fork worker " . $i . "\n");
			}
			// Child, start the worker
			else if (!$pid)
			{
				$queues = explode(',', $QUEUE);
				$worker = new Resque_Worker($queues);
				$worker->registerLogger($logger);
				$worker->logLevel = $logLevel;
				logStart($logger, array('message' => '*** Starting worker ' . $worker, 'data' => array('type' => 'start', 'worker' => (string) $worker)), $logLevel);
				$worker->work($interval);
				break;
			}
		}
	}
	// Start a single worker
	else
	{
		$queues = explode(',', $QUEUE);
		$worker = new Resque_Worker($queues);
		$worker->registerLogger($logger);
		$worker->logLevel = $logLevel;

		$PIDFILE = getenv('PIDFILE');
		if ($PIDFILE)
		{
			file_put_contents($PIDFILE, getmypid()) or die('Could not write PID information to ' . $PIDFILE);
		}

		logStart($logger, array('message' => '*** Starting worker ' . $worker, 'data' => array('type' => 'start', 'worker' => (string) $worker)), $logLevel);
		$worker->work($interval);
	}

	function logStart($logger, $message, $logLevel)
	{
		if($logger === null || $logger->getInstance() === null)
		{
			fwrite(STDOUT, (($logLevel == Resque_Worker::LOG_NORMAL) ? "" : "[" . strftime('%T %Y-%m-%d') . "] ") . $message['message'] . "\n");
		}
		else
		{
			list($host, $pid, $queues) = explode(':', $message['data']['worker'], 3);
			$message['data']['worker'] = $host . ':' . $pid;
			$message['data']['queues'] = explode(',', $queues);

			$logger->getInstance()->addInfo($message['message'], $message['data']);
		}
	}