<?php

namespace ArcaSolutions\ModStoresBundle\Plugins\CommunityForum\Twig\Extension;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Twig_Environment;
use Twig_Extension;
use Twig_SimpleFunction;

class ForumExtension extends Twig_Extension
{
    /**
     * ContainerInterface
     *
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * @return array
     */
    public function getFunctions()
    {
        return [
            new Twig_SimpleFunction('forumCategories', [$this, 'forumCategories'], [
                'needs_environment' => true,
                'is_safe'           => ['html'],
            ]),
        ];
    }

    public function forumCategories(Twig_Environment $twig_Environment)
    {
        return $this->container->get('twig')->render('CommunityForumBundle::categories.html.twig', []);
    }

    public function getName()
    {
        return 'forum';
    }
}
