<?php

namespace ArcaSolutions\ModStoresBundle\Plugins\CommunityForum\Twig\Extension;

use ArcaSolutions\ModStoresBundle\Plugins\CommunityForum\Entity\Question;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Twig_Extension;
use Twig_SimpleFunction;

class SeoExtension extends Twig_Extension
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'seo.question';
    }

    /**
     * @return array
     */
    public function getFunctions()
    {
        return [
            new Twig_SimpleFunction(
                'generateQuestionSEO',
                [$this, 'generateQuestionSEO'],
                ['is_safe' => ['all']]
            ),
        ];
    }


    public function generateQuestionSEO(Question $item)
    {
        $translator = $this->container->get('translator');

        $title = $translator->trans(
            '%pageTitle% | %directoryTitle%',
            [
                '%pageTitle%'      => $item->getTitle(),
                '%directoryTitle%' => $this->container->get('multi_domain.information')->getTitle(),
            ]
        );

        $categoryNames = [];
        $category = $item->getCategory();
        if ($category) {
            $categoryNames[] = $category->getTitle();
        }

        $image = $this->container->get('utility')->getLogoImage();

        $url = $this->container->get('router')->generate(
            'forum_detail',
            [
                'friendlyUrl' => $item->getFriendlyUrl(),
                '_format'     => 'html',
            ],
            true
        );

        $section = $this->container->get('utility')->convertArrayToHumanReadableString($categoryNames);

        $schema = [
            '@context'       => 'http://schema.org',
            '@type'          => 'DiscussionForumPosting',
            'headline'       => $item->getTitle(),
            'datePublished'  => $item->getEntered()->format('c'),
            'articleSection' => $section,
        ];

        $description = str_replace(["\r", "\n", "\t", "\s{2,}"], '',
            htmlentities(strip_tags(str_replace('  ', ' ', str_replace('<', ' <', $item->getDescription())))));
        $abstract = rtrim(substr($description, 0, 250));

        $image and $schema['image'] = $this->container->get('request_stack')->getCurrentRequest()->getSchemeAndHttpHost().$image;
        $abstract and $schema['description'] = $abstract;

        $author = [];
        $item->getAccountId() and $author['name'] = $item->getAccount()->getFirstName().' '.$item->getAccount()->getLastName();

        if ($author) {
            $author['@type'] = 'Person';
            $schema['author'] = $author;
        }

        return $this->container->get('twig')->render(
            'CommunityForumBundle::seo.html.twig',
            [
                'title'       => $title,
                'description' => $abstract,
                'author'      => $this->container->get('settings')->getDomainSetting('header_author'),
                'schema'      => json_encode($schema),
                'og'          => [
                    'url'         => $url,
                    'type'        => 'article',
                    'title'       => $title,
                    'description' => $abstract,
                    'image'       => $image,
                    'article'     => [
                        'author'        => isset($author['name']) ? $author['name'] : '',
                        'modifiedTime'  => $item->getUpdated()->format('c'),
                        'publishedTime' => $item->getEntered()->format('c'),
                        'section'       => $section,
                    ],
                ],
            ]
        );
    }
}
