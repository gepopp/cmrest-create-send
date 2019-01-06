<?php

namespace cmrest\admin;

/**
* Class CMREST_metabox
* Output the metabox content depending on the current post campaign status
*
* @package cmrest\admin
*/
class CMREST_metabox extends CMREST_hook {



    /**
    *
    * @since 0.2.0
    * @var mixed template and sender settings from the option
    */
    protected $CMREST_campaign_settings;


    /**
    *
    * @since 0.2.0
    * @var CMREST_api_communication
    */
    protected $CMREST_api_communication;






    function __construct()
    {
        $this->CMREST_campaign_settings = get_option('CMREST_campaign_settings');
        $this->CMREST_api_communication = new CMREST_api_communication();

    }




    /**
    *
    * @since 0.2.0
    * Add the Metabox to the post types defined in the settings
    */
    public function CMREST_add_metabox()
    {
        foreach ($this->CMREST_campaign_settings['CMREST_campaigns_post_types'] as $post_type) {
            add_meta_box('CMREST_Metabox_outer', __('Create and send Campaign', 'cmrest'), [$this, 'CMREST_metabox_content'], $post_type);
        }
    }





    /**
    * Ads Content to the Metabox
    * This function is called directly on Ajax refresh
    *
    * @since 0.2.0
    */
    public function CMREST_metabox_content()
    {
        //opening html and laoder overlay
        $this->CMREST_metabox_start();

        if ( ! $this->CMREST_api_communication->CMREST_validate_saved_api_keys() ) {

            $this->CMREST_metabox_keys_notice();
        }else{
            $this->CMREST_determine_post_campaign_status();
        }

        //closing metabox content tag
        $this->CMREST_metabox_end();
    }



    /**
    * Determines the campaign status by the CM Id saved in the posts meta
    * shows the content depending on the status
    *
    * @since 0.2.0
    */
    public function CMREST_determine_post_campaign_status(){

        global $post;
        $campaign = get_post_meta($post->ID, 'CMREST_sent_campaign_id', true);


        if( empty($campaign) || ! $status = $this->CMREST_api_communication->CMREST_campaign_exists($campaign) ){
            $this->CMREST_create_campaign_from_post($post);
        }else{
            $show = 'CMREST_show_' . $status . '_campaign';
            $this->{$show}($post, $campaign);
        }

    }


    /**
    * Html output form to create campaign from post data
    *
    * @since 0.2.0
    * @param $post WP post obejct
    */
    public function CMREST_create_campaign_from_post($post){

        ?>
        <fieldset class="CMREST_campaign_fields">
            <? wp_nonce_field( 'CMREST_create_campaign', 'CMREST_ajax_nonce' ); ?>
            <input type="hidden" name="CMREST_create_post_id" value="<?= $post->ID ?>">
            <p>
                <label class="CMREST_label" for="CMREST_subject_input"><?= __('E-Mail Subject', 'cmrest') ?></label>
                <input class="CMREST_input" type="text" name="CMREST_subject" id="CMREST_subject_input" placeholder="<?= __('Leave blank to use post title as subject', 'cmrest') ?>">
            </p>
            <p>
                <label class="CMREST_label" for="CMREST_email_title_input"><?= __('Title in E-Mail', 'cmrest') ?></label>
                <input class="CMREST_input" type="text" name="CMREST_email_title" id="CMREST_email_title_input" value="<?= get_the_title($post) ?>">
            </p>
            <p>
                <label class="CMREST_label" for="CMREST_email_text_input"><?= __('Text in E-Mail', 'cmrest') ?></label>
                <? wp_editor(
                        get_the_excerpt($post),
                        'CMREST_email_text_input',
                        [
                                'media_buttons' => false,
                                'textarea_name' => 'CMREST_email_text',
                                'quicktags' => ['buttons' => 'strong,em,link,block,del,ins,img,ul,ol,li,code,close'],
                        ]) ?>
            </p>
            <p>
                <button class="button button-primary CMREST_call"  data-call="CMREST_create_campaign"><?= __('Create Campaign', 'cmrest') ?></button>
            </p>
        </fieldset>
        <?php
    }


    /**
    * Opening html of the metabox content
    *
    * @since 0.2.0
    */
    public function CMREST_metabox_start()
    {
        ?>
        <div id="CMREST_metabox">
            <div class="CMREST_loader_overlay">
                <div class="CMREST_overlay_content_holder">
                    <div class="CMREST_overlay_content">
                        <div class="lds-roller">
                            <div></div>
                            <div></div>
                            <div></div>
                            <div></div>
                            <div></div>
                            <div></div>
                            <div></div>
                            <div></div>
                        </div>
                        <h1 class="CMREST_countdown"></h1>
                        <h3 class="CMREST_feedback"></h3>
                    </div>
                </div>
            </div>
            <div id="CMREST_content">
        <?php
    }




