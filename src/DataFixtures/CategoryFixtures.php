<?php

namespace App\DataFixtures;

use Faker\Factory;
use App\Entity\Category;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;

class CategoryFixtures extends Fixture
{
    public function load(ObjectManager $manager)
    {
        $faker = Factory::create('fr_FR');

        for ($j=1; $j < 8; $j++) {
            $category = new Category();
            $category->setName($faker->words(3, true));
            $category->setDescription($faker->text(250));
            $manager->persist($category);

            $this->addReference('category_'. $j, $category);
        }
        $manager->flush();
    }
}
