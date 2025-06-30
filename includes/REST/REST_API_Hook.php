<?php

namespace Mia\WeatherMap\REST;

use Mia\WeatherMap\Weather_Station\Weather_Station;

class REST_API_Hook {

    /**
     * Initialize hooks
     *
     * @return void
     */
    public static function init() {

        add_action( 'rest_api_init', [ __CLASS__, 'regiter_route' ] );

    }

    public static function regiter_route () {

        register_rest_route( 'weather-map/v1', 'weather-station/(?P<id>\d+)', [
            'methods' => 'GET',
            'callback' => [ __CLASS__, 'get_weather_station' ],
            'permission_callback' => '__return_true',
        ] );

    }


    /**
     * Get a weather station data
     *
     * @param \WP_REST_Request $request Full data about the request.
     * @return \WP_Error|\WP_REST_Response
     */
    public static function get_weather_station($request) {

        $id = $request->get_param('id');

        if (empty($id)) {
            return new \WP_Error( 'id_not_provided', __( 'The param ID is not provided', 'weather-map' ) );
        }

        $weather_station = new Weather_Station($id);
        $weather_station->load_data();
        $weather_station->load_weather_data();

        $json_data = json_encode($weather_station);

        return new \WP_REST_Response( json_decode($json_data), 200 );

    }

}
