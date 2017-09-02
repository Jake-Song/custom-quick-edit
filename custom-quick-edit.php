<?php
/*
Plugin Name: Custom Quick Edit
Plugin URI: https://wpjake.com
Description: Extends the quick-edit interface to display additional post meta
Version: 1.0.0
Author: Jake Song
Author URI: http://wpjake.com
Text Domain: custom-quick-edit
*/

class custom_extend_quick_edit{

    private static $instance = null;

    public function __construct(){

        add_action('manage_cosmetic_posts_columns', array($this, 'add_custom_admin_column'), 10, 1); //add custom column
        add_action('manage_posts_custom_column', array($this, 'manage_custom_admin_columns'), 10, 2); //populate column
        add_action('quick_edit_custom_box', array($this, 'display_quick_edit_custom'), 10, 2); //output form elements for quickedit interface
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts_and_styles')); //enqueue admin script (for prepopulting fields with JS)
        add_action('add_meta_boxes', array($this, 'add_cosmetic_meta_field'), 10, 2); //add metabox to posts to add our meta info
        add_action('save_post', array($this, 'cosmetic_meta_content_save'), 10, 1); //call on save, to update metainfo attached to our metabox
        add_filter('manage_edit-cosmetic_sortable_columns', array($this, 'custom_sortable_columns'));
        add_action('admin_head', array($this, 'custom_postlist_css'));
    }

    //adds a new metabox on our single post edit screen
    public function add_cosmetic_meta_field($post_type, $post) {
      add_meta_box(
        'cosmetic_meta_field',
        __( '부가 정보', 'custom-quick-edit' ),
        array($this, 'cosmetic_meta_content'),
        'cosmetic',
        'normal',
        'low'
      );
    }

    //metabox output function, displays our fields, prepopulating as needed
    public function cosmetic_meta_content( $post ){
      wp_nonce_field( 'cosmetic_meta', 'cosmetic_meta_field' );
      $content = '';

      $product_ranking_order = get_post_meta( get_the_ID(), 'product_ranking_order', true );
      $product_descendant_order = get_post_meta( get_the_ID(), 'product_descendant_order', true );
      $product_brand_order = get_post_meta( get_the_ID(), 'product_brand_order', true );
      $product_featured = get_post_meta( get_the_ID(), 'product_featured', true );
      $product_featured_attr = !empty($product_featured) ? ' checked' : '';
      $product_featured_order = get_post_meta( get_the_ID(), 'product_featured_order', true );
      $product_price = get_post_meta( get_the_ID(), 'product_price', true );

      $old_product_ranking_order = $product_ranking_order;
      $old_product_featured_order = $product_featured_order;
      $old_product_descendant_order = $product_descendant_order;
      $old_product_brand_order = $product_brand_order;

      $content .= "<input type='hidden' id='old_product_ranking_order'
                  name='old_product_ranking_order' value='{$old_product_ranking_order}' />";
      $content .= "<input type='hidden' id='old_product_featured_order'
                  name='old_product_featured_order' value='{$old_product_featured_order}' />";
      $content .= "<input type='hidden' id='old_product_descendant_order'
                  name='old_product_descendant_order' value='{$old_product_descendant_order}' />";
      $content .= "<input type='hidden' id='old_product_brand_order'
                  name='old_product_brand_order' value='{$old_product_brand_order}' />";

      $content .= "<label for='product_price'>가격</label>";
      $content .= "<input type='text' id='product_price'
                  name='product_price' placeholder='가격을 입력하세요.'
                  value='{$product_price}' /><br>";

      $content .= "<label for='product_ranking_order'>카테고리별 순위</label>";

      $content .= "<input type='text' id='product_ranking_order'
                  name='product_ranking_order' placeholder='순위를 입력하세요.'
                  value='{$product_ranking_order}' /><br>";

      $content .= "<label for='product_descendant_order'>하위 카테고리별 순위</label>";

      $content .= "<input type='text' id='product_descendant_order'
                  name='product_descendant_order' placeholder='순위를 입력하세요.'
                  value='{$product_descendant_order}' /><br>";

      $content .= "<label for='product_brand_order'>브랜드별 순위</label>";

      $content .= "<input type='text' id='product_brand_order'
                              name='product_brand_order' placeholder='순위를 입력하세요.'
                              value='{$product_brand_order}' /><br>";

      $content .= '<label for="product_featured">종합랭킹등록하기</label>';

      $content .= "<input type='checkbox' id='product_featured' name='product_featured' value='featured'
      {$product_featured_attr} />";

      $content .= '<label for="product_featured_order"></label>';

      $content .= "<input type='text' id='product_featured_order' name='product_featured_order' placeholder='순위를 입력하세요.' value='{$product_featured_order}' />";

      echo $content;
    }

