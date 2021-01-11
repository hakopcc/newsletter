<?php

////////////////////////////////////////////////////////////////////////////////////////////////////
ini_set("html_errors", false);
ini_set('memory_limit', '-1');
////////////////////////////////////////////////////////////////////////////////////////////////////

////////////////////////////////////////////////////////////////////////////////////////////////////
define("EDIRECTORY_ROOT", __DIR__."/..");
define("SELECTED_DOMAIN_ID", 1);
////////////////////////////////////////////////////////////////////////////////////////////////////

////////////////////////////////////////////////////////////////////////////////////////////////////
$_inCron = true;
include_once("../conf/config.inc.php");
////////////////////////////////////////////////////////////////////////////////////////////////////

////////////////////////////////////////////////////////////////////////////////////////////////////
$_inCron = false;
include_once(EDIRECTORY_ROOT . "/conf/loadconfig.inc.php");

$host = _DIRECTORYDB_HOST;
$db = _DIRECTORYDB_NAME;
$user = _DIRECTORYDB_USER;
$pass = _DIRECTORYDB_PASS;

$link = ($GLOBALS["___mysqli_ston"] = mysqli_connect($host,  $user,  $pass));
mysqli_query( $link, "SET NAMES 'utf8'");
mysqli_query( $link, 'SET character_set_connection=utf8');
mysqli_query( $link, 'SET character_set_client=utf8');
mysqli_query( $link, 'SET character_set_results=utf8');
mysqli_select_db($GLOBALS["___mysqli_ston"], $db);

/********************** Forum Module Insertion **********************/
$container = SymfonyCore::getContainer();
$em = $container->get('doctrine.orm.domain_entity_manager');
$domain = $container->get('multi_domain.information')->getId();

$eventsXMLMedia = simplexml_load_file("events_featured_images.xml");
$eventsXML = simplexml_load_file("events_items.xml");

$eventInsertion = false;
$postInsertion = false;
$listingInsertion = false;

