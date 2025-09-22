<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250922130656 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE event ADD start DATETIME NOT NULL, ADD end DATETIME DEFAULT NULL, DROP date_event');
        $this->addSql('ALTER TABLE media ADD event_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE media ADD CONSTRAINT FK_6A2CA10C71F7E88B FOREIGN KEY (event_id) REFERENCES event (id)');
        $this->addSql('CREATE INDEX IDX_6A2CA10C71F7E88B ON media (event_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE media DROP FOREIGN KEY FK_6A2CA10C71F7E88B');
        $this->addSql('DROP INDEX IDX_6A2CA10C71F7E88B ON media');
        $this->addSql('ALTER TABLE media DROP event_id');
        $this->addSql('ALTER TABLE event ADD date_event DATE NOT NULL, DROP start, DROP end');
    }
}
