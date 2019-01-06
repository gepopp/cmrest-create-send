<?php
/**
 * Created by PhpStorm.
 * User: offic
 * Date: 31.12.2018
 * Time: 18:46
 */

namespace cmrest\admin;


abstract class CMREST_hook {

    /**
     * @var array instances of instantiated Hook child classes
     */
    private static $CMREST_hook_instance = [];


    /**
     * CMREST_Hook constructor. Prevent direct instantiation
     */
    protected function __construct() {}


    /**
     * @return mixed return child instance.
     */
    public static function CMREST_hook_get_instance()
    {
        $class = get_called_class();
        if (!isset(self::$CMREST_hook_instance[$class])) {
            self::$CMREST_hook_instance[$class] = new static();
        }
        return self::$CMREST_hook_instance[$class];
    }

    /**
     * prevent clone of hook classes
     */
    protected function __clone() {}


}