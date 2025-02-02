<?php

namespace App\DataFixtures;

use Faker\Factory;
use App\Entity\Task;
use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    public function __construct(UserPasswordHasherInterface $hasher, UserRepository $userRepository)
    {
        $this->hasher = $hasher;
        $this->userRepository = $userRepository;
    }

    public function load(ObjectManager $manager,): void
    {
        //anonymous user

            $faker = Factory::create('fr_FR');
            $user = new User();
            $user->setUserName('anonymous_user');
            $user->setEmail('default@email.com');
            $passwordHasher = $this->hasher->hashPassword($user, 'password');
            $user->setPassword($passwordHasher);
            $user->setRoles(['ROLE_USER_ANONYMOUS']);
            $user->setRoleSelection('ROLE_USER_ANONYMOUS');
            $manager->persist($user);
            $manager->flush();


        //John Doe user for test e2e 
            $faker = Factory::create('fr_FR');
            $user = new User();
            $user->setUserName('John_Doe');
            $user->setEmail('john.doe@example.com');
            $passwordHasher = $this->hasher->hashPassword($user, 'hello');
            $user->setPassword($passwordHasher);
            $user->setRoles(['ROLE_ADMIN']);
            $user->setRoleSelection('ROLE_ADMIN');
            $manager->persist($user);
            $manager->flush();

        //fixtures Users
        for ($i = 0; $i < 5; $i++) {
            $faker = Factory::create('fr_FR');
            $user = new User();
            $username = $faker->name();
            if(strlen($username) < 25 ){ 
                $username;
            } else {
                $username = substr($username,0,24);
            };
            $user->setUserName($username);
            $user->setEmail($faker->email());
            $passwordHasher = $this->hasher->hashPassword($user, 'hello');
            $user->setPassword($passwordHasher);
            $user->setRoles(['ROLE_USER']);
            $user->setRoleSelection('ROLE_USER');
            $manager->persist($user);
        }
        $manager->flush();

        // fixtures Task todo example for test e2e assigned to John Doe
            $faker = Factory::create('fr_FR');
            $task = new Task();
            $task->setTitle('Task1');
            $task->setContent('Content1');
            $task->setIsDone(false);
            $task->setCreatedAt(new \DateTime());
            $task->setUser($this->userRepository->find(2));
            $manager->persist($task);
            $manager->flush();

        // fixtures Task completed example for test e2e assigned to John Doe
            $faker = Factory::create('fr_FR');
            $task = new Task();
            $task->setTitle('Task2');
            $task->setContent('Content2');
            $task->setIsDone(true);
            $task->setCreatedAt(new \DateTime());
            $task->setUser($this->userRepository->find(2));
            $manager->persist($task);
            $manager->flush();

        // fixtures Task todo example for test e2e assigned to John Doe for test edit button
            $faker = Factory::create('fr_FR');
            $task = new Task();
            $task->setTitle('Task3');
            $task->setContent('Content3');
            $task->setIsDone(false);
            $task->setCreatedAt(new \DateTime());
            $task->setUser($this->userRepository->find(2));
            $manager->persist($task);
            $manager->flush();

        // fixtures Task todo example for test e2e assigned to anonymous user
            $faker = Factory::create('fr_FR');
            $task = new Task();
            $task->setTitle('Task3');
            $task->setContent('Content3');
            $task->setIsDone(false);
            $task->setCreatedAt(new \DateTime());
            $task->setUser($this->userRepository->find(1));
            $manager->persist($task);
            $manager->flush();


        //fixtures Task not assigned to a user
        for ($i = 0; $i < 2; $i++) {
            $faker = Factory::create('fr_FR');
            $task = new Task();
            $task->setTitle($faker->sentence(3));
            $task->setContent($faker->paragraph());
            $task->setIsDone(false);
            $task->setCreatedAt($faker->dateTime());
            $task->setUser(null);
            $manager->persist($task);
        }
        $manager->flush();

        //fixtures Task assigned to a user id = 2 (it's John Doe)

        for ($i = 0; $i < 3; $i++) {
            $user = $this->userRepository->find(2);
            $faker = Factory::create('fr_FR');
            $task = new Task();
            $task->setTitle($faker->sentence(3));
            $task->setContent($faker->paragraph());
            $task->setIsDone(false);
            $task->setCreatedAt($faker->dateTime());
            $task->setUser($user);
            $manager->persist($task);
        }
        $manager->flush();
    }
}
