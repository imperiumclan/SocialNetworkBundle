<?php

namespace ICS\SocialNetworkBundle\Controller;

use ICS\MediaBundle\Entity\MediaFile;
use ICS\MediaBundle\Entity\MediaImage;
use ICS\MediaBundle\Form\Type\MediaFileType;
use ICS\SocialNetworkBundle\Entity\Instagram\InstagramAccount;
use ICS\SocialNetworkBundle\Service\InstagramClient;
use ICS\SocialNetworkBundle\Service\TwitterClient;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class InstagramController extends AbstractController
{
    /**
     * @Route("/instagram" , name="ics_social_instagram_homepage")
     */
    public function index(Request $request,InstagramClient $client)
    {
        $search = $request->get('search');
        $result = array();

        if($search!=null)
        {
            $result=$client->search($search);
        }

        $accounts=$this->getDoctrine()->getRepository(InstagramAccount::class)->findAll();

        return $this->render('@SocialNetwork/instagram/index.html.twig',array(
            "debug" => $result,
            "search" => $search,
            "accounts" => $accounts
        ));

    }

    /**
     * @Route("/instagram/{id}" , name="ics_social_instagram_account")
     */
    public function showAccount(InstagramClient $client, $id)
    {

        $result=$client->getAccount($id);

        $client->updateAccount($result);

        return $this->render('@SocialNetwork/instagram/show.html.twig',array(
            "result" => $result,
        ));

    }


}