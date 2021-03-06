<?php
/*
* # Admin Panel for eDirectory
* @copyright Copyright 2018 Arca Solutions, Inc.
* @author Basecode - Arca Solutions, Inc.
*/

# ----------------------------------------------------------------------------------------------------
# * FILE: /sitemgr/ckeditor.php
# ----------------------------------------------------------------------------------------------------

# ----------------------------------------------------------------------------------------------------
# LOAD CONFIG
# ----------------------------------------------------------------------------------------------------

include("conf/loadconfig.inc.php");

# ----------------------------------------------------------------------------------------------------
# UPLOAD IMAGES
# ----------------------------------------------------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] == "POST" && (sess_isAccountLogged() || sess_isSitemgrLogged())) {
    if ($_POST['ckCsrfToken'] && $file = $_FILES['upload']) {
        if(!array_key_exists('uploadType', $_GET) || empty($_GET['uploadType']) || $_GET['uploadType'] !== 'image'){
            echo '<script type="text/javascript">window.parent.CKEDITOR.tools.callFunction(' . $_GET['CKEditorFuncNum'] . ', "", "Unsupported file upload.");</script>';
            return;
        }
        try {
            /* Setting Image name */
            $fileName = (sess_getAccountIdFromSession() ?: 'sitemgr') . '_' . $file['name'];

            /* Creating File Object */
            $file = new Symfony\Component\HttpFoundation\File\UploadedFile($file['tmp_name'], $fileName);

            /* Calling Symfony Image Upload */
            $imageHandler = SymfonyCore::getContainer()->get('imageuploader');

            /* Setting Header to Response */
            $upload = $imageHandler->uploadImageCkeditor($file, $fileName, SELECTED_DOMAIN_ID);
            echo "<script type='text/javascript'>window.parent.CKEDITOR.tools.callFunction({$_GET['CKEditorFuncNum']}, '{$upload['url']}', '{$upload['message']}');</script>";
        } catch (Exception $e){
            echo '<script type="text/javascript">window.parent.CKEDITOR.tools.callFunction(' . $_GET['CKEditorFuncNum'] . ', "", "Unexpected error on file upload.");</script>';
        }
    }
}
