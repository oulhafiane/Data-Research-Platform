<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints AS Assert;
use JMS\Serializer\Annotation as Serializer;

/**
 * @ORM\Entity(repositoryClass="App\Repository\VariableRepository")
 */
class Variable
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     * @Serializer\ReadOnly
     * @Assert\IsNull(groups={"add-variables"})
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=100)
     * @Assert\NotBlank(groups={"add-variables"})
     * @Assert\Length(
     *	min = 2,
     *	max = 100,
     *	groups={"add-variables"}
     * )
     * @Serializer\Groups({"my-dataset", "add-variables", "my-dataset"})
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Assert\NotBlank(groups={"add-variables"})
     * @Assert\Length(
     *	min = 10,
     *	max = 255,
     *	groups={"add-variables"}
     * )
     * @Serializer\Groups({"my-dataset", "add-variables", "my-dataset"})
     */
    private $question;

    /**
     * @ORM\Column(type="smallint")
     * @Assert\NotBlank(groups={"add-variables"})
     * @Assert\Type(type="integer", groups={"add-variables"})
	 * @Serializer\Groups({"my-dataset", "add-variables", "my-dataset"})
     */
    private $type;

    /**
     * @ORM\Column(type="array", nullable=true)
     * @Serializer\Type("array")
     * @Serializer\Groups({"my-dataset", "add-variables", "my-dataset"})
     */
    private $options = [];

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Part", inversedBy="variables")
     */
    private $part;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\TableT", inversedBy="variables")
     * @ORM\JoinColumn(nullable=false)
     */
    private $tableT;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getQuestion(): ?string
    {
        return $this->question;
    }

    public function setQuestion(?string $question): self
    {
        $this->question = $question;

        return $this;
    }

    public function getType(): ?int
    {
        return $this->type;
    }

    public function setType(int $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getOptions(): ?array
    {
        return $this->options;
    }

    public function setOptions(?array $options): self
    {
        $this->options = $options;

        return $this;
    }

    public function getPart(): ?Part
    {
        return $this->part;
    }

    public function setPart(?Part $part): self
    {
        $this->part = $part;

        return $this;
    }

    public function getTableT(): ?TableT
    {
        return $this->tableT;
    }

    public function setTableT(?TableT $tableT): self
    {
        $this->tableT = $tableT;

        return $this;
    }
}
