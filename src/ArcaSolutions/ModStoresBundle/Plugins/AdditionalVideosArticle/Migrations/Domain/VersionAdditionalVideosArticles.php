<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class VersionAdditionalVideosArticles extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql',
            'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE IF NOT EXISTS ArticleLevel_Videos (level_id INT(1) UNSIGNED NULL DEFAULT NULL, module VARCHAR(50) NULL DEFAULT NULL, videos TINYINT(3) UNSIGNED NOT NULL DEFAULT 0, INDEX level_id_module (level_id, module)) ENGINE=InnoDB');
        $this->addSql('CREATE TABLE IF NOT EXISTS Article_Videos (id INT(1) UNSIGNED NOT NULL AUTO_INCREMENT, article_id INT(11) NOT NULL, video_snippet TEXT NULL, video_url TEXT NULL, video_description TEXT NULL, PRIMARY KEY (id), INDEX `article_id` (article_id)) COLLATE=utf8_general_ci ENGINE=InnoDB');
        $this->addSql('CREATE TABLE IF NOT EXISTS ArticleLevel_Field (id INT(11) NULL DEFAULT NULL, `level` INT(11) NULL DEFAULT NULL, field INT(11) NULL DEFAULT NULL ) COLLATE=utf8_general_ci ENGINE=InnoDB');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql',
            'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE ArticleLevel_Videos');
        $this->addSql('DROP TABLE Article_Videos');
        $this->addSql('DROP TABLE ArticleLevel_Field');
    }
}
