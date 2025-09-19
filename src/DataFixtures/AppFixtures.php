<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    private UserPasswordHasherInterface $hasher;

    public function __construct(UserPasswordHasherInterface $hasher)
    {
        $this->hasher = $hasher;
    }

    public function load(ObjectManager $manager): void
    {
        $data = [
            ['email' => 'tom.ochietti@gmail.com', 'password' => 'admin', 'name' => 'Tom'],
            ['email' => '', 'password' => 'admin', 'name' => 'admin']
        ];

        foreach ($data as $item) {
            $user = new User();
            $user->setEmail($item['email']);
            $user->setName($item ['name']);
            $password = $this->hasher->hashPassword($user, $item['password']);
            $user->setPassword($password);
            $user->setRoles(['ROLE_ADMIN']);
            $manager->persist($user);
        }

        $manager->flush();
    }
}
