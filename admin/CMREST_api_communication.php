<?php
/**
 * Created by PhpStorm.
 * User: offic
 * Date: 30.12.2018
 * Time: 21:30
 */

namespace cmrest\admin;


/**
 * Class CMREST_api_communication
 * @package cmrest\admin
 */
class CMREST_api_communication {


    /**
     * @var the API key saved in the option
     */
    private $CMREST_api_key;


    /**
     * @var the client id saved in the option
     */
    private $CMREST_client_id;


    /**
     * @var array with authorisation header and value = base64 encoded api key
     */
    private $CMREST_base_authorisation;


    /**
     * CMREST_API_Calls constructor.
     */
    public function __construct()
    {
        $api_keys = get_option('CMREST_api_keys');

        $this->CMREST_api_key = $api_keys['CMREST_api_key'];
        $this->CMREST_client_id = $api_keys['CMREST_client_id'];

        $this->CMREST_base_authorisation = ['authorization' => 'Basic ' . base64_encode($this->CMREST_api_key)];
    }


    /**
     * Wrapper for validate keys with the saved data in the option
     *
     * @return bool
     */
    public function CMREST_validate_saved_api_keys(){

        return $this->CMREST_validate_api_keys($this->CMREST_api_key, $this->CMREST_client_id);
    }


    /**
     * To validate the values from the option retreive all clients from CM
     * by the given API Key and compare their client ID's to the saved value.
     *
     * @since 0.2.0
     * @param $api_key
     * @param $client_id
     * @return bool
     */
    public function CMREST_validate_api_keys($api_key, $client_id)
    {

        if ($clients = $this->CMREST_get_clients($api_key)) {
            foreach ($clients as $client) {
                if ($client->ClientID == $client_id) {
                    return true;
                }
            }
        }
        return false;
    }


    /**
     * Receive all clients from CM by a given API KEY
     *
     * @since 0.2.0
     * @param string $api_key
     * @return bool
     */
    private function CMREST_get_clients($api_key = false)
    {

        $response = wp_remote_get(esc_url_raw(CMREST_API_URL . 'clients.json'), ['headers' => $this->CMREST_base_authorisation]);
        return $this->CMREST_request_was_successfull($response);
    }


    /**
     * Receive all subscriber lists from CM by the client ID from the option
     *
     * @since 0.2.0
     * @return mixed
     */
    public function CMREST_get_subscriber_lists()
    {
        $response = wp_remote_get(esc_url_raw(CMREST_API_URL . 'clients/' . $this->CMREST_client_id . '/lists.json'),
                                    [
                                        'headers' => $this->CMREST_base_authorisation
                                    ]);
        return $this->CMREST_request_was_successfull($response);
    }


    /**
     * Compare given Campaign ID against the ID's of CM drafted, scheduled or sent Campaigns.
     *
     * @since 0.2.0
     * @param string $campaign_id
     * @return bool if the campaign exists
     */
    public function CMREST_campaign_exists($campaign_id)
    {

        $campaigns['sent']      = $this->CMREST_get_sent_campaigns();
        $campaigns['scheduled'] = $this->CMREST_get_scheduled_campaigns();
        $campaigns['draft']     = $this->CMREST_get_draft_campaigns();

        foreach ( $campaigns as $key => $campaign ) {
            if (is_array($campaign)) {
                foreach ($campaign as $campaign_data) {
                    if($campaign_data->CampaignID == $campaign_id){
                        return $key;
                    }
                }
            }
        }
        return false;
    }




    /**
     * wrapper function for CMREST_API_get_campaigns_by_type to receive sent campaigns
     *
     * @since 0.2.0
     * @return array of campaign objects
     */
    public function CMREST_get_sent_campaigns()
    {
        return $this->CMREST_get_campaigns_by_type('campaigns');
    }


    /**
     * wrapper function for CMREST_API_get_campaigns_by_type to receive scheduled campaigns
     *
     * @since 0.2.0
     * @return array of campaign objects
     */
    public function CMREST_get_scheduled_campaigns()
    {
        return $this->CMREST_get_campaigns_by_type('scheduled');
    }


