<?php
/**
 * Created by PhpStorm.
 * User: offic
 * Date: 01.01.2019
 * Time: 01:09
 */

namespace cmrest\admin;


trait CMREST_option_fields_html {


    /**
     * print a text|email|password field html
     *
     *
     * @param $args
     */
    public function CMREST_option_text_field($args){

        printf(
            '<input type="%s" id="%s" name="%s" value="%s" class="regular-text code" />',
            $args['type'], $args['id'], $args['option'].'['. $args['id'] . ']', $args['value']
        );
    }


    /**
     * print wp_editor html
     *
     *
     * @param $args
     */
    public function CMREST_editor_callback($args)
    {
        wp_editor($args['value'], $args['id'], ['media_buttons' => false, 'textarea_name' => $args['name']]);
    }


    /**
     * print select with CM subscriber lists
     */
    public function CMREST_subscribers_callback()
    {

        $rest_call = new CMREST_api_communication();
        $subscribers = $rest_call->CMREST_get_subscriber_lists();

        echo '<select id="CMREST_campaigns_subscribers" name="CMREST_campaign_settings[CMREST_campaigns_subscribers]">';
        echo '<option value="">' . __('please choose', 'cmrest') . '</option>';

        foreach ($subscribers as $list) {
            $selected = $this->CMREST_campaign_settings['CMREST_campaigns_subscribers'] == $list->ListID ? ' selected="selected" ' : '';
            echo '<option value="' . $list->ListID . '"' . $selected . '>' . $list->Name . '</option>';
        }
        echo '</select>';
    }


    /**
     * print post type checkboxes
     */
    public function CMREST_sendable_post_types_callback()
    {
        $checked = '';
        $field_id = 'CMREST_campaigns_post_types';

        $args = [
            'public' => true,
            '_builtin' => false,
        ];

        $output = 'objects'; // names or objects, note names is the default
        $operator = 'and'; // 'and' or 'or'
        $post_types = get_post_types($args, $output, $operator);

        echo '<ul class="list-unstyled">';

        if (isset($this->CMREST_campaign_settings[ $field_id ]) && is_array($this->CMREST_campaign_settings[$field_id]))
            $checked = in_array('post', $this->CMREST_campaign_settings[$field_id]) ? ' checked="checked" ' : '';

        echo '<li><input type="checkbox" name="CMREST_campaign_settings['.$field_id.'][]" value="post" ' . $checked . ' >Post</li>';

        foreach ($post_types as $post_type) {

            if (isset($this->CMREST_campaign_settings[$field_id]) && is_array($this->CMREST_campaign_settings[$field_id]))
                $checked = in_array($post_type, $this->CMREST_campaign_settings[$field_id]) ? ' checked="checked" ' : '';

            echo '<li><input type="checkbox" name="CMREST_campaign_settings['.$field_id.'][]" value="' . $post_type->name . '" ' . $checked . ' >' . $post_type->label . '</li>';
        }
        echo '</ul>';
    }


    /**
     * print select with available templates
     * depending on existing template folders
     */
    public function CMREST_available_template_callback()
    {

        foreach (glob(CMREST_PATH . "templates/*", GLOB_ONLYDIR) as $filename) {
            $dir_paths[] = $filename;
        }

        foreach ($dir_paths as $dir_path) {

            $template_folder = pathinfo($dir_path, PATHINFO_BASENAME);
            $template_name = ucwords(str_ireplace('_', ' ', $template_folder));

            $checked = isset($this->CMREST_campaign_settings['CMREST_available_template']) && $this->CMREST_campaign_settings['CMREST_available_template'] == $template_folder ? ' checked="checked" ' : '';

            print '<input type="radio" id="CMREST_available_template" name="CMREST_campaign_settings[CMREST_available_template]" value="' . $template_folder . '" ' . $checked . '>' . $template_name;
        }
    }


    /**
     * print wp media uploader for template logo
     */
    public function CMREST_campaign_logo_callback()
    {
        wp_enqueue_media();
        ?>
        <div class='image-preview-wrapper'>
            <? if (isset($this->CMREST_campaign_settings['CMREST_campaign_logo'])) {
                echo '<img class="CMREST_upload_logo_button" id="image-preview" src="' . wp_get_attachment_image_url($this->CMREST_campaign_settings['CMREST_campaign_logo'], 'thumbnail') . '" width="150" height="150" style="max-height: 150px; width: 150px;">';
            } else {
                echo '<img class="CMREST_upload_logo_button" id="image-preview" src="" width="100" height="100" style="max-height: 150px; width: 150px;display: none">';
            } ?>
        </div>
        <input class="CMREST_upload_logo_button" type="button" class="button"
               value="<?php _e('Upload image', 'cmrest'); ?>"/>
        <input type='hidden' name='CMREST_campaign_settings[CMREST_campaign_logo]' id='image_attachment_id'
               value="<?= $this->CMREST_campaign_settings['CMREST_campaign_logo'] ?>">
        <?php
    }


    /**
     * print a color picker for the email button color
     */
    public function CMREST_campaign_button_color_callback()
    {
        $val = isset($this->CMREST_campaign_settings['CMREST_campaign_button_color']) ? esc_attr($this->CMREST_campaign_settings['CMREST_campaign_button_color']) : '#333333';
        ?>
        <input type="text" name="CMREST_campaign_settings[CMREST_campaign_button_color]" value="<?= $val ?>"
               class="CMREST-color-picker">
        <?php
    }


    /**
     * hex color check
     *
     * @param $value
     * @return mixed
     */
    public function CMREST_check_color($value)
    {
        if (preg_match('/^#[a-f0-9]{6}$/i', $value)) { // if user insert a HEX color with #
            return $value;
        }

    }

}