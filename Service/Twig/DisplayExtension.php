<?php

namespace EP\DisplayBundle\Service\Twig;

use EP\DisplayBundle\Annotation\Exclude;
use EP\DisplayBundle\Annotation\Expose;
use EP\DisplayBundle\Annotation\File;
use EP\DisplayBundle\Annotation\Image;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\Translation\TranslatorInterface;
use Doctrine\Common\Annotations\Reader;
use Twig_Environment;

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
     * @var Twig_Environment
     */
    private $twig;

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
     * @var array
     */
    private $bundleConfigs = [];

    /**
     * @var array
     */
    private $configs = [];

    /**
     * @var
     */
    private $normalizedEntity;

    public function __construct(
        TranslatorInterface $translator,
        Reader $reader,
        Twig_Environment $twig,
        $imageRender,
        $fileRender,
        $template,
        $excludeVars,
        $arrayCollectionRender,
        $collectionItemCount
    )
    {
        $this->translator       = $translator;
        $this->reader           = $reader;
        $this->twig             = $twig;
        $this->bundleConfigs    = [
            'image_render'              => $imageRender,
            'file_render'               => $fileRender,
            'template'                  => $template,
            'exclude_vars'              => $excludeVars,
            'array_collection_render'   => $arrayCollectionRender,
            'collection_item_count'     => $collectionItemCount,
        ];
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
        if(!method_exists($entity, 'display')){
            throw new Exception('Please create an public display method for object');
        }
        $this->entity = $entity;
        $this->setupBundleConfigs();
        $this->setupAnnotationOptions();
        $this->normalizeConfigurations();
    }

    private function setupBundleConfigs($options = [])
    {
        $this->configs['image_render'] = $this->bundleConfigs['image_render'];
        $this->configs['file_render'] = $this->bundleConfigs['file_render'];
        $this->configs['template'] = $this->bundleConfigs['template'];
        $this->configs['exclude_vars'] = $this->bundleConfigs['exclude_vars'];
        $this->configs['array_collection_render'] = $this->bundleConfigs['array_collection_render'];
        $this->configs['collection_item_count'] = $this->bundleConfigs['collection_item_count'];
    }

    private function normalizeConfigurations()
    {
    }

    /**
     * @param $var
     */
    private function addExcludeVar($var)
    {
        if(!in_array($var, $this->excludeVars)){
            $this->excludeVars[] = $var;
            return;
        }
        return;
    }

    /**
     * @param $var
     */
    private function addExposeVar($var)
    {
        if(!in_array($var, $this->exposeVars)){
            $this->exposeVars[] = $var;
            return;
        }
        return;
    }

    public function getName()
    {
        return 'ep_display_extension';
    }
}
