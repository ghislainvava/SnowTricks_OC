<?php

namespace App\Controller\Admin;

use App\Entity\Figure;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

class FigureCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Figure::class;
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
