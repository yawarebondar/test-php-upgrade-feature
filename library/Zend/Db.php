<?php

/**
 * Created by PhpStorm.
 * User: vmelnychuk
 * Date: 07.09.16
 * Time: 12:35
 */

/**
 * Class Yaware_Zend_Db. CLASS DEPRECATED!
 */
class Yaware_Zend_Db extends Zend_Db
{

    public static function factory($adapter, $config = array())
    {
        if (Yaware_Common::isPHPUnitEnv()) {
            $config['adapterNamespace'] = 'Yaware_Zend';
        }
        return parent::factory($adapter, $config);
    }
}