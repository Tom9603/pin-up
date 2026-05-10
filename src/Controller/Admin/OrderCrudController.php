<?php

namespace App\Controller\Admin;

use App\Entity\Order;
use App\Enum\OrderStatus;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\MoneyField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Component\HttpFoundation\RedirectResponse;

class OrderCrudController extends AbstractCrudController
{
    public function __construct(
        private EntityManagerInterface $em,
        private AdminUrlGenerator $adminUrlGenerator,
    ) {}

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
        $markPaid = Action::new('markPaid', 'Marquer payée', 'fa fa-check')
            ->linkToCrudAction('markPaid')
            ->displayIf(fn (Order $o) => $o->getStatus() === OrderStatus::Pending);

        $markShipped = Action::new('markShipped', 'Marquer expédiée', 'fa fa-truck')
            ->linkToCrudAction('markShipped')
            ->displayIf(fn (Order $o) => $o->getStatus() === OrderStatus::Paid);

        $markDelivered = Action::new('markDelivered', 'Marquer livrée', 'fa fa-box-check')
            ->linkToCrudAction('markDelivered')
            ->displayIf(fn (Order $o) => $o->getStatus() === OrderStatus::Shipped);

        $markCancelled = Action::new('markCancelled', 'Annuler', 'fa fa-ban')
            ->linkToCrudAction('markCancelled')
            ->displayIf(fn (Order $o) => in_array($o->getStatus(), [OrderStatus::Pending, OrderStatus::Paid], true));

        return $actions
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
            ->add(Crud::PAGE_INDEX, $markPaid)
            ->add(Crud::PAGE_INDEX, $markShipped)
            ->add(Crud::PAGE_INDEX, $markDelivered)
            ->add(Crud::PAGE_INDEX, $markCancelled)
            ->add(Crud::PAGE_DETAIL, $markPaid)
            ->add(Crud::PAGE_DETAIL, $markShipped)
            ->add(Crud::PAGE_DETAIL, $markDelivered)
            ->add(Crud::PAGE_DETAIL, $markCancelled)
            ->disable(Action::NEW)
            ->disable(Action::EDIT)
            ->disable(Action::DELETE)
            ->update(Crud::PAGE_INDEX, Action::DETAIL, fn (Action $a) => $a->setLabel('Consulter'));
    }

    public function configureFields(string $pageName): iterable
    {
        yield FormField::addPanel('Commande');

        yield IdField::new('id')->setLabel('ID');
        yield AssociationField::new('user')->setLabel('Acheteur');
        yield MoneyField::new('total')
            ->setLabel('Montant')
            ->setCurrency('EUR')
            ->setStoredAsCents(true);
        yield ChoiceField::new('status')
            ->setLabel('Statut')
            ->setChoices(OrderStatus::choices())
            ->formatValue(fn ($value) => $value instanceof OrderStatus ? $value->label() : $value);
        yield DateTimeField::new('createdAt')->setLabel('Créée le');

        yield FormField::addPanel('Adresse de livraison')->onlyOnDetail();
        yield TextField::new('shippingName')->setLabel('Destinataire')->onlyOnDetail();
        yield TextField::new('shippingLine1')->setLabel('Adresse')->onlyOnDetail();
        yield TextField::new('shippingLine2')->setLabel('Complément')->onlyOnDetail();
        yield TextField::new('shippingPostalCode')->setLabel('Code postal')->onlyOnDetail();
        yield TextField::new('shippingCity')->setLabel('Ville')->onlyOnDetail();
        yield TextField::new('shippingCountry')->setLabel('Pays')->onlyOnDetail();
        yield TextField::new('shippingPhone')->setLabel('Téléphone')->onlyOnDetail();

        yield FormField::addPanel('Paiement')->onlyOnDetail();
        yield TextField::new('invoiceNumber')
            ->setLabel('N° de facture')
            ->setHelp('Généré automatiquement au paiement (format AAAA-NNNN).');
        yield TextField::new('stripeSessionId')
            ->setLabel('Session Stripe')
            ->setHelp('Identifiant de la session Stripe Checkout.')
            ->onlyOnDetail();
    }

    public function markPaid(AdminContext $context): RedirectResponse
    {
        return $this->changeStatus($context, OrderStatus::Paid);
    }

    public function markShipped(AdminContext $context): RedirectResponse
    {
        return $this->changeStatus($context, OrderStatus::Shipped);
    }

    public function markDelivered(AdminContext $context): RedirectResponse
    {
        return $this->changeStatus($context, OrderStatus::Delivered);
    }

    public function markCancelled(AdminContext $context): RedirectResponse
    {
        return $this->changeStatus($context, OrderStatus::Cancelled);
    }

    private function changeStatus(AdminContext $context, OrderStatus $status): RedirectResponse
    {
        /** @var Order $order */
        $order = $context->getEntity()->getInstance();
        $order->setStatus($status);
        $this->em->flush();

        $this->addFlash('success', sprintf('Commande #%d : statut → %s', $order->getId(), $status->label()));

        $url = $this->adminUrlGenerator
            ->setController(self::class)
            ->setAction(Action::INDEX)
            ->generateUrl();

        return $this->redirect($url);
    }
}
