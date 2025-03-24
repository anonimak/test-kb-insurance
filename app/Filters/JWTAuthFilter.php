<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\API\ResponseTrait;
use Config\Services;

class JWTAuthFilter implements FilterInterface
{
    use ResponseTrait;

    protected $response;

    public function __construct()
    {
        $this->response = Services::response();
    }

    public function before(RequestInterface $request, $arguments = null)
    {
        helper('jwt');

        $token = $request->getHeaderLine('Authorization');

        if (empty($token)) {
            return Services::response()
                ->setStatusCode(401)
                ->setJSON([
                    'status' => 401,
                    'error' => 'Access denied. No token provided.'
                ])
                ->setContentType('application/json');
        }

        if (strpos($token, 'Bearer ') === 0) {
            $token = substr($token, 7);
        }

        $decodedToken = validateJWT($token);

        if (!$decodedToken) {
            return Services::response()
                ->setStatusCode(401)
                ->setJSON([
                    'status' => 401,
                    'error' => 'Invalid token or token expired'
                ])
                ->setContentType('application/json');
        }

        $request->user = $decodedToken->data;
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null) {}
}
