<?php
/**
 * Created by PhpStorm.
 * User: offic
 * Date: 30.12.2018
 * Time: 20:31
 */
namespace cmrest\admin;

class CMREST_boot {


    public function __construct()
    {

        $this->CMREST_load_hooks();

    }


    /**
     * load all the WordPress Actions an Filters as defined in the @class CSREST_Hooks contructor
     *
     * @since 0.2.0
     * @return void
     */
    public function CMREST_load_hooks(){

        $hooks = new CMREST_hooks_loader();
        $hooks->run();

    }





}