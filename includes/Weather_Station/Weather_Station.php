<?php

namespace Mia\WeatherMap\Weather_Station;

use Mia\WeatherMap\API\Open_Weather_Map;

class Weather_Station implements \JsonSerializable {

    /**
     * @var int The post id
     */
    protected int $post_id;

    /**
     * @var bool Is valid post data
     */
    protected $is_valid = true;

    /**
     * @var string Location Title
     */
    protected mixed $title;

    /**
     * @var string Location Latitude
     */
    protected mixed $lat;

    /**
     * @var string Location Longitude
     */
    protected mixed $lng;

    /**
     * @var string Location Weather Data
     */
    protected mixed $weather_data;

    /**
     * @var false|int
     */
    protected mixed $weather_map_update;



    public function __construct($post) {

        if ($post instanceof \WP_Post) {
            $this->post_id = $post->ID;
        }

        if (is_numeric($post)) {
            $wp_post = get_post($post);

            if ($wp_post instanceof \WP_Post) {
                $this->post_id = $wp_post->ID;
            }
        }

    }

    /**
     * Load data for weather station
     *
     * @return void
     */
    public function load_data() {

        if (!function_exists('get_field')) {
            $this->is_valid = false;
            return;
        }

        if (empty($this->post_id)) {
            $this->is_valid = false;
            return;
        }

        $this->title = get_field( 'title', $this->post_id );
        $this->lat = get_field( 'lat', $this->post_id );
        $this->lng = get_field( 'lng', $this->post_id );
        $this->weather_data = get_field( 'weather_data', $this->post_id );
        $this->weather_map_update = get_post_meta( $this->post_id, 'weather_map_update', true );

        if (!empty($this->weather_data)) {
            $current_time = time();
            $yesterday = $current_time - (24 * 60 * 60);

            // if data longer then 24h remove it to load
            if ($this->weather_map_update < $yesterday ) {
                $this->weather_map_update = [];
            }
        }

    }

    /**
     * Load Weather Data from api
     *
     * @return void
     */
    public function load_weather_data() {

        if (empty($this->post_id)) {
            return;
        }


        try {

            if (!empty($this->weather_data)) {
                update_field('weather_data', [], $this->post_id);// clear field to force update weather data
            }

            $api = new Open_Weather_Map();

            $weather_data = $api->get_weather($this->lat, $this->lng);

            if (empty($weather_data)) {
                throw new \Exception('Failed to load weather data. Please check logs.');
            }

            $this->weather_data = $weather_data;

            $updated = update_field('weather_data', $this->weather_data, $this->post_id);

            if (!$updated) {
                $this->is_valid = false;
            } else {
                update_post_meta($this->post_id, 'weather_map_update', time());
            }

        } catch (\Exception $e) {

            $this->is_valid = false;

            error_log($e->getMessage());

        }

    }

    /**
     * When json encode we return array
     *
     * @return mixed
     */
    public function jsonSerialize() : mixed {

        $this->load_data();

        if (!$this->is_valid) {
            return [];
        }

        return [
            'id'           => $this->post_id,
            'title'        => $this->title,
            'lat'          => $this->lat,
            'lng'          => $this->lng,
            'weather_data' => $this->weather_data,
        ];

    }

}
