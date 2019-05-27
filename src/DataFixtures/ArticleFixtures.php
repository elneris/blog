<?php

namespace App\DataFixtures;

use Faker;
use App\Entity\Article;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

class ArticleFixtures extends Fixture implements DependentFixtureInterface
{
    public function getDependencies()
    {
        return [CategoryFixtures::class];
    }

    public function load(ObjectManager $manager)
    {
        for ($i=0; $i < 50; $i++) {
            $article = new Article();
            $faker = Faker\Factory::create('fr_FR');

            $article->setTitle(mb_strtolower($faker->sentence('3')));
            $article->setContent(mb_strtolower($faker->paragraph('3')));

            $manager->persist($article);
            $article->setCategory($this->getReference('categorie_'.($i+1)%5));
            $manager->flush();
        }
    }
}
