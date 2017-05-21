<?php

namespace App\Middlewares\web;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

class AuthMiddleware extends BaseMiddleware
{
    public function __invoke($request, $response, $next)
    {
        if ($_SESSION['login']) {

            $response = $next($request, $response);

            return $response;
        } else {
            $this->container->flash->addMessage('warning', 'You must login to access this page!');

            return $response->withRedirect($this->container->router->pathFor('login'));
        }
    }
}