/* Insert Events Itens to the eDirectory */
echo "Inserting Events into eDirectory...\n";
if ($eventInsertion) {
    foreach($eventsXML->channel->item as $item) {
        $content = $item->children('http://purl.org/rss/1.0/modules/content/');
        $wp = $item->children('http://wordpress.org/export/1.2/');

        $event = new Event();
        $title = (string)$item->title;

        $friendlyUrl = system_generateFriendlyURL($title);
        $sqlFriendlyURL = "SELECT friendly_url FROM Event WHERE friendly_url = " . db_formatString($friendlyUrl) . " LIMIT 1";

        $dbMain = db_getDBObject(DEFAULT_DB, true);
        $dbObjFriendlyURL = db_getDBObjectByDomainID(SELECTED_DOMAIN_ID, $dbMain);
        $resultFriendlyURL = $dbObjFriendlyURL->query($sqlFriendlyURL);
        if (mysqli_num_rows($resultFriendlyURL) > 0) {
            $friendlyUrl = $friendlyUrl . FRIENDLYURL_SEPARATOR . uniqid();
        }

        $enteredTime = strtotime((string)$wp->post_date);
        $entered = date('d/m/Y H:i:s', $enteredTime);

        // Set post meta variables
        $startDate = '';
        $startTime = '';
        $endDate = '';
        $endTime = '';
        $thumbnailId = '';
        $recurring = 'N';
        $recurringPeriod = '';
        $repeat = 'N';
        $eventLink = '';
        $eventPhone = '';
        $eventLocation = '';
        $eventContact = '';
        $fees = '';
        $week_days = '';
        $month = '';
        $postmeta = $wp->postmeta;
        foreach ($postmeta as $meta) {
            $key = (string)$meta->meta_key;
            $value = (string)$meta->meta_value;

            if ($key == 'start_date' && !empty($value)) {
                $dateVal = strtotime($value);
                $startDate = date('d/m/Y', $dateVal);
            } else if ($key == 'start_time' && !empty($value)) {
                $dateVal = strtotime($value);
                $startTime = date('H:i:s', $dateVal);
            } else if ($key == 'end_date') {
                if (!empty($value)) {
                    $dateVal = strtotime($value);
                    $endDate = date('d/m/Y', $dateVal);
                } else {
                    $endDate = '00/00/0000';
                    $repeat = 'Y';
                }
            } else if ($key == 'end_time' && !empty($value)) {
                $dateVal = strtotime($value);
                $endTime = date('H:i:s', $dateVal);
            } else if ($key == 'recurring_options') {
                $recurring = 'Y';
                $recurringPeriod = strtolower($value);
            } else if ($key == 'event_link') {
                $eventLink = $value;
            } else if ($key == 'event_phone') {
                $eventPhone = $value;
            } else if ($key == 'event_location_name') {
                $eventLocation = $value;
            } else if ($key == 'artist') {
                $eventContact = $value;
            } else if ($key == 'fees') {
                $fees = $value;
            } else if ($key == 'month_days' && !empty($value)) {
                $month = $value;
            } else if ($key == 'week_days' && !empty($value)) {
                $week_days = $value;
            } else if ($key == 'thumbnail' && !empty($value)) {
                $thumbnailId = $value;
            }
        }

        $event->setString('title', $title);
        $event->setString('friendly_url', $friendlyUrl);
        $event->setString('description', (string)$item->description) . "\n" . $fees;
        $event->setString('long_description', (string)$content->encoded);
        $event->setDate('entered', $entered);
        $event->setDate('start_date', $startDate);
        $event->setString('start_time', $startTime);
        $event->setDate('end_date', $endDate);
        $event->setString('end_time', $endTime);
        $event->setString('url', $eventLink);
        $event->setString('phone', $eventPhone);
        $event->setString('address', $eventLocation);
        $event->setString('contact_name', $eventContact);
        $event->setString('recurring', $recurring);
        $event->setString('repeat_event', $repeat);
        $event->setNumber('wp_event_id', (integer)$wp->post_id);
        $event->setString('status', 'A');

        $event->save();
        $container->get('event.synchronization')->addUpsert($event->getNumber('id'));
        echo "Event Added: " . $title . ", ID: " . $event->getNumber('id') . "\n";
    }
    echo "************ All Events has been Added! ************ \n\n";

    /* Insert Events Images and added the relation with the Event */
    echo "Adding Events Media...\n";
    foreach($eventsXMLMedia->channel->item as $item) {
        $wp = $item->children('http://wordpress.org/export/1.2/');
        $eventId = (integer)$wp->post_parent;
        $imageUrl = (string)$wp->attachment_url;

        // Check if the event has been added
        $event = $container->get('doctrine')
            ->getRepository('EventBundle:Event')
            ->findOneBy(['wpEventId' => $eventId]);

        if (is_null($event)) {
            continue;
        }

        $gallery = new Gallery();
        $gallery->setString('account_id', 0);
        $gallery->setString('title', $event->getTitle());
        $gallery->setDate('entered', date("Y-m-d H:i:s"));
        $gallery->setDate('update', date("Y-m-d H:i:s"));

        $gallery->save();

        $galleryItem = new \ArcaSolutions\ImageBundle\Entity\GalleryItem();
        $galleryItem->setGalleryId($gallery->getNumber('id'));
        $galleryItem->setItemId($event->getId());
        $galleryItem->setItemType('event');

        $em->persist($galleryItem);
        $em->flush();

        // Validate image URL, if it exists, saves it
        $imageExist = @fopen($imageUrl, 'r');
        if (!$imageExist) {
            continue;
        }

        $image = file_get_contents($imageUrl);

        if (!$image) {
            continue;
        }

        $imageData = getimagesizefromstring($image)['mime'];
        if (!$imageData) {
            continue;
        }

        $extension = explode("image/", $imageData);

        // If count extension == 1, the file isn't an image
        if (count($extension) == 1) {
            continue;
        }

        if ($extension[1] == 'jpeg') {
            $extension[1] = 'jpg';
        }

        $imageExtension = '.'.$extension[1];
        $fileName = 'img_event_'.$event->getId().$imageExtension;
        $file = '../custom/domain_'.$domain.'/extra_files/'.$fileName;

        // Saves Image
        file_put_contents($file, $image);

        // Insert Image on DB
        if(file_exists($file)) {
            list($width, $height, $type, $attr) = getimagesize($file);

            $prefix = "sitemgr_";

            $imageObj = new \ArcaSolutions\ImageBundle\Entity\Image();
            $imageObj->setWidth($width);
            $imageObj->setHeight($height);
            $imageObj->setType(strtoupper(str_replace(".", "", $imageExtension)));
            $imageObj->setPrefix($prefix);

            $em->persist($imageObj);
            $em->flush();

            $thumbObj = new \ArcaSolutions\ImageBundle\Entity\Image();
            $thumbObj->setWidth($width);
            $thumbObj->setHeight($height);
            $thumbObj->setType(strtoupper(str_replace(".", "", $imageExtension)));
            $thumbObj->setPrefix($prefix);

            $em->persist($thumbObj);
            $em->flush();

            $event->setImage($imageObj);
            $event->setImageId($imageObj->getId());

            $em->persist($event);
            $em->flush();

            // Move Images to the right folder
            $newFile = '../custom/domain_' . $domain . '/image_files/' . $imageObj->getPrefix() . 'photo_' . $imageObj->getId() . $imageExtension;
            copy($file, $newFile);
            $newThumb = '../custom/domain_' . $domain . '/image_files/' . $imageObj->getPrefix() . 'photo_' . $thumbObj->getId() . $imageExtension;
            rename($file, $newThumb);

            // Creates a new Gallery Image
            $galleryImage = new \ArcaSolutions\ImageBundle\Entity\GalleryImage();
            $galleryImage->setGalleryId($gallery->getNumber('id'));
            $galleryImage->setImageId($imageObj->getId());
            $galleryImage->setImage($imageObj);
            $galleryImage->setImageCaption($fileName);
            $galleryImage->setImageDefault('y');
            $galleryImage->setOrder(1);

            $em->persist($galleryImage);
            $em->flush();

            $container->get('event.synchronization')->addUpsert($event->getId());

            print_r("Event " . $event->getTitle() . " ID: " . $event->getId() . " Updated!\n");
        }
    }
    echo "************ All Events Media has been Added! ************ \n\n";
}
/****************** End Of Forum Module Insertion ******************/

