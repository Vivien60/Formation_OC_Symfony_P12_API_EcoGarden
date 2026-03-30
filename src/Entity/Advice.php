<?php

namespace App\Entity;

use App\Enum\Month;
use App\Repository\AdviceRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: AdviceRepository::class)]
class Advice
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(["getAdvices"])]
    private ?int $id = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(["getAdvices"])]
    private ?string $title = null;

    #[ORM\Column(length: 255, nullable: false)]
    #[Groups(["getAdvices"])]
    #[Assert\NotBlank(message: 'Le champ description est obligatoire')]
    private ?string $description = null;

    /**
     * @var Collection<int, MonthAdvice>
     */
    #[ORM\OneToMany(targetEntity: MonthAdvice::class, mappedBy: 'advice', cascade: ['persist', 'remove'], orphanRemoval: true)]
    #[Groups(["getAdvices"])]
    #[Assert\Count(min: 1, minMessage: 'Le champ months est obligatoire')]
    private Collection $months;

    public function __construct()
    {
        $this->months = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @return Collection<int, MonthAdvice>
     */
    public function getMonths(): Collection
    {
        return $this->months;
    }

    public function addMonth(MonthAdvice $month): static
    {
        if (!$this->months->contains($month)) {
            $this->months->add($month);
            $month->setAdvice($this);
        }

        return $this;
    }

    public function removeMonth(MonthAdvice $month): static
    {
        $this->months->removeElement($month);

        return $this;
    }
}
