<?php

namespace ArcaSolutions\ModStoresBundle\Plugins\CommunityForum\Controller;

use ArcaSolutions\CoreBundle\Exception\UnavailableItemException;
use ArcaSolutions\CoreBundle\Inflector;
use ArcaSolutions\CoreBundle\Services\ValidationDetail;
use ArcaSolutions\ModStoresBundle\Plugins\CommunityForum\Entity\Answer;
use ArcaSolutions\ModStoresBundle\Plugins\CommunityForum\Entity\Question;
use ArcaSolutions\ModStoresBundle\Plugins\CommunityForum\Entity\QuestionCategory;
use ArcaSolutions\ModStoresBundle\Plugins\CommunityForum\QuestionItemDetail;
use ArcaSolutions\ModStoresBundle\Plugins\CommunityForum\Services\ReportHandlerWithCommunityForum;
use ArcaSolutions\SearchBundle\Entity\Elasticsearch\Category;
use ArcaSolutions\WebBundle\Services\BaseCategoryService;
use DateTime;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Twig_Error;

class DefaultController extends Controller
{
    /**
     * @return Response
     */
    public function indexAction()
    {
        $categories = $this->getDoctrine()->getRepository('CommunityForumBundle:QuestionCategory')->findByFeatured('y');
        if ($categories) {
            array_walk($categories, function ($category) {
                $category->counter = $this->getDoctrine()->getRepository('CommunityForumBundle:Question')->createQueryBuilder('q')
                    ->select('count(q.id)')
                    ->where("q.status = 'A'")
                    ->where('q.category = :category_id')
                    ->setParameter('category_id', $category->getId())
                    ->getQuery()
                    ->getSingleScalarResult();
            });
        }

        $lastQuestions = $this->getDoctrine()->getRepository('CommunityForumBundle:Question')->findBy(['status' => 'A'],
            ['id' => 'DESC'], 10);
        $lastAnswers = $this->getDoctrine()->getRepository('CommunityForumBundle:Answer')->getLastAnswers(5);

        if ($lastAnswers) {
            array_walk($lastAnswers, function ($answer) {
                $answer->totalAnswers = $this->getDoctrine()->getRepository('CommunityForumBundle:Answer')->createQueryBuilder('a')
                    ->select('count(a.id)')
                    ->where('a.question = :question_id')
                    ->setParameter('question_id', $answer->getQuestion()->getId())
                    ->getQuery()
                    ->getSingleScalarResult();
            });
        }

        $twig = $this->container->get('twig');

        $twig->addGlobal('categories', $categories);
        $twig->addGlobal('lastQuestions', isset($lastQuestions) ? $lastQuestions : []);
        $twig->addGlobal('lastAnswers', isset($lastAnswers) ? $lastAnswers : []);

        $this->get('widget.service')->setModule('forum');
        $page = $this->container->get('doctrine')->getRepository('WysiwygBundle:Page')->getPageByType('Forum Homepage');

        return $this->render('::base.html.twig', [
            'pageId'          => $page->getId(),
            'pageTitle'       => $page->getTitle(),
            'metaDescription' => $page->getMetaDescription(),
            'metaKeywords'    => $page->getMetaKey(),
            'customTag'       => $page->getCustomTag()
        ]);
    }

