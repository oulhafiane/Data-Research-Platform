<?php

namespace App\Controller;

use App\Entity\DataSet;
use App\Entity\Part;
use App\Service\CurrentUser;
use App\Service\FormHandler;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Filesystem\Filesystem;
use Doctrine\ORM\EntityManagerInterface;
use JMS\Serializer\SerializerInterface;
use JMS\Serializer\SerializationContext;
use Ramsey\Uuid\Uuid;

class DataSetController extends AbstractController
{    
    private $validator;
    private $cr;
    private $imagineCacheManager;
    private $form;
    private $em;
    private $serializer;

    public function __construct(ValidatorInterface $validator, CurrentUser $cr, FormHandler $form, EntityManagerInterface $em, SerializerInterface $serializer)
    {
        $this->validator = $validator;
        $this->cr = $cr;
        $this->form = $form;
        $this->em = $em;
        $this->serializer = $serializer;
    }
    
    private function checkRoleAndId(Request $request)
    {
        $data = json_decode($request->getContent(), true);
        if (null !== $data && array_key_exists('id', $data))
            throw $this->createAccessDeniedException();

        $this->denyAccessUnlessGranted('ROLE_SEARCHER');
        return;
    }

    public function setOwner($dataset)
    {
        $dataset->setOwner($this->cr->getCurrentUser($this));
        $dataset->setUuid(Uuid::uuid4()->toString());
        try {
            $db_name = $this->getParameter('datasets_location').'/'.$dataset->getUuid().'.db';
            $filesystem = new Filesystem();
            while ($filesystem->exists($db_name)) {
                $dataset->setUuid(Uuid::uuid4()->toString());
                $db_name = $this->getParameter('datasets_location').'/'.$dataset->getUuid().'.db';
            }
            $db=new \SQLite3($db_name);
            return $dataset->getUuid();
        } catch (\Exception $ex) {
            throw new HttpException(400, "Cannot create the dataset.");
        }
    }

    public function addVariables($part)
    {
        $variables = $part->getVariables();
        foreach($variables as $variable) {
            $violations = $this->validator->validate($variable, null, ['add-variables']);
            $message = '';
            foreach ($violations as $violation) {
                $message .= $violation->getPropertyPath() . ': ' . $violation->getMessage() . ' ';
            }
            if (count($violations) !== 0)
                throw new HttpException(406, $message);

            $variable->setPart($part);
            try {
                $this->em->persist($variable);
            } catch (\Exception $ex) {
                throw new HttpException(400, $ex->getMessage());
            }
        }
        return false;
    }

    public function setDataSetToObject($object, $dataset)
    {
        $object->setDataSet($dataset);
        $this->addVariables($object);
        return true;
    }
    
    public function doNothing($object)
    {
        return false;
    }

    /**
     * @Route("/api/current/dataset/{uuid}", name="update_dataset", methods={"PATCH"}, requirements={"uuid"="[a-f0-9]{8}-[a-f0-9]{4}-4[a-f0-9]{3}-[89aAbB][a-f0-9]{3}-[a-f0-9]{12}"})
     */
    public function UpdateDataSetAction(Request $request, $uuid)
    {
        $this->checkRoleAndId($request);

        $dataset = $this->em->getRepository(DataSet::class)->findOneBy(['uuid' => $uuid]);
        if (null === $dataset)
            throw new HttpException(404, "Dataset not found.");
        
        $current = $this->cr->getCurrentUser($this);
        if ($current !== $dataset->getOwner())
            throw new HttpException(401, "You are not the owner.");

        return $this->form->update($request, DataSet::class, array($this, 'doNothing'), ['update-dataset'], ['update-dataset'], $dataset->getId());
    }

    /**
     * @Route("/api/current/dataset/{uuid}", name="specific_dataset", methods={"GET"}, requirements={"uuid"="[a-f0-9]{8}-[a-f0-9]{4}-4[a-f0-9]{3}-[89aAbB][a-f0-9]{3}-[a-f0-9]{12}"})
     */
    public function SpecificDataSetAction($uuid)
    {
        $dataset = $this->em->getRepository(DataSet::class)->findOneBy(['uuid' => $uuid]);
        if (null === $dataset)
            throw new HttpException(404, "Dataset not found.");
        $current = $this->cr->getCurrentUser($this);
        if ($current != $dataset->getOwner())
            throw new HttpException(401, "You are not the owner");
        $data = $this->serializer->serialize($dataset, 'json', SerializationContext::create()->setGroups(array('my-dataset', 'my-dataset')));
        $response = new Response($data);
        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }

