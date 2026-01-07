<?php

namespace App\Controller\Admin;

use App\Entity\Appointment;
use App\Entity\User;
use Doctrine\Persistence\ManagerRegistry;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField; 

class AppointmentCrudController extends AbstractCrudController
{
    private ManagerRegistry $doctrine;

    public function __construct(ManagerRegistry $doctrine)
    {
        $this->doctrine = $doctrine;
    }

    public static function getEntityFqcn(): string
    {
        return Appointment::class;
    }

    public function configureFields(string $pageName): iterable
    {
        // Preselect the dedicated doctor (id = 3) only when creating a new Appointment
        $doctor = null;
        if ($pageName === Crud::PAGE_NEW) {
            $doctor = $this->doctrine->getRepository(User::class)->find(3);
        }

        return [
            IdField::new('idAppointment')->hideOnForm(),
            DateField::new('dateAppointment'),
            AssociationField::new('patient'),
            AssociationField::new('doctor')->setFormTypeOption('data', $doctor),
        ];
    }
}
