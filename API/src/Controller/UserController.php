<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegisterType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class UserController extends AbstractController
{
    /**
     * @Route("/api/user", name="api_user")
     */
    public function index(): JsonResponse
    {
        return new JsonResponse(['authenticated'], JsonResponse::HTTP_OK);
    }

    /**
     * @Route("/api/register", name="api_register", methods={"POST"})
     */
    public function register(Request $request, UserPasswordEncoderInterface $passwordEncoder): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $formUser = new User();

        $form = $this->createForm(RegisterType::class, $formUser);
        $form->submit($data);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($data['password'] !== $data['password_confirmation']) {
                return new JsonResponse(['Passwords don\'t match'], JsonResponse::HTTP_BAD_REQUEST);
            }

            $entityManager = $this->getDoctrine()->getManager();
            $user = $entityManager
                ->getRepository(User::class)
                ->findOneByEmail($formUser->getEmail());

            if ($user) {
                return new JsonResponse(['Email already in use.'], JsonResponse::HTTP_BAD_REQUEST);
            }

            $formUser->setPassword($passwordEncoder->encodePassword(
                $formUser,
                $formUser->getPassword()
            ));

            $entityManager->persist($formUser);
            $entityManager->flush();

            return new JsonResponse([], JsonResponse::HTTP_CREATED);
        }

        return new JsonResponse($this->getErrorMessages($form), JsonResponse::HTTP_BAD_REQUEST);
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
