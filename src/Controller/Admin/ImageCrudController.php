<?php

namespace App\Controller\Admin;

use App\Entity\Image;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_ADMIN')]
class ImageCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Image::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Image')
            ->setEntityLabelInPlural('Images')
            ->setDefaultSort(['createdAt' => 'DESC'])
            ->setPageTitle('index', 'Liste des images')
            ->setPageTitle('new', 'Ajouter une image')
            ->setPageTitle('edit', fn (Image $image) => sprintf('Modifier %s', $image->getTitle() ?? $image->getFilename()))
            ->setPageTitle('detail', fn (Image $image) => $image->getTitle() ?? $image->getFilename());
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id')->hideOnForm();
        yield TextField::new('filename', 'Nom du fichier');
        yield TextField::new('alt', 'Texte alternatif');
        yield TextField::new('title', 'Titre');
        yield IntegerField::new('position', 'Position');
        yield ImageField::new('path', 'Aperçu')
            ->setBasePath('/uploads/images/')
            ->hideOnForm();
        yield AssociationField::new('products', 'Produits')
            ->setFormTypeOption('choice_label', 'name')
            ->hideOnIndex();
        yield AssociationField::new('events', 'Événements')
            ->setFormTypeOption('choice_label', 'title')
            ->hideOnIndex();
        yield AssociationField::new('posts', 'Posts')
            ->setFormTypeOption('choice_label', 'title')
            ->hideOnIndex();
        yield DateTimeField::new('createdAt', 'Date de création')->hideOnForm();
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
            ->update(Crud::PAGE_INDEX, Action::NEW, function (Action $action) {
                return $action->setLabel('Ajouter une image');
            });
    }
}
