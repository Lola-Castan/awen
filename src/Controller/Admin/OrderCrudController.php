<?php

namespace App\Controller\Admin;

use App\Entity\Order;
use App\Enum\OrderStatus;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\MoneyField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Filter\EntityFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\ChoiceFilter;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_ADMIN')]
class OrderCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Order::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Commande')
            ->setEntityLabelInPlural('Commandes')
            ->setDefaultSort(['createdAt' => 'DESC'])
            ->setPageTitle('index', 'Liste des commandes')
            ->setPageTitle('new', 'Créer une commande')
            ->setPageTitle('edit', fn (Order $order) => sprintf('Modifier la commande #%d', $order->getId()))
            ->setPageTitle('detail', fn (Order $order) => sprintf('Commande #%d', $order->getId()))
            ->overrideTemplate('crud/detail', 'admin/order/detail.html.twig');
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id')->hideOnForm();
        
        yield AssociationField::new('user', 'Client')
            ->setFormTypeOption('choice_label', 'username');
            
        yield ChoiceField::new('status', 'Statut')
            ->setChoices([
                'En attente' => OrderStatus::Pending,
                'Payée' => OrderStatus::Paid,
                'En préparation' => OrderStatus::Processing,
                'Expédiée' => OrderStatus::Shipped,
                'Livrée' => OrderStatus::Delivered,
                'Annulée' => OrderStatus::Cancelled,
                'Remboursée' => OrderStatus::Refunded,
            ]);
            
        yield MoneyField::new('totalHT', 'Total HT')
            ->setCurrency('EUR')
            ->setStoredAsCents(false);
            
        yield MoneyField::new('tvaAmount', 'TVA')
            ->setCurrency('EUR')
            ->setStoredAsCents(false);
            
        yield MoneyField::new('shippingCost', 'Frais de port')
            ->setCurrency('EUR')
            ->setStoredAsCents(false);
            
        yield MoneyField::new('totalTTC', 'Total TTC')
            ->setCurrency('EUR')
            ->setStoredAsCents(false);
            
        yield TextField::new('paymentMethod', 'Méthode de paiement');
        yield TextField::new('shippingAddress', 'Adresse de livraison')->hideOnIndex();
        yield TextField::new('billingAddress', 'Adresse de facturation')->hideOnIndex();
        
        yield DateTimeField::new('createdAt', 'Date de création')->hideOnForm();
        yield DateTimeField::new('expectedDeliveryDate', 'Date de livraison prévue')->hideOnIndex();
        yield DateTimeField::new('deliveredAt', 'Date de livraison')->hideOnIndex();
        
        yield AssociationField::new('orderDetails', 'Détails')
            ->onlyOnDetail();
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
            ->update(Crud::PAGE_INDEX, Action::NEW, function (Action $action) {
                return $action->setLabel('Créer une commande');
            });
    }
    
    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add(EntityFilter::new('user', 'Client'))
            ->add(ChoiceFilter::new('status', 'Statut')->setChoices([
                'En attente' => 'pending',
                'Payée' => 'paid',
                'En préparation' => 'processing',
                'Expédiée' => 'shipped',
                'Livrée' => 'delivered',
                'Annulée' => 'cancelled',
                'Remboursée' => 'refunded',
            ]));
    }
} 