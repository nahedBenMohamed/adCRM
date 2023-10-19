<?php

namespace App\Controller\Admin;

use App\Entity\Gestionnaire;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

class GestionnaireCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Gestionnaire::class;
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
