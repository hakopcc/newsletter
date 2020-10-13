<?php

namespace ArcaSolutions\ModStoresBundle\Plugins\TailoredMapListing;

use ArcaSolutions\MultiDomainBundle\HttpFoundation\MultiDomainRequest;
use ListingCategory as LegacyListingCategory;
use ArcaSolutions\CoreBundle\Kernel\Kernel;
use ArcaSolutions\ModStoresBundle\Kernel\Hooks;
use ArcaSolutions\ModStoresBundle\Plugins\AbstractPluginBundle as Bundle;
use Exception;
use Image;
use ReflectionClass;
use ReflectionException;
use Symfony\Bridge\Monolog\Logger;
use Symfony\Component\Asset\Exception\InvalidArgumentException;

class TailoredMapListingBundle extends Bundle
{
    private $devEnvironment = false;

    /**
     * Boots the Bundle.
     * @throws Exception
     */
    public function boot()
    {
        $logger = $this->container->get('logger');
        $notLoggedCriticalException = null;
        try {
            $this->devEnvironment = Kernel::ENV_DEV === $this->container->getParameter('kernel.environment');
            if ($this->isSitemgr()) {
                /*
                 * Register sitemgr only bundle hooks
                 */
                Hooks::Register('generalsettings_after_save', function (&$params = null) {
                    $this->getGeneralSettingsAfterSave($params);
                });
                Hooks::Register('generalsettings_after_render_form', function (&$params = null) {
                    $this->getGeneralSettingsAfterRenderForm($params);
                });
                Hooks::Register('formcategory_after_render_category', function (&$params = null) {
                    $this->getFormCategoryAfterRenderCategory($params);
                });
            } else {
                /*
                 * Register front only bundle hooks
                 */
                Hooks::Register('distancesorter_before_return_subscribers', function (&$params = null) {
                    $this->getDistanceSorterBeforeReturnSubscribers($params);
                });
                Hooks::Register('wysiwygextension_before_validate_widget', function (&$params = null) {
                    $this->getWysiwygExtensionBeforeValidateWidget($params);
                });
                Hooks::Register('wysiwygextension_before_render_widget', function (&$params = null) {
                    $this->getWysiwygExtensionBeforeRenderWidget($params);
                });
                Hooks::Register('base_after_add_js', function (&$params = null) {
                    $this->getBaseAfterAddJs($params);
                });
            }
            Hooks::Register('widget_construct', function (&$params = null) {
                $this->getWidgetConstruct($params);
            });
            Hooks::Register('categorycode_after_remove_image', function (&$params = null) {
                $this->getCategoryCodeAfterRemoveImage($params);
            });
            Hooks::Register('categorycode_after_setup_form', function (&$params = null) {
                $this->getCategoryCodeAfterSetupForm($params);
            });
            Hooks::Register('legacy_coverimagecode_after_ajaxchecktype', function (&$params = null) {
                $this->getLegacyCoverImageCodeAfterAjaxCheckType($params);
            });
            Hooks::Register('listingcategoryservice_before_return_retrieveserializedcategory', function (&$params = null) {
                $this->getListingCategoryServiceBeforeReturnGetSerializedCategory($params);
            });
            Hooks::Register('classbasecategory_after_makerow', function (&$params = null) {
                $this->getClassBaseCategoryAfterMakeRow($params);
            });
            Hooks::Register('classbasecategory_after_save', function (&$params = null) {
                $this->getClassBaseCategoryAfterSave($params);
            });
            Hooks::Register('classbasecategory_before_delete', function (&$params = null) {
                $this->getClassBaseCategoryBeforeDelete($params);
            });
            parent::boot();
        } catch (Exception $e) {
            if (!empty($logger)) {
                $logger->critical('Unexpected error on boot method of TailoredMapListingBundle.php', ['exception' => $e]);
            } else {
                $notLoggedCriticalException = $e;
            }
        } finally {
            unset($logger);
            if ($notLoggedCriticalException !== null) {
                throw $notLoggedCriticalException;
            }
        }
    }

