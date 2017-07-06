<?php

if ( ! defined( 'ABSPATH' ) )
  exit; // Exit if accessed directly

function add_thumbnail_link($thumbnail, $post_id){
  $link = get_permalink($post_id);
  $thumbnail_html = "<a class='media-left' href='{$link}'>{$thumbnail}</a>";

  return $thumbnail_html;
}
function the_thumbnail(){
  $post_id = get_the_id();

  if( is_singular() ){
    $thumbnail = get_the_post_thumbnail(
      $post_id,
      apply_filters( 'content_full_image_size', 'medium' ),
      apply_filters( 'content_full_image_args', array('class' => 'al') )
    );
  }
  else {
    $thumbnail = get_the_post_thumbnail(
      $post_id,
      apply_filters( 'content_thumbnail_size', 'thumbnail' ),
      apply_filters( 'content_thumbnail_args', array('class' => 'al') )
    );
  }

  echo apply_filters( 'content_image_html', $thumbnail, $post_id );
}
function get_tpl_content( $affix, $return = false ){
  if($return)
    ob_start();

  if( ! is_front_page() && is_archive() && !is_search() ){
    the_advanced_archive_title();
    the_archive_description( '<div class="taxonomy-description">', '</div>' );
  }

  echo "<div class='row'>";

  while ( have_posts() ){
    the_post();
    
    // need for search
    if( ! $affix )
      $affix = get_post_type();

    if( $affix != 'product' )
      get_template_part( 'template-parts/content', $affix );
  }

  echo "</div>";

  if($return)
    return ob_get_clean();
}
function get_tpl_search_content( $return = false ){
  ob_start();
  while ( have_posts() ){
    the_post();

    if( get_post_type() == 'product' )
      wc_get_template_part( 'content', 'product' );
  }
  $products = ob_get_clean();
  $content = get_tpl_content( false, true );

  if( $return ){
    return $products . $content;
  }
  else {
    if($products)
      echo "<ul class='products row'>" . $products . "</ul>";
    echo $content;
  }
}
/**
 * Показывать sidebar или нет
 * @return boolean
 */
function is_show_sidebar(){
  $post_type = get_post_type();
  $enable_types = apply_filters( 'sidebar_archive_enable_on_type', array('post', 'page') );

  if( function_exists('is_woocommerce') ){
    if( is_woocommerce() || is_shop() && is_active_sidebar('woocommerce')  )
       $show_sidebar = 'woocommerce';
    if( is_cart() || is_checkout() || is_account_page())
      $show_sidebar = false;
    elseif( is_active_sidebar('archive') && in_array($post_type, $enable_types) )
      $show_sidebar = 'archive';
  }
  else {
    if( is_active_sidebar('archive') && in_array($post_type, $enable_types) )
      $show_sidebar = 'archive';
  }

  return apply_filters( 'enable_sidebar', $show_sidebar );
}

/**
 * Принятые настройки постраничной навигации
 */
function the_template_pagination($echo=true){
  $args = array(
    'show_all'     => false,
    'end_size'     => 1,    
    'mid_size'     => 1,     
    'prev_next'    => true,  
    'prev_text'    => '« Пред.',
    'next_text'    => 'След. »',
    'add_args'     => false,
    );

  if($echo){
    the_posts_pagination($args);
    return true;
  }
  else {
    return get_the_posts_pagination($args);
  }    
}

/**
 * Наличие подкатегорий (подтерминов)
 */
function has_children_terms($hide_empty=true){
  $o = get_queried_object();
  if(!empty($o->has_archive) && $o->has_archive==true){
    $tax = $o->taxonomies[0];
    $parent = 0;
  }

  if( !empty($o->term_id) ){
    $tax = $o->taxonomy;
    $parent = $o->term_id;
  }

  $children = get_terms( array(
    'taxanomy'  => $tax,
    'parent'    => $parent,
    'hide_empty' => $hide_empty
    ) );

  if($children) {
    return true;
  }
  return false;
}

/**
 * Получить ID самой родительской страницы (после "главной")
 */
function get_parent_page_id($post) {
  if ($post->post_parent)  {
    $ancestors=get_post_ancestors($post->ID);
    $root=count($ancestors)-1;
    $parent = $ancestors[$root];
  } else {
    $parent = $post->ID;
  }
  return $parent;
}

/***************************************************
 * Template Filters
 */

/**
 * Логотип
 */
add_filter( 'set_custom_brand', 'add_custom_brand', 10, 3 );
function add_custom_brand($brand, $brand_class, $brand_title){
  $home_url = get_home_url();

  $brand = "<a class='{$brand_class}' title='{$brand_title}' href='{$home_url}'>{$brand}</a>";
  return $brand;
}

