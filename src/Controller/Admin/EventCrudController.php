<?php

namespace App\Controller\Admin;

use App\Entity\Event;
use App\Enum\EventStatus;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_CREATOR')]
class EventCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Event::class;
    }    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Événement')
            ->setEntityLabelInPlural('Événements')
            ->setDefaultSort(['createdAt' => 'DESC'])
            ->setPageTitle('index', 'Liste des événements')
            ->setPageTitle('new', 'Créer un événement')
            ->setPageTitle('edit', fn (Event $event) => sprintf('Modifier %s', $event->getTitle()))
            ->setPageTitle('detail', fn (Event $event) => $event->getTitle())
            ->overrideTemplate('crud/detail', 'admin/event/detail.html.twig');
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id')->hideOnForm();
        yield TextField::new('title', 'Titre');        yield TextField::new('shortDescription', 'Description courte')->hideOnIndex();
        yield TextEditorField::new('longDescription', 'Description détaillée')->hideOnIndex();
        yield TextField::new('location', 'Lieu');
        yield DateTimeField::new('startDateTime', 'Date de début')
            ->setFormat('dd/MM/yyyy HH:mm');
        yield DateTimeField::new('endDateTime', 'Date de fin')
            ->setFormat('dd/MM/yyyy HH:mm');yield ChoiceField::new('status', 'Statut')
            ->setChoices([
                'Brouillon' => EventStatus::Draft,
                'Publié' => EventStatus::Published,
                'Archivé' => EventStatus::Archived,
                'Annulé' => EventStatus::Cancelled,
            ]);
        yield AssociationField::new('eventCategories', 'Catégories')
            ->setFormTypeOption('choice_label', 'name');
        yield AssociationField::new('images', 'Images')
            ->setFormTypeOption('choice_label', 'title')
            ->hideOnIndex();
        yield DateTimeField::new('createdAt', 'Date de création')->hideOnForm();
        yield DateTimeField::new('updatedAt', 'Date de mise à jour')->hideOnForm();
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
            ->update(Crud::PAGE_INDEX, Action::NEW, function (Action $action) {
                return $action->setLabel('Créer un événement');
            });
    }
}
