<?php
/**
 * Timify plugin frontend class.
 * This class filter the date and time by selected field from timify settings page
 * @subpackage Frontend interfaces
 * @since 1.0.0
 * @var settings - plugin options
 */

 namespace Timify\includes\frontend;
 use Timify\helpers\HelperFunctions;

if( !class_exists('Timify_Frontend') ):

	class Timify_Frontend {
		use HelperFunctions;

		protected $settings;

		private $date_format;

		public $reading_time;

		public $allwoed_html_kses = array(
			'br'     => array(),
			'em'     => array(),
			'b'      => array(),
			'strong' => array(),
		);

		/**
		 * Class Constructor
		 * Loads default options and filter the date and time by selected field
		 * 
		 */
		
		public function __construct() {

			$default_sets = array(
				'active'   			=> array( 'date' => 'date', 'time' => 'time', 'modified_date' => '', 'modified_time' => '' ),
				'time'    			=> array( 'number' => '12', 'type'  => 'months' ),
				'ago_label' 		=> 'ago',
				'lm_enable'         => 'on',
				'lm_label'          => 'Last Update On:',
				'lm_display_method' => 'before_content',
				'lm_alignment'		=> 'left',
				'lm_post_date_selector'=>'.posted-on .entry-date',
				'rt_enable'			=> 'on',
				'rt_label'			=> 'Reading Time:',
				'rt_postfix'		=> 'Minutes',
				'rt_postfixs'		=> 'Minute',
				'rt_word_per_minute'=> '200',
				'rt_display_method' => 'before_content',
				'rt_alignment'		=> 'left',
				'lm_rt_post_types'	=> array('post'),

			);

			$default_sets = apply_filters( 'timify_modify_default_sets', $default_sets );
			$this->settings = get_option( 'timify_settings', $default_sets );
			$this->settings = wp_parse_args( $this->settings, $default_sets);
			$list_filter_array = array();

			if ( isset($this->settings['active']['date']) ):
				$list_filter_array = array_merge( $list_filter_array, array( 'the_date', 'get_the_date' ) );
			endif;
			if ( isset($this->settings['active']['time']) ) :
				$list_filter_array = array_merge( $list_filter_array, array( 'get_the_time', 'the_time' ) );
			endif;
			if ( isset($this->settings['active']['modified_date']) ) :
				$list_filter_array = array_merge( $list_filter_array, array( 'get_the_modified_date', 'the_modified_date' ) );
			endif;
			if ( isset($this->settings['active']['modified_time']) ) :
				$list_filter_array = array_merge( $list_filter_array, array( 'get_the_modified_time', 'the_modified_time' ) );
			endif;
			$filterlists = apply_filters( 'timify_filters',$list_filter_array );

			foreach ( $filterlists as $filter ) :
				add_filter( $filter, array( &$this, 'convert_date_time_ago' ), 10, 2 );
			endforeach;

			//last modified and reading time insert post content
			add_filter( 'the_content', array($this,'lm_rt_display_info'), apply_filters( 'timify_display_priority', 5 ) );
			add_action( 'wp_footer', array($this,'lm_published_date_replace'), 99 );

				
		}

		/**
		 * Published date to modified Date replace using jQuery.
		 */
		public function lm_published_date_replace() {
			global $post;
			
			if ( ! is_singular() ) {
				return;
			}

			if ( ! $this->is_enabled( 'lm_enable' ) ) {
				return;
			}

			$post_id = $post->ID;
			$post_types = $this->get_data( 'lm_rt_post_types', [ 'post' ] );
			if ( ! in_array( get_post_type( $post_id ), $post_types ) ) {
				return;
			}

			$position = $this->get_data( 'lm_display_method', 'before_content' );
			if ( $position !== 'replace_original' ) {
				return;
			}

			$disable = $this->get_meta( $post_id, '_lm_disable' );
			if ( ! empty( $disable ) && $disable == 'yes' ) {
				return;
			}

			$selectors = $this->get_data( 'lm_post_date_selector' );
			if (  empty( $selectors ) ) {
				return;
			}

			$modified_timestamp = get_post_modified_time( 'U' );
			$time = current_time( 'U' );

			$ago_label =  $this->settings['ago_label'];
			$timestamp = human_time_diff( $modified_timestamp, $time ).' '.$ago_label;

			//time filter hook
			$timestamp = apply_filters( 'timify_post_formatted_date', $timestamp, get_the_ID() );

			$template ='<span class="timify-lm-rlast-modified-info">'.$timestamp.'</span>';
			$selectors = preg_replace( "/\r|\n/", '', wp_kses_post( $selectors ) ); ?>

			<script type="text/javascript">
				if(typeof jQuery != "undefined") {
					jQuery(document).ready(function ($) {
						var selector = $( '<?php echo wp_kses_post($selectors); ?>' );
						if ( selector.length ) {
							selector.replaceWith( '<?php echo wp_kses_post($template); ?>' );
						}
					});
				} else {
					document.addEventListener('DOMContentLoaded', (event) => {
						var selector = document.querySelectorAll( '<?php echo wp_kses_post($selectors); ?>' );
						if ( selector.length ) {
							selector[0].innerHTML='<?php echo wp_kses_post($template); ?>';
						}
					});
				}
			</script>
			
			<?php
		}


		/**
		 * Show last modified date and reading time info.
		 * 
		 * @param string  $content  Original Content
		 * @return string $content  Filtered Content
		 */
		public function lm_rt_display_info( $content ) {
			$template='<div class="lm-rt-wrap">';
			$lm_display_position = $this->get_data( 'lm_display_method', 'before_content' );
			$rt_display_position = $this->get_data( 'rt_display_method', 'before_content' );

			if ( ! is_singular() ) {
				return $content;
			}
		
			$post_types = $this->get_data( 'lm_rt_post_types', [ 'post' ] );
			if ( ! in_array( get_post_type(), $post_types ) ) {
				return $content;
			}

			if ( ! in_the_loop() && apply_filters( 'timify_disable_post_loop', true ) ) {
				return $content;
			}

		
			if ( $this->settings['lm_enable']==='on' && in_array( $lm_display_position, [ 'before_content' ]) ) {
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
					$template .='<span class="timify_lm_info" ' .$lm_style. '>
					<span class="lm-label">' . wp_kses( $label, $this->allwoed_html_kses ) . '</span> 
					<span class="lm-date">'.$timestamp.'</span> 
					</span>';
				}
			}

			if( $this->settings['rt_enable']==='on' && in_array( $rt_display_position, [ 'before_content' ]) ) {
				$post_id          = get_the_ID();
				$this->rt_calculation( $post_id, $this->settings );
				$label            = $this->settings['rt_label'];
				$postfix          = $this->settings['rt_postfix'];
				$postfixs         = $this->settings['rt_postfixs'];
				$rt_alignment     = $this->settings['rt_alignment'];
				$rt_style 		  = "style='display:block;text-align:$rt_alignment'";
				$cal_postfix	  = $this->add_postfix_reading_time( $this->reading_time, $postfixs, $postfix );
				$rtdisable 		  = $this->get_meta( get_the_ID(), '_rt_disable' );
				if ( empty( $rtdisable ) || ! empty( $rtdisable ) && $rtdisable == 'no' ) {
					$template .='<span class="timify_rt_info" '.$rt_style.'>
					<span class="rt-label rt-prefix">' . wp_kses( $label, $this->allwoed_html_kses ) . '</span> 
					<span class="rt-time">' . esc_html( $this->reading_time ) . '</span> 
					<span class="rt-label rt-postfix">' . wp_kses( $cal_postfix, $this->allwoed_html_kses ) . '</span>
					</span>';
				}
			}

			$template.='</div>';

			$content = $template . $content;
		
			return apply_filters( 'timify_post_content_output', $content, $template, get_the_ID() );
		}


		/**
		 * Convert Date
		 * settings page applay date formate is active then this function is working
		 * or not active this function not working
		 * 
		 * @param $original_time Original time
		 * @param $date_format the_date and get_the_date formate
		 * @return string
		 * @since 1.0.0
		 */
		public function convert_date_time_ago( $original_time, $date_format ) {

			$timelist_array = array(
				'minutes' => 60,
				'hours' => HOUR_IN_SECONDS,
				'days' => DAY_IN_SECONDS,
				'months' => YEAR_IN_SECONDS / 12,
			);

			global $post;
			$this->date_format = $date_format;

			if( !$this->is_can_convert_date() ){
				return $original_time;
			}

			if ( !$this->settings['active'] ){
				return $original_time;
			}

			$curr_time = current_time( 'timestamp' );
			$limit = (int)$this->settings['time']['number'] * $timelist_array[$this->settings['time']['type']] ;
			$post_time = strpos( current_filter(), 'modified' ) ? strtotime( $post->post_modified ) : strtotime( $post->post_date );

			if ( ( $curr_time - $post_time ) <= $limit ) {
				$alabel = $this->settings['ago_label'];
				return human_time_diff( $post_time, $curr_time ).' '.$alabel;
			}

			return $original_time;

		}

		
		/**
		 * post readign time calculate by settings page input field word per minute 
		 * @param $post_id ,reading time $options
		 * @return string time
		 * @since  1.0.0
		 */
		public function rt_calculation( $post_id, $rt_options ) {

			$rt_content       	= get_post_field( 'post_content', $post_id );
			$rt_content 		= wp_strip_all_tags( $rt_content );
			$word_count 		= count( preg_split( '/\s+/', $rt_content ) );
			$word_count 		= apply_filters( 'timify_filter_wordcount', $word_count );
			$this->reading_time = $word_count / $rt_options['rt_word_per_minute'];

			// If the reading time is 0 then return it as < 1 instead of 0.
			if ( 1 >= $this->reading_time ) {
				$this->reading_time = __( '1', 'timify' );
			} else {
				$this->reading_time = ceil( $this->reading_time );
			}
			return $this->reading_time;

		}

		/**
		 * Check the conflict with our Timify functionality
		 * AMP WordPress plugin already has Timify functionality
		 *
		 * @return bool
		 * @since 1.0.0
		 */
		function is_can_convert_date(){
			return $this->is_check_amp() || !$this->is_valid_dateformate() ? false : true;
		}

		/**
		 * Check date formate
		 * @since 1.0.0
		 * @return bool
		 */
		function is_valid_dateformate(){
			if($this->date_format === ""){
				$this->date_format = get_option( 'date_format' );
			}

			$this->date_format = preg_replace('/[^\da-z]/i', '', $this->date_format);
			return strlen($this->date_format) >= 2;
		}


		/**
		 * remove conflict with AMP plugin
		 * @return bool
		 * @since  1.0.0
		 */
		function is_check_amp(){
			return function_exists( 'is_amp_endpoint') && is_amp_endpoint();
		}


	}

endif;

//new Timify_Frontend();

?>