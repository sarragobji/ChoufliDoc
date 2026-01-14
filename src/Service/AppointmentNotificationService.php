<?php

namespace App\Service;

use App\Entity\Appointment;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;

class AppointmentNotificationService
{
    public function __construct(
        private MailerInterface $mailer
    ) {
    }

    public function sendAppointmentAcceptedNotification(Appointment $appointment): void
    {
        $patient = $appointment->getPatient();
        $doctor = $appointment->getDoctor();

        $email = (new TemplatedEmail())
            ->from(new Address('CabinetDR.Labyedh.Slimen@gmail.com', 'ChoufliDoc'))
            ->to($patient->getEmail())
            ->subject('Appointment Accepted - ChoufliDoc')
            ->htmlTemplate('emails/appointment_accepted.html.twig')
            ->context([
                'patient' => $patient,
                'doctor' => $doctor,
                'appointment' => $appointment,
                'appointmentDate' => $appointment->getDateAppointment(),
            ]);

        $this->mailer->send($email);
    }

    public function sendAppointmentRejectedNotification(Appointment $appointment): void
    {
        $patient = $appointment->getPatient();
        $doctor = $appointment->getDoctor();

        $email = (new TemplatedEmail())
            ->from(new Address('CabinetDR.Labyedh.Slimen@gmail.com', 'ChoufliDoc'))
            ->to($patient->getEmail())
            ->subject('Appointment Rejected - ChoufliDoc')
            ->htmlTemplate('emails/appointment_rejected.html.twig')
            ->context([
                'patient' => $patient,
                'doctor' => $doctor,
                'appointment' => $appointment,
                'appointmentDate' => $appointment->getDateAppointment(),
            ]);

        $this->mailer->send($email);
    }

    public function sendAppointmentCreatedNotification(Appointment $appointment): void
    {
        $patient = $appointment->getPatient();
        $doctor = $appointment->getDoctor();

        $email = (new TemplatedEmail())
            ->from(new Address('CabinetDR.Labyedh.Slimen@gmail.com', 'ChoufliDoc'))
            ->to($patient->getEmail())
            ->subject('Appointment Request Received - ChoufliDoc')
            ->htmlTemplate('emails/appointment_created.html.twig')
            ->context([
                'patient' => $patient,
                'doctor' => $doctor,
                'appointment' => $appointment,
                'appointmentDate' => $appointment->getDateAppointment(),
            ]);

        $this->mailer->send($email);
    }
}