    /**
     * @param null $params
     * @throws Exception
     */
    private function getGeneralSettingsAfterSave(&$params = null): void
    {
        if (!empty($params) && !empty($this->container)) {
            /** @var Logger $logger */
            $logger = $this->container->get('logger');
            $notLoggedCriticalException = null;
            try {
                if (!empty($params['http_post_array']) && is_array($params['http_post_array']) && array_key_exists('save_plugin', $params['http_post_array'])) {
                    $settings = $this->container->get('settings');

                    $settings->setSetting('max_map_zoom', $_POST['max_map_zoom']);
                    $settings->setSetting('default_latitude', $_POST['default_latitude']);
                    $settings->setSetting('default_longitude', $_POST['default_longitude']);

                    $params['success'] = true;
                }
            } catch (Exception $e) {
                if ($logger !== null) {
                    $logger->critical('Unexpected error on getGeneralSettingsAfterSave method of TailoredMapListingBundle.php', ['exception' => $e]);
                } else {
                    $notLoggedCriticalException = $e;
                }
            } finally {
                unset($logger);
                if ($notLoggedCriticalException !== null) {
                    throw $notLoggedCriticalException;
                }
            }
        }
    }

    /**
     * @param null $params
     * @throws Exception
     */
    private function getGeneralSettingsAfterRenderForm(&$params = null): void
    {
        if (!empty($params) && !empty($this->container)) {
            /** @var Logger $logger */
            $logger = $this->container->get('logger');
            $notLoggedCriticalException = null;
            try {
                $settings = $this->container->get('settings');
                $arrayValueDD = array();
                $arrayNameDD = array();
                for ($i = 2; $i < 24; $i++) {
                    $arrayValueDD[] = $i;
                    $arrayNameDD[] = $i . ' x';
                }

                $zoomDropDown = html_selectBox(
                    'max_map_zoom',
                    $arrayNameDD,
                    $arrayValueDD,
                    ($settings->getDomainSetting('max_map_zoom', true) ?
                        $settings->getDomainSetting('max_map_zoom') :
                        $this->container->getParameter('tailored_map_listing.default_zoom')
                    ),
                    '',
                    'class="form-control status-select"'
                );

                echo $this->container->get('twig')->render('TailoredMapListingBundle::sitemgr-form-tailored-map.html.twig',
                    [
                        'zoomDropDown' => $zoomDropDown,
                        'default_latitude' => $settings->getDomainSetting('default_latitude', true),
                        'default_longitude' => $settings->getDomainSetting('default_longitude', true),
                    ]);
            } catch (Exception $e) {
                if ($logger !== null) {
                    $logger->critical('Unexpected error on getGeneralSettingsAfterRenderForm method of TailoredMapListingBundle.php', ['exception' => $e]);
                } else {
                    $notLoggedCriticalException = $e;
                }
            } finally {
                unset($logger);
                if ($notLoggedCriticalException !== null) {
                    throw $notLoggedCriticalException;
                }
            }
        }
    }

    /**
     * @param null $params
     * @throws Exception
     */
    private function getWidgetConstruct(&$params = null): void
    {
        if (!empty($params) && !empty($this->container)) {
            /** @var Logger $logger */
            $logger = $this->container->get('logger');
            $notLoggedCriticalException = null;
            try {
                $params['widgetNonDuplicate']['tailored'] = [
                    'Tailored Map',
                ];
            } catch (Exception $e) {
                if ($logger !== null) {
                    $logger->critical('Unexpected error on getWidgetConstruct method of TailoredMapListingBundle.php', ['exception' => $e]);
                } else {
                    $notLoggedCriticalException = $e;
                }
            } finally {
                unset($logger);
                if ($notLoggedCriticalException !== null) {
                    throw $notLoggedCriticalException;
                }
            }
        }
    }

