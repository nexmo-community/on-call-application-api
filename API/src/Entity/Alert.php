<?php

namespace App\Entity;

use App\Repository\AlertRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use DateTime;

/**
 * @ORM\Entity(repositoryClass=AlertRepository::class)
 */
class Alert
{
    use TimestampableEntity;

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $title;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $description;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $status;

    /**
     * @ORM\OneToMany(targetEntity=UserAlert::class, mappedBy="alert")
     */
    private $userAlerts;

    public function __construct()
    {
        $this->status = 'raised';
        $this->createdAt = new DateTime();
        $this->updatedAt = new DateTime();
        $this->userAlerts = new ArrayCollection();
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

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): self
    {
        $this->status = $status;

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
            $userAlert->setAlert($this);
        }

        return $this;
    }

    public function removeUserAlert(UserAlert $userAlert): self
    {
        if ($this->userAlerts->contains($userAlert)) {
            $this->userAlerts->removeElement($userAlert);
            // set the owning side to null (unless already changed)
            if ($userAlert->getAlert() === $this) {
                $userAlert->setAlert(null);
            }
        }

        return $this;
    }

    public function getUserAssigned(): ?User
    {
        if ($this->getUserAlerts()->isEmpty()) {
            return null;
        }

        return $this
            ->getUserAlerts()
            ->first()
            ->getUser();
    }

    public function toArray()
    {
        return [
            'id' => $this->getId(),
            'title' => $this->getTitle(),
            'description' => $this->getDescription(),
            'status' => $this->getStatus(),
            'dateRaised' => $this->getCreatedAt()->format('Y-m-d H:i:s'),
            'assigned' => $this->getUserAssigned()->getName(),
            'incidentId' => $this->getId()
        ];
    }
}
