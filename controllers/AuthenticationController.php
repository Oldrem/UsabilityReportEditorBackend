<?php

namespace app\controllers;

use app\exceptions\BadRequestHttpException;
use app\exceptions\NotAuthorizedHttpException;
use app\model\User;
use app\repositories\UserRepository;
use DateTimeImmutable;
use Exception;
use Lcobucci\JWT\Configuration;
use Lcobucci\JWT\Signer\Hmac\Sha256;
use Lcobucci\JWT\Signer\Key\InMemory;

class AuthenticationController extends Controller
{
    private UserRepository $userRepository;

    public function __construct()
    {
        parent::__construct();
        $this->userRepository = new UserRepository();
    }
    public function login()
    {
        $user_body = $this->request->user;
        $user = $this->userRepository->findByUsername($this->request->username);
        if (password_verify($this->request->password, $user->password)){
            $config = Configuration::forSymmetricSigner(
                new Sha256(),
                InMemory::plainText('kjcmlethjisdfglmjg')
            );
            $now   = new DateTimeImmutable();
            $token = $config->builder()
                // Configures the issuer (iss claim)
                ->issuedBy('http://example.com')
                // Configures the audience (aud claim)
                ->permittedFor('http://example.org')
                // Configures the id (jti claim)
                ->identifiedBy('4f1g23a12aa')
                // Configures the time that the token was issue (iat claim)
                ->issuedAt($now)
                // Configures the expiration time of the token (exp claim)
                ->expiresAt($now->modify('+10 days'))
                // Configures a new claim, called "uid"
                ->withClaim('uid', $user->id)
                // Configures a new header, called "foo"
                ->withHeader('foo', 'bar')
                // Builds a new token
                ->getToken($config->signer(), $config->signingKey());

            $this->response->json([
                'id' => $user->id,
                'username' => $user->username,
                'accessToken' => $token->toString()
            ]);
        }
        else {
            throw new NotAuthorizedHttpException('Неверный логин или пароль');
        }
    }

    public function register()
    {
        $user = $this->userRepository->findByUsername($this->request->username);
        if ($user != null){
            throw new BadRequestHttpException('Пользователь уже существует');
        }
        $created_user = new User();
        $created_user->username = $this->request->username;
        $created_user->password =  password_hash($this->request->password, PASSWORD_DEFAULT);
        if (!$this->userRepository->create($created_user)){
            throw new Exception('Не удалось зарегистрировать пользователя');
        }
    }

    public function getUserInfo()
    {
        $user = $this->userRepository->findById($this->request->uid);
        $this->response->json([
            'id' => $user->id,
            'username' => $user->username
        ]);
    }
}