    /**
     * @Route("/api/current/dataset", name="my_datasets", methods={"GET"})
     */
    public function getMyDataSetsAction(Request $request)
    {
        $page = $request->query->get('page', 1);
        $limit = $request->query->get('limit', 12);
        $pager = $this->em->getRepository(DataSet::class)->findMyDataSets($page, $limit, $this->cr->getCurrentUser($this));
        $results = $pager->getCurrentPageResults();
        $nbPages = $pager->getNbPages();
        $currentPage = $pager->getCurrentPage();
        $maxPerPage = $pager->getMaxPerPage();
        $itemsCount = $pager->count();
        $datasets = array();
        foreach ($results as $result) {
            $datasets[] = $result;
        }
        $data = array('nbPages' => $nbPages, 'currentPage' => $currentPage, 'maxPerPage' => $maxPerPage, 'itemsCount' => $itemsCount, 'datasets' => $datasets);
        $data = $this->serializer->serialize($data, 'json', SerializationContext::create()->setGroups(array('my-dataset')));
        $response = new Response($data);
        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }
    
    /**
     * @Route("/api/current/dataset", name="create_dataset", methods={"POST"})
     */
    public function createDataSetAction(Request $request)
    {
        $this->checkRoleAndId($request);

        return $this->form->validate($request, DataSet::class, array($this, 'setOwner'), ['new-dataset'], ['new-dataset']);
    }

    /**
     * @Route("/api/current/dataset/{uuid}/part", name="add_part_dataset", methods={"POST"}, requirements={"uuid"="[a-f0-9]{8}-[a-f0-9]{4}-4[a-f0-9]{3}-[89aAbB][a-f0-9]{3}-[a-f0-9]{12}"})
     */
    public function addPartToDataSetAction(Request $request, $uuid)
    {
        $this->checkRoleAndId($request);

        $dataset = $this->em->getRepository(DataSet::class)->findOneBy(['uuid' => $uuid]);
        if (null === $dataset)
            throw new HttpException(404, "Dataset not found.");
        
        $current = $this->cr->getCurrentUser($this);
        if ($current !== $dataset->getOwner())
            throw new HttpException(401, "You are not the owner.");

        return $this->form->validate($request, Part::class, array($this, 'setDataSetToObject'), ['new-part'], ['new-part'], null, $dataset);
    }

    /**
     * @Route("/api/current/dataset/{uuid}/part/{id}", name="update_part_dataset", methods={"PATCH"}, requirements={"id"="\d+", "uuid"="[a-f0-9]{8}-[a-f0-9]{4}-4[a-f0-9]{3}-[89aAbB][a-f0-9]{3}-[a-f0-9]{12}"})
     */
    public function updatePartToDataSetAction(Request $request, $uuid, $id)
    {
        $this->checkRoleAndId($request);

        $dataset = $this->em->getRepository(DataSet::class)->findOneBy(['uuid' => $uuid]);
        if (null === $dataset)
            throw new HttpException(404, "Dataset not found.");
        
        $current = $this->cr->getCurrentUser($this);
        if ($current !== $dataset->getOwner())
            throw new HttpException(401, "You are not the owner.");

        $part = $this->em->getRepository(Part::class)->findOneBy(['id' => $id, 'dataSet' => $dataset->getId()]);
        if (null === $part)
            throw new HttpException(404, "Part of dataset not found.");

        return $this->form->update($request, Part::class, array($this, 'doNothing'), ['update-part'], ['update-part'], $part->getId());
    }

    /**
     * @Route("/api/current/dataset/{uuid}/part/{id}", name="add_variables_dataset", methods={"POST"}, requirements={"id"="\d+", "uuid"="[a-f0-9]{8}-[a-f0-9]{4}-4[a-f0-9]{3}-[89aAbB][a-f0-9]{3}-[a-f0-9]{12}"})
     */
    public function addVariablesAction(Request $request, $uuid, $id)
    {
        $this->checkRoleAndId($request);

        $dataset = $this->em->getRepository(DataSet::class)->findOneBy(['uuid' => $uuid]);
        if (null === $dataset)
            throw new HttpException(404, "Dataset not found.");
        
        $current = $this->cr->getCurrentUser($this);
        if ($current !== $dataset->getOwner())
            throw new HttpException(401, "You are not the owner.");

        $part = $this->em->getRepository(Part::class)->findOneBy(['id' => $id, 'dataSet' => $dataset->getId()]);
        if (null === $part)
            throw new HttpException(404, "Part of dataset not found.");

        return $this->form->update($request, Part::class, array($this, 'addVariables'), ['add-variables'], ['add-variables'], $part->getId());
    }
}