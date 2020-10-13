<?php

namespace ArcaSolutions\ModStoresBundle\Plugins\AdvancedSocialMediaEvent\Controller;

use DOMDocument;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class DefaultController extends Controller
{
    /**
     * @return Response
     */
    public function instagramAction(Request $request)
    {
        $phinstagram_json_object = null;
        $username = 'instagram';

        if (isset($_GET['username'])) {
            $username = $_GET['username'];
        }

        define('TMP_DIR', $_SERVER['DOCUMENT_ROOT'].'/custom/tmp');
        define('CACHE_FILE_NAME', $username.'.json');
        define('LOCAL_CACHE_IN_SECONDS', 300);

        if (file_exists(TMP_DIR.'/'.CACHE_FILE_NAME) && (filemtime(TMP_DIR.'/'.CACHE_FILE_NAME) > (time() - LOCAL_CACHE_IN_SECONDS))) {

            $phinstagram_json_object = json_decode(file_get_contents(TMP_DIR.'/'.CACHE_FILE_NAME));

        } else {

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, "https://www.instagram.com/$username/");
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_USERAGENT,
                'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_11_5) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/50.0.2661.102 Safari/537.36');
            $html = curl_exec($ch);
            curl_close($ch);

            $doc = new DOMDocument();

            libxml_use_internal_errors(true);

            $doc->loadHTML($html);

            foreach (explode("\n", $doc->textContent) as $line) {
                if (strpos($line, 'window._sharedData = ') !== false) {
                    $json_string = preg_replace("/^.*window\.\_sharedData \= (\{.*?\});.*$/", '\1', $line);
                    $phinstagram_json_object = json_decode($json_string);
                }

            }

            if ($phinstagram_json_object == null) {

                if (file_exists(TMP_DIR.'/'.CACHE_FILE_NAME)) {
                    $phinstagram_json_object = json_decode(file_get_contents(TMP_DIR.'/'.CACHE_FILE_NAME));
                } else {
                    $phinstagram_json_object = ['error' => json_last_error()];
                }

            } else {

                file_put_contents(TMP_DIR.'/'.CACHE_FILE_NAME, $json_string);

            }
        }

        header('Content-type: application/json');

        echo json_encode($phinstagram_json_object);
        exit;
    }
}
