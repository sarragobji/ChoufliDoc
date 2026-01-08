<?php

namespace App\Form;

use App\Entity\Appointment;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AppointmentType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    { 
        $role = $options['role'];

        $builder->add('dateAppointment', DateType::class, [
            'widget' => 'single_text',
        ]);

        // ADMIN: can choose patient + doctor
        if ($role === 'admin') {
            $builder
                ->add('patient', EntityType::class, [
                    'class' => User::class,
                    'choice_label' => 'email',
                ])
                ->add('doctor', EntityType::class, [
                    'class' => User::class,
                    'choice_label' => 'email',
                ]);
        }

        // PATIENT: only chooses doctor
        if ($role === 'patient') {
            $builder->add('doctor', EntityType::class, [
                'class' => User::class,
                'choice_label' => 'email',
            ]);
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Appointment::class,
            'role' => null,
        ]);
    }
}