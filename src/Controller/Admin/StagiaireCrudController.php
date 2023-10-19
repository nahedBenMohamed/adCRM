<?php

namespace App\Controller\Admin;

use App\Entity\Stagiaire;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\ArrayField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class StagiaireCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Stagiaire::class;
    }

    
    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id'),
                yield TextField::new('nom'),
                yield TextField::new('prenom'),
                yield TextField::new('email'),
                yield TextField::new('password'),
                yield TextField::new('telephone'),
                yield TextField::new('civilite'),
                yield TextField::new('fonction'),
                yield TextField::new('entreprise'),
           
              
            ];
        }
        
    }
