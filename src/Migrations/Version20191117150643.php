<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20191117150643 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE user ADD job_title VARCHAR(50) DEFAULT NULL, ADD organization_city VARCHAR(50) DEFAULT NULL, ADD organization_country VARCHAR(50) DEFAULT NULL, DROP city, DROP country, DROP postal_code, CHANGE phone phone VARCHAR(20) DEFAULT NULL, CHANGE address organization_address VARCHAR(255) DEFAULT NULL');
        $this->addSql('DROP INDEX vote_unique ON vote');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE user ADD city VARCHAR(50) DEFAULT NULL COLLATE utf8mb4_unicode_ci, ADD country VARCHAR(50) DEFAULT NULL COLLATE utf8mb4_unicode_ci, ADD postal_code VARCHAR(5) DEFAULT NULL COLLATE utf8mb4_unicode_ci, DROP job_title, DROP organization_city, DROP organization_country, CHANGE phone phone VARCHAR(20) NOT NULL COLLATE utf8mb4_unicode_ci, CHANGE organization_address address VARCHAR(255) DEFAULT NULL COLLATE utf8mb4_unicode_ci');
        $this->addSql('CREATE UNIQUE INDEX vote_unique ON vote (voter_id, problematic_id, comment_id)');
    }
}
