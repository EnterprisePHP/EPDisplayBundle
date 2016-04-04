<?php

namespace EP\DisplayBundle\Service\Twig;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Collections\ArrayCollection;
use EP\DisplayBundle\Annotation\Display;
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
     * @var \ReflectionClass
     */
    private $reflEntity;

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
        $this->setupTemplateOptions($options);
        $this->normalizedEntity = $this->normalizeEntity();
    }

    private function setupBundleConfigs()
    {
        $this->configs['image_render'] = $this->bundleConfigs['image_render'];
        $this->configs['file_render'] = $this->bundleConfigs['file_render'];
        $this->configs['template'] = $this->bundleConfigs['template'];
        foreach($this->bundleConfigs['exclude_vars'] as $exclude_var){
            $this->addExcludeVar($exclude_var);
        }
        $this->configs['array_collection_render'] = $this->bundleConfigs['array_collection_render'];
        $this->configs['collection_item_count'] = $this->bundleConfigs['collection_item_count'];
    }

    private function setupAnnotationOptions()
    {
        $this->reflEntity = new \ReflectionClass($this->entity);
        foreach($this->reflEntity->getProperties() as $property){
            foreach($this->reader->getPropertyAnnotations($property) as $annotation){
                if($annotation instanceof Exclude){
                    $this->addExcludeVar($property->name);
                } elseif ($annotation instanceof Expose){
                    $this->addExposeVar($property->name);
                    $this->excludeVars = array_diff($this->excludeVars, $this->exposeVars);
                } elseif ($annotation instanceof File){
                    $file['path'] = $annotation->getPath();
                    $this->files[$property->name] = $file;
                } elseif ($annotation instanceof Image){
                    $image['path'] = $annotation->getPath();
                    $this->images[$property->name] = $image;
                }
            }
        }

        $reader = new AnnotationReader();
        /** @var Display $displayAnnotation */
        $displayAnnotation = $reader->getClassAnnotation($this->reflEntity, 'EP\\DisplayBundle\\Annotation\\Display');
        if($displayAnnotation === null){
            return;
        }
        if($displayAnnotation->image_render !== null){
            $this->configs['image_render'] = $displayAnnotation->image_render;
        }
        if($displayAnnotation->file_render !== null){
            $this->configs['file_render'] = $displayAnnotation->file_render;
        }
        if($displayAnnotation->array_collection_render !== null){
            $this->configs['array_collection_render'] = $displayAnnotation->array_collection_render;
        }
        if($displayAnnotation->collection_item_count !== null){
            $this->configs['collection_item_count'] = $displayAnnotation->collection_item_count;
        }
        if($displayAnnotation->template !== null){
            $this->configs['template'] = $displayAnnotation->template;
        }
    }

    /**
     * @param array $options
     */
    private function setupTemplateOptions($options = [])
    {
        if(isset($options['files'])){
            if(is_array($options['files'])){
                $this->files = array_merge($this->files, $options['files']);
            }else{
                throw new Exception('files option must be an array');
            }
        }
        if(isset($options['images'])){
            if(is_array($options['images'])){
                $this->images = array_merge($this->images, $options['images']);
            }else{
                throw new Exception('images option must be an array');
            }
        }
        if(isset($options['exclude'])){
            if(is_array($options['exclude'])){
                $this->excludeVars = array_merge($this->excludeVars, $options['exclude']);
            }elseif(is_string($options['exclude'])){
                $this->excludeVars[] = $options['exclude'];
            }else{
                throw new Exception('exclude option must be array or string');
            }
        }
        if(isset($options['expose'])){
            if(is_array($options['expose'])){
                $this->exposeVars = array_merge($this->exposeVars, $options['expose']);
            }elseif(is_string($options['expose'])){
                $this->exposeVars[] = $options['expose'];
            }else{
                throw new Exception('expose option must be array or string');
            }
            foreach($this->exposeVars as $expose){
                if(in_array($expose, $this->excludeVars)){
                    $this->excludeVars = array_diff($this->excludeVars, $this->exposeVars);
                }
            }
        }

        $this->configs['image_render'] = isset($options['image_render']) ? $options['image_render'] : $this->configs['image_render'];
        $this->configs['file_render'] = isset($options['file_render']) ? $options['file_render'] : $this->configs['file_render'];
        $this->configs['array_collection_render'] = isset($options['array_collection_render']) ? $options['array_collection_render'] : $this->configs['array_collection_render'];
        $this->configs['collection_item_count'] = isset($options['collection_item_count']) ? $options['collection_item_count'] : $this->configs['collection_item_count'];
        $this->configs['template'] = isset($options['template']) ? $options['template'] : $this->configs['template'];
    }

    /**
     * @return mixed
     */
    private function normalizeEntity()
    {
        $this->normalizedEntity = $this->entity->display();
        $this->customNormalize();
        foreach ($this->normalizedEntity as $fieldName => $fieldValue) {
            if (in_array($fieldName, $this->excludeVars)) {
                unset($this->normalizedEntity[$fieldName]);
                continue;
            } elseif (empty($fieldValue)) {
                $this->normalizedEntity[$fieldName] = '-';
                continue;
            }
            if (is_bool($fieldValue)) {
                $this->normalizeBool($fieldName);
            }
            if (is_object($fieldValue)) {
                $this->normalizeObject($fieldName);
            }
        }
        return $this->normalizedEntity;
    }

    private function customNormalize()
    {
        $this->normalizeFiles();
        $this->normalizeImages();

        return;
    }

    private function normalizeFiles()
    {
        if(empty($this->files)){
            return;
        }
        foreach($this->files as $fileKey => $file){
            if(!array_key_exists($fileKey, $this->normalizedEntity)){
                throw new Exception('This file field not exists!');
            }
            if(!empty($this->normalizedEntity[$fileKey])) {
                if($this->configs['file_render'] == true){
                    $this->normalizedEntity[$fileKey] = '<a href="' . $this->files[$fileKey]["path"] . '/' . $this->normalizedEntity[$fileKey] . '" target="_blank">' . $this->normalizedEntity[$fileKey] . '</a>';
                }
            }else{
                $this->normalizedEntity[$fileKey] = '-';
            }
        }
    }

    private function normalizeImages()
    {
        if(empty($this->images)){
            return;
        }
        foreach($this->images as $imageKey => $image){
            if(!array_key_exists($imageKey, $this->normalizedEntity)){
                throw new Exception('This image field not exists!');
            }
            if(!empty($this->normalizedEntity[$imageKey])) {
                if($this->configs['image_render'] == true){
                    $filteredImage = $image['path'].$this->normalizedEntity[$imageKey];
                    $this->normalizedEntity[$imageKey] = '<a href="'.$filteredImage.'" target="_blank"><img src="'.$filteredImage.'"/></a>';
                }
            }else{
                $this->normalizedEntity[$imageKey] = '-';
            }
        }
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

    private function normalizeBool($fieldName)
    {
        if($this->normalizedEntity[$fieldName]){
            $this->normalizedEntity[$fieldName] = '<i class="fa fa-check-circle-o" style="color:green"></i>';
        }else{
            $this->normalizedEntity[$fieldName] = '<i class="fa fa-times" style="color:red"></i>';
        }
    }

    private function normalizeObject($fieldName)
    {
        $fieldValue = $this->normalizedEntity[$fieldName];
        if(method_exists($fieldValue, '__toString')){
            $this->normalizedEntity[$fieldName] = (string)$fieldValue;
        }
        if($fieldValue instanceof ArrayCollection && $this->configs['array_collection_render'] == true){
            $counter = 0;
            foreach($fieldValue as $collectionObject){
                if($counter >= $this->configs['collection_item_count']){
                    continue;
                }
                if(method_exists($collectionObject, '__toString')){
                    $objectToString = (string)$collectionObject;
                    if(is_object($this->normalizedEntity[$fieldName])){
                        $this->normalizedEntity[$fieldName] = $objectToString;
                    }else{
                        $this->normalizedEntity[$fieldName].= '<br>'.$objectToString;
                    }
                    $counter = $counter+1;
                }
            }
        }elseif($fieldValue instanceof ArrayCollection && $this->configs['array_collection_render'] == false){
            unset($this->normalizedEntity[$fieldName]);
        }
        if($fieldValue instanceof \DateTime){
            $this->normalizedEntity[$fieldName] = $fieldValue->format('Y-m-d H:i:s');
        }
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
