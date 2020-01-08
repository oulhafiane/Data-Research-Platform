<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Searcher;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;

class TokenController extends AbstractController
{
    private $em;
    private $JWTManager;
    private $encoder;

    public function __construct(UserPasswordEncoderInterface $encoder, EntityManagerInterface $em, JWTTokenManagerInterface $JWTManager)
    {
        $this->em = $em;
        $this->JWTManager = $JWTManager;
        $this->encoder = $encoder;
    }

    /**
     * @Route("/api/oauth", name="oauth", methods={"POST"})
     */
    public function oAuthAction(Request $request)
    {
        $data = json_decode($request->getContent(), true);
        if (null == $data)
            throw $this->createAccessDeniedException();
        if (null !== $data && !array_key_exists('token', $data))
            throw $this->createAccessDeniedException();
        if (null !== $data && !array_key_exists('provider', $data))
            throw $this->createAccessDeniedException();

        $client = new \Google_Client(['client_id' => $this->getParameter('google_id')]);
        $payload = $client->verifyIdToken($data['token']);
        if ($payload) {
            // var_dump($payload);
            // $userid = $payload['sub'];
            $user = $this->em->getRepository(User::class)->findOneBy(['email' => $payload['email']]);
            if (null === $user) {
                $user = new Searcher();
                $user->setFirstName($payload['given_name']);
                $user->setLastName($payload['family_name']);
                $user->setPassword(
                    $this->encoder->encodePassword(
                        $user,
                        random_bytes(15)
                    )
                );
                $user->eraseCredentials();
                $user->setEmail($payload['email']);
                try {
                    $this->em->persist($user);
                    $this->em->flush();
                } catch (\Exception $ex) {
                    throw new HttpException(400, $ex->getMessage());
                }
            }
            $now = time();
            return new JsonResponse([
                'token' => $this->JWTManager->create($user)
            ], 200);
            // If request specified a G Suite domain:
            //$domain = $payload['hd'];
        }

        return new JsonResponse([
            'code' => 401,
            'message' => "Unauthorized"
        ], 401);
    }
}
