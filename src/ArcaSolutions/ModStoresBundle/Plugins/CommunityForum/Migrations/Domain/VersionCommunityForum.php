<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class VersionCommunityForum extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql',
            'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE IF NOT EXISTS QuestionUpvotes (id INT AUTO_INCREMENT NOT NULL, question INT DEFAULT NULL, user_id INT DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE IF NOT EXISTS Answer (id INT AUTO_INCREMENT NOT NULL, question_id INT DEFAULT NULL, user_id INT DEFAULT NULL, description LONGTEXT NOT NULL, entered DATETIME DEFAULT NULL, updated DATETIME DEFAULT NULL, upvotes INT NOT NULL, `status` VARCHAR(2) NOT NULL, INDEX IDX_DD714F131E27F6BF (question_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE IF NOT EXISTS Question (id INT AUTO_INCREMENT NOT NULL, category_id INT DEFAULT NULL, user_id INT DEFAULT NULL, title VARCHAR(255) DEFAULT NULL, friendly_url VARCHAR(255) DEFAULT NULL, description LONGTEXT DEFAULT NULL, keywords LONGTEXT DEFAULT NULL, entered DATETIME DEFAULT NULL, updated DATETIME DEFAULT NULL, upvotes INT DEFAULT NULL, `status` VARCHAR(2) NOT NULL, INDEX IDX_4F812B1812469DE2 (category_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE IF NOT EXISTS QuestionCategory (id INT AUTO_INCREMENT NOT NULL, root_id INT DEFAULT NULL, `left` INT DEFAULT NULL, `right` INT DEFAULT NULL, title VARCHAR(255) NOT NULL, category_id INT DEFAULT NULL, thumb_id INT DEFAULT NULL, image_id INT DEFAULT NULL, featured VARCHAR(1) NOT NULL, summary_description VARCHAR(255) NOT NULL, seo_description VARCHAR(255) NOT NULL, page_title VARCHAR(255) NOT NULL, friendly_url VARCHAR(255) NOT NULL, keywords VARCHAR(255) NOT NULL, seo_keywords VARCHAR(255) NOT NULL, content LONGTEXT DEFAULT NULL, active_post INT NOT NULL, full_friendly_url LONGTEXT DEFAULT NULL, `level` INT NOT NULL, legacy_id VARCHAR(255) NOT NULL, enabled VARCHAR(1) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE IF NOT EXISTS AnswerUpvotes (id INT AUTO_INCREMENT NOT NULL, answer INT DEFAULT NULL, user_id INT DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql',
            'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE QuestionUpvotes');
        $this->addSql('DROP TABLE Answer');
        $this->addSql('DROP TABLE Question');
        $this->addSql('DROP TABLE QuestionCategory');
        $this->addSql('DROP TABLE AnswerUpvotes');
    }
}
