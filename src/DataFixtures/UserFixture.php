<?php

namespace App\DataFixtures;


use App\Entity\User;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class UserFixture extends BaseFixture
{
    private UserPasswordEncoderInterface $passwordEncoder;

    public function __construct(UserPasswordEncoderInterface $passwordEncoder)
    {

        $this->passwordEncoder = $passwordEncoder;
    }
    protected function loadData(ObjectManager $manager)
    {
       $this->createMany(10, 'main_users',function ($i){
           $user= new User();
           $user->setEmail(sprintf('spacebar%d@example.com', $i));
           $user->setFirstName($this->faker->firstName);
           $user->setPassword($this->passwordEncoder->encodePassword(
               $user,
               'password'));
           return $user;
       });

        $this->createMany(3, 'admin_users',function ($i){
            $user= new User();
            $user->setEmail(sprintf('admin%d@thespacebar.com', $i));
            $user->setFirstName($this->faker->firstName);
            $user->setRoles(['ROLE_ADMIN']);
            $user->setPassword($this->passwordEncoder->encodePassword(
                $user,
                'password'));
            return $user;
        });

        $manager->flush();
    }
}