    /**
    * Closing content div
    *
    * @since 0.2.0
    */
    public function CMREST_metabox_end()
    {
        ?>
            </div>
        <?php
    }





    /**
    * Show a notice to update settings if API key and client Id could not be validted
    *
    * @since 0.2.0
    */
    public function CMREST_metabox_keys_notice()
    {
        ?>
        <div class="CMREST_notice_cotnent_holder">
            <div class="CMREST_notice_content">
            <h3><?= __('Please provide valid API credentials to create and send campaigns.', 'cmrest') ?></h3>
            <p><a href="<? menu_page_url('cmrest-settings')?>" class="button button-primary"><?= __('Update now', 'cmrest') ?></a></p>
            </div>
        </div>
        <?php
    }




    /**
    * HTML output for drafted campaign
    *
    * @since 0.2.0
    * @param $post WP post object
    * @param $campaignId
    */
    private function CMREST_show_draft_campaign($post, $campaignId)
    {
        $draft_data = $this->CMREST_api_communication->CMREST_get_draft_data($campaignId);

        $user = wp_get_current_user();

        ?>
        <div class="CSREST_infobox">
            <h3><?= __('Drafted Campaign', 'cmrest') ?></h3>
            <ul>
                <li><strong><?= __('Campaign Name', 'cmrest') ?>:</strong> <?= $draft_data->Name ?></li>
                <li><strong><?= __('Created at', 'cmrest') ?>:</strong> <?= $draft_data->DateCreated ?></li>
                <li><strong><?= __('Plain text preview', 'cmrest') ?></strong>: <a href="<?= $draft_data->PreviewTextURL ?>" target="_blank"><?= __('View preview', 'cmrest') ?></a></li>
                <li><strong><?= __('HTML preview', 'cmrest') ?></strong>: <a href="<?= $draft_data->PreviewURL ?>" target="_blank"><?= __('View preview', 'cmrest') ?></a></li>
            </ul>
        </div>
        <fieldset class="CMREST_campaign_fields">
            <? wp_nonce_field( 'CMREST_create_campaign', 'CMREST_ajax_nonce' ); ?>
            <input type="hidden" name="CMREST_create_post_id" value="<?= $post->ID ?>">
            <input type="hidden" name="CMREST_campaign_id" value="<?= $campaignId ?>">
            <p>
                <label class="CMREST_label" for="CMREST_schedule_draft"><?= __('Schedule Draft', 'cmrest') ?></label>
                <input class="CMREST_input CMREST_with-explain" type="datetime-local" name="CMREST_schedule_draft" id="CMREST_schedule_draft">
                <small><?= __('Leave blank to send emediatly.') ?></small>
            </p>
            <p>
                <label class="CMREST_label" for="CMREST_confirmation_to"><?= __('Schedule Draft', 'cmrest') ?></label>
                <input class="CMREST_input CMREST_with-explain" type="text" name="CMREST_confirmation_to" id="CMREST_confirmation_to" value="<?= $user->user_email ?>">
                <small><?= __('You can enter up to 5 E-Mail addresses, seperated by comma.') ?></small>
            </p>
            <p>
                <button class="button button-primary CMREST_call"  data-call="CMREST_send_campaign"><?= __('Schedule Draft', 'cmrest') ?></button>
                <button class="button button-link-delete CMREST_call"  data-call="CMREST_delete_draft"><?= __('Delete draft', 'cmrest') ?></button>
            </p>
            </fieldset>
            <fieldset class="CMREST_campaign_fields">
            <? wp_nonce_field( 'CMREST_create_campaign', 'CMREST_ajax_nonce' ); ?>
            <input type="hidden" name="CMREST_create_post_id" value="<?= $post->ID ?>">
            <input type="hidden" name="CMREST_campaign_id" value="<?= $campaignId ?>">
            <p>
                <label class="CMREST_label" for="CMREST_preview_to"><?= __('Send a preview to', 'cmrest') ?></label>
                <input class="CMREST_input CMREST_with-explain" type="text" name="CMREST_preview_to" id="CMREST_preview_to" value="<?= $user->user_email ?>">
                <small><?= __('You can enter up to 5 E-Mail addresses, separated by comma.') ?></small>
            </p>
            <p>
                <button class="button button-primary CMREST_call"  data-call="CMREST_send_preview"><?= __('Send preview', 'cmrest') ?></button>
            </p>
            </fieldset>
            <?php

    }

