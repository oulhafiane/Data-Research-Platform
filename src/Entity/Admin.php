<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\AdminRepository")
 */
class Admin extends User
{
    /**
     * @ORM\OneToMany(targetEntity="App\Entity\SearcherApplications", mappedBy="acceptedBy")
     */
    private $searcherApplications;

    public function __construct()
    {
        $this->searcherApplications = new ArrayCollection();
    }

    /**
     * @return Collection|SearcherApplications[]
     */
    public function getSearcherApplications(): Collection
    {
        return $this->searcherApplications;
    }

    public function addSearcherApplication(SearcherApplications $searcherApplication): self
    {
        if (!$this->searcherApplications->contains($searcherApplication)) {
            $this->searcherApplications[] = $searcherApplication;
            $searcherApplication->setAcceptedBy($this);
        }

        return $this;
    }

    public function removeSearcherApplication(SearcherApplications $searcherApplication): self
    {
        if ($this->searcherApplications->contains($searcherApplication)) {
            $this->searcherApplications->removeElement($searcherApplication);
            // set the owning side to null (unless already changed)
            if ($searcherApplication->getAcceptedBy() === $this) {
                $searcherApplication->setAcceptedBy(null);
            }
        }

        return $this;
    }
}
