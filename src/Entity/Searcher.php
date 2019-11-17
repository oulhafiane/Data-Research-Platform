<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\SearcherRepository")
 */
class Searcher extends User
{
    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Serializer\SerializedName("organizationAddress")
     * @Serializer\Groups({"update-user", "infos"})
     */
    private $organizationAddress;

    /**
     * @ORM\Column(type="string", length=50, nullable=true)
     * @Serializer\SerializedName("organizationCity")
     * @Serializer\Groups({"update-user", "infos", "public"})
     */
    private $organizationCity;

    /**
     * @ORM\Column(type="string", length=50, nullable=true)
     * @Serializer\SerializedName("organizationCountry")
     * @Serializer\Groups({"update-user", "infos", "public"})
     */
    private $organizationCountry;

    /**
     * @ORM\Column(type="text", nullable=true)
     * @Serializer\Groups({"update-user", "infos", "public"})
     */
    private $bio;

    /**
     * @ORM\Column(type="string", length=20)
     * @Serializer\Groups({"new-user", "infos", "update-user"})
     */
    private $phone;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Problematic", mappedBy="owner", orphanRemoval=true)
     */
    private $problematics;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Vote", mappedBy="voter", orphanRemoval=true)
     */
    private $votes;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Comment", mappedBy="owner", orphanRemoval=true)
     */
    private $comments;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Category", inversedBy="searchers")
     * @Serializer\Type("ArrayCollection<App\Entity\Category>")
	 * @Serializer\Groups({"update-user", "infos"})
	 * @Assert\Valid(groups={"update-user"})
     */
    private $domains;

    public function __construct()
    {
        $this->problematics = new ArrayCollection();
        $this->votes = new ArrayCollection();
        $this->comments = new ArrayCollection();
        $this->domains = new ArrayCollection();
    }

    public function getOrganizationAddress(): ?string
    {
        return $this->organizationAddress;
    }

    public function setOrganizationAddress(?string $address): self
    {
        $this->organizationAddress = $address;

        return $this;
    }

    public function getOrganizationCity(): ?string
    {
        return $this->organizationCity;
    }

    public function setOrganizationCity(?string $city): self
    {
        $this->organizationCity = $city;

        return $this;
    }

    public function getOrganizationCountry(): ?string
    {
        return $this->organizationCountry;
    }

    public function setOrganizationCountry(?string $country): self
    {
        $this->organizationCountry = $country;

        return $this;
    }

    public function getBio(): ?string
    {
        return $this->bio;
    }

    public function setBio(?string $bio): self
    {
        $this->bio = $bio;

        return $this;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(string $phone): self
    {
        $this->phone = $phone;

        return $this;
    }

    /**
     * @return Collection|Problematic[]
     */
    public function getProblematics(): Collection
    {
        return $this->problematics;
    }

    public function addProblematic(Problematic $problematic): self
    {
        if (!$this->problematics->contains($problematic)) {
            $this->problematics[] = $problematic;
            $problematic->setOwner($this);
        }

        return $this;
    }

    public function removeProblematic(Problematic $problematic): self
    {
        if ($this->problematics->contains($problematic)) {
            $this->problematics->removeElement($problematic);
            // set the owning side to null (unless already changed)
            if ($problematic->getOwner() === $this) {
                $problematic->setOwner(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Vote[]
     */
    public function getVotes(): Collection
    {
        return $this->votes;
    }

    public function addVote(Vote $vote): self
    {
        if (!$this->votes->contains($vote)) {
            $this->votes[] = $vote;
            $vote->setVoter($this);
        }

        return $this;
    }

    public function removeVote(Vote $vote): self
    {
        if ($this->votes->contains($vote)) {
            $this->votes->removeElement($vote);
            // set the owning side to null (unless already changed)
            if ($vote->getVoter() === $this) {
                $vote->setVoter(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Comment[]
     */
    public function getComments(): Collection
    {
        return $this->comments;
    }

    public function addComment(Comment $comment): self
    {
        if (!$this->comments->contains($comment)) {
            $this->comments[] = $comment;
            $comment->setOwner($this);
        }

        return $this;
    }

    public function removeComment(Comment $comment): self
    {
        if ($this->comments->contains($comment)) {
            $this->comments->removeElement($comment);
            // set the owning side to null (unless already changed)
            if ($comment->getOwner() === $this) {
                $comment->setOwner(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Category[]
     */
    public function getDomains(): Collection
    {
        return $this->domains;
    }

    public function addDomain(Category $domain): self
    {
        if (!$this->domains->contains($domain)) {
            $this->domains[] = $domain;
        }

        return $this;
    }

    public function removeDomain(Category $domain): self
    {
        if ($this->domains->contains($domain)) {
            $this->domains->removeElement($domain);
        }

        return $this;
    }
}
