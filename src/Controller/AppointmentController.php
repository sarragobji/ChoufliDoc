<?php

namespace App\Controller;

use App\Entity\Appointment;
use App\Form\AppointmentType;
use App\Repository\AppointmentRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/appointment')]
class AppointmentController extends AbstractController
{
    /* ==========================
     * ADMIN – SEE ALL
     * ========================== */
    #[IsGranted('ROLE_ADMIN')]
    #[Route('/', name: 'appointment_index', methods: ['GET'])]
    public function index(AppointmentRepository $appointmentRepository): Response
    {
        return $this->render('appointment/index.html.twig', [
            'appointments' => $appointmentRepository->findAll(),
        ]);
    }

    /* ==========================
     * PATIENT – CREATE
     * ========================== */
    #[IsGranted('ROLE_USER')]
    #[Route('/new', name: 'appointment_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        $appointment = new Appointment();
        $appointment->setPatient($this->getUser()); // FORCE patient

        $form = $this->createForm(AppointmentType::class, $appointment, [
            'role' => 'patient', // IMPORTANT
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($appointment);
            $em->flush();

            return $this->redirectToRoute('appointment_my');
        }

        return $this->render('appointment/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /* ==========================
     * PATIENT – SEE OWN
     * ========================== */
    #[IsGranted('ROLE_USER')]
    #[Route('/my', name: 'appointment_my', methods: ['GET'])]
    public function myAppointments(AppointmentRepository $repo): Response
    {
        return $this->render('appointment/my.html.twig', [
            'appointments' => $repo->findBy([
                'patient' => $this->getUser(),
            ]),
        ]);
    }

    /* ==========================
     * DOCTOR – SEE ASSIGNED
     * ========================== */
    #[IsGranted('ROLE_DOCTOR')]
    #[Route('/doctor', name: 'appointment_doctor', methods: ['GET'])]
    public function doctorAppointments(AppointmentRepository $repo): Response
    {
        return $this->render('appointment/doctor.html.twig', [
            'appointments' => $repo->findBy([
                'doctor' => $this->getUser(),
            ]),
        ]);
    }

    /* ==========================
     * ADMIN – EDIT
     * ========================== */
    #[IsGranted('ROLE_ADMIN')]
    #[Route('/{id}/edit', name: 'appointment_edit', methods: ['GET', 'POST'])]
    public function edit(
        Request $request,
        Appointment $appointment,
        EntityManagerInterface $em
    ): Response {
        $form = $this->createForm(AppointmentType::class, $appointment, [
            'role' => 'admin',
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();

            return $this->redirectToRoute('appointment_index');
        }

        return $this->render('appointment/edit.html.twig', [
            'form' => $form->createView(),
            'appointment' => $appointment,
        ]);
    }

    /* ==========================
     * ADMIN – DELETE
     * ========================== */
    #[IsGranted('ROLE_ADMIN')]
    #[Route('/{id}', name: 'appointment_delete', methods: ['POST'])]
    public function delete(
        Request $request,
        Appointment $appointment,
        EntityManagerInterface $em
    ): Response {
        if ($this->isCsrfTokenValid('delete'.$appointment->getIdAppointment(), $request->request->get('_token'))) {
            $em->remove($appointment);
            $em->flush();
        }

        return $this->redirectToRoute('appointment_index');
    }
}
