<?php
/**
 * Created by PhpStorm.
 * User: offic
 * Date: 31.12.2018
 * Time: 22:52
 */

namespace cmrest\admin;


class CMREST_scripts_and_styles extends CMREST_hook {


    public function CSREST_admin_enqueue(){

        wp_enqueue_script('CMREST_admin_script', CMREST_URL . 'js/cmrest_admin_script.js', ['jquery', 'wp-color-picker'], CMREST_VERSION );

        wp_enqueue_style('lds-loader', CMREST_URL . 'css/lds-roller.css');
        wp_enqueue_style('CMREST_admin_style', CMREST_URL . 'css/CMREST_admin.css');

    }


}