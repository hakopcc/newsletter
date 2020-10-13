<?

/*==================================================================*\
######################################################################
#                                                                    #
# Copyright 2018 Arca Solutions, Inc. All Rights Reserved.           #
#                                                                    #
# This file may not be redistributed in whole or part.               #
# eDirectory is licensed on a per-domain basis.                      #
#                                                                    #
# ---------------- eDirectory IS NOT FREE SOFTWARE ----------------- #
#                                                                    #
# http://www.edirectory.com | http://www.edirectory.com/license.html #
######################################################################
\*==================================================================*/

# ----------------------------------------------------------------------------------------------------
# * FILE: /includes/code/CategoryActionAjax.php
# ----------------------------------------------------------------------------------------------------

# ----------------------------------------------------------------------------------------------------
# LOAD CONFIG
# ----------------------------------------------------------------------------------------------------
use ArcaSolutions\WebBundle\Services\BaseCategoryService;

if (isset($_POST["domain_id"]) && is_numeric($_POST["domain_id"])) {
    define("SELECTED_DOMAIN_ID", $_POST["domain_id"]);
} else if (isset($_GET["domain_id"]) && is_numeric($_GET["domain_id"])) {
    define("SELECTED_DOMAIN_ID", $_GET["domain_id"]);
}
$loadSitemgrLangs = true;
include '../../conf/loadconfig.inc.php';

# ----------------------------------------------------------------------------------------------------
# SESSION
# ----------------------------------------------------------------------------------------------------
sess_validateSMSession();

