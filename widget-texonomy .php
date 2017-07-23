 <?php 
/*
 * Plugin Name: Widget Taxonomy 
 * Description: Add taxonomy widget for your custom post type.
 * Version: 1.0.0
 * Author: Juhi Patel
 * Author URI: https://profiles.wordpress.org/juiiee8487
 * Text Domain: taxonomy_widget_terms
 * License: GNU General Public License v3.0
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
*/
if (!defined('ABSPATH')) { exit; }

// Creating the widget 
class ttw_widget extends WP_Widget {

    /**
     * Register widget with WordPress.
     */
    public function __construct() {
        parent::__construct(
            'ttw_widget', 
            'Texonomy Widget', 
            array( 'description' => __( 'A Custom Texonomy Widget', 'ttw_widget' ), ) 
        );
        $widget_ops = array( 'classname' => 'texonomy_widget_terms' , 'description' => __( "A Custom Texonomy Widget " ) );
        parent::__construct( 'taxonomy_terms' , __( 'Taxonomies' ) , $widget_ops );
    }

    /**
     * Front-end display of widget.
     *
     */
    public function widget( $args, $instance ) {
        extract( $args );
 
        $current_taxonomy = $this->_get_current_taxonomy( $instance );
        $tax = get_taxonomy( $current_taxonomy );
        if ( !empty( $instance['title'] ) ) {
          $title = $instance['title'];
        } else {
          $title = $tax->labels->name;
        }
     
        global $taxonomy;
        $taxonomy = $instance['taxonomy'];
        $format = $instance['format'];
        $count = $instance['count'] ? '1' : '0';
        $hierarchical = $instance['hierarchical'] ? '1' : '0';
     
        $w_id = $args['widget_id'];
        $w_id = 'ttw' . str_replace( 'taxonomy_terms' , '' , $w_id );
     
        echo $before_widget;
        if ( $title )
          echo $before_title . $title . $after_title;
     
        $tax_args = array( 'orderby' => 'name' , 'show_count' => $count , 'hierarchical' => $hierarchical , 'taxonomy' => $taxonomy );
     
        if ( $format == 'dropdown' ) {
          $tax_args['show_option_none'] = __( 'Select ' . $tax->labels->singular_name );
          $tax_args['name'] = __( $w_id );
          $tax_args['echo'] = false;
          $my_dropdown_categories = wp_dropdown_categories( apply_filters( 'widget_categories_dropdown_args' , $tax_args ) );
     
          $my_get_term_link = create_function( '$matches' , 'global $taxonomy; return "value=\"" . get_term_link( (int) $matches[1] , $taxonomy ) . "\"";' );
          echo preg_replace_callback( '#value="(\\d+)"#' , $my_get_term_link , $my_dropdown_categories );
     
    ?>
    <script type='text/javascript'>
    /* <![CDATA[ */
      var dropdown<?php echo $w_id; ?> = document.getElementById("<?php echo $w_id; ?>");
      function on<?php echo $w_id; ?>change() {
        if ( dropdown<?php echo $w_id; ?>.options[dropdown<?php echo $w_id; ?>.selectedIndex].value != '-1' ) {
          location.href = dropdown<?php echo $w_id; ?>.options[dropdown<?php echo $w_id; ?>.selectedIndex].value;
        }
      }
      dropdown<?php echo $w_id; ?>.onchange = on<?php echo $w_id; ?>change;
    /* ]]> */
    </script>
    <?php
     
        } elseif ( $format == 'list' ) {
     
    ?>
        <ul>
    <?php  $tax_args['title_li'] = '';
        wp_list_categories( apply_filters( 'widget_categories_args' , $tax_args ) ); ?>
        </ul>
    <?php } else {  ?>
        <div>
    <?php wp_tag_cloud( apply_filters( 'widget_tag_cloud_args' , array( 'taxonomy' => $taxonomy ) ) ); ?>
    </div>
    <?php  }  echo $after_widget;  }

