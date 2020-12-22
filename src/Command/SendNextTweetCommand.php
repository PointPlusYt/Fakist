<?php

namespace App\Command;

use App\Entity\Tweet;
use App\Repository\TweetRepository;
use App\Repository\UserRepository;
use App\Service\TwitterApi;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class SendNextTweetCommand extends Command
{
    protected static $defaultName = 'app:send-next-tweet';

    private $em;
    private $tweetRepository;
    private $twitterApi;
    private $userRepository;

    public function __construct(
        EntityManagerInterface $em,
        TweetRepository $tweetRepository,
        UserRepository $userRepository,
        TwitterApi $twitterApi
        )
    {
        parent::__construct();
        $this->em = $em;
        $this->tweetRepository = $tweetRepository;
        $this->twitterApi = $twitterApi;
        $this->userRepository = $userRepository;
    }

    protected function configure()
    {
        $this
            ->setDescription('Publie le prochain tweet accepté mais non publié')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $tweet = $this->tweetRepository->findOneBy([
            'moderated' => Tweet::ACCEPTED,
            'tweetId' => null,
        ]);
        $user = $this->userRepository->findActivatedUser();

        $sentTweet = $this->twitterApi->sendTweet($tweet->getContent(), $user);
        $tweet->setTweetId($sentTweet->id_str);

        $this->em->flush();

        $io->success('Tweet envoyé');

        return Command::SUCCESS;
    }
}
