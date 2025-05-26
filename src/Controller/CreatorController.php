<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Category;
use App\Repository\UserRepository;
use App\Repository\CategoryRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\ORM\EntityManagerInterface;
use App\Form\CreatorInfoType;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class CreatorController extends AbstractController
{
    #[Route('/creators', name: 'creators')]
    public function index(Request $request, UserRepository $userRepository, CategoryRepository $categoryRepository): Response
    {
        $categoryId = $request->query->get('category');
        $sort = $request->query->get('sort', 'newest');

        $category = null;
        if ($categoryId) {
            $category = $categoryRepository->find($categoryId);
        }

        $creators = $userRepository->findCreators($category, $sort);
        $categories = $categoryRepository->findAll();

        $sortOptions = [
            'newest' => 'Plus récents',
            'name_asc' => 'Nom A-Z',
            'name_desc' => 'Nom Z-A',
            'products_count' => 'Nombre de produits'
        ];

        return $this->render('creator/index.html.twig', [
            'creators' => $creators,
            'categories' => $categories,
            'current_category' => $category,
            'current_sort' => $sort,
            'sort_options' => $sortOptions,
        ]);
    }

    #[Route('/creator/{id}', name: 'creator_show')]
    public function show(Request $request, User $user): Response
    {
        // Vérifier si l'utilisateur est bien un créateur
        if (!$user->isCreator()) {
            throw $this->createNotFoundException('Cet utilisateur n\'est pas un créateur.');
        }
        
        return $this->render('creator/show.html.twig', [
            'creator' => $user,
            'active_tab' => $request->query->get('tab', 'products')
        ]);
    }

    #[Route('/creator/{id}/edit', name: 'creator_edit')]
    public function edit(Request $request, User $creator, EntityManagerInterface $entityManager): Response
    {
        // Vérifier que l'utilisateur est bien le propriétaire du profil
        if ($this->getUser() !== $creator || !$creator->getCreatorInfo()) {
            throw $this->createAccessDeniedException('Accès refusé.');
        }

        $form = $this->createForm(CreatorInfoType::class, $creator->getCreatorInfo());
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var UploadedFile|null $coverImageFile */
            $coverImageFile = $form->get('coverImage')->getData();
            $deleteCoverImage = $form->get('deleteCoverImage')->getData();

            // Gestion des catégories
            $newCategories = $form->get('categories')->getData();
            // Supprimer toutes les anciennes catégories
            foreach ($creator->getCategories() as $category) {
                $creator->removeCategory($category);
            }
            // Ajouter les nouvelles catégories sélectionnées
            foreach ($newCategories as $category) {
                $creator->addCategory($category);
            }

            if ($deleteCoverImage) {
                $oldImage = $creator->getCreatorInfo()->getCoverImage();
                if ($oldImage) {
                    $oldImagePath = $this->getParameter('images_directory').'/'.$oldImage;
                    if (file_exists($oldImagePath)) {
                        unlink($oldImagePath);
                    }
                    $creator->getCreatorInfo()->setCoverImage(null);
                }
            } elseif ($coverImageFile) {
                $originalFilename = pathinfo($coverImageFile->getClientOriginalName(), PATHINFO_FILENAME);
                $newFilename = $originalFilename.'-'.uniqid().'.'.$coverImageFile->guessExtension();

                try {
                    $coverImageFile->move(
                        $this->getParameter('images_directory'),
                        $newFilename
                    );
                    
                    // Si une ancienne image existe, la supprimer
                    $oldImage = $creator->getCreatorInfo()->getCoverImage();
                    if ($oldImage) {
                        $oldImagePath = $this->getParameter('images_directory').'/'.$oldImage;
                        if (file_exists($oldImagePath)) {
                            unlink($oldImagePath);
                        }
                    }
                    
                    $creator->getCreatorInfo()->setCoverImage($newFilename);
                } catch (FileException $e) {
                    $this->addFlash('error', 'Une erreur est survenue lors du téléchargement de l\'image');
                }
            }

            $entityManager->flush();
            $this->addFlash('success', 'Votre profil créateur a été mis à jour avec succès !');
            
            return $this->redirectToRoute('creator_show', ['id' => $creator->getId()]);
        }

        return $this->render('creator/edit.html.twig', [
            'form' => $form->createView(),
            'creator' => $creator
        ]);
    }
}