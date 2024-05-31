<?php

namespace App\DataFixtures;

use App\Entity\Lieu;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class LieuxFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('fr_FR');

        for ($i = 0; $i < 10; $i++) {
            $lieu = new Lieu();
            $lieu->setNom($faker->company());
            $lieu->setLatitude($faker->latitude());
            $lieu->setLongitude($faker->longitude());
            $lieu->setRue($faker->streetAddress());
            $lieu->setVille($faker->city());
            $lieu->setCodePostal($faker->postcode());
            $lieu->setVille($faker->city());
            $this->addReference('lieu_'.$i, $lieu);

            $manager->persist($lieu);
        }
        $manager->flush();
    }
}