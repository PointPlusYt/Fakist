<?php

namespace App\Controller;

use App\Service\TwitterApi;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class OauthController extends AbstractController
{
    /**
     * @Route("/oauth-connect", name="oauth_connect")
     */
    public function connect(TwitterApi $twitterApi)
    {
        return $this->redirect($twitterApi->getAuthorizationRequestUrl());
    }

    /**
     * @Route("/oauth-confirm", name="oauth_confirm")
     */
    public function oauthConfirm(TwitterApi $twitterApi)
    {
        $twitterApi->storeAccessToken();
        
        return $this->redirectToRoute('tweet_suggest_form');
    }
}
