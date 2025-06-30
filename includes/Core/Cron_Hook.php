<?php

namespace Mia\WeatherMap\Core;

use const Mia\WeatherMap\PLUGIN_FILE;
use Mia\WeatherMap\Weather_Station\Weather_Station;

class Cron_Hook {

    /**
     * Initialize hooks
     *
     * @return void
     */
    public static function init() {

        add_action( 'wp', [ __CLASS__, 'schedule_cron' ] );

        add_action( 'weather_map_update_data', [ __CLASS__, 'execute_cron' ] );

        register_deactivation_hook( PLUGIN_FILE, [ __CLASS__, 'remove_cron' ] );

    }

    /**
     * Add cronjob
     *
     * @return void
     */
    public static function schedule_cron() {

        if ( ! wp_next_scheduled( 'weather_map_update_data' ) ) {
            wp_schedule_event (time(), 'daily', 'weather_map_update_data' );
        }

    }

    /**
     * Run the cron jox
     *
     * @return void
     */
    public static function execute_cron() {

        $current_time = time();
        $yesterday = $current_time - (24 * 60 * 60);

        $args = array(
            'post_type' => 'weather_station',
            'post_status' => 'publish',
            'meta_query' => array(
                'relation' => 'OR',
                array(
                    'key' => 'weather_map_update',
                    'compare' => 'NOT EXISTS'
                ),
                array(
                    'key' => 'weather_map_update',
                    'value' => '',
                    'compare' => '='
                ),
                array(
                    'key' => 'weather_map_update',
                    'value' => $yesterday,
                    'compare' => '<',
                    'type' => 'NUMERIC'
                )
            ),
            'numberposts' => -1 // Get all posts
        );

        $posts = get_posts($args);


        foreach ($posts as $post) {

            $weather_station = new Weather_Station($post);
            $weather_station->load_data();
            $weather_station->load_weather_data();

        }

    }

    /**
     * Remove cron on plugin deactivation
     *
     * @return void
     */
    public static function remove_cron() {

        $timestamp = wp_next_scheduled('weather_map_update_data');
        wp_unschedule_event($timestamp, 'weather_map_update_data');

    }

}
