<?php

namespace Mia\WeatherMap\Core;

use Mia\WeatherMap\Weather_Station\Weather_Station;
use const Mia\WeatherMap\PLUGIN_FILE;

//prevent direct access data leaks
defined( 'ABSPATH' ) || exit;

class Core_Hook {

    /**
     * Initialize hooks
     *
     * @return void
     */
    public static function init() {

        add_action( 'plugins_loaded', [ __CLASS__, 'load_text_domain' ] );

        register_activation_hook( PLUGIN_FILE, [ __CLASS__, 'activation' ] );

        add_filter( 'display_post_states', [ __CLASS__, 'display_map_status' ], 10, 2 );

        add_filter( 'template_include', [ __CLASS__, 'custom_map_template'] );
        
    }

    /**
     * Load text domain
     *
     * @return void
     */
    public static function load_text_domain() {
        load_textdomain( 'weather-map', plugin_dir_path(PLUGIN_FILE) . 'languages/weather-map.mo' );
    }

    /**
     * On plugin activation add map page
     *
     * @return void
     */
    public static function activation() {
        
        $check_page_exist = get_page_by_path( 'map' );
        // Check if the page already exists
        if( empty( $check_page_exist ) ) {
            wp_insert_post(
                array(
                    'comment_status' => 'close',
                    'ping_status'    => 'close',
                    'post_author'    => 1,
                    'post_title'     => __('Map', 'weather-map'),
                    'post_name'      => 'map',
                    'post_status'    => 'publish',
                    'post_content'   => '',
                    'post_type'      => 'page',
                    'post_parent'    => 0
                )
            );
        }

    }

    /**
     * @param array $post_states
     * @param \WP_Post $post
     * @return array
     */
    public static function display_map_status($post_states, $post) {

        if ('map' === $post->post_name) {
            $post_states['weather_map'] = __( 'Map Page', 'weather-map' );
        }

        return $post_states;

    }

    /**
     * Load custom template for map page
     *
     * @param $template
     * @return string
     */
    public static function custom_map_template($template) {

        if (is_page('map')) {

            $custom_template = plugin_dir_path(__FILE__) . 'templates/map-template.php';

            if (file_exists($custom_template)) {
                return $custom_template;
            }

        }


        return $template;

    }

    /**
     * Get the weather stations json
     *
     * @return string
     */
    public static function get_weather_stations() {

        $args = array(
            'post_type' => 'weather_station',
            'post_status' => 'publish',
            'numberposts' => -1 // Get all posts
        );

        $posts = get_posts($args);

        $weather_stations = [];

        foreach ($posts as $post) {

            $weather_stations[] = new Weather_Station($post);

        }

        return json_encode($weather_stations);
    }

    /**
     * Get json string for the fronted application
     *
     * @return string
     */
    public static function get_weather_map_data() {
        $weather_map_data = [
            'jsonUrl' => get_rest_url(null, 'weather-map/v1/weather-station'),
        ];

        return json_encode($weather_map_data);
    }

}