    /**
    * HTML for scheduled campaign
    *
    * @since 0.2.0
    * @param $post
    * @param $campaignId
     */
    protected function CMREST_show_scheduled_campaign($post, $campaignId){

     $scheduled_data = $this->CMREST_api_communication->CMREST_get_scheduled_data($campaignId);

     $user = wp_get_current_user();
     ?>
        <div class="CSREST_infobox">
            <h3><?= __('Scheduled Campaign', 'cmrest') ?></h3>
            <ul>
                <li><strong><?= __('Campaign Name', 'cmrest') ?>:</strong> <?= $scheduled_data->Name ?></li>
                <li><strong><?= __('Scheduled at', 'cmrest') ?>:</strong> <?= $scheduled_data->DateScheduled ?></li>
                <li><strong><?= __('Plain text preview', 'cmrest') ?></strong>: <a href="<?= $scheduled_data->PreviewTextURL ?>" target="_blank"><?= __('View preview', 'cmrest') ?></a></li>
                <li><strong><?= __('HTML preview', 'cmrest') ?></strong>: <a href="<?= $scheduled_data->PreviewURL ?>" target="_blank"><?= __('View preview', 'cmrest') ?></a></li>
            </ul>
        </div>
        <fieldset class="CMREST_campaign_fields">
            <? wp_nonce_field( 'CMREST_create_campaign', 'CMREST_ajax_nonce' ); ?>
            <input type="hidden" name="CMREST_create_post_id" value="<?= $post->ID ?>">
            <input type="hidden" name="CMREST_campaign_id" value="<?= $campaignId ?>">
            <p>
                <button class="button button-link-delete CMREST_call"  data-call="CMREST_campaign_move_to_draft"><?= __('Cancel - move to Draft', 'cmrest') ?></button>
                <button class="button button-link-delete CMREST_call"  data-call="CMREST_delete_draft"><?= __('Delete Campaign', 'cmrest') ?></button>
            </p>
            </fieldset>

     <?php
    }

    /**
     * HTML for sent campaign
     *
     * @since 0.2.0
     * @param $post
     * @param $campaignId
     */
    protected function CMREST_show_sent_campaign($post, $campaignId){

            $campaign_data = $this->CMREST_api_communication->CMREST_get_summary($campaignId);
            ?>
             <div class="CSREST_infobox">
                <h3><?= __('Sent Campaign', 'cmrest') ?></h3>
                <ul>
                    <li><strong><?= __('Recipients', 'cmrest') ?>:</strong> <?= $campaign_data->Recipients ?></li>
                    <li><strong><?= __('Total opened', 'cmrest') ?>:</strong> <?= $campaign_data->TotalOpened ?></li>
                    <li><strong><?= __('Unique opened', 'cmrest') ?>:</strong> <?= $campaign_data->UniqueOpened ?></li>
                    <li><strong><?= __('Clicks', 'cmrest') ?>:</strong> <?= $campaign_data->Clicks ?></li>
                    <li><strong><?= __('Unsubscribed', 'cmrest') ?>:</strong> <?= $campaign_data->Unsubscribed ?></li>
                    <li><strong><?= __('Bounced', 'cmrest') ?>:</strong> <?= $campaign_data->Bounced ?></li>
                    <li><strong><?= __('Spam complaints', 'cmrest') ?>:</strong> <?= $campaign_data->SpamComplaints ?></li>
                    <li><strong><?= __('Forwards', 'cmrest') ?>:</strong> <?= $campaign_data->Forwards ?></li>
                    <li><strong><?= __('Likes', 'cmrest') ?>:</strong> <?= $campaign_data->Likes ?></li>
                    <li><strong><?= __('Mentions', 'cmrest') ?>:</strong> <?= $campaign_data->Mentions ?></li>
                    <li><strong><?= __('Plain text preview', 'cmrest') ?></strong>: <a href="<?= $campaign_data->WebVersionTextURL ?>" target="_blank"><?= __('View text version', 'cmrest') ?></a></li>
                    <li><strong><?= __('HTML preview', 'cmrest') ?></strong>: <a href="<?= $campaign_data->WebVersionURL ?>" target="_blank"><?= __('View web version', 'cmrest') ?></a></li>
                    <li><strong><?= __('Worldview', 'cmrest') ?></strong>: <a href="<?= $campaign_data->WorldviewURL ?>" target="_blank"><?= __('Lounch Worldview', 'cmrest') ?></a></li>
                </ul>
            </div>
            <?php
    }

}