<?php

namespace App\Controller\Admin;

use App\Entity\Appointment;
use App\Entity\User;
use App\Entity\MedicalRecord;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminDashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Component\HttpFoundation\Response;

#[AdminDashboard(routePath: '/admin', routeName: 'admin')]
class AdminDashboardController extends AbstractDashboardController
{
    public function index(): Response
    {
        $adminUrlGenerator = $this->container->get(AdminUrlGenerator::class);

        return $this->redirect(
            $adminUrlGenerator
                ->setController(AppointmentCrudController::class)
                ->generateUrl()
        );

    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('🩺 ChoufliDoc - Admin Dashboard');
    }

    public function configureMenuItems(): iterable
    {
        yield MenuItem::linkToDashboard('Dashboard', 'fa fa-home');
        yield MenuItem::section('Management');
        yield MenuItem::linkToCrud('Manage Appointments', 'fa fa-calendar', Appointment::class);
        yield MenuItem::linkToCrud('Manage Users', 'fa fa-users', User::class);
        yield MenuItem::linkToCrud('Manage Medical Records', 'fa fa-file-medical', MedicalRecord::class);
    }
}
