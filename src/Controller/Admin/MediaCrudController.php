<?php

namespace App\Controller\Admin;

use App\Entity\Media;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use Symfony\Component\Validator\Constraints\File;

class MediaCrudController extends AbstractCrudController
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
                ->setUploadedFileNamePattern('[timestamp]-[slug].[extension]')
                ->setRequired(false)
                ->setFormTypeOption('constraints', [
                    new File(
                        mimeTypes: ['image/jpeg', 'image/png', 'image/webp', 'image/gif'],
                        mimeTypesMessage: 'Seuls les formats JPEG, PNG, WebP et GIF sont acceptés.',
                        maxSize: '5M'
                    )
                ])
                ->setFormTypeOption('attr', [
                    'onchange' => "const w=this.closest('.form-widget'); const img=w && w.querySelector('.image-preview'); if(img && this.files[0]){img.src=window.URL.createObjectURL(this.files[0]); img.style.display='block';}"
                ])
                ->setHelp('<img class="image-preview" style="display:none;margin-top:10px;max-width:150px;border-radius:8px;box-shadow:0 2px 6px rgba(0,0,0,0.2);" alt="Aperçu" />')
                ->setTemplatePath('admin/fields/media_preview.html.twig'),
        ];
    }
}
