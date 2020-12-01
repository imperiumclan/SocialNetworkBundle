<?php

namespace ICS\SocialNetworkBundle\Twig;

use Doctrine\ORM\EntityManagerInterface;
use ICS\SocialNetworkBundle\Entity\Instagram\InstagramSideCar;
use ICS\SocialNetworkBundle\Entity\Instagram\InstagramVideo;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Twig\Environment;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

/**
 * NavBarExtension.
 *
 * @author David Dutas <david.dutas@ia.defensecdd.gouv.fr >
 */
class SocialNetworkExtension extends AbstractExtension
{
    private $doctrine;
    private $container;

    /**
     * Constructeur.
     *
     * @param RegistryInterface $doctrine
     */
    public function __construct(EntityManagerInterface $doctrine, ContainerInterface $container)
    {
        $this->doctrine = $doctrine;
        $this->container = $container;
    }

    public function getFilters()
    {
        return [
            new TwigFilter('instagramMedia', [$this, 'instagramMedia'], [
                'is_safe' => ['html'],
                'needs_environment' => true,
            ]),
        ];
    }

    public function getFunctions()
    {
        return [];
    }

    public function instagramMedia(Environment $env, $media)
    {
        switch (get_class($media)) {
            case InstagramVideo::class:
                $view = '@SocialNetwork/medias/instagram/video.html.twig';
            break;
            case InstagramSideCar::class:
                $view = '@SocialNetwork/medias/instagram/sidecar.html.twig';
            break;
            default:
                $view = '@SocialNetwork/medias/instagram/image.html.twig';
            break;
       }

        return $env->render($view, [
           'media' => $media,
       ]);
    }
}
