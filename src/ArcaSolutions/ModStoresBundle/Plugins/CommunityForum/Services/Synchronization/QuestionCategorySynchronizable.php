<?php

namespace ArcaSolutions\ModStoresBundle\Plugins\CommunityForum\Services\Synchronization;

use ArcaSolutions\CoreBundle\Services\Synchronization\BaseCategorySynchronizable;
use Symfony\Component\DependencyInjection\ContainerInterface;

class QuestionCategorySynchronizable extends BaseCategorySynchronizable
{
    public function __construct(ContainerInterface $container)
    {
        parent::__construct($container, 'Q:%d', 'CommunityForumBundle:QuestionCategory', 'question');
    }
}
