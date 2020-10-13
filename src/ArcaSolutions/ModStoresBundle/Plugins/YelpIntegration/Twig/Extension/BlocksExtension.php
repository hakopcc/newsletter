<?php


namespace ArcaSolutions\ModStoresBundle\Plugins\YelpIntegration\Twig\Extension;

use ArcaSolutions\ListingBundle\Twig\Extension;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Twig_SimpleFunction;

class BlocksExtension extends Extension\BlocksExtension
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
        parent::__construct($containerInterface);
    }

    public function getFunctions()
    {
        $functionList = parent::getFunctions();
        /**
         * @var $function Twig_SimpleFunction
         */
        foreach ($functionList as &$function){
            if($function instanceof Twig_SimpleFunction){
                $functionName = $function->getName();
                if(!empty($functionName)){
                    if($functionName === 'statusTimeText'){
                        $function = new Twig_SimpleFunction('statusTimeText', [$this, 'statusTimeTextYelp'], []);
                    }
                }
            }
        }
        return $functionList;
    }
    
    public function statusTimeTextYelp($hoursWork = [])
    {
        $text = $this->container->get('translator')->trans('Closed Now');
        $class = 'closed-now';
        $displayDate = null;
        $runDefault = true;
        $modstoreStorageService = $this->container->get('modstore.storage.service');
        if(!empty($modstoreStorageService)) {
            $businessFromYelp = $modstoreStorageService->retrieveAndDestroy('businessFromYelp');
            if ($businessFromYelp) {
                $runDefault = false;
                $yelpBusinessIsOpenNow = $modstoreStorageService->retrieveAndDestroy('yelpBusinessIsOpenNow');
                if ($yelpBusinessIsOpenNow === true) {
                    $class = 'open-now';
                    $text = $this->container->get('translator')->trans('Open Now');
                }
                unset($yelpBusinessIsOpenNow);
            }
            unset($businessFromYelp);
        }
        unset($modstoreStorageService);
        if($runDefault) {
            unset($runDefault);
            return parent::statusTimeText($hoursWork);
        }else{
            unset($runDefault);
            return ['text' => $text, 'class' => $class];
        }
    }
}