/**
 * Русскоязычная дата
 */
add_filter('the_time', 'the_russian_date');
add_filter('get_the_time', 'the_russian_date');
add_filter('the_date', 'the_russian_date');
add_filter('get_the_date', 'the_russian_date');
add_filter('the_modified_time', 'the_russian_date');
add_filter('get_the_modified_date', 'the_russian_date');
add_filter('get_post_time', 'the_russian_date');
add_filter('get_comment_date', 'the_russian_date');
function the_russian_date($tdate = '') {
  if ( substr_count($tdate , '---') > 0 )
    return str_replace('---', '', $tdate);

  $treplace = array (
    "Январь" => "января",
    "Февраль" => "февраля",
    "Март" => "марта",
    "Апрель" => "апреля",
    "Май" => "мая",
    "Июнь" => "июня",
    "Июль" => "июля",
    "Август" => "августа",
    "Сентябрь" => "сентября",
    "Октябрь" => "октября",
    "Ноябрь" => "ноября",
    "Декабрь" => "декабря",

    "January" => "января",
    "February" => "февраля",
    "March" => "марта",
    "April" => "апреля",
    "May" => "мая",
    "June" => "июня",
    "July" => "июля",
    "August" => "августа",
    "September" => "сентября",
    "October" => "октября",
    "November" => "ноября",
    "December" => "декабря",  

    "Sunday" => "воскресенье",
    "Monday" => "понедельник",
    "Tuesday" => "вторник",
    "Wednesday" => "среда",
    "Thursday" => "четверг",
    "Friday" => "пятница",
    "Saturday" => "суббота",

    "Sun" => "воскресенье",
    "Mon" => "понедельник",
    "Tue" => "вторник",
    "Wed" => "среда",
    "Thu" => "четверг",
    "Fri" => "пятница",
    "Sat" => "суббота",

    "th" => "",
    "st" => "",
    "nd" => "",
    "rd" => ""
  );
  return strtr($tdate, $treplace);
}

/**
 * Добавить ссылку о разработчике в топбар
 */
add_action('admin_bar_menu', 'customize_toolbar_link', 999);
function customize_toolbar_link($wp_admin_bar) {
  $wp_admin_bar->add_node(array(
    'id' => 'seo',
    'title' => 'Seo18.ru',
    'href' => 'http://seo18.ru',
    'meta' => array(
      'title' => 'Перейти на сайт разработчика'
      )
    ));
}

/**
 * Сменить строку "Спасибо за творчество с Wordpress"
 */
add_filter('admin_footer_text', 'custom_admin_footer');
function custom_admin_footer() {
  $ver = get_bloginfo('version');
  $char = get_bloginfo('charset');
  $wp_ver_str = $ver.'-'.$char;

  echo '<span id="footer-thankyou">Разработано компанией <a href="http://seo18.ru" target="_blank">seo18.ru - создание и продвижение сайтов</a></span>.
  <small> Использована система <a href="wordpress.com">WordPress ('.$wp_ver_str.')</a>. </small>';
}

/***************************************************
 * Actions
 */

/**
 * yoast крошки ( Для активации установить/активировать плагин, дополнительно => breadcrumbs => enable )
 */
add_action( 'woocommerce_before_main_content', 'breadcrumbs_from_yoast', 25 );
function breadcrumbs_from_yoast(){
  if ( function_exists('yoast_breadcrumb') ) {
    yoast_breadcrumb('<p id="breadcrumbs">','</p>');
  }
}

/**
 * Отчистить мета теги
 */
add_action( 'init', 'template_head_cleanup' );
function template_head_cleanup() {
  remove_action( 'wp_head', 'feed_links_extra', 3 );                    // Category Feeds
  remove_action( 'wp_head', 'feed_links', 2 );                          // Post and Comment Feeds
  remove_action( 'wp_head', 'rsd_link' );                               // EditURI link
  remove_action( 'wp_head', 'wlwmanifest_link' );                       // Windows Live Writer
  remove_action( 'wp_head', 'index_rel_link' );                         // index link
  remove_action( 'wp_head', 'parent_post_rel_link', 10, 0 );            // previous link
  remove_action( 'wp_head', 'start_post_rel_link', 10, 0 );             // start link
  remove_action( 'wp_head', 'adjacent_posts_rel_link_wp_head', 10, 0 ); // Links for Adjacent Posts
  remove_action( 'wp_head', 'wp_generator' );                           // WP version
}