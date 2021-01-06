<?php

namespace App\Command;

use App\Entity\UserAlert;
use App\Util\VonageUtil;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class EscalateAlertCommand extends Command
{
    protected static $defaultName = 'app:escalate-alert';

    /** @var VonageUtil */
    private $vonageUtil;

    /** @var EntityManagerInterface */
    private $entityManager;

    public function __construct(VonageUtil $vonageUtil, EntityManagerInterface $entityManager)
    {
        $this->vonageUtil = $vonageUtil;
        $this->entityManager = $entityManager;

        parent::__construct();
    }

    protected function configure()
    {
        $this->setDescription('Increasing alert by triggering a voice call to user on call.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $userAlertRepository = $this->entityManager->getRepository(UserAlert::class);
        $userAlerts = $userAlertRepository->findRaiseduserAlerts();

        if (!$userAlerts) {
            $io->warning('There are no alerts needing to be raised.');
        }

        /** @var UserAlert $userAlert */
        foreach ($userAlerts as $userAlert) {
            $this->vonageUtil->makePhoneCall(
                $userAlert->getUser()->getPhoneNumber(),
                getenv('VONAGE_NUMBER'),
                'A new alert has been raised, please log into the mobile app to investigate.'
            );

            $userAlert->setVoiceSentAt((new DateTime));
            $this->entityManager->flush();
        }

        return Command::SUCCESS;
    }
}
