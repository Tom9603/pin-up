<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260509000000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add unique constraint on (user_id, event_id) in reservation table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE UNIQUE INDEX unique_user_event ON reservation (user_id, event_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP INDEX unique_user_event ON reservation');
    }
}
