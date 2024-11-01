<?php
/*

Plugin Name: Shopizi
Plugin URI: https://surecode.me

Description: Plugin for infinity scroll your WooCommerce Products without end-user action.

Version: 1.0.0

Author: Kirill Shur(SureCode Marketing)

License: GPLv2

*/

add_shortcode('shopizi','shopizi_method');

function shopizi_method($atts){

  $shopizi_parameters = [
    "selector" => null,
    "method" => null,
    "ppp" => null,
    "action" => null,
    "post_type" => null,
    "page_number" => null,
    "category" => null,
    "taxonomy" => null,
    "order" => null
   ];


$attrset = shortcode_atts($shopizi_parameters,$atts);

if (is_plugin_active( 'woocommerce/woocommerce.php')) {
 ob_start();
?>
<style type="text/css" rel="stylesheet">

.shopizi_style ul {
    display: flex;
    justify-content: center;
    list-style: none;
    flex-wrap: wrap;
    padding: 0;
    position: relative;
}
div.shopizi_style {
    padding: 1em 1em;
    font-size: min(17px,5vw);
    position: relative;
    width: 80%;
    margin: auto;
}
.shopizi_style ul li {
    display: flex;
    height: 27rem;
    overflow: hidden;
    flex-direction: column;
    align-items: center;
}
.shopizi_style ul li img {
   display: inline-block;
   height:300px;
   width:300px;
   max-width:100%;
   object-fit: contain;
}
.shopizi_style ul li {
   display: flex;
   height: 27rem;
   overflow: hidden;
   flex-direction: column;
   align-items: center;
   margin: : 0 2em;
}
.shopizi_link {
    color: #fff!important;
    background: #50a90c;
    display: inline-block;
    padding: 0.2em 0.8em;
}
span#loader  > img {
    position: absolute;
    display: inline-block;
    bottom: -42px;
    left: 50%;
    transform: translateX(-50%);
    top: auto;
    right: auto;
    width: 2rem;
    height: auto;
    object-fit: contain;
    margin: 0;
}

</style>
<script type="text/javascript">

var pagenum = 0;
jQuery(document).ready(function($){
  $shopizi = $.noConflict();
  $shopizi.ajax({
      type: '<?php echo esc_html($attrset['method']);?>',
      dataType: 'html',
      url: '<?php echo admin_url('admin-ajax.php');?>',
      data: {
        action:'<?php echo esc_html($attrset['action']);?>',
        ppp : '<?php echo esc_html($attrset['ppp']);?>',
        post_type : '<?php echo esc_html($attrset['post_type']);?>',
        page_number : ++pagenum,
        category : '<?php echo esc_html($attrset['category']);?>',
        taxonomy : '<?php echo esc_html($attrset['taxonomy']);?>',
        order : '<?php echo esc_html($attrset['order']);?>',
        init: '1'
      },
      success: function(data){
         $shopizi('#'+'<?php echo esc_html($attrset['selector']);?>').empty();
         $shopizi('#'+'<?php echo esc_html($attrset['selector']);?>').append(data);
         $shopizi('#'+'<?php echo esc_html($attrset['selector']);?>').addClass('shopizi_style');
      },
      error : function(jqXHR, textStatus, errorThrown) {
          console.log(jqXHR + " :: " + textStatus + " :: " + errorThrown);
      }
  });

  var loadmore = true;

  $shopizi(window).on('scroll',function(e){

   try{
    var $top_win = $shopizi(window).scrollTop();
    var $prod_list_offset = $shopizi(document).find('#prod_list li').last().offset().top - 500;
    if($top_win > $prod_list_offset){
      if(loadmore && $("#prod_list").data('maxpages') > pagenum ){
        loadmore = false;
      $shopizi.ajax({
          type: '<?php echo esc_html($attrset['method']);?>',
          dataType: 'html',
          url: '<?php echo admin_url('admin-ajax.php');?>',
          beforeSend: function(){
            $shopizi('#'+'<?php echo esc_html($attrset['selector']);?>')
            .find("#prod_list")
            .append('<span id="loader"><img src="<?php  echo plugins_url('/images/loading.gif',__FILE__) ?>" alt="loader"/><span>');
          },
          data: {
            action:'<?php echo esc_html($attrset['action']);?>',
            ppp : '<?php echo esc_html($attrset['ppp']);?>',
            post_type : '<?php echo esc_html($attrset['post_type']);?>',
            page_number : ++pagenum,
            category : '<?php echo esc_html($attrset['category']);?>',
            taxonomy : '<?php echo esc_html($attrset['taxonomy']);?>',
            order : '<?php echo esc_html($attrset['order']);?>',
            scroll: '1'
          },
          success: function(data){
             if(data){
               $shopizi('#'+'<?php echo esc_html($attrset['selector']);?>').find("#prod_list").find("#loader").remove();
               $shopizi('#'+'<?php echo esc_html($attrset['selector']);?>').find("#prod_list").append(data);
               $shopizi('#'+'<?php echo esc_html($attrset['selector']);?>').addClass('shopizi_style');
               loadmore = true;
            }
          },
          error : function(jqXHR, textStatus, errorThrown) {
              console.log(jqXHR + " :: " + textStatus + " :: " + errorThrown);
          }
      });
    }
    }

   }
   catch(e){
   }
  e.preventDefault();
  });
});

</script>
<?php
$shopizi_set = ob_get_clean();
return $shopizi_set;
}
}

