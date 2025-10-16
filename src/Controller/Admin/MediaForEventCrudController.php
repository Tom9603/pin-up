<?php

namespace App\Controller\Admin;

use App\Entity\Media;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;

class MediaForEventCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Media::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            ImageField::new('fileName')
                ->setLabel('Image')
                ->setUploadDir('public/media/')
                ->setBasePath('media/')
                ->setRequired(true)
                ->setFormTypeOption('attr', [
                    'onchange' => "const img=this.closest('.form-widget').querySelector('.image-preview'); if(img && this.files[0]) img.src=window.URL.createObjectURL(this.files[0]);"
                ])
                ->setHelp('<img class="image-preview" style="margin-top:10px;max-width:150px;border-radius:8px;box-shadow:0 2px 5px rgba(0,0,0,0.3);" />'),
        ];
    }
}
