<?php

namespace App\Controller;

use App\Service\FormHandler;
use App\Entity\User;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Doctrine\ORM\EntityManagerInterface;
use Ramsey\Uuid\Uuid;
use Lexik\Bundle\JWTAuthenticationBundle\Encoder\JWTEncoderInterface;

class RegistrationController extends AbstractController
{
    private $encoder;
	private $validator;
	private $form;
	private $em;
	private $JWTencoder;
	private $mailer;

	public function __construct(UserPasswordEncoderInterface $encoder, ValidatorInterface $validator, FormHandler $form, EntityManagerInterface $em, JWTEncoderInterface $JWTencoder, \Swift_Mailer $mailer)
	{
		$this->encoder = $encoder;
		$this->validator = $validator;
		$this->form = $form;
		$this->em = $em;
		$this->JWTencoder = $JWTencoder;
		$this->mailer = $mailer;
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

	/**
	 * @Route("/api/forgotPassword", name="forgot_password", methods={"POST"})
	 */
	public function forgotPasswordAction(Request $request)
	{
		$data = json_decode($request->getContent(), true);
		if (null !== $data && array_key_exists('id', $data))
			throw new HttpException(406, 'Field \'id\' not acceptable.');
		if (!array_key_exists('username', $data))
			throw new HttpException(406, 'Field \'username\' not found.');

		$username = $data['username'];
		$user = $this->em->getRepository(User::class)->findOneBy(['email' => $username]);
        if (null !== $user) {
			try {
				$recoveryToken = Uuid::uuid4()->toString();
				$user->setRecoveryToken($recoveryToken);
				$this->em->persist($user);
				$this->em->flush();
				$exp = $exp = new \DateTime();
				$exp->add(new \DateInterval('PT1H'));
				$jwtToken = $this->JWTencoder->encode(['email' => $user->getEmail(), 'token' => $recoveryToken, 'exp' => $exp->getTimestamp()]);
				
				$message = (new \Swift_Message('Resetting your password'))
				->setFrom('info@impactree.com')
				->setTo($user->getEmail())
				->setBody(
					$this->renderView(
						'emails/resetPassword.html.twig',
						[
							'username' => $user->getEmail(),
							'confirmationUrl' => "https://impactree.um6p.ma/resetPassword?token=".$jwtToken
						]
					),
					'text/html'
				);
				$this->mailer->send($message);
			} catch (\Exception $ex) {
				throw new HttpException(500, $ex->getMessage());
			}
		}

		return new JsonResponse([
            'code' => 200,
            'message' => "If you are registered, an email will be sent to you.",
        ], 200);
	}
}
