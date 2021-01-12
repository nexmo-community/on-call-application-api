<?php

namespace App\DataFixtures;

use App\Entity\OnCall;
use Carbon\CarbonImmutable;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class OnCallFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager)
    {
        $currentWeek = CarbonImmutable::now();

        $onCall = new OnCall();
        $onCall
            ->setUser($this->getReference('user_1'))
            ->setStartDate($currentWeek->startOfWeek())
            ->setEndDate($currentWeek->endOfWeek());

        $manager->persist($onCall);

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            UserFixtures::class,
        ];
    }
}