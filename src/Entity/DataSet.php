<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints AS Assert;
use JMS\Serializer\Annotation as Serializer;

/**
 * @ORM\HasLifecycleCallbacks
 * @ORM\Entity(repositoryClass="App\Repository\DataSetRepository")
 */
class DataSet
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     * @Serializer\ReadOnly
     * @Assert\IsNull(groups={"new-dataset"})
     */
    private $id;
    
    /**
     * @ORM\Column(type="uuid", unique=true)
     * @Serializer\ReadOnly
     * @Serializer\Type("string")
     * @Serializer\Groups({"new-dataset", "my-dataset"})
     */
    private $uuid;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank(groups={"new-dataset", "update-dataset"})
     * @Assert\Length(
     *	min = 5,
     *	max = 100,
     *	groups={"new-dataset", "update-dataset"}
     * )
     * @Serializer\Groups({"new-dataset", "update-dataset", "my-dataset"})
     */
    private $name;

    /**
     * @ORM\Column(type="text", nullable=true)
	 * @Serializer\Groups({"new-dataset", "update-dataset", "my-dataset"})
     */
    private $description;

    /**
     * @ORM\Column(type="smallint")
     * @Assert\NotBlank(groups={"new-dataset", "update-dataset"})
     * @Assert\Type(type="integer", groups={"new-dataset", "update-dataset"})
	 * @Serializer\Groups({"new-dataset", "update-dataset", "my-dataset"})
     */
    private $privacy;

    /**
     * @ORM\Column(type="datetime")
     * @Serializer\Type("DateTime<'Y-m-d'>")
     * @Serializer\SerializedName("creationDate")
     * @Serializer\Groups({"my-dataset"})
     */
    private $creationDate;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Searcher", inversedBy="dataSets")
     * @ORM\JoinColumn(nullable=false)
     */
    private $owner;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Part", mappedBy="dataSet")
     * @Serializer\Groups({"my-dataset"})
     */
    private $parts;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\TableT", mappedBy="dataSet")
     * @Serializer\Groups({"my-dataset"})
     */
    private $tables;

    public function __construct()
    {
        $this->tables = new ArrayCollection();
        $this->parts = new ArrayCollection();
    }

    /**
     * @ORM\PrePersist
     */
    public function onPrePersist()
    {
        $this->creationDate = new \DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUuid(): string
    {
        return $this->uuid;
    }

    public function setUuid(string $uuid): self
    {
        $this->uuid = $uuid;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getPrivacy(): ?int
    {
        return $this->privacy;
    }

    public function setPrivacy(int $privacy): self
    {
        $this->privacy = $privacy;

        return $this;
    }

    public function getCreationDate(): ?\DateTimeInterface
    {
        return $this->creationDate;
    }

    public function getOwner(): ?Searcher
    {
        return $this->owner;
    }

    public function setOwner(?Searcher $owner): self
    {
        $this->owner = $owner;

        return $this;
    }

    /**
     * @return Collection|Part[]
     */
    public function getParts(): Collection
    {
        return $this->parts;
    }

    public function addPart(Part $part): self
    {
        if (!$this->parts->contains($part)) {
            $this->parts[] = $part;
            $part->setDataSet($this);
        }

        return $this;
    }

    public function removePart(Part $part): self
    {
        if ($this->parts->contains($part)) {
            $this->parts->removeElement($part);
            // set the owning side to null (unless already changed)
            if ($part->getDataSet() === $this) {
                $part->setDataSet(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|TableT[]
     */
    public function getTables(): Collection
    {
        return $this->tables;
    }

    public function addTable(TableT $table): self
    {
        if (!$this->tables->contains($table)) {
            $this->tables[] = $table;
            $table->setDataSet($this);
        }

        return $this;
    }

    public function removeTable(TableT $table): self
    {
        if ($this->tables->contains($table)) {
            $this->tables->removeElement($table);
            // set the owning side to null (unless already changed)
            if ($table->getDataSet() === $this) {
                $table->setDataSet(null);
            }
        }

        return $this;
    }
}
