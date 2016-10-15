<?php
/**
 * Created by PhpStorm.
 * User: tuxcoder
 * Date: 9/26/16
 * Time: 2:05 PM
 */

namespace Cloud\FrontBundle\Services;


use Cloud\FrontBundle\Form\Type\ServiceType;

class FromGenerator
{

    /**
     * @var array
     */
    private $mainSettings;

    /**
     * @var array
     */
    private $serviceSettings;

    public function __construct(array $mainSettings, array $serviceSettings)
    {
        $this->mainSettings = $mainSettings;
        $this->serviceSettings = $serviceSettings;
    }

    public function getUserForms()
    {
        $object_forms = $this->mainSettings['object_forms'];
        $forms = array();
        foreach($object_forms as $object_form) {
            $form = new $object_form();
            $forms[$form->getName()] = $form;
        }
        return $forms;
    }

    public function getServiceForms($serviceName)
    {
        // add default form for all services
        $forms = array('ServiceType::class)' => new ServiceType());

        // read form from config file
        $object_forms = $this->serviceSettings[$serviceName]['object_forms'];
        foreach($object_forms as $object_form) {
            $form = new $object_form();
            $forms[$form->getName()] = $form;
        }
        return $forms;
    }

}