    /**
     * widget form values as they are saved.
     *
     */
    public function update( $new_instance, $old_instance ) {
        $instance = $old_instance;
        $instance['title'] = strip_tags( $new_instance['title'] );
        $instance['taxonomy'] = stripslashes( $new_instance['taxonomy'] );
        $instance['format'] = stripslashes( $new_instance['format'] );
        $instance['count'] = !empty( $new_instance['count'] ) ? 1 : 0;
        $instance['hierarchical'] = !empty( $new_instance['hierarchical'] ) ? 1 : 0;
     
        return $instance;
    }

    /**
     * Back-end widget form.
     */
    public function form( $instance ) {

       /*if ( isset( $instance[ 'title' ] ) ) {
            $title = $instance[ 'title' ];
           
        }else{
            $title = '';
        }*/ 

        $instance = wp_parse_args( (array) $instance , array( 'title' => '' ) );
        $current_taxonomy = $this->_get_current_taxonomy( $instance );
        $current_format = esc_attr( $instance['format'] );
        $title = esc_attr( $instance['title'] );
        $count = isset( $instance['count'] ) ? (bool) $instance['count'] : false;
        $hierarchical = isset( $instance['hierarchical'] ) ? (bool) $instance['hierarchical'] : false;?>

    <p><label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:' ); ?></label>
    <input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo $title; ?>" /></p>
 
    <p><label for="<?php echo $this->get_field_id( 'taxonomy' ); ?>"><?php _e( 'Taxonomy:' ); ?></label>
    <select class="widefat" id="<?php echo $this->get_field_id( 'taxonomy' ); ?>" name="<?php echo $this->get_field_name( 'taxonomy' ); ?>">
          <?php $args = array(
                'public' => true ,
                '_builtin' => false
              );
              $output = 'names';
              $operator = 'and';
           
              $taxonomies = get_taxonomies( $args , $output , $operator );
              $taxonomies = array_merge( $taxonomies, array( 'category' , 'post_tag' ) );
              foreach ( $taxonomies as $taxonomy ) {
                $tax = get_taxonomy( $taxonomy );
                if ( empty( $tax->labels->name ) )
                  continue;
          ?>
        <option value="<?php echo esc_attr( $taxonomy ); ?>" <?php selected( $taxonomy , $current_taxonomy ); ?>><?php echo $tax->labels->name; ?></option>
       <?php   } ?>
    </select></p>
 
    <p><label for="<?php echo $this->get_field_id( 'format' ); ?>"><?php _e( 'Format:' ) ?></label>
    <select class="widefat" id="<?php echo $this->get_field_id( 'format' ); ?>" name="<?php echo $this->get_field_name( 'format' ); ?>">
          <?php  $formats = array( 'list' , 'dropdown' , 'cloud' );
                  foreach( $formats as $format ) { ?>
                  <option value="<?php echo esc_attr( $format ); ?>" <?php selected( $format , $current_format ); ?>><?php echo ucfirst( $format ); ?></option>
              <?php } ?>
    </select></p>

    <input type="checkbox" class="checkbox" id="<?php echo $this->get_field_id( 'count' ); ?>" name="<?php echo $this->get_field_name( 'count' ); ?>"<?php checked( $count ); ?> />
    <label for="<?php echo $this->get_field_id( 'count' ); ?>"><?php _e( 'Show post counts' ); ?></label><br />
 
    <input type="checkbox" class="checkbox" id="<?php echo $this->get_field_id( 'hierarchical' ); ?>" name="<?php echo $this->get_field_name( 'hierarchical' ); ?>"<?php checked( $hierarchical ); ?> />
    <label for="<?php echo $this->get_field_id( 'hierarchical' ); ?>"><?php _e( 'Show hierarchy' ); ?></label></p>
<?php }
 
  function _get_current_taxonomy( $instance ) {
    if ( !empty( $instance['taxonomy'] ) && taxonomy_exists( $instance['taxonomy'] ) )
      return $instance['taxonomy'];
    else
      return 'category';
  }
}

add_action( 'widgets_init', create_function( '', 'register_widget( "ttw_widget" );' ) );