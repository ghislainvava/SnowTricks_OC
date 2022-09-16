<?php

namespace App\DataFixtures;

use Faker\Factory;
use App\Entity\Video;
use App\Entity\Figure;
use DateTimeImmutable;
use App\Entity\Comment;
use App\Entity\Pictures;
use App\DataFixtures\UsersFixtures;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture implements DependentFixtureInterface
{
    public function __construct(UserPasswordHasherInterface $encoder)
    {
        $this->encoder = $encoder;
    }
    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('fr_FR');

        $figures = array();

        for ($i = 1; $i <= 10; $i++) {
            $user = $this->getReference('user_'. $faker->numberBetween(1, 30));
            $category = $this->getReference('category_'. $faker->numberBetween(1, 7));

            $video = new Video();
            $video->setFrame('<iframe width="560" height="315" src="https://www.youtube.com/embed/-fdiyluIM8I" title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>');
            $manager->persist($video);

            $nameFigure = array('Mute','Melancholle', 'Indy', 'Stelefish', 'Japan', 'Nose grab', 'Le 720', 'Le 360', 'Le Flip', 'Rodeo', 'Tail slide', 'Method air', 'Big Air');
            $figure = new Figure();
            $figure->setName($faker->randomElement($nameFigure))
                    ->setGroupe($category)
                    ->addVideo($video)
                    ->setUser($user)
                    ->setDateCreate($faker->DateTimeBetween('-6 month', 'now'))
                    ->setContent($faker->realText(350));

            $upload = array('6b6a34b98bf43bfc14eca9e70a59fcf9.jpg', '523f7ee743699a5944d51835cfe1c6b4.jpg','859d2582663e34d2c5dcd8b446a41772.jpg', '0f213eb43507073d24534eb63c84bd17.jpg','2db7869b7773f7762bb4b20f9e588de2.jpg',
            '4e1f6120d940df93a8d9d86a34e346f4.jpg', '657418d4a994cdc314a9f088ebf2defd.jpg','7010734edbdd1b24a934240c7cde4945.jpg', '00a6298edb63d990f4bb4e152afe1a76.jpg', '798aa627221e982bd3547b002908b60c.jpg');

            for ($j = 1; $j <= 3; $j++) {
                $nomImg = $faker->randomElement($upload);
                $image = new Pictures();
                $image->setName($nomImg);
                $figure->addPicture($image);
            }


            $figures = [$i => $figure];
            $manager->persist($figure);

            foreach ($figures as $figure) {
                for ($k = 1; $k <= 15; $k++) {
                    $comment = new Comment();
                    $comment->Setcontent($faker->realText(100));
                    $comment->setCreateAt(new DateTimeImmutable());
                    $comment->setUser($user);
                    $comment->Setfigure($figure);
                    $manager->persist($comment);
                }
            }
        }
        $manager->flush();
    }
         public function getDependencies()
         {
             return [

                CategoryFixtures::class,
                UsersFixtures::class
             ];
         }
}
