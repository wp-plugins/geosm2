=== Plugin Name ===
Contributors: Christian Jensen
Donate link: http://chrsoft.net/?page_id=18
Tags: Geotag, Geographical, Position, OpenStreetMap, Map, Geosm, Geosm2
Requires at least: 2.8
Tested up to: 3.0
Stable tag: 0.8.3

GeOSM2 is a widget that adds a minimap to your sidebar(s) displaying the location where you posted the entry.

== Description ==

GeOSM2 will search your post for a set of tags. Entering geo_longitude, geo_latitude and geo_public will give you a coordinate and
an acceptance from the blogger to use the location added. The iphone program (amongst others) will add these parameters if you tag
the entry.

The map is loaded from the OpenStreetMap (OSM) project. The rendering will happen on the OSM servers. Traffic heavy pages should
consider the amount of requests to their servers and also consider contributing to the OSM project. (www.openstreetmap.org)

The map data it self is filed under the terms of the Creative Commons Attribution-ShareAlike 2.0 license.
(http://creativecommons.org/licenses/by-sa/2.0/)

== Installation ==

This section describes how to install the plugin and get it working.

1. Download the plugin package and extract the archive.
2. Move/Copy the folder to wp-content/plugins
3. Activate the plugin through the 'Plugins' menu in WordPress
4. Move the Widget into a desired position in your menu system

== Frequently Asked Questions ==
Q: Why aren't my map showing up?
A: This have many causes. The main one being that your host is grabing too many tiles from the OpenStreetMap server. Consider establishing your own tile server.

Q: I still see needles even if I removed the "needle" tag from the widget setup
A: This is a error prior to version 0.8.3. Upgrade to atleast 0.8.3 to make it work


== Screenshots ==

1. The minimap shown in single view on a post
2. The parameters in your post panel
3. The widget setup panel, also notice the second title when last known position is available
4. The minimap with the secondary header when last known position is in use
5. The new setup panel.

== Changelog ==

= 0.9 =
- New needle (thanks Geoff)
- Documented the geo_zoom function
- Last known position will provide a minimal even if you didn't geotag the post (it will use the last known position instead)
- A new settings panel that will help you customize non-widget related information
- A shortcode called geosm2map that will show all your post locations on one big map.

= 0.8.3 =
- Fixed up a error where the different maptypes still where showing. This might be implimented on a later stadium, but right now I see no use of it and have no 
requests for it.
- Added a clickable map function so that one can get a bigger view of the actual map. Works very well together with Fancybox 
(http://wordpress.org/extend/plugins/fancy-box/)
- Implimented the posibility of removing the needle from the map.


= 0.8.2 =
Added support for localization and updated some links


= 0.8.1 =
Updating the versions in all files.

= 0.8 =
The javascripts getting the images was a bit of a problem because too many fetches caused some users to be locked out of the OSM servers.
This version is converted to PHP with a tile cache updating every 7 days on request of the specific picture/map.

= 0.7 =
Minor upgrades and commenting to meet the requirements of Wordpress.org

== Upgrade Notice ==

= 0.69 =
This is an internal version that never got distributed.
