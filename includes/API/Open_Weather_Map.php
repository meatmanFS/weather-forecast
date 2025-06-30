<?php

namespace Mia\WeatherMap\API;

//prevent direct access data leaks
defined( 'ABSPATH' ) || exit;

class Open_Weather_Map {

    /**
     * @var string Open Weather Map API url
     */
    const API_URL = 'http://api.openweathermap.org/data/2.5/weather';

    /**
     * @var string|null Open Weather Map App ID
     */
    protected $app_id = null;

    /**
     * Create map instance if appid is empty throw exception
     *
     * @throws \Exception
     */
    public function __construct() {

        $this->app_id = get_option( 'weather_map_appid' );

        if ( empty( $this->app_id ) ) {
            throw new \Exception( __( 'App ID is empty. Please set one in settings.', 'weather-map' ) );
        }

    }



    /**
     * Get the weather data from api
     *
     * @param $lat
     * @param $lon
     * @return bool|array
     * @throws \Exception
     */
    public function get_weather( $lat, $lon ) {

        if (empty($lat) || empty($lon)) {
            return false;
        }

        $response = wp_remote_get(
            add_query_arg(
                [
                    'lat' => $lat,
                    'lon' => $lon,
                    'appid' => $this->app_id,
                    'units' => 'metric'
                ],
                self::API_URL
            )
        );

        if ( is_wp_error( $response ) ) {

            throw new \Exception($response->get_error_message());

        }

        $response_code = wp_remote_retrieve_response_code( $response );
        $body = wp_remote_retrieve_body( $response );
        $data = json_decode( $body, true );

        if (200 !== $response_code) {

            throw new \Exception($data['message'] ?? 'Error while retrieving weather from api');

        }

        if (isset($data['main']['temp'])) {// convert to fahrenheit
            $temp = $data['main']['temp'];
            $data['main']['temp_fahrenheit'] = $temp*9/5+32;
        }
        if (isset($data['main']['feels_like'])) {// convert to fahrenheit
            $feels_like = $data['main']['feels_like'];
            $data['main']['feels_like_fahrenheit'] = $feels_like*9/5+32;
        }

        return $data;

    }

}