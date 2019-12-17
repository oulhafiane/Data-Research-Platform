<?php

namespace App\Service;

use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use JMS\Serializer\SerializerInterface;
use JMS\Serializer\DeserializationContext;

class FormHandler
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

	public function checkId(Request $request)
	{
		$data = json_decode($request->getContent(), true);
		if (null !== $data && array_key_exists('id', $data))
			throw new HttpException(406, 'Field \'id\' not acceptable.');
	}

	public function validate(Request $request, $class, $callBack, $validation_groups, $serializer_groups, $notificationCallBack = null)
	{
		$code = 401;
		$message = "Unauthorized";
		$extras = NULL;

		try {
			$object = $this->serializer->deserialize($request->getContent(), $class, 'json', DeserializationContext::create()->setGroups($serializer_groups));

			if (!is_null($object)) {
				$violations = $this->validator->validate($object, null, $validation_groups);
				if (count($violations) !== 0) {
					foreach ($violations as $violation) {
						$extras[$violation->getPropertyPath()] = $violation->getMessage();
					}
				}
				else {
					$showId = $callBack($object);

					$this->entityManager->persist($object);
					$this->entityManager->flush();

					$code = 201;
					$message = substr(strrchr($class, "\\"), 1).' created successfully';
					if ($showId === True)
						$extras['id'] = $object->getId();
					else if (null !== $showId && !is_bool($showId))
						$extras['uuid'] = $showId;
					if (null !== $notificationCallBack)
						$notificationCallBack($object);
				}
			}
		} catch (UniqueConstraintViolationException $ex) {
			$extras['email'] = 'This value is already used.';
		} catch (\LogicException $ex) {
			$extras['type'] = 'This value should not be blank.';
		} catch (HttpException $ex) {
			$code = $ex->getStatusCode();
			$message = $ex->getMessage();
		} catch (\Exception $ex) {
			$code = 400;
			$message = $ex->getMessage();
		}
		
		return new JsonResponse([
			'code' => $code,
			'message' => $message,
			'extras' => $extras
		], $code);
	}

	public function update(Request $request, $class, $callBack, $validation_groups, $serializer_groups, $id)
	{
		$code = 401;
		$message = "Unauthorized";
		$extras = NULL;

		try {
			$data = json_decode($request->getContent(), true);
			$data['id'] = $id;
			$object = $this->serializer->deserialize(json_encode($data), $class, 'json', DeserializationContext::create()->setGroups($serializer_groups));

			if (!is_null($object)) {
				$violations = $this->validator->validate($object, null, $validation_groups);
				if (count($violations) !== 0) {
					foreach ($violations as $violation) {
						$extras[$violation->getPropertyPath()] = $violation->getMessage();
					}
				}
				else {
					$showId = $callBack($object);

					$this->entityManager->persist($object);
					$this->entityManager->flush();

					$code = 201;
					$message = substr(strrchr($class, "\\"), 1).' updated successfully';
					if ($showId === True)
						$extras['id'] = $object->getId();
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
