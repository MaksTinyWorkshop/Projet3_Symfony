<?php

namespace App\DataFixtures;

use App\Entity\Lieu;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class LieuxFixtures extends Fixture implements DependentFixtureInterface
{

    public function load(ObjectManager $manager): void
    {
        $faker = \Faker\Factory::create('fr_FR');

        for ($i = 0; $i < 10; $i++) {
            $lieu = new Lieu();
            $lieu->setNom($faker->company());
            $lieu->setLatitude($faker->latitude());
            $lieu->setLongitude($faker->longitude());
            $lieu->setRue($faker->streetAddress());
            $ville = $this->getReference('ville_'.rand(1,5));
            $lieu->setVille($ville);

            $this->addReference('lieu_'.$i, $lieu);

            $manager->persist($lieu);
        }
        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            VilleFixtures::class,
        ];
    }
}