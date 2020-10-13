<?php

namespace ArcaSolutions\ModStoresBundle\Plugins\MailchimpIntegration\Controller;

use ArcaSolutions\WebBundle\Services\TimelineHandler;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Validation;

class DefaultController extends Controller
{
    /**
     * Newsletter action
     *
     * Used to save news visitors
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function newsletterAction(Request $request)
    {
        if (!$this->container->get('mailchimp.service')->isSetUp()) {
            $response = $this->forward('ArcaSolutions\WebBundle\Controller\DefaultController::newsletterAction', [
                'request'  => $request
            ]);
    
            return $response;
        }
        
        $constraint = new Assert\Collection([
            'email' => [
                new Assert\Email(),
                new Assert\NotBlank(),
            ],
            'name'  => [
                new Assert\NotBlank(),
            ],
        ]);

        $validation = Validation::createValidator()->validate([
            'name'  => $request->get('name', ''),
            'email' => $request->get('email', ''),
        ], $constraint);

        if (count($validation) == 0) {

            $this->container->get('timelinehandler')->add(
                0,
                TimelineHandler::ITEMTYPE_NEWSLETTER,
                TimelineHandler::ACTION_NEW
            );

            $this->container->get('mailchimp.service')->subscribeUser([
                'EMAIL' => $request->get('email'),
                'FNAME' => $request->get('name'),
            ]);

            return JsonResponse::create([
                'success' => true,
                'message' => $this->get('translator')->trans('Subscriptions created successfully'),
            ]);
        }

        $error = [];
        $error['success'] = false;
        for ($i = 0, $iMax = count($validation); $i < $iMax; $i++) {
            preg_match('/[a-zA-Z]+/', $validation->get($i)->getPropertyPath(), $key);
            $key = current($key);

            $error['errors'][] = [
                'field'   => $key,
                'message' => $validation->get($i)->getMessage(),
            ];
        }

        return JsonResponse::create($error);
    }
}
