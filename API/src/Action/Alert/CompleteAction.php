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
class CompleteAction
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
     *     name="_complete",
     *     path="/{id}/complete",
     *     methods={"POST"},
     *     defaults={"_api_item_operation_name"="complete_alert"}
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
            $workflow->apply($alert, 'complete');

            $this->entityManager->flush();
        } catch (LogicException $exception) {
            // ...
        }

        return new JsonResponse([], 200);
    }
}