    /**
     * wrapper function for CMREST_API_get_campaigns_by_type to receive drafted campaigns
     *
     * @since 0.2.0
     * @return array of campaign objects
     */
    public function CMREST_get_draft_campaigns()
    {
        return $this->CMREST_get_campaigns_by_type('drafts');
    }


    /**
     * Retreive campaigns from CM
     *
     * @since 0.2.0
     * @param string $type
     * @return array of objects
     */
    public function CMREST_get_campaigns_by_type($type)
    {

        $response = wp_remote_get(esc_url_raw(CMREST_API_URL . 'clients/' . $this->CMREST_client_id . '/'.$type.'.json'),
            [
                'headers' => $this->CMREST_base_authorisation
            ]);
        return $this->CMREST_request_was_successfull($response);
    }


    /**
     * Updates an existing CM template by sending the html file and zip file of images URL
     *
     * @since 0.2.0
     * @param array $update
     * @param $templateId CM id of template
     * @return bool
     */
    public function CSREST_update_template(array $update, $templateId)
    {
        $response = wp_remote_post(esc_url_raw(CMREST_API_URL . 'templates/' . $templateId . '.json'),
            [
                'headers' => $this->CMREST_base_authorisation,
                'body' => json_encode($update),
                'method' => 'PUT',
                'data_format' => 'body',
            ]);

        return $this->CMREST_request_was_successfull($response);

    }


    /**
     * create a new template at CM by sending the html file and zip file of images URL
     *
     * @since 0.2.0
     * @param array $create
     * @return string CM ID of the newly created template
     */
    public function CSREST_create_template(array $create)
    {
        $response = wp_remote_post(
            esc_url_raw(CMREST_API_URL . 'templates/' . $this->CMREST_client_id . '.json'),
            [
                'headers' => $this->CMREST_base_authorisation,
                'body' => json_encode($create),
                'method' => 'POST',
                'data_format' => 'body',
            ]);

        return $this->CMREST_request_was_successfull($response);

    }


    /**
     * Find CM Template by name and return the ID
     *
     * @since 0.2.0
     * @param $CMREST_template
     * @return bool|string
     */
    public function CMREST_template_exists($CMREST_template)
    {

        if ($templates = $this->CMREST_get_templates()) {
            foreach ($templates as $template) {
                if ($template->Name == ucwords(str_ireplace('_', ' ', $CMREST_template))) {
                    return $template->TemplateID;
                }
            }
        }
        return false;
    }


    /**
     * Retreive all CM Templates
     *
     * @since 0.2.0
     * @return array of objects
     */
    public function CMREST_get_templates()
    {
        $response = wp_remote_get(esc_url_raw(CMREST_API_URL . 'clients/' . $this->CMREST_client_id . '/templates.json'),
            [
                'headers' => $this->CMREST_base_authorisation
            ]);
        return $this->CMREST_request_was_successfull($response);
    }


    /**
     * Create a new CM draft
     *
     * @param $campaign_settings data of the Campaign
     * @return object
     */
    public function CMREST_create_campaign($campaign_settings)
    {
        $response = wp_remote_post(
            esc_url_raw(CMREST_API_URL . 'campaigns/' . $this->CMREST_client_id . '/fromtemplate.json'),
            [
                'headers' => $this->CMREST_base_authorisation,
                'body' => json_encode($campaign_settings),
                'method' => 'POST',
                'data_format' => 'body',
            ]);

        return $this->CMREST_request_was_successfull($response);
    }


    /**
     * Receive CM draft object by given ID
     *
     * @since 0.2.0
     * @param $campaignId
     * @return object|bool
     */
    public function CMREST_get_draft_data($campaignId)
    {
        $drafts = $this->CMREST_get_draft_campaigns();
        if(is_array($drafts)){
            foreach ($drafts as $draft){
                if($draft->CampaignID == $campaignId){
                    return $draft;
                }
            }
        }
        return false;

    }


