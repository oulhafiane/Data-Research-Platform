<?php

namespace App\Controller;

use App\Service\CurrentUser;
use App\Service\FormHandler;
use App\Entity\Problematic;
use App\Entity\Comment;
use App\Helper\UploadedBase64EncodedFile;
use App\Helper\Base64EncodedFile;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Liip\ImagineBundle\Imagine\Cache\CacheManager;
use Doctrine\ORM\EntityManagerInterface;
use JMS\Serializer\SerializerInterface;
use JMS\Serializer\SerializationContext;

class ProblematicController extends AbstractController
{
    private $validator;
    private $cr;
    private $imagineCacheManager;
    private $form;
    private $em;
    private $serializer;

    public function __construct(ValidatorInterface $validator, CurrentUser $cr, CacheManager $cacheManager, FormHandler $form, EntityManagerInterface $em, SerializerInterface $serializer)
    {
        $this->validator = $validator;
        $this->cr = $cr;
        $this->imagineCacheManager = $cacheManager;
        $this->form = $form;
        $this->em = $em;
        $this->serializer = $serializer;
    }

    public function setOwner($problematic)
    {
        $problematic->setOwner($this->cr->getCurrentUser($this));
        $photos = $problematic->getPhotos();
        if (null === $photos)
            return True;
        foreach ($photos as $photo) {
            $file = new UploadedBase64EncodedFile(new Base64EncodedFile($photo->getFile()));
            $photo->setFile($file);
            $photo->setProblematic($problematic);
            $photo->setLink($file->getClientOriginalName());
            $this->imagineCacheManager->getBrowserPath($photo->getLink(), 'photo_thumb');
            $this->imagineCacheManager->getBrowserPath($photo->getLink(), 'photo_scale_down');
        }
        $violations = $this->validator->validate($problematic, null, ['new-photo']);
        $message = '';
        foreach ($violations as $violation) {
            $message .= $violation->getPropertyPath() . ': ' . $violation->getMessage() . ' ';
        }
        if (count($violations) !== 0)
            throw new HttpException(406, $message);
        return True;
    }

    private function checkRoleAndId(Request $request)
    {
        $data = json_decode($request->getContent(), true);
        if (null !== $data && array_key_exists('id', $data))
            throw $this->createAccessDeniedException();

        $this->denyAccessUnlessGranted('ROLE_SEARCHER');
        return;
    }

    /**
     * @Route("/api/problematic/new", name="new_problematic", methods={"POST"})
     */
    public function newProblematicAction(Request $request)
    {
        $this->checkRoleAndId($request);

        return $this->form->validate($request, Problematic::class, array($this, 'setOwner'), ['new-problematic'], ['new-problematic']);
    }

    /**
	 * @Route("/api/problematic/{id}", name="specific_problematic", methods={"GET"}, requirements={"id"="\d+"})
	 */
	public function SpecificProblematicAction($id)
	{
		$problematic = $this->em->getRepository(problematic::class)->find($id);
		if (null === $problematic)
			throw new HttpException(404, "problematic not found.");
		$data = $this->serializer->serialize($problematic, 'json', SerializationContext::create()->setGroups(array('list-problematics', 'specific-problematic')));
		$response = new Response($data);
		$response->headers->set('Content-Type', 'application/json');

		return $response;
	}

    /**
     * @Route("/api/problematic/all", name="all_problematic", methods={"GET"})
     */
    public function getAllProblematicAction(Request $request)
    {
        $page = $request->query->get('page', 1);
		$limit = $request->query->get('limit', 10);
		$category = $request->query->get('category', null);
		$results = $this->em->getRepository(Problematic::class)->findProblematic($page, $limit, $category)->getCurrentPageResults();
		$problematics = array();
		foreach ($results as $result) {
            $problematics[] = $result;
        }
		$data = $this->serializer->serialize($problematics, 'json', SerializationContext::create()->setGroups(array('list-problematics')));
		$response = new Response($data);
		$response->headers->set('Content-Type', 'application/json');

		return $response;
    }

    /**
	 * @Route("/api/problematic/{id}/comment", name="new_comment", methods={"POST"}, requirements={"id"="\d+"})
	 */
    public function newCommentAction(Request $request, $id)
    {
        $this->checkRoleAndId($request);
        $problematic = $this->em->getRepository(Problematic::class)->find($id);
		if (null === $problematic)
			throw new HttpException(404, "Problematic not found.");

        $data = json_decode($request->getContent(), true);
        if (null !== $data && !array_key_exists('text', $data))
			throw new HttpException(406, 'Field \'text\' not found.');
        $comment = new Comment();
        $comment->setOwner($this->cr->getCurrentUser($this));
        $comment->setProblematic($problematic);
        $comment->setText($data['text']);

        try{
            $this->em->persist($comment);
            $this->em->flush();
        }catch(\Exception $ex){
            throw new HttpException(500, $ex->getMessage());
        }
        
        return new JsonResponse([
			'code' => 201,
			'message' => 'Comment added successfully'
		], 201);
    }

    /**
	 * @Route("/api/problematic/{id}/comment", name="all_problematic_comment", methods={"GET"}, requirements={"id"="\d+"})
	 */
    public function getAllProblematicCommentsAction(Request $request, $id)
    {
        $page = $request->query->get('page', 1);
		$limit = $request->query->get('limit', 10);
        $problematic = $this->em->getRepository(Problematic::class)->find($id);
        if (null === $problematic)
			throw new HttpException(404, "Problematic not found.");
        $results = $this->em->getRepository(Comment::class)->findComments($page, $limit, $problematic)->getCurrentPageResults();
		$comments = array();
		foreach ($results as $result) {
            $comments[] = $result;
        }
		$data = $this->serializer->serialize($comments, 'json', SerializationContext::create()->setGroups(array('list-comments')));
		$response = new Response($data);
		$response->headers->set('Content-Type', 'application/json');

		return $response;
    }
}
