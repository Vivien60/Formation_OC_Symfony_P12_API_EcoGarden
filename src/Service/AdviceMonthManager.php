<?php

namespace App\Service;

use App\Entity\Advice;
use App\Entity\MonthAdvice;
use App\Enum\Month;
use Doctrine\ORM\EntityManagerInterface;

class AdviceMonthManager
{
    public function __construct(private EntityManagerInterface $em)
    {
    }

    /**
     * @param int[] $months
     */
    public function syncMonths(Advice $advice, array $months, bool $isUpdate = false): void
    {
        if (empty($months)) {
            return;
        }

        $advice->getMonths()->clear();

        if ($isUpdate) {
            $this->em->flush();
        }

        foreach ($months as $month) {
            $monthAdvice = MonthAdvice::fromMonth(Month::from((int)$month));
            $advice->addMonth($monthAdvice);
        }
    }
}