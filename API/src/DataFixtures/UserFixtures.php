<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class UserFixtures extends Fixture
{
    /** UserPasswordEncoderInterface */
    private $passwordEncoder;

    public function __construct(UserPasswordEncoderInterface $passwordEncoder)
    {
        $this->passwordEncoder = $passwordEncoder;
    }

    public function load(ObjectManager $manager)
    {
        for ($userCount = 0; $userCount < 5; $userCount++) {
            $user = new User();
            $user
                ->setEmail('dev+' . $userCount . '@company.com')
                ->setPassword($this->passwordEncoder->encodePassword(
                    $user,
                    'test_pass'
                ))
                ->setPhoneNumber('447000000000');

            $manager->persist($user);

            $this->addReference('user_' . $userCount, $user);
        }

        $manager->flush();
    }
}
