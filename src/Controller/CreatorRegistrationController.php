<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Role;
use App\Form\CreatorRegistrationType;
use App\Form\CreatorInfoType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class CreatorRegistrationController extends AbstractController
{
    #[Route('/register/creator', name: 'app_register_creator')]
    public function register(
        Request $request, 
        UserPasswordHasherInterface $userPasswordHasher,
        EntityManagerInterface $entityManager,
        Security $security,
    ): Response
    {
        // Rediriger si l'utilisateur est déjà connecté
        if ($this->getUser()) {
            return $this->redirectToRoute('app_home');
        }

        $user = new User();
        $form = $this->createForm(CreatorRegistrationType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Encoder le mot de passe
            $user->setPassword(
                $userPasswordHasher->hashPassword(
                    $user,
                    $form->get('password')->getData()
                )
            );

            // Ajouter les rôles (ROLE_USER est ajouté automatiquement)
            $roleCreator = $entityManager->getRepository(Role::class)->findOneBy(['name' => 'ROLE_CREATOR']);
            if ($roleCreator) {
                $user->addRole($roleCreator);
            }

            // Définir les timestamps
            $user->setCreatedAt(new \DateTimeImmutable());            $entityManager->persist($user);
            $entityManager->flush();
            // Connecter l'utilisateur automatiquement
            $security->login($user);

            // Rediriger vers son profil créateur
            return $this->redirectToRoute('app_profile_show', ['username' => $user->getUserIdentifier()]);
        }

        return $this->render('creator/register.html.twig', [
            'registrationForm' => $form,
        ]);
    }

    #[Route('/become-creator', name: 'app_become_creator')]
    public function becomeCreator(
        Request $request,
        EntityManagerInterface $entityManager,
        Security $security
    ): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        if (!$user) {
            throw $this->createAccessDeniedException('Vous devez être connecté pour accéder à cette page.');
        }

        // Vérifier si l'utilisateur n'est pas déjà créateur
        if ($user->isCreator()) {
            return $this->redirectToRoute('app_home');
        }

        $form = $this->createForm(CreatorInfoType::class, $user->getCreatorInfo());
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Ajouter le rôle créateur
            $roleCreator = $entityManager->getRepository(Role::class)->findOneBy(['name' => 'ROLE_CREATOR']);
            if ($roleCreator) {
                $user->addRole($roleCreator);
            }

            // Gestion de l'upload d'image
            if ($coverImage = $form->get('coverImage')->getData()) {
                $newFilename = uniqid().'.'.$coverImage->guessExtension();

                try {
                    $coverImage->move(
                        $this->getParameter('images_directory'),
                        $newFilename
                    );
                    $user->getCreatorInfo()->setCoverImage($newFilename);
                } catch (FileException $e) {
                    $this->addFlash('error', 'Un problème est survenu lors de l\'upload de l\'image');
                }
            }

            $entityManager->flush();

            $this->addFlash('success', 'Félicitations ! Vous êtes maintenant créateur.');
            return $this->redirectToRoute('app_creator_show', ['id' => $user->getId()]);
        }

        return $this->render('creator/become_creator.html.twig', [
            'form' => $form,
        ]);
    }
}
