<?php

namespace App\Service;

use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Doctrine\ORM\EntityManagerInterface;
use JMS\Serializer\SerializerInterface;
use JMS\Serializer\DeserializationContext;

class UpdateFormHandler
{
	private $validator;
	private $serializer;
	private $entityManager;

	public function __construct(ValidatorInterface $validator, SerializerInterface $serializer, EntityManagerInterface $entityManager)
	{
		$this->validator = $validator;
		$this->serializer = $serializer;
		$this->entityManager = $entityManager;
	}

	public function validate($data, $class, $callBack, $validation_groups, $serializer_groups)
	{
		$code = 401;
		$message = "Unauthorized";
		$extras = NULL;

		try {
			$object = $this->serializer->deserialize($data, $class, 'json', DeserializationContext::create()->setGroups($serializer_groups));

			if (!is_null($object)) {
				$violations = $this->validator->validate($object, null, $validation_groups);
				if (count($violations) !== 0) {
					foreach ($violations as $violation) {
						$extras[$violation->getPropertyPath()] = $violation->getMessage();
					}
				}
				else {
					$toUpdate = $callBack($object);

					$this->entityManager->persist($toUpdate);
					$this->entityManager->flush();

					$code = 201;
					$message = substr(strrchr($class, "\\"), 1).' updated successfully';
				}
			}
		}catch (HttpException $ex) {
			$code = $ex->getStatusCode();
			$message = $ex->getMessage();
		}catch (\Exception $ex) {
			$code = 500;
			$message = $ex->getMessage();
		}
		
		return new JsonResponse([
			'code' => $code,
			'message' => $message,
			'extras' => $extras
		], $code);
	}
}
