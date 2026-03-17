<?php

namespace App\Entity;

use App\Repository\MonthRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: MonthRepository::class)]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_MONTH_ADVICE', fields: ['numberInYear', 'advice'])]
class MonthAdvice
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;
    #[ORM\Column(type: 'integer')]
    private ?int $numberInYear = null;

    #[ORM\ManyToOne(targetEntity: Advice::class)]
    private Advice $advice;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNumberInYear(): ?int
    {
        return $this->numberInYear;
    }

    public function setNumberInYear(int $numberInYear): static
    {
        $this->numberInYear = $numberInYear;

        return $this;
    }

    public function getAdvice(): Advice
    {
        return $this->advice;
    }

    public function setAdvice(?Advice $advice): static
    {
        $this->advice = $advice;
        return $this;
    }
}
