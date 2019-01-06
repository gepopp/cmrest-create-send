<?php
/**
 * Created by PhpStorm.
 * User: offic
 * Date: 02.01.2019
 * Time: 08:22
 */

namespace cmrest\admin;

/**
 * Class CMREST_Ajax handles all Ajax requests of the plugin
 * @package cmrest\admin
 */
class CMREST_ajax extends CMREST_hook {


    /**
     * @var holds the campaign settings option
     */
    protected $CMREST_campaign_settings;



    /**
     * Function called by the wp_ajax_ hook which delegates to the call method
     *
     */
    public function CMREST_ajax_request()
    {

        $this->CMREST_campaign_settings = get_option( CMREST_api_campaign_options_page::CMREST_get_option_name() );
        $call = sanitize_text_field($_POST['call']);


        if ( ! isset( $call ) || ! method_exists('\cmrest\admin\CMREST_campaign', $call )) {
            $this->CMREST_ajax_error('method');
        }

        //call method with sanitized data
        $campaign = new CMREST_campaign($this->CMREST_campaign_settings);
        $campaign->{$call}($this->CMREST_ajax_simplify_and_sanitize());


    }

    /**
     * sanitizes the data send through ajax post
     *
     * @since 0.2.0
     * @TODO implement error response
     * @return mixed the sanitized data
     */
    public function CMREST_ajax_simplify_and_sanitize()
    {

        // data needs to be a arra since its generated through jquery's serializeArray on the fieldset in the metabox
        if ( ! is_array($_POST['data']))  $this->CMREST_ajax_error('data');

        // simplify the array
        foreach ($_POST['data'] as $data) {
            $simple_data[filter_var( $data['name'], FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_HIGH)] = sanitize_text_field($data['value']);
        }


        // check the nonce
        if ( ! isset($simple_data['CMREST_ajax_nonce']) || ! wp_verify_nonce($simple_data['CMREST_ajax_nonce'], 'CMREST_create_campaign')) {
            $this->CMREST_ajax_error('nonce');
        }


        if (isset($simple_data['CMREST_create_post_id'])) {

            if (    is_numeric($simple_data['CMREST_create_post_id'])
                && ( get_post_status($simple_data['CMREST_create_post_id']) == 'future' || get_post_status($simple_data['CMREST_create_post_id']) == 'publish' )
                && in_array( get_post_type( $simple_data['CMREST_create_post_id']), $this->CMREST_campaign_settings['CMREST_campaigns_post_types'] )) {

                $sanitized_data['CMREST_create_post_id'] = $simple_data['CMREST_create_post_id'];
            } else {
                $this->CMREST_ajax_error('post type/status');
            }
        } else {
            $this->CMREST_ajax_error('post id');
        }

        if (isset($simple_data['CMREST_subject'])) {
            $sanitized_data['CMREST_subject'] = sanitize_text_field($simple_data['CMREST_subject']);
        }

        if (isset($simple_data['CMREST_email_title'])) {
            $sanitized_data['CMREST_email_title'] = sanitize_text_field($simple_data['CMREST_email_title']);
        }

        if (isset($simple_data['CMREST_email_text'])) {
            $sanitized_data['CMREST_email_text'] = sanitize_textarea_field($simple_data['CMREST_email_text']);
        }

        if(isset($simple_data['CMREST_campaign_id'])){
            $sanitized_data['CMREST_campaign_id'] = sanitize_text_field($simple_data['CMREST_campaign_id']);
        }

        if(isset($simple_data['CMREST_confirmation_to'])){
            $emails = explode(',', $simple_data['CMREST_confirmation_to'] );
            foreach($emails as $email){
                $sanitized[] = sanitize_email($email);
            }
            $sanitized_data['CMREST_confirmation_to'] = implode(',', $sanitized);
        }

        if(isset($simple_data['CMREST_schedule_draft'])){
            $sanitized_data['CMREST_schedule_draft'] = sanitize_text_field($simple_data['CMREST_schedule_draft']);
        }
        if(isset($simple_data['CMREST_preview_to'])){
            $emails = explode(',', $simple_data['CMREST_preview_to'] );
            foreach($emails as $email){
                $sanitized[] = sanitize_email($email);
            }
            $sanitized_data['CMREST_preview_to'] = $sanitized;
        }
        return $sanitized_data;
    }


    /**
     * echo new metabox content via ajax
     *
     * @since 0.2.0
     */
    public function CMREST_ajax_reload_metabox(){

        $refferer = parse_url(wp_get_referer());
        parse_str($refferer['query'], $query);

        global $post;
        $post = get_post($query['post']);

        $metabox = new CMREST_metabox();

        echo $metabox->CMREST_metabox_content();
        die();

    }


    /**
     * Get the current post excerpt
     * second ajax call after metabox refresh
     * if campaign is deleted and form for creation is showen
     * to populate wp_editor
     *
     * @since 0.2.0
     */
    public function CMREST_ajax_get_excerpt(){

        $refferer = parse_url(wp_get_referer());
        parse_str($refferer['query'], $query);
        global $post;
        $post = get_post($query['post']);
        setup_postdata( $post );
        echo get_the_excerpt();
        die();
    }


    /**
     * return the error message
     *
     *
     * @param $reason
     */
    protected function CMREST_ajax_error($reason){

        wp_die(
            sprintf(__('Cant resolve request. Reason: %s', 'cmrest'), $reason),
            null,
            400
        );

    }


}