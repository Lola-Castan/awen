<?php

namespace App\Controller\Admin;

use App\Entity\EventCategory;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_ADMIN')]
class EventCategoryCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return EventCategory::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Catégorie d\'événement')
            ->setEntityLabelInPlural('Catégories d\'événements')
            ->setPageTitle('index', 'Liste des catégories d\'événements')
            ->setPageTitle('new', 'Créer une catégorie d\'événement')
            ->setPageTitle('edit', fn (EventCategory $category) => sprintf('Modifier %s', $category->getName()))
            ->setPageTitle('detail', fn (EventCategory $category) => $category->getName());
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id')->hideOnForm();
        yield TextField::new('name', 'Nom');
        yield TextareaField::new('description', 'Description')->hideOnIndex();
        yield AssociationField::new('events', 'Événements')
            ->setFormTypeOption('choice_label', 'title')
            ->hideOnIndex();
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
            ->update(Crud::PAGE_INDEX, Action::NEW, function (Action $action) {
                return $action->setLabel('Créer une catégorie d\'événement');
            });
    }
}
