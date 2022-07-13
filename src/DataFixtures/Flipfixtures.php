<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use App\Entity\Figure;
use App\Entity\Category;
use App\Entity\Comment;

class Flipfixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
       

       
        // for ($i = 1; $i <= 10; $i++) {
        //     $figure = new Figure();
        //     $figure->setName(("figure n°$i"))
        //             ->setGroupe("flip")
        //             ->setImage(("http://placehold.it/350*150"))
        //             ->setContent(("<p> Contenu de l'article n°$i</p>"));

        //     $manager->persist(($figure));
        // }
      
       // $manager->flush();
    }
}
