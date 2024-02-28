<?php

namespace App\Controller\Admin;

use App\Entity\DeliveryTime;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class DeliveryTimeCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return DeliveryTime::class;
    }

    
    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id'),
            DateField::new('date'),
            TextField::new('time'),
            TextField::new('time_end'),
            BooleanField::new('statu','disponible'),

        ];
    }
    
}
