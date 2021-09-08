<?php
/**
 */
/*
Plugin Name: Api Tabs
Description: Posts list tabs using WP Rest API
Version: 1.0.0
Author: Maciej Molenda
Author URI: https://profiles.wordpress.org/maciex777/
License: GPLv2 or later
*/

defined( 'ABSPATH' ) || exit;

define( 'APITABS_URL', plugin_dir_url( __FILE__ ) );

/**
  * Podpięcie skryptów
  */
function apitabs_load_scripts(){
  wp_enqueue_script('apitabs-script', APITABS_URL . 'main.js', array('jquery'), false, true);
  wp_enqueue_style('apitabs-style', APITABS_URL . 'style.css');

  // pobranie URL strony do globalnej zmiennej javascript
  wp_localize_script('apitabs-script', 'WPURLS', array( 'siteurl' => get_option('siteurl') ));
}
add_action('wp_enqueue_scripts', 'apitabs_load_scripts');


add_action('rest_api_init', function(){
  // Rejestracja własnej ścieżki dla wszystkich wpisów
  register_rest_route('apitabs/v1', 'posts',  [
    'methods' => 'GET',
    'callback' => 'apitabs_posts',
    'permission_callback' => function() { return ''; }
  ]);

  // Rejestracja własnej ścieżki dla wszystkich wpisów z danej kategorii
  register_rest_route('apitabs/v1', 'posts/categories=(?P<category_id>\d+)',
    array(
      'methods' => 'GET',
      'callback' => 'apitabs_categories',
      'permission_callback' => function() { return ''; }
    )
  );
});

/**
  * Własny endpoint dla wszystkich wpisów
  */
function apitabs_posts(){
  $args = [
    'numerposts' => 99999,
    'post_type' => 'post'
  ];

  // Jeśli nie ma wpisów
  if( empty( $args ) ){
        return new WP_Error( 'no_post_found', 'there is no posts', array( 'status' => 404 ) );
    }

   return throw_posts_data($args);
}

/**
  * Własny endpoint dla wszystkich wpisów z danej kategorii
  */
function apitabs_categories($request){
  // return $request['category_id'];
  if(isset($request['category_id'])){
    $args = [
      'category'   => $request['category_id'],
      'numerposts' => 99999,
      'post_type'  => 'post',
    ];
  }

  // Jeśli nie ma wpisów
   if( empty( $args ) ){
       return new WP_Error( 'no_post_found', 'there is no posts in this category', array( 'status' => 404 ) );
   }

   return throw_posts_data($args);
}

/**
  * Wyrzucenie konkretnych danych dla własnego endpointa
  */
function throw_posts_data($args){
  $posts = get_posts($args);

  $data = [];
  $i = 0;

  foreach($posts as $post){
    $data[$i]['id'] = $post->ID;
    $data[$i]['title'] = $post->post_title;
    $data[$i]['content'] = wp_trim_words($post->post_content, 70, '...');
    $data[$i]['slug'] = $post->post_name;
    $data[$i]['featured_image']['thumbnail'] = get_the_post_thumbnail_url($post->ID, 'thumbnail');
    $data[$i]['featured_image']['medium'] = get_the_post_thumbnail_url($post->ID, 'medium');
    $data[$i]['featured_image']['large'] = get_the_post_thumbnail_url($post->ID, 'large');
    $output_categories = array();
    $categories=get_the_category($post->ID);
      foreach($categories as $category) {
         $output_categories[] = $category->cat_ID;
    }
    $data[$i]['categories'] = $output_categories;
    $i++;
  }

  return $data;
}


/**
  * utwórzenie shortcode, aby wyświetlić w nim tabsy i ich zawartość
  */
function apitabs_show_content(){
  // $postsList = wp_list_categories('echo=0&show_count=1&title_li=<h2>Categories</h2>');
  $output = '';
  // $output .= '<div id="apitabs-tabs">'.$postsList.'</div>';
  $output .= '<div id="apitabs-tabs"><ul>';
  $output .= '<li class="cat-item" data-id="all"><button>Wszystkie</button></li>';
  $categories = get_categories();
  foreach ( $categories as $category ) :
     $output .= '<li class="cat-item" data-id="'.$category->cat_ID.'"><button>'.$category->name.'</button></li>';
  endforeach;
  $output .= '</ul></div>';
  $output .= '<div id="apitabs-posts-container"></div>';

  return $output;
}

// zainicjowanie shortcode [apitabs]
function apitabs_shortcodes_init()
{
    add_shortcode('apitabs', 'apitabs_show_content');
}

add_action('init', 'apitabs_shortcodes_init');
