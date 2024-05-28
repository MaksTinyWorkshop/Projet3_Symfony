<?php

namespace App\DataFixtures;

use App\Entity\Participants;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class ParticipantsFixtures extends Fixture implements DependentFixtureInterface {

    public function __construct(private UserPasswordHasherInterface $passwordHasher){}
    public function load(ObjectManager $manager):void
    {
        $admin = new Participants();
        $admin->setNom('Admin');
        $admin->setPrenom('Admin');
        $admin->setEmail('admin@admin.com');
        $admin->setPseudo('adminou');
        $admin->setPassword($this->passwordHasher->hashPassword($admin, 'Pa$$w0rd'));
        $admin->setTelephone('0601020304');
        $admin->setRoles(['ROLE_ADMIN']);
        $admin->setSite($this->getReference('site_0'));
        $admin->setActif(true);
        $manager->persist($admin);


        $faker = \Faker\Factory::create('fr_FR');

        for ($i = 0; $i < 10; $i++) {
            $user = new Participants();
            $user->setPrenom($faker->firstName());
            $user->setNom($faker->lastName());
            $user->setEmail($faker->email());
            $user->setPseudo($faker->userName());
            $user->setPassword($this->passwordHasher->hashPassword($user, 'Pa$$w0rd'));
            $user->setTelephone($faker->phoneNumber());
            $user->setRoles(['ROLE_USER']);
            $user->setActif(true);
            $user->setSite($this->getReference('site_'.rand(0,3)));
            $manager->persist($user);
            $this->addReference('participant_' . $i, $user);
        }

        $manager->flush();
    }
    public function getDependencies():array {
        return [
            SiteFixtures::class,
        ];
    }
}