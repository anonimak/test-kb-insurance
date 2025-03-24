<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Models\UserModel;
use CodeIgniter\API\ResponseTrait;
use Exception;

class AuthController extends BaseController
{
    use ResponseTrait;

    protected $userModel;

    public function __construct()
    {
        helper('jwt');
        $this->userModel = new UserModel();
    }

    public function register()
    {
        $rules = [
            'username' => 'required|min_length[3]|is_unique[users.username]',
            'email' => 'required|valid_email|is_unique[users.email]',
            'password' => 'required|min_length[6]',
            'name' => 'required'
        ];

        if (!$this->validate($rules)) {
            return $this->fail($this->validator->getErrors());
        }

        $data = [
            'username' => $this->request->getVar('username'),
            'email' => $this->request->getVar('email'),
            'password' => $this->request->getVar('password'),
            'name' => $this->request->getVar('name')
        ];

        try {
            $this->userModel->save($data);

            return $this->respond([
                'status' => 201,
                'message' => 'User registered successfully'
            ], 201);
        } catch (Exception $e) {
            return $this->failServerError('An error occurred while creating the user.');
        }
    }

    public function login()
    {
        $rules = [
            'login' => 'required',
            'password' => 'required'
        ];

        if (!$this->validate($rules)) {
            return $this->fail($this->validator->getErrors());
        }

        $login = $this->request->getVar('login');
        $password = $this->request->getVar('password');

        $user = $this->userModel->findUserByEmailOrUsername($login);

        if (!$user) {
            return $this->failNotFound('User not found');
        }

        if (!password_verify($password, $user['password'])) {
            return $this->fail('Invalid password');
        }

        $userData = [
            'id' => $user['id'],
            'username' => $user['username'],
            'email' => $user['email'],
            'name' => $user['name']
        ];

        $token = generateJWT($userData);

        return $this->respond([
            'status' => 200,
            'message' => 'Login successful',
            'token' => $token,
            'user' => $userData
        ]);
    }

    public function profile()
    {
        $token = $this->request->getHeaderLine('Authorization');

        if (empty($token)) {
            return $this->failUnauthorized('No token provided');
        }

        if (strpos($token, 'Bearer ') === 0) {
            $token = substr($token, 7);
        }

        $decodedToken = validateJWT($token);

        if (!$decodedToken) {
            return $this->failUnauthorized('Invalid token');
        }

        $userId = $decodedToken->data->id;
        $user = $this->userModel->find($userId);

        if (!$user) {
            return $this->failNotFound('User not found');
        }

        unset($user['password']);

        return $this->respond([
            'status' => 200,
            'user' => $user
        ]);
    }

    public function logout()
    {

        return $this->respond([
            'status' => 200,
            'message' => 'Logout successful'
        ]);
    }
}