    /**
     * @param null $params
     * @throws Exception
     */
    private function getClassBaseCategoryAfterMakeRow(&$params = null): void
    {
        if (!empty($params) && !empty($this->container)) {
            /** @var Logger $logger */
            $logger = $this->container->get('logger');
            $notLoggedCriticalException = null;
            try {
                if (!empty($params['that'])) {
                    $thatRef = &$params['that'];

                    if ($thatRef instanceof LegacyListingCategory) {
                        if (empty($params['row']['pin_id'])) {
                            $em = $this->container->get('doctrine')->getManager();
                            $connection = $em->getConnection();

                            $statement = $connection->prepare('SELECT * FROM Module_CategoryIcon WHERE category_id = :id');
                            $statement->bindValue('id', str_replace("'", '', $params['that']->id));

                            $statement->execute();

                            $row = $statement->fetch();

                            if ($row['pin_id']) {
                                $params['that']->pin_id = $row['pin_id'];
                            } else if (!$params['that']->pin_id) {
                                $params['that']->pin_id = 'NULL';
                            }
                        } else {
                            $params['that']->pin_id = $params['row']['pin_id'];
                        }
                    }
                }
            } catch (Exception $e) {
                if ($logger !== null) {
                    $logger->critical('Unexpected error on getClassBaseCategoryAfterMakeRow method of TailoredMapListingBundle.php', ['exception' => $e]);
                } else {
                    $notLoggedCriticalException = $e;
                }
            } finally {
                unset($logger);
                if ($notLoggedCriticalException !== null) {
                    throw $notLoggedCriticalException;
                }
            }
        }
    }

    /**
     * @param null $params
     * @throws Exception
     */
    private function getClassBaseCategoryAfterSave(&$params = null): void
    {
        if (!empty($params) && !empty($this->container)) {
            /** @var Logger $logger */
            $logger = $this->container->get('logger');
            $notLoggedCriticalException = null;
            try {
                if (!empty($params['that'])) {
                    /** @var LegacyListingCategory $thatRef */
                    $thatRef = &$params['that'];

                    if ($thatRef instanceof LegacyListingCategory) {
                        $em = $this->container->get('doctrine')->getManager();
                        $connection = $em->getConnection();

                        $uploadResult = null;

                        $statement = $connection->prepare('SELECT * FROM Module_CategoryIcon WHERE category_id = :category_id AND `module` = :module');
                        $statement->bindValue('category_id', str_replace("'", '', $params['that']->id));
                        $statement->bindValue('module', 'listing');

                        $statement->execute();
                        $existentModuleCategoryIcon = false;
                        if ($row = $statement->fetch()) {
                            if ($row['pin_id']) {
                                $imageobj = new Image($row['pin_id']);
                                if ($imageobj) {
                                    $imageobj->Delete();//TODO: Verificar se existe constraint de FK e se o cascade está habilitado. Caso positivo, não deverá ser feito o update e sim sempre um insert
                                }
                            }
                            $existentModuleCategoryIcon = true;
                        }

                        if ($existentModuleCategoryIcon) {
                            $statement = $connection->prepare('UPDATE Module_CategoryIcon SET pin_id = :pin_id WHERE category_id = :category_id AND `module` = :module');
                        } else {
                            $statement = $connection->prepare('INSERT INTO Module_CategoryIcon (`module`, category_id, pin_id) VALUES(:module, :category_id, :pin_id)');
                        }

                        if (property_exists($thatRef, 'pin_id')) {
                            $pinId = $thatRef->pin_id;
                            if (!empty($pinId)) {
                                $statement->bindValue('category_id', str_replace("'", '', $params['that']->id));
                                $statement->bindValue('pin_id', $pinId);
                                $statement->bindValue('module', 'listing');

                                $statement->execute();
                            }
                        }
                    }
                }
            } catch (Exception $e) {
                if ($logger !== null) {
                    $logger->critical('Unexpected error on getClassBaseCategoryAfterSave method of TailoredMapListingBundle.php', ['exception' => $e]);
                } else {
                    $notLoggedCriticalException = $e;
                }
            } finally {
                unset($logger);
                if ($notLoggedCriticalException !== null) {
                    throw $notLoggedCriticalException;
                }
            }
        }
    }

