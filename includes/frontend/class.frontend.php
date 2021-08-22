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
		public $words_count;

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
				'show_on'   		=> array( 'single_page' => 'single_page', 'home_blog_page' => '', 'archive_page' => '' ),
				'time'    			=> array( 'number' => '12', 'type'  => 'months' ),
				'ago_label' 		=> 'ago',
				'font_size'		    => '15',
				'line_height'		=> '22',
				'margin'			=> array('left' => '1', 'top'  => '1','right'=>'1','bottom'  => '1','type'=>'px'),
				'padding'			=> array('left' => '0.5', 'top'  => '0.7','right'=>'0.5','bottom'  => '0.7','type'=>'em'),
				'bg_color'		    => '#dddddd',
				'display_bg'        => 'block',
				'text_color'		=> '#000000',
				'alignment'		    => 'center',
				'lm_enable'         => 'on',
				'lm_label'          => 'Last Update On:',
				'lm_display_method' => 'before_content',
				'lm_post_date_selector'=>'.posted-on .entry-date',
				'lm_icon_class'		=> 'dashicons-calendar-alt',
				'rt_enable'			=> 'on',
				'rt_label'			=> 'Reading Time:',
				'rt_postfix'		=> 'Minutes',
				'rt_postfixs'		=> 'Minute',
				'rt_word_per_minute'=> '200',
				'rt_display_method' => 'before_content',
				'rt_icon_class'     => 'dashicons-clock',
				'lm_rt_post_types'	=> array('post'),
				'wc_enable'			=> 'on',
				'wc_label'			=> 'Post Words:',
				'wc_postfix'		=> 'Words',
				'wc_display_method' => 'before_content',
				'wc_icon_class'	    => 'dashicons-editor-table',
				'pvc_enable'		=> 'on',
				'pvc_label'			=> 'PostView Count:',
				'pvc_postfix'		=> 'Views',
				'pvc_display_method' => 'before_content',
				'pvc_icon_class'	=> 'dashicons-visibility'


			);

			$default_sets = apply_filters( 'timify_modify_default_sets', $default_sets );
			$this->settings = get_option( 'timify_settings', $default_sets );
			$this->settings = wp_parse_args( $this->settings, $default_sets);
			$this->settings = wp_parse_args( get_option( 'timify_reading_settings', $default_sets ), $this->settings);
			$this->settings = wp_parse_args( get_option( 'timify_word_settings', $default_sets ), $this->settings);
			$this->settings = wp_parse_args( get_option( 'timify_view_settings', $default_sets ), $this->settings);

	
			add_action('loop_start', array($this,'render_loop_start'), 50);
			add_action( 'wp_enqueue_scripts', array(&$this,'render_frontend_styles') );
			add_action( 'wp', array( $this, 'render_frontend' ) );
			add_action( 'wp_footer', array($this,'lm_published_date_replace'), 99 );
			add_action( 'wp_head', array($this,'pvc_insert_by_ip') );

				
		}

		public function render_frontend(){
			
			$current_theme = $this->get_current_theme();
			$show_on = $this->get_data( 'show_on', [ 'single_page' ] );
		
			if ( in_array( 'single_page', $show_on ) && is_singular() ) {
				add_filter( 'the_content', array($this,'lm_rt_display_info'), 90 );
			}

			if ( in_array( 'home_blog_page', $show_on ) && is_home() && ! is_archive() ) {
				add_filter( 'get_the_excerpt', array( $this, 'lm_rt_display_info' ), 1000 );
				if ( 'Twenty Twenty' === $current_theme || 'Twenty Fifteen' === $current_theme || 'Twenty Nineteen' === $current_theme || 'Twenty Thirteen' === $current_theme || 'Twenty Fourteen' === $current_theme || 'Twenty Sixteen' === $current_theme || 'Twenty Seventeen' === $current_theme || 'Twenty Twelve' === $current_theme ) {
					add_filter( 'the_content', array( $this, 'lm_rt_display_info' ), 1000 );
				}
			}

			if ( in_array( 'archive_page', $show_on ) && ! is_home() && is_archive() ) { 
				add_filter( 'get_the_excerpt', array( $this, 'lm_rt_display_info' ), 1000 );
				if ( 'Twenty Twenty' === $current_theme || 'Twenty Fifteen' === $current_theme || 'Twenty Nineteen' === $current_theme || 'Twenty Thirteen' === $current_theme || 'Twenty Fourteen' === $current_theme || 'Twenty Sixteen' === $current_theme || 'Twenty Seventeen' === $current_theme || 'Twenty Twelve' === $current_theme ) {
					add_filter( 'the_content', array( $this, 'lm_rt_display_info' ), 1000 );
				}
			}

		}

		public function render_frontend_styles() {
			wp_register_style( 'timify-style', false );
			wp_enqueue_style( 'timify-style' );
			$css = array();
			$css[] = ".timify-meta-wrap { 
				font-size: {$this->settings['font_size']}px;
				line-height: {$this->settings['line_height']}px;
				text-align: {$this->settings['alignment']};
				margin-top: {$this->settings['margin']['top']}{$this->settings['margin']['type']};
				margin-right: {$this->settings['margin']['right']}{$this->settings['margin']['type']};
				margin-bottom: {$this->settings['margin']['bottom']}{$this->settings['margin']['type']};
				margin-left: {$this->settings['margin']['left']}{$this->settings['margin']['type']};
			}";
			$css[] = ".timify-container {
				background:{$this->settings['bg_color']};
				color: {$this->settings['text_color']};
				padding-top: {$this->settings['padding']['top']}{$this->settings['padding']['type']};
				padding-right: {$this->settings['padding']['right']}{$this->settings['padding']['type']};
				padding-bottom: {$this->settings['padding']['bottom']}{$this->settings['padding']['type']};
				padding-left: {$this->settings['padding']['left']}{$this->settings['padding']['type']};
				display: {$this->settings['display_bg']};
			}";
			wp_add_inline_style( 'timify-style', preg_replace( '/\n|\t/i', '', implode( '', $css ) ));
		}
		
		public function pvc_insert_by_ip() {
			global $post;
			if ( ! wp_is_post_revision( $post ) && ! is_preview() ) {
				if ( is_single() ) {
					timify_insert_ip();
				}
			}
		}

		public function render_loop_start() {
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
			$filterlists = apply_filters( 'timify_date_filters',$list_filter_array );

			foreach ( $filterlists as $filter ) :
				add_filter( $filter, array( &$this, 'convert_date_time_ago' ), 10, 2 );
			endforeach;

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
			$timestamp = human_time_diff( $modified_timestamp, $time ).'&nbsp;'.$ago_label;

			//time filter hook
			$timestamp = apply_filters( 'timify_post_formatted_date', $timestamp, get_the_ID() );

			$template ='<span class="timify_lm_info">'.$timestamp.'</span>';
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
			global $post;
			$template_reading = $template_last_modified = $template_word = $template_view = '';
			$lm_display_position  = $this->settings['lm_display_method'];
			$rt_display_position  = $this->settings['rt_display_method'];
			$wc_display_position  = $this->settings['wc_display_method'];
			$pvc_display_position = $this->settings['pvc_display_method'];

			$post_types = $this->get_data( 'lm_rt_post_types', [ 'post' ] );
			if ( ! in_array( get_post_type(), $post_types ) ) {
				return $content;
			}

			if ( ! in_the_loop() && apply_filters( 'timify_disable_post_loop', true ) ) {
				return $content;
			}

		
			if ( $this->settings['lm_enable']==='on' && in_array( $lm_display_position, [ 'before_content' ]) ) {
				$modified_timestamp = get_post_modified_time( 'U');
				$time = current_time( 'U' );
				$ago_label = $this->settings['ago_label'];
				$lm_label  = !empty($this->settings['lm_label'])?'<span class="label">'.$this->settings['lm_label'].'</span>':'';
				$icon	   = !empty($this->settings['lm_icon_class'])?'<span class="icon dashicons '.$this->settings['lm_icon_class'].'"></span>':'';
				$timestamp = '<span class="time">&nbsp;'.human_time_diff( $modified_timestamp, $time ).'&nbsp;'.$ago_label.'</span>';
				$lmdisable = $this->get_meta( get_the_ID(), '_lm_disable' );
				if ( empty( $lmdisable ) || ! empty( $lmdisable ) && $lmdisable == 'no' ) {
					$template_last_modified = '<span class="timify-meta-last-modified-wrap">'.$lm_label.'&nbsp;'.$icon .$timestamp.'</span>';
				}
			}

			if ( $this->settings['rt_enable']==='on' && in_array( $rt_display_position, [ 'before_content' ]) ) { 
				$post_id = $post->ID;
				$this->rt_calculation( $post_id, $this->settings );
				$postfix          = $this->settings['rt_postfix'];
				$postfixs         = $this->settings['rt_postfixs'];
				$reading_time     = '<span class="reading">&nbsp;'.$this->reading_time.'</span>';
				$cal_postfix	  = $this->add_postfix_reading_time( $this->reading_time, $postfixs, $postfix );
				$cal_postfix	  = !empty($cal_postfix)?'<span class="postfix">'.$cal_postfix.'</span>':'';
				$icon		  	  = !empty($this->settings['rt_icon_class'])?'<span class="icon dashicons '.$this->settings['rt_icon_class'].'"></span>':'';
				$rtdisable 		  = $this->get_meta( get_the_ID(), '_rt_disable' );
				if ( empty( $rtdisable ) || ! empty( $rtdisable ) && $rtdisable == 'no' ) {
					$template_reading = '<span class="timify-meta-reading-wrap">'.$icon . $reading_time.'&nbsp;'.$cal_postfix.'</span>';
				}
			}

			if ( $this->settings['wc_enable']==='on' && in_array( $wc_display_position, [ 'before_content' ]) ) { 
				$post_id          = $post->ID;
				$content_post     = get_post($post_id);
				$content_word 	  = $content_post->post_content;
				$post_words_count = '<span class="words">&nbsp;'.$this->wc_calculation($content_word).'</span>';
				$postfix          = !empty($this->settings['wc_postfix'])?'<span class="postfix">'.$this->settings['wc_postfix'].'</span>':'';
				$icon		  	  = !empty($this->settings['wc_icon_class'])?'<span class="icon dashicons '.$this->settings['wc_icon_class'].'"></span>':'';
				$wcdisable 		  = $this->get_meta( get_the_ID(), '_wc_disable' );
				if ( empty( $wcdisable ) || ! empty( $wcdisable ) && $wcdisable == 'no' ) {
					$template_word    = '<span class="timify-meta-word-wrap">'. $icon . $post_words_count.'&nbsp;'.$postfix.'</span>';
				}
			}

			if ( $this->settings['pvc_enable']==='on' && in_array( $pvc_display_position, [ 'before_content' ]) ) { 
				$post_id          = $post->ID;
				$post_view_count  = '<span class="views">&nbsp;'.timify_get_post_view_count().'</span>';
				$postfix          = !empty($this->settings['pvc_postfix'])?'<span class="postfix">'.$this->settings['pvc_postfix'].'</span>':'';
				$icon		  	  = !empty($this->settings['pvc_icon_class'])?'<span class="icon dashicons '.$this->settings['pvc_icon_class'].'"></span>':'';
				$pvcdisable 	  = $this->get_meta( get_the_ID(), '_pvc_disable' );
				if ( empty( $pvcdisable ) || ! empty( $pvcdisable ) && $pvcdisable == 'no' ) {
					$template_view 	  = '<span class="timify-meta-view-wrap">'. $icon. $post_view_count.'&nbsp;'.$postfix.'</span>';
				}
			}
            
			if( !empty($template_last_modified) || !empty($template_reading) || !empty($template_word) || !empty($template_view) && in_the_loop() ):
				$html = '<div class="timify-meta-wrap"><span class="timify-container">'.$template_last_modified.'&nbsp;'.$template_reading.'&nbsp;'.$template_word.'&nbsp;'.$template_view.'</span></div>';
				$content = $html . $content;
			else:
				$content = $content;
			endif;
		
			return apply_filters( 'timify_post_content_output', $content, get_the_ID() );
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
			global $post;
			$this->date_format = $date_format;

			$timelist_array = array(
				'minutes' => 60,
				'hours' => HOUR_IN_SECONDS,
				'days' => DAY_IN_SECONDS,
				'months' => YEAR_IN_SECONDS / 12,
			);

			$post_id = $post->ID;
			$post_types = $this->get_data( 'lm_rt_post_types', [ 'post' ] );
			if ( ! in_array( get_post_type( $post_id ), $post_types ) ) {
				return $original_time;
			}

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
				return human_time_diff( $post_time, $curr_time ).'&nbsp;'.$alabel;
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
		 * post words count calculate 
		 * @param $content
		 * @return string words
		 * @since  1.0.0
		 */
		public function wc_calculation($content){
			return $this->words_count=str_word_count((strip_tags($content)));
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