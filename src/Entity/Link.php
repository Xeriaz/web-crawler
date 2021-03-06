<?php

namespace App\Entity;

use App\Repository\LinkRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass=LinkRepository::class)
 */
class Link
{
    public const STATE_PENDING = 'pending';
    public const STATE_SUCCESS = 'success';
    public const STATE_FAILED = 'failed';
    public const STATE_SKIPPED = 'skipped';
    public const STATE_DEAD = 'dead';

    public const TRANSITION_SKIPPING = 'skipping';
    public const TRANSITION_SUCCESS = 'success';
    public const TRANSITION_PENDING = 'pending';
    public const TRANSITION_REDIRECTING = 'redirecting';
    public const TRANSITION_FAILING = 'failing';
    public const TRANSITION_DYING = 'dying';

    public const WORKFLOW_LINK_CRAWLING = 'link_crawling';


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
    private $link;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $state = self::STATE_PENDING;

    /**
     * @ORM\Column(type="integer")
     */
    private $timesVisited;

    /**
     * @ORM\Column(type="datetime")
     */
    private $createdOn;

    /**
     * @ORM\ManyToMany(targetEntity=Link::class, inversedBy="innerLinks")
     */
    private $parentLinks;

    /**
     * @ORM\ManyToMany(targetEntity=Link::class, mappedBy="parentLinks")
     */
    private $innerLinks;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $httpStatus;

    /**
     * @var string
     */
    private $currentPlace;

    public function __construct()
    {
        $this->timesVisited = 0;
        $this->createdOn = new \DateTime();
        $this->parentLinks = new ArrayCollection();
        $this->innerLinks = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLink(): ?string
    {
        return $this->link;
    }

    public function setLink(string $link): self
    {
        $this->link = $link;

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
    public function getParentLinks(): Collection
    {
        return $this->parentLinks;
    }

    public function addParentLink(self $parentLink): self
    {
        if (!$this->parentLinks->contains($parentLink)) {
            $this->parentLinks[] = $parentLink;
        }

        return $this;
    }

    public function removeParentLink(self $parentLink): self
    {
        if ($this->parentLinks->contains($parentLink)) {
            $this->parentLinks->removeElement($parentLink);
        }

        return $this;
    }

    /**
     * @return Collection|self[]
     */
    public function getInnerLinks(): Collection
    {
        return $this->innerLinks;
    }

    public function addInnerLink(self $innerLink): self
    {
        if (!$this->innerLinks->contains($innerLink)) {
            $this->innerLinks[] = $innerLink;
            $innerLink->addParentLink($this);
        }

        return $this;
    }

    public function removeInnerLink(self $innerLink): self
    {
        if ($this->innerLinks->contains($innerLink)) {
            $this->innerLinks->removeElement($innerLink);
            $innerLink->removeParentLink($this);
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

    /**
     * @return string
     */
    public function getCurrentPlace(): string
    {
        return $this->currentPlace;
    }

    /**
     * @param string $currentPlace
     */
    public function setCurrentPlace(string $currentPlace): void
    {
        $this->currentPlace = $currentPlace;
    }
}
