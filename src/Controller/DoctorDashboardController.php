<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use App\Repository\AppointmentRepository;
use App\Entity\User;
use App\Service\AppointmentNotificationService;
use Doctrine\ORM\EntityManagerInterface;

#[IsGranted('ROLE_DOCTOR')]
class DoctorDashboardController extends AbstractController
{
    public function __construct(
        private AppointmentNotificationService $notificationService
    ) {
    }
    #[Route('/doctor/dashboard', name: 'doctor_dashboard')]
    public function index(AppointmentRepository $appointmentRepository)
    {
        $doctor = $this->getUser();
        
        // Get all appointments for this doctor
        $appointments = $appointmentRepository->findBy(
            ['doctor' => $doctor],
            ['dateAppointment' => 'ASC']
        );
        
        // Separate appointments by status
        $pendingAppointments = array_filter($appointments, fn($apt) => $apt->getStatus() === 'pending');
        $acceptedAppointments = array_filter($appointments, fn($apt) => $apt->getStatus() === 'accepted');
        $rejectedAppointments = array_filter($appointments, fn($apt) => $apt->getStatus() === 'rejected');
        
        return $this->render('doctor/dashboard.html.twig', [
            'appointments' => $appointments,
            'pendingAppointments' => $pendingAppointments,
            'acceptedAppointments' => $acceptedAppointments,
            'rejectedAppointments' => $rejectedAppointments,
            'totalAppointments' => count($appointments),
        ]);
    }
    #[Route('/doctor/calendar/events', name: 'doctor_calendar_events')]
    public function calendarEvents(AppointmentRepository $repo): JsonResponse
    {
        $appointments = $repo->findBy([
            'doctor' => $this->getUser(),
        ]);

        $events = [];

        foreach ($appointments as $appointment) {
            $patientName = $appointment->getPatient()->getFirstName() . ' ' . $appointment->getPatient()->getLastName();
            $events[] = [
                'id' => $appointment->getIdAppointment(),
                'title' => $patientName . ' - ' . ucfirst($appointment->getStatus()),
                'start' => $appointment->getDateAppointment()->format('Y-m-d\TH:i:s'),
                'status' => $appointment->getStatus(),
                'extendedProps' => [
                    'status' => $appointment->getStatus(),
                    'patient' => $patientName,
                    'patientEmail' => $appointment->getPatient()->getEmail(),
                ]
            ];
        }

        return new JsonResponse($events);
    }
    #[Route('/doctor/toggle-availability', name: 'doctor_toggle_availability')]
    public function toggleAvailability(Request $request, EntityManagerInterface $em)
    {
        /** @var User $user */
        $user = $this->getUser();
        
        if (!$user instanceof User) {
            throw $this->createAccessDeniedException('Invalid user type.');
        }
        
        $user->setIsAvailable(!$user->isAvailable());
        $em->persist($user);
        $em->flush();

        $this->addFlash('success', 'Availability status updated successfully.');

        // Redirect back to the referrer or doctor dashboard
        $referer = $request->headers->get('referer');
        if ($referer) {
            return $this->redirect($referer);
        }
        
        return $this->redirectToRoute('doctor_dashboard');
    }

    #[Route('/doctor/calendar', name: 'doctor_calendar')]
    public function calendar(AppointmentRepository $appointmentRepository)
    {
        /** @var User $doctor */
        $doctor = $this->getUser();
        
        // Get all appointments for the calendar (including pending for visibility)
        $appointments = $appointmentRepository->findBy(
            ['doctor' => $doctor],
            ['dateAppointment' => 'ASC']
        );
        
        return $this->render('doctor/calendar.html.twig', [
            'appointments' => $appointments,
        ]);
    }

    #[Route('/doctor/appointment/{id}/accept', name: 'doctor_appointment_accept')]
    public function acceptAppointment(int $id, AppointmentRepository $appointmentRepository, EntityManagerInterface $em)
    {
        $appointment = $appointmentRepository->find($id);
        
        if (!$appointment || $appointment->getDoctor() !== $this->getUser()) {
            throw $this->createAccessDeniedException('You cannot access this appointment.');
        }

        $appointment->setStatus('accepted');
        $em->flush();

        // Send email notification
        try {
            $this->notificationService->sendAppointmentAcceptedNotification($appointment);
        } catch (\Exception $e) {
            error_log('Failed to send appointment acceptance email: ' . $e->getMessage());
        }

        $this->addFlash('success', 'Appointment accepted successfully. Email notification sent.');

        return $this->redirectToRoute('doctor_dashboard');
    }

    #[Route('/doctor/appointment/{id}/reject', name: 'doctor_appointment_reject')]
    public function rejectAppointment(int $id, AppointmentRepository $appointmentRepository, EntityManagerInterface $em)
    {
        $appointment = $appointmentRepository->find($id);
        
        if (!$appointment || $appointment->getDoctor() !== $this->getUser()) {
            throw $this->createAccessDeniedException('You cannot access this appointment.');
        }

        $appointment->setStatus('rejected');
        $em->flush();

        // Send email notification
        try {
            $this->notificationService->sendAppointmentRejectedNotification($appointment);
        } catch (\Exception $e) {
            error_log('Failed to send appointment rejection email: ' . $e->getMessage());
        }

        $this->addFlash('danger', 'Appointment rejected. Email notification sent.');

        return $this->redirectToRoute('doctor_dashboard');
    }

}
