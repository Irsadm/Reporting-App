<?php

namespace App\Controllers\web;

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

abstract class BaseController
{
	protected $container;

	public function __construct($container)
	{
		return $this->container = $container;
	}

	public function __get($property)
	{
		return $this->container->{$property};
	}

	protected function sendWebNotif($message, $token)
    {
        $default_url = 'https://test-fcm-notif.firebaseio.com/';
        $key = 'AIzaSyD0BJ9ETP_NcrDGuQNGXVyjW8OUr32_10I';
        $default_path = '/'.$key.'/notify';

        $firebase = new \Firebase\FirebaseLib($default_url);

        $dateTime = date();

        if (!empty($message)) {
            $firebase->push($default_path , [
                'message'  => $message,
                'key'      => $token,
                'datatime' => $dateTime
            ]);
        }
    }
}

?>
