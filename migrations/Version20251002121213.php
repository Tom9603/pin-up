<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251002121213 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE event DROP FOREIGN KEY FK_3BAE0AA7B83297E7');
        $this->addSql('DROP INDEX IDX_3BAE0AA7B83297E7 ON event');
        $this->addSql('ALTER TABLE event DROP reservation_id');
        $this->addSql('ALTER TABLE media CHANGE event_id event_id INT NOT NULL');
        $this->addSql('ALTER TABLE reservation ADD event_id INT NOT NULL, CHANGE user_id user_id INT NOT NULL');
        $this->addSql('ALTER TABLE reservation ADD CONSTRAINT FK_42C8495571F7E88B FOREIGN KEY (event_id) REFERENCES event (id)');
        $this->addSql('CREATE INDEX IDX_42C8495571F7E88B ON reservation (event_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE event ADD reservation_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE event ADD CONSTRAINT FK_3BAE0AA7B83297E7 FOREIGN KEY (reservation_id) REFERENCES reservation (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('CREATE INDEX IDX_3BAE0AA7B83297E7 ON event (reservation_id)');
        $this->addSql('ALTER TABLE media CHANGE event_id event_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE reservation DROP FOREIGN KEY FK_42C8495571F7E88B');
        $this->addSql('DROP INDEX IDX_42C8495571F7E88B ON reservation');
        $this->addSql('ALTER TABLE reservation DROP event_id, CHANGE user_id user_id INT DEFAULT NULL');
    }
}
