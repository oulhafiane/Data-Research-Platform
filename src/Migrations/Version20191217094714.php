<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20191217094714 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE searcher_applications (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, accepted_by_id INT DEFAULT NULL, status TINYINT(1) DEFAULT NULL, creation_date DATETIME NOT NULL, UNIQUE INDEX UNIQ_D4F82819A76ED395 (user_id), INDEX IDX_D4F8281920F699D9 (accepted_by_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE data_set (id INT AUTO_INCREMENT NOT NULL, uuid CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\', name VARCHAR(255) NOT NULL, description LONGTEXT DEFAULT NULL, privacy SMALLINT NOT NULL, UNIQUE INDEX UNIQ_A298C469D17F50A6 (uuid), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE msg_contact_us (id INT AUTO_INCREMENT NOT NULL, full_name VARCHAR(255) NOT NULL, email VARCHAR(180) NOT NULL, message LONGTEXT NOT NULL, date DATETIME NOT NULL, seen TINYINT(1) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user (id INT AUTO_INCREMENT NOT NULL, photo_id INT DEFAULT NULL, uuid CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\', email VARCHAR(180) NOT NULL, roles JSON NOT NULL, password VARCHAR(255) NOT NULL, first_name VARCHAR(50) NOT NULL, last_name VARCHAR(50) NOT NULL, organization VARCHAR(50) DEFAULT NULL, job_title VARCHAR(50) DEFAULT NULL, organization_address VARCHAR(255) DEFAULT NULL, organization_city VARCHAR(50) DEFAULT NULL, organization_country VARCHAR(50) DEFAULT NULL, bio LONGTEXT DEFAULT NULL, phone VARCHAR(20) DEFAULT NULL, subscription_date DATETIME NOT NULL, is_active TINYINT(1) NOT NULL, type VARCHAR(255) NOT NULL, UNIQUE INDEX UNIQ_8D93D649D17F50A6 (uuid), UNIQUE INDEX UNIQ_8D93D649E7927C74 (email), INDEX IDX_8D93D6497E9E4C8C (photo_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user_category (user_id INT NOT NULL, category_id INT NOT NULL, INDEX IDX_E6C1FDC1A76ED395 (user_id), INDEX IDX_E6C1FDC112469DE2 (category_id), PRIMARY KEY(user_id, category_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user_searcher (user_id INT NOT NULL, searcher_id INT NOT NULL, INDEX IDX_F792EC8FA76ED395 (user_id), INDEX IDX_F792EC8FC9F91E67 (searcher_id), PRIMARY KEY(user_id, searcher_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE vote (id INT AUTO_INCREMENT NOT NULL, voter_id INT NOT NULL, problematic_id INT DEFAULT NULL, comment_id INT DEFAULT NULL, good TINYINT(1) NOT NULL, creation_date DATETIME NOT NULL, INDEX IDX_5A108564EBB4B8AD (voter_id), INDEX IDX_5A108564BC0013C7 (problematic_id), INDEX IDX_5A108564F8697D13 (comment_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE comment (id INT AUTO_INCREMENT NOT NULL, owner_id INT NOT NULL, problematic_id INT NOT NULL, text LONGTEXT NOT NULL, creation_date DATETIME NOT NULL, INDEX IDX_9474526C7E3C61F9 (owner_id), INDEX IDX_9474526CBC0013C7 (problematic_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE problematic (id INT AUTO_INCREMENT NOT NULL, owner_id INT NOT NULL, category_id INT NOT NULL, title VARCHAR(100) NOT NULL, description LONGTEXT NOT NULL, solution LONGTEXT DEFAULT NULL, advantage LONGTEXT DEFAULT NULL, possible_application LONGTEXT DEFAULT NULL, link VARCHAR(255) DEFAULT NULL, type VARCHAR(100) NOT NULL, creation_date DATETIME NOT NULL, keywords LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:array)\', INDEX IDX_B366E46B7E3C61F9 (owner_id), INDEX IDX_B366E46B12469DE2 (category_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE category (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(100) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE sub_category (id INT AUTO_INCREMENT NOT NULL, category_id INT NOT NULL, title VARCHAR(100) NOT NULL, INDEX IDX_BCE3F79812469DE2 (category_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE news (id INT AUTO_INCREMENT NOT NULL, creator_id INT NOT NULL, title VARCHAR(100) NOT NULL, description LONGTEXT NOT NULL, date DATETIME NOT NULL, creation_date DATETIME NOT NULL, is_event TINYINT(1) NOT NULL, INDEX IDX_1DD3995061220EA6 (creator_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE notification (id INT AUTO_INCREMENT NOT NULL, owner_id INT NOT NULL, type SMALLINT NOT NULL, date DATETIME NOT NULL, seen TINYINT(1) NOT NULL, message VARCHAR(255) NOT NULL, reference BIGINT DEFAULT NULL, INDEX IDX_BF5476CA7E3C61F9 (owner_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE photo (id INT AUTO_INCREMENT NOT NULL, problematic_id INT DEFAULT NULL, name VARCHAR(255) NOT NULL, size INT NOT NULL, link VARCHAR(255) NOT NULL, upload_at DATETIME DEFAULT NULL, INDEX IDX_14B78418BC0013C7 (problematic_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE refresh_tokens (id INT AUTO_INCREMENT NOT NULL, refresh_token VARCHAR(128) NOT NULL, username VARCHAR(255) NOT NULL, valid DATETIME NOT NULL, UNIQUE INDEX UNIQ_9BACE7E1C74F2195 (refresh_token), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE searcher_applications ADD CONSTRAINT FK_D4F82819A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE searcher_applications ADD CONSTRAINT FK_D4F8281920F699D9 FOREIGN KEY (accepted_by_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE user ADD CONSTRAINT FK_8D93D6497E9E4C8C FOREIGN KEY (photo_id) REFERENCES photo (id)');
        $this->addSql('ALTER TABLE user_category ADD CONSTRAINT FK_E6C1FDC1A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE user_category ADD CONSTRAINT FK_E6C1FDC112469DE2 FOREIGN KEY (category_id) REFERENCES category (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE user_searcher ADD CONSTRAINT FK_F792EC8FA76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE user_searcher ADD CONSTRAINT FK_F792EC8FC9F91E67 FOREIGN KEY (searcher_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE vote ADD CONSTRAINT FK_5A108564EBB4B8AD FOREIGN KEY (voter_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE vote ADD CONSTRAINT FK_5A108564BC0013C7 FOREIGN KEY (problematic_id) REFERENCES problematic (id)');
        $this->addSql('ALTER TABLE vote ADD CONSTRAINT FK_5A108564F8697D13 FOREIGN KEY (comment_id) REFERENCES comment (id)');
        $this->addSql('ALTER TABLE comment ADD CONSTRAINT FK_9474526C7E3C61F9 FOREIGN KEY (owner_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE comment ADD CONSTRAINT FK_9474526CBC0013C7 FOREIGN KEY (problematic_id) REFERENCES problematic (id)');
        $this->addSql('ALTER TABLE problematic ADD CONSTRAINT FK_B366E46B7E3C61F9 FOREIGN KEY (owner_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE problematic ADD CONSTRAINT FK_B366E46B12469DE2 FOREIGN KEY (category_id) REFERENCES sub_category (id)');
        $this->addSql('ALTER TABLE sub_category ADD CONSTRAINT FK_BCE3F79812469DE2 FOREIGN KEY (category_id) REFERENCES category (id)');
        $this->addSql('ALTER TABLE news ADD CONSTRAINT FK_1DD3995061220EA6 FOREIGN KEY (creator_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE notification ADD CONSTRAINT FK_BF5476CA7E3C61F9 FOREIGN KEY (owner_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE photo ADD CONSTRAINT FK_14B78418BC0013C7 FOREIGN KEY (problematic_id) REFERENCES problematic (id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE searcher_applications DROP FOREIGN KEY FK_D4F82819A76ED395');
        $this->addSql('ALTER TABLE searcher_applications DROP FOREIGN KEY FK_D4F8281920F699D9');
        $this->addSql('ALTER TABLE user_category DROP FOREIGN KEY FK_E6C1FDC1A76ED395');
        $this->addSql('ALTER TABLE user_searcher DROP FOREIGN KEY FK_F792EC8FA76ED395');
        $this->addSql('ALTER TABLE user_searcher DROP FOREIGN KEY FK_F792EC8FC9F91E67');
        $this->addSql('ALTER TABLE vote DROP FOREIGN KEY FK_5A108564EBB4B8AD');
        $this->addSql('ALTER TABLE comment DROP FOREIGN KEY FK_9474526C7E3C61F9');
        $this->addSql('ALTER TABLE problematic DROP FOREIGN KEY FK_B366E46B7E3C61F9');
        $this->addSql('ALTER TABLE news DROP FOREIGN KEY FK_1DD3995061220EA6');
        $this->addSql('ALTER TABLE notification DROP FOREIGN KEY FK_BF5476CA7E3C61F9');
        $this->addSql('ALTER TABLE vote DROP FOREIGN KEY FK_5A108564F8697D13');
        $this->addSql('ALTER TABLE vote DROP FOREIGN KEY FK_5A108564BC0013C7');
        $this->addSql('ALTER TABLE comment DROP FOREIGN KEY FK_9474526CBC0013C7');
        $this->addSql('ALTER TABLE photo DROP FOREIGN KEY FK_14B78418BC0013C7');
        $this->addSql('ALTER TABLE user_category DROP FOREIGN KEY FK_E6C1FDC112469DE2');
        $this->addSql('ALTER TABLE sub_category DROP FOREIGN KEY FK_BCE3F79812469DE2');
        $this->addSql('ALTER TABLE problematic DROP FOREIGN KEY FK_B366E46B12469DE2');
        $this->addSql('ALTER TABLE user DROP FOREIGN KEY FK_8D93D6497E9E4C8C');
        $this->addSql('DROP TABLE searcher_applications');
        $this->addSql('DROP TABLE data_set');
        $this->addSql('DROP TABLE msg_contact_us');
        $this->addSql('DROP TABLE user');
        $this->addSql('DROP TABLE user_category');
        $this->addSql('DROP TABLE user_searcher');
        $this->addSql('DROP TABLE vote');
        $this->addSql('DROP TABLE comment');
        $this->addSql('DROP TABLE problematic');
        $this->addSql('DROP TABLE category');
        $this->addSql('DROP TABLE sub_category');
        $this->addSql('DROP TABLE news');
        $this->addSql('DROP TABLE notification');
        $this->addSql('DROP TABLE photo');
        $this->addSql('DROP TABLE refresh_tokens');
    }
}
