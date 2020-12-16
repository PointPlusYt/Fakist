<?php

namespace App\Service;

use Abraham\TwitterOAuth\TwitterOAuth;

class TwitterApi
{
    public function initialize()
    {
        return new TwitterOAuth($_ENV['TWITTER_API_KEY'], $_ENV['TWITTER_API_SECRET']);
    }

    public function getAuthorizationRequestUrl()
    {
        $connection = $this->initialize();
        $request_token = $connection->oauth('oauth/request_token', ['callback_url' => 'http://127.0.0.1:8000/twitter-done']);
        $connection->setOauthToken($request_token['oauth_token'], $request_token['oauth_token_secret']);
        $url = $connection->url('oauth/authorize', ['oauth_token' => $request_token['oauth_token']]);
        return $url;
    }

    public function setAcccessTokenInSession($request)
    {
        $tokenVerifier = $this->getTokenVerifier($request);
        
        $connection = $this->initialize();
        $accessToken = $connection->oauth("oauth/access_token", ['oauth_token' => $tokenVerifier['token'] ,"oauth_verifier" => $tokenVerifier['verifier']]);

        $request->getSession()->set('access_token', $accessToken);
    }

    public function getTokenVerifier($request)
    {
        return [
            'token' => $request->query->get('oauth_token'),
            'verifier' => $request->query->get('oauth_verifier'),
        ];
    }

    public function sendTweet(string $tweet)
    {
        $access_token = $request->getSession()->get('access_token');
        $connection = new TwitterOAuth($_ENV['TWITTER_API_KEY'], $_ENV['TWITTER_API_SECRET'], $access_token['oauth_token'], $access_token['oauth_token_secret']);
        $connection->post("statuses/update", ["status" => "hello world"]);
    }
}