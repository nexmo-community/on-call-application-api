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
        $user = new User();
        $user
            ->setName('Test User')
            ->setEmail('dev+1@company.com')
            ->setPassword($this->passwordEncoder->encodePassword(
                $user,
                'test_pass'
            ))
            ->setPhoneNumber(getenv('ON_CALL_NUMBER'));

        $manager->persist($user);

        $this->addReference('user_1', $user);

        $manager->flush();
    }
}
