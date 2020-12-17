<?php

namespace App\Service;

use Abraham\TwitterOAuth\TwitterOAuth;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class TwitterApi
{
    private $connection;
    private $request;
    private $session;

    public function __construct(Request $request, SessionInterface $session)
    {
        $this->connection = new TwitterOAuth($_ENV['TWITTER_API_KEY'], $_ENV['TWITTER_API_SECRET']);
        $this->request = $request;
        $this->session = $session;
    }

    public function getAuthorizationRequestUrl()
    {
        $request_token = $this->connection->oauth('oauth/request_token', ['callback_url' => 'http://127.0.0.1:8000/twitter-done']);
        $this->connection->setOauthToken($request_token['oauth_token'], $request_token['oauth_token_secret']);
        $url = $this->connection->url('oauth/authorize', ['oauth_token' => $request_token['oauth_token']]);
        return $url;
    }

    public function setAccessTokenInSession()
    {
        $tokenVerifier = $this->getTokenVerifier();
        $accessToken = $this->connection->oauth(
            "oauth/access_token",
            ['oauth_token' => $tokenVerifier['token'] ,"oauth_verifier" => $tokenVerifier['verifier']]
        );
        $this->session->set('access_token', $accessToken);
    }

    public function getTokenVerifier()
    {
        return [
            'token' => $this->request->query->get('oauth_token'),
            'verifier' => $this->request->query->get('oauth_verifier'),
        ];
    }

    public function sendTweet(string $tweet)
    {
        $access_token = $this->session->get('access_token');
        $this->connection->setOauthToken($access_token['oauth_token'], $access_token['oauth_token_secret']);
        return $this->connection->post("statuses/update", ["status" => $tweet]);
    }
}