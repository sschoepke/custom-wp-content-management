<?php
/*
Plugin Name: 	Custom Content Management Plugin
Plugin URI: 	https://github.com/sschoepke/content-management
Description: 	This is a custom content management wrapper plugin for Wordpress developers with examples
Version: 		0.0.1
Author: 		Stephen Schoepke
Author URI: 	http://sschoepke.com
License:		GNU General Public License v2 or later
License URI:	http://www.gnu.org/licenses/gpl-2.0.html
*/


/** CONFIG CUSTOMIZATIONS */

// Ensure unique post slugs
add_filter('wp_unique_post_slug', function ($slug, $post_ID, $post_status, $post_type, $post_parent) {
    global $wpdb, $wp_rewrite;
    // Don't touch hierarchical post types
    // $hierarchical_post_types = get_post_types( array('hierarchical' => true) );
    // if( in_array( $post_type, $hierarchical_post_types ) )
    // return $slug;
    if ('attachment' == $post_type) {
        //These will be unique anyway
        return $slug;
    }
    $feeds = $wp_rewrite->feeds;
    if (!is_array($feeds)) {
        $feeds = array();
    }
    // Lets make sure the slug is really unique:
    $check_sql = "SELECT post_name FROM $wpdb->posts WHERE post_name = %s AND ID != %d LIMIT 1";
    $post_name_check = $wpdb->get_var($wpdb->prepare($check_sql, $slug, $post_ID));
    if ($post_name_check || in_array($slug, $feeds)) {
        $suffix = 2;
        do {
            $alt_post_name   = substr($slug, 0, 200 - (strlen($suffix) + 1)) . "-$suffix";
            $post_name_check = $wpdb->get_var($wpdb->prepare($check_sql, $alt_post_name, $post_ID));
            $suffix++;
        } while ($post_name_check);
        $slug = $alt_post_name;
    }
    return $slug;
}, 10, 5);

// Set ACF JSON location
// See Advanced Custom Fields: https://www.advancedcustomfields.com/

add_filter('acf/settings/save_json', function () {
    return realpath(dirname(__FILE__)) . '/acf-json';
});

add_filter('acf/settings/load_json', function ($paths) {
    $paths[] = realpath(dirname(__FILE__)) . '/acf-json';
    return $paths;
});

/** CUSTOM POST TYPES, TAXONOMIES & OPTIONS PAGES */

// Create custom options pages

add_action('admin_menu', function(){
    
    add_menu_page('My Options Page', 'My Options Page', 'edit_posts', 'acf-options-my-options-page', function(){
        return '';
    });
    acf_add_options_sub_page(array(
        'title' => 'My Options Sub Page',
        'parent' => 'acf-options-my-options-sub-page',
        'menu' => 'My Options Sub Page'
    ));
});

// Create custom post types

function create_custom_post_types() {
    register_post_type('my_post_type', array(
        'labels' => array(
            'name' => __('My Post Type') ,
            'singular_name' => __('My Post Type') ,
        ),
        'with_front' => false,
        'menu_position' => 5,
        'public' => true,
        'menu_icon' => 'dashicons-slides',
        'rewrite' => array(
            'slug' => 'my-post-type'
        ),
        'supports' => array(
            'title',
            'revisions'
        ),
    ));
}

add_action('init', 'create_custom_post_types');

// Create custom taxonomies

function my_tax_init() {
    // create a new taxonomy
    register_taxonomy('my_tax', array(
        'my_tax'
    ), array(
        'label' => __('My Tax') ,
        'rewrite' => array(
            'slug' => 'my-tax'
        ) ,
        'show_tagcloud' => false,
        'hierarchical' => true,
        'show_admin_column' => true
    ));
}

add_action('init', 'my_tax_init');

/** ADMIN MENU CUSTOMIZATION */

// Remove unnecessary menu items

// function remove_menu_items() {
//     remove_menu_page('edit.php?post_type=acf-field-group');
// }

// add_action('admin_menu', 'remove_menu_items');

// Custom menu order

function set_custom_menu_order($menu_order) {
    return array(
    		'index.php', 				                 // Dashboard
    		'edit.php?post_type=page',	                 // Pages
    		'edit.php?post_type=my-post-type',	         // My Post Type
            'edit.php',                                  // Blog Posts
    		'edit-comments.php',		                 // Comments
    		'uploads.php',								 // Media
    		'themes.php',				                 // Appearance
    		'plugins.php',			           	         // Plugins
    		'users.php',				                 // Users
    		'tools.php',				                 // Tools
    		'options-general.php',		                 // Settings
        );
}

add_filter('custom_menu_order', function() {
    return true;
});

add_filter('menu_order', 'set_custom_menu_order');

// Rename Posts to Blog Posts

// function change_post_menu_label() {
//     global $menu;
//     global $submenu;
//     $menu[5][0] = 'Blog Posts';
//     $submenu['edit.php'][5][0] = 'Blog Posts';
//     $submenu['edit.php'][10][0] = 'Add Blog Posts';
//     echo '';
// }

// function change_post_object_label() {
//         global $wp_post_types;
//         $labels = &$wp_post_types['post']->labels;
//         $labels->name = 'Blog Posts';
//         $labels->singular_name = 'Blog Post';
//         $labels->add_new = 'Add Blog Post';
//         $labels->add_new_item = 'Add Blog Post';
//         $labels->edit_item = 'Edit Blog Post';
//         $labels->new_item = 'Blog Post';
//         $labels->view_item = 'View Blog Post';
//         $labels->search_items = 'Search Blog Posts';
//         $labels->not_found = 'No Blog Posts found';
//         $labels->not_found_in_trash = 'No Blog Posts found in Trash';
// }

// add_action( 'init', 'change_post_object_label' );
// add_action( 'admin_menu', 'change_post_menu_label' );

// Enqueue admin styles

// add_action('admin_enqueue_scripts', function() {
//     wp_enqueue_style('custom-admin', get_stylesheet_directory_uri() . '/static/css/admin.css');
// });

// Add post title to admin body classes

// if(is_admin()) {
//     function give_admin_body_class( $classes ) {   

//         $this_post_title = strtolower( get_the_title() ) . '-page-admin';   

//         $classes .= ' ' . $this_post_title . ' ';
     
//         return $classes;
//     }

//     add_action( 'admin_body_class', 'give_admin_body_class' );
// }

// Remove WP Admin bar when viewing the site because it's annoying

function remove_admin_bar() {
    return false;
}

add_filter('show_admin_bar', 'remove_admin_bar');