    /**
     * @param null $params
     * @throws Exception
     */
    private function getClassBaseCategoryBeforeDelete(&$params = null): void
    {
        if (!empty($params) && !empty($this->container)) {
            /** @var Logger $logger */
            $logger = $this->container->get('logger');
            $notLoggedCriticalException = null;
            try {
                if (!empty($params['that'])) {
                    $thatRef = &$params['that'];
                    $entityClassNameConstantValue = null;
                    try {
                        $class_reflex = new ReflectionClass($thatRef);
                        $class_constants = $class_reflex->getConstants();
                        if (array_key_exists('ENTITY_CLASS_NAME', $class_constants)) {
                            $entityClassNameConstantValue = $class_constants['ENTITY_CLASS_NAME'];
                        }
                    } catch (ReflectionException $e) {
                        //DO NOTHING, JUST CONSIDER NULL
                        $entityClassNameConstantValue = null;
                    }

                    if ($entityClassNameConstantValue === 'Listing') {
                        $em = $this->container->get('doctrine')->getManager();
                        $connection = $em->getConnection();

                        $statement = $connection->prepare('SELECT * FROM Module_CategoryIcon WHERE category_id = :category_id AND `module` = :module');
                        $statement->bindValue('category_id', str_replace("'", '', $params['that']->id));
                        $statement->bindValue('module', 'listing');

                        $statement->execute();

                        if ($row = $statement->fetch()) {

                            if ($row['pin_id']) {
                                $image = new Image($row['pin_id']);
                                if (!empty($image->id)) {
                                    $image->Delete();
                                }
                            }

                            $statement = $connection->prepare('DELETE FROM Module_CategoryIcon WHERE category_id = :category_id AND `module` = :module');
                            $statement->bindValue('category_id', $row['category_id']);
                            $statement->bindValue('module', 'listing');

                            $statement->execute();

                        }
                    }
                }
            } catch (Exception $e) {
                if ($logger !== null) {
                    $logger->critical('Unexpected error on getClassBaseCategoryBeforeDelete method of TailoredMapListingBundle.php', ['exception' => $e]);
                } else {
                    $notLoggedCriticalException = $e;
                }
            } finally {
                unset($logger);
                if ($notLoggedCriticalException !== null) {
                    throw $notLoggedCriticalException;
                }
            }
        }
    }

    /**
     * @param null $params
     * @throws Exception
     */
    private function getCategoryCodeAfterRemoveImage(&$params = null): void
    {
        if (!empty($params) && !empty($this->container)) {
            /** @var Logger $logger */
            $logger = $this->container->get('logger');
            $notLoggedCriticalException = null;
            try {
                if ($_POST['type'] === 'removePin') {
                    $moduleCategoryClass = $_POST['module'] . 'Category';
                    if (!empty($params['id'])) {
                        $category = new $moduleCategoryClass($params['id']);
                        $pinId = $category->getNumber('pin_id');
                        $category->setString('pin_id', 'NULL');
                        $category->Save();
                    }

                    if (!empty($pinId)) {
                        $image = new Image($pinId);
                        $image->getNumber('pin_id') and $image->Delete();
                    }
                    $params['response']['status'] = true;
                }
            } catch (Exception $e) {
                if ($logger !== null) {
                    $logger->critical('Unexpected error on getCategoryCodeAfterRemoveImage method of TailoredMapListingBundle.php', ['exception' => $e]);
                } else {
                    $notLoggedCriticalException = $e;
                }
            } finally {
                unset($logger);
                if ($notLoggedCriticalException !== null) {
                    throw $notLoggedCriticalException;
                }
            }
        }
    }

