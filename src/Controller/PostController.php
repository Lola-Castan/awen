<?php

namespace App\Controller;

use App\Entity\Post;
use App\Entity\User;
use App\Form\PostType;
use App\Repository\PostRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/posts')]
class PostController extends AbstractController
{
    #[Route('/', name: 'app_post_index', methods: ['GET'])]
    public function index(PostRepository $postRepository): Response
    {
        return $this->render('post/index.html.twig', [
            'posts' => $postRepository->findPublished(),
        ]);
    }

    #[Route('/author/{id}', name: 'app_post_by_author', methods: ['GET'])]
    public function byAuthor(User $user, PostRepository $postRepository): Response
    {
        return $this->render('post/index.html.twig', [
            'posts' => $postRepository->findByAuthor($user),
            'author' => $user,
        ]);
    }

    #[Route('/new', name: 'app_post_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        // Vérifier que l'utilisateur est connecté
        $user = $this->getUser();
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }
        
        // Créer un nouveau post
        $post = new Post();
        $post->setAuthor($user);
        
        // Créer le formulaire (il faudra créer un PostType)
        $form = $this->createForm(PostType::class, $post);
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($post);
            $entityManager->flush();
            
            $this->addFlash('success', 'Votre post a été créé avec succès.');
            
            return $this->redirectToRoute('app_post_show', ['id' => $post->getId()]);
        }
        
        return $this->render('post/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'app_post_show', methods: ['GET'])]
    public function show(Post $post): Response
    {
        // Vérifie que le post est publié ou que l'utilisateur est l'auteur
        if (!$post->isIsPublished() && 
            ($this->getUser() === null || $post->getAuthor() !== $this->getUser())) {
            throw $this->createAccessDeniedException('Ce post n\'est pas accessible.');
        }
        
        return $this->render('post/show.html.twig', [
            'post' => $post,
        ]);
    }

    #[Route('/{id}/delete', name: 'app_post_delete', methods: ['POST'])]
    public function delete(Request $request, Post $post, EntityManagerInterface $entityManager): Response
    {
        // Vérification que l'utilisateur est l'auteur ou un administrateur
        if ($this->getUser() !== $post->getAuthor() && !$this->isGranted('ROLE_ADMIN')) {
            throw $this->createAccessDeniedException('Vous n\'êtes pas autorisé à supprimer ce post.');
        }
        
        // Vérification du token CSRF
        if ($this->isCsrfTokenValid('delete'.$post->getId(), $request->request->get('_token'))) {
            // Suppression des images associées au post (si nécessaire)
            // Si vous avez des images à supprimer, ajoutez le code ici
            
            // Suppression du post
            $entityManager->remove($post);
            $entityManager->flush();
            
            $this->addFlash('success', 'Le post a été supprimé avec succès.');
        }
          // Redirection vers la page de profil de l'auteur
        return $this->redirectToRoute('app_profile_show', ['username' => $post->getAuthor()->getUserIdentifier()]);
    }

    #[Route('/{id}/edit', name: 'app_post_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Post $post, EntityManagerInterface $entityManager): Response
    {
        // Vérifier que l'utilisateur est l'auteur du post
        if ($this->getUser() !== $post->getAuthor() && !$this->isGranted('ROLE_ADMIN')) {
            throw $this->createAccessDeniedException('Vous n\'êtes pas autorisé à modifier ce post.');
        }
        
        $form = $this->createForm(PostType::class, $post);
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            // Mettre à jour la date de modification
            $post->setUpdatedAt(new \DateTimeImmutable());
            
            $entityManager->flush();
            
            $this->addFlash('success', 'Votre post a été modifié avec succès.');
            
            return $this->redirectToRoute('app_post_show', ['id' => $post->getId()]);
        }
        
        return $this->render('post/edit.html.twig', [
            'post' => $post,
            'form' => $form->createView(),
        ]);
    }
}