<?php
namespace ArcaSolutions\ModStoresBundle\Plugins\CommunityForum;

use ArcaSolutions\CoreBundle\Interfaces\ItemDetailInterface;
use ArcaSolutions\ModStoresBundle\Plugins\CommunityForum\Entity\Question;
use Exception;
use Symfony\Component\DependencyInjection\ContainerInterface;

class QuestionItemDetail implements ItemDetailInterface
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @var Question
     */
    private $item = null;

    /**
     * Doesn't have it
     */
    private $level = null;

    /**
     * @param ContainerInterface $containerInterface
     * @param Question $question
     */
    public function __construct(ContainerInterface $containerInterface, Question $question)
    {
        $this->container = $containerInterface;
        $this->item = $question;

        /* sets item's level */
        $this->setLevel();
    }

    /**
     * Sets item's level
     */
    private function setLevel()
    {
    }

    /** {@inheritdoc} */
    public function getModuleName()
    {
        return 'forum';
    }

    /** {@inheritdoc} */
    public function getLevel()
    {
        return $this->level;
    }

    /** {@inheritdoc} */
    public function getItem()
    {
        /* checks if item was seated */
        if (is_null($this->item)) {
            throw new Exception('You must set the item');
        }

        return $this->item;
    }

    /**
     * Returns container object to give access on services
     *
     * @return ContainerInterface
     */
    public function getContainer()
    {
        return $this->container;
    }
}
