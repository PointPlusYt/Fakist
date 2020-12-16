<?php

namespace App\Controller;

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
}