    /**
     * @param $friendlyUrl
     *
     * @return Response
     * @throws Exception
     */
    public function detailAction($friendlyUrl)
    {
        /*
         * Validation
         */
        /* @var $item Question For phpstorm get properties of entity Question */
        $item = $this->get('search.engine')->itemFriendlyURL($friendlyUrl, 'question', 'CommunityForumBundle:Question');
        if ($item === null) {
            throw $this->createNotFoundException('This Question does not exist');
        }

        /* normalizes item to validate detail */
        $questionItemDetail = new QuestionItemDetail($this->container, $item);

        /* validating if question is enabled, if question's level is active and if level allows detail */
        if (!ValidationDetail::isDetailAllowed($questionItemDetail)) {
            /* error page */
            throw new UnavailableItemException();
        }

        /* ModStores Hooks */
        HookFire("question_after_validate_itemdetail", [
            "item" => &$item,
            "that" => &$this,
        ]);

        /*
         * Report
         */
        if (false === ValidationDetail::isSponsorsOrSitemgr($questionItemDetail)) {
            /* Counts the view towards the statistics */
            $this->container->get("question.reporthandler")->addQuestionReport($item->getId(), ReportHandlerWithCommunityForum::QUESTION_DETAIL);
        }

        $categoryIds = $categories = [];
        $questionCategory = $item->getCategory();
        /* @var $questionCategory QuestionCategory */
        $categories[] = $questionCategory;
        $categoryIds[] = ($questionCategory ? Category::create()->setId($questionCategory->getId())->setModule('question') : null);


        $this->get('widget.service')->setModule("forum");

        // Featured Categories
        $categoriesFeatured = $this->container->get('search.repository.category')->findCategoriesWithItens("question", true);

        // Popular Posts
        $content = new \stdClass();
        $content->custom = new \stdClass();
        $content->custom->order1 = 'popular';
        $content->custom->order2 = 'random';
        $popularPosts = $this->container->get('search.block')->getCards("question", 5, $content);

        $lastQuestions = $this->getDoctrine()->getRepository('CommunityForumBundle:Question')->createQueryBuilder('qc')
            ->select('qc.id, qc.title, qc.friendlyUrl, qc.entered')
            ->where("qc.status = 'A'")
            ->andWhere('qc.category = :category')
            ->andWhere('qc.id != :question_id')
            ->orderBy('qc.id', 'DESC')
            ->setMaxResults(10)
            ->setParameter('category', $item->getCategory() !== null ? $item->getCategory()->getId() : 0)
            ->setParameter('question_id', $item->getId())
            ->getQuery()
            ->getResult();

        $twig = $this->container->get('twig');

        /* ModStores Hooks */
        HookFire("forum_before_add_globalvars", [
            "item" => &$item,
            "that" => &$this,
        ]);

        $twig->addGlobal('bannerCategories', $categoryIds);
        $twig->addGlobal('item', $item);
        $twig->addGlobal('lastQuestions', $lastQuestions);
        $twig->addGlobal('categories', $categories);
        $twig->addGlobal('categoriesFeatured', $categoriesFeatured);
        $twig->addGlobal('popularQuestions', $popularPosts);


        $page = $this->container->get('doctrine')->getRepository('WysiwygBundle:Page')->getPageByType('Forum Detail');

        /* ModStores Hooks */
        HookFire("forum_before_render", [
            "page" => &$page,
            "that" => &$this,
        ]);

        return $this->render('CommunityForumBundle::detail.html.twig', [
            'pageId'    => $page->getId(),
            'customTag' => $page->getCustomTag(),
        ]);
    }

