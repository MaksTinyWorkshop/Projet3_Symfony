<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UsersFixtures extends Fixture {

    public function __construct(private UserPasswordHasherInterface $passwordHasher){}
    public function load(ObjectManager $manager):void
    {
        $admin = new User();
        $admin->setNom('Admin');
        $admin->setPrenom('Admin');
        $admin->setEmail('admin@admin.com');
        $admin->setPseudo('adminou');
        $admin->setPassword($this->passwordHasher->hashPassword($admin, 'admin'));
        $admin->setTelephone('0601020304');
        $admin->setAdmin(true);
    }
}