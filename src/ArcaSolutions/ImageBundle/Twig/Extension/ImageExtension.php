<?php

namespace ArcaSolutions\ImageBundle\Twig\Extension;

use ArcaSolutions\CoreBundle\Entity\Account;
use ArcaSolutions\CoreBundle\Entity\Profile;
use ArcaSolutions\ImageBundle\Entity\Image;
use ArcaSolutions\WebBundle\Entity\Accountprofilecontact;
use phpDocumentor\Reflection\DocBlock\Tags\Since;
use Symfony\Component\DependencyInjection\ContainerInterface;

class ImageExtension extends \Twig_Extension
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
     * @return array
     */
    public function getFunctions()
    {
        return [
            new \Twig_SimpleFunction(
                'imagePath',
                [$this, 'getPath'],
                ['is_safe' => ['all']]
            ),
            new \Twig_SimpleFunction(
                'imageProfile',
                [$this, 'getProfileImage'],
                ['is_safe' => ['all']]
            ),
            new \Twig_SimpleFunction(
                'imageProfileByAccountId',
                [$this, 'getProfileImageByAccountId'],
                ['is_safe' => ['all']]
            ),
            new \Twig_SimpleFunction(
                'backgroundImage',
                [$this, 'getBackgroundImage'],
                ['is_safe' => ['all']]
            ),
        ];
    }

    /**
     * Alias for create the image name
     *
     * @param Image $image
     * @param null $imageId
     * @return string
     */
    public function getPath($image = null, $imageId = null)
    {
        if($imageId) {
            $image = $this->container->get('doctrine')->getRepository('ImageBundle:Image')->find($imageId);
        }

        return $this->container->get('imagehandler')->getPath($image);
    }

    /**
     * Alias for create the image name
     *
     * @param $accountId
     * @return string
     */
    public function getProfileImageByAccountId($accountId)
    {
        $return = null;

        if ($accountId) {
            $repository = $this->container->get('doctrine')->getRepository('CoreBundle:Account', 'main');

            /* @var Account $account */
            if ($account = $repository->find($accountId)) {
                $return = $this->getProfileImage($account->getProfile());
            }
        }

        return $return;
    }

    /**
     * Alias for create the image name
     *
     * @param Profile $profile
     * @return string
     */
    public function getProfileImage($profile)
    {
        $repository = $this->container->get('doctrine')->getRepository('CoreBundle:Image', 'main');

        if ($profile->getImageId() && $image = $repository->find($profile->getImageId())) {
            $prefix = null;

            if($profile instanceof Accountprofilecontact) {
                $prefix = $profile->getAccountId();
            }

            if($profile instanceof Profile) {
                $prefix = $profile->getAccount()->getId();
            }

            $id = $image->getId();
            $type = strtolower($image->getType());

            return sprintf('%d_photo_%d.%s', $prefix, $id, $type);
        }

        return null;
    }

    /**
     * @param null $unsplash string
     * @param null $imageId
     * @param null $templateId
     * @param null $title
     * @param string $filter
     * @return mixed|string
     */
    public function getBackgroundImage($unsplash = null, $imageId = null, $templateId = null, $title = null, $filter = 'large')
    {
        $filters = [
            'desktop' => $filter,
            'laptop'  => $filter,
            'tablet'  => $filter,
            'mobile'  => $filter
        ];

        if (!empty($imageId) && !empty($templateId) && empty($unsplash)) {
            $imgObj = $this->container->get('doctrine')->getRepository('ImageBundle:Image')->find($imageId);

            if($imgObj !== null && empty($imgObj->getUnsplash())) {
                $imageUrl = $this->container->get('templating.helper.assets')
                    ->getUrl($this->container->get('imagehandler')->getPath($imgObj), 'domain_images');

                $imageData = $this->container->get('tag.picture.service')->getImageSource($imageUrl, $title, $filters);

                $imageData['itemId'] = $templateId;
                return $this->container->get('templating')->render('@Web/images/background.html.twig', $imageData);
            }
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'image_extension';
    }
}
