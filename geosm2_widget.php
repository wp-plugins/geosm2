<?PHP
/*
Plugin name: 	GeOSM2
Plugin URI:		http://chrsoft.net/?page_id=6
Description:	Uses the already set parameters geo_latitude and geo_longitude to put a marker on a embedded Open Street Map if public is set to 1. A perfect match with mobile blogging devices like iPhone
Version:		0.8.2
Author:			Christian Jensen
Author URI:		http://chrsoft.net/
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

	//image save function will save a new image if the old one is more than 24 hours old
function LoadImageCURL($save_to,$url)
{
	$update = true;
	
	if( file_exists($save_to) ) 
	{ 
		if( filectime($save_to)+(7*24*60*60) > time() ) { $update = false; }
	}  
	
	if ( $update ) 
	{
		$ch = curl_init($url);
		$fp = fopen($save_to, "wb");
		
		// set URL and other appropriate options
		$options = array(	CURLOPT_FILE => $fp, 
							CURLOPT_HEADER => 0, 
							CURLOPT_FOLLOWLOCATION => 0, //1 
							CURLOPT_TIMEOUT => 60);
		curl_setopt_array($ch, $options);
		curl_exec($ch);
		curl_close($ch);
		fclose($fp);	
	}
}


function add_GeOSM2_head() {
		// This is a part of the OpenStreetMap script.
	if ( is_single() ) {
		?>
		<link rel="stylesheet" href="<?php echo plugins_url('css/geosm2_map.css',__FILE__); ?>" type="text/css" />
		<?php
	}
}



class GeOSM2 extends WP_Widget {
	function GeOSM2() {
		parent::WP_Widget(false, $name = 'GeOSM2');
	}

	function form($instance) 
	{
        load_plugin_textdomain('geosm2',false, dirname(plugin_basename( __FILE__)).'/language');?>
            <p>
	            <label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:','geosm2'); ?>
	            <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>"
	            		type="text" value="<?php echo esc_attr($instance['title']); ?>" /></label>
            </p>
			<p>
				<label for="<?php echo $this->get_field_id( 'maptype' ); ?>"<?php _e('Map Type:','geosm2'); ?>
				<select class="widefat" id="<?php echo $this->get_field_id( 'maptype' ); ?>" name="<?php echo $this->get_field_name( 'maptype' ); ?>">
					<option <?php if ( 'cyclemap' == $instance['maptype'] ) echo 'selected="selected"'; ?>>cyclemap</option>
					<option <?php if ( 'mapnik' == $instance['maptype'] ) echo 'selected="selected"'; ?>>mapnik</option>
					<option <?php if ( 'osmarender' == $instance['maptype'] ) echo 'selected="selected"'; ?>>osmarender</option>
				</select></label>
			</p>
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

		/* Strip tags (if needed) and update the widget settings. */
		$instance['title'] = strip_tags( $new_instance['title'] );
		$instance['maptype'] = strip_tags( $new_instance['maptype'] );
		$instance['needle'] = strip_tags( $new_instance['needle'] );
		$instance['width'] = strip_tags( $new_instance['width'] );
		$instance['height'] = strip_tags( $new_instance['height'] );
		return $instance;

	}

	function widget($args, $instance)
	{

			// This widget is only shown on a single page, so we wanna do as little as possible as long as we are not there

		if ( is_single() )
		{
			global $post;

			$tilewidth = 256;
			$tileheight = 256;
			
			$path=ABSPATH.'wp-content/plugins/'.str_replace(basename( __FILE__),"",plugin_basename(__FILE__));
			
			$tiles = $path.'cache/tiles/';
			$maps = $path.'cache/maps/';
					
				//Check if the geotag is set to public or not
			if ((bool)get_post_meta($post->ID, 'geo_public', true)) 
			{
					//Defining a pattern to clean up spaces and digits in a coordinate
				$pattern = '/^(\-)?(\d{1,3})\.(\d{1,15})/';
					// Get Lon/Lat
				preg_match($pattern, get_post_meta($post->ID, 'geo_latitude', true), $matches);
				$lat = $matches[0];
				preg_match($pattern, get_post_meta($post->ID, 'geo_longitude', true), $matches);
				$lon = $matches[0];

				if (!empty($lon) && !empty($lat)) 
				{
		
					extract( $args );
					
					$title = apply_filters('widget_title', $instance['title'] );
					$maptype = $instance['maptype'];
					$needle = $instance['needle'];
					$width = $instance['width'];
					$height = $instance['height'];
					
									
						//Getting the zoom level, set to a standard if not defined
					$zoom = get_post_meta($post->ID, 'geo_zoom', true);
					if (empty($zoom)) { $zoom = 14; }	
		
					// get the tilenumber
					$xtile = floor((($lon + 180) / 360) * pow(2, $zoom));
					$ytile = floor((1 - log(tan(deg2rad($lat)) + 1 / cos(deg2rad($lat))) / pi()) /2 * pow(2, $zoom));
					
					// get the coordinate
					$xpin = floor($tilewidth*(((($lon + 180) / 360) * pow(2, $zoom)) - $xtile));
					$ypin = floor($tileheight*(((1 - log(tan(deg2rad($lat)) + 1 / cos(deg2rad($lat))) / pi()) /2 * pow(2, $zoom)) - $ytile));
					
					// upper left tile
					$ul_tile_x = $xtile - ceil((($width/2) - $xpin) / $tilewidth);
					$ul_tile_y = $ytile - ceil((($height/2) - $ypin) / $tileheight);
				
					// lower right tile
					$lr_tile_x = $xtile + ceil((($width/2) - ($tilewidth - $xpin)) / $tilewidth);
					$lr_tile_y = $ytile + ceil((($height/2) - ($tileheight - $ypin)) / $tileheight);

					$savefile = $zoom.'_'.$lat.'_'.$lon.'_'.$width.'x'.$height.'.png';


					$update = true;
					
					if( file_exists($maps.$savefile) ) 
					{ 
						if( filectime($maps.$savefile)+(7*24*60*60) > time() ) { $update = false; }
					}  
					
					if ( $update ) 
					{
	
					
					
						// Generate dumb pictureframe
						$tempimg = imagecreatetruecolor(($lr_tile_x-$ul_tile_x+1)*$tilewidth, ($lr_tile_y-$ul_tile_y+1)*$tileheight);
					
						for ($countery=$ul_tile_y; $countery<=$lr_tile_y;$countery+=1)
						{
							for ($counterx=$ul_tile_x; $counterx<=$lr_tile_x;$counterx+=1)
							{
								LoadImageCURL($tiles.$zoom.'-'.$counterx.'-'.$countery.'.png','http://b.tile.openstreetmap.org/'.$zoom.'/'.$counterx.'/'.$countery.'.png');
								
								$temptile = imagecreatefrompng($tiles.$zoom.'-'.$counterx.'-'.$countery.'.png');
								imagecopy($tempimg,$temptile,($counterx-$ul_tile_x)*$tilewidth,($countery-$ul_tile_y)*$tileheight,0,0,$tilewidth,$tileheight);
								imagedestroy($temptile);
								//echo 'http://tile.openstreetmap.org/'.$zoom.'/'.$counterx.'/'.$countery.'.png<br/>';
							}		
						}
						
						// chop temporary image
						$marker = imagecreatefrompng($path.'needle.png');
						
						$realimg = imagecreatetruecolor($width,$height);
						imagecopy($realimg,$tempimg,0,0,(($xtile-$ul_tile_x)*$tilewidth+$xpin-floor($width/2)),
							(($ytile-$ul_tile_y)*$tileheight+$ypin-floor($height/2)),$width,$height);
						imagecopy($realimg,$marker,floor(($width/2)-(imagesx($marker)/2)),floor($height/2-imagesy($marker)),0,0,imagesx($marker),imagesy($marker));
					
						imagepng($realimg,$maps.$savefile);
						imagedestroy($tempimg);
						imagedestroy($realimg);

					}

					echo $before_widget;
					echo $before_title.$title.$after_title;
					echo '<div class="geosm2">';
					echo '<img src="'.plugins_url('cache/maps/'.$savefile,__FILE__).'"/><br>';
					echo '<p>&copy;<a href="http://www.openstreetmap.org/" target="_new">OpenStreetMap</a> &amp; ';
					echo '<a href="http://creativecommons.org/licenses/by-sa/2.0/" target="_new">contributors, CC-BY-SA</a></p></div>';
					echo $after_widget;

				}
			}
		}
	}
}




?>
