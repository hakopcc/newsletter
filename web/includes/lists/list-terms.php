<?php
    /*
    * # Admin Panel for eDirectory
    * @copyright Copyright 2020 Arca Solutions, Inc.
    * @author Basecode - Arca Solutions, Inc.
    */

    # ----------------------------------------------------------------------------------------------------
    # * FILE: /includes/lists/list-terms.php
    # ----------------------------------------------------------------------------------------------------
?>

<section>
    <form name="item_list" role="form">
        <ul class="list-content-item list">
            <?php if ($nearbyTerms) {
                $cont = 0;
                foreach ($nearbyTerms as $term) {
                    $cont++;
                    $previewTerm[$cont]["id"] = $term->getNumber("id");
                    $previewTerm[$cont]["token"] = $term->getString("token");
                    $previewTerm[$cont]["radius"] = $term->getNumber("radius");
                    $previewTerm[$cont]["latitude"] = $term->getNumber("latitude");
                    $previewTerm[$cont]["longitude"] = $term->getNumber("longitude");
                ?>
                <li class="content-item" data-id="<?= $term->getNumber("id")?>" onclick="loadTermsMap(<?= $term->getNumber("id")?>)">
                    <div class="check-bulk">
                        <input type="checkbox" id="<?=$manageModule?>_id<?=$cont?>" name="item_check[]" value="<?=$term->getNumber("id")?>" onclick="bulkSelect('<?=$manageModule?>');"/>
                    </div>

                    <div class="item">
                        <h3 class="item-title"><?= $term->getString("token") ?></h3>
                    </div>
                </li>
            <? }
            } ?>
        </ul>
    </form>
</section>
