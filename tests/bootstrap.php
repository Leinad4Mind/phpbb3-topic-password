<?php

if (!defined('ANONYMOUS'))
{
	define('ANONYMOUS', 1);
}

if (!interface_exists('Symfony\\Component\\EventDispatcher\\EventSubscriberInterface'))
{
	require __DIR__ . '/stubs/event_subscriber_interface.php';
}

$extension_root = dirname(__DIR__);

spl_autoload_register(function ($class) use ($extension_root)
{
	$prefix = 'leinad4mind\\topicpassword\\';
	if (strpos($class, $prefix) !== 0)
	{
		return;
	}

	$file = $extension_root . '/' . str_replace('\\', '/', substr($class, strlen($prefix))) . '.php';
	if (is_file($file))
	{
		require_once $file;
	}
});
