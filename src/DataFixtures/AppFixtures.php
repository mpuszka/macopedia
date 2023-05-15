<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use App\Entity\Category;

class AppFixtures extends Fixture
{
    private $defaultCategories = [
        'Clothing',
        'Mobile phones',
        'Game consoles',
        'Household furniture',
    ];

    public function load(ObjectManager $manager): void
    {
        foreach ($this->defaultCategories as $cat) {
            $category = new Category();
            $category->setName($cat);
            $manager->persist($category);
        }

        $manager->flush();
    }
}
