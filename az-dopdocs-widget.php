<?php
/*
 * Plugin Name: az-dopdocs-widget
 * Description: Виджет дополнительной документации для проекта termoshkaf. Для открытия ссылок в popup-окне используется плагин Popups - WordPress Popup
 * Version: 1.0
 * Author: jvj 
 */
 
 if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
?>
<?php
add_action( 'woocommerce_product_options_advanced', 'az_add_dopdocs_field' );
function az_add_dopdocs_field() {
	
	global $product, $post;
	?>
	
	<div class="options_group">
		
		<h2><strong>Дополнительная документация</strong></h2>
		
		<?php
			wp_editor(get_post_meta( $post->ID, '_az_dop_docs', true ), 'azdopdocs', array(
				'wpautop'       => 1,
				'media_buttons' => 1,
				'textarea_name' => 'azdopdocs',
				'textarea_rows' => 20,
				'tabindex'      => null,
				'editor_css'    => '<style>.quicktags-toolbar, .wp-editor-tools, .wp-editor-wrap, .wp-switch-editor {padding: 5px 10px;} </style>',
				'editor_class'  => 'form-field',
				'teeny'         => 0,
				'dfw'           => 0,
				'tinymce'       => 1,
				'quicktags'     => 1,
				'drag_drop_upload' => false
			) );		
		?>
		
		<h2><strong>Кнопка запроса чертежа</strong></h2>
		
		<?php
			woocommerce_wp_checkbox( array(
			   'id'            => '_az_drawing_checkbox',
			   'wrapper_class' => '',
			   'label'         => 'Показать',
			   'description'   => '',
			) );	
		?>
		
		
	</div>
	
	<?php
}
add_action( 'woocommerce_process_product_meta', 'az_dopdocs_field_save', 10 );
function az_dopdocs_field_save( $post_id ) {
	// Сохранение в базу
	
	/*$post_azdopdocs = $_POST['azdopdocs'];	
	
	if ( ! empty( $post_azdopdocs ) ) {
		update_post_meta( $post_id, '_az_dop_docs', $post_azdopdocs );
	}*/
	
	$post_azdopdocs = isset( $_POST['azdopdocs'] ) ? $_POST['azdopdocs'] : '';
	update_post_meta( $post_id, '_az_dop_docs', $post_azdopdocs );
	
	$post_azdrawingcheckbox = isset( $_POST['_az_drawing_checkbox'] ) ? 'yes' : 'no';
	update_post_meta( $post_id, '_az_drawing_checkbox', $post_azdrawingcheckbox );	
	
}
?>
<?php
/**
* Adds Product Docs widget
*/
class Productdocs_Widget extends WP_Widget {

	/**
	* Register widget with WordPress
	*/
	function __construct() {
		parent::__construct(
			'productdocs_widget', // Base ID
			esc_html__( 'Виджет документации', 'termoshkaf' ), // Name
			array( 'description' => esc_html__( 'Отображает дополнительную документацию к товару', 'termoshkaf' ), ) // Args
		);
	}

	/**
	* Widget Fields
	*/
	private $widget_fields = array(
	);

	/**
	* Front-end display of widget
	*/
	public function widget( $args, $instance ) {
		
            if ( is_product() ) {			
			
                global $post;

				$az_draw = get_post_meta($post->ID, '_az_drawing_checkbox', true);
				$az_docs = get_post_meta($post->ID, '_az_dop_docs', true);
				
				if ( $az_docs != '' || $az_draw == 'yes' ) {
				
					echo $args['before_widget'];
					
					if ( !empty($instance['title']) ) {
                        echo $args['before_title'] . apply_filters('widget_title', $instance['title']) . $args['after_title'];
                    }
					
					if ($az_docs != '') { echo $az_docs; }
					
					// проверка отображения кнопки чертежей         

					if ($az_draw == 'yes') : ?>
						
						<div class="docs-div">
						
						<h2>Чертежи</h2>
													
						<?php echo do_shortcode('[caldera_form_modal id="CF5b573e555ade3" type="button" width="600"]<span class="cad button">Запросить CAD-файл</span> [/caldera_form_modal]'); ?>
						
						</div>
						
					<?php   endif;
					
										
					
					echo $args['after_widget'];
				}
                
        }
		
	}

	/**
	* Back-end widget fields
	*/
	public function field_generator( $instance ) {
		$output = '';
		foreach ( $this->widget_fields as $widget_field ) {
			$widget_value = ! empty( $instance[$widget_field['id']] ) ? $instance[$widget_field['id']] : esc_html__( $widget_field['default'], 'amadon' );
			switch ( $widget_field['type'] ) {
				default:
					$output .= '<p>';
					$output .= '<label for="'.esc_attr( $this->get_field_id( $widget_field['id'] ) ).'">'.esc_attr( $widget_field['label'], 'amadon' ).':</label> ';
					$output .= '<input class="widefat" id="'.esc_attr( $this->get_field_id( $widget_field['id'] ) ).'" name="'.esc_attr( $this->get_field_name( $widget_field['id'] ) ).'" type="'.$widget_field['type'].'" value="'.esc_attr( $widget_value ).'">';
					$output .= '</p>';
			}
		}
		echo $output;
	}

	public function form( $instance ) {
		$title = ! empty( $instance['title'] ) ? $instance['title'] : esc_html__( '', 'amadon' );
		?>
		<p>
		<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php esc_attr_e( 'Title:', 'amadon' ); ?></label>
		<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>">
		</p>
		<?php
		$this->field_generator( $instance );
	}

	/**
	* Sanitize widget form values as they are saved
	*/
	public function update( $new_instance, $old_instance ) {
		$instance = array();
		$instance['title'] = ( ! empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '';
		foreach ( $this->widget_fields as $widget_field ) {
			switch ( $widget_field['type'] ) {
				case 'checkbox':
					$instance[$widget_field['id']] = $_POST[$this->get_field_id( $widget_field['id'] )];
					break;
				default:
					$instance[$widget_field['id']] = ( ! empty( $new_instance[$widget_field['id']] ) ) ? strip_tags( $new_instance[$widget_field['id']] ) : '';
			}
		}
		return $instance;
	}
} // class Productdocs_Widget

// register Product Docs widget
function register_productdocs_widget() {
	register_widget( 'Productdocs_Widget' );
}
add_action( 'widgets_init', 'register_productdocs_widget' );


//удаляем из БД созданные плагином мета-поля
register_uninstall_hook(__FILE__, 'az_dopdocs_uninstall');
function az_dopdocs_uninstall() {
	
	$allposts = get_posts('numberposts=-1&post_type=product&post_status=any');

	foreach( $allposts as $postinfo) {
		delete_post_meta( $postinfo->ID, '_az_dop_docs');
		delete_post_meta( $postinfo->ID, '_az_drawing_checkbox');		
	}
	
}
?>