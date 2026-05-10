<?php

namespace App\Controller\Admin;

use App\Entity\Product;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\MoneyField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\Field;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use Vich\UploaderBundle\Form\Type\VichImageType;

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
            ->setPageTitle(Crud::PAGE_INDEX, 'Gestion des produits')
            ->setDefaultSort(['id' => 'DESC']);
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
            ->update(Crud::PAGE_INDEX, Action::DETAIL, function (Action $action) {
                return $action->setLabel('Consulter');
            })
            ->update(Crud::PAGE_INDEX, Action::NEW, function (Action $action) {
                return $action->setLabel('Ajouter');
            })
            ->update(Crud::PAGE_INDEX, Action::EDIT, function (Action $action) {
                return $action->setLabel('Modifier');
            })
            ->update(Crud::PAGE_INDEX, Action::DELETE, function (Action $action) {
                return $action->setLabel('Supprimer');
            });
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->setLabel('ID')->onlyOnIndex(),

            ImageField::new('imageName', 'Image')
                ->setBasePath('/uploads/images')
                ->onlyOnIndex(),

            ImageField::new('imageName', 'Image')
                ->setBasePath('/uploads/images')
                ->onlyOnDetail(),

            Field::new('imageFile', 'Image (upload)')
                ->setFormType(VichImageType::class)
                ->onlyOnForms(),

            TextField::new('name')->setLabel('Nom'),

            TextField::new('description')
                ->setLabel('Description')
                ->onlyOnForms(),

            TextField::new('description')
                ->setLabel('Description')
                ->onlyOnDetail(),

            MoneyField::new('price')
                ->setLabel('Prix')
                ->setCurrency('EUR')
                ->setStoredAsCents(true),

            IntegerField::new('stock')
                ->setLabel('Stock')
                ->setHelp('Laisser vide pour stock illimité.'),

            BooleanField::new('isActive')
                ->setLabel('Actif'),
        ];
    }
}
