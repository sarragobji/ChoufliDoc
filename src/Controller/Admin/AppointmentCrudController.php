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
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use Symfony\Component\HttpFoundation\RedirectResponse;

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
            ChoiceField::new('status')->setChoices([
                'Pending' => 'pending',
                'Accepted' => 'accepted',
                'Rejected' => 'rejected',
            ]),
        ];
    }
    public function configureActions(Actions $actions): Actions
    {
        $accept = Action::new('accept', 'Accept')
        ->linkToCrudAction('acceptAppointment')
        ->setCssClass('btn btn-success')
        ->displayIf(fn ($entity) => $entity->getStatus() === 'pending');

        $reject = Action::new('reject', 'Reject')
            ->linkToCrudAction('rejectAppointment')
            ->setCssClass('btn btn-danger')
            ->displayIf(fn ($entity) => $entity->getStatus() === 'pending');

        return $actions
            ->add(Crud::PAGE_INDEX, $accept)
            ->add(Crud::PAGE_INDEX, $reject);
    }
    public function acceptAppointment(AdminContext $context): RedirectResponse
{
        $appointment = $context->getEntity()->getInstance();

        $appointment->setStatus('accepted');

        $this->doctrine->getManager()->flush();

        $this->addFlash('success', 'Appointment accepted');

        return $this->redirect($context->getReferrer());
}

    public function rejectAppointment(AdminContext $context): RedirectResponse
    {
        $appointment = $context->getEntity()->getInstance();
        $appointment->setStatus('rejected');
        $this->doctrine->getManager()->flush();
        $this->addFlash('danger', 'Appointment rejected');
        return $this->redirect($context->getReferrer());
    }

}
