<?php
/**
 * Created by PhpStorm.
 * User: offic
 * Date: 31.12.2018
 * Time: 21:44
 */

namespace cmrest\admin;


class CMREST_api_campaign_options_page extends CMREST_hook {


    /**
     * trait with options html output
     */
    use CMREST_option_fields_html;


    /**
     * @var array saved options
     */
    private $CMREST_campaign_settings;

    /**
     * @var string slug of the page
     */
    const CMREST_page_slug  = 'cmrest-campaign-settings';



    /**
     * @var string the option name
     */
    const CMREST_option_name = 'CMREST_campaign_settings';


    /**
     * Add the submenu page under the CM api settings
     */
    public function CMREST_add_submenu_page()
    {
        add_submenu_page('cmrest-settings', __('Campaign Settings', 'cmrest'), __('Campaign Settings', 'cmrest'), 'manage_options', self::CMREST_get_page_slug(), [$this, 'CMREST_create_campaign_page']);
    }


    /**
     * initialize the settings page with its sections and fields
     */
    public function CMREST_page_init()
    {

        $sending_section = 'CMREST_sender_information_section';
        $template_section = 'CMREST_template_header_and_footer_section';


        $this->CMREST_campaign_settings = get_option(self::CMREST_get_option_name());

        //register the option
        register_setting( 'CMREST_campaign_settings_group', self::CMREST_get_option_name(), [$this, 'CMREST_sanitize_campaign_settings'] );

        // first sending_section with sender and receiver information
        add_settings_section( $sending_section, __('Campaign sending information', 'cmrest'), [$this, 'CMREST_print_section_sender'], self::CMREST_get_page_slug() );



        add_settings_field( 'CMREST_campaings_sender_name', __('Sender Name', 'cmrest'), [$this, 'CMREST_option_text_field'], self::CMREST_get_page_slug(),  $sending_section,
                            [
                                'type'  => 'text',
                                'option'=> self::CMREST_get_option_name(),
                                'id'    => 'CMREST_campaings_sender_name',
                                'value' => isset($this->CMREST_campaign_settings['CMREST_campaings_sender_name']) ? $this->CMREST_campaign_settings['CMREST_campaings_sender_name']: '',
                            ]);


        add_settings_field( 'CMREST_campaings_sender_email', __('Sender E-Mail', 'cmrest'), [$this, 'CMREST_option_text_field'], self::CMREST_get_page_slug(), $sending_section,
                            [
                                'type'  => 'email',
                                'option'=> self::CMREST_get_option_name(),
                                'id'    => 'CMREST_campaings_sender_email',
                                'value' => isset($this->CMREST_campaign_settings['CMREST_campaings_sender_email']) ? $this->CMREST_campaign_settings['CMREST_campaings_sender_email'] : ''
                            ]);


        add_settings_field( 'CMREST_campaings_reply_email', __('Reply E-Mail', 'cmrest'), [$this, 'CMREST_option_text_field'], self::CMREST_get_page_slug(), $sending_section,
                            [
                                'type'  => 'email',
                                'option'=> self::CMREST_get_option_name(),
                                'id'    => 'CMREST_campaings_reply_email',
                                'value' => isset($this->CMREST_campaign_settings['CMREST_campaings_reply_email']) ? $this->CMREST_campaign_settings['CMREST_campaings_reply_email'] : ''
                            ]);



        add_settings_field( 'CMREST_campaigns_subscribers', __('Subscriber List', 'cmrest'), [$this, 'CMREST_subscribers_callback'], self::CMREST_get_page_slug(), $sending_section );

        add_settings_field( 'CMREST_campaigns_post_types', __('Sendable post types'), [$this, 'CMREST_sendable_post_types_callback'], self::CMREST_get_page_slug(), $sending_section, ['post']);

        
        // Section for template defaults
        add_settings_section( $template_section, __('Template Header and Footer data', 'cmrest'), [$this, 'CMREST_print_section_tmpl_header_and_footer'], self::CMREST_get_page_slug() );


        add_settings_field( 'CMREST_available_template', __('Select a available Template.', 'cmrest'), [$this, 'CMREST_available_template_callback'], self::CMREST_get_page_slug(), $template_section );

        add_settings_field( 'CMREST_campaign_logo', __('Logo to be added in the E-Mail.', 'cmrest'), [$this, 'CMREST_campaign_logo_callback'], self::CMREST_get_page_slug(), $template_section );

        add_settings_field( 'CMREST_campaign_button_text', __('Text on the call to Action Button in the E-Mail', 'cmrest'), [$this, 'CMREST_option_text_field'], self::CMREST_get_page_slug(), $template_section,
                            [
                                'type'  => 'text',
                                'id'    => 'CMREST_campaign_button_text',
                                'option'=> self::CMREST_get_option_name(),
                                'value' => isset($this->CMREST_campaign_settings['CMREST_campaign_button_text']) ? $this->CMREST_campaign_settings['CMREST_campaign_button_text'] : ''
                            ]);


        add_settings_field( 'CMREST_campaign_button_color', __('Color of the call to Action Button in the E-Mail', 'cmrest'), [$this, 'CMREST_campaign_button_color_callback'], self::CMREST_get_page_slug(), $template_section );

        add_settings_field( 'CMREST_campaign_footer_explain', __('Footer Section to explain why E-Mail is send', 'cmrest'), [$this, 'CMREST_editor_callback'], self::CMREST_get_page_slug(), $template_section,
                            [
                                    'value' => isset($this->CMREST_campaign_settings['CMREST_campaign_footer_explain']) ? $this->CMREST_campaign_settings['CMREST_campaign_footer_explain'] : '',
                                    'id'    => 'CMREST_campaign_footer_explain',
                                    'name'  => self::CMREST_get_option_name() . '[CMREST_campaign_footer_explain]'
                            ]);

        add_settings_field( 'CMREST_campaign_footer_terms', __('Footer Section to explain why E-Mail is send', 'cmrest'), [$this, 'CMREST_editor_callback'], self::CMREST_get_page_slug(), $template_section,
                            [
                                'value' => isset($this->CMREST_campaign_settings['CMREST_campaign_footer_terms']) ? $this->CMREST_campaign_settings['CMREST_campaign_footer_terms'] : '',
                                'id'    => 'CMREST_campaign_footer_terms',
                                'name'  => self::CMREST_get_option_name() . '[CMREST_campaign_footer_terms]'
                            ]);

    }


