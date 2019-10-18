<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\VoteRepository")
 */
class Vote
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="boolean")
     */
    private $good;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Searcher", inversedBy="votes")
     * @ORM\JoinColumn(nullable=false)
     */
    private $voter;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Problematic", inversedBy="votes")
     */
    private $problematic;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Comment", inversedBy="votes")
     */
    private $comment;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getGood(): ?bool
    {
        return $this->good;
    }

    public function setGood(bool $good): self
    {
        $this->good = $good;

        return $this;
    }

    public function getVoter(): ?Searcher
    {
        return $this->voter;
    }

    public function setVoter(?Searcher $voter): self
    {
        $this->voter = $voter;

        return $this;
    }

    public function getProblematic(): ?Problematic
    {
        return $this->problematic;
    }

    public function setProblematic(?Problematic $problematic): self
    {
        $this->problematic = $problematic;

        return $this;
    }

    public function getComment(): ?Comment
    {
        return $this->comment;
    }

    public function setComment(?Comment $comment): self
    {
        $this->comment = $comment;

        return $this;
    }
}
