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
# * FILE: /includes/code/widgetActionAjax.php
# ----------------------------------------------------------------------------------------------------

$loadSitemgrLangs = true;
include '../../conf/loadconfig.inc.php';

# ----------------------------------------------------------------------------------------------------
# LOAD CONFIG
# ----------------------------------------------------------------------------------------------------
use ArcaSolutions\WebBundle\Services\BaseCategoryService;

# ----------------------------------------------------------------------------------------------------
# CODE
# ----------------------------------------------------------------------------------------------------
$container = SymfonyCore::getContainer();

if ($_SERVER['REQUEST_METHOD'] === 'GET') {

    /** @var BaseCategoryService $categoryService */
    $categoryService = $container->get($_GET['module'] . '.category.service');

    if($_GET['action'] === 'load') {
        if (empty($_GET['module'])) {
            echo json_encode([
                'success' => 'false',
                'message' => 'Invalid Module'
            ]);
            exit;
        }

        if (empty($_GET['level'])) {
            $level = 0;
        } else {
            $level = $_GET['level'];
        }

        if (!empty($_GET['listingTemplate'])) {
            $categories = $categoryService->getAllParentCategoriesByListingTemplate($_GET['listingTemplate']);

            if(empty($categories)) {
                if (!empty($_GET['manageCategories'])){
                    $categories = $categoryService->getAllParentCategories();
                }else{
                    $categories = $categoryService->getAllParentCategoriesEnabled();
                }
            }
        } elseif(!empty($_GET['id'])) {
            if (!empty($_GET['manageCategories'])){
                $categories = $categoryService->getAllChildCategoriesById($_GET['id']);
            }else{
                $categories = $categoryService->getAllChildEnabledCategoriesById($_GET['id']);
            }

        } else {
            if (!empty($_GET['manageCategories'])){
                $categories = $categoryService->getAllParentCategories();
            }else{
                $categories = $categoryService->getAllParentCategoriesEnabled();
            }
        }
        $template = null;
        try {
            $categoryService->buildCategoryTree($level, $categories, $template, $_GET['selectParent'], $_GET['onlyParents'], $_GET['manageCategories'], $_GET['categories']);
        } catch (Twig_Error $e) {
        }

        if (!empty($template)) {
            echo json_encode([
                'success' => 'true',
                'template' => $template
            ]);
        } else {
            echo json_encode([
                'success' => 'false'
            ]);
        }
    } elseif ($_GET['action'] === 'search') {
        if (empty($_GET['module'])) {
            echo json_encode([
                'success' => 'false',
                'message' => 'Invalid Module'
            ]);
            exit;
        }

        try {
            $categoryService->buildCategoryTreeByTerm($_GET['term'], $template, (bool)$_GET['selectParent'], $_GET['listingTemplate'], $_GET['categories']);
        } catch (Twig_Error $e) {
        }

        if (!empty($template)) {
            echo json_encode([
                'success' => 'true',
                'template' => $template
            ]);
        } else {
            echo json_encode([
                'success' => 'false'
            ]);
        }
    } elseif ($_GET['action'] === 'retrieve') {
        $retrievedValue = '';
        try {
            $retrievedValue = $categoryService->retrieveSerializedCategory($_GET['id']);
        } catch (Exception $e){
            $retrievedValue = json_encode([
                'exception' => true,
                'exceptionMessage' => $e->getMessage(),
                'exceptionStackTrace' => $e->getTraceAsString()
            ]);
        }
        echo $retrievedValue;
        exit();
    }
}