    /**
     * print the section sub headline
     */
    public function CMREST_print_section_sender()
    {
        print __('Set the E-Mail addressor information for your campaigns, your subscriber list and the sendable post types.', 'cmrest');
    }



    /**
     * print the section sub headline
     */
    public function CMREST_print_section_tmpl_header_and_footer()
    {
        print __('Set your E-Mails header and footer data.', 'cmrest');
    }


    /**
     * print the form and its fields
     *
     * @since 0.2.0
     */
    public function CMREST_create_campaign_page()
    {
        settings_errors('CMREST_Campaign_settings_validation_Errors');
        ?>
        <div class="wrap">
            <h1><?= __('Campaign Settings', 'cmrest') ?></h1>
            <form method="post" action="options.php">
                <?php
                settings_fields('CMREST_campaign_settings_group');
                do_settings_sections('cmrest-campaign-settings');
                submit_button();
                ?>
            </form>
        </div>
        <?php
    }


    /**
     * sanitize the input fields
     *
     * @param $input array of requested values
     * @return mixed sanitized values
     */
    public function CMREST_sanitize_campaign_settings($input)
    {

        if (isset($input['CMREST_campaings_sender_name'])) {
            $sanitized_input['CMREST_campaings_sender_name'] = sanitize_text_field($input['CMREST_campaings_sender_name']);
        }

        if (isset($input['CMREST_campaings_sender_email'])) {
            $sanitized_input['CMREST_campaings_sender_email'] = sanitize_email($input['CMREST_campaings_sender_email']);
        }

        if (isset($input['CMREST_campaings_reply_email'])) {
            $sanitized_input['CMREST_campaings_reply_email'] = sanitize_email($input['CMREST_campaings_reply_email']);
        }

        if (isset($input['CMREST_campaign_logo'])) {
            $id = sanitize_text_field($input['CMREST_campaign_logo']);
            if (get_post_type($id) == 'attachment') {
                $sanitized_input['CMREST_campaign_logo'] = $id;
            }
        }

        if (isset($input['CMREST_available_template'])) {
            $sanitized_input['CMREST_available_template'] = sanitize_text_field($input['CMREST_available_template']);
        }

        if (isset($input['CMREST_campaign_button_text'])) {
            $sanitized_input['CMREST_campaign_button_text'] = sanitize_text_field($input['CMREST_campaign_button_text']);
        }

        if (isset($input['CMREST_campaign_button_color'])) {
            $sanitized_input['CMREST_campaign_button_color'] = $this->CMREST_check_color($input['CMREST_campaign_button_color']);
        }

        if (isset($input['CMREST_campaign_footer_explain'])) {
            $sanitized_input['CMREST_campaign_footer_explain'] = sanitize_textarea_field($input['CMREST_campaign_footer_explain']);
        }

        if (isset($input['CMREST_campaign_footer_terms'])) {
            $sanitized_input['CMREST_campaign_footer_terms'] = sanitize_textarea_field($input['CMREST_campaign_footer_terms']);
        }

        if (isset($input['CMREST_campaigns_subscribers'])) {
            $sanitized_input['CMREST_campaigns_subscribers'] = sanitize_text_field($input['CMREST_campaigns_subscribers']);
        }

        if (isset($input['CMREST_campaigns_post_types'])) {
            $sanitized_input['CMREST_campaigns_post_types'] = $input['CMREST_campaigns_post_types'];
        }
        return $sanitized_input;
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