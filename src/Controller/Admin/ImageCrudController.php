<?php

namespace App\Controller\Admin;

use App\Entity\Image;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class ImageCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Image::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            ImageField::new('filename')
                ->setLabel('Image')
                ->setUploadDir('public/uploads/media')
                ->setBasePath('uploads/media')
                ->setUploadedFileNamePattern('[timestamp]-[slug].[extension]')
                ->setFormTypeOption('attr', [
                    'onchange' => "const w=this.closest('.form-widget'); const img=w && w.querySelector('.image-preview'); if(img && this.files[0]){img.src=window.URL.createObjectURL(this.files[0]); img.style.display='block';}"
                ])
                ->setHelp('<img class="image-preview" style="display:none;margin-top:10px;max-width:200px;border-radius:8px;box-shadow:0 2px 6px rgba(0,0,0,0.2);" alt="Aperçu" />'),
        ];
    }
}
