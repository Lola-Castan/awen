<?php

namespace App\Controller\Admin;

use App\Entity\User;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ArrayField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_ADMIN')]
class UserCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return User::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Utilisateur')
            ->setEntityLabelInPlural('Utilisateurs')
            ->setDefaultSort(['createdAt' => 'DESC'])
            ->setPageTitle('index', 'Liste des utilisateurs')
            ->setPageTitle('new', 'Créer un utilisateur')
            ->setPageTitle('edit', fn (User $user) => sprintf('Modifier %s', $user->getUsername()))
            ->setPageTitle('detail', fn (User $user) => $user->getUsername());
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id')->hideOnForm();
        yield TextField::new('username', 'Nom d\'utilisateur');
        yield EmailField::new('email', 'Email');
        yield TextField::new('firstName', 'Prénom');
        yield TextField::new('lastName', 'Nom');
        
        if ($pageName === Crud::PAGE_NEW || $pageName === Crud::PAGE_EDIT) {
            yield TextField::new('password', 'Mot de passe')->hideOnIndex()->onlyOnForms();
        }
        
        yield TextareaField::new('bio', 'Biographie')->hideOnIndex();
        yield TextField::new('address1', 'Adresse 1')->hideOnIndex();
        yield TextField::new('address2', 'Adresse 2')->hideOnIndex();
        yield TextField::new('zipCode', 'Code postal')->hideOnIndex();
        yield TextField::new('city', 'Ville')->hideOnIndex();
        yield DateField::new('birthDate', 'Date de naissance')->hideOnIndex();
        yield ImageField::new('profilePicture', 'Photo de profil')
            ->setBasePath('/uploads/images/')
            ->setUploadDir('public/uploads/images/')
            ->hideOnIndex();
        
        yield AssociationField::new('roles', 'Rôles')
            ->setFormTypeOption('choice_label', 'name');
        
        yield BooleanField::new('isVerified', 'Compte vérifié');
        yield DateTimeField::new('createdAt', 'Date de création')->hideOnForm();
        yield DateTimeField::new('updatedAt', 'Date de mise à jour')->hideOnForm();
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
            ->update(Crud::PAGE_INDEX, Action::NEW, function (Action $action) {
                return $action->setLabel('Créer un utilisateur');
            });
    }
}