    /**
     * @param null $params
     * @throws Exception
     */
    private function getLegacyCoverImageCodeAfterAjaxCheckType(&$params = null): void
    {
        if (!empty($params) && !empty($this->container)) {
            /** @var Logger $logger */
            $logger = $this->container->get('logger');
            $notLoggedCriticalException = null;
            try {
                if (!empty($params['http_get_array']['action']) && $params['http_get_array']['type'] === 'uploadPin') {
                    $params['field_image'] = 'pin';
                    $params['type_action'] = 'upload';
                }
            } catch (Exception $e) {
                if ($logger !== null) {
                    $logger->critical('Unexpected error on getLegacyCoverImageCodeAfterAjaxCheckType method of TailoredMapListingBundle.php', ['exception' => $e]);
                } else {
                    $notLoggedCriticalException = $e;
                }
            } finally {
                unset($logger);
                if ($notLoggedCriticalException !== null) {
                    throw $notLoggedCriticalException;
                }
            }
        }
    }

    /**
     * @param null $params
     * @throws Exception
     */
    private function getListingCategoryServiceBeforeReturnGetSerializedCategory(&$params = null): void
    {
        if (!empty($params) && !empty($this->container)) {
            /** @var Logger $logger */
            $logger = $this->container->get('logger');
            $notLoggedCriticalException = null;
            try {
                if (!empty($params['serialized_category']) && !empty($params['category_id'])) {
                    $serializedCategoryRef = &$params['serialized_category'];
                    $unserializedCategoryObj = json_decode($serializedCategoryRef, false);

                    $em = $this->container->get('doctrine')->getManager();
                    $connection = $em->getConnection();

                    $statement = $connection->prepare('SELECT * FROM Module_CategoryIcon WHERE category_id = :id');
                    $statement->bindValue('id', $params['category_id']);
                    $statement->execute();

                    $row = $statement->fetch();

                    $domainImageAssetPackage = null;
                    try {
                        $domainImageAssetPackage = $this->container->get('assets.packages')->getPackage('domain_images');
                    } catch (InvalidArgumentException $getPackageInvalidArgumentException) {
                        //Do nothing, consider package inaccessible
                        $domainImageAssetPackage = null;
                    } catch (Exception $getPackageException) {
                        throw $getPackageException;
                    }
                    if (empty($domainImageAssetPackage)) {
                        $this->container->get("utility")->setPackages();//Add all asset packages
                    }
                    unset($domainImageAssetPackage);

                    if (!empty($row['pin_id'])) {
                        $pinImage = $this->container->get('doctrine')->getRepository('ImageBundle:Image')->find($row['pin_id']);
                        $pinImagePath = $this->container->get('imagehandler')->getPath($pinImage);
                        $pinImageUrl = $this->container->get('templating.helper.assets')->getUrl($pinImagePath, 'domain_images');
                        $unserializedCategoryObj->pin = (object)array('id' => $row['pin_id'], 'url' => $pinImageUrl);
                    }

                    $serializedCategoryRef = json_encode($unserializedCategoryObj);
                }
            } catch (Exception $e) {
                if ($logger !== null) {
                    $logger->critical('Unexpected error on getListingCategoryServiceBeforeReturnGetSerializedCategory method of TailoredMapListingBundle.php', ['exception' => $e]);
                } else {
                    $notLoggedCriticalException = $e;
                }
            } finally {
                unset($logger);
                if ($notLoggedCriticalException !== null) {
                    throw $notLoggedCriticalException;
                }
            }
        }
    }

