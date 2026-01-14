<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserProfileType;
use App\Repository\AppointmentRepository;
use App\Repository\MedicalRecordRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\String\Slugger\SluggerInterface;

#[IsGranted('ROLE_USER')]
final class UserDashboardController extends AbstractController
{
    #[Route('/user/dashboard', name: 'app_user_dashboard')]
    public function index(
        AppointmentRepository $appointmentRepo,
        MedicalRecordRepository $medicalRecordRepo,
        EntityManagerInterface $em,
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

        // ===== Fetch ALL appointments for count =====
        $allAppointments = $appointmentRepo->findBy(['patient' => $user]);
        
        // ===== Fetch upcoming appointments for display =====
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
            'allAppointments' => $allAppointments, // For count display
            'medicalRecords' => $medicalRecords,
            'profileForm' => $profileForm->createView(),
        ]);
    }

    #[Route('/user/profile', name: 'app_user_profile')]
    public function profile(
        Request $request,
        EntityManagerInterface $em,
        SluggerInterface $slugger
    ): Response {
        /** @var User $user */
        $user = $this->getUser();

        $profileForm = $this->createForm(UserProfileType::class, $user);
        $profileForm->handleRequest($request);

        if ($profileForm->isSubmitted() && $profileForm->isValid()) {
            /** @var UploadedFile $profilePictureFile */
            $profilePictureFile = $profileForm->get('profilePicture')->getData();

            if ($profilePictureFile) {
                $originalFilename = pathinfo($profilePictureFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename . '-' . uniqid() . '.' . $profilePictureFile->guessExtension();

                try {
                    $profilePictureFile->move(
                        $this->getParameter('kernel.project_dir') . '/public/uploads/profiles',
                        $newFilename
                    );
                    $user->setProfilePicture('uploads/profiles/' . $newFilename);
                } catch (FileException $e) {
                    $this->addFlash('error', 'Failed to upload profile picture.');
                }
            }

            $em->persist($user);
            $em->flush();

            $this->addFlash('success', 'Profile updated successfully!');
            return $this->redirectToRoute('app_user_profile');
        }

        return $this->render('user_dashboard/profile.html.twig', [
            'user' => $user,
            'profileForm' => $profileForm->createView(),
        ]);
    }

    #[Route('/user/profile/{id}', name: 'app_user_profile_view')]
    #[IsGranted('ROLE_USER')]
    public function viewProfile(int $id, UserRepository $userRepository): Response
    {
        $user = $userRepository->find($id);
        
        if (!$user) {
            throw $this->createNotFoundException('User not found');
        }

        return $this->render('user_dashboard/profile_view.html.twig', [
            'profileUser' => $user,
        ]);
    }
}
