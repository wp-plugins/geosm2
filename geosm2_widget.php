<?PHP

class GeOSM2_widget extends WP_Widget {
	function GeOSM2_widget() {
		parent::WP_Widget('GeOSM2_widget', 'GeOSM2 Widget', array('description' => __('Minimap from the GeOSM2 plugin','geosm2')));
	}

	function form($instance) 
	{
        load_plugin_textdomain('geosm2',false, dirname(plugin_basename( __FILE__)).'/language'); ?>
            <p>
	            <label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:','geosm2'); ?>
	            <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>"
	            		type="text" value="<?php echo esc_attr($instance['title']); ?>" /></label>
            </p>
			<? if(get_option('geosm2_option_lastknown') == 'on') { ?>
            <p>
	            <label for="<?php echo $this->get_field_id('title_lastknown'); ?>"><?php _e('Title when last known position is in use:','geosm2'); ?>
	            <input class="widefat" id="<?php echo $this->get_field_id('title_lastknown'); ?>" name="<?php echo $this->get_field_name('title_lastknown'); ?>"
	            		type="text" value="<?php echo esc_attr($instance['title_lastknown']); ?>" /></label>
            </p>
            <? } ?>
            
			<p>
				<label for="<?php echo $this->get_field_id('width'); ?>"><?php _e('Width','geosm2'); ?>:
				<input id="<?php echo $this->get_field_id('width'); ?>" name="<?php echo $this->get_field_name('width'); ?>"
					type="text" value="<?php echo esc_attr($instance['width']); ?>" />
				</label>
			</p>
			<p>
				<label for="<?php echo $this->get_field_id('height'); ?>"><?php _e('Height','geosm2'); ?>:
				<input id="<?php echo $this->get_field_id('height'); ?>" name="<?php echo $this->get_field_name('height'); ?>"
					type="text" value="<?php echo esc_attr($instance['height']); ?>" />
				</label>
			</p>
			<p>
				<label for="<?php echo $this->get_field_id( 'needle' ); ?>">
				<input class="checkbox" type="checkbox" <?php if ( 'on' == $instance['needle'] ) echo 'checked'; ?> id="<?php echo $this->get_field_id( 'needle' ); ?>" name="<?php echo $this->get_field_name( 'needle' ); ?>"/>
				<?php _e('Show the needle?','geosm2'); ?></label>
			</p>

        <?php 
	}

	function update($new_instance, $old_instance) 
	{
		// processes widget options to be saved
		$instance = $old_instance;

		$instance['title'] = strip_tags( $new_instance['title'] );
		$instance['title_lastknown'] = strip_tags( $new_instance['title_lastknown'] );
		$instance['needle'] = strip_tags( $new_instance['needle'] );
		$instance['width'] = strip_tags( $new_instance['width'] );
		$instance['height'] = strip_tags( $new_instance['height'] );
		
		return $instance;

	}

	function widget($args, $instance)
	{

			// This widget is only shown on a single page, so we wanna do as little as possible as long as we are not there

		//get_option('geosm2_option_maptype') = 'mapnik';

		if ( is_single() )
		{
			global $post;
					
				//Check if the geotag is set to public or not
			if (((bool)get_post_meta($post->ID, 'geo_public', true))  or (get_option('geosm2_option_lastknown') == 'on'))
			{
				
					//Defining a pattern to clean up spaces and digits in a coordinate
				$pattern = '/^(\-)?(\d{1,3})\.(\d{1,15})/';
				
					// Get Lon/Lat
				preg_match($pattern, get_post_meta($post->ID, 'geo_latitude', true), $matches);
				$lat = $matches[0];
				preg_match($pattern, get_post_meta($post->ID, 'geo_longitude', true), $matches);
				$lon = $matches[0];
				
				if (empty($lon) || empty($lat)) 
				{
					$lat = get_option('geosm2_option_lastknown_lat');
					$lon = get_option('geosm2_option_lastknown_lon');
					$zoom = get_option('geosm2_option_lastknown_zoom');
					$title = apply_filters('widget_title', $instance['title_lastknown'] );
				}
				else
				{
					$zoom = get_post_meta($post->ID, 'geo_zoom', true);
					if (empty($zoom)) { $zoom = 14; }
					$title = apply_filters('widget_title', $instance['title'] );

				}
				
				if (!empty($lon) && !empty($lat)) 
				{
		
					extract( $args );

						//Checking if we want to add location in text
					if (get_option('geosm2_option_autoupdate') == 'on') { geosm2_fillnames($post->ID,$lon,$lat); }
					
					$needle = $instance['needle'];
					$width = $instance['width'];
					$height = $instance['height'];

					$clickable = get_option('geosm2_option_clickable');
					$cwidth = get_option('geosm2_option_cwidth');
					$cheight = get_option('geosm2_option_cheight');

					$streetname = get_post_meta($post->ID,'geo_streetname',true);
					$cityname = get_post_meta($post->ID,'geo_cityname',true);
					
					if (!empty($streetname) && !empty($cityname)) { $extrainfo = 'alt="'.$streetname.', '.$cityname.'"'; }
					
						//Getting the zoom level, set to a standard if not defined

					$savefile = get_option('geosm2_option_maptype').'_'.$zoom.'_'.$lat.'_'.$lon.'_'.$width.'x'.$height.'_'.$needle.'.png';
					$clicked_savefile = get_option('geosm2_option_maptype').'_'.$zoom.'_'.$lat.'_'.$lon.'_'.$cwidth.'x'.$cheight.'_'.$needle.'.png';
					
					GenerateImage($zoom, $width, $height, $savefile, $lat, $lon, $needle);
					if ( ($clickable == 'on' ) && (!empty($cwidth)) && (!empty($cheight)) )
					{						
						GenerateImage($zoom, $cwidth, $cheight, $clicked_savefile, $lat, $lon, $needle);
						$before_url = '<a href="'.plugins_url('cache/maps/'.$clicked_savefile,__FILE__).'">';
						$after_url = '</a>';
					}
					
					echo $before_widget;
					echo $before_title.$title.$after_title;
					echo '<div class="geosm2">';
					echo $before_url.'<img '.$extrainfo.' src="'.plugins_url('cache/maps/'.$savefile,__FILE__).'"/>'.$after_url.'<br>';
					echo '<p>&copy;<a href="http://www.openstreetmap.org/" target="_new">OpenStreetMap</a> &amp; ';
					echo '<a href="http://creativecommons.org/licenses/by-sa/2.0/" target="_new">contributors, CC-BY-SA</a></p></div>';
					echo $after_widget;

				}
			}
		}
	}
}




?>
