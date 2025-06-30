<?php

namespace Mia\WeatherMap\Weather_Station;

//prevent direct access data leaks
defined( 'ABSPATH' ) || exit;

class Weather_Station_Setting_Page {

    /**
     * Initialize hooks
     *
     * @return void
     */
    public static function init() {

        add_action( 'admin_init', [__CLASS__, 'register_options'] );

        add_action( 'admin_menu', [__CLASS__, 'add_options_page'] );

    }

    /**
     * Add options group
     *
     * @return void
     */
    public static function register_options() {
        register_setting( 'weather-map-settings-group', 'weather_map_appid' );
    }

    /**
     * Add Submenu page for settings
     *
     * @return void
     */
    public static function add_options_page() {

        add_submenu_page(
            'edit.php?post_type=weather_station',
            __( 'Options', 'weather-map' ),
            __( 'Options', 'weather-map' ),
            'edit_posts',
            'weather_station_options',
            [__CLASS__, 'display_settings']
        );


    }

    /**
     * Display settings form
     *
     * @return void
     */
    public static function display_settings() {
        ?>
        <div class="wrap">
            <h1><?php _e('Weather Map Settings', 'weather-map'); ?></h1>

            <form method="post" action="options.php">
                <?php settings_fields( 'weather-map-settings-group' ); ?>
                <?php do_settings_sections( 'weather-map-settings-group' ); ?>
                <table class="form-table">
                    <tr valign="top">
                        <th scope="row"><?php _e('Open Weather Map App ID') ?></th>
                        <td><input type="text" name="weather_map_appid" value="<?php echo esc_attr( get_option('weather_map_appid') ); ?>" /></td>
                    </tr>
                </table>

                <?php submit_button(); ?>

            </form>
        </div>
        <?php
    }

}
