<?php
    namespace Mia\WeatherMap\Core;

    use const Mia\WeatherMap\PLUGIN_FILE;

    $assets_dir = trailingslashit(plugin_dir_url(PLUGIN_FILE)) . 'includes/Core/templates/assets';
?>
<!doctype html>
<html lang="en">
    <head>
        <meta charset="UTF-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />
        <title><?php _e( 'Weather Map', 'weather-map' ); ?></title>
        <link rel="stylesheet" href="<?php echo $assets_dir; ?>/style.css">
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/ol@latest/ol.css">
        <script src="https://cdn.jsdelivr.net/npm/ol@latest/ol.js"></script>
    </head>
    <body>
        <div id="app"></div>
        <script>
            var weatherStations = <?php echo Core_Hook::get_weather_stations(); ?>;
            var weatherMapData = <?php echo Core_Hook::get_weather_map_data(); ?>;
        </script>
        <script type="module" src="<?php echo $assets_dir; ?>/script.js"></script>
    </body>
</html>

