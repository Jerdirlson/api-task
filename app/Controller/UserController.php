<?php
namespace app\Controller;

use Slim\Psr7\Request;
use Slim\Psr7\Response;
use app\Model\Repositories\UserRepository;
use app\Utils\ResponseHelper;

class UserController
{
    private $userRepository;

    public function __construct()
    {
        $this->userRepository = new UserRepository();
    }

    public function register(Request $request, Response $response): Response
    {
        $body = $request->getBody()->getContents();
        $params = json_decode($body, true);
        $username = $params['username'] ?? '';
        $password = $params['password'] ?? '';

        echo 'hola' . $username . ' ' . $password;

        if ($this->userRepository->findByUsername($username)) {
            return ResponseHelper::error($response, 'Username already exists', 409);
        }

        if ($this->userRepository->create($username, $password)) {
            return ResponseHelper::success($response, ['message' => 'User registered successfully'], 201);
        }

        return ResponseHelper::error($response, 'Failed to register user', 500);
    }

    public function getUser(Request $request, Response $response): Response
    {
        $user = $request->getAttribute('user');

        if ($user) {
            return ResponseHelper::success($response, $user);
        }

        return ResponseHelper::error($response, 'User not found', 404);
    }
}
