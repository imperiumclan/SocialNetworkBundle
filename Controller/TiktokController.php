<?php

namespace ICS\SocialNetworkBundle\Controller;

use ICS\MediaBundle\Entity\MediaFile;
use ICS\MediaBundle\Entity\MediaImage;
use ICS\MediaBundle\Form\Type\MediaFileType;
use ICS\SocialNetworkBundle\Entity\Instagram\InstagramAccount;
use ICS\SocialNetworkBundle\Service\InstagramClient;
use ICS\SocialNetworkBundle\Service\TiktokClient;
use ICS\SocialNetworkBundle\Service\TwitterClient;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class TiktokController extends AbstractController
{
    /**
     * @Route("/tiktok" , name="ics_social_tiktok_homepage")
     */
    public function index(Request $request,TiktokClient $client)
    {
        $search = $client->getItems("yelloz");

        return $this->render('@SocialNetwork/tiktok/index.html.twig',array(
            'search' => $search
        ));
    }

}