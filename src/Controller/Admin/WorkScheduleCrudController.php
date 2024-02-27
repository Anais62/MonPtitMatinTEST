<?php

namespace App\Controller\Admin;

use App\Entity\WorkSchedule;
use Doctrine\DBAL\Types\BooleanType;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TimeField;

class WorkScheduleCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return WorkSchedule::class;
    }

    
    public function configureFields(string $pageName): iterable
    {
        return [
            TextField::new('day', 'Jour'),
            TimeField::new('heure_debut'),
            TimeField::new('heure_fin'),
            BooleanField::new('work', 'Travaille')
        ];
    }
    
}
