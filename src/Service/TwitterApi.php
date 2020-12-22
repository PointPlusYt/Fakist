<?php

namespace App\Service;

use Abraham\TwitterOAuth\TwitterOAuth;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Security;

class TwitterApi
{
    private $connection;
    private $em;
    private $request;
    private $router;
    private $security; 
    private $session;

    public function __construct(
        EntityManagerInterface $em,
        RequestStack $requestStack,
        Security $security,
        SessionInterface $session,
        UrlGeneratorInterface $router
        )
    {
        $this->connection = new TwitterOAuth($_ENV['TWITTER_API_KEY'], $_ENV['TWITTER_API_SECRET']);
        $this->em = $em;
        $this->request = $requestStack->getCurrentRequest();
        $this->router = $router;
        $this->security = $security;
        $this->session = $session;
    }

    public function getAuthorizationRequestUrl()
    {
        $callbackUrl = $this->router->generate('oauth_confirm', [], UrlGeneratorInterface::ABSOLUTE_URL);
        $request_token = $this->connection->oauth('oauth/request_token', ['callback_url' => $callbackUrl]);
        $this->connection->setOauthToken($request_token['oauth_token'], $request_token['oauth_token_secret']);
        $url = $this->connection->url('oauth/authorize', ['oauth_token' => $request_token['oauth_token']]);
        return $url;
    }

    public function storeAccessToken()
    {
        $tokenVerifier = $this->getTokenVerifier();
        $accessToken = $this->connection->oauth(
            "oauth/access_token",
            ['oauth_token' => $tokenVerifier['token'] ,"oauth_verifier" => $tokenVerifier['verifier']]
        );

        $user = $this->security->getUser();
        $user->setApiToken($accessToken['oauth_token']);
        $user->setApiVerifier($accessToken['oauth_token_secret']);
        $this->em->flush();
    }

    public function getTokenVerifier()
    {
        return [
            'token' => $this->request->query->get('oauth_token'),
            'verifier' => $this->request->query->get('oauth_verifier'),
        ];
    }

    /**
     * Send a tweet to the account of a certain user
     */
    public function sendTweet(string $tweet, User $user)
    {
        $this->connection->setOauthToken($user->getApiToken(), $user->getApiVerifier());
        return $this->connection->post("statuses/update", ["status" => $tweet]);
    }
}