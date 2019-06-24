<?php
namespace Base\Middleware;

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

class UserMiddleware {

    public function __invoke(Request $request, Response $response, $args) {
        $response->getBody()->write("in the middleware");
        $response = next($request, $response);

        return $response;
   }

}
