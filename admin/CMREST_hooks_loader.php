<?php

namespace cmrest\admin;
/**
 * Register all actions and filters for the plugin.
 *
 * Maintain a list of all hooks that are registered throughout
 * the plugin, and register them with the WordPress API. Call the
 * run function to execute the list of actions and filters.
 */
class CMREST_hooks_loader {

    /**
     * The array of actions registered with WordPress.
     *
     * @since    0.2.0
     * @access   protected
     * @var      array $actions The actions registered with WordPress to fire when the plugin loads.
     */
    protected $actions;

    /**
     * The array of filters registered with WordPress.
     *
     * @since    0.2.0
     * @access   protected
     * @var      array $filters The filters registered with WordPress to fire when the plugin loads.
     */
    protected $filters;

    /**
     * Initialize the collections used to maintain the actions and filters.
     *
     * @since    0.2.0
     */
    public function __construct()
    {

        $this->actions = [



            // enqueue styles and scripts and load language
            ['hook' => 'admin_enqueue_scripts', 'component' => '\cmrest\admin\CMREST_scripts_and_styles', 'callback' => 'CSREST_admin_enqueue', 'priority' => null, 'accepted_args' => null],


            // the settings page for API Key and Client ID
            ['hook' => 'admin_menu', 'component' => '\cmrest\admin\CMREST_api_key_option_page', 'callback' => 'CMREST_add_option_pages', 'priority' => null, 'accepted_args' => null],
            ['hook' => 'admin_init', 'component' => '\cmrest\admin\CMREST_api_key_option_page', 'callback' => 'CMREST_page_init', 'priority' => null, 'accepted_args' => null],

            // the settings page basic sender information
            ['hook' => 'admin_menu', 'component' => '\cmrest\admin\CMREST_api_campaign_options_page', 'callback' => 'CMREST_add_submenu_page', 'priority' => null, 'accepted_args' => null],
            ['hook' => 'admin_init', 'component' => '\cmrest\admin\CMREST_api_campaign_options_page', 'callback' => 'CMREST_page_init', 'priority' => null, 'accepted_args' => null],

            // add the Metabox
            ['hook' => 'add_meta_boxes', 'component' => '\cmrest\admin\CMREST_metabox', 'callback' => 'CMREST_add_metabox', 'priority' => null, 'accepted_args' => null],

            // create or update template when campaign settings are updated
            ['hook' => 'update_option_CMREST_campaign_settings', 'component' => '\cmrest\admin\CMREST_templates', 'callback' => 'CMREST_template_create_or_update', 'priority' => null, 'accepted_args' => 3],

            //handle ajax calls from metabox
            ['hook' => 'wp_ajax_CSREST_ajax_request', 'component' => '\cmrest\admin\CMREST_ajax', 'callback' => 'CMREST_ajax_request', 'priority' => null, 'accepted_args' => 0],

                //handle ajax calls from metabox
            ['hook' => 'wp_ajax_CMREST_reload_metabox', 'component' => '\cmrest\admin\CMREST_ajax', 'callback' => 'CMREST_ajax_reload_metabox', 'priority' => null, 'accepted_args' => 0],
            ['hook' => 'wp_ajax_CMREST_get_excerpt', 'component' => '\cmrest\admin\CMREST_ajax', 'callback' => 'CMREST_ajax_get_excerpt', 'priority' => null, 'accepted_args' => 0],





        ];



        $this->filters = [];

    }

    /**
     * Add a new action to the collection to be registered with WordPress.
     *
     * @since    0.2.0
     * @param    string $hook The name of the WordPress action that is being registered.
     * @param    object $component A reference to the instance of the object on which the action is defined.
     * @param    string $callback The name of the function definition on the $component.
     * @param    int $priority Optional. The priority at which the function should be fired. Default is 10.
     * @param    int $accepted_args Optional. The number of arguments that should be passed to the $callback. Default is 1.
     */
    public function add_action($hook, $component, $callback, $priority = 10, $accepted_args = 1 )
    {
        $this->actions = $this->add($this->actions, $hook, $component, $callback, $priority, $accepted_args);
    }

    /**
     * Add a new filter to the collection to be registered with WordPress.
     *
     * @since    0.2.0
     * @param    string $hook The name of the WordPress filter that is being registered.
     * @param    object $component A reference to the instance of the object on which the filter is defined.
     * @param    string $callback The name of the function definition on the $component.
     * @param    int $priority Optional. The priority at which the function should be fired. Default is 10.
     * @param    int $accepted_args Optional. The number of arguments that should be passed to the $callback. Default is 1
     */
    public function add_filter($hook, $component, $callback, $priority = 10, $accepted_args = 1)
    {
        $this->filters = $this->add($this->filters, $hook, $component, $callback, $priority, $accepted_args);
    }

    /**
     * A utility function that is used to register the actions and hooks into a single
     * collection.
     *
     * @since    0.2.0
     * @access   private
     * @param    array $hooks The collection of hooks that is being registered (that is, actions or filters).
     * @param    string $hook The name of the WordPress filter that is being registered.
     * @param    object $component A reference to the instance of the object on which the filter is defined.
     * @param    string $callback The name of the function definition on the $component.
     * @param    int $priority The priority at which the function should be fired.
     * @param    int $accepted_args The number of arguments that should be passed to the $callback.
     * @return   array                                  The collection of actions and filters registered with WordPress.
     */
    private function add($hooks, $hook, $component, $callback, $priority, $accepted_args, $args)
    {

        $hooks[] = [
            'hook' => $hook,
            'component' => $component,
            'callback' => $callback,
            'priority' => $priority,
            'accepted_args' => $accepted_args,
            'args'  => $args
        ];

        return $hooks;

    }

    /**
     * Register the filters and actions with WordPress.
     *
     * @since    0.2.0
     */
    public function run()
    {

        foreach ($this->filters as $hook) {

            $class_name = $hook['component'];
            $function_callback = $hook['callback'];

            add_filter($hook['hook'], function () use($class_name, $function_callback) {
                $action = $class_name::CMREST_Hook_get_instance();
                $action->{$function_callback}();
            }, $hook['priority'], $hook['accepted_args']);
        }

        foreach ($this->actions as $hook) {

            $class_name = $hook['component'];
            $function_callback = $hook['callback'];

            add_action($hook['hook'], function ( $arg = null, $arg1 = null, $arg2 = null, $args3 = null, $args4 = null ) use($class_name, $function_callback ) {
                $action = $class_name::CMREST_Hook_get_instance();
                $action->{$function_callback}( $arg, $arg1, $arg2, $args3, $args4 );
            }, $hook['priority'], $hook['accepted_args']);

        }

    }

}