<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260509230153 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE `order` ADD shipping_name VARCHAR(255) DEFAULT NULL, ADD shipping_line1 VARCHAR(255) DEFAULT NULL, ADD shipping_line2 VARCHAR(255) DEFAULT NULL, ADD shipping_postal_code VARCHAR(20) DEFAULT NULL, ADD shipping_city VARCHAR(100) DEFAULT NULL, ADD shipping_country VARCHAR(2) DEFAULT NULL, ADD shipping_phone VARCHAR(30) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE `order` DROP shipping_name, DROP shipping_line1, DROP shipping_line2, DROP shipping_postal_code, DROP shipping_city, DROP shipping_country, DROP shipping_phone');
    }
}
