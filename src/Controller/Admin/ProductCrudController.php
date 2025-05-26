<?php

namespace App\Controller\Admin;

use App\Entity\Product;
use App\Enum\ProductStatus;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\MoneyField;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

class ProductCrudController extends AbstractCrudController
{
	public static function getEntityFqcn(): string
	{
		return Product::class;
	}

	public function configureCrud(Crud $crud): Crud
	{
		return $crud
			->setEntityLabelInSingular('Produit')
			->setEntityLabelInPlural('Produits')
			->setDefaultSort(['createdAt' => 'DESC'])
			->setPageTitle('index', 'Liste des produits')
			->setPageTitle('new', 'Créer un produit')
			->setPageTitle('edit', fn (Product $product) => sprintf('Modifier %s', $product->getName()))
			->setPageTitle('detail', fn (Product $product) => $product->getName());
	}

	public function configureFields(string $pageName): iterable
	{
		yield IdField::new('id')->hideOnForm();
		yield TextField::new('name', 'Nom');
		yield TextField::new('shortDescription', 'Description courte')->hideOnIndex();
		yield TextEditorField::new('longDescription', 'Description détaillée')->hideOnIndex();
		yield NumberField::new('stock', 'Stock');
		yield MoneyField::new('price', 'Prix')->setCurrency('EUR')->setStoredAsCents(true);
		yield NumberField::new('weight', 'Poids (g)')->hideOnIndex();
		yield NumberField::new('width', 'Largeur (mm)')->hideOnIndex();
		yield NumberField::new('depth', 'Profondeur (mm)')->hideOnIndex();
		yield NumberField::new('height', 'Hauteur (mm)')->hideOnIndex();        yield BooleanField::new('showcaseProduct', 'Produit en vitrine');
		yield ChoiceField::new('status', 'Statut')
			->setChoices([
				'Brouillon' => ProductStatus::Draft,
				'Publié' => ProductStatus::Published,
				'Archivé' => ProductStatus::Archived,
			]);
		yield AssociationField::new('categories', 'Catégories')
			->setFormTypeOption('choice_label', 'name');
		yield AssociationField::new('creator', 'Créateur')
			->setFormTypeOption('choice_label', 'username')
			->hideOnForm();
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
				return $action->setLabel('Créer un produit');
			});
	}
}
