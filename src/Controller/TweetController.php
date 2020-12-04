<?php

namespace App\Controller;

use App\Entity\Tweet;
use App\Form\TweetType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class TweetController extends AbstractController
{
    /**
     * @Route("/", name="tweet_suggest_form")
     */
    public function suggestForm(Request $request): Response
    {
        $tweet = new Tweet();
        $form = $this->createForm(TweetType::class, $tweet);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($tweet);
            $em->flush();

            $this->addFlash('success', 'Le tweet a bien envoyé ! : <em>' . $tweet->getContent() . '</em>')

            return $this->redirectToRoute('tweet_suggest_form');
        }

        return $this->render('tweet/index.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
