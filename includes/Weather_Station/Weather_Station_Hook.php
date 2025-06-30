<?php

namespace Mia\WeatherMap\Weather_Station;

//prevent direct access data leaks
defined( 'ABSPATH' ) || exit;

class Weather_Station_Hook {

    /**
     * Initialize hooks
     *
     * @return void
     */
    public static function init() {

        add_action( 'init', [__CLASS__, 'register_cpt'] );

        add_action( 'acf/include_fields', [__CLASS__, 'acf_fields']);

    }

    /**
     * Register weather station CPT
     *
     * @return void
     */
    public static function register_cpt() {

        $labels = array(
            'name'                  => __( 'Weather stations', 'weather-map' ),
            'singular_name'         => __( 'Weather station', 'weather-map' ),
            'menu_name'             => __( 'Weather stations', 'weather-map' ),
            'name_admin_bar'        => __( 'Weather station', 'weather-map' ),
            'add_new'               => __( 'Add New', 'weather-map' ),
            'add_new_item'          => __( 'Add New weather station', 'weather-map' ),
            'new_item'              => __( 'New weather station', 'weather-map' ),
            'edit_item'             => __( 'Edit weather station', 'weather-map' ),
            'view_item'             => __( 'View weather station', 'weather-map' ),
            'all_items'             => __( 'All weather stations', 'weather-map' ),
            'search_items'          => __( 'Search weather stations', 'weather-map' ),
            'parent_item_colon'     => __( 'Parent weather stations:', 'weather-map' ),
            'not_found'             => __( 'No weather stations found.', 'weather-map' ),
            'not_found_in_trash'    => __( 'No weather stations found in Trash.', 'weather-map' ),
            'insert_into_item'      => __( 'Insert into weather station', 'weather-map' ),
            'uploaded_to_this_item' => __( 'Uploaded to this weather station', 'weather-map' ),
            'filter_items_list'     => __( 'Filter weather station list', 'weather-map' ),
            'items_list_navigation' => __( 'Weather stations list navigation', 'weather-map' ),
            'items_list'            => __( 'Weather stations list', 'weather-map' ),
        );
        $args = array(
            'labels'             => $labels,
            'public'             => false,
            'publicly_queryable' => false,
            'show_ui'            => true,
            'show_in_menu'       => true,
            'query_var'          => false,
            'rewrite'            => array( 'slug' => 'weather_station' ),
            'capability_type'    => 'post',
            'has_archive'        => false,
            'hierarchical'       => false,
            'menu_position'      => 20,
            'supports'           => array( 'title' ),
            'show_in_rest'       => false,
            'menu_icon'          => 'dashicons-location-alt',
        );

        register_post_type( 'weather_station', $args );

    }

    /**
     * Add ACF fields for Weather station CPT
     *
     * @return void
     */
    public static function acf_fields() {

        if ( ! function_exists( 'acf_add_local_field_group' ) ) {
            return;
        }

        acf_add_local_field_group( array(
            'key' => 'group_67b058b300aa1',
            'title' => 'Weather Station Fields',
            'fields' => array(
                array(
                    'key' => 'field_67b058b3ba1df',
                    'label' => 'Weather station title',
                    'name' => 'title',
                    'aria-label' => '',
                    'type' => 'text',
                    'instructions' => '',
                    'required' => 0,
                    'conditional_logic' => 0,
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                    'default_value' => '',
                    'maxlength' => '',
                    'allow_in_bindings' => 0,
                    'placeholder' => '',
                    'prepend' => '',
                    'append' => '',
                ),
                array(
                    'key' => 'field_67b058d8ba1e1',
                    'label' => 'Location latitude',
                    'name' => 'lat',
                    'aria-label' => '',
                    'type' => 'text',
                    'instructions' => '',
                    'required' => 0,
                    'conditional_logic' => 0,
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                    'default_value' => '',
                    'maxlength' => '',
                    'allow_in_bindings' => 0,
                    'placeholder' => '',
                    'prepend' => '',
                    'append' => '',
                ),
                array(
                    'key' => 'field_67b058ebba1e2',
                    'label' => 'Location longitude',
                    'name' => 'lng',
                    'aria-label' => '',
                    'type' => 'text',
                    'instructions' => '',
                    'required' => 0,
                    'conditional_logic' => 0,
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                    'default_value' => '',
                    'maxlength' => '',
                    'allow_in_bindings' => 0,
                    'placeholder' => '',
                    'prepend' => '',
                    'append' => '',
                ),
                array(
                    'key' => 'field_67b0590bba1e3',
                    'label' => 'Weather data stored for 24 hours.',
                    'name' => 'weather_data',
                    'aria-label' => '',
                    'type' => 'textarea',
                    'instructions' => '',
                    'required' => false,
                    'conditional_logic' => 0,
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                    'default_value' => '',
                    'new_lines' => '',
                    'maxlength' => '',
                    'placeholder' => '',
                    'rows' => '',
                ),
            ),
            'location' => array(
                array(
                    array(
                        'param' => 'post_type',
                        'operator' => '==',
                        'value' => 'weather_station',
                    ),
                ),
            ),
            'menu_order' => 0,
            'position' => 'normal',
            'style' => 'default',
            'label_placement' => 'top',
            'instruction_placement' => 'label',
            'hide_on_screen' => '',
            'active' => true,
            'description' => '',
            'show_in_rest' => 0,
        ) );

    }

}
