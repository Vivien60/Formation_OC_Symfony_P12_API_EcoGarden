<?php

namespace App\DataFixtures;

use App\Entity\Advice;
use App\Entity\MonthAdvice;
use App\Enum\Month;
use App\Factory\AdviceFactory;
use App\Factory\UserFactory;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    private UserPasswordHasherInterface $passwordHasher;

    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
    }

    public function load(ObjectManager $manager): void
    {
        // $product = new Product();
        // $manager->persist($product);
        AdviceFactory::createMany(50, function () use ($manager) {
            //On génère des collections de mois (numéros) au hasard,
            // d'une taille randomisée entre 1 et 8
            $randomMonths = (array) array_rand(array_flip(range(1, 12)), rand(1, 8));
            return [
                'months' => array_map(
                    fn($num) => MonthAdvice::fromMonth(Month::from($num)),
                    $randomMonths
                ),
            ];
        });
        UserFactory::createMany(10);
        $user = UserFactory::new()->create();
        $passwordHash = $this->passwordHasher->hashPassword($user, $this->passwordHasher->hashPassword($user, getenv('DEFAULT_PWD')));
        $user->setPassword($passwordHash);
        $manager->persist($user);
        $manager->flush();
    }
}
