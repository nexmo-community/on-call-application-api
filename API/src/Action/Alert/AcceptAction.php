<?php

namespace App\Action\Alert;

use App\Entity\Alert;
use Symfony\Component\Workflow\Registry;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * @Route("/api/alerts", name="api_alerts")
 */
class AcceptAction
{
    /** Registry */
    private $workflowRegistry;

    /** EntityManagerInterface */
    private $entityManager;

    public function __construct(Registry $workflowRegistry, EntityManagerInterface $entityManager)
    {
        $this->workflowRegistry = $workflowRegistry;
        $this->entityManager = $entityManager;
    }

    /**
     * @Route(
     *     name="_accept",
     *     path="/{id}/accept",
     *     methods={"POST"},
     *     defaults={"_api_item_operation_name"="accept_alert"}
     * )
     */
    public function __invoke(int $id): JsonResponse
    {
        $alert = $this->entityManager
            ->getRepository(Alert::class)
            ->findOneById($id);

        if (!$alert) {
            // Return error, no id exists
            return $alert;
        }

        $workflow = $this->workflowRegistry->get($alert);

        try {
            $workflow->apply($alert, 'accept');

            $this->entityManager->flush();
        } catch (LogicException $exception) {
            // ...
        }

        return new JsonResponse([], 200);
    }
}