/********************** Blog Module Insertion **********************/
/* Columns:
* 1- Title
* 2- Content
* 3- Categories (Separated by ', ')
* 4- Tags
* 5- Date
* 6- SEO Score
* 7- Readability Score
* 8- Guest Author
* 9- Guest ID
*/
if ($postInsertion) {
    echo "Inserting Posts into eDirectory...\n";
    $arrayTranslator = [
        1 => 'title',
        2 => 'content',
        4 => 'date',
        7 => 'image_url',
        24 => 'categories',
        30 => 'authorFirstName',
        31 => 'authorLastName',
        32 => 'friendlyUrl',
    ];
    $row = 0;
    // i <= 11
    for ($i = 1; $i <= 11; $i ++) {
        echo "Starting importation of the File " . $i . "\n\n";
        if (($handle = fopen("post_items_" . $i . ".csv", "r")) !== FALSE) {
            while (($data = fgetcsv($handle, 10002, ",")) !== FALSE) {
                // Skip Header Values
                if ($row == 0 || $row == 1) {
                    $row++;
                    continue;
                }
                echo "Row: " . $row . "\n";
                // $num = Number of Columns existent
                $num = count($data);
                $row++;

                $postArray = null;
                foreach ($arrayTranslator as $key => $value) {
                    $postArray[$value] = $data[$key];
                }

                $postExistentObj = $container->get('doctrine')
                    ->getRepository('BlogBundle:Post')
                    ->findOneBy(['friendlyUrl' => $postArray['friendlyUrl']]);

                if (!empty($postExistentObj)) {
                    echo "Post Already Added: " . $postExistentObj->getTitle() . ", Jumping to the next.\n";
                    unset($postExistentObj);
                    unset($postArray);
                    continue;
                }

                // Set Post Content
                $post = new Post();

                $post->setString('title', $postArray['title']);
                $post->setString('content', $postArray['content']);
                $post->setString('friendly_url', $postArray['friendlyUrl']);
                $post->setString('author', $postArray['authorFirstName'] . ' ' . $postArray['authorLastName']);
                $post->setDate('entered', $postArray['date']);

                // Categories = Categories IDS
                $categoriesIds = null;
                $categoriesArray = explode(',', $postArray['categories']);
                if (!empty($postArray['categories'])) {
                    foreach ($categoriesArray as $categoryName) {
                        $categ = trim($categoryName);
                        // Has Parent Category
                        if (strpos('>', $categ) !== false) {
                            $parents = explode(">", $categ);
                            $lastCategoryId = '';
                            foreach($parents as $parent) {
                                $category = $container->get('doctrine')
                                    ->getRepository('BlogBundle:Blogcategory')
                                    ->findOneBy(['title' => $categ]);

                                if (is_null($category)) {
                                    $categUrl = system_generateFriendlyURL($parent);

                                    $blogCategory = new \ArcaSolutions\BlogBundle\Entity\Blogcategory();
                                    $blogCategory->setTitle($parent);
                                    $blogCategory->setEnabled('y');
                                    $blogCategory->setFeatured('y');
                                    $blogCategory->setFriendlyUrl($categUrl);

                                    $em->persist($blogCategory);
                                    $em->flush();

                                    $lastCategoryId = $blogCategory->getId();
                                } else {
                                    $lastCategoryId = $category->getId();
                                }
                            }

                            $categoriesIds[] = $lastCategoryId;
                        // Doesn't have a parent category
                        } else {
                            $category = $container->get('doctrine')
                                ->getRepository('BlogBundle:Blogcategory')
                                ->findOneBy(['title' => $categ]);

                            if (is_null($category)) {
                                $categUrl = system_generateFriendlyURL($categ);

                                $blogCategory = new \ArcaSolutions\BlogBundle\Entity\Blogcategory();
                                $blogCategory->setTitle($categ);
                                $blogCategory->setEnabled('y');
                                $blogCategory->setFeatured('y');
                                $blogCategory->setFriendlyUrl($categUrl);

                                $em->persist($blogCategory);
                                $em->flush();

                                $categoriesIds[] = $blogCategory->getId();
                            } else {
                                $categoriesIds[] = $category->getId();
                            }
                        }
                    }
                }

                $post->setCategories($categoriesIds);

                $post->save();
                echo "Post Added: " . $post->getString('title') . " ID: " . $post->getNumber('id') . "\n";

                // Validate image URL, if it exists, saves it
                $imageUrl = $postArray['image_url'];
                $imageExist = @fopen($imageUrl, 'r');
                if (!$imageExist) {
                    unset($post);
                    unset($imageUrl);
                    unset($imageExist);
                    unset($postExistentObj);
                    unset($postArray);
                    continue;
                }

                $image = file_get_contents($imageUrl);

                if (!$image) {
                    unset($post);
                    unset($imageUrl);
                    unset($imageExist);
                    unset($postExistentObj);
                    unset($postArray);
                    unset($image);
                    continue;
                }

                $imageData = getimagesizefromstring($image)['mime'];
                if (!$imageData) {
                    unset($post);
                    unset($imageUrl);
                    unset($imageExist);
                    unset($postExistentObj);
                    unset($postArray);
                    unset($image);
                    unset($imageData);
                    continue;
                }

                $extension = explode("image/", $imageData);

                // If count extension == 1, the file isn't an image
                if (count($extension) == 1) {
                    unset($post);
                    unset($imageUrl);
                    unset($imageExist);
                    unset($postExistentObj);
                    unset($postArray);
                    unset($image);
                    unset($imageData);
                    unset($extension);
                    continue;
                }

                if ($extension[1] == 'jpeg') {
                    $extension[1] = 'jpg';
                }

                $imageExtension = '.'.$extension[1];
                $fileName = 'img_event_'.$post->getNumber('id').$imageExtension;
                $file = '../custom/domain_'.$domain.'/extra_files/'.$fileName;

                // Saves Image
                file_put_contents($file, $image);

                // Insert Image on DB
                if(file_exists($file)) {
                    list($width, $height, $type, $attr) = getimagesize($file);

                    $prefix = "sitemgr_";

                    $imageObj = new \ArcaSolutions\ImageBundle\Entity\Image();
                    $imageObj->setWidth($width);
                    $imageObj->setHeight($height);
                    $imageObj->setType(strtoupper(str_replace(".", "", $imageExtension)));
                    $imageObj->setPrefix($prefix);

                    $em->persist($imageObj);
                    $em->flush();

                    $post->setNumber('image_id', $imageObj->getId());

                    // Move Images to the right folder
                    $newFile = '../custom/domain_' . $domain . '/image_files/' . $imageObj->getPrefix() . 'photo_' . $imageObj->getId() . $imageExtension;
                    copy($file, $newFile);
                }

                $post->save();
                $container->get('blog.synchronization')->addUpsert($post->getNumber('id'));

                echo "Post Image Added!\n";
                unset($post);
                unset($blogCategory);
                unset($postExistentObj);
                unset($image);
                unset($imageObj);
                unset($imageExtension);
                unset($fileName);
                unset($file);
                unset($categoriesIds);
                unset($categoriesArray);
                unset($postArray);
            }

            fclose($handle);
        }

        echo "File Number " . $i . " Successfully Imported!\n\n";
    }
}
/****************** End Of Blog Module Insertion ******************/

