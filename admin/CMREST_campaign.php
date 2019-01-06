<?php
/**
 * Created by PhpStorm.
 * User: offic
 * Date: 02.01.2019
 * Time: 09:20
 */

namespace cmrest\admin;


class CMREST_campaign {

    /**
     * @var CMREST_api_communication
     */
    protected $CMREST_api_communication;

    /**
     * @var option
     */
    protected $CMREST_campaign_settings;


    /**
     * @var CMREST_templates
     */
    protected $CMREST_template;


    /**
     * CMREST_Campaign constructor.
     */
    public function __construct($campaign_settings)
    {

        $this->CMREST_campaign_settings = $campaign_settings;
        $this->CMREST_api_communication = new CMREST_api_communication();
        $this->CMREST_template = new CMREST_templates();

    }





    /**
     * Create a new campaign draft at cm
     * Loads the template content array from the set content folder
     *
     * @param $campaign_data
     */
    public function CMREST_create_campaign($campaign_data)
    {


        //check template and get the CM id
        $template_id = $this->CMREST_api_communication->CMREST_template_exists($this->CMREST_campaign_settings['CMREST_available_template']);
        if ( ! $template_id) {
            wp_die(sprintf(__('Could not locate your Template on Campaign monitor please update your <a href="%s">preferences</a>'), admin_url('admin.php?page=cmrest-campaign-settings')), 400);
        }

        //create content
        if ( ! file_exists(CMREST_PATH . 'templates/' . $this->CMREST_campaign_settings['CMREST_available_template'] . '/template_content.php')) {
            wp_die(__('Could not locate Template content file.'), 400);
        }


        global $post;
        $post = get_post($campaign_data['CMREST_create_post_id']);

        $template_content = include_once(CMREST_PATH . 'templates/' . $this->CMREST_campaign_settings['CMREST_available_template'] . '/template_content.php');

        $campaign_settings = [

            'Subject' => $campaign_data['CMREST_subject'] != '' ? $campaign_data['CMREST_subject'] : get_the_title(),
            'Name' => $campaign_data['CMREST_subject'] . sprintf(esc_html__('Created from Wordpress at: %s'), date('H:i:s d.m.Y'), 'cmoc'),
            'FromName' => $this->CMREST_campaign_settings['CMREST_campaings_sender_name'],
            'FromEmail' => $this->CMREST_campaign_settings['CMREST_campaings_sender_email'],
            'ReplyTo' => $this->CMREST_campaign_settings['CMREST_campaings_reply_email'],
            'ListIDs' => [$this->CMREST_campaign_settings['CMREST_campaigns_subscribers'],],
            'TemplateID' => $template_id,
            'TemplateContent' => $template_content,
        ];


        /** @var TYPE_NAME $campaign_settings */
        $campaign_id = $this->CMREST_api_communication->CMREST_create_campaign($campaign_settings);

        update_post_meta($post->ID, 'CMREST_sent_campaign_id', $campaign_id);

        wp_die(__('Campaign successfully drafted', 'cmrest'));

    }





    /**
     * @param $campaign_data
     */
    public function CMREST_send_campaign($campaign_data)
    {
        $this->CMREST_api_communication->CMREST_send_or_schedule($campaign_data['CMREST_campaign_id'], $campaign_data['CMREST_confirmation_to'], $campaign_data['CMREST_schedule_draft']);
        wp_die(__('Draft successfully scheduled or sent', 'cmrest'));

    }


    /**
     * delete a draft via CMREST_communicator
     *
     *
     * @param mixed $campaign_data
     */
    public function CMREST_delete_draft($campaign_data)
    {
        $this->CMREST_api_communication->CMREST_delete_campaign($campaign_data['CMREST_campaign_id']);
        wp_die(__('Successfully deleted', 'cmrest'));
    }


    /**
     * send a preview via CMREST_api_communicator
     *
     * @param mixed $campaign_data
     */
    public function CMREST_send_preview($campaign_data)
    {
        $this->CMREST_api_communication->CMREST_send_preview($campaign_data['CMREST_campaign_id'], $campaign_data['CMREST_preview_to']);
        wp_die(__('Successfully sent', 'cmrest'));
    }


    /**
     * move a scheduled campaign to drafts
     * via CMREST_api_communicator
     *
     * @param $campaign_data
     */
    public function CMREST_campaign_move_to_draft($campaign_data)
    {
        $this->CMREST_api_communication->CMREST_campaign_move_to_draft($campaign_data['CMREST_campaign_id']);
        wp_die(__('Successfully moved to draft', 'cmrest'));
    }


}