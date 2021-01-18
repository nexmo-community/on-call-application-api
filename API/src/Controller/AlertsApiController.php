<?php

namespace App\Controller;

use App\Entity\Alert;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Workflow\Registry;

/** 
 * @Route("/api/alerts")
 */
class AlertsApiController extends AbstractController
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
     * @Route("", methods={"GET"})
     */
    public function listAction(): JsonResponse
    {
        $data = $this->entityManager
            ->getRepository(Alert::class)
            ->findAll();

        $alerts = [];

        foreach ($data as $alert) {
            $alerts[] = $alert->toArray();
        }

        return new JsonResponse(
            $alerts, 
            JsonResponse::HTTP_OK
        );
    }

    /**
     * @Route("/{id}", methods={"GET"})
     */
    public function readAction(int $id): JsonResponse
    {
        $alert = $this->entityManager
            ->getRepository(Alert::class)
            ->findOneById($id);
        
        if (!$alert) {
            return new JsonResponse(
                null,
                JsonResponse::HTTP_NOT_FOUND
            );
        }

        return new JsonResponse(
            $alert->toArray(), 
            JsonResponse::HTTP_OK
        );
    }

    /**
     * @Route("/{id}/accept", methods={"POST"})
     */
    public function acceptAction(int $id): JsonResponse
    {
        $alert = $this->entityManager
            ->getRepository(Alert::class)
            ->findOneById($id);

        if (!$alert) {
            return new JsonResponse(null, JsonResponse::HTTP_NOT_FOUND);
        }

        $workflow = $this->workflowRegistry->get($alert);

        try {
            $workflow->apply($alert, 'accept');

            $this->entityManager->flush();
        } catch (LogicException $exception) {
            return new JsonResponse(['message' => $exception->getMessage()], 400);
        }

        return new JsonResponse([], 200);
    }

    /**
     * @Route("/{id}/cancel", methods={"POST"})
     */
    public function cancelAction(int $id): JsonResponse
    {
        $alert = $this->entityManager
            ->getRepository(Alert::class)
            ->findOneById($id);

        if (!$alert) {
            return new JsonResponse(null, JsonResponse::HTTP_NOT_FOUND);
        }

        $workflow = $this->workflowRegistry->get($alert);

        try {
            $workflow->apply($alert, 'cancel');

            $this->entityManager->flush();
        } catch (LogicException $exception) {
            return new JsonResponse(['message' => $exception->getMessage()], 400);
        }

        return new JsonResponse([], 200);
    }

    /**
     * @Route("/{id}/complete", methods={"POST"})
     */
    public function completeAction(int $id): JsonResponse
    {
        $alert = $this->entityManager
            ->getRepository(Alert::class)
            ->findOneById($id);

        if (!$alert) {
            return new JsonResponse(null, JsonResponse::HTTP_NOT_FOUND);
        }

        $workflow = $this->workflowRegistry->get($alert);

        try {
            $workflow->apply($alert, 'complete');

            $this->entityManager->flush();
        } catch (LogicException $exception) {
            return new JsonResponse(['message' => $exception->getMessage()], 400);
        }

        return new JsonResponse([], 200);
    }
}