    //enqueue admin js to pre-populate the quick-edit fields
    public function enqueue_admin_scripts_and_styles(){
       wp_enqueue_script('quick-edit-script', plugin_dir_url(__FILE__) .
       '/post-quick-edit-script.js', array('jquery','inline-edit-post' ));
    }
    //Display our custom content on the quick-edit interface, no values can be pre-populated (all done in JS)
    public function display_quick_edit_custom($column){
       $html = '';
       wp_nonce_field('cosmetic_meta', 'cosmetic_meta_field');

       //output post featured checkbox
       if($column == 'product_featured'){
           $html .= '<fieldset class="inline-edit-col-left clear">';
               $html .= '<div class="inline-edit-group wp-clearfix">';
                   $html .= '<label class="alignleft" for="product_featured">';
                      $html .= '<span class="checkbox-title">Product Featured</span></label>';
                      $html .= '<input type="checkbox" name="product_featured" id="product_featured_quick" value="featured"/>';
               $html .= '</div>';
           $html .= '</fieldset>';
       }
   //output post rating select field
   else if($column == 'product_ranking_order'){
       $html .= '<fieldset class="inline-edit-col-left ">';
           $html .= '<div class="inline-edit-group wp-clearfix">';
              $html .= "<input type='hidden' id='old_product_ranking_order_quick'
                          name='old_product_ranking_order' value='' />";
              $html .= '<label class="alignleft" for="product_ranking_order">카테고리별 순위</label>';
               $html .= "<input type='text' id='product_ranking_order_quick'
                           name='product_ranking_order' placeholder='순위를 입력하세요.'
                           value='' />";
           $html .= '</div>';
       $html .= '</fieldset>';
   }
   //output post subtitle text field
   else if($column == 'product_featured_order'){
       $html .= '<fieldset class="inline-edit-col-left ">';
           $html .= '<div class="inline-edit-group wp-clearfix">';
               $html .= "<input type='hidden' id='old_product_featured_order_quick'
                           name='old_product_featured_order' value='' />";
               $html .= '<label class="alignleft" for="product_featured_order">Top 30 순위</label>';
               $html .= '<input type="text" name="product_featured_order" id="product_featured_order_quick" value="" />';
           $html .= '</div>';
       $html .= '</fieldset>';
   }
   else if($column == 'product_descendant_order'){
       $html .= '<fieldset class="inline-edit-col-left ">';
           $html .= '<div class="inline-edit-group wp-clearfix">';
               $html .= "<input type='hidden' id='old_product_descendant_order_quick'
                           name='old_product_descendant_order' value='' />";
               $html .= '<label class="alignleft" for="product_descendant_order">하위 카테고리별 순위</label>';
               $html .= '<input type="text" name="product_descendant_order" id="product_descendant_order_quick" value="" />';
           $html .= '</div>';
       $html .= '</fieldset>';
   }
   else if($column == 'product_brand_order'){
       $html .= '<fieldset class="inline-edit-col-left ">';
           $html .= '<div class="inline-edit-group wp-clearfix">';
               $html .= "<input type='hidden' id='old_product_brand_order_quick'
                           name='old_product_brand_order' value='' />";
               $html .= '<label class="alignleft" for="product_brand_order">브랜드별 순위</label>';
               $html .= '<input type="text" name="product_brand_order" id="product_brand_order_quick" value="" />';
           $html .= '</div>';
       $html .= '</fieldset>';
   }
   echo $html;
    }
    //add a custom column to hold our data
    public function add_custom_admin_column($columns){
      $new_columns = array();

      $new_columns['product_featured'] = 'Top 30';
      $new_columns['product_ranking_order'] = '카테고리별';
      $new_columns['product_featured_order'] = 'Top 30 순위';
      $new_columns['product_descendant_order'] = '하위카테고리';
      $new_columns['product_brand_order'] = '브랜드별';

      return array_merge($columns, $new_columns);
    }
    //customise the data for our custom column, it's here we pull in meatdata info
    public function manage_custom_admin_columns($column_name, $post_id){
      $html = '';

    if($column_name == 'product_featured'){
        $product_featured = get_post_meta($post_id, 'product_featured', true);

        $html .= '<div id="product_featured_' . $post_id . '">';
        if(empty($product_featured)){
            $html .= 'no featured';
        }else if ($product_featured == 'featured'){
            $html .= 'featured';
        }
        $html .= '</div>';
    }
    else if($column_name == 'product_ranking_order'){
        $product_ranking_order = get_post_meta($post_id, 'product_ranking_order', true);

        $html .= '<div id="product_ranking_order_' . $post_id . '">';
            $html .= $product_ranking_order;
        $html .= '</div>';
    }
    else if($column_name == 'product_featured_order'){
        $product_featured_order = get_post_meta($post_id, 'product_featured_order', true);

        $html .= '<div id="product_featured_order_' . $post_id . '">';
            $html .= $product_featured_order;
        $html .= '</div>';
    }
    else if($column_name == 'product_descendant_order'){
        $product_descendant_order = get_post_meta($post_id, 'product_descendant_order', true);

        $html .= '<div id="product_descendant_order_' . $post_id . '">';
            $html .= $product_descendant_order;
        $html .= '</div>';
    }
    else if($column_name == 'product_brand_order'){
        $product_brand_order = get_post_meta($post_id, 'product_brand_order', true);

        $html .= '<div id="product_brand_order_' . $post_id . '">';
            $html .= $product_brand_order;
        $html .= '</div>';
    }
    echo $html;
    }
    //saving meta info (used for both traditional and quick-edit saves)
    public function cosmetic_meta_content_save($post_id){
      $post_type = get_post_type( $post_id );

      if( $post_type === 'cosmetic' ) :

        if( !isset( $_POST['post_author'] ) ) return;

        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )
        return;