/********************** Listing Module Insertion **********************/
/* Columns:
* 1- CompanyName
* 2- NumberReviews
* 3- OverallRating
* 4- FullAddress
* 5- Address
* 6- City
* 7- State
* 8- Zip
* 9- Latitude
* 10- Longitude
* 11- Hours
* 12- YelpCategories
* 13- YelpDetailsUrl
* 14- PhoneNumber
* 15- Website
*/
if ($listingInsertion) {
    echo "Inserting Listings into eDirectory...\n";
    $arrayTranslator = [
        0 => 'CompanyName',
        1 => 'NumberReviews',
        2 => 'OverallRating',
        3 => 'FullAddress',
        4 => 'Address',
        5 => 'City',
        6 => 'State',
        7 => 'Zip',
        8 => 'Latitude',
        9 => 'Longitude',
        10 => 'Hours',
        11 => 'YelpCategories',
        12 => 'YelpDetailsUrl',
        13 => 'PhoneNumber',
        14 => 'Website',
    ];
    $row = 0;
    if (($handle = fopen("listings.csv", "r")) !== FALSE) {
        while (($data = fgetcsv($handle, 302000, ",")) !== FALSE) {
            // Skip Header Values
            if ($row == 0 || $row < 301081) {
                $row++;
                continue;
            }

            echo "Row: " . $row . "\n";
            // $num = Number of Columns existent
            $num = count($data);
            $row++;

            $listingArray = null;
            foreach ($arrayTranslator as $key => $value) {
                $listingArray[$value] = $data[$key];
            }

            $listingUrl = system_generateFriendlyURL($listingArray['CompanyName']);
            $listingExistentObj = $container->get('doctrine')
                ->getRepository('ListingBundle:Listing')
                ->findOneBy(['friendlyUrl' => $listingUrl]);

            if (!empty($listingExistentObj)) {
                echo "Listing Already Added: " . $listingExistentObj->getTitle() . ", Jumping to the next.\n";
                unset($listingExistentObj);
                unset($listingArray);
                continue;
            }
            unset($listingExistentObj);
            // Set Post Content
            $listing = new Listing();

            $listing->setString('title', $listingArray['CompanyName']);
            $listing->setString('avg_review', $listingArray['OverallRating']);
            $listing->setString('address', $listingArray['Address']);
            $listing->setString('address2', $listingArray['FullAddress']);
            $listing->setString('zip_code', $listingArray['Zip']);
            $listing->setString('latitude', $listingArray['Latitude']);
            $listing->setString('longitude', $listingArray['Longitude']);
            $listing->setString('hours_work', $listingArray['Hours']);
            $listing->setString('phone', $listingArray['PhoneNumber']);
            $listing->setString('url', $listingArray['Website']);
            $listing->setString('friendly_url', $listingUrl);


            if (!empty($listingArray['State'])) {
                $countryId = null;
                $countryObj = $container->get('doctrine')
                    ->getRepository('CoreBundle:Location1', 'main')
                    ->findOneBy(['name' => 'United States']);

                if (is_null($countryObj)) {
                    $countryObj = new Location1();
                    $locationUrl = system_generateFriendlyURL('United States');
                    $countryObj->setString('name', 'United States');
                    $countryObj->setString('abbreviation', 'US');
                    $countryObj->setString('friendly_url', $locationUrl);

                    $countryObj->save();
                    $countryId = $countryObj->getNumber('id');
                } else {
                    $countryId = $countryObj->getId();
                }

                $listing->setNumber('location_1', $countryId);
                unset($countryObj);

                $stateId = null;
                $stateObj = $container->get('doctrine')
                    ->getRepository('CoreBundle:Location3', 'main')
                    ->findOneBy(['abbreviation' => $listingArray['State']]);

                if (is_null($stateObj)) {
                    $stateObj = new Location3();
                    $locationUrl = system_generateFriendlyURL($listingArray['State']);
                    $stateObj->setString('name', $listingArray['State']);
                    $stateObj->setString('abbreviation', $listingArray['State']);
                    $stateObj->setString('friendly_url', $locationUrl);
                    $stateObj->setNumber('location_1', $countryId);
                    $stateObj->save();

                    $stateId = $stateObj->getNumber('id');
                } else {
                    $stateId = $stateObj->getId();
                }

                $listing->setNumber('location_3', $stateId);
                unset($stateObj);

                if (!empty($listingArray['City'])) {
                    $cityId = null;
                    $cityObj = $container->get('doctrine')
                        ->getRepository('CoreBundle:Location5', 'main')
                        ->findOneBy(['abbreviation' => $listingArray['City']]);

                    if (is_null($cityObj)) {
                        $cityObj = new Location5();
                        $locationUrl = system_generateFriendlyURL($listingArray['City']);
                        $cityObj->setString('name', $listingArray['City']);
                        $cityObj->setString('abbreviation', $listingArray['City']);
                        $cityObj->setString('friendly_url', $locationUrl);
                        $cityObj->setNumber('location_1', $countryId);
                        $cityObj->setNumber('location_3', $stateId);
                        $cityObj->save();

                        $cityId = $cityObj->getNumber('id');
                    } else {
                        $cityId = $cityObj->getId();
                    }

                    $listing->setNumber('location_5', $cityId);
                    unset($cityObj);
                }
            }

            // Categories = Categories IDS
            $categoriesIds = null;
            $categoriesArray = explode(' | ', $listingArray['YelpCategories']);
            if (!empty($listingArray['YelpCategories'])) {
                foreach ($categoriesArray as $categoryName) {
                    $categ = trim($categoryName);
                    // Has Parent Category
                    if (strpos('>', $categ) !== false) {
                        $parents = explode(">", $categ);
                        $lastCategoryId = '';
                        foreach($parents as $parent) {
                            $category = $container->get('doctrine')
                                ->getRepository('ListingBundle:ListingCategory')
                                ->findOneBy(['title' => $categ]);

                            if (is_null($category)) {
                                $categUrl = system_generateFriendlyURL($parent);

                                $listingCategory = new \ArcaSolutions\ListingBundle\Entity\ListingCategory();
                                $listingCategory->setTitle($parent);
                                $listingCategory->setEnabled('y');
                                $listingCategory->setFeatured('y');
                                $listingCategory->setFriendlyUrl($categUrl);

                                $em->persist($listingCategory);
                                $em->flush();

                                $lastCategoryId = $listingCategory->getId();
                            } else {
                                $lastCategoryId = $category->getId();
                            }
                        }

                        $categoriesIds[] = $lastCategoryId;
                        // Doesn't have a parent category
                    } else {
                        $category = $container->get('doctrine')
                            ->getRepository('ListingBundle:ListingCategory')
                            ->findOneBy(['title' => $categ]);

                        if (is_null($category)) {
                            $categUrl = system_generateFriendlyURL($categ);

                            $listingCategory = new \ArcaSolutions\ListingBundle\Entity\ListingCategory();
                            $listingCategory->setTitle($categ);
                            $listingCategory->setEnabled('y');
                            $listingCategory->setFeatured('y');
                            $listingCategory->setFriendlyUrl($categUrl);

                            $em->persist($listingCategory);
                            $em->flush();

                            $categoriesIds[] = $listingCategory->getId();
                        } else {
                            $categoriesIds[] = $category->getId();
                        }
                    }
                }
            }

            $listing->setCategories($categoriesIds);

            $listing->save();

            if (!empty($listingArray['OverallRating'])) {
                $listing->setAvgReview($listingArray['OverallRating'], $listing->getNumber('id'));
            }

            echo "Listing Added: " . $listing->getString('title') . " ID: " . $listing->getNumber('id') . "\n";
        }

        fclose($handle);
    }
}
/****************** End Of Listing Module Insertion ******************/

?>
