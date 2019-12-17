<?php

namespace App\Controller;

use App\Entity\DataSet;
use App\Service\CurrentUser;
use App\Service\FormHandler;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpFoundation\Request;
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
     * @Route("/api/current/dataset", name="create_data_set", methods={"POST"})
     */
    public function createDataSetAction(Request $request)
    {
        $this->checkRoleAndId($request);

        return $this->form->validate($request, DataSet::class, array($this, 'setOwner'), ['new-dataset'], ['new-dataset']);
    }
}
