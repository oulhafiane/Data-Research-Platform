<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use JMS\Serializer\Annotation as Serializer;

/**
 * @ORM\HasLifecycleCallbacks()
 * @ORM\Entity(repositoryClass="App\Repository\SurveyTokenRepository")
 */
class SurveyToken
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     * @Serializer\ReadOnly
     */
    private $id;

    /**
     * @ORM\Column(type="uuid", unique=true)
     * @Serializer\ReadOnly
     * @Serializer\Type("string")
     * @Serializer\Groups({"tokens"})
     */
    private $uuid;

    /**
     * @ORM\Column(type="smallint")
     * @Serializer\Groups({"tokens"})
     */
    private $privacy;

    /**
     * @ORM\Column(type="array", nullable=true)
     * @Serializer\Type("array")
     * @Serializer\Groups({"tokens"})
     */
    private $emails = [];

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\DataSet", inversedBy="surveyTokens")
     * @ORM\JoinColumn(nullable=false)
     */
    private $dataset;

    /**
     * @ORM\Column(type="datetime")
     * @Serializer\Groups({"tokens"})
     */
    private $creationDate;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     * @Serializer\Groups({"tokens"})
     */
    private $expirationDate;

    /**
     * @ORM\PrePersist
     */
    public function onPrePersist()
    {
        $this->uuid = Uuid::uuid4()->toString();
        $this->creationDate = new \DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUuid(): ?string
    {
        return $this->uuid;
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

    public function getEmails(): ?array
    {
        return $this->emails;
    }

    public function setEmails(?array $emails): self
    {
        $this->emails = $emails;

        return $this;
    }

    public function getDataset(): ?DataSet
    {
        return $this->dataset;
    }

    public function setDataset(?DataSet $dataset): self
    {
        $this->dataset = $dataset;

        return $this;
    }

    public function getExpirationDate(): ?\DateTimeInterface
    {
        return $this->expirationDate;
    }

    public function setExpirationDate(?\DateTimeInterface $expirationDate): self
    {
        $this->expirationDate = $expirationDate;

        return $this;
    }
}
