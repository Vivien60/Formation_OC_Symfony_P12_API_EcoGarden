<?php

namespace App\DataFixtures;

use App\Entity\MonthAdvice;
use App\Enum\Month;
use App\Factory\AdviceFactory;
use App\Factory\UserFactory;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use App\Entity\User;

class AppFixtures extends Fixture
{
    private UserPasswordHasherInterface $passwordHasher;

    public function __construct(UserPasswordHasherInterface $passwordHasher, #[Autowire('%env(DEFAULT_PWD)%')] private string $defaultPassword)
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
        for($i=0; $i<10; $i++) {
            UserFactory::new()->afterInstantiate(function (User $user) {
                $hash = $this->passwordHasher->hashPassword($user, $this->defaultPassword);
                $user->setPassword($hash);
            })->create();
        }
        $manager->flush();
    }
}
