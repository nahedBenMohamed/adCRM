<?php

namespace App\Controller\Admin;

use App\Entity\Formation;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;

use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;

use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;



class FormationCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Formation::class;
    }
    public function configureActions(Actions $actions): Actions
    {
        return $actions
            // ...
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
            ->add(Crud::PAGE_EDIT, Action::SAVE_AND_ADD_ANOTHER)
        ;
    }
    
    
    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id'),
            yield TextField::new('nomFormation'),
            yield TextField::new('domaineFormation'),
            yield TextField::new('dureeFormation'),
            yield DateTimeField::new('dateDebutFormation')
            ->setFormat('dd-MM-yyyy HH:mm'),
            yield DateTimeField::new('dateFinFormation')->setFormat('dd-MM-yyyy HH:mm'),
            yield ChoiceField::new('modaliteFormation')
            ->setChoices([
                'Mixte' => 'Mixte',
                'Présentiel' => 'Présentiel',
                'Ligne' => 'Sur place',
            ])
            ->renderAsBadges([
                'Mixte' => 'primary',
                'Présentiel' => 'success',
                'Ligne' => 'warning',
            ]),
            yield TextField::new('lienFormation'),
            yield TextField::new('adresseFormation'),
            yield AssociationField::new('formateur'),
            yield AssociationField::new('stagiaire')
        ];
    }

    
}
