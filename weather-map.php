<?php
/**
 * Plugin Name: Weather Map
 * Description: Test Application to display weather in a map
 * Version: 1.0.0
 * Author: Ivan Mudryk
 * Text Domain: weather-map
 * Domain Path: /languages
 * Network: false
 */

namespace Mia\WeatherMap;

//prevent direct access data leaks
defined( 'ABSPATH' ) || exit;

define(__NAMESPACE__ . '\PLUGIN_FILE', __FILE__);

require 'vendor/autoload.php';

Core\Core_Hook::init();
Core\Cron_Hook::init();
REST\REST_API_Hook::init();
Weather_Station\Weather_Station_Hook::init();
Weather_Station\Weather_Station_Setting_Page::init();