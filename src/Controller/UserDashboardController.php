<?php

namespace App\Controller;

use App\Form\UserProfileType;
use App\Repository\AppointmentRepository;
use App\Repository\MedicalRecordRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_USER')]
final class UserDashboardController extends AbstractController
{
    #[Route('/user/dashboard', name: 'app_user_dashboard')]
    public function index(
        AppointmentRepository $appointmentRepo,
        MedicalRecordRepository $medicalRecordRepo,
        EntityManagerInterface $em, // inject EntityManager
        Request $request
    ): Response {
        $user = $this->getUser();

        // ===== Create profile form =====
        $profileForm = $this->createForm(UserProfileType::class, $user);
        $profileForm->handleRequest($request);

        if ($profileForm->isSubmitted() && $profileForm->isValid()) {
            $em->persist($user);
            $em->flush();

            $this->addFlash('success', 'Profile updated successfully!');
            return $this->redirectToRoute('app_user_dashboard');
        }

        // ===== Fetch upcoming appointments =====
        $appointments = $appointmentRepo->createQueryBuilder('a')
            ->where('a.patient = :user')
            ->andWhere('a.dateAppointment >= :today')
            ->setParameter('user', $user)
            ->setParameter('today', new \DateTime('today'))
            ->orderBy('a.dateAppointment', 'ASC')
            ->getQuery()
            ->getResult();

        // ===== Fetch medical records =====
        $medicalRecords = $medicalRecordRepo->findBy(['patient' => $user], ['createdAt' => 'DESC']);

        // ===== Render template with profile form =====
        return $this->render('user_dashboard/index.html.twig', [
            'appointments' => $appointments,
            'medicalRecords' => $medicalRecords,
            'profileForm' => $profileForm->createView(), // <-- Pass profile form here
        ]);
    }
}
