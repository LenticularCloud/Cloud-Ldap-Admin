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
        return $this->mainSettings['object_forms'];
    }

    public function getServiceForms($serviceName)
    {
        return array_merge([ServiceType::class], $this->serviceSettings[$serviceName]['object_forms']);
    }

}