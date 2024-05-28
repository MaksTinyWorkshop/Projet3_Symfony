<?php
namespace App\DataFixtures;

use App\Entity\Sortie;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class SortieFixtures extends Fixture implements DependentFixtureInterface
{

    public function load(ObjectManager $manager): void
    {
        $faker = \Faker\Factory::create('fr_FR');

        for ($i = 0; $i < 10; $i++) {
            $sortie = new Sortie();
            $sortie->setNom($faker->sentence(3));
            $sortie->setDateHeureDebut($faker->dateTimeBetween('+1 days', '+1 month'));
            $sortie->setDuree($faker->numberBetween(1,300));
            $sortie->setDateLimiteInscription($faker->dateTimeBetween('-2 month', '+10 days'));
            $sortie->setNbInscriptionsMax($faker->numberBetween(5, 50));
            $sortie->setInfosSortie($faker->text(200));

            $lieu = $this->getReference('lieu_'.rand(0,9)); // Référence à un lieu existant
            $sortie->setLieu($lieu);

            $site = $this->getReference('site_'.rand(0,3)); // Référence à un site existant
            $sortie->setSite($site);

            $etat = $this->getReference('etat_'.rand(1,6)); // Référence à un état existant
            $sortie->setEtat($etat);

            $organisateur = $this->getReference('participant_'.rand(0,9)); // Référence à un participant existant
            $sortie->setOrganisateur($organisateur);

            $manager->persist($sortie);

            //$this->addReference('sortie_'.$i, $sortie);

        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            LieuxFixtures::class,
            SiteFixtures::class,
            EtatFixtures::class,
            ParticipantsFixtures::class,
        ];
    }
}