    /**
     * Send a draft Immediatly or schedule it for a specific date time
     *
     * @since 0.2.0
     * @param $campaign_id CM id of the campaign
     * @param $confirm_to E-Mail addresses to send the confirmation to
     * @param bool $send_date when to schedule the campaign
     * @return array on success
     */
    public function CMREST_send_or_schedule($campaign_id, $confirm_to, $send_date = false)
    {

        $sending_date = $send_date ?: 'Immediately';

        $response = wp_remote_post(
            esc_url_raw(CMREST_API_URL . 'campaigns/' . $campaign_id . '/send.json'),
            [
                'headers' => $this->CMREST_base_authorisation,
                'body' => json_encode([
                    "ConfirmationEmail" => $confirm_to,
                    "SendDate"          => $sending_date
                ]),
                'method' => 'POST',
                'data_format' => 'body',
            ]);

        return $this->CMREST_request_was_successfull($response);

    }

    /**
     * Delete a CM Campaign
     *
     * @since 0.2.0
     * @param $campaign_id CM Id of the Campaign to delete
     * @return array|bool|mixed|object
     */
    public function CMREST_delete_campaign($campaign_id)
    {
        $response = wp_remote_post(
            esc_url_raw(CMREST_API_URL . 'campaigns/' . $campaign_id . '.json'),
            [
                'headers' => $this->CMREST_base_authorisation,
                'method' => 'DELETE',
                'data_format' => 'body',
            ]);

        return $this->CMREST_request_was_successfull($response);
    }


    /**
     * Send a preview of the campaign with the given CM ID through CM
     *
     * @param $campaign_id
     * @param $preview_to
     * @return array|bool|mixed|object
     */
    public function CMREST_send_preview($campaign_id, $preview_to)
    {


        $response = wp_remote_post(
            esc_url_raw(CMREST_API_URL . 'campaigns/' . $campaign_id . '/sendpreview.json'),
            [
                'headers' => $this->CMREST_base_authorisation,
                'method' => 'POST',
                'data_format' => 'body',
                'body' => json_encode([
                    "PreviewRecipients" => $preview_to
                ])
            ]);
            return $this->CMREST_request_was_successfull($response);

    }


    /**
     * Get the CM object of a scheduled campaign
     *
     * @since 0.2.0
     * @param $campaignId
     * @return bool|object
     */
    public function CMREST_get_scheduled_data($campaignId){

        $scheduled_campaigns = $this->CMREST_get_scheduled_campaigns();
        if(is_array($scheduled_campaigns)){
            foreach ($scheduled_campaigns as $campaign){
                if($campaign->CampaignID == $campaignId){
                    return $campaign;
                }
            }
        }
        return false;

    }




    /**
     * get the summary data of a CM campaign
     *
     * @param $campaignId
     * @return array|bool|mixed|object
     */
    public function CMREST_get_summary($campaignId)
    {
        $response = wp_remote_get(esc_url_raw(CMREST_API_URL . 'campaigns/'.$campaignId . '/summary.json'),
            ['headers' => $this->CMREST_base_authorisation
            ]);
        return $this->CMREST_request_was_successfull($response);
    }




    /**
     * Move Campaign from schedule to drafts
     *
     * @since 0.2.0
     * @param $campaign_id
     * @return array|bool|mixed|object
     */
    public function CMREST_campaign_move_to_draft($campaign_id)
    {
        $response = wp_remote_post(
            esc_url_raw(CMREST_API_URL . 'campaigns/' . $campaign_id . '/unschedule.json'),
            [
                'headers' => $this->CMREST_base_authorisation,
                'method' => 'POST',
                'data_format' => 'body',
            ]);
        return $this->CMREST_request_was_successfull($response);
    }

    /**
     * Check weather a API call was successful
     *
     * @since 0.2.0
     * @param $response
     * @return array|bool|mixed|object
     */
    private function CMREST_request_was_successfull($response)
    {

        if (wp_remote_retrieve_response_code($response) >= 200 && wp_remote_retrieve_response_code($response) < 300) {
            return json_decode(wp_remote_retrieve_body($response));
        }

        error_log('CMREST API ERROR: ' . $response['body'] . ' Called in: ' . debug_backtrace()[1]['function']);
        $body = json_decode($response['body']);
        wp_die( 'ERROR: ' . $body->Message );

    }



}