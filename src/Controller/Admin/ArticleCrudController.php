<?php

namespace App\Controller\Admin;

use App\Entity\Article;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;


class ArticleCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Article::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Article')
            ->setEntityLabelInPlural('Articles')
            ->setPageTitle(Crud::PAGE_INDEX, 'Gestion des articles');
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            AssociationField::new('category')->setLabel('Catégorie'),

            TextField::new('title')->setLabel('Titre'),

            TextEditorField::new('content')
                ->setLabel('Contenu')
                ->onlyOnForms(),

            TextareaField::new('content')
                ->setLabel('Contenu')
                ->hideOnForm()
                ->onlyOnIndex()
                ->renderAsHtml()
                ->setMaxLength(80),

            TextareaField::new('content')
                ->setLabel('Contenu')
                ->renderAsHtml()
                ->onlyOnDetail(),

            CollectionField::new('medias', 'Média')
                ->setLabel('Média')
                ->useEntryCrudForm(MediaCrudController::class)
                ->allowAdd()
                ->allowDelete()
                ->onlyOnForms(),

            CollectionField::new('medias', 'Média')
                ->setLabel('Média')
                ->onlyOnDetail()
                ->setTemplatePath('admin/fields/media_preview.html.twig'),

            CollectionField::new('medias', 'Média')
                ->setLabel('Média')
                ->onlyOnIndex(),

            DateTimeField::new('date_publication')
                ->setLabel('Date de publication')
                ->setFormat('dd MMM yyyy'),
        ];
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
            ->update(Crud::PAGE_INDEX, Action::DETAIL, function (Action $action) {
                return $action->setLabel('Consulter');
            });
    }
}
