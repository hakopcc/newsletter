<?php


namespace Application\Migrations;

use ArcaSolutions\WysiwygBundle\Entity\Page;
use ArcaSolutions\WysiwygBundle\Entity\PageType;
use ArcaSolutions\WysiwygBundle\Entity\PageWidget;
use ArcaSolutions\WysiwygBundle\Entity\Widget;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\DBAL\DBALException;
use Doctrine\DBAL\Migrations\AbortMigrationException;
use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Migrations\Configuration\YamlConfiguration;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Schema\SchemaException;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\PersistentCollection;
use Doctrine\ORM\Query\Expr\GroupBy;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Security\Acl\Exception\Exception;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class VersionCommunityForum8 extends AbstractMigration implements ContainerAwareInterface
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @inheritDoc
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    /**
     * @inheritDoc
     */
    public function getContainer()
    {
        return $this->container;
    }

     /**
     * @param Schema $schema
     * @throws DBALException
     * @throws AbortMigrationException
     * @throws SchemaException
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE QuestionCategory DROP active_post, DROP full_friendly_url, DROP level, DROP legacy_id, CHANGE summary_description summary_description VARCHAR(255) DEFAULT NULL, CHANGE seo_description seo_description VARCHAR(255) DEFAULT NULL, CHANGE page_title page_title VARCHAR(255) DEFAULT NULL, CHANGE keywords keywords VARCHAR(255) DEFAULT NULL, CHANGE seo_keywords seo_keywords VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE QuestionCategory ADD CONSTRAINT FK_9291348A12469DE2 FOREIGN KEY (category_id) REFERENCES QuestionCategory (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE QuestionCategory ADD CONSTRAINT FK_9291348A3DA5256D FOREIGN KEY (image_id) REFERENCES Image (id) ON DELETE SET NULL');
        $this->addSql('CREATE INDEX IDX_9291348A12469DE2 ON QuestionCategory (category_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_9291348A3DA5256D ON QuestionCategory (image_id)');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE QuestionCategory DROP FOREIGN KEY FK_9291348A12469DE2');
        $this->addSql('ALTER TABLE QuestionCategory DROP FOREIGN KEY FK_9291348A3DA5256D');
        $this->addSql('DROP INDEX IDX_9291348A12469DE2 ON QuestionCategory');
        $this->addSql('DROP INDEX UNIQ_9291348A3DA5256D ON QuestionCategory');
        $this->addSql('ALTER TABLE QuestionCategory ADD active_post INT NOT NULL, ADD full_friendly_url LONGTEXT DEFAULT NULL COLLATE utf8_unicode_ci, ADD level INT NOT NULL, ADD legacy_id VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci, CHANGE summary_description summary_description VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci, CHANGE seo_description seo_description VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci, CHANGE page_title page_title VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci, CHANGE keywords keywords VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci, CHANGE seo_keywords seo_keywords VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci');    }
}