# ----------------------------------------------------------------------------------------------------
# CODE
# ----------------------------------------------------------------------------------------------------
$container = SymfonyCore::getContainer();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    /** @var BaseCategoryService $moduleCategoryService */
    $moduleCategoryService = $container->get($_POST['module']. '.category.service');

    if ($_POST['action'] === 'ajax') {
        $response = [
            'status' => false,
        ];

        /* Category image removal */
        if ($_POST['type'] === 'removeImage') {
            try{
                $responseStatus = true;
                if (!empty($_POST['id'])) {
                    $imageId = null;
                    /* ModStores Hooks */
                    if (!HookFire('legacy_categoryactionajaxcode_ovewrite_removeimagetype', array(
                        'response_array' => &$response,
                        'response_status' => &$responseStatus,
                        'image_id' => &$imageId,
                        'http_post_array' => &$_POST,
                        'http_get_array' => &$_GET
                    ))) {
                        $categModule = ucfirst($_POST['module']) . 'Category';
                        $category = new $categModule($_POST['id']);
                        $imageId = $category->getNumber('image_id');

                        $category->setString('image_id', 'NULL');

                        $category->Save();
                    }

                    if (!empty($imageId)) {
                        $image = new Image($imageId);
                        $image->getNumber('id') and $image->delete();
                    }
                }
                $response['status'] = $responseStatus;
            } catch (Exception $e) {
                $response['status'] = false;
                $response['exception'] = true;
                $response['exceptionMessage'] = $e->getMessage();
                $response['exceptionStackTrace'] = $e->getTraceAsString();
            }
        }

        /* Category icon removal */
        if ($_POST['type'] === 'removeIcon') {
            try{
                $responseStatus = true;
                if (!empty($_POST['id'])) {
                    $iconId = null;
                    /* ModStores Hooks */
                    if (!HookFire('legacy_categoryactionajaxcode_ovewrite_removeicontype', array(
                        'response_array' => &$response,
                        'response_status' => &$responseStatus,
                        'icon_id' => &$iconId,
                        'http_post_array' => &$_POST,
                        'http_get_array' => &$_GET
                    ))) {

                        $categModule = ucfirst($_POST['module']) . 'Category';
                        $category = new $categModule($_POST['id']);
                        $iconId = $category->getNumber('icon_id');

                        $category->setString('icon_id', 'NULL');

                        $category->Save();
                    }

                    if (!empty($iconId)) {
                        $image = new Image($iconId);
                        $image->getNumber('id') and $image->delete();
                    }
                }
                $response['status'] = $responseStatus;
            } catch (Exception $e) {
                $response['status'] = false;
                $response['exception'] = true;
                $response['exceptionMessage'] = $e->getMessage();
                $response['exceptionStackTrace'] = $e->getTraceAsString();
            }
        }

        /* ModStores Hooks */
        HookFire('categorycode_after_remove_image', [
            'id'       => &$id,
            'response' => &$response
        ]);

        echo json_encode($response);
        exit();
    }

    if ($_POST['action'] === 'save') {

        if ($_POST['title']) {
            $_POST['title'] = trim($_POST['title']);
            $_POST['title'] = preg_replace('/\s\s+/', ' ', $_POST['title']);
        }

        if ($_POST['seo_description']) {
            $_POST['seo_description'] = str_replace('"', '', $_POST['seo_description']);
        }
        if ($_POST['seo_keywords']) {
            $_POST['seo_keywords'] = str_replace('"', '', $_POST['seo_keywords']);
        }

        if (validate_form('category', $_POST, $message_category)) {

            /* @var ListingCategory $category */

            $obj = ucfirst($_POST['module'])."Category";

            /* ModStores Hooks */
            HookFire( 'categorycode_before_initialize_objectonformvalidate', [
                'obj' => &$obj
            ]);

            $category = new $obj($_POST['id']);


            //Saving category
            $_POST['featured'] = ($_POST['featured'] === 'on' ? 'y' : 'n');
            $_POST['enabled'] = ($_POST['clickToDisable'] === 'on' ? 'n' : 'y');
            $category->makeFromRow($_POST);
            if (string_strlen(trim($_POST['keywords'])) ==  0) {
                $category->setString('keywords', '');
            }

            mixpanel_trackFirstItem(ucfirst($_POST['module'])."Category");

            $category->Save();

            $moduleScalabilityConstantName = string_strtoupper(str_replace('Category','', ucfirst($_POST['module'])."Category")) .'_SCALABILITY_OPTIMIZATION';
            $moduleScalability = (defined($moduleScalabilityConstantName) ? constant($moduleScalabilityConstantName) : 'off');

            /* ModStores Hooks */
            HookFire('categorycode_after_save', [
                'category' => &$category,
            ]);

            //Updating items fulltext fields
            if ($moduleScalability !== 'on') {
                $category->updateFullTextItems([]);
            }
            if($_POST["id"] != ""){
                $message_category = $container->get('translator')->trans('Category successfully updated.',[],'administrator');
            }else{
                $message_category = $container->get('translator')->trans('Category successfully created.',[],'administrator');
            }
            $response = [
                'status'  => true,
                'message' => $message_category,
                'name'    => $category->title,
                'id'      => $category->id
            ];
        }else{
            $response = [
                'status' => false,
                'message' => $message_category
            ];
        }
        echo json_encode($response);
        exit();

    }

    if($_POST['action'] === 'delete') {
        $responseStringValue = '';
        $responseErrorArray = array(
            'exception' => true,
            'exceptionMessage' => 'Category cannot be deleted due to an unexpected error',
            'exceptionStackTrace' => ''
        );
        $responseSuccessArray = array(
            'status' => false,
            'message' => 'Category successfully deleted.',
        );
        try {
            try {
                $responseSuccessArray['message'] = $container->get('translator')->trans('Category successfully deleted.', [], 'administrator');
            } catch (Exception $translationException){
                //DO NOTHING. If a translation error occurs, will be considered the english version.
                //TODO: Usage of logger service to log translation error here
            }
            /* ModStores Hooks */
            if(!HookFire( 'legacy_categoryactionajaxcode_ovewrite_deleteaction', array(
                'response_error_array_model' => $responseErrorArray,
                'response_success_array_model' => $responseSuccessArray,
                'response_string_value' => &$responseStringValue,
                'http_post_array' => &$_POST,
                'http_get_array' => &$_GET
            ))){
                $obj = ucfirst($_POST['module']) . "Category";
                $category = new $obj($_POST['id']);
                $category->delete();
                $responseStringValue = json_encode($responseSuccessArray);
            }
        } catch (Exception $e) {
            $responseErrorArray['exceptionMessage'] = $e->getMessage();
            $responseErrorArray['exceptionStackTrace'] = $e->getTraceAsString();
            $responseStringValue = json_encode($responseErrorArray);
        }
        echo $responseStringValue;
        exit();
    }

    if($_POST['actionType'] === 'upload') {
        include INCLUDES_DIR.'/code/coverimage.php';
        exit();
    }
}
