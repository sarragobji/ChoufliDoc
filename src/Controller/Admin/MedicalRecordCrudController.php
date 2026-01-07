<?php

namespace App\Controller\Admin;

use App\Entity\MedicalRecord;
use Doctrine\ORM\Mapping\Id;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use Symfony\Bundle\SecurityBundle\Security;

class MedicalRecordCrudController extends AbstractCrudController
{
    public function __construct(private Security $security) {}

    public static function getEntityFqcn(): string
    {
        return MedicalRecord::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Medical Record')
            ->setEntityLabelInPlural('Medical Records')
            ->setDefaultSort(['createdAt' => 'DESC']);
    }

    public function configureFields(string $pageName): iterable
    {
        // Fields everyone (Dalanda + Slimen) can access
        $basicFields = [
            yield AssociationField::new('patient')->setRequired(true),
            //yield DateTimeField::new('createdAt')->setFormTypeOptions(['disabled' => true])->setHelp('Auto-filled with current date'),
            yield TextareaField::new('reason')->setLabel('Visit Reason'),
        ];

        // Doctor-only fields
        if ($this->security->isGranted('ROLE_DOCTOR')) {
            yield TextareaField::new('diagnosis');
            yield TextareaField::new('analysis');
            yield TextareaField::new('prescription');
        }

        // Read-only fields
        yield DateTimeField::new('createdAt')->hideOnForm();
        yield AssociationField::new('createdBy')->hideOnForm();
    }

    // Auto-fill creator and createdAt on persist
    public function persistEntity($entityManager, $entityInstance): void
    {
        if (!$entityInstance instanceof MedicalRecord) {
            return;
        }

        // Auto-fill creator
        $entityInstance->setCreatedBy($this->security->getUser());

        // createdAt already set in constructor, but we ensure it
        if ($entityInstance->getCreatedAt() === null) {
            $entityInstance->__construct();
        }

        parent::persistEntity($entityManager, $entityInstance);
    }
}
