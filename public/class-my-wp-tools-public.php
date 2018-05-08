<?php

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @link       https://github.com/Dualcon/my-wp-tools.git
 * @since      1.0.0
 *
 * @package    My_Wp_Tools
 * @subpackage My_Wp_Tools/public
 * @author     Dualcon <dualconcompany@gmail.com>
 */

class My_Wp_Tools_Public
{

    /**
     * The ID of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string $plugin_name The ID of this plugin.
     */
    private $plugin_name;


    /**
     * The version of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string $version The current version of this plugin.
     */
    private $version;


    /**
     * Initialize the class and set its properties.
     *
     * @since    1.0.0
     * @param      string $plugin_name The name of the plugin.
     * @param      string $version The version of this plugin.
     */
    public function __construct($plugin_name, $version)
    {
        $this->plugin_name = $plugin_name;
        $this->version = $version;
    }


    /**
     * Register the stylesheets for the public-facing side of the site.
     *
     * @since    1.0.0
     */
    public function enqueue_styles()
    {
        // Load bootstrap.
        wp_enqueue_style('bootstrap-css', plugin_dir_url(__FILE__) . 'css/bootstrap.3.1.1.min.css', array(), '3.1.1', 'all');
        // Custom CSS file.
        wp_enqueue_style($this->plugin_name, plugin_dir_url(__FILE__) . 'css/my-wp-tools-public.css', array(), $this->version, 'all');
    }


    /**
     * Register the JavaScript for the public-facing side of the site.
     *
     * @since    1.0.0
     */
    public function enqueue_scripts()
    {
        // Load the bootstrap js files.
        wp_enqueue_script('bootstrap-js', plugin_dir_url(__FILE__) . 'css/bootstrap.3.1.1.min.js', array(), '3.1.1', 'all');
        // Custom js.
        wp_enqueue_script($this->plugin_name, plugin_dir_url(__FILE__) . 'js/my-wp-tools-public.js', array('jquery'), $this->version, false);
    }


    /**
     * Count post views.
     */

    function mwt_post_views($content)
    {
        if (is_single() && !is_home()) {
            global $post;
            $content .= $this->mwt_getPostViews($post->ID);
        }
        return $content;
    }


    // function to count views.
    function mwt_getPostViews($postID)
    {
        $count_key = 'post_views_count';
        $count = get_post_meta($postID, $count_key, true);
        if ($count == '') {
            delete_post_meta($postID, $count_key);
            add_post_meta($postID, $count_key, '1');
            return "<br><span>Views: 1</span>";
        }
        return '<br><span>Views: ' . $count . '</span>';
    }


    // function to display number of posts.
    function mwt_setPostViews($post)
    {
        $postID = $post->ID;
        $count_key = 'post_views_count';
        $count = get_post_meta($postID, $count_key, true);
        if ($count == '') {
            $count = 1;
            delete_post_meta($postID, $count_key);
            add_post_meta($postID, $count_key, '1');
        } else {
            $count++;
            update_post_meta($postID, $count_key, $count);
        }
    }


