<?php

namespace App\Controller\Admin;

use App\Entity\Order;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\MoneyField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

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
            ->setPageTitle(Crud::PAGE_INDEX, 'Gestion des commandes')
            ->setDefaultSort(['createdAt' => 'DESC']);
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
            ->disable(Action::NEW)
            ->disable(Action::EDIT)
            ->disable(Action::DELETE)
            ->update(Crud::PAGE_INDEX, Action::DETAIL, function (Action $action) {
                return $action->setLabel('Consulter');
            });
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->setLabel('ID'),

            AssociationField::new('user')->setLabel('Acheteur'),

            MoneyField::new('total')
                ->setLabel('Montant')
                ->setCurrency('EUR')
                ->setStoredAsCents(true),

            ChoiceField::new('status')
                ->setLabel('Statut')
                ->setChoices([
                    'En attente' => 'pending',
                    'Payée' => 'paid',
                ]),

            // Explication + affichage propre
            TextField::new('stripeSessionId')
                ->setLabel('Session Stripe (Checkout)')
                ->setHelp('Identifiant de la session Stripe Checkout (cs_test_... / cs_live_...). Sert à relier le paiement Stripe à la commande via webhook.')
                ->formatValue(function ($value) use ($pageName) {
                    if (!$value) {
                        return '—';
                    }

                    if ($pageName === Crud::PAGE_INDEX) {
                        return mb_strimwidth($value, 0, 24, '…');
                    }

                    return $value;
                }),

            DateTimeField::new('createdAt')->setLabel('Créée le'),
        ];
    }
}
