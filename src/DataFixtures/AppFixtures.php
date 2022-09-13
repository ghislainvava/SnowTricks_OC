<?php

namespace App\DataFixtures;

use Faker\Factory;
use App\Entity\User;
use App\Entity\Video;
use App\Entity\Figure;
use DateTimeImmutable;
use App\Entity\Category;
use App\Entity\Pictures;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    public function __construct(UserPasswordHasherInterface $encoder)
    {
        $this->encoder = $encoder;
    }
    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('fr_FR');
        $category = new Category();
        $user = new User();

        $user->setEmail('user@test.com')
        ->setUsername($faker->firstName());
        $password = $this->encoder->hashPassword($user, '12345678');
        $user->setPassword($password);



        $picture = new Pictures();
        $video = new Video();
        $video->setFrame('<iframe width="560" height="315" src="https://www.youtube.com/embed/-fdiyluIM8I" title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>');
        for ($i = 1; $i <= 10; $i++) {
            $category->setName('flip');

            $picture->setName('523f7ee743699a5944d51835cfe1c6b4.jpg');
            $manager->persist($picture);
            $manager->persist($category);
            $user->Id = 1;
            $manager->persist($user);

            $figure = new Figure();
            $figure->setName('il fait beau')
                    ->setGroupe($category)
                    ->addPicture($picture)
                    ->addVideo($video)
                    ->setUser($user)
                    ->setDateCreate(new DateTimeImmutable())
                    ->setContent($faker->text(350));


            $manager->persist($video);
            $manager->persist($figure);

            // dd($figure);
        }

        $manager->flush();
    }
}
