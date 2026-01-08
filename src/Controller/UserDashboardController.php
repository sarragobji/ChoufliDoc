<?php

namespace App\Controller;

use App\Repository\AppointmentRepository;
use App\Repository\MedicalRecordRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_USER')]
final class UserDashboardController extends AbstractController
{
    #[Route('/user/dashboard', name: 'app_user_dashboard')]
    public function index(
        AppointmentRepository $appointmentRepo,
        MedicalRecordRepository $medicalRecordRepo
    ): Response {
        $user = $this->getUser();
        // Fetch upcoming appointments for the user
        $appointments = $appointmentRepo->createQueryBuilder('a')
            ->where('a.patient = :user')
            ->andWhere('a.dateAppointment >= :today')
            ->setParameter('user', $user)
            ->setParameter('today', new \DateTime('today'))
            ->orderBy('a.dateAppointment', 'ASC')
            ->getQuery()
            ->getResult();
        // Fetch medical records for the user
        $medicalRecords = $medicalRecordRepo->findBy(['patient' => $user], ['createdAt' => 'DESC']);

        // Render the dashboard template with fetched data
        return $this->render('user_dashboard/index.html.twig', [
            'appointments' => $appointments,
            'medicalRecords' => $medicalRecords,
        ]);
    }
}
