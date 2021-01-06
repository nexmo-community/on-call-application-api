<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use App\Repository\UserAlertRepository;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use DateTime;

/**
 * @ApiResource()
 * @ORM\Entity(repositoryClass=UserAlertRepository::class)
 */
class UserAlert
{
    use TimestampableEntity;

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="userAlerts")
     */
    private $user;

    /**
     * @ORM\ManyToOne(targetEntity=Alert::class, inversedBy="userAlerts")
     */
    private $alert;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $smsSentAt;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $voiceSentAt;

    public function __construct()
    {
        $this->createdAt = new DateTime();
        $this->updatedAt = new DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getAlert(): ?Alert
    {
        return $this->alert;
    }

    public function setAlert(?Alert $alert): self
    {
        $this->alert = $alert;

        return $this;
    }

    public function getSmsSentAt(): ?\DateTimeInterface
    {
        return $this->smsSentAt;
    }

    public function setSmsSentAt(?\DateTimeInterface $smsSentAt): self
    {
        $this->smsSentAt = $smsSentAt;

        return $this;
    }

    public function getVoiceSentAt(): ?\DateTimeInterface
    {
        return $this->voiceSentAt;
    }

    public function setVoiceSentAt(?\DateTimeInterface $voiceSentAt): self
    {
        $this->voiceSentAt = $voiceSentAt;

        return $this;
    }
}
