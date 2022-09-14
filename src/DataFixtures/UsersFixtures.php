<?php

namespace App\DataFixtures;

use Faker\Factory;
use App\Entity\User;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UsersFixtures extends Fixture
{
    private $encoder;

    public function __construct(UserPasswordHasherInterface $encoder)
    {
        $this->encoder = $encoder;
    }

    public function load(ObjectManager $manager)
    {
        $faker = Factory::create('fr_FR');

        for ($nbUsers = 1; $nbUsers <= 30; $nbUsers++) {
            $user = new User();
            $user->setEmail($faker->email);
            if ($nbUsers === 1) {
                $user->setRoles(['ROLE_ADMIN']);
            } else {
                $user->setRoles(['ROLE_USER']);
            }
            $user->setPassword($this->encoder->hashPassword($user, 'azerty'));
            $user->setUsername($faker->lastName);
            $user->setIsVerify($faker->numberBetween(0, 1));
            $manager->persist($user);

            // Enregistre l'utilisateur dans une référence
            $this->addReference('user_'. $nbUsers, $user);
        }

        $manager->flush();
    }
}
