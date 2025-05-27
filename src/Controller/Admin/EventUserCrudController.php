<?php

namespace App\Controller\Admin;

use App\Entity\EventUser;
use App\Enum\EventUserStatus;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ArrayField;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Filter\EntityFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\ChoiceFilter;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_ADMIN')]
class EventUserCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return EventUser::class;
    }    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Participant')
            ->setEntityLabelInPlural('Participants')
            ->setDefaultSort(['createdAt' => 'DESC'])
            ->setPageTitle('index', 'Liste des participants aux événements')
            ->setPageTitle('new', 'Ajouter un participant')
            ->setPageTitle('edit', fn (EventUser $eventUser) => sprintf(
                'Modifier la participation de %s à %s',
                $eventUser->getUser()->getUsername(),
                $eventUser->getEvent()->getTitle()
            ))
            ->setPageTitle('detail', fn (EventUser $eventUser) => sprintf(
                'Participation de %s à %s',
                $eventUser->getUser()->getUsername(),
                $eventUser->getEvent()->getTitle()
            ))
            ->overrideTemplate('crud/detail', 'admin/event_user/detail.html.twig');
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id')->hideOnForm();
        
        yield AssociationField::new('event', 'Événement')
            ->setFormTypeOption('choice_label', 'title');
            
        yield AssociationField::new('user', 'Utilisateur')
            ->setFormTypeOption('choice_label', 'username');
            
        yield ChoiceField::new('status', 'Statut')
            ->setChoices([
                'Organisateur' => EventUserStatus::ORGANIZER,
                'Participant' => EventUserStatus::PARTICIPANT,
                'Intéressé' => EventUserStatus::INTERESTED,
                'Invité' => EventUserStatus::INVITED,
                'A décliné' => EventUserStatus::DECLINED,
            ]);
            
        yield TextareaField::new('comment', 'Commentaire')->hideOnIndex();
        
        yield DateTimeField::new('createdAt', 'Date d\'inscription')->hideOnForm();
        yield DateTimeField::new('statusChangedAt', 'Dernier changement de statut')->hideOnForm();
        yield DateTimeField::new('participationConfirmedAt', 'Confirmation de participation')->hideOnForm()->hideOnIndex();
        yield DateTimeField::new('invitedAt', 'Date d\'invitation')->hideOnForm()->hideOnIndex();
        
        yield ArrayField::new('statusHistory', 'Historique des statuts')->hideOnForm()->hideOnIndex();
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
            ->update(Crud::PAGE_INDEX, Action::NEW, function (Action $action) {
                return $action->setLabel('Ajouter un participant');            });
    }
    
    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add(EntityFilter::new('event', 'Événement'))
            ->add(EntityFilter::new('user', 'Utilisateur'))
            ->add(ChoiceFilter::new('status', 'Statut')->setChoices([
                'Organisateur' => 'organizer',
                'Participant' => 'participant',
                'Intéressé' => 'interested',
                'Invité' => 'invited',
                'A décliné' => 'declined',
            ]));
    }
}
