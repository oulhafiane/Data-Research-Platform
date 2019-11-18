<?php

namespace App\Controller;

use App\Entity\Admin;
use App\Service\CurrentUser;
use App\Entity\SearcherApplications;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Doctrine\ORM\EntityManagerInterface;
use JMS\Serializer\SerializerInterface;
use JMS\Serializer\SerializationContext;

class AdminController extends AbstractController
{
    private $em;
    private $cr;
    private $serializer;

    public function __construct(EntityManagerInterface $em, CurrentUser $cr, SerializerInterface $serializer)
    {
        $this->em = $em;
        $this->cr = $cr;
        $this->serializer = $serializer;
    }

    /**
     * @Route("/api/admin/listApplications", name="list_applications", methods={"GET"})
     */
    public function listApplicationsAction()
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        $current = $this->cr->getCurrentUser($this);
        if (!($current instanceof Admin)) {
            throw new HttpException(500, "You are not an admin.");
        }

        $applications = $this->em->getRepository(SearcherApplications::class)->findBy(['status' => null]);
        $data = $this->serializer->serialize($applications, 'json', SerializationContext::create()->setGroups(array('list-applications')));
        $response = new Response($data);
        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }

    /**
     * @Route("/api/admin/approveSearcher/{id}", name="approve_searcher", methods={"PATCH"}, requirements={"id"="\d+"})
     */
    public function approveSearcher($id)
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        $current = $this->cr->getCurrentUser($this);
        if (!($current instanceof Admin)) {
            throw new HttpException(500, "You are not an admin.");
        }

        $application = $this->em->getRepository(SearcherApplications::class)->find($id);
        if (null === $application)
            throw new HttpException(404, "Application not found.");
        if (null !== $application->getStatus())
            throw new HttpException(404, "Application already closed.");
        try{
            $application->setAcceptedBy($current);
            $application->setStatus(true);
            $query = "UPDATE user SET type = 'searcher', roles = '[\"ROLE_SEARCHER\"]' WHERE id = ".$application->getUser()->getId();
            $this->em->getConnection()->exec( $query );
            $this->em->persist($application);
            $this->em->flush();

            return new JsonResponse([
                'code' => 200,
                'message' => "Application approved successfully.",
                'extras' => NULL
            ], 200);
        } catch (\Exception $ex) {
            throw new HttpException(500, $ex->getMessage());
        }
    }

    /**
     * @Route("/api/admin/rejectSearcher/{id}", name="reject_searcher", methods={"PATCH"}, requirements={"id"="\d+"})
     */
    public function rejectSearcher($id)
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        $current = $this->cr->getCurrentUser($this);
        if (!($current instanceof Admin)) {
            throw new HttpException(500, "You are not an admin.");
        }

        $application = $this->em->getRepository(SearcherApplications::class)->find($id);
        if (null === $application)
            throw new HttpException(404, "Application not found.");
        if (null !== $application->getStatus())
            throw new HttpException(404, "Application already closed.");
        try{
            $application->setAcceptedBy($current);
            $application->setStatus(false);
            $this->em->persist($application);
            $this->em->flush();

            return new JsonResponse([
                'code' => 200,
                'message' => "Application rejected successfully.",
                'extras' => NULL
            ], 200);
        } catch (\Exception $ex) {
            throw new HttpException(500, $ex->getMessage());
        }
    }
}
