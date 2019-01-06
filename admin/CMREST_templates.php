<?php
/**
 * Created by PhpStorm.
 * User: offic
 * Date: 31.12.2018
 * Time: 21:33
 */

namespace cmrest\admin;


class CMREST_templates extends CMREST_hook {


    /**
     * @var mixed the sender and template setings from the option
     */
    protected $CMREST_campaign_settings;


    /**
     * @var string template name
     */
    protected $CMREST_template;


    /**
     * @var CMREST_api_communication
     */
    protected $CMREST_api_communication;

    /**
     * @var int wp attachment id
     */
    protected $CMREST_logo_id;


    /**
     * CMREST_templates constructor.
     */
    function __construct()
    {
        $this->CMREST_campaign_settings = get_option(CMREST_api_campaign_options_page::CMREST_get_option_name());

        $this->CMREST_template = $this->CMREST_campaign_settings['CMREST_available_template'];

        $this->CMREST_api_communication = new CMREST_api_communication();
    }


    /**
     * called from the update_action_{option_name} hook
     *
     * @since 0.2.0
     * @param $old_value
     * @param $value
     * @param $option
     */
    public function CMREST_template_create_or_update($old_value,  $value,  $option){

        // bail early if logo and template are unchanged
        if($old_value['CMREST_campaign_logo'] == $value['CMREST_campaign_logo'] && $old_value['CMREST_available_template'] == $value['CMREST_available_template']) return;

        $this->CMREST_logo_id = $value['CMREST_campaign_logo'];

        $this->CMREST_create_zip();

        if($template_id = $this->CMREST_api_communication->CMREST_template_exists($this->CMREST_template) ){
            $this->CMREST_update_template($template_id);
        }else{

            $this->CMREST_create_template();
        }
    }


    /**
     * Update the template by name via CMREST_api_communicator
     *
     * @since 0.2.0
     * @param $template_id
     * @return bool
     */
    public function CMREST_update_template($template_id)
    {

        $update = [
            'Name' => ucwords(str_ireplace('_', ' ', $this->CMREST_template)),
            'HtmlPageURL' => $this->CMREST_get_index_file(),
            'ZipFileURL' => CMREST_URL . 'templates/' . $this->CMREST_template . '/images.zip',
        ];

        return $this->CMREST_api_communication->CSREST_update_template($update, $template_id);
    }


    /**
     * Create a new template at CM
     * via CMREST_api_communicator
     *
     * @since 0.2.0
     * @return string
     */
    public function CMREST_create_template()
    {

        $create = [
            'Name' => ucwords(str_ireplace('_', ' ', $this->CMREST_template)),
            'HtmlPageURL' => $this->CMREST_get_index_file(),
            'ZipFileURL' => CMREST_URL . 'templates/' . $this->CMREST_template . '/images.zip',
        ];

        return $this->CMREST_api_communication->CSREST_create_template($create);
    }


    /**
     * Zip the images folder in from the template
     *
     * @since 0.2.0
     * @return bool
     */
    function CMREST_create_zip()
    {

        if(!$this->CMREST_copy_logo()) return false;

        $destination = CMREST_PATH . 'templates/' . $this->CMREST_template . '/images.zip';

        if(file_exists($destination)) unlink($destination);

        foreach (glob(CMREST_PATH . 'templates/' . $this->CMREST_template . '/images/*') as $file) {
            if (file_exists($file)) {
                $zip_files[] = $file;
            }
        }

        if (count($zip_files)) {
            //create the archive
            $zip = new \ZipArchive();
            if ($zip->open($destination, \ZIPARCHIVE::CREATE) !== true) {
                return false;
            }
            //add the files
            foreach ($zip_files as $file) {
                $zip_file = pathinfo($file, PATHINFO_BASENAME);
                $zip->addFile($file, $zip_file);
            }
            $zip->close();

            return file_exists($destination);
        }
    }


    /**
     * Copy the logo image from wp uploads to template images
     * change mime type to png
     *
     * @since 0.2.0
     * @return bool
     */
    public function CMREST_copy_logo()
    {

        $logo = ! $this->CMREST_logo_id ? CMREST_PATH . 'templates/default_logo.png' : wp_get_attachment_url($this->CMREST_logo_id);

        $to = CMREST_PATH . 'templates/' . $this->CMREST_template . '/images/logo.png';

        if(file_exists($to)) unlink($to);

        return imagepng(imagecreatefromstring(file_get_contents($logo)), $to);
    }


    /**
     * Return the URL of the templates index file
     * depending on the language setting
     *
     * @since 0.2.0
     * @return string
     */
    public function CMREST_get_index_file()
    {
        if (file_exists(CMREST_PATH . 'templates/' . $this->CMREST_template . '/index_' . get_locale() . '.html')) {
            return CMREST_URL . 'templates/' . $this->CMREST_template . '/index_' . get_locale() . '.html';
        } else {
            return CMREST__URL . 'templates/' . $this->CMREST_template . '/index_en_EN.html';
        }
    }
}