<?php

namespace App\Entity;

use App\Repository\TweetRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=TweetRepository::class)
 */
class Tweet
{
    const NOT_MODERATED = 0;
    const ACCEPTED = 1;
    const REJECTED = 2;

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="text")
     */
    private $content;

    /**
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    /**
     * @ORM\Column(type="smallint", options={"default": 0})
     */
    private $moderated = self::NOT_MODERATED;

    /**
     * @ORM\Column(type="boolean")
     */
    private $markedAsFake = false;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $tweetId;

    public function __construct()
    {
        $this->createdAt = new \DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(string $content): self
    {
        $this->content = $content;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getModerated(): ?int
    {
        return $this->moderated;
    }

    public function setModerated(int $moderated): self
    {
        $this->moderated = $moderated;

        return $this;
    }

    public function getMarkedAsFake(): ?bool
    {
        return $this->markedAsFake;
    }

    public function setMarkedAsFake(bool $markedAsFake): self
    {
        $this->markedAsFake = $markedAsFake;

        return $this;
    }

    public function getTweetId(): ?string
    {
        return $this->tweetId;
    }

    public function setTweetId(?string $tweetId): self
    {
        $this->tweetId = $tweetId;

        return $this;
    }
}