    /**
     * @param null $params
     * @throws Exception
     */
    private function getCategoryCodeAfterSetupForm(&$params = null): void
    {
        if (!empty($params) && !empty($this->container)) {
            /** @var Logger $logger */
            $logger = $this->container->get('logger');
            $notLoggedCriticalException = null;
            try {
                if ($this->container->get('modstore.storage.service')->retrieve('upload_pin') === 'failed') {
                    $params['message_category'] .= $this->container->get('translator')->trans('Invalid image type. Please insert a JPG, GIF or PNG image.');
                }

                if (string_strpos($_SERVER['PHP_SELF'], LISTING_FEATURE_FOLDER) !== false) {
                    $params['fullWidth'] = false;
                }
            } catch (Exception $e) {
                if ($logger !== null) {
                    $logger->critical('Unexpected error on getListingCategoryServiceBeforeReturnGetSerializedCategory method of TailoredMapListingBundle.php', ['exception' => $e]);
                } else {
                    $notLoggedCriticalException = $e;
                }
            } finally {
                unset($logger);
                if ($notLoggedCriticalException !== null) {
                    throw $notLoggedCriticalException;
                }
            }
        }
    }

    /**
     * @param null $params
     * @throws Exception
     */
    private function getFormCategoryAfterRenderCategory(&$params = null): void
    {
        if (!empty($params) && !empty($this->container)) {
            /** @var Logger $logger */
            $logger = $this->container->get('logger');
            $notLoggedCriticalException = null;
            try {
                $em = $this->container->get('doctrine')->getManager();
                $connection = $em->getConnection();

                $pinWidth = $this->container->getParameter('tailored_map_listing.pin_width');
                $pinHeight = $this->container->getParameter('tailored_map_listing.pin_height');

                $statement = $connection->prepare('SELECT * FROM Module_CategoryIcon WHERE category_id = :category_id AND `module` = :module');
                $statement->bindValue('category_id', str_replace("'", '', $params['id']));
                $statement->bindValue('module', 'listing');

                $statement->execute();

                $pinId = null;
                $pinTag = null;
                if ($row = $statement->fetch()) {

                    $pinId = $row['pin_id'];
                    $imageObj = new Image($pinId);

                    $pinTag = $imageObj->getTag(true, $pinWidth, $pinHeight, '', false, false, 'img-responsive');
                }

                $currentRequest = $this->container->get('request_stack')->getCurrentRequest();
                if(!empty($currentRequest)) {
                    $defaultUrl = $currentRequest->getSchemeAndHttpHost();
                } else {
                    $request = MultiDomainRequest::createFromGlobals();
                    $defaultUrl = $request->getSchemeAndHttpHost();
                    unset($request);
                }
                echo $this->container->get('twig')->render('TailoredMapListingBundle::sitemgr-form-category-tailored.html.twig',
                    [
                        'table_category' => $params['table_category'],
                        'pin_id' => $pinId,
                        'title' => $params['title'],
                        'id' => $params['id'],
                        'pin_width' => $pinWidth,
                        'pin_height' => $pinHeight,
                        'max_size' => UPLOAD_MAX_SIZE,
                        'pinTag' => $pinTag,
                        'lang' => $this->getCurrentISOLang(),
                        'defaultUrl' => $defaultUrl,
                        'domainId' => $this->container->get('multi_domain.information')->getId()
                    ]);
            } catch (Exception $e) {
                if ($logger !== null) {
                    $logger->critical('Unexpected error on getFormCategoryAfterRenderCategory method of TailoredMapListingBundle.php', ['exception' => $e]);
                } else {
                    $notLoggedCriticalException = $e;
                }
            } finally {
                unset($logger);
                if ($notLoggedCriticalException !== null) {
                    throw $notLoggedCriticalException;
                }
            }
        }
    }

    /**
     * @param null $params
     * @throws Exception
     */
    private function getDistanceSorterBeforeReturnSubscribers(&$params = null): void
    {
        if (!empty($params) && !empty($this->container)) {
            /** @var Logger $logger */
            $logger = $this->container->get('logger');
            $notLoggedCriticalException = null;
            try {
                $params['events'] = array_merge(
                    $params['events'],
                    [
                        'search.global.map' => 'register',
                        'search.listing.map' => 'register',
                    ]
                );
            } catch (Exception $e) {
                if ($logger !== null) {
                    $logger->critical('Unexpected error on getDistanceSorterBeforeReturnSubscribers method of TailoredMapListingBundle.php', ['exception' => $e]);
                } else {
                    $notLoggedCriticalException = $e;
                }
            } finally {
                unset($logger);
                if ($notLoggedCriticalException !== null) {
                    throw $notLoggedCriticalException;
                }
            }
        }
    }

