<?php

namespace App\Controller;

use Abraham\TwitterOAuth\TwitterOAuth;
use App\Entity\Tweet;
use App\Form\TweetType;
use App\Repository\TweetRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route(name="tweet_")
 */
class TweetController extends AbstractController
{
    /**
     * @Route("/", name="suggest_form")
     */
    public function suggestForm(Request $request, TweetRepository $tweetRepository): Response
    {
        $tweet = new Tweet();
        $form = $this->createForm(TweetType::class, $tweet);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($tweet);
            $em->flush();

            $this->addFlash('success', 'Le tweet a bien envoyé ! : <em>' . $tweet->getContent() . '</em>');

            return $this->redirectToRoute('tweet_suggest_form');
        }

        if ($this->getUser()) {
            $unsafeTweets = $tweetRepository->findBy(['moderated' => false]);
        } else {
            $unsafeTweets = [];
        }

        return $this->render('tweet/index.html.twig', [
            'form' => $form->createView(),
            'safeTweets' => $tweetRepository->findBy(['moderated' => true]),
            'unsafeTweets' => $unsafeTweets,
        ]);
    }

    /**
     * @Route("/moderate/{id}", name="moderate")
     */
    public function moderate(Tweet $tweet)
    {
        $tweet->setModerated(true);
        $this->getDoctrine()->getManager()->flush();
        // TODO: On prend le tweet et on l'envoie sur Twitter

        return $this->redirectToRoute('tweet_suggest_form');
    }

    /**
     * @Route("/twitter-connect", name="oauth_connect")
     */
    public function oauthConnect()
    {
        $connection = new TwitterOAuth($_ENV['TWITTER_API_KEY'], $_ENV['TWITTER_API_SECRET']);
        $request_token = $connection->oauth('oauth/request_token', ['callback_url' => 'http://127.0.0.1:8000/twitter-done']);
        $connection->setOauthToken($request_token['oauth_token'], $request_token['oauth_token_secret']);
        $url = $connection->url('oauth/authorize', ['oauth_token' => $request_token['oauth_token']]);
        return $this->redirect($url);
    }

    /**
     * @Route("/twitter-done", name="oauth_done")
     */
    public function oauthDone(Request $request)
    {
        $token = $request->query->get('oauth_token');
        $verifier = $request->query->get('oauth_verifier');
        
        $connection = new TwitterOAuth($_ENV['TWITTER_API_KEY'], $_ENV['TWITTER_API_SECRET']);
        $access_token = $connection->oauth("oauth/access_token", ['oauth_token' => $token ,"oauth_verifier" => $verifier]);

        $request->getSession()->set('access_token', $access_token);
        
        return $this->redirectToRoute('tweet_suggest_form');
    }

    /**
     * @Route("/send", name="send")
     */
    public function send(Request $request)
    {
        $access_token = $request->getSession()->get('access_token');
        $connection = new TwitterOAuth($_ENV['TWITTER_API_KEY'], $_ENV['TWITTER_API_SECRET'], $access_token['oauth_token'], $access_token['oauth_token_secret']);
        $connection->post("statuses/update", ["status" => "hello world"]);

        dump($connection);

        return $this->json('Joyeux Noël');
    }
}
