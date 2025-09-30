<?php

namespace App\Controller\Admin;

use App\Entity\Event;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminDashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use Symfony\Component\HttpFoundation\Response;
use App\Entity\Article;
use App\Entity\Category;
use App\Entity\Comment;
use App\Entity\User;
use App\Entity\Image;
use Symfony\Component\Routing\Attribute\Route;

#[AdminDashboard(routePath: '/admin', routeName: 'admin')]
class DashboardController extends AbstractDashboardController
{
    public function index(): Response
    {
        return $this->render('admin/dashboard/index.html.twig');
    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('Comité Miss Pin-Up Bretagne');
    }

    public function configureMenuItems(): iterable
    {
        yield MenuItem::linkToDashboard('Dashboard', 'fa fa-home');
        yield MenuItem::linkToCrud('Évènement', 'fa fa-calendar', Event::class);
        yield MenuItem::linkToCrud('Catégorie', 'fa fa-list', Category::class);
        yield MenuItem::linkToCrud('Article', 'fa fa-newspaper', Article::class);
        yield MenuItem::linkToCrud('Commentaire', 'fa fa-comments', Comment::class);
        yield MenuItem::linkToCrud('Utilisateur', 'fa fa-users', User::class);
        yield MenuItem::linkToCrud('Carrousel', 'fa fa-camera', Image::class);
        yield MenuItem::section('   ');
        yield MenuItem::section('   ');
        yield MenuItem::section('   ');
        yield MenuItem::linkToUrl('Retour au site', 'fa fa-arrow-left', $this->generateUrl('app_home'));
    }
}
