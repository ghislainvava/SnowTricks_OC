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
            $figure->setName($faker->unique()->randomElement($nameFigure))
                    ->setGroupe($category)
                    ->addVideo($video)
                    ->setUser($user)
                    ->setDateCreate($faker->DateTimeBetween('-6 month', 'now'))
                    ->setContent($faker->realText(350));

            $upload = array('6b6a34b98bf43bfc14eca9e70a59fcf9.jpg', '07d58f1b2f7ff6da147aab90904876b1.jpg','91bc53dc4156d1e617fc6fe293c65a02.jpg', '0f213eb43507073d24534eb63c84bd17.jpg','9daf4b0890d9cd357ba2a42e0b297296.jpg',
            '4e1f6120d940df93a8d9d86a34e346f4.jpg', '7503ae982b9cc8410bc0f0bf57d4eff1.jpg','82312523a8348276cee7228a02775e2e.jpg', '00a6298edb63d990f4bb4e152afe1a76.jpg', '9e28c531cd38b5e2aa74d6df672f587e.jpg');
                $nomImg = $faker->unique()->randomElement($upload);
                $image = new Pictures();
                $image->setName($nomImg);
                $figure->addPicture($image);

                for ($j = 1; $j <= 3; $j++) {
                        $nomImg = $faker->randomElement($upload);
                        unset($upload[$nomImg]);
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
