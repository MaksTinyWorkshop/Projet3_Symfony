<?php

namespace App\DataFixtures;

use App\Entity\Inscriptions;
use App\Entity\Participants;
use App\Entity\Sortie;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use App\Repository\SortieRepository;
use App\Repository\ParticipantsRepository;
use Faker\Factory;

class InscriptionsFixtures extends Fixture implements DependentFixtureInterface
{

    public function __construct(private SortieRepository $sortieRepository, private ParticipantsRepository $participantsRepository)
    {
        $this->sortieRepository = $sortieRepository;
        $this->participantsRepository = $participantsRepository;
    }

    public function load(ObjectManager $manager)
    {
        // Récupération des sorties et des participants existants
        $sorties = $this->sortieRepository->findAll();
        $participants = $this->participantsRepository->findAll();

        // Création des inscriptions pour chaque sortie et chaque participant
        foreach ($sorties as $sortie) {
            foreach ($participants as $participant) {
                $inscription = new Inscriptions();
                $inscription->setSortie($sortie);
                $inscription->setParticipant($participant);
                $inscription->setDateInscription(new \DateTime()); // Vous pouvez ajuster la date si nécessaire

                $manager->persist($inscription);
            }
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            SortieFixtures::class,
            ParticipantsFixtures::class,
        ];
    }
}