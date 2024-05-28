<?php

namespace App\DataFixtures;

use App\Entity\Site;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class SiteFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $sites = [
            'Rennes',
            'Bagdad',
            'Schnell',
            'Bourgouin-Jailleux'
        ];

        foreach ($sites as $index => $nom) {
            $site = $this->createSite($nom, $manager);
            $this->addReference('site_' . $index, $site); // Use index for reference
        }

        $manager->flush();
    }

    public function createSite(string $nom, ObjectManager $manager): Site
    {
        $site = new Site();
        $site->setNom($nom);
        $manager->persist($site);

        return $site;
    }
}