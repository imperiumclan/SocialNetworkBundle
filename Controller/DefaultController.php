<?php

namespace ICS\SocialNetworkBundle\Controller;

use ICS\MediaBundle\Entity\MediaFile;
use ICS\MediaBundle\Entity\MediaImage;
use ICS\MediaBundle\Form\Type\MediaFileType;
use ICS\SocialNetworkBundle\Service\InstagramClient;
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
        $search = $request->get('search');
        $result = array();
        
        if($search!=null)
        {
            $result=$client->search($search);
        }


        return $this->render('@SocialNetwork/index.html.twig',array(
            "debug" => $result,
            "search" => $search
        ));
       
    }

    /**
     * @Route("/instagram/{id}" , name="ics_social_instagram_account")
     */
    public function showAccount(InstagramClient $client, $id)
    {

        $result=$client->getAccount($id);
        
        return $this->render('@SocialNetwork/show.html.twig',array(
            "result" => $result,
        ));
       
    }
}