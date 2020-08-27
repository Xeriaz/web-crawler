<?php

namespace App\Entity;

use App\Repository\RoutesRepository;
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
     * @ORM\ManyToOne(targetEntity=BaseRoutes::class, inversedBy="routes")
     * @ORM\JoinColumn(name="base_route_id", referencedColumnName="id")
     */
    private $baseRoute;

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

    public function __construct()
    {
        $this->timesVisited = 0;
        $this->createdOn = new \DateTime();
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

    public function getBaseRoute(): ?BaseRoutes
    {
        return $this->baseRoute;
    }

    public function setBaseRoute(?BaseRoutes $baseRoute): self
    {
        $this->baseRoute = $baseRoute;

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

    public function setTimesVisited(int $timesVisited): self
    {
        $this->timesVisited = $timesVisited;

        return $this;
    }

    public function getCreatedOn(): ?\DateTimeInterface
    {
        return $this->createdOn;
    }
}
