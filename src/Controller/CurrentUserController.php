<?php

namespace App\Controller;

use App\Service\CurrentUser;
use App\Entity\Customer;
use App\Entity\SearcherApplications;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use JMS\Serializer\SerializerInterface;
use JMS\Serializer\SerializationContext;
use Doctrine\ORM\EntityManagerInterface;

class CurrentUserController extends AbstractController
{
	private $cr;
	private $serializer;
	private $em;

	public function __construct(CurrentUser $cr, SerializerInterface $serializer, EntityManagerInterface $em)
	{
		$this->cr = $cr;
		$this->serializer = $serializer;
		$this->em = $em;
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

	private function persistApplication($application) {
		$extras = NULL;

		try {
			$this->em->persist($application);
			$this->em->flush();
			$extras['id'] = $application->getId();
		} catch (\Exception $ex) {
			throw new HttpException(500, $ex->getMessage());
		}

		return new JsonResponse([
			'code' => 201,
			'message' => "Application created successfully.",
			'extras' => $extras
		], 201);
	}

	/**
     * @Route("/api/current/applySearcher", name="apply_searcher", methods={"PATCH"})
     */
    public function applySearcherAction()
    {
		$code = 401;
		$message = "You are already a searcher or an admin.";
		$extras = NULL;

		$current = $this->cr->getCurrentUser($this);
		if ($current instanceof Customer) {
			$oldApplication = $current->getSearcherApplication();
			if (null === $oldApplication) {
				$application = new SearcherApplications();
				$application->setUser($current);
				return $this->persistApplication($application);
			} else if (null === $oldApplication->getStatus()) {
					$message = "You have already an application open, please wait for an response from an administrator.";
					$extras['id'] = $oldApplication->getId();
			} else {
				$creationDate = $oldApplication->getCreationDate();
				$diff = date_diff(new \DateTime(), $creationDate)->format('%a');
				if ($diff < 7) {
					throw new HttpException(401, "You muse wait 7 days to apply again.");
				} else {
					$oldApplication->setCreationDate(new \DateTime());
					$oldApplication->setAcceptedBy(NULL);
					$oldApplication->setStatus(NULL);
					return $this->persistApplication($oldApplication);
				}
			}
		}

		return new JsonResponse([
			'code' => $code,
			'message' => $message,
			'extras' => $extras
		], $code);
    }
}