    /**
     * Adds a grid of the most popular posts for today using a shortcode.
     */
	 
	 
	 function mwpt_most_popular_latest($atts) {
	 
	 if (is_home() || is_front_page()) {

	 // Define attributes and their defaults.
	 extract( shortcode_atts( array (
	 // This is the original Wordpress variable.
	//'numberposts' => '24',
	'limit' => '10',
	'offset' => 0,
	'category' => 0,
	'orderby' => 'post_date',
	'order' => 'DESC',
	'include' => '',
	'exclude' => '',
	'meta_key' => '',
	'meta_value' =>'',
	'post_type' => 'post',
	'post_status' => 'publish',
	'suppress_filters' => true,
	// This is not from wordpress, just a custom tag.
	'header' => ''
), $atts));
	 
	 $popular_posts = wp_get_recent_posts(array('numberposts' => $limit));

	 // Get the thumbnail url, the thumbnail alt, the post title of each post.
            $ppop_post = [];
            $res = '';
			foreach ($popular_posts as $item) {
                
				$thumb_id = get_post_thumbnail_id($item['ID']);
				$thumb_url = wp_get_attachment_image_src($thumb_id, 'thumbnail', true);
                $thumb_alt = (get_post_meta($thumb_id, '_wp_attachment_image_alt', true) != '') ? get_post_meta($thumb_id, '_wp_attachment_image_alt', true) : $item['post_title'];
				
                // Create the object for each post.
                $pop = new stdClass();
                $pop->thumb_url = $thumb_url[0];
                $pop->thumb_alt = $thumb_alt;
                $pop->post_url = get_permalink($item['ID']);
                $pop->post_title = $item['post_title'];
                array_push($ppop_post, $pop);

            }
	
			return $content .
			(($header) ? '<p><h3>' . $header . '</h3></p>' : '') .
			$this->create_grid($ppop_post);
			
		}

        return $content;
	 }
	 
    function mwpt_most_popular_today($atts) {
        
		if (is_home() || is_front_page()) {

		// Define attributes and their defaults.
		extract( shortcode_atts( array (
		'post_type' => 'post, page', // post,page.
		'range' => 'last24hours', // last24hours, last7days, last30days, all, custom.
		'time_quantity' => 24, // Integer.
		'title_length' => 25, // integer.
		'time_unit' => 'hour', // minute, hour, day, week, month.
		'order_by' => 'views', // comments, views, avg (for average views per day).
		'limit' => 10, // Integer.
		'header' => None // String
		), $atts));

            // Query the database with the wordpress popular posts plugin.
            $popular_posts = new WPP_Query(array('post_type' => $post_type, 'range' => $range, 'order_by' => $order_by, 'limit' => $limit, 'time_quantity' => $time_quantity, 'title_length' => $title_length, 'time_unit' => $time_unit));
			
// Get the thumbnail url, the thumbnail alt, the post title of each post.
            $ppop_post = [];
            foreach ($popular_posts->get_posts() as $item) {
                $thumb_id = get_post_thumbnail_id($item->id);
                $thumb_url = wp_get_attachment_image_src($thumb_id, 'thumbnail', true);
                $thumb_alt = (get_post_meta($thumb_id, '_wp_attachment_image_alt', true) != '') ? get_post_meta($thumb_id, '_wp_attachment_image_alt', true) : $item->title;

                // Create the object for each post.
                $pop = new stdClass();
                $pop->thumb_url = $thumb_url[0];
                $pop->thumb_alt = $thumb_alt;
                $pop->post_url = get_permalink($item->id);
                $pop->post_title = $item->title;
                array_push($ppop_post, $pop);

            }

            return $content .
			(($header) ? '<p><h3>' . $header . '</h3></p>' : '') .
			$this->create_grid($ppop_post);
        
		}

        return $content;
    
	}


    function create_grid($ppop_post)
    {
        $grid = '<div class="pt-cv-wrapper">';

        $col = 1;
        $max_col = 4;
        $num_array_elements = 1;
        foreach ($ppop_post as $item) {

            if ($col % $max_col == 1) {
                $grid .= '<div class="pt-cv-view pt-cv-grid pt-cv-colsys">';
            }

            $grid .= '<div class="col-md-3 col-sm-3 col-xs-3 pt-cv-content-item pt-cv-1-col">' .
                '<a href="' . $item->post_url . '"><img src="' . $item->thumb_url . '" alt="' . $item->thumb_alt . '" class="pt-cv-thumbnail" height="225" width="225"></a>' .
                '<h4 style="font-size:17px;"><a href="' . $item->post_url . '"><b>' . $item->post_title . '</b></a></h4>' .
                '</div>';

            if ($col % $max_col == 0 || $num_array_elements == count($ppop_post)) {
                $grid .= '</div>';
            }

            $col++;
            $num_array_elements++;
        }

        $grid .= '</div>';

        return $grid;
    }

}
