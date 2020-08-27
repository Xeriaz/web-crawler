<?php

namespace App\Entity;

use App\Repository\BaseRoutesRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass=BaseRoutesRepository::class)
 */
class BaseRoutes
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255, unique=true)
     * @Assert\Url(
     *    message = "The url '{{ value }}' is not a valid url",
     * )
     */
    private $baseRoute;

    /**
     * @ORM\OneToMany(targetEntity=Routes::class, mappedBy="baseRoute", orphanRemoval=true)
     */
    private $routes;

    public function __construct()
    {
        $this->routes = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getBaseRoute(): ?string
    {
        return $this->baseRoute;
    }

    public function setBaseRoute(string $baseRoute): self
    {
        $this->baseRoute = $baseRoute;

        return $this;
    }

    /**
     * @return Collection|Routes[]
     */
    public function getRoutes(): Collection
    {
        return $this->routes;
    }

    public function addRoute(Routes $route): self
    {
        if (!$this->routes->contains($route)) {
            $this->routes[] = $route;
            $route->setBaseRoute($this);
        }

        return $this;
    }

    public function removeRoute(Routes $route): self
    {
        if ($this->routes->contains($route)) {
            $this->routes->removeElement($route);
            // set the owning side to null (unless already changed)
            if ($route->getBaseRoute() === $this) {
                $route->setBaseRoute(null);
            }
        }

        return $this;
    }
}
