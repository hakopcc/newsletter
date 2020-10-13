<?php
    /*
    * # Admin Panel for eDirectory
    * @copyright Copyright 2018 Arca Solutions, Inc.
    * @author Basecode - Arca Solutions, Inc.
    */

    # ----------------------------------------------------------------------------------------------------
    # * FILE: /includes/lists/list-categories.php
    # ----------------------------------------------------------------------------------------------------

    if ($table_category == 'ListingCategory') {
        $maxLevelCat = LISTING_CATEGORY_LEVEL_AMOUNT;
    } else {
        $maxLevelCat = CATEGORY_LEVEL_AMOUNT;
    }
?>

    <div class="list-content" id="manageCategory">
        <div class="categories-list" id="categoryContainer">
            <?
            // ModStores Hooks
            HookFire( 'listcategory_before_load_category', [
                'table_category' => &$table_category,
            ]);

            foreach ($categories as $category) { ?>
                <div class="categories-item">
                    <?php $categoryObj = new $table_category($category);
                        $subcategories = db_getFromDB(strtolower($table_category), 'category_id', $categoryObj->getNumber('id'), 'all', 'title', 'object', SELECTED_DOMAIN_ID, false, 'id, `title`');

                        $category['isLastChild'] = empty($subcategories);

                        try {
                            echo $container->get('templating')->render('@Web/category-tree.html.twig', [
                                'categoryLevel' => 0,
                                'category' => $category,
                                'selectParent' => false,
                                'onlyParents' => false,
                                'manageCategories' => true
                            ]);
                        } catch (Twig_Error $e) {
                        }
                    ?>
                </div>
            <?php } ?>
        </div>
    </div>
