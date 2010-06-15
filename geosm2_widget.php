<?PHP
/*
Plugin name: 	GeOSM2
Plugin URI:		http://...
Description:	Uses the already set parameters geo_latitude and geo_longitude to put a marker on a embedded Open Street Map if public is set to 1. A perfect match with mobile blogging devices like iPhone
Version:		0.1 (alfa)
Author:			Christian Jensen
Author URI:		http://
License:		GPL2
*/

/*  Copyright 2010 Christian Jensen (email : varsling@chrsoft.net)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as 
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

	// register the widget
add_action( 'widgets_init', 'GeOSM2_load_widgets' );

	// register the header
add_action('wp_head', 'add_GeOSM2_head');


function GeOSM2_load_widgets() {
	register_widget( 'GeOSM2' );
}


class GeOSM2 extends WP_Widget {
	function GeOSM2() {
		parent::WP_Widget(false, $name = 'GeOSM2');
	}

	function form($instance) {
        ?>
            <p>
	            <label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:'); ?>
	            <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>"
	            		type="text" value="<?php echo esc_attr($instance['title']); ?>" /></label>
            </p>
			<p>
				<label for="<?php echo $this->get_field_id( 'maptype' ); ?>">Map Type:
				<select class="widefat" id="<?php echo $this->get_field_id( 'maptype' ); ?>" name="<?php echo $this->get_field_name( 'maptype' ); ?>">
					<option <?php if ( 'cyclemap' == $instance['maptype'] ) echo 'selected="selected"'; ?>>cyclemap</option>
					<option <?php if ( 'mapnik' == $instance['maptype'] ) echo 'selected="selected"'; ?>>mapnik</option>
					<option <?php if ( 'osmarender' == $instance['maptype'] ) echo 'selected="selected"'; ?>>osmarender</option>
				</select></label>
			</p>
			<p>
				<label for="<?php echo $this->get_field_id( 'needle' ); ?>">
				<input class="checkbox" type="checkbox" <?php if ( 'on' == $instance['needle'] ) echo 'checked'; ?> id="<?php echo $this->get_field_id( 'needle' ); ?>" name="<?php echo $this->get_field_name( 'needle' ); ?>"/>
				Show the needle?</label>
			</p>

        <?php 
	}

	function update($new_instance, $old_instance) {
		// processes widget options to be saved
		$instance = $old_instance;

		/* Strip tags (if needed) and update the widget settings. */
		$instance['title'] = strip_tags( $new_instance['title'] );
		$instance['maptype'] = strip_tags( $new_instance['maptype'] );
		$instance['needle'] = strip_tags( $new_instance['needle'] );

		return $instance;

	}

	function widget($args, $instance) {

			// This widget is only shown on a single page, so we wanna do as little as possible as long as we are not there

		if ( is_single() ) {
			global $post;
			
				//Check if the geotag is set to public or not
			if ((bool)get_post_meta($post->ID, 'geo_public', true)) {
	
				extract( $args );
				
				$title = apply_filters('widget_title', $instance['title'] );
				$maptype = $instance['maptype'];
				$needle = $instance['needle'];
				
					//Defining a pattern to clean up spaces and digits in a coordinate
				$pattern = '/^(\-)?(\d{1,3})\.(\d{1,15})/';
				
					// Get Lon/Lat
				preg_match($pattern, get_post_meta($post->ID, 'geo_latitude', true), $matches);
				$latitude = $matches[0];
				preg_match($pattern, get_post_meta($post->ID, 'geo_longitude', true), $matches);
				$longitude = $matches[0];
				
					//Getting the zoom level, set to a standard if not defined
				$zoom = get_post_meta($post->ID, 'geo_zoom', true);
				if (empty($zoom)) { $zoom = 14; }	

				echo $before_widget;
				echo $before_title.$title.$after_title;

				echo '<div class="geosm2"><div style="width:278px; height:200px" id="map"><script type="text/javascript">ShowMap('.$longitude.','.$latitude.','.$zoom.',\''.$maptype.'\',\''.$needle.'\');</script></div>';
				echo '<p>&copy;<a href="http://www.openstreetmap.org/" target="_new">OpenStreetMap</a> &amp; ';
				echo '<a href="http://creativecommons.org/licenses/by-sa/2.0/" target="_new">contributors, CC-BY-SA</a></p></div>';
				echo $after_widget;
			}
		}
	}
}

function add_GeOSM2_head() {
		// This is a part of the OpenStreetMap script.
	if ( is_single() ) {

		?>
		<script src="<?php echo plugins_url('js/OpenLayers.js',__FILE__); ?>"></script>
		<script src="<?php echo plugins_url('js/OpenStreetMap.js',__FILE__); ?>"></script>
		<script type="text/javascript">
	 		function ShowMap(lon,lat,zoom,type,needle) {
				var map;
				map = new OpenLayers.Map ("map", {
					controls:[
					//new OpenLayers.Control.Attribution()
					new OpenLayers.Control.ArgParser()
					],
					maxExtent: new OpenLayers.Bounds(-20037508.34,-20037508.34,20037508.34,20037508.34),
								maxResolution: 156543.0399,
					numZoomLevels: 19,
					units: "m",
					projection: new OpenLayers.Projection("EPSG:900913"),
					displayProjection: new OpenLayers.Projection("EPSG:4326")
				} );
				switch (type)
				{
				case 'osmarender':
					layerTilesAtHome = new OpenLayers.Layer.OSM.Osmarender("Osmarender");
					map.addLayer(layerTilesAtHome);
					break;
				case 'mapnik':
					layerMapnik = new OpenLayers.Layer.OSM.Mapnik("Mapnik");
					map.addLayer(layerMapnik);
					break;
				case 'cyclemap':
					layerCycleMap = new OpenLayers.Layer.OSM.CycleMap("CycleMap");
					map.addLayer(layerCycleMap);
					break;
				default:
				}
				if (needle=='on') {
					layerMarkers = new OpenLayers.Layer.Markers("Markers");
					map.addLayer(layerMarkers);
				}
				var lonLat = new OpenLayers.LonLat(lon, lat).transform(new OpenLayers.Projection("EPSG:4326"), map.getProjectionObject());
				map.setCenter (lonLat, zoom);
		
				var size = new OpenLayers.Size(18,25);
				var offset = new OpenLayers.Pixel(0, -size.h);
				var icon = new OpenLayers.Icon("<?php echo plugins_url('needle.png',__FILE__); ?>",size,offset);
				layerMarkers.addMarker(new OpenLayers.Marker(lonLat,icon));
	 		}
		</script>
		
		<link rel="stylesheet" href="<?php echo plugins_url('css/geosm2_map.css',__FILE__); ?>" type="text/css" />
		
		<?php
	}
}


?>