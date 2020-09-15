<?php

namespace App\Entity;

use App\Repository\RoutesRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass=RoutesRepository::class)
 */
class Routes
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\Url(
     *    message = "The url '{{ value }}' is not a valid url",
     * )
     */
    private $route;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $state;

    /**
     * @ORM\Column(type="integer")
     */
    private $timesVisited;

    /**
     * @ORM\Column(type="datetime")
     */
    private $createdOn;

    /**
     * @ORM\ManyToMany(targetEntity=Routes::class, inversedBy="innerRoutes")
     */
    private $parentRoutes;

    /**
     * @ORM\ManyToMany(targetEntity=Routes::class, mappedBy="parentRoutes")
     */
    private $innerRoutes;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $httpStatus;

    public function __construct()
    {
        $this->timesVisited = 0;
        $this->createdOn = new \DateTime();
        $this->parentRoutes = new ArrayCollection();
        $this->innerRoutes = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getRoute(): ?string
    {
        return $this->route;
    }

    public function setRoute(string $route): self
    {
        $this->route = $route;

        return $this;
    }

    public function getState(): ?string
    {
        return $this->state;
    }

    public function setState(string $state): self
    {
        $this->state = $state;

        return $this;
    }

    public function getTimesVisited(): ?int
    {
        return $this->timesVisited;
    }

    public function incrementTimesVisited(): self
    {
        $this->timesVisited++;

        return $this;
    }

    public function getCreatedOn(): ?\DateTimeInterface
    {
        return $this->createdOn;
    }

    /**
     * @return Collection|self[]
     */
    public function getParentRoutes(): Collection
    {
        return $this->parentRoutes;
    }

    public function addParentRoute(self $parentRoute): self
    {
        if (!$this->parentRoutes->contains($parentRoute)) {
            $this->parentRoutes[] = $parentRoute;
        }

        return $this;
    }

    public function removeParentRoute(self $parentRoute): self
    {
        if ($this->parentRoutes->contains($parentRoute)) {
            $this->parentRoutes->removeElement($parentRoute);
        }

        return $this;
    }

    /**
     * @return Collection|self[]
     */
    public function getInnerRoutes(): Collection
    {
        return $this->innerRoutes;
    }

    public function addInnerRoute(self $innerRoute): self
    {
        if (!$this->innerRoutes->contains($innerRoute)) {
            $this->innerRoutes[] = $innerRoute;
            $innerRoute->addParentRoute($this);
        }

        return $this;
    }

    public function removeInnerRoute(self $innerRoute): self
    {
        if ($this->innerRoutes->contains($innerRoute)) {
            $this->innerRoutes->removeElement($innerRoute);
            $innerRoute->removeParentRoute($this);
        }

        return $this;
    }

    public function getHttpStatus(): ?int
    {
        return $this->httpStatus;
    }

    public function setHttpStatus(?int $httpStatus): self
    {
        $this->httpStatus = $httpStatus;

        return $this;
    }
}
