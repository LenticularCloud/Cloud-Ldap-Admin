<?php
namespace Cloud\LdapBundle\Entity\Ldap;


abstract class AbstractEntity
{

    /**
     * @var array
     */
    private $attributes=[];

    /**
     *
     * @return array
     *  [
     *  'uid' => 'scalar',
     *  'userPassword' => 'list'
     *  ]
     */
    abstract function getAttributeList();

    public function parseLdapData(array $data)
    {
        foreach ($this->getAttributeList() as $attr=>$type) {
            if (isset($data[$attr]) && isset($data[$attr]["count"]) && $data[$attr]["count"] > 0) {
                switch($type) {
                    case'scalar':
                        $this->attributes[$attr]=$data[$attr]["count"] > 0?$data[$attr][0]:null;
                        break;
                    case'list':
                        $this->attributes[$attr]=[];
                        for($i=0;$i<$data[$attr]["count"];$i++) {
                            $this->attributes[$attr][]=$data[$attr][$i];
                        }
                        break;
                }
            }
        }
    }

}