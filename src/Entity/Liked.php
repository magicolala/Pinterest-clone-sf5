<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Entity\Pin;

/**
 * @ORM\Entity(repositoryClass="App\Repository\LikedRepository")
 */
class Liked
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Pin", inversedBy="likeds")
     */
    private $pin;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="likeds")
     */
    private $user;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPin(): ?Pin
    {
        return $this->pin;
    }

    public function setPin(?Pin $pin): self
    {
        $this->pin = $pin;

        return $this;
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
}
