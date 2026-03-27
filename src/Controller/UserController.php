<?php

namespace App\Controller;

use App\DTO\CreateUserRequest;
use App\Entity\User;
use App\Exception\ConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class UserController extends AbstractController
{
    #[Route('/user', name: 'user', methods: 'GET')]
    public function index(): JsonResponse
    {
        return $this->json([
            'message' => 'Welcome to your new controller!',
            'path' => 'src/Controller/UserController.php',
        ]);
    }

    #[Route('/user', name: 'user_create', methods: 'POST')]
    public function create(Request $request, ValidatorInterface $validator, UserPasswordHasherInterface $userPasswordHasher, EntityManagerInterface $em, SerializerInterface $serializer): JsonResponse
    {
        $user = $serializer->deserialize($request->getContent(),
            User::class,
            'json');
        $errors = $validator->validate($user);
        if (count($errors) > 0) {
            throw new ConstraintViolationException($errors);
        }
        $password = $request->toArray()['password'];
        $user->setPassword($userPasswordHasher->hashPassword($user, $password));
        $em->persist($user);
        $em->flush();

        return $this->json([], 201);
    }

    #[Route('/user/{id}', name: 'user_delete', requirements: ['id' => '\d+'], methods: 'DELETE')]
    public function delete(User $user, EntityManagerInterface $em): JsonResponse
    {
        $em->remove($user);

        return $this->json([], 204);
    }

     #[Route('/user/{id}', name: 'user_update', requirements: ['id' => '\d+'], methods: 'PUT')]
    public function update(
         Request $request,
         User $currentUser,
         ValidatorInterface $validator,
         EntityManagerInterface $em,
         SerializerInterface $serializer,
     ): JsonResponse
    {
        $updatedUser = $serializer->deserialize($request->getContent(),
            User::class,
            'json',
            [AbstractNormalizer::OBJECT_TO_POPULATE => $currentUser, 'groups' => 'user:write']
        );
        $errors = $validator->validate($updatedUser);
        if (count($errors) > 0) {
            throw new ConstraintViolationException($errors);
        }
        $em->persist($updatedUser);
        $em->flush();

        return $this->json([], 204);
    }

    #[Route('/user/me', name: 'user_me', requirements: ['id' => '\d+'], methods: 'GET')]
    public function me(): JsonResponse
    {
        return $this->json($this->getUser(), 200, [], ['groups' => 'user:read']);
    }

}
