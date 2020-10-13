<?php

namespace ArcaSolutions\ModStoresBundle\Plugins\MarketSelection;

use ArcaSolutions\CoreBundle\Kernel\Kernel;
use ArcaSolutions\ModStoresBundle\Kernel\Hooks;
use ArcaSolutions\ModStoresBundle\Plugins\AbstractPluginBundle as Bundle;
use ArcaSolutions\SearchBundle\Services\SearchEngine;
use Elastica\Query\Term;
use Exception;
use Location1;
use Location2;
use Location3;
use Location4;
use Location5;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Response;

class MarketSelectionBundle extends Bundle
{
    private $devEnvironment = false;
    /**
     * Boots the Bundle.
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
                Hooks::Register('classlocation1_after_makerow', function (&$params = null) {
                    return $this->getClassLocation1AfterMakeRow($params);
                });
                Hooks::Register('classlocation2_after_makerow', function (&$params = null) {
                    return $this->getClassLocation2AfterMakeRow($params);
                });
                Hooks::Register('classlocation3_after_makerow', function (&$params = null) {
                    return $this->getClassLocation3AfterMakeRow($params);
                });
                Hooks::Register('classlocation4_after_makerow', function (&$params = null) {
                    return $this->getClassLocation4AfterMakeRow($params);
                });
                Hooks::Register('classlocation5_after_makerow', function (&$params = null) {
                    return $this->getClassLocation5AfterMakeRow($params);
                });
                Hooks::Register('classlocation1_after_updatequery', function (&$params = null) {
                    return $this->getClassLocation1AfterUpdateQuery($params);
                });
                Hooks::Register('classlocation2_after_updatequery', function (&$params = null) {
                    return $this->getClassLocation2AfterUpdateQuery($params);
                });
                Hooks::Register('classlocation3_after_updatequery', function (&$params = null) {
                    return $this->getClassLocation3AfterUpdateQuery($params);
                });
                Hooks::Register('classlocation4_after_updatequery', function (&$params = null) {
                    return $this->getClassLocation4AfterUpdateQuery($params);
                });
                Hooks::Register('classlocation5_after_updatequery', function (&$params = null) {
                    return $this->getClassLocation5AfterUpdateQuery($params);
                });
                Hooks::Register('classlocation1_after_insertquery', function (&$params = null) {
                    return $this->getClassLocation1AfterInsertQuery($params);
                });
                Hooks::Register('classlocation2_after_insertquery', function (&$params = null) {
                    return $this->getClassLocation2AfterInsertQuery($params);
                });
                Hooks::Register('classlocation3_after_insertquery', function (&$params = null) {
                    return $this->getClassLocation3AfterInsertQuery($params);
                });
                Hooks::Register('classlocation4_after_insertquery', function (&$params = null) {
                    return $this->getClassLocation4AfterInsertQuery($params);
                });
                Hooks::Register('classlocation5_after_insertquery', function (&$params = null) {
                    return $this->getClassLocation5AfterInsertQuery($params);
                });
                Hooks::Register('formlocation_after_render_abbreviation', function (&$params = null) {
                    return $this->getFormLocationAfterRenderAbbreviation($params);
                });
                Hooks::Register('locationcode_after_setup_insertfields', function (&$params = null) {
                    return $this->getLocationCodeAfterSetupInsertFields($params);
                });
                Hooks::Register('locationcode_after_setup_updatefields', function (&$params = null) {
                    return $this->getLocationCodeAfterSetupUpdateFields($params);
                });
                Hooks::Register('locationsettings_before_return', function (&$params = null) {
                    return $this->getLocationSettingsBeforeReturn($params);
                });
                Hooks::Register('colorschemefunct_before_write_customcss', function (&$params = null) {
                    return $this->getColorSchemeFunctBeforeWriteCustomCss($params);
                });

            } else {

                /*
                 * Register front only bundle hooks
                 */
                Hooks::Register('base_after_add_js', function (&$params = null) {
                    return $this->getBaseAfterJs($params);
                });
                Hooks::Register('classifiedconfiguration_before_setup_itemquery', function (&$params = null) {
                    return $this->getClassifiedConfigurationBeforeSetupItemQuery($params);
                });
                Hooks::Register('classifiedconfiguration_before_setup_featuredquery', function (&$params = null) {
                    return $this->getClassifiedConfigurationBeforeSetupFeaturedQuery($params);
                });
                Hooks::Register('classifiedconfiguration_before_setup_bestofquery', function (&$params = null) {
                    return $this->getClassifiedConfigurationBeforeSetupBestofQuery($params);
                });
                Hooks::Register('classifiedconfiguration_before_setup_recentquery', function (&$params = null) {
                    return $this->getClassifiedConfigurationBeforeSetupRecentQuery($params);
                });
                Hooks::Register('dealconfiguration_before_setup_itemquery', function (&$params = null) {
                    return $this->getDealConfigurationBeforeSetupItemQuery($params);
                });
                Hooks::Register('dealconfiguration_before_setup_popularquery', function (&$params = null) {
                    return $this->getDealConfigurationBeforeSetupPopularQuery($params);
                });
                Hooks::Register('dealconfiguration_before_setup_recentquery', function (&$params = null) {
                    return $this->getDealConfigurationBeforeSetupRecentQuery($params);
                });
                Hooks::Register('eventconfiguration_before_setup_featuredquery', function (&$params = null) {
                    return $this->getEventConfigurationBeforeSetupFeaturedQuery($params);
                });
                Hooks::Register('eventconfiguration_before_setup_popularquery', function (&$params = null) {
                    return $this->getEventConfigurationBeforeSetupPopularQuery($params);
                });
                Hooks::Register('eventconfiguration_before_return_filterquery', function (&$params = null) {
                    return $this->getEventConfigurationBeforeSetupFilterQuery($params);
                });
                Hooks::Register('event_before_setup_dateaggregationquery', function (&$params = null) {
                    return $this->getEventBeforeSetupDateAggregationQuery($params);
                });
                Hooks::Register('listingconfiguration_before_setup_featuredquery', function (&$params = null) {
                    return $this->getListingConfigurationBeforeSetupFeaturedQuery($params);
                });
                Hooks::Register('listingconfiguration_before_setup_itemquery', function (&$params = null) {
                    return $this->getListingConfigurationBeforeSetupItemQuery($params);
                });
                Hooks::Register('listingconfiguration_before_setup_bestofquery', function (&$params = null) {
                    return $this->getListingConfigurationBeforeSetupBestofQuery($params);
                });
                Hooks::Register('eventconfiguration_before_setup_recurringquery', function (&$params = null) {
                    return $this->getListingConfigurationBeforeSetupRecurringQuery($params);
                });
                Hooks::Register('search_before_after_searchparams', function (&$params = null) {
                    return $this->getSearchBeforeAfterSearchParams($params);
                });
                Hooks::Register('search_after_searchpagination', function (&$params = null) {
                    return $this->getSearchAfterSearchPagination($params);
                });
                Hooks::Register('categoryrepository_after_setup_countcategoryquery', function (&$params = null) {
                    return $this->getCategoryRepositoryAfterSetupCountCategoryQuery($params);
                });
                Hooks::Register('searchengine_after_setup_locationbymodulequery', function (&$params = null) {
                    return $this->getSearchEngineAfterSetupLocationByModuleQuery($params);
                });
                Hooks::Register('calendarcontroller_before_setup_calendarquery', function (&$params = null) {
                    return $this->getCalendarControllerBeforeSetupCalendarQuery($params);
                });
            }

            // Todo: revise hooks names
            Hooks::Register('location_settings_featuredmarket', function (&$params = null) {
                return $this->getLocationSettingsBeforeReturn($params);
            });
            Hooks::Register('sitemgr_form_locationsettings_market', function (&$params = null) {
                return $this->getFormLocWettingsMarket($params);
            });
            Hooks::Register('sitemgr_form_locationsettings_checkbox_market', function (&$params = null) {
                return $this->getFormLocSettingsCheckbox($params);
            });
            Hooks::Register('search_default_controller_market', function (&$params = null) {
                return $this->getSearchMarket($params);
            });
            Hooks::Register('marketbox', function (&$params = null) {
                return $this->getMarketBox($params);
            });
            parent::boot();
        } catch (Exception $e) {
            if (!empty($logger)) {
                $logger->critical('Unexpected error on boot method of MarketSelectionBundle.php', ['exception' => $e]);
            } else {
                $notLoggedCriticalException = $e;
            }
        } finally {
            unset($logger);
            if (!empty($notLoggedCriticalException)) {
                throw $notLoggedCriticalException;
            }
        }
    }

    private function getClassLocation1AfterMakeRow(&$params = null)
    {
        if (!$params['that']->featured_market) {
            $em = $this->container->get('doctrine')->getManager('main');
            $connection = $em->getConnection('main');

            $statement = $connection->prepare('SELECT * FROM Location_FeaturedMarket WHERE domain_id = :domain_id AND location_level = :location_level AND location_id = :location_id');
            $statement->bindValue('domain_id', $this->container->get('multi_domain.information')->getId());
            $statement->bindValue('location_level', 1);
            $statement->bindValue('location_id', $_GET['id']);
            $statement->execute();

            $row = $statement->fetch();

            if ($row) {
                $params['that']->featured_market = 'y';
            } else {
                $params['that']->featured_market = 'n';
            }
        }
    }

    private function getClassLocation2AfterMakeRow(&$params = null)
    {
        if (!$params['that']->featured_market) {
            $em = $this->container->get('doctrine')->getManager('main');
            $connection = $em->getConnection('main');

            $statement = $connection->prepare('SELECT * FROM Location_FeaturedMarket WHERE domain_id = :domain_id AND location_level = :location_level AND location_id = :location_id');
            $statement->bindValue('domain_id', $this->container->get('multi_domain.information')->getId());
            $statement->bindValue('location_level', 2);
            $statement->bindValue('location_id', $_GET['id']);
            $statement->execute();

            $row = $statement->fetch();

            if ($row) {
                $params['that']->featured_market = 'y';
            } else {
                $params['that']->featured_market = 'n';
            }
        }
    }

    private function getClassLocation3AfterMakeRow(&$params = null)
    {
        if (!$params['that']->featured_market) {
            $em = $this->container->get('doctrine')->getManager('main');
            $connection = $em->getConnection('main');

            $statement = $connection->prepare('SELECT * FROM Location_FeaturedMarket WHERE domain_id = :domain_id AND location_level = :location_level AND location_id = :location_id');
            $statement->bindValue('domain_id', $this->container->get('multi_domain.information')->getId());
            $statement->bindValue('location_level', 3);
            $statement->bindValue('location_id', $_GET['id']);
            $statement->execute();

            $row = $statement->fetch();

            if ($row) {
                $params['that']->featured_market = 'y';
            } else {
                $params['that']->featured_market = 'n';
            }
        }
    }

    private function getClassLocation4AfterMakeRow(&$params = null)
    {
        if (!$params['that']->featured_market) {
            $em = $this->container->get('doctrine')->getManager('main');
            $connection = $em->getConnection('main');

            $statement = $connection->prepare('SELECT * FROM Location_FeaturedMarket WHERE domain_id = :domain_id AND location_level = :location_level AND location_id = :location_id');
            $statement->bindValue('domain_id', $this->container->get('multi_domain.information')->getId());
            $statement->bindValue('location_level', 4);
            $statement->bindValue('location_id', $_GET['id']);
            $statement->execute();

            $row = $statement->fetch();

            if ($row) {
                $params['that']->featured_market = 'y';
            } else {
                $params['that']->featured_market = 'n';
            }
        }
    }

    private function getClassLocation5AfterMakeRow(&$params = null)
    {
        if (!$params['that']->featured_market) {
            $em = $this->container->get('doctrine')->getManager('main');
            $connection = $em->getConnection('main');

            $statement = $connection->prepare('SELECT * FROM Location_FeaturedMarket WHERE domain_id = :domain_id AND location_level = :location_level AND location_id = :location_id');
            $statement->bindValue('domain_id', $this->container->get('multi_domain.information')->getId());
            $statement->bindValue('location_level', 5);
            $statement->bindValue('location_id', $_GET['id']);
            $statement->execute();

            $row = $statement->fetch();

            if ($row) {
                $params['that']->featured_market = "'y'";
            } else {
                $params['that']->featured_market = "'n'";
            }
        }
    }

    private function getClassLocation1AfterUpdateQuery(&$params = null)
    {
        if ($params['that'] instanceof Location1) {
            $em = $this->container->get('doctrine')->getManager('main');
            $connection = $em->getConnection();

            $statement = $connection->prepare('SELECT * FROM Location_FeaturedMarket WHERE domain_id = :domain_id AND location_level = 1 AND location_id = :id');
            $statement->bindValue('id', $_POST['id']);
            $statement->bindValue('domain_id', $this->container->get('multi_domain.information')->getId());
            $statement->execute();

            $row = $statement->fetch();

            if ($params['that']->featured_market !== "'y'") {
                if ($row) {
                    $statement = $connection->prepare('DELETE FROM Location_FeaturedMarket WHERE location_id = :id AND location_level = 1 AND domain_id = :domain_id');
                    $statement->bindValue('domain_id', $this->container->get('multi_domain.information')->getId());
                    $statement->bindValue('id', $_POST['id']);
                    $statement->execute();
                }
            } else {
                if (!$row) {
                    $statement = $connection->prepare('INSERT INTO Location_FeaturedMarket(`domain_id`, `location_level`, `location_id`) VALUES (:domain_id, 1, :id)');
                    $statement->bindValue('domain_id', $this->container->get('multi_domain.information')->getId());
                    $statement->bindValue('id', $_POST['id']);
                    $statement->execute();
                }
                if ($row['location_id'] == 0) {
                    $statement = $connection->prepare('SELECT id FROM Location_1 ORDER BY id DESC LIMIT 1');
                    $statement->execute();
                    $id = $statement->fetch();

                    $statement = $connection->prepare('INSERT INTO Location_FeaturedMarket(`domain_id`, `location_level`, `location_id`) VALUES (:domain_id, 1, :id)');
                    $statement->bindValue('domain_id', $this->container->get('multi_domain.information')->getId());
                    $statement->bindValue('id', $id['id'] + 1);
                    $statement->execute();
                }
            }
        }
    }

    private function getClassLocation2AfterUpdateQuery(&$params = null)
    {
        if ($params['that'] instanceof Location2) {
            $em = $this->container->get('doctrine')->getManager('main');
            $connection = $em->getConnection();

            $statement = $connection->prepare('SELECT * FROM Location_FeaturedMarket WHERE domain_id = :domain_id AND location_level = 2 AND location_id = :id');
            $statement->bindValue('id', $_POST['id']);
            $statement->bindValue('domain_id', $this->container->get('multi_domain.information')->getId());
            $statement->execute();

            $row = $statement->fetch();

            if ($params['that']->featured_market !== "'y'") {
                if ($row) {
                    $statement = $connection->prepare('DELETE FROM Location_FeaturedMarket WHERE location_id = :id AND location_level = 2 AND domain_id = :domain_id');
                    $statement->bindValue('domain_id', $this->container->get('multi_domain.information')->getId());
                    $statement->bindValue('id', $_POST['id']);
                    $statement->execute();
                }
            } else {

                if (!$row) {
                    $statement = $connection->prepare('INSERT INTO Location_FeaturedMarket(`domain_id`, `location_level`, `location_id`) VALUES (:domain_id, 2, :id)');
                    $statement->bindValue('domain_id', $this->container->get('multi_domain.information')->getId());
                    $statement->bindValue('id', $_POST['id']);
                    $statement->execute();
                }
                if ($row['location_id'] == 0) {
                    $statement = $connection->prepare('SELECT id FROM Location_2 ORDER BY id DESC LIMIT 1');
                    $statement->execute();
                    $id = $statement->fetch();

                    $statement = $connection->prepare('INSERT INTO Location_FeaturedMarket(`domain_id`, `location_level`, `location_id`) VALUES (:domain_id, 2, :id)');
                    $statement->bindValue('domain_id', $this->container->get('multi_domain.information')->getId());
                    $statement->bindValue('id', $id['id'] + 1);
                    $statement->execute();
                }
            }
        }
    }

    private function getClassLocation3AfterUpdateQuery(&$params = null)
    {
        if ($params['that'] instanceof Location3) {
            $em = $this->container->get('doctrine')->getManager('main');
            $connection = $em->getConnection();

            $statement = $connection->prepare('SELECT * FROM Location_FeaturedMarket WHERE domain_id = :domain_id AND location_level = 3 AND location_id = :id');
            $statement->bindValue('id', $_POST['id']);
            $statement->bindValue('domain_id', $this->container->get('multi_domain.information')->getId());
            $statement->execute();

            $row = $statement->fetch();

            if ($params['that']->featured_market !== "'y'") {
                if ($row) {
                    $statement = $connection->prepare('DELETE FROM Location_FeaturedMarket WHERE location_id = :id AND location_level = 3 AND domain_id = :domain_id');
                    $statement->bindValue('domain_id', $this->container->get('multi_domain.information')->getId());
                    $statement->bindValue('id', $_POST['id']);
                    $statement->execute();
                }
            } else {
                if (!$row) {
                    $statement = $connection->prepare('INSERT INTO Location_FeaturedMarket(`domain_id`, `location_level`, `location_id`) VALUES (:domain_id, 3, :id)');
                    $statement->bindValue('domain_id', $this->container->get('multi_domain.information')->getId());
                    $statement->bindValue('id', $_POST['id']);
                    $statement->execute();
                }
                if ($row['location_id'] == 0) {
                    $statement = $connection->prepare('SELECT id FROM Location_3 ORDER BY id DESC LIMIT 1');
                    $statement->execute();
                    $id = $statement->fetch();

                    $statement = $connection->prepare('INSERT INTO Location_FeaturedMarket(`domain_id`, `location_level`, `location_id`) VALUES (:domain_id, 3, :id)');
                    $statement->bindValue('domain_id', $this->container->get('multi_domain.information')->getId());
                    $statement->bindValue('id', $id['id'] + 1);
                    $statement->execute();
                }
            }
        }
    }

    private function getClassLocation4AfterUpdateQuery(&$params = null)
    {
        if ($params['that'] instanceof Location4) {
            $em = $this->container->get('doctrine')->getManager('main');
            $connection = $em->getConnection();

            $statement = $connection->prepare('SELECT * FROM Location_FeaturedMarket WHERE domain_id = :domain_id AND location_level = 4 AND location_id = :id');
            $statement->bindValue('id', $_POST['id']);
            $statement->bindValue('domain_id', $this->container->get('multi_domain.information')->getId());
            $statement->execute();

            $row = $statement->fetch();

            if ($params['that']->featured_market !== "'y'") {
                if ($row) {
                    $statement = $connection->prepare('DELETE FROM Location_FeaturedMarket WHERE location_id = :id AND location_level = 4 AND domain_id = :domain_id');
                    $statement->bindValue('domain_id', $this->container->get('multi_domain.information')->getId());
                    $statement->bindValue('id', $_POST['id']);
                    $statement->execute();
                }
            } else {
                if (!$row) {
                    $statement = $connection->prepare('INSERT INTO Location_FeaturedMarket(`domain_id`, `location_level`, `location_id`) VALUES (:domain_id, 4, :id)');
                    $statement->bindValue('domain_id', $this->container->get('multi_domain.information')->getId());
                    $statement->bindValue('id', $_POST['id']);
                    $statement->execute();
                }
                if ($row['location_id'] == 0) {
                    $statement = $connection->prepare('SELECT id FROM Location_4 ORDER BY id DESC LIMIT 1');
                    $statement->execute();
                    $id = $statement->fetch();

                    $statement = $connection->prepare('INSERT INTO Location_FeaturedMarket(`domain_id`, `location_level`, `location_id`) VALUES (:domain_id, 4, :id)');
                    $statement->bindValue('domain_id', $this->container->get('multi_domain.information')->getId());
                    $statement->bindValue('id', $id['id'] + 1);
                    $statement->execute();
                }
            }
        }
    }

    private function getClassLocation5AfterUpdateQuery(&$params = null)
    {
        if ($params['that'] instanceof Location5) {
            $em = $this->container->get('doctrine')->getManager('main');
            $connection = $em->getConnection();

            $statement = $connection->prepare('SELECT * FROM Location_FeaturedMarket WHERE domain_id = :domain_id AND location_level = 5 AND location_id = :id');
            $statement->bindValue('id', $_POST['id']);
            $statement->bindValue('domain_id', $this->container->get('multi_domain.information')->getId());
            $statement->execute();

            $row = $statement->fetch();

            if ($params['that']->featured_market !== "'y'") {
                if ($row) {
                    $statement = $connection->prepare('DELETE FROM Location_FeaturedMarket WHERE location_id = :id AND location_level = 5 AND domain_id = :domain_id');
                    $statement->bindValue('domain_id', $this->container->get('multi_domain.information')->getId());
                    $statement->bindValue('id', $_POST['id']);
                    $statement->execute();
                }
            } else {
                if (!$row) {
                    $statement = $connection->prepare('INSERT INTO Location_FeaturedMarket(`domain_id`, `location_level`, `location_id`) VALUES (:domain_id, 5, :id)');
                    $statement->bindValue('domain_id', $this->container->get('multi_domain.information')->getId());
                    $statement->bindValue('id', $_POST['id']);
                    $statement->execute();
                }
                if ($row['location_id'] == 0) {
                    $statement = $connection->prepare('SELECT id FROM Location_5 ORDER BY id DESC LIMIT 1');
                    $statement->execute();
                    $id = $statement->fetch();

                    $statement = $connection->prepare('INSERT INTO Location_FeaturedMarket(`domain_id`, `location_level`, `location_id`) VALUES (:domain_id, 5, :id)');
                    $statement->bindValue('domain_id', $this->container->get('multi_domain.information')->getId());
                    $statement->bindValue('id', $id['id'] + 1);
                    $statement->execute();
                }
            }
        }
    }

    private function getClassLocation1AfterInsertQuery(&$params = null)
    {
        $em = $this->container->get('doctrine')->getManager('main');
        $connection = $em->getConnection();

        $statement = $connection->prepare('SELECT * FROM Location_FeaturedMarket WHERE domain_id = :domain_id AND location_level = 1 AND location_id = :id');
        $statement->bindValue('id', $_POST['id']);
        $statement->bindValue('domain_id', $this->container->get('multi_domain.information')->getId());
        $statement->execute();

        $row = $statement->fetch();

        if ($params['that']->featured_market !== "'y'") {
            if ($row) {
                $statement = $connection->prepare('DELETE FROM Location_FeaturedMarket WHERE location_id = :id AND location_level = 1 AND domain_id = :domain_id');
                $statement->bindValue('domain_id', $this->container->get('multi_domain.information')->getId());
                $statement->bindValue('id', $_POST['id']);
                $statement->execute();
            }
        } else {
            if (!$row) {
                $statement = $connection->prepare('INSERT INTO Location_FeaturedMarket(`domain_id`, `location_level`, `location_id`) VALUES (:domain_id, 1, :id)');
                $statement->bindValue('domain_id', $this->container->get('multi_domain.information')->getId());
                $statement->bindValue('id', $_POST['id']);
                $statement->execute();
            }
            if ($row['location_id'] == 0) {
                $statement = $connection->prepare('SELECT id FROM Location_1 ORDER BY id DESC LIMIT 1');
                $statement->execute();
                $id = $statement->fetch();

                $statement = $connection->prepare('INSERT INTO Location_FeaturedMarket(`domain_id`, `location_level`, `location_id`) VALUES (:domain_id, 1, :id)');
                $statement->bindValue('domain_id', $this->container->get('multi_domain.information')->getId());
                $statement->bindValue('id', $id['id'] + 1);
                $statement->execute();
            }
        }
    }

    private function getClassLocation2AfterInsertQuery(&$params = null)
    {
        $em = $this->container->get('doctrine')->getManager('main');
        $connection = $em->getConnection();

        $statement = $connection->prepare('SELECT * FROM Location_FeaturedMarket WHERE domain_id = :domain_id AND location_level = 2 AND location_id = :id');
        $statement->bindValue('id', $_POST['id']);
        $statement->bindValue('domain_id', $this->container->get('multi_domain.information')->getId());
        $statement->execute();

        $row = $statement->fetch();

        if ($params['that']->featured_market !== "'y'") {
            if ($row) {
                $statement = $connection->prepare('DELETE FROM Location_FeaturedMarket WHERE location_id = :id AND location_level = 2 AND domain_id = :domain_id');
                $statement->bindValue('domain_id', $this->container->get('multi_domain.information')->getId());
                $statement->bindValue('id', $_POST['id']);
                $statement->execute();
            }
        } else {

            if (!$row) {
                $statement = $connection->prepare('INSERT INTO Location_FeaturedMarket(`domain_id`, `location_level`, `location_id`) VALUES (:domain_id, 2, :id)');
                $statement->bindValue('domain_id', $this->container->get('multi_domain.information')->getId());
                $statement->bindValue('id', $_POST['id']);
                $statement->execute();
            }
            if ($row['location_id'] == 0) {
                $statement = $connection->prepare('SELECT id FROM Location_2 ORDER BY id DESC LIMIT 1');
                $statement->execute();
                $id = $statement->fetch();

                $statement = $connection->prepare('INSERT INTO Location_FeaturedMarket(`domain_id`,`location_level`,`location_id`) VALUES (:domain_id, 2, :id)');
                $statement->bindValue('domain_id', $this->container->get('multi_domain.information')->getId());
                $statement->bindValue('id', $id['id'] + 1);
                $statement->execute();
            }
        }
    }

    private function getClassLocation3AfterInsertQuery(&$params = null)
    {
        $em = $this->container->get('doctrine')->getManager('main');
        $connection = $em->getConnection();

        $statement = $connection->prepare('SELECT * FROM Location_FeaturedMarket WHERE domain_id = :domain_id AND location_level = 3 AND location_id = :id');
        $statement->bindValue('id', $_POST['id']);
        $statement->bindValue('domain_id', $this->container->get('multi_domain.information')->getId());
        $statement->execute();

        $row = $statement->fetch();

        if ($params['that']->featured_market !== "'y'") {
            if ($row) {
                $statement = $connection->prepare('DELETE FROM Location_FeaturedMarket WHERE location_id = :id AND location_level = 3 AND domain_id = :domain_id');
                $statement->bindValue('domain_id', $this->container->get('multi_domain.information')->getId());
                $statement->bindValue('id', $_POST['id']);
                $statement->execute();
            }
        } else {
            if (!$row) {
                $statement = $connection->prepare('INSERT INTO Location_FeaturedMarket(`domain_id`, `location_level`, `location_id`) VALUES (:domain_id, 3, :id)');
                $statement->bindValue('domain_id', $this->container->get('multi_domain.information')->getId());
                $statement->bindValue('id', $_POST['id']);
                $statement->execute();
            }
            if ($row['location_id'] == 0) {
                $statement = $connection->prepare('SELECT id FROM Location_3 ORDER BY id DESC LIMIT 1');
                $statement->execute();
                $id = $statement->fetch();

                $statement = $connection->prepare('INSERT INTO Location_FeaturedMarket(`domain_id`, `location_level`, `location_id`) VALUES (:domain_id, 3, :id)');
                $statement->bindValue('domain_id', $this->container->get('multi_domain.information')->getId());
                $statement->bindValue('id', $id['id'] + 1);
                $statement->execute();
            }
        }
    }

    private function getClassLocation4AfterInsertQuery(&$params = null)
    {
        $em = $this->container->get('doctrine')->getManager('main');
        $connection = $em->getConnection();

        $statement = $connection->prepare('SELECT * FROM Location_FeaturedMarket WHERE domain_id = :domain_id AND location_level = 4 AND location_id = :id');
        $statement->bindValue('id', $_POST['id']);
        $statement->bindValue('domain_id', $this->container->get('multi_domain.information')->getId());
        $statement->execute();

        $row = $statement->fetch();

        if ($params['that']->featured_market !== "'y'") {
            if ($row) {
                $statement = $connection->prepare('DELETE FROM Location_FeaturedMarket WHERE location_id = :id AND location_level = 4 AND domain_id = :domain_id');
                $statement->bindValue('domain_id', $this->container->get('multi_domain.information')->getId());
                $statement->bindValue('id', $_POST['id']);
                $statement->execute();
            }
        } else {
            if (!$row) {
                $statement = $connection->prepare('INSERT INTO Location_FeaturedMarket(`domain_id`, `location_level`, `location_id`) VALUES (:domain_id, 4, :id)');
                $statement->bindValue('domain_id', $this->container->get('multi_domain.information')->getId());
                $statement->bindValue('id', $_POST['id']);
                $statement->execute();
            }
            if ($row['location_id'] == 0) {
                $statement = $connection->prepare('SELECT id FROM Location_4 ORDER BY id DESC LIMIT 1');
                $statement->execute();
                $id = $statement->fetch();

                $statement = $connection->prepare('INSERT INTO Location_FeaturedMarket(`domain_id`, `location_level`, `location_id`) VALUES ( :domain_id, 4, :id)');
                $statement->bindValue('domain_id', $this->container->get('multi_domain.information')->getId());
                $statement->bindValue('id', $id['id'] + 1);
                $statement->execute();
            }
        }
    }

    private function getClassLocation5AfterInsertQuery(&$params = null)
    {
        $em = $this->container->get('doctrine')->getManager('main');
        $connection = $em->getConnection();

        $statement = $connection->prepare('SELECT * FROM Location_FeaturedMarket WHERE domain_id = :domain_id AND location_level = 5 AND location_id = :id');
        $statement->bindValue('id', $_POST['id']);
        $statement->bindValue('domain_id', $this->container->get('multi_domain.information')->getId());
        $statement->execute();

        $row = $statement->fetch();

        if ($params['that']->featured_market !== "'y'") {
            if ($row) {
                $statement = $connection->prepare('DELETE FROM Location_FeaturedMarket WHERE location_id = :id AND location_level = 5 AND domain_id = :domain_id');
                $statement->bindValue('domain_id', $this->container->get('multi_domain.information')->getId());
                $statement->bindValue('id', $_POST['id']);
                $statement->execute();
            }
        } else {
            if (!$row) {
                $statement = $connection->prepare('INSERT INTO Location_FeaturedMarket(`domain_id`, `location_level`, `location_id`) VALUES (:domain_id, 5, :id)');
                $statement->bindValue('domain_id', $this->container->get('multi_domain.information')->getId());
                $statement->bindValue('id', $_POST['id']);
                $statement->execute();
            }
            if ($row['location_id'] == 0) {
                $statement = $connection->prepare('SELECT id FROM Location_5 ORDER BY id DESC LIMIT 1');
                $statement->execute();
                $id = $statement->fetch();

                $statement = $connection->prepare('INSERT INTO Location_FeaturedMarket(`domain_id`, `location_level`, `location_id`) VALUES (:domain_id, 5, :id)');
                $statement->bindValue('domain_id', $this->container->get('multi_domain.information')->getId());
                $statement->bindValue('id', $id['id'] + 1);
                $statement->execute();
            }
        }
    }

    private function getFormLocationAfterRenderAbbreviation(&$params = null)
    {
        $marketLevel = $this->container->get('settings')->getDomainSetting('show_market');

        if((int)$marketLevel === (int)$params['location_level']) {

            $em = $this->container->get('doctrine')->getManager('main');
            $connection = $em->getConnection();

            $statement = $connection->prepare('SELECT * FROM Location_FeaturedMarket WHERE domain_id = :domain_id AND location_level = :location_level AND location_id = :location_id');
            $statement->bindValue('domain_id', $this->container->get('multi_domain.information')->getId());
            $statement->bindValue('location_level', $params['location_level']);
            $statement->bindValue('location_id', $params['id']);
            $statement->execute();

            $row = $statement->fetch();

            $featuredMarket = '';
            if ($row) {
                $featuredMarket = 'on';
            }

            echo $this->container->get('templating')->render('MarketSelectionBundle::sitemgr-location-market.html.twig',
                [
                    'featured_market' => $featuredMarket,
                ]);
        }

    }

    private function getLocationCodeAfterSetupInsertFields(&$params = null)
    {
        if ($_POST['featured_market']) {
            $_POST['featured_market'] = ($_POST['featured_market'] === 'on' ? 'y' : 'n');
        }

        $params['objLocation']->setString('featured_market', $_POST['featured_market']);
    }

    private function getLocationCodeAfterSetupUpdateFields(&$params = null)
    {
        if ($_POST['featured_market']) {
            $_POST['featured_market'] = ($_POST['featured_market'] === 'on' ? 'y' : 'n');
        }

        $params['objLocation']->setString('featured_market', $_POST['featured_market']);
    }

    private function getLocationSettingsBeforeReturn(&$params = null)
    {
        $this->container->get('settings')->setSetting('show_market', $_POST['show_market']);
    }

    private function getColorSchemeFunctBeforeWriteCustomCss(&$params = null)
    {
        $params['phpContent'] .= '
            .morph-button > button,
            .morph-button-overlay .morph-content {
                background-color: #'.$params['colors']['color2'].';
            }';
    }

    private function getBaseAfterJs(&$params = null)
    {
        echo $this->container->get('templating')->render('MarketSelectionBundle::js/market_js.html.twig');
    }

    private function getClassifiedConfigurationBeforeSetupItemQuery(&$params = null)
    {
        $request = $this->container->get('request_stack')->getMasterRequest();
        $cookies = $request->cookies;

        if (
            ($cookies->has('market_location_level') && $level = $cookies->get('market_location_level')) &&
            ($cookies->has('market_location_id') && $id = $cookies->get('market_location_id'))
        ) {
            $params['filter']->addMust(SearchEngine::getElasticaQueryBuilder()->filter()->term()->setTerm('locationId',
                sprintf('L%d:%d', (int)$level, (int)$id)));
        }
    }

    private function getClassifiedConfigurationBeforeSetupFeaturedQuery(&$params = null)
    {
        $request = $this->container->get('request_stack')->getMasterRequest();
        $cookies = $request->cookies;

        if (
            ($cookies->has('market_location_level') && $level = $cookies->get('market_location_level')) &&
            ($cookies->has('market_location_id') && $id = $cookies->get('market_location_id'))
        ) {
            $params['filter']->addMust(SearchEngine::getElasticaQueryBuilder()->filter()->term()->setTerm('locationId',
                sprintf('L%d:%d', (int)$level, (int)$id)));
        }
    }

    private function getClassifiedConfigurationBeforeSetupBestofQuery(&$params = null)
    {
        $request = $this->container->get('request_stack')->getMasterRequest();
        $cookies = $request->cookies;

        if (
            ($cookies->has('market_location_level') && $level = $cookies->get('market_location_level')) &&
            ($cookies->has('market_location_id') && $id = $cookies->get('market_location_id'))
        ) {
            $params['filter']->addMust(SearchEngine::getElasticaQueryBuilder()->filter()->term()->setTerm('locationId',
                sprintf('L%d:%d', (int)$level, (int)$id)));
        }
    }

    private function getClassifiedConfigurationBeforeSetupRecentQuery(&$params = null)
    {
        $request = $this->container->get('request_stack')->getMasterRequest();
        $cookies = $request->cookies;

        if (
            ($cookies->has('market_location_level') && $level = $cookies->get('market_location_level')) &&
            ($cookies->has('market_location_id') && $id = $cookies->get('market_location_id'))
        ) {
            $params['filter']->addMust(SearchEngine::getElasticaQueryBuilder()->filter()->term()->setTerm('locationId',
                sprintf('L%d:%d', (int)$level, (int)$id)));
        }
    }

    private function getDealConfigurationBeforeSetupItemQuery(&$params = null)
    {
        $request = $this->container->get('request_stack')->getMasterRequest();
        $cookies = $request->cookies;

        if (
            ($cookies->has('market_location_level') && $level = $cookies->get('market_location_level')) &&
            ($cookies->has('market_location_id') && $id = $cookies->get('market_location_id'))
        ) {

            $params['filter'][] = SearchEngine::getElasticaQueryBuilder()->filter()->term()->setTerm('locationId',
                sprintf('L%d:%d', (int)$level, (int)$id));

        }
    }

    private function getDealConfigurationBeforeSetupPopularQuery(&$params = null)
    {
        $request = $this->container->get('request_stack')->getMasterRequest();
        $cookies = $request->cookies;

        if (
            ($cookies->has('market_location_level') && $level = $cookies->get('market_location_level')) &&
            ($cookies->has('market_location_id') && $id = $cookies->get('market_location_id'))
        ) {
            $params['filter']->addMust(SearchEngine::getElasticaQueryBuilder()->filter()->term()->setTerm('locationId',
                sprintf('L%d:%d', (int)$level, (int)$id)));
        }
    }

    private function getDealConfigurationBeforeSetupRecentQuery(&$params = null)
    {
        $request = $this->container->get('request_stack')->getMasterRequest();
        $cookies = $request->cookies;

        if (
            ($cookies->has('market_location_level') && $level = $cookies->get('market_location_level')) &&
            ($cookies->has('market_location_id') && $id = $cookies->get('market_location_id'))
        ) {
            $params['filter']->addMust(SearchEngine::getElasticaQueryBuilder()->filter()->term()->setTerm('locationId',
                sprintf('L%d:%d', (int)$level, (int)$id)));
        }
    }

    private function getEventConfigurationBeforeSetupFeaturedQuery(&$params = null)
    {
        $request = $this->container->get('request_stack')->getMasterRequest();
        $cookies = $request->cookies;

        if (
            ($cookies->has('market_location_level') && $level = $cookies->get('market_location_level')) &&
            ($cookies->has('market_location_id') && $id = $cookies->get('market_location_id'))
        ) {
            $params['filter']->addMust(SearchEngine::getElasticaQueryBuilder()->filter()->term()->setTerm('locationId',
                sprintf('L%d:%d', (int)$level, (int)$id)));
        }
    }

    private function getEventConfigurationBeforeSetupPopularQuery(&$params = null)
    {
        $request = $this->container->get('request_stack')->getMasterRequest();
        $cookies = $request->cookies;

        if (
            ($cookies->has('market_location_level') && $level = $cookies->get('market_location_level')) &&
            ($cookies->has('market_location_id') && $id = $cookies->get('market_location_id'))
        ) {
            $params['filter']->addMust(SearchEngine::getElasticaQueryBuilder()->filter()->term()->setTerm('locationId',
                sprintf('L%d:%d', (int)$level, (int)$id)));
        }
    }

    private function getEventConfigurationBeforeSetupFilterQuery(&$params = null)
    {
        $request = $this->container->get('request_stack')->getMasterRequest();
        $cookies = $request->cookies;

        if (
            ($cookies->has('market_location_level') && $level = $cookies->get('market_location_level')) &&
            ($cookies->has('market_location_id') && $id = $cookies->get('market_location_id'))
        ) {
            $params['filter']->addMust(SearchEngine::getElasticaQueryBuilder()->filter()->term()->setTerm('locationId',
                sprintf('L%d:%d', (int)$level, (int)$id)));
        }
    }

    private function getEventBeforeSetupDateAggregationQuery(&$params = null)
    {
        $request = $this->container->get('request_stack')->getMasterRequest();
        $cookies = $request->cookies;

        if (
            ($cookies->has('market_location_level') && $level = $cookies->get('market_location_level')) &&
            ($cookies->has('market_location_id') && $id = $cookies->get('market_location_id'))
        ) {
            $params['query']->addMust(
                SearchEngine::getElasticaQueryBuilder()->query()->bool()
                    ->addFilter((new Term())->setTerm('locationId', sprintf('L%d:%d', (int)$level, (int)$id)))
            );
        }
    }

    private function getListingConfigurationBeforeSetupFeaturedQuery(&$params = null)
    {
        $request = $this->container->get('request_stack')->getMasterRequest();
        $cookies = $request->cookies;

        if (
            ($cookies->has('market_location_level') && $level = $cookies->get('market_location_level')) &&
            ($cookies->has('market_location_id') && $id = $cookies->get('market_location_id'))
        ) {
            $params['filter']->addMust(SearchEngine::getElasticaQueryBuilder()->filter()->term()->setTerm('locationId',
                sprintf('L%d:%d', (int)$level, (int)$id)));
        }
    }

    private function getListingConfigurationBeforeSetupItemQuery(&$params = null)
    {
        $request = $this->container->get('request_stack')->getMasterRequest();
        $cookies = $request->cookies;

        if (
            ($cookies->has('market_location_level') && $level = $cookies->get('market_location_level')) &&
            ($cookies->has('market_location_id') && $id = $cookies->get('market_location_id'))
        ) {
            $params['filter']->addMust(SearchEngine::getElasticaQueryBuilder()->filter()->term()->setTerm('locationId',
                sprintf('L%d:%d', (int)$level, (int)$id)));
        }
    }

    private function getListingConfigurationBeforeSetupBestofQuery(&$params = null)
    {
        $request = $this->container->get('request_stack')->getMasterRequest();
        $cookies = $request->cookies;

        if (
            ($cookies->has('market_location_level') && $level = $cookies->get('market_location_level')) &&
            ($cookies->has('market_location_id') && $id = $cookies->get('market_location_id'))
        ) {
            $params['filter']->addMust(SearchEngine::getElasticaQueryBuilder()->filter()->term([
                'locationId' => sprintf('L%d:%d', (int)$level, (int)$id),
            ]));
        }
    }

    private function getListingConfigurationBeforeSetupRecurringQuery(&$params = null)
    {
        $request = $this->container->get('request_stack')->getMasterRequest();
        $cookies = $request->cookies;

        if (
            ($cookies->has('market_location_level') && $level = $cookies->get('market_location_level')) &&
            ($cookies->has('market_location_id') && $id = $cookies->get('market_location_id'))
        ) {
            $params['filter']->addMust(SearchEngine::getElasticaQueryBuilder()->filter()->term()->setTerm('locationId',
                sprintf('L%d:%d', (int)$level, (int)$id)));
        }
    }

    private function getSearchBeforeAfterSearchParams(&$params = null)
    {
        if ($this->container->get('search.parameters')->hasCategories()) {
            $params['where'] = null;
        }
    }

    private function getSearchAfterSearchPagination(&$params = null)
    {
        if ($params['pagination']->getTotalItemCount() < 1) {
            $clearWhere = new Response();
            $clearWhere->headers->setCookie(new Cookie('edirectory_searchQuery_where_internal', ''));
            $clearWhere->sendHeaders();
        }
    }

    private function getCategoryRepositoryAfterSetupCountCategoryQuery(&$params = null)
    {
        $request = $this->container->get('request_stack')->getMasterRequest();
        $cookies = $request->cookies;

        if (
            ($cookies->has('market_location_level') && $level = $cookies->get('market_location_level')) &&
            ($cookies->has('market_location_id') && $id = $cookies->get('market_location_id'))
        ) {
            $boolQuery = $params['query']->getQuery();

            if (method_exists($boolQuery, 'addMust')) {
                $boolQuery->addMust(SearchEngine::getElasticaQueryBuilder()->query()->term([
                    'locationId' => sprintf('L%d:%d', (int)$level, (int)$id),
                ]));
            }
        }
    }

    private function getSearchEngineAfterSetupLocationByModuleQuery(&$params = null)
    {
        $request = $this->container->get('request_stack')->getMasterRequest();
        $cookies = $request->cookies;

        if (
            ($cookies->has('market_location_level') && $level = $cookies->get('market_location_level')) &&
            ($cookies->has('market_location_id') && $id = $cookies->get('market_location_id'))
        ) {
            $Query = $params['query']->getQuery();

            $Query->setTerm('locationId', sprintf('L%d:%d', (int)$level, (int)$id));
        }
    }

    // Todo: methods to hooks revision names
    private function getFormLocWettingsMarket(&$params = null)
    {
        echo $this->container->get('templating')->render('MarketSelectionBundle::sitemgr-locationsettings-market.html.twig');
    }

    private function getFormLocSettingsCheckbox(&$params = null)
    {
        $showMarket = $this->container->get('settings')->getDomainSetting('show_market');

        echo $this->container->get('templating')->render('MarketSelectionBundle::sitemgr-location-checkboxmarket.html.twig',
            [
                'show_market' => $showMarket,
                'i'           => $params['i'],
            ]);
    }

    private function getSearchMarket(&$params = null)
    {
        $getCurrentWhereMarket = $this->container->get('search.parameters')->getLocations();
        $getCurrentWhereMarket = array_pop($getCurrentWhereMarket);

        $cookies = $this->container->get('request_stack')->getCurrentRequest()->cookies;
        if (
            ($cookies->has('market_location_level') && $level = $cookies->get('market_location_level')) &&
            ($cookies->has('market_location_id') && $id = $cookies->get('market_location_id'))
        ) {
            $locationid = sprintf('L%d:%d', (int)$level, (int)$id);
            $marketlocation = $this->get('search.engine')->locationIdSearch($locationid);
        }

        if (!empty($getCurrentWhereMarket) && $cookies->has('force_market_location') && $force = $cookies->get('force_market_location')) {

            $curUrl = $this->container->get('request')->getSchemeAndHttpHost();
            $routeUri = $this->container->get('request_stack')->getCurrentRequest()->getPathInfo();

            if ($force === 'clean') {
                $forceLocation = new Response();
                $forceLocation->headers->setCookie(new Cookie('force_market_location', 'false'));
                $forceLocation->sendHeaders();

                header('Location: '.str_ireplace('/'.$getCurrentWhereMarket->getFriendlyUrl(), '',
                        $curUrl.$routeUri));
                exit;
            }

            if ($force === 'true') {
                $forceLocation = new Response();
                $forceLocation->headers->setCookie(new Cookie('force_market_location', 'false'));
                $forceLocation->sendHeaders();

                header('Location: '.str_ireplace('/'.$getCurrentWhereMarket->getFriendlyUrl(),
                        '/'.$marketlocation[$locationid]->getFriendlyUrl(), $curUrl.$routeUri));
                exit;
            }
        }

        $forceLocation = new Response();
        $forceLocation->headers->setCookie(new Cookie('force_market_location', 'false'));
        $forceLocation->sendHeaders();
    }

    private function getMarketBox(&$params = null)
    {
        $this->container->get('javascripthandler')->addJSExternalFile('/bundles/marketselection/js/market.js');

        echo $this->container->get('templating')->render('MarketSelectionBundle::market_twigextension.html.twig');
    }

    private function getCalendarControllerBeforeSetupCalendarQuery(&$params = null)
    {
        $request = $this->container->get('request_stack')->getMasterRequest();
        $cookies = $request->cookies;

        if (
            ($cookies->has('market_location_level') && $level = $cookies->get('market_location_level')) &&
            ($cookies->has('market_location_id') && $id = $cookies->get('market_location_id'))
        ) {
            $params['query']->addMust(SearchEngine::getElasticaQueryBuilder()->filter()->term()->setTerm('locationId',
                sprintf('L%d:%d', (int)$level, (int)$id)));
        }
    }
}
