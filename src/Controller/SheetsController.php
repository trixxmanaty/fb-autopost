<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\EncoderAwareInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class SheetsController extends AbstractController
{
    /**
     * @Route("/sheets", name="sheets")
     */
    public function index(UserPasswordEncoderInterface $passwordEncoder, EntityManagerInterface $objectManager)
    {
        $user = new User();
        $user->setUsername('admin');
        $user->setPassword(
            $passwordEncoder->encodePassword(
                $user,
                'test'
            )
        );
        $user->setRoles(['ROLE_ADMIN']);

        $objectManager->persist($user);
        $objectManager->flush();


        return new Response('Sucessful');
    }
}
