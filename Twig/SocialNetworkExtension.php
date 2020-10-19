<?php

namespace ICS\SocialNetworkBundle\Twig;

use Twig\Extension\AbstractExtension;
use Twig\Environment;
use Doctrine\ORM\EntityManagerInterface;
use ICS\MediaBundle\Entity\MediaFile;
use ICS\MediaBundle\Entity\MediaImage;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Twig\TwigFilter;

/**
 * NavBarExtension
 * @author David Dutas <david.dutas@ia.defensecdd.gouv.fr >
 */
class SocialNetworkExtension extends AbstractExtension
{
    private $doctrine;
    private $container;


    /**
     * Constructeur
     * @param RegistryInterface $doctrine
     */
    public function __construct(EntityManagerInterface $doctrine,ContainerInterface $container)
    {
        $this->doctrine = $doctrine;
        $this->container= $container;
    }

    public function getFilters()
    {
        return [
            new TwigFilter('mediaType', [$this,'MediaType'],array(
                'is_safe' => array('html'),
                'needs_environment' => true
            ))
        ];

    }

    public function getFunctions()
    {
        return [];
    }

    public function MediaType(Environment $env, $file)
    {
        if(is_a($file,MediaImage::class))
        {
            return 'image';
        }

        return 'file';
    }

}

