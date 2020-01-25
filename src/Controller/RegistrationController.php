<?php

namespace App\Controller;

use App\Service\FormHandler;
use App\Entity\User;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class RegistrationController extends AbstractController
{
    private $encoder;
	private $validator;
	private $form;

	public function __construct(UserPasswordEncoderInterface $encoder, ValidatorInterface $validator, FormHandler $form)
	{
		$this->encoder = $encoder;
		$this->validator = $validator;
		$this->form = $form;
	}

	public function doNothing($object)
    {
        return false;
    }

	public function setPassword($user)
	{
		$user->setPassword(
			$this->encoder->encodePassword(
				$user,
				$user->getPlainPassword()
			)
        );
        $user->eraseCredentials();

		return False;
	}

	/**
	 * @Route("/api/register", name="register_user", methods={"POST"})
	 */
	public function registerAction(Request $request)
	{
		$this->form->checkId($request);

		return $this->form->validate($request, User::class, array($this, 'setPassword'), array($this, 'doNothing'), ['new-user'], ['new-user']);
	}
}
