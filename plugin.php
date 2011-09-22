<?php
/*
Plugin Name: Top Commenters (Cached!)
Plugin URI: http://github.com/tommcfarlin/top-commentered-cached
Description: A plugin used to demonstrate the WordPress Transients API for an Envato blog series.
Version: 1.0
Author: Tom McFarlin
Author URI: http://tommcfarlin.com
Author Email: tom@tommcfarlin.com
License:

  Copyright 2011 Tom McFarlin (tom@tommcfarlin.com)

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

class Top_Commenters_Cached extends WP_Widget {

	const name = 'Top Commenters (Cached!)';
	const locale = 'top-commenters-cached-locale';
	const slug = 'top-commenters-cached';
	

	/*--------------------------------------------------*/
	/* Constructor
	/*--------------------------------------------------*/
	
	/**
	 * The widget constructor. Specifies the classname and description, instantiates
	 * the widget, loads localization files, and includes necessary scripts and
	 * styles.
	 */
	function Top_Commenters_Cached() {

    	// TODO: update classname and description
		$widget_opts = array (
			'classname' => self::name, 
			'description' => __('A plugin used to demonstrate the WordPress Transients API for an Envato blog series.', self::locale)
		);	
		$this->WP_Widget(self::slug, __(self::name, self::locale), $widget_opts);
		
		load_plugin_textdomain(self::locale, false, dirname(plugin_basename( __FILE__ ) ) . '/lang/' );
		
    	// Load JavaScript and stylesheets
    	$this->register_scripts_and_styles();
		
	} // end constructor

	/*--------------------------------------------------*/
	/* API Functions
	/*--------------------------------------------------*/
	
	/**
	 * Outputs the content of the widget.
	 *
	 * @args			The array of form elements
	 * @instance
	 */
	function widget($args, $instance) {
	
		extract($args, EXTR_SKIP);
		
		echo $before_widget;
		
		$widget_title = empty($instance['widget_title']) ? '' : apply_filters('widget_title', $instance['widget_title']);
		$commenters = $this->query_for_commenters();
    
		// Display the widget
		include(WP_PLUGIN_DIR . '/' . self::slug . '/views/widget.php');
		
		echo $after_widget;
		
	} // end widget
	
	/**
	 * Processes the widget's options to be saved.
	 *
	 * @new_instance	The previous instance of values before the update.
	 * @old_instance	The new instance of values to be generated via the update.
	 */
	function update($new_instance, $old_instance) {
		
		$instance = $old_instance;
		
		$instance['widget_title'] = $this->strip($new_instance, 'widget_title');
    
		return $instance;
		
	} // end widget
	
	/**
	 * Generates the administration form for the widget.
	 *
	 * @instance	The array of keys and values for the widget.
	 */
	function form($instance) {
	
		$instance = wp_parse_args(
			(array)$instance,
			array(
				'widget_title' => ''
			)
		);
	
		$widget_title = $this->strip($instance, 'widget_title');
		
		// Display the admin form
    	include(WP_PLUGIN_DIR . '/' . self::slug . '/views/admin.php');
		
	} // end form

	/*--------------------------------------------------*/
	/* Private Functions
	/*--------------------------------------------------*/
	
	/**
	 * Retrieves the weekly top commenters for the past week and stores the values in the cache.
	 * If the cache is empty, then the function will request information from the database and 
	 * store it in the cache.
	 */
	private function query_for_commenters() {
	
		$commenters = null;
		
		// check to see if the transient exists. set it if it's expired or missing
		if(!get_transient('top_commenters_cached')) {

			// query the database for the top commenters
			global $wpdb;
			$commenters = $wpdb->get_results("
				select count(comment_author) as comments_count, comment_author, comment_type
				from $wpdb->comments
				where comment_type != 'pingback'
				and comment_author != ''
				and comment_approved = '1'
				group by comment_author
				order by comment_author desc
				LIMIT 10
			");

			// store the result 
			set_transient('top_commenters_cached', $commenters, 60 * 60 * 12);
			
		} // end if 
		
		// transient is guaranteed to exist now, so return it
		return get_transient('top_commenters_cached');
	
	} // end query_for_commenters
	
	/*--------------------------------------------------*/
	/* Helper Functions
	/*--------------------------------------------------*/
  
	/**
	 * Convenience method for stripping tags and slashes from the content
	 * of a form input.
	 *
	 * @obj			The instance of the argument array
	 * @title		The title of the element from which we're stripping tags and slashes.
	 */
	private function strip($obj, $title) {
		return strip_tags(stripslashes($obj[$title]));
	} // end strip
  
	/**
	 * Registers and enqueues stylesheets for the administration panel and the
	 * public facing site.
	 */
	private function register_scripts_and_styles() {
		if(is_admin()) {
      		$this->load_file(PLUGIN_NAME, '/' . self::slug . '/js/admin.js', true);
			$this->load_file(PLUGIN_NAME, '/' . self::slug . '/css/admin.css');
		} else { 
      		$this->load_file(PLUGIN_NAME, '/' . self::slug . '/js/widget.js', true);
			$this->load_file(PLUGIN_NAME, '/' . self::slug . '/css/widget.css');
		} // end if/else
	} // end register_scripts_and_styles

	/**
	 * Helper function for registering and enqueueing scripts and styles.
	 *
	 * @name	The 	ID to register with WordPress
	 * @file_path		The path to the actual file
	 * @is_script		Optional argument for if the incoming file_path is a JavaScript source file.
	 */
	private function load_file($name, $file_path, $is_script = false) {
		
    	$url = WP_PLUGIN_URL . $file_path;
		$file = WP_PLUGIN_DIR . $file_path;
    
		if(file_exists($file)) {
			if($is_script) {
				wp_register_script($name, $url);
				wp_enqueue_script($name);
			} else {
				wp_register_style($name, $url);
				wp_enqueue_style($name);
			} // end if
		} // end if
    
	} // end load_file
	
} // end class
add_action('widgets_init', create_function('', 'register_widget("Top_Commenters_Cached");')); 
?>