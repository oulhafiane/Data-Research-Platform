<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;

/**
 * @ORM\Entity(repositoryClass="App\Repository\CategoryRepository")
 */
class Category
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     * @Serializer\Groups({"list-categories", "list-problematics", "infos"})
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=100)
     * @Serializer\Groups({"list-categories", "list-problematics", "infos"})
     */
    private $title;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\SubCategory", mappedBy="category", orphanRemoval=true)
     * @Serializer\Groups({"list-categories"})
     */
    private $subCategories;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Searcher", mappedBy="domains")
     */
    private $searchers;

    public function __construct()
    {
        $this->subCategories = new ArrayCollection();
        $this->searchers = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    /**
     * @return Collection|SubCategory[]
     */
    public function getSubCategories(): Collection
    {
        return $this->subCategories;
    }

    public function addSubCategory(SubCategory $subCategory): self
    {
        if (!$this->subCategories->contains($subCategory)) {
            $this->subCategories[] = $subCategory;
            $subCategory->setCategory($this);
        }

        return $this;
    }

    public function removeSubCategory(SubCategory $subCategory): self
    {
        if ($this->subCategories->contains($subCategory)) {
            $this->subCategories->removeElement($subCategory);
            // set the owning side to null (unless already changed)
            if ($subCategory->getCategory() === $this) {
                $subCategory->setCategory(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Searcher[]
     */
    public function getSearchers(): Collection
    {
        return $this->searchers;
    }

    public function addSearcher(Searcher $searcher): self
    {
        if (!$this->searchers->contains($searcher)) {
            $this->searchers[] = $searcher;
            $searcher->addDomain($this);
        }

        return $this;
    }

    public function removeSearcher(Searcher $searcher): self
    {
        if ($this->searchers->contains($searcher)) {
            $this->searchers->removeElement($searcher);
            $searcher->removeDomain($this);
        }

        return $this;
    }
}