    /**
     * @param Request $request
     *
     * @return JsonResponse
     * @throws Exception
     */
    public function questionAction(Request $request)
    {
        $doctrine = $this->getDoctrine();
        $manager = $doctrine->getManager();

        $data = [
            'title'       => $request->request->get('title', ''),
            'description' => $request->request->get('description', ''),
            'category'    => $request->request->get('category', ''),
        ];

        $accountId = $request->getSession()->get('SESS_ACCOUNT_ID');

        if ($accountId === null) {

            $response = new Response();
            $response->headers->setCookie(new Cookie('forum_info', serialize($data)));
            $response->prepare(Request::createFromGlobals());
            $response->sendHeaders();

            return JsonResponse::create([
                'status' => 'login',
                'url'    => '/profile/login.php?userperm=1&forum_remember=1',
            ]);
        }

        $friendlyUrl = Inflector::friendly_title($data['title']);

        $invalidFriendly = $doctrine->getRepository('CommunityForumBundle:Question')->findOneBy([
            'friendlyUrl' => $friendlyUrl,
        ]);

        if ($invalidFriendly) {
            $friendlyUrl .= '-'.uniqid();
        }

        $account = $doctrine->getRepository('WebBundle:Accountprofilecontact')->find($accountId);

        $question = new Question();

        $question->setTitle($data['title']);
        $question->setDescription($data['description']);
        $question->setAccount($account);
        if ($data['category']) {
            $category = $doctrine->getRepository('CommunityForumBundle:QuestionCategory')->find($data['category']);
            $question->setCategory($category);
        }
        $question->setFriendlyUrl($friendlyUrl);
        $question->setEntered(new DateTime('now'));
        $question->setUpdated(new DateTime('now'));
        $question->setUpvotes(0);
        $question->setStatus('A');

        $manager->persist($question);
        $manager->flush();

        $this->get('question.synchronization')->addUpsert($question->getId());

        return JsonResponse::create([
            'status' => 'sendQuestion',
            'url'    => $this->generateUrl('forum_detail', [
                'friendlyUrl' => $friendlyUrl,
                '_format'     => 'html',
            ]),
        ]);

    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function loadCategoryAction(Request $request){
        /**
         * @var SessionInterface $session
         */
        $session =  $request->getSession();
        $userId = $session->get('SESS_ACCOUNT_ID');
        $smLoggedIn = $session->get('SM_LOGGEDIN ');
        if(is_null($userId) && is_null($smLoggedIn)) {
            $allSessionVars = $session->all();
            if(array_key_exists('SESS_ACCOUNT_ID',$allSessionVars)){
                $userId = $allSessionVars['SESS_ACCOUNT_ID'];
            }
            if(array_key_exists('SM_LOGGEDIN',$allSessionVars)) {
                $smLoggedIn = $allSessionVars['SM_LOGGEDIN'];
            }
            unset($allSessionVars);
        }

        if (empty($userId) && empty($smLoggedIn)) {
            return new JsonResponse([
                'success' => 'false',
                'message' => 'Unauthorized access'
            ]);
        }

        if(!$request->request->has('action') || empty($request->request->get('action')) || !($request->request->get('action') === 'load' || $request->request->get('action') === 'search' || $request->request->get('action') === 'get_title')) {
            return JsonResponse::create([
                'success' => 'false',
                'message' => 'Invalid action'
            ]);
        }

        if (!$request->request->has('module') || empty($request->request->get('module')) || $request->request->get('module') !== 'forum') {
            return JsonResponse::create([
                'success' => 'false',
                'message' => 'Invalid module'
            ]);
        }
        /** @var BaseCategoryService $categoryService */
        $categoryService = $this->container->get('question.category.service');

        if($request->request->get('action') === 'load') {

            if (!$request->request->has('level') || empty($request->request->get('level'))) {
                $level = 0;
            } else {
                $level = $request->request->get('level');
            }



            if (!$request->request->has('id') || empty($request->request->get('id'))) {
                $categories = $categoryService->getAllParentCategories();
            } else {
                $categories = $categoryService->getAllChildCategoriesById($request->request->get('id'));
            }

            $template = null;

            try {
                $selectParent = $request->request->has('selectParent') && $request->request->get('selectParent') === 'true';
                $onlyParents = $request->request->has('onlyParents') && $request->request->get('onlyParents') === 'true';
                $requestCategories = $request->request->has('categories') && !empty($request->request->get('categories')) && is_array($request->request->get('categories')) ? $request->request->get('categories') : [];
                $categoryService->buildCategoryTree($level, $categories, $template, $selectParent, $onlyParents, false, $requestCategories);
            } catch (Twig_Error $e) {
                return JsonResponse::create([
                    'success' => 'false',
                    'message' => 'Can not build category tree due to a rendering error. Exception: ' . $e->getMessage()
                ]);
            } catch (Exception $e){
                return JsonResponse::create([
                    'success' => 'false',
                    'message' => 'Can not build category tree due to an unexpected exception. Exception: ' . $e->getMessage()
                ]);
            }

            if (!empty($template)) {
                return JsonResponse::create([
                    'success' => 'true',
                    'template' => $template
                ]);
            } else {
                return JsonResponse::create([
                    'success' => 'false'
                ]);
            }
        } elseif ($request->request->get('action') === 'search') {
            $template = null;
            try {
                $term = ($request->request->has('term') && !empty($request->request->get('term')))?$request->request->get('term'):null;
                if(empty($term)){
                    return JsonResponse::create([
                        'success' => 'false',
                        'message' => 'Empty term is not allowed.'
                    ]);
                }

                $selectParent = $request->request->has('selectParent') && $request->request->get('selectParent') === 'true';
                $requestCategories = $request->request->has('categories') && !empty($request->request->get('categories')) && is_array($request->request->get('categories')) ? $request->request->get('categories') : [];

                $categoryService->buildCategoryTreeByTerm($term, $template, $selectParent, null, $requestCategories);
            } catch (Twig_Error $e) {
                return JsonResponse::create([
                    'success' => 'false',
                    'message' => 'Can not build category tree due to a rendering error. Exception: ' . $e->getMessage()
                ]);
            } catch (Exception $e){
                return JsonResponse::create([
                    'success' => 'false',
                    'message' => 'Can not build category tree due to an unexpected exception. Exception: ' . $e->getMessage()
                ]);
            }

            if (!empty($template)) {
                return JsonResponse::create([
                    'success' => 'true',
                    'template' => $template
                ]);
            } else {
                return JsonResponse::create([
                    'success' => 'false'
                ]);
            }
        } elseif ($request->request->get('action') === 'get_title') {
            $template = null;
            try {
                $id = ($request->request->has('id') && !empty($request->request->get('id')))?$request->request->get('id'):null;
                if(empty($id)){
                    return JsonResponse::create([
                        'success' => 'false',
                        'message' => 'Empty id is not allowed.'
                    ]);
                }
                if(!is_numeric($id)){
                    return JsonResponse::create([
                        'success' => 'false',
                        'message' => 'Non numeric id is not allowed.'
                    ]);
                }
                /**
                 * @var QuestionCategory $questionCategory
                 */
                $questionCategory = $categoryService = $this->container->get('doctrine')->getRepository('CommunityForumBundle:QuestionCategory')->find($id);
                if(!empty($questionCategory)){
                    return JsonResponse::create([
                        'success' => 'true',
                        'title' => $questionCategory->getTitle()
                    ]);
                } else {
                    return JsonResponse::create([
                        'success' => 'false'
                    ]);
                }
            } catch (Exception $e){
                return JsonResponse::create([
                    'success' => 'false',
                    'message' => 'Can not get category title due to an unexpected exception. Exception: ' . $e->getMessage()
                ]);
            }
        }
        unset($session);
    }


    /**
     * @param Request $request
     *
     * @return JsonResponse|RedirectResponse
     * @throws Exception
     */
    public function answerAction(Request $request)
    {
        $manager = $this->getDoctrine()->getManager();

        $accountId = $request->getSession()->get('SESS_ACCOUNT_ID');

        if ($accountId === null) {

            $destiny = $request->request->get('destiny', '');

            return JsonResponse::create([
                'status' => 'login',
                'url'    => '/profile/login.php?userperm=1&destiny='.$destiny,
            ]);
        }

        $answerText = $request->request->get('answer', '');
        $questionId = $request->request->get('question', 0);
        $question = $this->getDoctrine()->getRepository('CommunityForumBundle:Question')->findOneById($questionId);

        if ($answerText && $questionId && $question) {

            $account = $this->getDoctrine()->getRepository('WebBundle:Accountprofilecontact')->find($accountId);

            $answer = new Answer();
            $answer->setQuestion($question);
            $answer->setAccount($account);
            $answer->setDescription(trim($answerText));
            $answer->setEntered(new DateTime('now'));
            $answer->setUpdated(new DateTime('now'));
            $answer->setUpvotes(0);
            $answer->setStatus('A');

            $manager->persist($answer);
            $manager->flush();

            try {

                $sitemgrEmail = $this->getDoctrine()->getRepository('WebBundle:Setting')->getSetting('sitemgr_email');
                $sitemgrEmail = explode(',', $sitemgrEmail)[0];

                $description = str_replace(["\r", "\n", "\t", "\s{2,}", '&nbsp;', '&amp;', '&nbsp;', 'nbsp;'], ' ',
                    htmlentities(substr(strip_tags($answer->getDescription()), 0, 255)));
                $description = preg_replace('/[\x00-\x1F\x7F-\xFF]/', ' ', $description);
                $description .= '... ('.$this->container->get('translator')->trans('read more').')';


                if (!empty($question->getAccount()) && !empty($sitemgrEmail)) {
                    $this->get('email.notification.service')->getEmailMessage(100)
                        ->setTo($question->getAccount()->getUsername())
                        ->setFrom($sitemgrEmail)
                        ->setPlaceholder('ACCOUNT_NAME', $account->getFirstName().' '.$account->getLastName())
                        ->setPlaceholder('ACCOUNT_USERNAME', $account->getUsername())
                        ->setPlaceholder('TOPIC_ANSWER', $description)
                        ->setPlaceholder('ITEM_URL', $this->generateUrl('forum_detail',
                            ['friendlyUrl' => $question->getFriendlyUrl(), '_format' => 'html'],
                            UrlGeneratorInterface::ABSOLUTE_URL))
                        ->sendEmail();
                }

            } catch (Exception $e) {
                $this->container->get('logger')->addError('An error occurred: ['.$e->getMessage().']');
            }

        }

        return $this->redirectToRoute('forum_detail', [
            'friendlyUrl' => $question->getFriendlyUrl(),
            '_format'     => 'html',
        ]);
    }
}
