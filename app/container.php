<?php

use Slim\Container;

$container = $app->getContainer();

// Set Container And Connect Database
$container['db'] = function (Container $container) {
	$setting = $container->get('settings');

	$config = new \Doctrine\DBAL\Configuration();

	$connect = \Doctrine\DBAL\DriverManager::getConnection($setting['db'],
	$config);

	return $connect;
};

// Set Validation
$container['validator'] = function ($c) {
	$setting = $c->get('settings')['lang'];
	$param = $c['request']->getParams();
	return new \Valitron\Validator($param, [], $setting['default']);
};

$container['view'] = function ($container) {
	$setting = $container->get('settings')['view'];
	$view = new \Slim\Views\Twig($setting['path'], $setting['twig']);

	$view->addExtension(new Slim\Views\TwigExtension(
		$container->router, $container->request->getUri())
	);

	$view->getEnvironment()->addGlobal('old', @$_SESSION['old']);
	unset($_SESSION['old']);
	$view->getEnvironment()->addGlobal('errors', @$_SESSION['errors']);
	unset($_SESSION['errors']);

	if (@$_SESSION['login']) {
		$view->getEnvironment()->addGlobal('login', $_SESSION['login']);
	}

	if (@$_SESSION['user_group']) {
		$view->getEnvironment()->addGlobal('user_group', $_SESSION['user_group']);
	}

	if (@$_SESSION['guard']) {
		$view->getEnvironment()->addGlobal('guard', $_SESSION['guard']);
	}

	if (@$_SESSION['group']) {
		$view->getEnvironment()->addGlobal('group', $_SESSION['group']);
	}

	$view->getEnvironment()->addGlobal('flash', $container->flash);

	return $view;
};

$container['flash'] = function ($container) {
	return new \Slim\Flash\Messages;
};
