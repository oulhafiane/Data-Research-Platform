<?php

namespace App\Controller;

use App\Service\CurrentUser;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use JMS\Serializer\SerializerInterface;
use JMS\Serializer\SerializationContext;

class CurrentUserController extends AbstractController
{
	private $cr;
	private $serializer;

	public function __construct(CurrentUser $cr, SerializerInterface $serializer)
	{
		$this->cr = $cr;
		$this->serializer = $serializer;
	}

    /**
     * @Route("/api/current/infos", name="current_user_infos", methods={"GET"})
     */
    public function currentUserInfosAction()
    {
		$current = $this->cr->getCurrentUser($this);	
		$data = $this->serializer->serialize($current, 'json', SerializationContext::create()->setGroups(array('infos')));
		$response = new Response($data, 200);
		$response->headers->set('Content-Type', 'application/json');

		return $response;
    }
}
