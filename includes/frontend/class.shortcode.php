<?php
/**
 * shortcode class for WP-Admin 
 *
 * @subpackage shortcode interface
 * @var version - plugin version
 * @since 1.0.0
 * @var settings - plugin options
 */

 use Timify\helpers\HelperFunctions;
 use Timify\includes\frontend\Timify_Frontend;

 defined( 'ABSPATH' ) || exit;
 
if( !class_exists('Timify_Shortcode') ):

	class Timify_Shortcode extends Timify_Frontend {
		use HelperFunctions;

		public function __construct() {
			parent::__construct();
			add_shortcode( 'timify-last-modified-date', [ $this, 'lm_render' ] );
			add_shortcode( 'timify-post-reading-time', [ $this, 'rt_render' ] );
		}

		/**
		 * Callback to register shortcodes for .
		 *
		 * @param array $atts Shortcode attributes.
		 * @return string     Shortcode output.
		 */
		public function lm_render( $atts ) {
			global $post;
			
			if ( ! $this->is_enabled( 'lm_enable' ) ) {
				return;
			}

			$lmposition = $this->get_data( 'lm_display_method', 'before_content' );
			if ( $lmposition !== 'shortcode_content' ) {
				return;
			}

			$post_id = $post->ID;
			$atts = shortcode_atts( [
				'id'           => $post_id,
			], $atts, 'timify-post-modified-info' );

			$get_post = get_post( absint( $atts['id'] ) );
			if ( ! $get_post ) {
				return;
			}

			$label            = $this->settings['lm_label'];
			$lm_alignment     = $this->settings['lm_alignment'];
			$lm_style 		  = "style='display:block;text-align:$lm_alignment'";

			$modified_timestamp = get_post_modified_time( 'U' );
			$time 				= current_time( 'U' );
			$ago_label 			= $this->settings['ago_label'];
			$timestamp			= human_time_diff( $modified_timestamp, $time ).' '.$ago_label;
			
			//time filter hook
			$timestamp = apply_filters( 'timify_post_formatted_date', $timestamp, get_the_ID() );
			$lmdisable = $this->get_meta( get_the_ID(), '_lm_disable' );

			if ( empty( $lmdisable ) || ! empty( $lmdisable ) && $lmdisable == 'no' ) {
				$template ='<span class="timify_lm_info" ' .$lm_style. '>
				<span class="lm-label">' . wp_kses( $label, $this->allwoed_html_kses ) . '</span> 
				<span class="lm-date">'.$timestamp.'</span> 
				</span>';
			}

			return $template;
		}

		/**
		 * Callback to register shortcodes for reading time.
		 *
		 * @param array $atts Shortcode attributes.
		 * @return string     Shortcode output.
		 */

		public function rt_render($atts) {

			global $post;
			
			if ( ! $this->is_enabled( 'rt_enable' ) ) {
				return;
			}

			$position = $this->get_data( 'rt_display_method', 'before_content' );
			if ( $position !== 'shortcode_content' ) {
				return;
			}

			$post_id = $post->ID;
			$atts = shortcode_atts( [

				'id'     => $post_id,

			], $atts, 'timify-post-modified-info' );

			$get_post = get_post( absint( $atts['id'] ) );
			if ( ! $get_post ) {
				return;
			}

			$this->rt_calculation( $post_id, $this->settings );
			$label            = $this->settings['rt_label'];
			$postfix          = $this->settings['rt_postfix'];
			$postfixs         = $this->settings['rt_postfixs'];
			$rt_alignment     = $this->settings['rt_alignment'];
			$rt_style 		  = "style='display:block;text-align:$rt_alignment'";
			$cal_postfix = $this->add_postfix_reading_time( $this->reading_time, $postfixs, $postfix );
			$rtdisable = $this->get_meta( get_the_ID(), '_rt_disable' );
			if ( empty( $rtdisable ) || ! empty( $rtdisable ) && $rtdisable == 'no' ) {
				$template ='<span class="timify_rt_info" '.$rt_style.'>
				<span class="rt-label rt-prefix">' . wp_kses( $label, $this->allwoed_html_kses ) . '</span> 
				<span class="rt-time">' . esc_html( $this->reading_time ) . '</span> 
				<span class="rt-label rt-postfix">' . wp_kses( $cal_postfix, $this->allwoed_html_kses ) . '</span>
				</span>';
			}

			return $template;
		}


	}

endif;

new Timify_Shortcode();