<?php

namespace App\Controller\Admin;

use App\Entity\Event;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;

class EventCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Event::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Événement')
            ->setEntityLabelInPlural('Événements')
            ->setPageTitle(Crud::PAGE_INDEX, 'Gestion des événements');
    }

    public function configureFields(string $pageName): iterable
    {
        return [
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

            CollectionField::new('medias')
                ->setLabel('Médias')
                ->useEntryCrudForm(MediaCrudController::class)
                ->allowAdd()
                ->allowDelete()
                ->setFormTypeOptions([
                    'label' => 'Média'
                ])
                ->onlyOnForms(),

            CollectionField::new('medias')
                ->setLabel('Médias')
                ->onlyOnDetail()
                ->setTemplatePath('admin/fields/media_preview.html.twig')
                ->setFormTypeOptions([
                'label' => 'Média',
                ]),

            CollectionField::new('medias')
                ->setLabel('Média')
                ->onlyOnIndex()
                ->setFormTypeOptions([
                    'label' => 'Média'
            ]),

            DateTimeField::new('start')->setLabel('Date de début')->setRequired(true),
            DateTimeField::new('end')->setLabel('Date de fin')->setRequired(true),

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
