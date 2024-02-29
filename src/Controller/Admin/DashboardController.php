<?php

namespace App\Controller\Admin;

use App\Entity\Category;
use App\Entity\City;
use App\Entity\Delivery;
use App\Entity\DeliveryTime;
use App\Entity\Formule;
use App\Entity\Order;
use App\Entity\Producter;
use App\Entity\Products;
use App\Entity\User;
use App\Entity\WorkSchedule;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DashboardController extends AbstractDashboardController
{
    #[Route('/admin', name: 'admin')]
    public function index(): Response
    {
        

        // Option 1. You can make your dashboard redirect to some common page of your backend

        $adminUrlGenerator = $this->container->get(AdminUrlGenerator::class);

        return $this->redirect($adminUrlGenerator->setController(UserCrudController::class)->generateUrl());

        // Option 2. You can make your dashboard redirect to different pages depending on the user
        //
        // if ('jane' === $this->getUser()->getUsername()) {
        //     return $this->redirect('...');
        // }

        // Option 3. You can render some custom template to display a proper dashboard with widgets, etc.
        // (tip: it's easier if your template extends from @EasyAdmin/page/content.html.twig)
        //
        // return $this->render('some/path/my-dashboard.html.twig');
    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('Mon Ptit Matin');
    }

    public function configureMenuItems(): iterable
    {
        yield MenuItem::linkToDashboard('Dashboard', 'fa fa-home');
        yield MenuItem::linkToCrud('Utilisateur', 'fas fa-user', User::class);
        yield MenuItem::linkToCrud('Formules', 'fas fa-cutlery', Formule::class);
        yield MenuItem::linkToCrud('Partenaires', 'fas fa-heart', Producter::class);
        yield MenuItem::linkToCrud('Catégories', 'fas fa-list', Category::class);
        yield MenuItem::linkToCrud('Produits', 'fas fa-tag', Products::class);
        yield MenuItem::linkToCrud('Ville', 'fas fa-city', City::class);
        yield MenuItem::linkToCrud('Livraison', 'fas fa-truck', Delivery::class);
        yield MenuItem::linkToCrud('Horaires de travail', 'fas fa-clock', WorkSchedule::class);
        yield MenuItem::linkToCrud('Plage horaire', 'fas fa-clock', DeliveryTime::class);
        yield MenuItem::linkToCrud('Commande', 'fas fa-shopping-cart ', Order::class);

    }
}
