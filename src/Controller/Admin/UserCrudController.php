<?php

namespace App\Controller\Admin;

use App\Entity\User;
use Dom\Text;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ArrayField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Dto\SearchDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FieldCollection;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FilterCollection;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\EntityManagerInterface;

class UserCrudController extends AbstractCrudController
{
    public function __construct(private UserPasswordHasherInterface $passwordHasher) {}
    public static function getEntityFqcn(): string
    {
        return User::class;
    }
        public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('User')
            ->setEntityLabelInPlural('Users')
            ->setDefaultSort(['lastName' => 'ASC']);
    }
    public function configureFields(string $pageName): iterable
    {
        return [    
            TextField::new('firstName', 'First Name'),
            TextField::new('lastName', 'Last Name'),
            EmailField::new('email'),
            TextField::new('phoneNumber', 'Phone Number')->hideOnIndex(),
            TextField::new('plainPassword', 'Password')
                ->setFormTypeOptions([
                    'required' => $pageName === Crud::PAGE_NEW, // Required only on creation
                ])
                ->onlyOnForms(), 
            ChoiceField::new('roles')
                ->setChoices([
                    'User' => 'ROLE_USER',
                    'Admin' => 'ROLE_ADMIN',
                    'Doctor' => 'ROLE_DOCTOR',
                ])
                ->allowMultipleChoices()
                ->renderAsBadges()
                ->hideOnIndex(),
            BooleanField::new('isVerified', 'Verified')->hideOnForm(),
        ];
    }
    public function createIndexQueryBuilder(
        SearchDto $searchDto,
        EntityDto $entityDto,
        FieldCollection $fields,
        FilterCollection $filters
): QueryBuilder {
    $qb = parent::createIndexQueryBuilder($searchDto, $entityDto, $fields, $filters);

    $qb->andWhere('(entity.roles LIKE :role OR entity.roles = :empty)')
       ->setParameter('role', '%"ROLE_USER"%')
       ->setParameter('empty', '[]')
       ->andWhere('entity.roles NOT LIKE :admin')
       ->setParameter('admin', '%"ROLE_ADMIN"%');

    return $qb;
}
public function persistEntity(EntityManagerInterface $entityManager, $entityInstance): void
{
    if (!$entityInstance instanceof User) {
        return;
    }

    if ($entityInstance->getPlainPassword()) {
        $entityInstance->setPassword(
            $this->passwordHasher->hashPassword(
                $entityInstance,
                $entityInstance->getPlainPassword()
            )
        );
    }

    parent::persistEntity($entityManager, $entityInstance);
    }
}
