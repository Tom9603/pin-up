<?php

namespace App\Controller\Admin;

use App\Entity\Comment;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;


class CommentCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Comment::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Commentaire')
            ->setEntityLabelInPlural('Commentaires')
            ->setPageTitle(Crud::PAGE_INDEX, 'Gestion des commentaires');
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
            ->disable(Action::NEW)
            ->update(Crud::PAGE_INDEX, Action::DETAIL, function (Action $action) {
                return $action->setLabel('Consulter');
            });
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            TextEditorField::new('content')
                ->setLabel('Contenu')
                ->onlyOnForms(),
            TextField::new('content')
                ->setLabel('Contenu')
                ->onlyOnDetail()
                ->renderAsHtml(),
            TextField::new('content')
                ->setLabel('Aperçu')
                ->onlyOnIndex()
                ->setMaxLength(150)
                ->renderAsHtml(),

            DateTimeField::new('date_comment')->setLabel('Date du commentaire'),
            AssociationField::new('user')->setLabel('Utilisateur'),
            AssociationField::new('article')->setLabel('Article'),

        ];
    }
}
