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
        for ($i = 0; $i < 20; $i++) {
            $currentWeek = CarbonImmutable::now()
                ->addWeeks($i);

            // exit;
            $onCall = new OnCall();
            $onCall
                ->setUser($this->getReference('user_' . rand(0, 4)))
                ->setStartDate($currentWeek->startOfWeek())
                ->setEndDate($currentWeek->endOfWeek());

            $manager->persist($onCall);

        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            UserFixtures::class,
        ];
    }
}