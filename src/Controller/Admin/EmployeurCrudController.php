<?php

namespace App\Controller\Admin;

use App\Entity\Employeur;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

class EmployeurCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Employeur::class;
    }

    /*
    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id'),
            TextField::new('title'),
            TextEditorField::new('description'),
        ];
    }
    */
}
