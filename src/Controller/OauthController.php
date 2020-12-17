<?php

namespace App\Controller;

use App\Service\TwitterApi;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
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
    public function oauthDone(TwitterApi $twitterApi)
    {
        $twitterApi->setAcccessTokenInSession();
        
        return $this->redirectToRoute('tweet_suggest_form');
    }

    /**
     * @Route("/send", name="send")
     */
    public function send(TwitterApi $twitterApi)
    {
        dump($twitterApi->sendTweet('The election is a fraud.'));

        return $this->json('Joyeux Noël');
    }
}
