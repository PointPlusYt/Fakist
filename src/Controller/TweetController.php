<?php

namespace App\Controller;

use App\Entity\Tweet;
use App\Form\TweetType;
use App\Repository\TweetRepository;
use App\Service\TwitterApi;
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
            $unsafeTweets = $tweetRepository->findBy(['moderated' => Tweet::NOT_MODERATED]);
        } else {
            $unsafeTweets = [];
        }

        return $this->render('tweet/index.html.twig', [
            'form' => $form->createView(),
            'safeTweets' => $tweetRepository->findBy(['moderated' => Tweet::ACCEPTED]),
            'unsafeTweets' => $unsafeTweets,
        ]);
    }

    /**
     * @Route("/moderate/accept/{id}", name="moderate_accept")
     */
    public function moderateAccept(Tweet $tweet, TwitterApi $twitterApi)
    {
        $tweet->setModerated(Tweet::ACCEPTED);
        $sentTweet = $twitterApi->sendTweet($tweet->getContent());
        dump($sentTweet);
        $tweet->setTweetId($sentTweet->id_str);

        $this->getDoctrine()->getManager()->flush();

        return $this->redirectToRoute('tweet_suggest_form');
    }

    /**
     * @Route("/moderate/refuse/{id}", name="moderate_refuse")
     */
    public function moderateRefuse(Tweet $tweet)
    {
        $tweet->setModerated(Tweet::REJECTED);
        $this->getDoctrine()->getManager()->flush();
       
        return $this->redirectToRoute('tweet_suggest_form');
    }
}
