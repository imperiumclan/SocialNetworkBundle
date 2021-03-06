<?php

namespace ICS\SocialNetworkBundle\Controller;

use ICS\MediaBundle\Entity\MediaFile;
use ICS\MediaBundle\Entity\MediaImage;
use ICS\MediaBundle\Form\Type\MediaFileType;
use ICS\SocialNetworkBundle\Service\InstagramClient;
use ICS\SocialNetworkBundle\Service\TwitterClient;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DefaultController extends AbstractController
{
    /**
     * @Route("/" , name="ics_social_homepage")
     */
    public function index(Request $request,InstagramClient $client)
    {
       


        return $this->render('@SocialNetwork/index.html.twig',array(
       
        ));
       
    }



    /**
     * @Route("/twitter" , name="ics_social_twitter")
     */
    public function searchTwitter(TwitterClient $client)
    {
        $result="";
        $client->search('bellathorne');
        
        return $this->render('@SocialNetwork/show.html.twig',array(
            "result" => $result,
        ));
       
    }

    
}