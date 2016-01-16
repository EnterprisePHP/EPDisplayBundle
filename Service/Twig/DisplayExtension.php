<?php

namespace EP\DisplayBundle\Service\Twig;

use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\Translation\TranslatorInterface;
use Doctrine\Common\Annotations\Reader;

class DisplayExtension extends \Twig_Extension
{
    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * @var Reader
     */
    private $reader;

    /**
     * exclude vars for basic entity
     * @var array
     */
    private $excludeVars = [];

    /**
     * expose vars for basic entity
     * @var array
     */
    private $exposeVars = [];

    /**
     * @var array
     */
    private $files = [];

    /**
     * @var array
     */
    private $images = [];

    /**
     * @var
     */
    private $entity;

    /**
     * @var
     */
    private $normalizedEntity;

    public function __construct(TranslatorInterface $translator, Reader $reader)
    {
        $this->translator = $translator;
        $this->reader = $reader;
    }

    public function getFunctions()
    {
        return array(
            new \Twig_SimpleFunction('display', array($this, 'getDisplay'), array('is_safe' => array('html'))),
        );
    }

    /**
     * @param $entity
     * @param array $options
     * @return string
     */
    public function getDisplay($entity, $options = array())
    {
        return true;
    }

    public function getName()
    {
        return 'ep_display_extension';
    }
}
