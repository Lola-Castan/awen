<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\ProfileType;
use App\Repository\PostRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[Route('/profile')]
class ProfileController extends AbstractController
{
    #[Route('/edit', name: 'app_profile_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, EntityManagerInterface $entityManager, SluggerInterface $slugger): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        if (!$user) {
            throw $this->createAccessDeniedException('Vous devez être connecté pour accéder à cette page.');
        }
        
        // Mettre à jour la date de modification
        $user->setUpdatedAt(new \DateTimeImmutable());

        $form = $this->createForm(ProfileType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Gestion de l'upload de la photo de profil
            $profilePictureFile = $form->get('profilePictureFile')->getData();
            
            if ($profilePictureFile) {
                $originalFilename = pathinfo($profilePictureFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename . '-' . uniqid() . '.' . $profilePictureFile->guessExtension();
                
                try {
                    // Vérifier si le répertoire existe, sinon le créer
                    $targetDirectory = $this->getParameter('profile_pictures_directory');
                    if (!file_exists($targetDirectory)) {
                        mkdir($targetDirectory, 0755, true);
                    }
                    
                    $profilePictureFile->move(
                        $targetDirectory,
                        $newFilename
                    );
                    // Récupérer l'ancienne image
                    // todo bug intelephense étrange
                    $oldFilename = $user->getProfilePicture();
                    
                    // Supprimer l'ancienne image si elle existe
                    if ($oldFilename) {
                        $oldFilePath = $this->getParameter('profile_pictures_directory') . '/' . $oldFilename;
                        if (file_exists($oldFilePath)) {
                            unlink($oldFilePath);
                        }
                    }
                    
                    // Définir la nouvelle image
                    $user->setProfilePicture($newFilename);
                    
                } catch (FileException $e) {
                    $this->addFlash('error', 'Une erreur est survenue lors de l\'upload de votre image : ' . $e->getMessage());
                } catch (\ReflectionException $e) {
                    $this->addFlash('error', 'Une erreur est survenue lors de la manipulation des données : ' . $e->getMessage());
                }
            }

            $entityManager->flush();

            $this->addFlash('success', 'Votre profil a été mis à jour avec succès');
            // Utiliser getUserIdentifier qui est garanti par l'interface UserInterface
            return $this->redirectToRoute('app_profile_show', ['username' => $user->getUserIdentifier()]);
        }

        return $this->render('profile/edit.html.twig', [
            'form' => $form->createView(),
        ]);
    }
    
    #[Route('/{username}', name: 'app_profile_show', methods: ['GET'])]
    public function show(string $username, PostRepository $postRepository, EntityManagerInterface $entityManager): Response
    {
        // Récupérer l'utilisateur par son nom d'utilisateur
        $user = $entityManager->getRepository(User::class)->findOneBy(['username' => $username]);
        
        if (!$user) {
            throw $this->createNotFoundException('Utilisateur non trouvé');
        }

        // Si l'utilisateur est un créateur, rediriger vers son profil créateur
        if ($user->isCreator()) {
            return $this->redirectToRoute('app_creator_show', ['id' => $user->getId()]);
        }
        
        // Récupérer les posts publiés de l'utilisateur
        $posts = $postRepository->findBy(
            ['author' => $user, 'isPublished' => true],
            ['createdAt' => 'DESC']
        );

        return $this->render('profile/show.html.twig', [
            'user' => $user,
            'posts' => $posts,
            'isOwner' => $this->getUser() === $user,
        ]);
    }
}