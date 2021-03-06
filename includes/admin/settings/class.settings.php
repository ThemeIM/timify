<?php
require_once( TIMIFY_INCLUDES . '/admin/settings/class.settings-api.php' );
if( !class_exists('Timify_Option') ):
	class Timify_Option {
		
		private $settings_api;

		function __construct() {
			$this->settings_api = new \Timify_Settings_API();
			add_action( 'admin_init', array( $this, 'admin_init' ) );
			add_action( 'admin_menu', array( $this, 'admin_menu' ) );
		}

		function admin_init() {
			//set the settings
			$this->settings_api->set_sections( $this->get_settings_sections() );
			$this->settings_api->set_fields( $this->get_settings_fields() );
			//initialize settings
			$this->settings_api->admin_init();
		}

		function admin_menu() {
			add_options_page( 
				__('Timify Settings','timify'), 
				__('Timify','timify'), 
				'manage_options', 
				'timify_settings',
				array($this,'settings_page') 
			);
		}

		function get_settings_sections() {
			$sections = array(
				array(
					'id'     => 'timify_settings',
					'title'  => __( 'Timify Settings', 'timify' ),
				),
				// array(
				// 	'id'    => 'timify_section_two',
				// 	'title' => __( 'Timify Settings Section Two', 'timify' )
				// )
			);

			return $sections;
		}

		/**
		 * Returns all the settings fields
		 *
		 * @return array settings fields
		 */
		function get_settings_fields() {
			$settings_fields = array(
				'timify_settings' => array(
					array(
						'name'  => 'active',
						'label' => __( 'Apply Date Time Format To', 'timify' ),
						'type'  => 'multicheck',
						'options'=>array(
							'date'  => __( 'Date', 'timify' ),
							'time' => __( 'Time', 'timify' ),
							'modified_date'=>__( 'Modified Date', 'timify' ),
							'modified_time'=>__( 'Modified Time', 'timify' ),
						),
						'default'=>array(
							'date'=>'date',
							'time' => 'time'
						)
					),

					array(
						'name'      => 'time',
						'label'     => __( 'Apply to Posts Time Ago Show Not Older Than', 'timify' ),
						'type'      => 'groupTextSelect',
						'group_fields'=>array('number','type'),
						'options'   =>array( 
							'minutes' => __( 'Minutes', 'timify' ), 
							'hours' => __( 'Hours', 'timify' ),
							'days'  =>__( 'Days','timify'),
							'months'=>__( 'Months','timify')
						),
						'default'=>array(
							'number'=>12,
							'type' => 'months'
						)
						
					),

					array(
						'name'  => 'ago_label',
						'label' => __( 'Change Ago Word ', 'timify' ),
						'type'  => 'text',
						'default'=> __('ago','timify')
					),

					array(
						'name'  => 'lm_enable',
						'label' => __( 'Last Modified Enable', 'timify' ),
						'type'  => 'checkbox',
						'default'=> 'on'
					),

					array(
						'name'  => 'lm_label',
						'label' => __( 'Last Modified Label', 'timify' ),
						'type'  => 'text',
						'default'=> __( 'Last modified on:', 'timify' ),
					),

					array(
						'name'  => 'lm_display_method',
						'label' => __( 'Last Modified Time Display Method:', 'timify' ),
						'type'  => 'select',
						'options'=> array( 
							'before_content' => __( 'Before Content', 'timify' ), 
							'replace_original'=>__('Replace Published Date','timify'),
							'shortcode_content'=>__('Shortcode','timify')
						), 
					),

					array(
						'name'  => 'lm_shortcode_content',
						'label' => __( 'Copy Last Modified Date Shortcode Enter the Post Content', 'timify' ),
						'type'  => 'html',
						'desc'  => '[timify-last-modified-date]'
					),

					array(
						'name'  => 'lm_alignment',
						'label' => __( 'Last Modified Before Content Alignment:', 'timify' ),
						'type'  => 'select',
						'options'=> array( 
							'left' => __( 'Left', 'timify' ), 
							'center'=>__('Center','timify'),
							'right'=>__('Right','timify')
						), 
					),

					array(
						'name'  => 'lm_post_date_selector',
						'label' => __( 'Enter CSS Selector of Post Date:', 'timify' ),
						'type'  => 'text',
						'desc'=> __('This field for replace published date css selector. If you are using any caching plugin, please clear/remove your cache after any changes made to this field.','timify'),
						'default'=>'.posted-on .entry-date'
					),

					array(
						'name'  => 'rt_enable',
						'label' => __( 'Reading Time Enable', 'timify' ),
						'type'  => 'checkbox',
						'default'=>'on'
					),

					array(
						'name'  => 'rt_label',
						'label' => __( 'Reading Time Label', 'timify' ),
						'type'  => 'text',
						'default'=>__('Reading Time:','timify')
					),

					array(
						'name'  => 'rt_postfix',
						'label' => __( 'Reading Time Postfix', 'timify' ),
						'type'  => 'text',
						'default'=>__('Minutes','timify')
					),

					array(
						'name'  => 'rt_postfixs',
						'label' => __( 'Reading Time Postfix Singular', 'timify' ),
						'type'  => 'text',
						'default'=>__('Minute','timify')
					),

					array(
						'name'  => 'rt_word_per_minute',
						'label' => __( 'Enter Word Per Minute', 'timify' ),
						'type'  => 'number',
						'default'=> 200
					),

					array(
						'name'  => 'rt_display_method',
						'label' => __( 'Reading Time Display Method', 'timify' ),
						'type'  => 'select',
						'options'=> array( 
							'before_content' => __( 'Before Content', 'timify' ), 
							'shortcode_content' => __( 'ShortCode', 'timify' ),
						)
					),

					array(
						'name'  => 'rt_shortcode_content',
						'label' => __( 'Copy Reading Time Shortcode Enter the Post Content', 'timify' ),
						'type'  => 'html',
						'desc'  => '[timify-post-reading-time]'
					),

					array(
						'name'  => 'rt_alignment',
						'label' => __( 'Reading Time Before Content Alignment:', 'timify' ),
						'type'  => 'select',
						'options'=> array( 
							'left' => __( 'Left', 'timify' ), 
							'center'=>__('Center','timify'),
							'right'=>__('Right','timify')
						), 
					),

					array(
						'name'  => 'lm_rt_post_types',
						'label' => __( 'Apply Post Type', 'timify' ),
						'type'  => 'postTypes',
						'default'=> array('post')
					),




				)


			);

			return $settings_fields;
		}

		function settings_page() { 
			echo '<div class="wrap">';
			echo sprintf( "<h2>%s</h2>", __( 'Timify Options', 'timify' ) );
			$this->settings_api->show_settings();
			echo '</div>';
		}

		/**
		 * Get all the pages
		 *
		 * @return array page names with key value pairs
		 */
		function get_pages() {
			$pages         = get_pages();
			$pages_options = array();
			if ( $pages ) {
				foreach ( $pages as $page ) {
					$pages_options[ $page->ID ] = $page->post_title;
				}
			}

			return $pages_options;
		}
	}

endif;