    /**
     * @param null $params
     * @throws Exception
     */
    private function getWysiwygExtensionBeforeValidateWidget(&$params = null): void
    {
        if (!empty($params) && !empty($this->container)) {
            /** @var Logger $logger */
            $logger = $this->container->get('logger');
            $notLoggedCriticalException = null;
            try {
                switch ($params['widgetFile']) {

                    case '::widgets/page-editor/map/tailored-map-search.html.twig':
                        $params['widgetFile'] = 'TailoredMapListingBundle::tailored-placement.html.twig';
                        $this->container->get('modstore.storage.service')->store('loadMap', true);
                        break;

                    case '::widgets/page-editor/map/tailored-map.html.twig':
                        $params['widgetFile'] = 'TailoredMapListingBundle::tailored-placement-without-search.html.twig';
                        $this->container->get('modstore.storage.service')->store('loadMap', true);
                        break;

                }
            } catch (Exception $e) {
                if ($logger !== null) {
                    $logger->critical('Unexpected error on getWysiwygExtensionBeforeValidateWidget method of TailoredMapListingBundle.php', ['exception' => $e]);
                } else {
                    $notLoggedCriticalException = $e;
                }
            } finally {
                unset($logger);
                if ($notLoggedCriticalException !== null) {
                    throw $notLoggedCriticalException;
                }
            }
        }
    }

    /**
     * @param null $params
     * @throws Exception
     */
    private function getWysiwygExtensionBeforeRenderWidget(&$params = null): void
    {
        if (!empty($params) && !empty($this->container)) {
            /** @var Logger $logger */
            $logger = $this->container->get('logger');
            $notLoggedCriticalException = null;
            try {
                if ($this->container->get('modstore.storage.service')->retrieveAndDestroy('loadMap')) {
                    $tailoredMap = $this->container->get('tailoredplacement.map');
                    $parameterHandler = $this->container->get('search.parameters');

                    $params['data']['map'] = $tailoredMap->buildTailoredMap(
                        $this->container->get('settings')->getDomainSetting('default_latitude'),
                        $this->container->get('settings')->getDomainSetting('default_longitude'),
                        $this->container->get('settings')->getDomainSetting('max_map_zoom'),
                        implode(' ', $parameterHandler->getWheres())
                    );
                }
            } catch (Exception $e) {
                if ($logger !== null) {
                    $logger->critical('Unexpected error on getWysiwygExtensionBeforeRenderWidget method of TailoredMapListingBundle.php', ['exception' => $e]);
                } else {
                    $notLoggedCriticalException = $e;
                }
            } finally {
                unset($logger);
                if ($notLoggedCriticalException !== null) {
                    throw $notLoggedCriticalException;
                }
            }
        }
    }

    /**
     * @param null $params
     * @throws Exception
     */
    private function getBaseAfterAddJs(&$params = null): void
    {
        if (!empty($params) && !empty($this->container)) {
            /** @var Logger $logger */
            $logger = $this->container->get('logger');
            $notLoggedCriticalException = null;
            try {
                if (empty($this->container->get('modstore.storage.service')->retrieve('needGeoLocation'))) {
                    $this->container->get('modstore.storage.service')->store('needGeoLocation', 'true');

                    echo $this->container->get('twig')->render('TailoredMapListingBundle:js:html5geo.html.twig');
                }
            } catch (Exception $e) {
                if ($logger !== null) {
                    $logger->critical('Unexpected error on getBaseAfterAddJs method of TailoredMapListingBundle.php', ['exception' => $e]);
                } else {
                    $notLoggedCriticalException = $e;
                }
            } finally {
                unset($logger);
                if ($notLoggedCriticalException !== null) {
                    throw $notLoggedCriticalException;
                }
            }
        }
    }
}
