<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Symfony\Component\Security\Core\User\UserInterface;
use DateTime;

/**
 * @ApiResource()
 * @ORM\Entity(repositoryClass=UserRepository::class)
 */
class User implements UserInterface
{
    use TimestampableEntity;

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=180, unique=true)
     */
    private $email;

    /**
     * @ORM\Column(type="json")
     */
    private $roles = [];

    /**
     * @var string The hashed password
     * @ORM\Column(type="string")
     */
    private $password;

    /**
     * @ORM\OneToMany(targetEntity=OnCall::class, mappedBy="user")
     */
    private $onCalls;

    /**
     * @ORM\OneToMany(targetEntity=UserAlert::class, mappedBy="user")
     */
    private $userAlerts;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $phoneNumber;

    public function __construct()
    {
        $this->onCalls = new ArrayCollection();
        $this->createdAt = new DateTime();
        $this->updatedAt = new DateTime();
        $this->userAlerts = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
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
        // $this->plainPassword = null;
    }

    /**
     * @return Collection|OnCall[]
     */
    public function getOnCalls(): Collection
    {
        return $this->onCalls;
    }

    public function addOnCall(OnCall $onCall): self
    {
        if (!$this->onCalls->contains($onCall)) {
            $this->onCalls[] = $onCall;
            $onCall->setUser($this);
        }

        return $this;
    }

    public function removeOnCall(OnCall $onCall): self
    {
        if ($this->onCalls->contains($onCall)) {
            $this->onCalls->removeElement($onCall);
            // set the owning side to null (unless already changed)
            if ($onCall->getUser() === $this) {
                $onCall->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|UserAlert[]
     */
    public function getUserAlerts(): Collection
    {
        return $this->userAlerts;
    }

    public function addUserAlert(UserAlert $userAlert): self
    {
        if (!$this->userAlerts->contains($userAlert)) {
            $this->userAlerts[] = $userAlert;
            $userAlert->setUser($this);
        }

        return $this;
    }

    public function removeUserAlert(UserAlert $userAlert): self
    {
        if ($this->userAlerts->contains($userAlert)) {
            $this->userAlerts->removeElement($userAlert);
            // set the owning side to null (unless already changed)
            if ($userAlert->getUser() === $this) {
                $userAlert->setUser(null);
            }
        }

        return $this;
    }

    public function getPhoneNumber(): ?string
    {
        return $this->phoneNumber;
    }

    public function setPhoneNumber(string $phoneNumber): self
    {
        $this->phoneNumber = $phoneNumber;

        return $this;
    }
}
