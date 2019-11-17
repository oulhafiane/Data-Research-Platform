<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation as Serializer;
use Ramsey\Uuid\Uuid;

/**
 * @ORM\HasLifecycleCallbacks
 * @ORM\Entity(repositoryClass="App\Repository\UserRepository")
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="type", type="string")
 * @ORM\DiscriminatorMap({"searcher" = "Searcher", "customer" = "Customer", "admin" = "Admin"})
 * @Serializer\Discriminator(field = "type", disabled = false, map = {"searcher" = "App\Entity\Searcher", "customer": "App\Entity\Customer", "admin" = "App\Entity\Admin"})
 */
abstract class User implements UserInterface
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     * @Assert\IsNull(groups={"new-user"})
     * @Serializer\ReadOnly
     */
    private $id;

    /**
     * @ORM\Column(type="uuid", unique=true)
     * @Serializer\Type("string")
     * @Serializer\Groups({"infos", "list-comments", "list-problematics"})
     */
    private $uuid;

    /**
     * @ORM\Column(type="string", length=180, unique=true)
     * @Assert\NotBlank(groups={"new-user"})
     * @Assert\Email(groups={"new-user"})
     * @Serializer\Groups({"new-user", "infos"})
     */
    private $email;

    /**
     * @ORM\Column(type="json")
     */
    private $roles = [];

    /**
     * @var string The hashed password
     * @ORM\Column(type="string")
     * @Serializer\ReadOnly
     * @Assert\IsNull(groups={"new-user"})
     */
    private $password;

    /**
     * @Assert\NotBlank(groups={"new-user"})
     * @Assert\Length(min=6, max=4096, groups={"new-user"})
     * @Assert\NotCompromisedPassword(groups={"new-user"})
     * @Serializer\SerializedName("password")
     * @Serializer\Type("string")
     * @Serializer\Groups({"new-user"})
     */
    protected $plainPassword;


    /**
     * @ORM\Column(type="string", length=50)
     * @Assert\NotBlank(groups={"new-user"})
     * @Serializer\SerializedName("firstName")
     * @Serializer\Groups({"new-user", "infos", "update-user", "list-comments", "list-problematics", "public"})
     */
    private $firstName;

    /**
     * @ORM\Column(type="string", length=50)
     * @Assert\NotBlank(groups={"new-user"})
     * @Serializer\SerializedName("lastName")
     * @Serializer\Groups({"new-user", "infos", "update-user", "list-comments", "list-problematics", "public"})
     */
    private $lastName;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\Photo", cascade={"persist", "remove"})
     * @ORM\JoinColumn(nullable=true)
     * @Serializer\Groups({"update-user", "infos", "list-comments", "list-problematics", "public"})
     */
    private $Photo;

    /**
     * @ORM\Column(type="string", length=50, nullable=true)
     * @Assert\NotBlank(groups={"new-user"})
     * @Serializer\Groups({"new-user", "update-user", "infos", "public"})
     */
    private $organization;

    /**
     * @ORM\Column(type="string", length=50, nullable=true)
     * @Assert\NotBlank(groups={"new-user"})
     * @Serializer\SerializedName("jobTitle")
     * @Serializer\Groups({"new-user", "update-user", "infos", "public"})
     */
    private $jobTitle;

    /**
     * @ORM\Column(type="datetime")
     */
    protected $subscriptionDate;

    /**
     * @ORM\Column(type="boolean")
     */
    protected $isActive;

    /**
     * @ORM\PrePersist
     */
    public function onPrePersist()
    {
        $this->uuid = Uuid::uuid4()->toString();
        if ($this instanceof Searcher)
            $this->roles[] = 'ROLE_SEARCHER';
        else if ($this instanceof Reseller)
            $this->roles[] = 'ROLE_CUSTOMER';
        $this->subscriptionDate = new \DateTime();
        $this->setIsActive(True);
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUuid(): Uuid
    {
        return $this->uuid;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUsername(): string
    {
        return (string) $this->email;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    public function getPlainPassword(): string
    {
        return (string) $this->plainPassword;
    }

    /**
     * @see UserInterface
     */
    public function getPassword(): string
    {
        return (string) $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function getSalt()
    {
        // not needed when using the "bcrypt" algorithm in security.yaml
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials()
    {
        // If you store any temporary, sensitive data on the user, clear it here
        $this->plainPassword = null;
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(string $firstName): self
    {
        $this->firstName = $firstName;

        return $this;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(string $lastName): self
    {
        $this->lastName = $lastName;

        return $this;
    }

    public function getPhoto(): ?Photo
    {
        return $this->Photo;
    }

    public function setPhoto(?Photo $Photo): self
    {
        $this->Photo = $Photo;

        return $this;
    }

    public function getOrganization(): ?string
    {
        return $this->organization;
    }

    public function setOrganization(?string $organization): self
    {
        $this->organization = $organization;

        return $this;
    }

    public function getJobTitle(): ?string
    {
        return $this->jobTitle;
    }

    public function setJobTitle(?string $jobTitle): self
    {
        $this->jobTitle = $jobTitle;

        return $this;
    }

    public function getSubscriptionDate(): ?\DateTimeInterface
    {
        return $this->subscriptionDate;
    }

    public function getIsActive(): ?bool
    {
        return $this->isActive;
    }

    public function setIsActive(bool $isActive): self
    {
        $this->isActive = $isActive;

        return $this;
    }
}
