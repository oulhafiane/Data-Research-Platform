<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20191117164725 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE searcher_category (searcher_id INT NOT NULL, category_id INT NOT NULL, INDEX IDX_CDC0568EC9F91E67 (searcher_id), INDEX IDX_CDC0568E12469DE2 (category_id), PRIMARY KEY(searcher_id, category_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE searcher_category ADD CONSTRAINT FK_CDC0568EC9F91E67 FOREIGN KEY (searcher_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE searcher_category ADD CONSTRAINT FK_CDC0568E12469DE2 FOREIGN KEY (category_id) REFERENCES category (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE searcher_category');
    }
}
