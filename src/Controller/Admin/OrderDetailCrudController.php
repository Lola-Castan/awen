<?php

namespace App\Controller\Admin;

use App\Entity\OrderDetail;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\MoneyField;
use EasyCorp\Bundle\EasyAdminBundle\Field\PercentField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Filter\EntityFilter;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_ADMIN')]
class OrderDetailCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return OrderDetail::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Détail de commande')
            ->setEntityLabelInPlural('Détails de commandes')
            ->setDefaultSort(['id' => 'DESC'])
            ->setPageTitle('index', 'Liste des détails de commandes')
            ->setPageTitle('new', 'Ajouter un détail de commande')
            ->setPageTitle('edit', fn (OrderDetail $orderDetail) => sprintf(
                'Modifier le détail #%d de la commande #%d',
                $orderDetail->getId(),
                $orderDetail->getOrderRef()->getId()
            ))
            ->setPageTitle('detail', fn (OrderDetail $orderDetail) => sprintf(
                'Détail #%d de la commande #%d',
                $orderDetail->getId(),
                $orderDetail->getOrderRef()->getId()
            ));
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id')->hideOnForm();
        
        yield AssociationField::new('orderRef', 'Commande')
            ->setFormTypeOption('choice_label', 'id');
            
        yield AssociationField::new('product', 'Produit')
            ->setFormTypeOption('choice_label', 'name');
            
        yield IntegerField::new('quantity', 'Quantité');
        
        yield MoneyField::new('unitPriceHT', 'Prix unitaire HT')
            ->setCurrency('EUR')
            ->setStoredAsCents(false);
            
        yield MoneyField::new('unitPriceTTC', 'Prix unitaire TTC')
            ->setCurrency('EUR')
            ->setStoredAsCents(false);
            
        yield MoneyField::new('totalPriceHT', 'Total HT')
            ->setCurrency('EUR')
            ->setStoredAsCents(false);
            
        yield MoneyField::new('totalPriceTTC', 'Total TTC')
            ->setCurrency('EUR')
            ->setStoredAsCents(false);
            
        yield PercentField::new('discountPercentage', 'Remise')
            ->setNumDecimals(2)
            ->setStoredAsFractional(false);
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
            ->update(Crud::PAGE_INDEX, Action::NEW, function (Action $action) {
                return $action->setLabel('Ajouter un détail');
            });
    }
    
    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add(EntityFilter::new('orderRef', 'Commande'))
            ->add(EntityFilter::new('product', 'Produit'));
    }
} 