<?php

namespace App\Command;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsCommand(
    name: 'app:create-admin',
    description: 'Create your admin user',
)]
class CreateAdminUserCommand extends Command
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private UserPasswordHasherInterface $passwordHasher
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
{
    $io = new SymfonyStyle($input, $output);

    $email = $io->ask('Admin email');
    $firstName = $io->ask('First name');
    $lastName = $io->ask('Last name');
    $phoneNumber = $io->ask('Phone number');
    $birthDate = $io->ask('Birth date (YYYY-MM-DD)');
    $address = $io->ask('Address');
    $password = $io->askHidden('Admin password');

    if (!$email || !$password || !$firstName || !$lastName || !$phoneNumber) {
        $io->error('All fields except adress and birth date are required.');
        return Command::FAILURE;
    }

    $user = new User();
    $user->setEmail($email);
    $user->setFirstName($firstName);
    $user->setLastName($lastName);
    $user->setPhoneNumber($phoneNumber);
    if ($birthDate) {
        $user->setBirthDate(new \DateTime($birthDate));
    }
    if ($address) {
        $user->setAddress($address);
    }
    $user->setRoles(['ROLE_ADMIN']);
    $user->setPassword(
        $this->passwordHasher->hashPassword($user, $password)
    );

    $this->entityManager->persist($user);
    $this->entityManager->flush();

    $io->success('Admin user created successfully.');

    return Command::SUCCESS;
}
}