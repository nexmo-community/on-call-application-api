<?php

namespace App\Controller;

use App\Entity\Alert;
use App\Entity\OnCall;
use App\Entity\UserAlert;
use App\Form\AlertType;
use App\Util\VonageUtil;
use Carbon\Carbon;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/webhooks")
 */
class WebhookController extends AbstractController
{
    /** @var VonageUtil */
    protected $vonageUtil;

    /** @var EntityManagerInterface */
    private $entityManager;

    public function __construct(
        VonageUtil $vonageUtil,
        EntityManagerInterface $entityManager
    ) {
        $this->vonageUtil = $vonageUtil;
        $this->entityManager = $entityManager;
    }

    /**
     * @Route("/raise_alert", name="raise_alert", methods={"POST"})
     */
    public function index(Request $request)
    {
        $data = json_decode($request->getContent(), true);

        // Create an alert.
        $alert = (new Alert())
            ->setStatus('raised');

        $form = $this->createForm(AlertType::class, $alert);
        $form->submit($data);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($alert);
            $entityManager->flush();

            // Get the on call user
            $onCall = $this->entityManager
                ->getRepository(OnCall::class)
                ->findOnCallByWeek(Carbon::now());

            if (!$onCall) {
                // Throw exception here. There is no on call user
                dump('no oncall exists'); exit;
            }

            // Create a UserAlert
            $userAlert = (new UserAlert())
                ->setUser($onCall->getUser())
                ->setAlert($alert);
            $entityManager->persist($userAlert);

            // Notify the on call user
            $this->vonageUtil->sendSms(
                $onCall->getUser()->getPhoneNumber(),
                getenv('VONAGE_BRAND'),
                'A new alert has been raised, please log into the mobile app to investigate.'
            );

            // Save this update to the user alert
            $userAlert->setSmsSentAt(Carbon::now());

            $entityManager->flush();

            return new JsonResponse([], 201);
        }

        return new JsonResponse($this->getErrorMessages($form), 400);
    }

    /**
     * @Route("/event", name="event", methods={"POST, GET"})
     */
    public function event(Request $request)
    {
        return new JsonResponse([], 200);
    }

    private function getErrorMessages(Form $form): array
    {
        $errors = [];

        foreach ($form->getErrors() as $key => $error) {
            if ($form->isRoot()) {
                $errors['#'][] = $error->getMessage();
            } else {
                $errors[] = $error->getMessage();
            }
        }

        foreach ($form->all() as $child) {
            if (!$child->isValid()) {
                $errors[$child->getName()] = $this->getErrorMessages($child);
            }
        }

        return $errors;
    }
}
