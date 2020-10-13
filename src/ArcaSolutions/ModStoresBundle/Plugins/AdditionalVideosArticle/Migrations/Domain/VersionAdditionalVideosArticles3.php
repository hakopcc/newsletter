<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class VersionAdditionalVideosArticles3 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql',
            'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE Article_Videos CHANGE id id INT AUTO_INCREMENT NOT NULL, CHANGE article_id article_id INT DEFAULT NULL');
        $this->addSql('RENAME TABLE ArticleLevel_Videos TO ArticleLevel_FieldVideos');
        $this->addSql('ALTER TABLE ArticleLevel_FieldVideos DROP COLUMN module;');
        $this->addSql('ALTER TABLE ArticleLevel_FieldVideos ADD id INT NOT NULL AUTO_INCREMENT FIRST, ADD PRIMARY KEY (id);');
        $this->addSql('ALTER TABLE ArticleLevel_FieldVideos CHANGE level_id `level`INT(11) NOT NULL, CHANGE videos field INT(11) NOT NULL');
        if ($schema->hasTable('ArticleLevel_FieldVideos')) {
            if ($schema->getTable('ArticleLevel_FieldVideos')->hasIndex('level_id_module')) {
                $this->addSql('ALTER TABLE ArticleLevel_FieldVideos DROP INDEX level_id_module');
            }
        }
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql',
            'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE ArticleLevel_FieldVideos CHANGE `level` level_id INT(11) UNSIGNED DEFAULT NULL, CHANGE field videos TINYINT(1) DEFAULT \'0\' NOT NULL');
        $this->addSql('ALTER TABLE ArticleLevel_FieldVideos ADD module VARCHAR(50) NULL DEFAULT NULL');
        $this->addSql('ALTER TABLE ArticleLevel_FieldVideos ADD UNIQUE level_id_module (`level_id`, `module`)');
        $this->addSql('ALTER TABLE ArticleLevel_FieldVideos DROP id');
        $this->addSql('RENAME TABLE ArticleLevel_FieldVideos TO ArticleLevel_Videos');
        $this->addSql('ALTER TABLE Article_Videos CHANGE id id INT(1) UNSIGNED NOT NULL AUTO_INCREMENT, CHANGE article_id article_id INT(11) NOT NULL');
    }
}
