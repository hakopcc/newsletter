<?php


namespace ArcaSolutions\ModStoresBundle\Plugins\CommunityForum\Twig\Extension;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Twig_Environment;
use Twig_Error_Loader;
use Twig_Error_Runtime;
use Twig_Error_Syntax;
use Twig_Extension;
use Twig_SimpleFunction;

class BlocksExtension extends Twig_Extension
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @param ContainerInterface $containerInterface
     */
    public function __construct(ContainerInterface $containerInterface)
    {
        $this->container = $containerInterface;
    }

    /**
     * @return array
     */
    public function getFunctions()
    {
        return [
            new \Twig_SimpleFunction('recentQuestion', [$this, 'recentQuestion'], [
                'needs_environment' => true,
                'is_safe'           => ['html'],
            ]),
            new Twig_SimpleFunction('getRecentQuestionsData', [$this, 'getRecentQuestionsData']),
        ];
    }

    /**
     * @param Twig_Environment $twig_Environment
     * @param int $quantity
     * @return string
     * @throws Twig_Error_Loader
     * @throws Twig_Error_Runtime
     * @throws Twig_Error_Syntax
     */
    public function recentQuestion(Twig_Environment $twig_Environment, $quantity = 4) {
        if (!$this->container->get('modules')->isModuleAvailable('forum')||!$this->container->get('modules')->isModuleAvailable('question')) {
            return '';
        }

        $items = $this->container->get('search.block')->getRecent('question', $quantity);

        if (!$items) {
            return '';
        }

        return $twig_Environment->render('CommunityForumBundle::/blocks/cards/recent-big.html.twig', [
            'items'      => $items
        ]);
    }

    /**
     * @return array
     */
    public function getRecentQuestionsData()
    {
        // Featured Categories
        $categoriesFeatured = $this->container->get('search.repository.category')->findCategoriesWithItens("question", true);

        // Popular Posts
        $popularQuestions = $this->container->get('search.block')->getPopular("question", 5, true);

        return [
            'categoriesFeatured' => $categoriesFeatured,
            'popularQuestions'       => $popularQuestions
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'blocks_forum';
    }
}
