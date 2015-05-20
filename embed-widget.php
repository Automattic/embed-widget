<?php
/**
 * Plugin Name: Embed Widget
 * Description: A self html validating embed widget
 * Version: 1.1.0
 * Author: Tom J Nowell, Automattic, CFTP
 * License: GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 */

class Embed_Code_Widget extends WP_Widget {

	public function __construct() {
		$widget_ops = array( 'description' => __('Embed newsletters and other html code.') );
		WP_Widget::__construct( 'embed_code', __('Embed Code'), $widget_ops );
	}

	public function widget( $args, $instance ) {
		$title = apply_filters( 'widget_title', $instance['title'] );

		echo $args['before_widget'];
		if ( ! empty( $title ) ) {
			echo $args['before_title'] . esc_html( $title ) . $args['after_title'];
		}
		if ( !empty( $instance['intro'] ) ) {
			?>
			<p><?php echo wp_kses_post( $instance['intro'] ); ?></p>
			<?php
		}
		$code = $instance['code'];
		echo $code;
		echo $args['after_widget'];
	}

	public function form( $instance ) {
		$title = __( 'New title', 'text_domain' );
		if ( isset( $instance[ 'title' ] ) ) {
			$title = $instance[ 'title' ];
		}
		?>
		<p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:' ); ?></label>
			<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" />
		</p>
		<?php
		$intro = '';
		if ( isset( $instance[ 'intro' ] ) ) {
			$intro = $instance[ 'intro' ];
		}
		?>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'intro' ) ; ?>"><?php _e( 'Intro text:' ); ?></label>
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'intro' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'intro' ) ); ?>" type="text" placeholder="Text shown before embed code" value="<?php echo esc_attr( $intro ); ?>" />
		</p>
		<?php
		$code = '';
		if ( isset( $instance[ 'code' ] ) ) {
			$code = $instance[ 'code' ];
			if ( strpos( $code, '<?' ) !== false ) {
				echo '<div class="error"><p>Embed Code error: You can\'t include PHP code here, it will not work, and will be shown on the frontend. Please remove the PHP code and contact a developer. PHP code is surrounded by &lt;?php and ?&gt;</p></div>';
			}
			$dom = new \DOMDocument();
			libxml_use_internal_errors( true );
			$dom->loadHtml( $code );
			$errors = libxml_get_errors();

			foreach ( $errors as $error ) {
				$return = '<strong>Issue found in the embed code:</strong><br>';
				switch ( $error->level ) {
					case LIBXML_ERR_WARNING:
						$return .= "Warning $error->code: ";
						break;
					case LIBXML_ERR_ERROR:
						$return .= "Error $error->code: ";
						break;
					case LIBXML_ERR_FATAL:
						$return .= "Fatal Error $error->code: ";
						break;
				}

				$return .= trim( $error->message ) .
					"<br>  Line: $error->line" .
					",  Column: $error->column";

				echo '<div class="error"><p>'.esc_html( $return).'</p></div>';
			}
			libxml_clear_errors();

		}
		?>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'code' ) ); ?>"><?php _e( 'Embed code:' ); ?></label>
			<textarea class="widefat" rows="16" cols="20" id="<?php echo esc_attr( $this->get_field_id( 'code' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'code' ) ); ?>" type="text" placeholder="HTML Code"><?php echo esc_attr( $code ); ?></textarea>
		</p>
		<p><small>There is a danger that code pasted in here will break the site layout. <em>Please test your embed codes when you add them</em>, and consider any warnings shown here carefully.</small></p>
	<?php
	}

	public function update( $new_instance, $old_instance ) {
		$instance = array();
		$instance['title'] = ( ! empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '';
		$instance['code'] = ( ! empty( $new_instance['code'] ) ) ? balanceTags( $new_instance['code'], true ) : '';
		$instance['intro'] = ( ! empty( $new_instance['intro'] ) ) ? strip_tags( $new_instance['intro'] ) : '';
		return $instance;
	}
}

add_action( 'widgets_init', function() {
	register_widget( 'Embed_Code_Widget' );
} );