add_action('init','shopizi_init');

function shopizi_init(){
  global $wp_query;
  function shopizi_action_method(){
    if(isset($_POST["init"])){
  		$args = array(
   	 	'posts_per_page' => sanitize_text_field($_POST["ppp"]),
   	 	'post_type' => sanitize_text_field($_POST["post_type"]),
   	 	'product_cat' => sanitize_text_field($_POST["category"]),
   	 	'order' => sanitize_text_field($_POST["order"])
   	 );
  	 $query = new WP_Query( $args );
     $curr_page = get_query_var('paged') ? get_query_var('paged') : 1;
  	 if ( $query->have_posts() ) {
  	 echo '<ul id="prod_list" data-maxpages="'.$query->max_num_pages.'">';
  		 while ( $query->have_posts() ) : $query->the_post();
  			 global $product;
  			 $product_output = '<li>';
  			 $image_links[0] = get_post_thumbnail_id( $product->id );
         $gallery = wp_get_attachment_image_src($image_links[0], 'full' );
  			 $product_output .=  "<img src='".$gallery[0]."'/>";
         $product_output .= '<span>'.$product->get_title().'</span>';
  			 $product_output .=  '<span>'.$product->get_price_html().'</span>';
         $product_output .= '<a class="shopizi_link" target="_blank" href="/checkout/?add-to-cart='.$product->get_id().'">BUY</a>';
  			 $product_output .=  '</li>';
        $allow_html = array(
          "ul" => array(
           "data-maxpages" => array(),
           "id" => array(),
           "class" => array()
         ),
         "li" => array(
          "class" => array()
         ),
         "span" => array(
          "class" => array()
         ),
         "a" => array(
          "class" =>array(),
          "target" => array(),
          "href" => array()
        ),
        "img" => array(
           "alt" => array(),
           "src" => array(),
           "class" => array()
         )
        );
  			echo wp_kses($product_output,$allow_html);
  		  endwhile;
  	    echo '</ul>';
  	  wp_reset_postdata();
  	 }
   }elseif (isset($_POST["scroll"])) {
     $paged = sanitize_text_field(isset($_POST['page_number'])) ? sanitize_text_field($_POST['page_number']) : 0;
     $args = array(
     'posts_per_page' => sanitize_text_field($_POST["ppp"]),
     'post_type' => sanitize_text_field($_POST["post_type"]),
     'product_cat' => sanitize_text_field($_POST["category"]),
     'order' => sanitize_text_field($_POST["order"]),
     'paged' =>  $paged
    );
    $query = new WP_Query( $args );
    $curr_page = get_query_var('paged') ? get_query_var('paged') : 1;
    if ( $query->have_posts() ) {
      while ( $query->have_posts() ) : $query->the_post();
        global $product;
        $product_output = '<li>';
        $image_links[0] = get_post_thumbnail_id( $product->id );
        $gallery = wp_get_attachment_image_src($image_links[0], 'full' );
        $product_output .=  "<img src='".$gallery[0]."'/>";
        $product_output .= '<span>'.$product->get_title().'</span>';
        $product_output .=  '<span>'.$product->get_price_html().'</span>';
        $product_output .= '<a class="shopizi_link" target="_blank" href="/checkout/?add-to-cart='.$product->get_id().'">BUY</a>';
        $product_output .=  '</li>';
        $allow_html = array(
          "ul" => array(
           "data-maxpages" => array(),
           "id" => array(),
           "class" => array()
         ),
         "li" => array(
          "class" => array()
         ),
         "span" => array(
          "class" => array()
         ),
         "a" => array(
          "class" =>array(),
          "target" => array(),
          "href" => array()
        ),
        "img" => array(
           "alt" => array(),
           "src" => array(),
           "class" => array()
         )
        );
      echo wp_kses($product_output,$allow_html);
      endwhile;
     wp_reset_postdata();
    }
   }
   die();
  }
  add_action("wp_ajax_shopizi","shopizi_action_method");

  add_action("wp_ajax_nopriv_shopizi","shopizi_action_method");
}
