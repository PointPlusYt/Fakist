<?php

namespace App\Controller;

use App\Service\TwitterApi;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class OauthController extends AbstractController
{
    /**
     * @Route("/twitter-connect", name="oauth_connect")
     */
    public function connect(TwitterApi $twitterApi)
    {
        $twitterApi->getAuthorizationRequestUrl();

        return $this->redirect($url);
    }

    /**
     * @Route("/twitter-done", name="oauth_done")
     */
    public function oauthDone(Request $request, TwitterApi $twitterApi)
    {
        $twitterApi->setAcccessTokenInSession($request);
        
        return $this->redirectToRoute('tweet_suggest_form');
    }

    /**
     * @Route("/send", name="send")
     */
    public function send(Request $request, TwitterApi $twitterApi)
    {
        $twitterApi->sendTweet('Hello world !');

        return $this->json('Joyeux Noël');
    }
}
