<?php

namespace App\Controller;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use JMS\Serializer\SerializerInterface;
use JMS\Serializer\SerializationContext;

class ProfileController extends AbstractController
{
    private $em;
    private $serializer;

    public function __construct(EntityManagerInterface $em, SerializerInterface $serializer)
    {
        $this->em = $em;
        $this->serializer = $serializer;
    }

    /**
     * @Route("/api/profile/all", name="all_profiles", methods={"GET"})
     */
    public function allProfilesAction(Request $request)
    {
        $profiles = $this->em->getRepository(User::class)->findAll();
        if (null === $profiles)
            throw new HttpException(404, "No profile found.");
        $data = $this->serializer->serialize($profiles, 'json', SerializationContext::create()->setGroups(array('all-profiles')));
		$response = new Response($data, 200);
		$response->headers->set('Content-Type', 'application/json');

		return $response;
    }

    /**
     * @Route("/api/profile/{uuid}", name="public_profile", methods={"GET"}, requirements={"uuid"="[a-f0-9]{8}-[a-f0-9]{4}-4[a-f0-9]{3}-[89aAbB][a-f0-9]{3}-[a-f0-9]{12}"})
     */
    public function publicProfileAction(Request $request, $uuid)
    {
        $profile = $this->em->getRepository(User::class)->findOneBy(["uuid" => $uuid]);
        if (null === $profile)
            throw new HttpException(404, "Profile not found.");
        $data = $this->serializer->serialize($profile, 'json', SerializationContext::create()->setGroups(array('public')));
		$response = new Response($data, 200);
		$response->headers->set('Content-Type', 'application/json');

		return $response;
    }
}