        if ( !wp_verify_nonce( $_POST['cosmetic_meta_field'], 'cosmetic_meta') )
        return;

        if ( 'page' == $_POST['post_type'] ) {
          if ( !current_user_can( 'edit_page', $post_id ) )
          return;
        } else {
          if ( !current_user_can( 'edit_post', $post_id ) )
          return;
        }

        $old_product_ranking_order = isset($_POST['old_product_ranking_order']) ?
        sanitize_text_field($_POST['old_product_ranking_order']) : 0;

        $old_product_featured_order = isset($_POST['old_product_featured_order']) ?
        sanitize_text_field($_POST['old_product_featured_order']) : 0;

        $old_product_descendant_order = isset($_POST['old_product_descendant_order']) ?
        sanitize_text_field($_POST['old_product_descendant_order']) : 0;

        $old_product_brand_order = isset($_POST['old_product_brand_order']) ?
        sanitize_text_field($_POST['old_product_brand_order']) : 0;

        $product_ranking_order = isset($_POST['product_ranking_order']) ?
        sanitize_text_field($_POST['product_ranking_order']) : 0;

        $product_descendant_order = isset($_POST['product_descendant_order']) ?
        sanitize_text_field($_POST['product_descendant_order']) : 0;

        $product_brand_order = isset($_POST['product_brand_order']) ?
        sanitize_text_field($_POST['product_brand_order']) : 0;

        $product_featured = isset($_POST['product_featured']) ?
        sanitize_text_field($_POST['product_featured']) : '';

        $product_featured_order = isset($_POST['product_featured_order']) ?
        sanitize_text_field($_POST['product_featured_order']) : 0;

        $product_price = isset($_POST['product_price']) ?
        sanitize_text_field($_POST['product_price']) : '';

        $product_ranking_changed = $old_product_ranking_order - $product_ranking_order;
        $featured_ranking_changed = $old_product_featured_order - $product_featured_order;
        $descendant_ranking_changed = $old_product_descendant_order - $product_descendant_order;
        $brand_ranking_changed = $old_product_brand_order - $product_brand_order;

        update_post_meta( $post_id, 'product_ranking_order', $product_ranking_order );
        update_post_meta( $post_id, 'product_descendant_order', $product_descendant_order );
        update_post_meta( $post_id, 'product_brand_order', $product_brand_order );
        update_post_meta( $post_id, 'product_featured', $product_featured );
        update_post_meta( $post_id, 'product_featured_order', $product_featured_order );
        update_post_meta( $post_id, 'product_price', $product_price );

        update_post_meta( $post_id, 'product_ranking_changed', $product_ranking_changed );
        update_post_meta( $post_id, 'featured_ranking_changed', $featured_ranking_changed );
        update_post_meta( $post_id, 'descendant_ranking_changed', $descendant_ranking_changed );
        update_post_meta( $post_id, 'brand_ranking_changed', $brand_ranking_changed );

      endif;
    }

    // add a sortable filter
    public function custom_sortable_columns($sortable_columns){
      $sortable_columns[ 'product_ranking_order' ] = 'product_ranking_order';
      $sortable_columns[ 'product_featured_order' ] = 'product_featured_order';
      $sortable_columns[ 'product_featured' ] = 'product_featured';
      return $sortable_columns;
    }

    // admin post list css
    public function custom_postlist_css() {
      
      $post_type = get_post_type();
      if ($post_type == 'cosmetic') :
      echo '
        <style>
          th#title{
            width: 25%;
          }
          th#taxonomy-cosmetic_category, th#taxonomy-cosmetic_brand{
            width: 10%;
          }
          th#product_featured{
            width: 7%;
          }
          th#product_ranking_order,
          th#product_featured_order,
          th#product_descendant_order
          th#product_brand_order{
            width: 6%;
          }
          td.product_ranking_order,
          td.product_featured_order,
          td.product_descendant_order,
          td.product_brand_order{
            text-align: center;
          }
        </style>
      ';
      endif;
    }

    // gets singleton instance
    public static function getInstance(){
        if(is_null(self::$instance)){
            self::$instance = new self();
        }
        return self::$instance;
    }


}
$custom_extend_quick_edit = custom_extend_quick_edit::getInstance();
