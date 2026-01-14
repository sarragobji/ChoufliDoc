<?php

namespace App\Controller\Admin;

use App\Entity\Appointment;
use App\Entity\User;
use App\Repository\AppointmentRepository;
use App\Service\AppointmentNotificationService;
use Doctrine\Persistence\ManagerRegistry;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField; 
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class AppointmentCrudController extends AbstractCrudController
{
    private ManagerRegistry $doctrine;

    public function __construct(
        ManagerRegistry $doctrine,
        private AppointmentNotificationService $notificationService
    ) {
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

        $patientField = AssociationField::new('patient');
        
        // Make patient name clickable on index page
        if ($pageName === Crud::PAGE_INDEX) {
            $patientField->formatValue(function ($value, $entity) {
                if ($entity && $entity->getPatient()) {
                    $patient = $entity->getPatient();
                    $patientId = $patient->getIdUser();
                    $patientName = $patient->getFirstName() . ' ' . $patient->getLastName();
                    $url = $this->generateUrl('app_user_profile_view', ['id' => $patientId]);
                    return sprintf('<a href="%s" target="_blank" style="color: #007bff; text-decoration: none;">%s</a>', 
                        htmlspecialchars($url, ENT_QUOTES, 'UTF-8'), 
                        htmlspecialchars($patientName, ENT_QUOTES, 'UTF-8'));
                }
                return $value ?: '';
            })->renderAsHtml();
        }

        return [
            IdField::new('idAppointment')->hideOnForm(),
            DateField::new('dateAppointment'),
            $patientField,
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
            ->linkToRoute('admin_appointment_accept', function ($entity) {
                return ['id' => $entity->getIdAppointment()];
            })
            ->setCssClass('btn btn-success')
            ->displayIf(fn ($entity) => $entity->getStatus() === 'pending');

        $reject = Action::new('reject', 'Reject')
            ->linkToRoute('admin_appointment_reject', function ($entity) {
                return ['id' => $entity->getIdAppointment()];
            })
            ->setCssClass('btn btn-danger')
            ->displayIf(fn ($entity) => $entity->getStatus() === 'pending');

        return $actions
            ->add(Crud::PAGE_INDEX, $accept)
            ->add(Crud::PAGE_INDEX, $reject);
    }

    #[Route('/admin/appointment/{id}/accept', name: 'admin_appointment_accept')]
    public function acceptAppointment(int $id, AppointmentRepository $appointmentRepository, EntityManagerInterface $em): RedirectResponse
    {
        $appointment = $appointmentRepository->find($id);
        
        if (!$appointment) {
            $this->addFlash('danger', 'Appointment not found.');
            return $this->redirect($this->generateUrl('admin_appointment_index'));
        }

        $appointment->setStatus('accepted');
        $em->flush();

        // Send email notification
        try {
            $this->notificationService->sendAppointmentAcceptedNotification($appointment);
        } catch (\Exception $e) {
            // Log error but don't fail the request
            error_log('Failed to send appointment acceptance email: ' . $e->getMessage());
        }

        $this->addFlash('success', 'Appointment accepted successfully. Email notification sent.');

        return $this->redirect($this->generateUrl('admin_appointment_index'));
    }

    #[Route('/admin/appointment/{id}/reject', name: 'admin_appointment_reject')]
    public function rejectAppointment(int $id, AppointmentRepository $appointmentRepository, EntityManagerInterface $em): RedirectResponse
    {
        $appointment = $appointmentRepository->find($id);
        
        if (!$appointment) {
            $this->addFlash('danger', 'Appointment not found.');
            return $this->redirect($this->generateUrl('admin_appointment_index'));
        }

        $appointment->setStatus('rejected');
        $em->flush();

        // Send email notification
        try {
            $this->notificationService->sendAppointmentRejectedNotification($appointment);
        } catch (\Exception $e) {
            // Log error but don't fail the request
            error_log('Failed to send appointment rejection email: ' . $e->getMessage());
        }

        $this->addFlash('danger', 'Appointment rejected. Email notification sent.');

        return $this->redirect($this->generateUrl('admin_appointment_index'));
    }

}
