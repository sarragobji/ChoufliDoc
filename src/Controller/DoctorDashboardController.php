<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Repository\AppointmentRepository;
use Doctrine\ORM\EntityManagerInterface;

#[IsGranted('ROLE_DOCTOR')]
class DoctorDashboardController extends AbstractController
{
    #[Route('/doctor/dashboard', name: 'doctor_dashboard')]
    public function index()
    {
        return $this->render('doctor/dashboard.html.twig');
    }
    #[Route('/doctor/calendar/events', name: 'doctor_calendar_events')]
    public function calendarEvents(AppointmentRepository $repo): JsonResponse
    {
        $appointments = $repo->findBy([
            'doctor' => $this->getUser(),
            'status' => 'accepted',
        ]);

        $events = [];

        foreach ($appointments as $appointment) {
            $events[] = [
                'title' => $appointment->getPatient()->getFirstName(),
                'start' => $appointment->getDateAppointment()->format('Y-m-d H:i'),
            ];
        }

        return new JsonResponse($events);
    }
    #[Route('/doctor/toggle-availability', name: 'doctor_toggle_availability')]
    public function toggleAvailability(EntityManagerInterface $em)
    {
        $user = $this->getUser();
        $user->setIsAvailable(!$user->getIsAvailable());

        $em->flush();

        return $this->redirectToRoute('doctor_dashboard');
    }

}
