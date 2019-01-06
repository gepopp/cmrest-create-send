<?php
/**
 * Created by PhpStorm.
 * User: offic
 * Date: 30.12.2018
 * Time: 21:34
 */

namespace cmrest\admin;

use cmrest\admin\CMREST_hook;

class CMREST_api_key_option_page extends CMREST_hook {


    use CMREST_option_fields_html;

    /**
     * @mixed Holds the option with the API KEY and Client ID Array
     */
    private $CMREST_api_keys;


    /**
     * @const string slug of the page
     */
    const CMREST_page_slug = 'cmrest-settings';


    /**
     * @var string the option name
     */
    const CMREST_option_name = 'CMREST_api_keys';


    /**
     * Add the Plugin main menu page with the input fields for API Key and Client ID
     */
    public function CMREST_add_option_pages()
    {

        add_menu_page( 'CMREST Admin', __('Campaign Monitor', 'cmrest'), 'manage_options', self::CMREST_get_page_slug(), [$this, 'CMREST_create_keys_page'], 'dashicons-email' );
    }


    /**
     * Register the CSREST_api_key_option and the settings section and fields
     */
    public function CMREST_page_init(){

        $this->CMREST_api_keys = get_option(self::CMREST_get_option_name());


        $api_key = $this->CMREST_api_keys['CMREST_api_key'];
        $client_id = $this->CMREST_api_keys['CMREST_client_id'];


        register_setting( 'CMREST_api_keys_group', self::CMREST_get_option_name(), [$this, 'CMREST_sanitize_api_keys']);

        add_settings_section('CMREST_api_keys_section', __('Campaign Monitor API Keys', 'cmrest'), [$this, 'CMREST_print_section_info_keys'], self::CMREST_get_page_slug());

        add_settings_field('CMREST_client_id', __('Client ID', 'cmrest'), [$this, 'CMREST_option_text_field'], self::CMREST_get_page_slug(), 'CMREST_api_keys_section',
            [
            'type'  => 'text',
            'id'    => 'CMREST_client_id',
            'option'=> self::CMREST_get_option_name(),
            'value' => isset($client_id) ? $client_id : ''
            ]);

        add_settings_field('CMREST_api_key', __('API KEY', 'cmrest'), [$this, 'CMREST_option_text_field'], self::CMREST_get_page_slug(), 'CMREST_api_keys_section',
            [
                'type'  => 'text',
                'id'    => 'CMREST_api_key',
                'option'=> self::CMREST_get_option_name(),
                'value' => isset($api_key) ? $api_key : ''
            ]);

    }





    /**
     * Output the basic HTML for the options form
     */
    public function CMREST_create_keys_page()
    {
        settings_errors('CMREST_api_keys_validation_errors');
        ?>
        <div class="wrap">
            <h1><?= __('API Key Settings', 'cmrest') ?></h1>
            <form method="post" action="options.php">
                <?php
                settings_fields('CMREST_api_keys_group');
                do_settings_sections(self::CMREST_get_page_slug());
                submit_button();
                ?>
            </form>
        </div>
        <?php
    }




    /**
     * Add a Section Header
     */
    public function CMREST_print_section_info_keys()
    {
        print __('API Key and Client ID from Campaign Monitor', 'cmrest');
    }


    /**
     * Sanitize the API key and Client ID input
     */
    public function CMREST_sanitize_api_keys($input){

        if(isset($input['CMREST_client_id'])){
            $sanitized_input['CMREST_client_id']    = sanitize_text_field($input['CMREST_client_id']);
        }

        if(isset($input['CMREST_api_key'])){
            $sanitized_input['CMREST_api_key']      = sanitize_text_field($input['CMREST_api_key']);
        }

        if(isset($input['CMREST_api_key']) && isset($input['CMREST_client_id'])){
            $this->CMREST_validate_API_Key_and_Client_ID($input);
        }

        return $sanitized_input;
    }


    private function CMREST_validate_API_Key_and_Client_ID($input){

            $api_call = new CMREST_api_communication();
            $validate = $api_call->CMREST_validate_api_keys($input['CMREST_api_key'], $input['CMREST_client_id']);
            if(!$validate){
                add_settings_error( 'CMREST_api_keys_validation_errors', esc_attr( 'settings_updated' ), __('Could not validate API Key and Client ID, please provide valid Key and ID.'), 'error' );
            }else{
                add_settings_error( 'CMREST_api_keys_validation_errors', esc_attr( 'settings_updated' ), __('Connection to Campaign Monitor successful, you can create and send now.'), 'updated' );
            }


    }

    /**
     * @return string
     */
    public static function CMREST_get_page_slug()
    {
        return self::CMREST_page_slug;
    }

    /**
     * @return string
     */
public static function CMREST_get_option_name()
    {
        return self::CMREST_option_name;
    }

}