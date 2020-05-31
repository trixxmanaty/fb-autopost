<?php

namespace App\Controller;

use App\Entity\Config;
use App\Form\Type\ConfigType;
use App\Repository\ConfigRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;

class AdminController extends AbstractController
{
    public function index(Request $request, ConfigRepository $configRepository)
    {
        $config = $configRepository->findAll();
        $hasShortLivedToken = false;
        if(!empty($config)) {
            $config = $config[0];
            if($config->getFacebookLongLivedAccessToken()) {
                $hasShortLivedToken = true;
            }
        } else {
            $config = new Config();
        }

        $form = $this->createForm(ConfigType::class, $config);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $config = $form->getData();
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($config);
            $entityManager->flush();
            $this->addFlash('success', 'Edited successfully');
            return $this->redirectToRoute('admin_index');
        }

        return $this->render('admin/config.html.twig', [
            'form' => $form->createView(),
            'hasShortLivedToken' => $hasShortLivedToken,
        ]);
    }

    public function login()
    {
        return $this->redirectToRoute('app_login');
    }

    public function generateLongLivedFbToken(ConfigRepository $configRepository)
    {
        $config = $configRepository->findAll();
        if(empty($config)) {
            throw new \Exception('Unable to find required value');
        }

        $config = $config[0];

        $shortLivedToken = $config->getFacebookLongLivedAccessToken();
        $clientId = $config->getFacebookAppId();
        $secret = $config->getFacebookSecret();

        if(null === $shortLivedToken || null === $clientId || null === $secret) {

            $this->addFlash('danger', 'The short lived token OR Facebook app id OR Facebook APP Secret is missing!');
            return $this->redirectToRoute('admin_index');
        }


        $url = 'https://graph.facebook.com/oauth/access_token?grant_type=fb_exchange_token&client_id=%s&client_secret=%s&fb_exchange_token=%s';

        $url = sprintf($url, $clientId, $secret, $shortLivedToken);

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        // Timeout in seconds
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);

        $response = curl_exec($ch);

        curl_close($ch);

        if(!$response) {
            $this->addFlash('danger', 'Unable to complete Facebook API call');
            return $this->redirectToRoute('admin_index');
        }

        $response = json_decode($response, true);

        if(json_last_error() !== JSON_ERROR_NONE) {
            $this->addFlash('danger', 'Facebook responded with an unknown error');
            return $this->redirectToRoute('admin_index');
        }

        $accessToken = $response['access_token'];

        $url = 'https://graph.facebook.com/%s?fields=access_token&access_token=%s';

        $url = sprintf($url, $config->getFacebookPageId(), $accessToken);

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        // Timeout in seconds
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);

        $response = curl_exec($ch);
        curl_close($ch);

        if(!$response) {
            $this->addFlash('danger', 'Unable to complete Facebook API call');
            return $this->redirectToRoute('admin_index');
        }

        $response = json_decode($response, true);

        if(json_last_error() !== JSON_ERROR_NONE) {
            $this->addFlash('danger', 'Facebook responded with an unknown error');
            return $this->redirectToRoute('admin_index');
        }

        $accessToken = $response['access_token'];

        $config->setFacebookPageToken($accessToken);

        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($config);
        $entityManager->flush();

        return $this->redirectToRoute('admin_index');
    }
}