<?php
/*
Plugin Name: Genesis Design Palette Pro - Entry Content
Plugin URI: https://genesisdesignpro.com/
Description: Fine tune the look of the content inside posts and pages in Genesis Design Palette Pro
Author: Reaktiv Studios
Version: 1.0.2
Requires at least: 3.7
Author URI: http://reaktivstudios.com/
*/
/*  Copyright 2014 Andrew Norcross, Reaktiv Studios

	This program is free software; you can redistribute it and/or modify
	it under the terms of the GNU General Public License as published by
	the Free Software Foundation; version 2 of the License (GPL v2) only.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with this program; if not, write to the Free Software
	Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

if ( ! defined( 'GPECN_BASE' ) ) {
	define( 'GPECN_BASE', plugin_basename(__FILE__) );
}

if ( ! defined( 'GPECN_DIR' ) ) {
	define( 'GPECN_DIR', dirname( __FILE__ ) );
}

if ( ! defined( 'GPECN_VER' ) ) {
	define( 'GPECN_VER', '1.0.2' );
}


class GP_Pro_Entry_Content
{

	/**
	 * Static property to hold our singleton instance
	 * @var instance
	 */
	static $instance = false;

	/**
	 * This is our constructor
	 *
	 * @return GP_Pro_Entry_Content
	 */
	private function __construct() {

		// general backend
		add_action( 'plugins_loaded',                       array( $this, 'textdomain'                      )           );
		add_action( 'admin_notices',                        array( $this, 'gppro_active_check'              ),  10      );
		add_action( 'admin_notices',                        array( $this, 'gppro_version_check'             ),  10      );

		// GP Pro specific
		add_filter( 'gppro_set_defaults',                   array( $this, 'entry_defaults_base'             ),  30      );
		add_filter( 'gppro_admin_block_add',                array( $this, 'entry_content_block'             ),  35      );
		add_filter( 'gppro_section_inline_post_content',    array( $this, 'entry_inline_post_content'       ),  15, 2   );
		add_filter( 'gppro_sections',                       array( $this, 'entry_content_sections'          ),  10, 2   );
	}

	/**
	 * If an instance exists, this returns it.  If
	 * not, it creates one and retuns it.
	 *
	 * @return instance
	 */
	public static function getInstance() {

		// load the instance if we don't have it
		if ( ! self::$instance ) {
			self::$instance = new self;
		}

		// return the instance
		return self::$instance;
	}

	/**
	 * load textdomain
	 *
	 * @return
	 */
	public function textdomain() {
		load_plugin_textdomain( 'gppro-entry-content', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
	}

	/**
	 * check for GP Pro being active
	 *
	 * @return [type] [description]
	 */
	public function gppro_active_check() {

		// get the current screen
		$screen = get_current_screen();

		// bail if not on the plugins page
		if ( ! is_object( $screen ) || empty( $screen->parent_file ) || $screen->parent_file !== 'plugins.php' ) {
			return;
		}

		// run the active check
		$coreactive = class_exists( 'Genesis_Palette_Pro' ) ? Genesis_Palette_Pro::check_active() : false;

		// active. bail
		if ( $coreactive ) {
			return;
		}

		// not active. show message
		echo '<div id="message" class="error fade below-h2"><p><strong>' . __( sprintf( 'This plugin requires Genesis Design Palette Pro to function and cannot be activated.' ), 'gppro-entry-content' ) . '</strong></p></div>';

		// hide activation method
		unset( $_GET['activate'] );

		// deactivate the plugin
		deactivate_plugins( plugin_basename( __FILE__ ) );

		// and finish
		return;
	}

	/**
	 * Check for valid Design Palette Pro Version
	 *
	 * Requires version 1.3.0+
	 *
	 * @since 1.0.1
	 *
	 */
	public function gppro_version_check() {

		// get the version
		$dppver = defined( 'GPP_VER' ) ? GPP_VER : 0;

		// do the message
		if ( version_compare( $dppver, '1.3.0', '<' ) ) {
			printf(
				'<div class="updated"><p>' . esc_html__( 'Please upgrade %2$sDesign Palette Pro to version 1.3.0 or greater%3$s to continue using the %1$s extension.', 'gppro' ) . '</p></div>',
				'<strong>' . 'Genesis Design Palette Pro - Entry Content' . '</strong>',
				'<a href="' . esc_url( admin_url( 'plugins.php?plugin_status=upgrade' ) ) . '">',
				'</a>'
			);
		}
	}

	/**
	 * swap default values
	 *
	 * @return string $defaults
	 */
	public static function entry_defaults_base( $defaults ) {

		// fetch the existing defaults
		$title_base      = ! empty( $defaults['post-title-text'] ) ? $defaults['post-title-text'] : '';
		$title_stack     = ! empty( $defaults['post-title-stack'] ) ? $defaults['post-title-stack'] : '';

		$text_base       = ! empty( $defaults['post-entry-text'] ) ? $defaults['post-entry-text'] : '';
		$text_link       = ! empty( $defaults['post-entry-link'] ) ? $defaults['post-entry-link'] : '';
		$text_link_hov   = ! empty( $defaults['post-entry-link-hov'] ) ? $defaults['post-entry-link-hov'] : '';
		$text_stack      = ! empty( $defaults['post-entry-stack'] ) ? $defaults['post-entry-stack'] : '';
		$text_size       = ! empty( $defaults['post-entry-size'] ) ? $defaults['post-entry-size'] : '';
		$text_weight     = ! empty( $defaults['post-entry-weight'] ) ? $defaults['post-entry-weight'] : '';

		$list_ul_style   = ! empty( $defaults['post-entry-list-ul'] ) ? $defaults['post-entry-list-ul'] : '';
		$list_ol_style   = ! empty( $defaults['post-entry-list-ol'] ) ? $defaults['post-entry-list-ol'] : '';

		$cap_base        = ! empty( $defaults['post-entry-caption-text'] ) ? $defaults['post-entry-caption-text'] : '';
		$cap_link        = ! empty( $defaults['post-entry-caption-link'] ) ? $defaults['post-entry-caption-link'] : '';
		$cap_link_hov    = ! empty( $defaults['post-entry-caption-link-hov'] ) ? $defaults['post-entry-caption-link-hov'] : '';

		// general body
		$changes    = array(
			'entry-content-h1-color-text'       => $title_base,
			'entry-content-h1-color-link'       => $text_link,
			'entry-content-h1-color-link-hov'   => $text_link_hov,
			'entry-content-h1-stack'            => $title_stack,
			'entry-content-h1-size'             => '36',
			'entry-content-h1-weight'           => '700',
			'entry-content-h1-margin-bottom'    => '16',
			'entry-content-h1-padding-bottom'   => '0',
			'entry-content-h1-transform'        => 'none',
			'entry-content-h1-align'            => 'left',
			'entry-content-h1-link-dec'         => 'underline',
			'entry-content-h1-link-dec-hov'     => 'underline',

			'entry-content-h2-color-text'       => $title_base,
			'entry-content-h2-color-link'       => $text_link,
			'entry-content-h2-color-link-hov'   => $text_link_hov,
			'entry-content-h2-stack'            => $title_stack,
			'entry-content-h2-size'             => '30',
			'entry-content-h2-weight'           => '700',
			'entry-content-h2-margin-bottom'    => '16',
			'entry-content-h2-padding-bottom'   => '0',
			'entry-content-h2-transform'        => 'none',
			'entry-content-h2-align'            => 'left',
			'entry-content-h2-link-dec'         => 'underline',
			'entry-content-h2-link-dec-hov'     => 'underline',

			'entry-content-h3-color-text'       => $title_base,
			'entry-content-h3-color-link'       => $text_link,
			'entry-content-h3-color-link-hov'   => $text_link_hov,
			'entry-content-h3-stack'            => $title_stack,
			'entry-content-h3-size'             => '24',
			'entry-content-h3-weight'           => '700',
			'entry-content-h3-margin-bottom'    => '16',
			'entry-content-h3-padding-bottom'   => '0',
			'entry-content-h3-transform'        => 'none',
			'entry-content-h3-align'            => 'left',
			'entry-content-h3-link-dec'         => 'underline',
			'entry-content-h3-link-dec-hov'     => 'underline',

			'entry-content-h4-color-text'       => $title_base,
			'entry-content-h4-color-link'       => $text_link,
			'entry-content-h4-color-link-hov'   => $text_link_hov,
			'entry-content-h4-stack'            => $title_stack,
			'entry-content-h4-size'             => '20',
			'entry-content-h4-weight'           => '700',
			'entry-content-h4-margin-bottom'    => '16',
			'entry-content-h4-padding-bottom'   => '0',
			'entry-content-h4-transform'        => 'none',
			'entry-content-h4-align'            => 'left',
			'entry-content-h4-link-dec'         => 'underline',
			'entry-content-h4-link-dec-hov'     => 'underline',

			'entry-content-h5-color-text'       => $title_base,
			'entry-content-h5-color-link'       => $text_link,
			'entry-content-h5-color-link-hov'   => $text_link_hov,
			'entry-content-h5-stack'            => $title_stack,
			'entry-content-h5-size'             => '18',
			'entry-content-h5-weight'           => '700',
			'entry-content-h5-margin-bottom'    => '16',
			'entry-content-h5-padding-bottom'   => '0',
			'entry-content-h5-transform'        => 'none',
			'entry-content-h5-align'            => 'left',
			'entry-content-h5-link-dec'         => 'underline',
			'entry-content-h5-link-dec-hov'     => 'underline',

			'entry-content-h6-color-text'       => $title_base,
			'entry-content-h6-color-link'       => $text_link,
			'entry-content-h6-color-link-hov'   => $text_link_hov,
			'entry-content-h6-stack'            => $title_stack,
			'entry-content-h6-size'             => '16',
			'entry-content-h6-weight'           => '700',
			'entry-content-h6-margin-bottom'    => '16',
			'entry-content-h6-padding-bottom'   => '0',
			'entry-content-h6-transform'        => 'none',
			'entry-content-h6-align'            => 'left',
			'entry-content-h6-link-dec'         => 'underline',
			'entry-content-h6-link-dec-hov'     => 'underline',

			'entry-content-p-color-text'        => $text_base,
			'entry-content-p-color-link'        => $text_link,
			'entry-content-p-color-link-hov'    => $text_link_hov,
			'entry-content-p-stack'             => $text_stack,
			'entry-content-p-size'              => $text_size,
			'entry-content-p-weight'            => $text_weight,
			'entry-content-p-margin-bottom'     => '26',
			'entry-content-p-padding-bottom'    => '0',
			'entry-content-p-transform'         => 'none',
			'entry-content-p-align'             => 'left',
			'entry-content-p-link-dec'          => 'underline',
			'entry-content-p-link-dec-hov'      => 'underline',

			'entry-content-ul-color-text'       => $text_base,
			'entry-content-ul-color-link'       => $text_link,
			'entry-content-ul-color-link-hov'   => $text_link_hov,
			'entry-content-ul-stack'            => $text_stack,
			'entry-content-ul-size'             => $text_size,
			'entry-content-ul-weight'           => $text_weight,
			'entry-content-ul-margin-left'      => '40',
			'entry-content-ul-margin-bottom'    => '26',
			'entry-content-ul-padding-left'     => '0',
			'entry-content-ul-padding-bottom'   => '0',
			'entry-content-ul-list-style'       => $list_ul_style,
			'entry-content-ul-transform'        => 'none',
			'entry-content-ul-align'            => 'left',
			'entry-content-ul-link-dec'         => 'underline',
			'entry-content-ul-link-dec-hov'     => 'underline',

			'entry-content-ol-color-text'       => $text_base,
			'entry-content-ol-color-link'       => $text_link,
			'entry-content-ol-color-link-hov'   => $text_link_hov,
			'entry-content-ol-stack'            => $text_stack,
			'entry-content-ol-size'             => $text_size,
			'entry-content-ol-weight'           => $text_weight,
			'entry-content-ol-margin-left'      => '40',
			'entry-content-ol-margin-bottom'    => '26',
			'entry-content-ol-padding-left'     => '0',
			'entry-content-ol-padding-bottom'   => '0',
			'entry-content-ol-list-style'       => $list_ol_style,
			'entry-content-ol-transform'        => 'none',
			'entry-content-ol-align'            => 'left',
			'entry-content-ol-link-dec'         => 'underline',
			'entry-content-ol-link-dec-hov'     => 'underline',

			'entry-content-cap-color-text'      => $cap_base,
			'entry-content-cap-color-link'      => $cap_link,
			'entry-content-cap-color-link-hov'  => $cap_link_hov,
			'entry-content-cap-stack'           => $text_stack,
			'entry-content-cap-size'            => $text_size,
			'entry-content-cap-weight'          => $text_weight,
			'entry-content-cap-transform'       => 'none',
			'entry-content-cap-margin-bottom'   => '26',
			'entry-content-cap-padding-bottom'  => '0',
			'entry-content-cap-transform'       => 'none',
			'entry-content-cap-align'           => 'center',
			'entry-content-cap-link-dec'        => 'underline',
			'entry-content-cap-link-dec-hov'    => 'underline',

			'entry-content-code-color-text'     => '#dddddd',
			'entry-content-code-background'     => '#333333',
			'entry-content-code-stack'          => 'monospace',
			'entry-content-code-size'           => '16',
			'entry-content-code-weight'         => '400',
		);

		// bail if changes are empty (even though they shouldn't be)
		if ( empty( $changes ) ) {
			return $defaults;
		}

		// put into key value pair
		foreach ( $changes as $key => $value ) {
			$defaults[ $key ]   = $value;
		}

		// send them back
		return $defaults;
	}

	/**
	 * add and filter options in the post content area
	 *
	 * @return array|string $sections
	 */
	public function entry_inline_post_content( $sections, $class ) {

		// remove the default post content settings in favor of our new ones
		unset( $sections['post-entry-color-setup']['title'] );
		unset( $sections['post-entry-type-setup'] );

		// info about new area
		$sections['post-entry-color-setup']['data'] = array(
			'post-entry-plugin-active'  => array(
				'input' => 'description',
				'desc'  => __( 'You are currently using the Entry Content add on, so all settings are now available there.', 'gppro-entry-content' ),
			),
		);

		// send it back
		return $sections;
	}

	/**
	 * add block to side
	 *
	 * @return
	 */
	public function entry_content_block( $blocks ) {

		$blocks['entry-content'] = array(
			'tab'       => __( 'Entry Content', 'gppro-entry-content' ),
			'title'     => __( 'Entry Content', 'gppro-entry-content' ),
			'intro'     => __( 'Fine tune the look of the content inside posts and pages.<br /><strong>Note:</strong> Post title and meta display settings are still contained in the Content Area tab.', 'gppro-entry-content' ),
			'slug'      => 'entry_content',
		);

		// return the block
		return $blocks;
	}

	/**
	 * add section to side
	 *
	 * @return
	 */
	public function entry_content_sections( $sections, $class ) {

		// build out the section
		$sections['entry_content']  = array(

			'section-break-entry-content-h1'    => array(
				'break' => array(
					'type'  => 'thin',
					'title' => __( 'H1 Headers', 'gppro-entry-content' ),
				),
			),

			'entry-content-h1-color-setup'  => array(
				'title'     => __( 'Colors', 'gppro-entry-content' ),
				'data'      => array(
					'entry-content-h1-color-text'   => array(
						'label'     => __( 'Base Color', 'gppro-entry-content' ),
						'input'     => 'color',
						'target'    => '.entry-content h1',
						'builder'   => 'GP_Pro_Builder::hexcolor_css',
						'selector'  => 'color',
					),
					'entry-content-h1-color-link'   => array(
						'label'     => __( 'Link Color', 'gppro-entry-content' ),
						'sub'       => __( 'Base', 'gppro-entry-content' ),
						'input'     => 'color',
						'target'    => '.entry-content h1 a',
						'builder'   => 'GP_Pro_Builder::hexcolor_css',
						'selector'  => 'color'
					),
					'entry-content-h1-color-link-hov'   => array(
						'label'     => __( 'Link Color', 'gppro-entry-content' ),
						'sub'       => __( 'Hover', 'gppro-entry-content' ),
						'input'     => 'color',
						'target'    => array( '.entry-content h1 a:hover', '.entry-content h1 a:focus' ),
						'builder'   => 'GP_Pro_Builder::hexcolor_css',
						'selector'  => 'color'
					),
				),
			),

			'entry-content-h1-type-setup'   => array(
				'title'     => __( 'Typography', 'gppro-entry-content' ),
				'data'      => array(
					'entry-content-h1-stack'    => array(
						'label'     => __( 'Font Stack', 'gppro-entry-content' ),
						'input'     => 'font-stack',
						'target'    => '.entry-content h1',
						'builder'   => 'GP_Pro_Builder::stack_css',
						'selector'  => 'font-family'
					),
					'entry-content-h1-size' => array(
						'label'     => __( 'Font Size', 'gppro-entry-content' ),
						'sub'       => __( 'px', 'gppro' ),
						'input'     => 'font-size',
						'scale'     => 'title',
						'target'    => '.entry-content h1',
						'builder'   => 'GP_Pro_Builder::px_css',
						'selector'  => 'font-size',
					),
					'entry-content-h1-weight'   => array(
						'label'     => __( 'Font Weight', 'gppro-entry-content' ),
						'input'     => 'font-weight',
						'target'    => '.entry-content h1',
						'builder'   => 'GP_Pro_Builder::number_css',
						'selector'  => 'font-weight',
						'tip'       => __( 'Certain fonts will not display every weight.', 'gppro-entry-content' )
					),
				),
			),

			'entry-content-h1-appearance-setup' => array(
				'title'     => __( 'Text Appearance', 'gppro-entry-content' ),
				'data'      => array(
					'entry-content-h1-margin-bottom'    => array(
						'label'     => __( 'Margin Bottom', 'gppro-entry-content' ),
						'input'     => 'spacing',
						'target'    => '.entry-content h1',
						'builder'   => 'GP_Pro_Builder::px_css',
						'selector'  => 'margin-bottom',
						'min'       => '0',
						'max'       => '60',
						'step'      => '1'
					),
					'entry-content-h1-padding-bottom'   => array(
						'label'     => __( 'Padding Bottom', 'gppro-entry-content' ),
						'input'     => 'spacing',
						'target'    => '.entry-content h1',
						'builder'   => 'GP_Pro_Builder::px_css',
						'selector'  => 'padding-bottom',
						'min'       => '0',
						'max'       => '60',
						'step'      => '1'
					),
					'entry-content-h1-transform'    => array(
						'label'     => __( 'Text Appearance', 'gppro-entry-content' ),
						'input'     => 'text-transform',
						'target'    => '.entry-content h1',
						'builder'   => 'GP_Pro_Builder::text_css',
						'selector'  => 'text-transform'
					),
					'entry-content-h1-align'    => array(
						'label'     => __( 'Text Alignment', 'gppro-entry-content' ),
						'input'     => 'text-align',
						'target'    => '.entry-content h1',
						'builder'   => 'GP_Pro_Builder::text_css',
						'selector'  => 'text-align'
					),
					'entry-content-h1-link-dec' => array(
						'label'     => __( 'Link Style', 'gppro-entry-content' ),
						'sub'       => __( 'Base', 'gppro-entry-content' ),
						'input'     => 'text-decoration',
						'target'    => '.entry-content h1 a',
						'builder'   => 'GP_Pro_Entry_Content::link_decorations',
						'selector'  => 'text-decoration'
					),
					'entry-content-h1-link-dec-hov' => array(
						'label'     => __( 'Link Style', 'gppro-entry-content' ),
						'sub'       => __( 'Hover', 'gppro-entry-content' ),
						'input'     => 'text-decoration',
						'target'    => array( '.entry-content h1 a:hover', '.entry-content h1 a:focus' ),
						'builder'   => 'GP_Pro_Entry_Content::link_decorations',
						'selector'  => 'text-decoration'
					),
				),
			),

			'section-break-entry-content-h2'    => array(
				'break' => array(
					'type'  => 'thin',
					'title' => __( 'H2 Headers', 'gppro-entry-content' ),
				),
			),

			'entry-content-h2-color-setup'  => array(
				'title'     => __( 'Colors', 'gppro-entry-content' ),
				'data'      => array(
					'entry-content-h2-color-text'   => array(
						'label'     => __( 'Base Color', 'gppro-entry-content' ),
						'input'     => 'color',
						'target'    => '.entry-content h2',
						'builder'   => 'GP_Pro_Builder::hexcolor_css',
						'selector'  => 'color'
					),
					'entry-content-h2-color-link'   => array(
						'label'     => __( 'Link Color', 'gppro-entry-content' ),
						'sub'       => __( 'Base', 'gppro-entry-content' ),
						'input'     => 'color',
						'target'    => '.entry-content h2 a',
						'builder'   => 'GP_Pro_Builder::hexcolor_css',
						'selector'  => 'color'
					),
					'entry-content-h2-color-link-hov'   => array(
						'label'     => __( 'Link Color', 'gppro-entry-content' ),
						'sub'       => __( 'Hover', 'gppro-entry-content' ),
						'input'     => 'color',
						'target'    => array( '.entry-content h2 a:hover', '.entry-content h2 a:focus' ),
						'builder'   => 'GP_Pro_Builder::hexcolor_css',
						'selector'  => 'color'
					),
				),
			),

			'entry-content-h2-type-setup'   => array(
				'title'     => __( 'Typography', 'gppro-entry-content' ),
				'data'      => array(
					'entry-content-h2-stack'    => array(
						'label'     => __( 'Font Stack', 'gppro-entry-content' ),
						'input'     => 'font-stack',
						'target'    => '.entry-content h2',
						'builder'   => 'GP_Pro_Builder::stack_css',
						'selector'  => 'font-family'
					),
					'entry-content-h2-size' => array(
						'label'     => __( 'Font Size', 'gppro-entry-content' ),
						'sub'       => __( 'px', 'gppro' ),
						'input'     => 'font-size',
						'scale'     => 'title',
						'target'    => '.entry-content h2',
						'builder'   => 'GP_Pro_Builder::px_css',
						'selector'  => 'font-size',
					),
					'entry-content-h2-weight'   => array(
						'label'     => __( 'Font Weight', 'gppro-entry-content' ),
						'input'     => 'font-weight',
						'target'    => '.entry-content h2',
						'builder'   => 'GP_Pro_Builder::number_css',
						'selector'  => 'font-weight',
						'tip'       => __( 'Certain fonts will not display every weight.', 'gppro-entry-content' )
					),
				),
			),

			'entry-content-h2-appearance-setup' => array(
				'title'     => __( 'Text Appearance', 'gppro-entry-content' ),
				'data'      => array(
					'entry-content-h2-margin-bottom'    => array(
						'label'     => __( 'Margin Bottom', 'gppro-entry-content' ),
						'input'     => 'spacing',
						'target'    => '.entry-content h2',
						'builder'   => 'GP_Pro_Builder::px_css',
						'selector'  => 'margin-bottom',
						'min'       => '0',
						'max'       => '60',
						'step'      => '1'
					),
					'entry-content-h2-padding-bottom'   => array(
						'label'     => __( 'Padding Bottom', 'gppro-entry-content' ),
						'input'     => 'spacing',
						'target'    => '.entry-content h2',
						'builder'   => 'GP_Pro_Builder::px_css',
						'selector'  => 'padding-bottom',
						'min'       => '0',
						'max'       => '60',
						'step'      => '1'
					),
					'entry-content-h2-transform'    => array(
						'label'     => __( 'Text Appearance', 'gppro-entry-content' ),
						'input'     => 'text-transform',
						'target'    => '.entry-content h2',
						'builder'   => 'GP_Pro_Builder::text_css',
						'selector'  => 'text-transform'
					),
					'entry-content-h2-align'    => array(
						'label'     => __( 'Text Alignment', 'gppro-entry-content' ),
						'input'     => 'text-align',
						'target'    => '.entry-content h2',
						'builder'   => 'GP_Pro_Builder::text_css',
						'selector'  => 'text-align'
					),
					'entry-content-h2-link-dec' => array(
						'label'     => __( 'Link Style', 'gppro-entry-content' ),
						'sub'       => __( 'Base', 'gppro-entry-content' ),
						'input'     => 'text-decoration',
						'target'    => '.entry-content h2 a',
						'builder'   => 'GP_Pro_Entry_Content::link_decorations',
						'selector'  => 'text-decoration'
					),
					'entry-content-h2-link-dec-hov' => array(
						'label'     => __( 'Link Style', 'gppro-entry-content' ),
						'sub'       => __( 'Hover', 'gppro-entry-content' ),
						'input'     => 'text-decoration',
						'target'    => array( '.entry-content h2 a:hover', '.entry-content h2 a:focus' ),
						'builder'   => 'GP_Pro_Entry_Content::link_decorations',
						'selector'  => 'text-decoration'
					),
				),
			),

			'section-break-entry-content-h3'    => array(
				'break' => array(
					'type'  => 'thin',
					'title' => __( 'H3 Headers', 'gppro-entry-content' ),
				),
			),

			'entry-content-h3-color-setup'  => array(
				'title'     => __( 'Colors', 'gppro-entry-content' ),
				'data'      => array(
					'entry-content-h3-color-text'   => array(
						'label'     => __( 'Base Color', 'gppro-entry-content' ),
						'input'     => 'color',
						'target'    => '.entry-content h3',
						'builder'   => 'GP_Pro_Builder::hexcolor_css',
						'selector'  => 'color'
					),
					'entry-content-h3-color-link'   => array(
						'label'     => __( 'Link Color', 'gppro-entry-content' ),
						'sub'       => __( 'Base', 'gppro-entry-content' ),
						'input'     => 'color',
						'target'    => '.entry-content h3 a',
						'builder'   => 'GP_Pro_Builder::hexcolor_css',
						'selector'  => 'color'
					),
					'entry-content-h3-color-link-hov'   => array(
						'label'     => __( 'Link Color', 'gppro-entry-content' ),
						'sub'       => __( 'Hover', 'gppro-entry-content' ),
						'input'     => 'color',
						'target'    => array( '.entry-content h3 a:hover', '.entry-content h3 a:focus' ),
						'builder'   => 'GP_Pro_Builder::hexcolor_css',
						'selector'  => 'color'
					),
				),
			),

			'entry-content-h3-type-setup'   => array(
				'title'     => __( 'Typography', 'gppro-entry-content' ),
				'data'      => array(
					'entry-content-h3-stack'    => array(
						'label'     => __( 'Font Stack', 'gppro-entry-content' ),
						'input'     => 'font-stack',
						'target'    => '.entry-content h3',
						'builder'   => 'GP_Pro_Builder::stack_css',
						'selector'  => 'font-family'
					),
					'entry-content-h3-size' => array(
						'label'     => __( 'Font Size', 'gppro-entry-content' ),
						'sub'       => __( 'px', 'gppro' ),
						'input'     => 'font-size',
						'scale'     => 'title',
						'target'    => '.entry-content h3',
						'builder'   => 'GP_Pro_Builder::px_css',
						'selector'  => 'font-size',
					),
					'entry-content-h3-weight'   => array(
						'label'     => __( 'Font Weight', 'gppro-entry-content' ),
						'input'     => 'font-weight',
						'target'    => '.entry-content h3',
						'builder'   => 'GP_Pro_Builder::number_css',
						'selector'  => 'font-weight',
						'tip'       => __( 'Certain fonts will not display every weight.', 'gppro-entry-content' )
					),
				),
			),

			'entry-content-h3-appearance-setup' => array(
				'title'     => __( 'Text Appearance', 'gppro-entry-content' ),
				'data'      => array(
					'entry-content-h3-margin-bottom'    => array(
						'label'     => __( 'Margin Bottom', 'gppro-entry-content' ),
						'input'     => 'spacing',
						'target'    => '.entry-content h3',
						'builder'   => 'GP_Pro_Builder::px_css',
						'selector'  => 'margin-bottom',
						'min'       => '0',
						'max'       => '60',
						'step'      => '1'
					),
					'entry-content-h3-padding-bottom'   => array(
						'label'     => __( 'Padding Bottom', 'gppro-entry-content' ),
						'input'     => 'spacing',
						'target'    => '.entry-content h3',
						'builder'   => 'GP_Pro_Builder::px_css',
						'selector'  => 'padding-bottom',
						'min'       => '0',
						'max'       => '60',
						'step'      => '1'
					),
					'entry-content-h3-transform'    => array(
						'label'     => __( 'Text Appearance', 'gppro-entry-content' ),
						'input'     => 'text-transform',
						'target'    => '.entry-content h3',
						'builder'   => 'GP_Pro_Builder::text_css',
						'selector'  => 'text-transform'
					),
					'entry-content-h3-align'    => array(
						'label'     => __( 'Text Alignment', 'gppro-entry-content' ),
						'input'     => 'text-align',
						'target'    => '.entry-content h3',
						'builder'   => 'GP_Pro_Builder::text_css',
						'selector'  => 'text-align'
					),
					'entry-content-h3-link-dec' => array(
						'label'     => __( 'Link Style', 'gppro-entry-content' ),
						'sub'       => __( 'Base', 'gppro-entry-content' ),
						'input'     => 'text-decoration',
						'target'    => '.entry-content h3 a',
						'builder'   => 'GP_Pro_Entry_Content::link_decorations',
						'selector'  => 'text-decoration'
					),
					'entry-content-h3-link-dec-hov' => array(
						'label'     => __( 'Link Style', 'gppro-entry-content' ),
						'sub'       => __( 'Hover', 'gppro-entry-content' ),
						'input'     => 'text-decoration',
						'target'    => array( '.entry-content h3 a:hover', '.entry-content h3 a:focus' ),
						'builder'   => 'GP_Pro_Entry_Content::link_decorations',
						'selector'  => 'text-decoration'
					),
				),
			),

			'section-break-entry-content-h4'    => array(
				'break' => array(
					'type'  => 'thin',
					'title' => __( 'H4 Headers', 'gppro-entry-content' ),
				),
			),

			'entry-content-h4-color-setup'  => array(
				'title'     => __( 'Colors', 'gppro-entry-content' ),
				'data'      => array(
					'entry-content-h4-color-text'   => array(
						'label'     => __( 'Base Color', 'gppro-entry-content' ),
						'input'     => 'color',
						'target'    => '.entry-content h4',
						'builder'   => 'GP_Pro_Builder::hexcolor_css',
						'selector'  => 'color'
					),
					'entry-content-h4-color-link'   => array(
						'label'     => __( 'Link Color', 'gppro-entry-content' ),
						'sub'       => __( 'Base', 'gppro-entry-content' ),
						'input'     => 'color',
						'target'    => '.entry-content h4 a',
						'builder'   => 'GP_Pro_Builder::hexcolor_css',
						'selector'  => 'color'
					),
					'entry-content-h4-color-link-hov'   => array(
						'label'     => __( 'Link Color', 'gppro-entry-content' ),
						'sub'       => __( 'Hover', 'gppro-entry-content' ),
						'input'     => 'color',
						'target'    => array( '.entry-content h4 a:hover', '.entry-content h4 a:focus' ),
						'builder'   => 'GP_Pro_Builder::hexcolor_css',
						'selector'  => 'color'
					),
				),
			),

			'entry-content-h4-type-setup'   => array(
				'title'     => __( 'Typography', 'gppro-entry-content' ),
				'data'      => array(
					'entry-content-h4-stack'    => array(
						'label'     => __( 'Font Stack', 'gppro-entry-content' ),
						'input'     => 'font-stack',
						'target'    => '.entry-content h4',
						'builder'   => 'GP_Pro_Builder::stack_css',
						'selector'  => 'font-family'
					),
					'entry-content-h4-size' => array(
						'label'     => __( 'Font Size', 'gppro-entry-content' ),
						'sub'       => __( 'px', 'gppro' ),
						'input'     => 'font-size',
						'scale'     => 'text',
						'target'    => '.entry-content h4',
						'builder'   => 'GP_Pro_Builder::px_css',
						'selector'  => 'font-size',
					),
					'entry-content-h4-weight'   => array(
						'label'     => __( 'Font Weight', 'gppro-entry-content' ),
						'input'     => 'font-weight',
						'target'    => '.entry-content h4',
						'builder'   => 'GP_Pro_Builder::number_css',
						'selector'  => 'font-weight',
						'tip'       => __( 'Certain fonts will not display every weight.', 'gppro-entry-content' )
					),
				),
			),

			'entry-content-h4-appearance-setup' => array(
				'title'     => __( 'Text Appearance', 'gppro-entry-content' ),
				'data'      => array(
					'entry-content-h4-margin-bottom'    => array(
						'label'     => __( 'Margin Bottom', 'gppro-entry-content' ),
						'input'     => 'spacing',
						'target'    => '.entry-content h4',
						'builder'   => 'GP_Pro_Builder::px_css',
						'selector'  => 'margin-bottom',
						'min'       => '0',
						'max'       => '60',
						'step'      => '1'
					),
					'entry-content-h4-padding-bottom'   => array(
						'label'     => __( 'Padding Bottom', 'gppro-entry-content' ),
						'input'     => 'spacing',
						'target'    => '.entry-content h4',
						'builder'   => 'GP_Pro_Builder::px_css',
						'selector'  => 'padding-bottom',
						'min'       => '0',
						'max'       => '60',
						'step'      => '1'
					),
					'entry-content-h4-transform'    => array(
						'label'     => __( 'Text Appearance', 'gppro-entry-content' ),
						'input'     => 'text-transform',
						'target'    => '.entry-content h4',
						'builder'   => 'GP_Pro_Builder::text_css',
						'selector'  => 'text-transform'
					),
					'entry-content-h4-align'    => array(
						'label'     => __( 'Text Alignment', 'gppro-entry-content' ),
						'input'     => 'text-align',
						'target'    => '.entry-content h4',
						'builder'   => 'GP_Pro_Builder::text_css',
						'selector'  => 'text-align'
					),
					'entry-content-h4-link-dec' => array(
						'label'     => __( 'Link Style', 'gppro-entry-content' ),
						'sub'       => __( 'Base', 'gppro-entry-content' ),
						'input'     => 'text-decoration',
						'target'    => '.entry-content h4 a',
						'builder'   => 'GP_Pro_Entry_Content::link_decorations',
						'selector'  => 'text-decoration'
					),
					'entry-content-h4-link-dec-hov' => array(
						'label'     => __( 'Link Style', 'gppro-entry-content' ),
						'sub'       => __( 'Hover', 'gppro-entry-content' ),
						'input'     => 'text-decoration',
						'target'    => array( '.entry-content h4 a:hover', '.entry-content h4 a:focus' ),
						'builder'   => 'GP_Pro_Entry_Content::link_decorations',
						'selector'  => 'text-decoration'
					),
				),
			),

			'section-break-entry-content-h5'    => array(
				'break' => array(
					'type'  => 'thin',
					'title' => __( 'H5 Headers', 'gppro-entry-content' ),
				),
			),

			'entry-content-h5-color-setup'  => array(
				'title'     => __( 'Colors', 'gppro-entry-content' ),
				'data'      => array(
					'entry-content-h5-color-text'   => array(
						'label'     => __( 'Base Color', 'gppro-entry-content' ),
						'input'     => 'color',
						'target'    => '.entry-content h5',
						'builder'   => 'GP_Pro_Builder::hexcolor_css',
						'selector'  => 'color'
					),
					'entry-content-h5-color-link'   => array(
						'label'     => __( 'Link Color', 'gppro-entry-content' ),
						'sub'       => __( 'Base', 'gppro-entry-content' ),
						'input'     => 'color',
						'target'    => '.entry-content h5 a',
						'builder'   => 'GP_Pro_Builder::hexcolor_css',
						'selector'  => 'color'
					),
					'entry-content-h5-color-link-hov'   => array(
						'label'     => __( 'Link Color', 'gppro-entry-content' ),
						'sub'       => __( 'Hover', 'gppro-entry-content' ),
						'input'     => 'color',
						'target'    => array( '.entry-content h5 a:hover', '.entry-content h5 a:focus' ),
						'builder'   => 'GP_Pro_Builder::hexcolor_css',
						'selector'  => 'color'
					),
				),
			),

			'entry-content-h5-type-setup'   => array(
				'title'     => __( 'Typography', 'gppro-entry-content' ),
				'data'      => array(
					'entry-content-h5-stack'    => array(
						'label'     => __( 'Font Stack', 'gppro-entry-content' ),
						'input'     => 'font-stack',
						'target'    => '.entry-content h5',
						'builder'   => 'GP_Pro_Builder::stack_css',
						'selector'  => 'font-family'
					),
					'entry-content-h5-size' => array(
						'label'     => __( 'Font Size', 'gppro-entry-content' ),
						'sub'       => __( 'px', 'gppro' ),
						'input'     => 'font-size',
						'scale'     => 'text',
						'target'    => '.entry-content h5',
						'builder'   => 'GP_Pro_Builder::px_css',
						'selector'  => 'font-size',
					),
					'entry-content-h5-weight'   => array(
						'label'     => __( 'Font Weight', 'gppro-entry-content' ),
						'input'     => 'font-weight',
						'target'    => '.entry-content h5',
						'builder'   => 'GP_Pro_Builder::number_css',
						'selector'  => 'font-weight',
						'tip'       => __( 'Certain fonts will not display every weight.', 'gppro-entry-content' )
					),
				),
			),

			'entry-content-h5-appearance-setup' => array(
				'title'     => __( 'Text Appearance', 'gppro-entry-content' ),
				'data'      => array(
					'entry-content-h5-margin-bottom'    => array(
						'label'     => __( 'Margin Bottom', 'gppro-entry-content' ),
						'input'     => 'spacing',
						'target'    => '.entry-content h5',
						'builder'   => 'GP_Pro_Builder::px_css',
						'selector'  => 'margin-bottom',
						'min'       => '0',
						'max'       => '60',
						'step'      => '1'
					),
					'entry-content-h5-padding-bottom'   => array(
						'label'     => __( 'Padding Bottom', 'gppro-entry-content' ),
						'input'     => 'spacing',
						'target'    => '.entry-content h5',
						'builder'   => 'GP_Pro_Builder::px_css',
						'selector'  => 'padding-bottom',
						'min'       => '0',
						'max'       => '60',
						'step'      => '1'
					),
					'entry-content-h5-transform'    => array(
						'label'     => __( 'Text Appearance', 'gppro-entry-content' ),
						'input'     => 'text-transform',
						'target'    => '.entry-content h5',
						'builder'   => 'GP_Pro_Builder::text_css',
						'selector'  => 'text-transform'
					),
					'entry-content-h5-align'    => array(
						'label'     => __( 'Text Alignment', 'gppro-entry-content' ),
						'input'     => 'text-align',
						'target'    => '.entry-content h5',
						'builder'   => 'GP_Pro_Builder::text_css',
						'selector'  => 'text-align'
					),
					'entry-content-h5-link-dec' => array(
						'label'     => __( 'Link Style', 'gppro-entry-content' ),
						'sub'       => __( 'Base', 'gppro-entry-content' ),
						'input'     => 'text-decoration',
						'target'    => '.entry-content h5 a',
						'builder'   => 'GP_Pro_Entry_Content::link_decorations',
						'selector'  => 'text-decoration'
					),
					'entry-content-h5-link-dec-hov' => array(
						'label'     => __( 'Link Style', 'gppro-entry-content' ),
						'sub'       => __( 'Hover', 'gppro-entry-content' ),
						'input'     => 'text-decoration',
						'target'    => array( '.entry-content h5 a:hover', '.entry-content h5 a:focus' ),
						'builder'   => 'GP_Pro_Entry_Content::link_decorations',
						'selector'  => 'text-decoration'
					),
				),
			),

			'section-break-entry-content-h6'    => array(
				'break' => array(
					'type'  => 'thin',
					'title' => __( 'H6 Headers', 'gppro-entry-content' ),
				),
			),

			'entry-content-h6-color-setup'  => array(
				'title'     => __( 'Colors', 'gppro-entry-content' ),
				'data'      => array(
					'entry-content-h6-color-text'   => array(
						'label'     => __( 'Base Color', 'gppro-entry-content' ),
						'input'     => 'color',
						'target'    => '.entry-content h6',
						'builder'   => 'GP_Pro_Builder::hexcolor_css',
						'selector'  => 'color'
					),
					'entry-content-h6-color-link'   => array(
						'label'     => __( 'Link Color', 'gppro-entry-content' ),
						'sub'       => __( 'Base', 'gppro-entry-content' ),
						'input'     => 'color',
						'target'    => '.entry-content h6 a',
						'builder'   => 'GP_Pro_Builder::hexcolor_css',
						'selector'  => 'color'
					),
					'entry-content-h6-color-link-hov'   => array(
						'label'     => __( 'Link Color', 'gppro-entry-content' ),
						'sub'       => __( 'Hover', 'gppro-entry-content' ),
						'input'     => 'color',
						'target'    => array( '.entry-content h6 a:hover', '.entry-content h6 a:focus' ),
						'builder'   => 'GP_Pro_Builder::hexcolor_css',
						'selector'  => 'color'
					),
				),
			),

			'entry-content-h6-type-setup'   => array(
				'title'     => __( 'Typography', 'gppro-entry-content' ),
				'data'      => array(
					'entry-content-h6-stack'    => array(
						'label'     => __( 'Font Stack', 'gppro-entry-content' ),
						'input'     => 'font-stack',
						'target'    => '.entry-content h6',
						'builder'   => 'GP_Pro_Builder::stack_css',
						'selector'  => 'font-family'
					),
					'entry-content-h6-size' => array(
						'label'     => __( 'Font Size', 'gppro-entry-content' ),
						'sub'       => __( 'px', 'gppro' ),
						'input'     => 'font-size',
						'scale'     => 'text',
						'target'    => '.entry-content h6',
						'builder'   => 'GP_Pro_Builder::px_css',
						'selector'  => 'font-size',
					),
					'entry-content-h6-weight'   => array(
						'label'     => __( 'Font Weight', 'gppro-entry-content' ),
						'input'     => 'font-weight',
						'target'    => '.entry-content h6',
						'builder'   => 'GP_Pro_Builder::number_css',
						'selector'  => 'font-weight',
						'tip'       => __( 'Certain fonts will not display every weight.', 'gppro-entry-content' )
					),
				),
			),

			'entry-content-h6-appearance-setup' => array(
				'title'     => __( 'Text Appearance', 'gppro-entry-content' ),
				'data'      => array(
					'entry-content-h6-margin-bottom'    => array(
						'label'     => __( 'Margin Bottom', 'gppro-entry-content' ),
						'input'     => 'spacing',
						'target'    => '.entry-content h6',
						'builder'   => 'GP_Pro_Builder::px_css',
						'selector'  => 'margin-bottom',
						'min'       => '0',
						'max'       => '60',
						'step'      => '1'
					),
					'entry-content-h6-padding-bottom'   => array(
						'label'     => __( 'Padding Bottom', 'gppro-entry-content' ),
						'input'     => 'spacing',
						'target'    => '.entry-content h6',
						'builder'   => 'GP_Pro_Builder::px_css',
						'selector'  => 'padding-bottom',
						'min'       => '0',
						'max'       => '60',
						'step'      => '1'
					),
					'entry-content-h6-transform'    => array(
						'label'     => __( 'Text Appearance', 'gppro-entry-content' ),
						'input'     => 'text-transform',
						'target'    => '.entry-content h6',
						'builder'   => 'GP_Pro_Builder::text_css',
						'selector'  => 'text-transform'
					),
					'entry-content-h6-align'    => array(
						'label'     => __( 'Text Alignment', 'gppro-entry-content' ),
						'input'     => 'text-align',
						'target'    => '.entry-content h6',
						'builder'   => 'GP_Pro_Builder::text_css',
						'selector'  => 'text-align'
					),
					'entry-content-h6-link-dec' => array(
						'label'     => __( 'Link Style', 'gppro-entry-content' ),
						'sub'       => __( 'Base', 'gppro-entry-content' ),
						'input'     => 'text-decoration',
						'target'    => '.entry-content h6 a',
						'builder'   => 'GP_Pro_Entry_Content::link_decorations',
						'selector'  => 'text-decoration'
					),
					'entry-content-h6-link-dec-hov' => array(
						'label'     => __( 'Link Style', 'gppro-entry-content' ),
						'sub'       => __( 'Hover', 'gppro-entry-content' ),
						'input'     => 'text-decoration',
						'target'    => array( '.entry-content h6 a:hover', '.entry-content h6 a:focus' ),
						'builder'   => 'GP_Pro_Entry_Content::link_decorations',
						'selector'  => 'text-decoration'
					),
				),
			),

			'section-break-entry-content-p' => array(
				'break' => array(
					'type'  => 'thin',
					'title' => __( 'Paragraphs', 'gppro-entry-content' ),
				),
			),

			'entry-content-p-color-setup'   => array(
				'title'     => __( 'Colors', 'gppro-entry-content' ),
				'data'      => array(
					'entry-content-p-color-text'    => array(
						'label'     => __( 'Base Color', 'gppro-entry-content' ),
						'input'     => 'color',
						'target'    => '.entry-content p',
						'builder'   => 'GP_Pro_Builder::hexcolor_css',
						'selector'  => 'color'
					),
					'entry-content-p-color-link'    => array(
						'label'     => __( 'Link Color', 'gppro-entry-content' ),
						'sub'       => __( 'Base', 'gppro-entry-content' ),
						'input'     => 'color',
						'target'    => '.entry-content p a',
						'builder'   => 'GP_Pro_Builder::hexcolor_css',
						'selector'  => 'color'
					),
					'entry-content-p-color-link-hov'    => array(
						'label'     => __( 'Link Color', 'gppro-entry-content' ),
						'sub'       => __( 'Hover', 'gppro-entry-content' ),
						'input'     => 'color',
						'target'    => array( '.entry-content p a:hover', '.entry-content p a:focus' ),
						'builder'   => 'GP_Pro_Builder::hexcolor_css',
						'selector'  => 'color'
					),
				),
			),

			'entry-content-p-type-setup'    => array(
				'title'     => __( 'Typography', 'gppro-entry-content' ),
				'data'      => array(
					'entry-content-p-stack' => array(
						'label'     => __( 'Font Stack', 'gppro-entry-content' ),
						'input'     => 'font-stack',
						'target'    => '.entry-content p',
						'builder'   => 'GP_Pro_Builder::stack_css',
						'selector'  => 'font-family'
					),
					'entry-content-p-size'  => array(
						'label'     => __( 'Font Size', 'gppro-entry-content' ),
						'sub'       => __( 'px', 'gppro' ),
						'input'     => 'font-size',
						'scale'     => 'text',
						'target'    => '.entry-content p',
						'builder'   => 'GP_Pro_Builder::px_css',
						'selector'  => 'font-size',
					),
					'entry-content-p-weight'    => array(
						'label'     => __( 'Font Weight', 'gppro-entry-content' ),
						'input'     => 'font-weight',
						'target'    => '.entry-content p',
						'builder'   => 'GP_Pro_Builder::number_css',
						'selector'  => 'font-weight',
						'tip'       => __( 'Certain fonts will not display every weight.', 'gppro-entry-content' )
					),
				),
			),

			'entry-content-p-appearance-setup'  => array(
				'title'     => __( 'Text Appearance', 'gppro-entry-content' ),
				'data'      => array(
					'entry-content-p-margin-bottom' => array(
						'label'     => __( 'Margin Bottom', 'gppro-entry-content' ),
						'input'     => 'spacing',
						'target'    => '.entry-content p',
						'builder'   => 'GP_Pro_Builder::px_css',
						'selector'  => 'margin-bottom',
						'min'       => '0',
						'max'       => '60',
						'step'      => '1'
					),
					'entry-content-p-padding-bottom'    => array(
						'label'     => __( 'Padding Bottom', 'gppro-entry-content' ),
						'input'     => 'spacing',
						'target'    => '.entry-content p',
						'builder'   => 'GP_Pro_Builder::px_css',
						'selector'  => 'padding-bottom',
						'min'       => '0',
						'max'       => '60',
						'step'      => '1'
					),
					'entry-content-p-transform' => array(
						'label'     => __( 'Text Appearance', 'gppro-entry-content' ),
						'input'     => 'text-transform',
						'target'    => '.entry-content p',
						'builder'   => 'GP_Pro_Builder::text_css',
						'selector'  => 'text-transform'
					),
					'entry-content-p-align' => array(
						'label'     => __( 'Text Alignment', 'gppro-entry-content' ),
						'input'     => 'text-align',
						'target'    => '.entry-content p',
						'builder'   => 'GP_Pro_Builder::text_css',
						'selector'  => 'text-align'
					),
					'entry-content-p-link-dec' => array(
						'label'     => __( 'Link Style', 'gppro-entry-content' ),
						'sub'       => __( 'Base', 'gppro-entry-content' ),
						'input'     => 'text-decoration',
						'target'    => '.entry-content p a',
						'builder'   => 'GP_Pro_Entry_Content::link_decorations',
						'selector'  => 'text-decoration'
					),
					'entry-content-p-link-dec-hov' => array(
						'label'     => __( 'Link Style', 'gppro-entry-content' ),
						'sub'       => __( 'Hover', 'gppro-entry-content' ),
						'input'     => 'text-decoration',
						'target'    => array( '.entry-content p a:hover', '.entry-content p a:focus' ),
						'builder'   => 'GP_Pro_Entry_Content::link_decorations',
						'selector'  => 'text-decoration'
					),
				),
			),


			'section-break-entry-content-ul'    => array(
				'break' => array(
					'type'  => 'thin',
					'title' => __( 'Unordered Lists (<ul>)', 'gppro-entry-content' ),
				),
			),

			'entry-content-ul-color-setup'  => array(
				'title'     => __( 'Colors', 'gppro-entry-content' ),
				'data'      => array(
					'entry-content-ul-color-text'   => array(
						'label'     => __( 'Base Color', 'gppro-entry-content' ),
						'input'     => 'color',
						'target'    => '.entry-content ul',
						'builder'   => 'GP_Pro_Builder::hexcolor_css',
						'selector'  => 'color'
					),
					'entry-content-ul-color-link'   => array(
						'label'     => __( 'Link Color', 'gppro-entry-content' ),
						'sub'       => __( 'Base', 'gppro-entry-content' ),
						'input'     => 'color',
						'target'    => '.entry-content ul a',
						'builder'   => 'GP_Pro_Builder::hexcolor_css',
						'selector'  => 'color'
					),
					'entry-content-ul-color-link-hov'   => array(
						'label'     => __( 'Link Color', 'gppro-entry-content' ),
						'sub'       => __( 'Hover', 'gppro-entry-content' ),
						'input'     => 'color',
						'target'    => array( '.entry-content ul a:hover', '.entry-content ul a:focus' ),
						'builder'   => 'GP_Pro_Builder::hexcolor_css',
						'selector'  => 'color'
					),
				),
			),

			'entry-content-ul-type-setup'   => array(
				'title'     => __( 'Typography', 'gppro-entry-content' ),
				'data'      => array(
					'entry-content-ul-stack'    => array(
						'label'     => __( 'Font Stack', 'gppro-entry-content' ),
						'input'     => 'font-stack',
						'target'    => '.entry-content ul',
						'builder'   => 'GP_Pro_Builder::stack_css',
						'selector'  => 'font-family'
					),
					'entry-content-ul-size' => array(
						'label'     => __( 'Font Size', 'gppro-entry-content' ),
						'sub'       => __( 'px', 'gppro' ),
						'input'     => 'font-size',
						'scale'     => 'text',
						'target'    => '.entry-content ul',
						'builder'   => 'GP_Pro_Builder::px_css',
						'selector'  => 'font-size',
					),
					'entry-content-ul-weight'   => array(
						'label'     => __( 'Font Weight', 'gppro-entry-content' ),
						'input'     => 'font-weight',
						'target'    => '.entry-content ul',
						'builder'   => 'GP_Pro_Builder::number_css',
						'selector'  => 'font-weight',
						'tip'       => __( 'Certain fonts will not display every weight.', 'gppro-entry-content' )
					),
				),
			),

			'entry-content-ul-appearance-setup' => array(
				'title'     => __( 'Text Appearance', 'gppro-entry-content' ),
				'data'      => array(
					'entry-content-ul-margin-left'  => array(
						'label'     => __( 'Margin Left', 'gppro-entry-content' ),
						'input'     => 'spacing',
						'target'    => '.entry-content ul',
						'builder'   => 'GP_Pro_Builder::px_css',
						'selector'  => 'margin-left',
						'min'       => '0',
						'max'       => '60',
						'step'      => '1'
					),
					'entry-content-ul-margin-bottom'    => array(
						'label'     => __( 'Margin Bottom', 'gppro-entry-content' ),
						'input'     => 'spacing',
						'target'    => '.entry-content ul',
						'builder'   => 'GP_Pro_Builder::px_css',
						'selector'  => 'margin-bottom',
						'min'       => '0',
						'max'       => '60',
						'step'      => '1'
					),
					'entry-content-ul-padding-left' => array(
						'label'     => __( 'Padding Left', 'gppro-entry-content' ),
						'input'     => 'spacing',
						'target'    => '.entry-content ul',
						'builder'   => 'GP_Pro_Builder::px_css',
						'selector'  => 'padding-left',
						'min'       => '0',
						'max'       => '60',
						'step'      => '1'
					),
					'entry-content-ul-padding-bottom'   => array(
						'label'     => __( 'Padding Bottom', 'gppro-entry-content' ),
						'input'     => 'spacing',
						'target'    => '.entry-content ul',
						'builder'   => 'GP_Pro_Builder::px_css',
						'selector'  => 'padding-bottom',
						'min'       => '0',
						'max'       => '60',
						'step'      => '1'
					),
					'entry-content-ul-list-style'   => array(
						'label'     => __( 'List Style', 'gppro' ),
						'input'     => 'lists',
						'target'    => '.entry-content ul li',
						'builder'   => 'GP_Pro_Builder::text_css',
						'selector'  => 'list-style-type',
					),
					'entry-content-ul-transform'    => array(
						'label'     => __( 'Text Appearance', 'gppro-entry-content' ),
						'input'     => 'text-transform',
						'target'    => '.entry-content ul',
						'builder'   => 'GP_Pro_Builder::text_css',
						'selector'  => 'text-transform'
					),
					'entry-content-ul-align'    => array(
						'label'     => __( 'Text Alignment', 'gppro-entry-content' ),
						'input'     => 'text-align',
						'target'    => '.entry-content ul',
						'builder'   => 'GP_Pro_Builder::text_css',
						'selector'  => 'text-align'
					),
					'entry-content-ul-link-dec' => array(
						'label'     => __( 'Link Style', 'gppro-entry-content' ),
						'sub'       => __( 'Base', 'gppro-entry-content' ),
						'input'     => 'text-decoration',
						'target'    => '.entry-content ul a',
						'builder'   => 'GP_Pro_Entry_Content::link_decorations',
						'selector'  => 'text-decoration'
					),
					'entry-content-ul-link-dec-hov' => array(
						'label'     => __( 'Link Style', 'gppro-entry-content' ),
						'sub'       => __( 'Hover', 'gppro-entry-content' ),
						'input'     => 'text-decoration',
						'target'    => array( '.entry-content ul a:hover', '.entry-content ul a:focus' ),
						'builder'   => 'GP_Pro_Entry_Content::link_decorations',
						'selector'  => 'text-decoration'
					),
				),
			),

			'section-break-entry-content-ol'    => array(
				'break' => array(
					'type'  => 'thin',
					'title' => __( 'Ordered Lists (<ol>)', 'gppro-entry-content' ),
				),
			),

			'entry-content-ol-color-setup'  => array(
				'title'     => __( 'Colors', 'gppro-entry-content' ),
				'data'      => array(
					'entry-content-ol-color-text'   => array(
						'label'     => __( 'Base Color', 'gppro-entry-content' ),
						'input'     => 'color',
						'target'    => '.entry-content ol',
						'builder'   => 'GP_Pro_Builder::hexcolor_css',
						'selector'  => 'color'
					),
					'entry-content-ol-color-link'   => array(
						'label'     => __( 'Link Color', 'gppro-entry-content' ),
						'sub'       => __( 'Base', 'gppro-entry-content' ),
						'input'     => 'color',
						'target'    => '.entry-content ol a',
						'builder'   => 'GP_Pro_Builder::hexcolor_css',
						'selector'  => 'color'
					),
					'entry-content-ol-color-link-hov'   => array(
						'label'     => __( 'Link Color', 'gppro-entry-content' ),
						'sub'       => __( 'Hover', 'gppro-entry-content' ),
						'input'     => 'color',
						'target'    => array( '.entry-content ol a:hover', '.entry-content ol a:focus' ),
						'builder'   => 'GP_Pro_Builder::hexcolor_css',
						'selector'  => 'color'
					),
				),
			),

			'entry-content-ol-type-setup'   => array(
				'title'     => __( 'Typography', 'gppro-entry-content' ),
				'data'      => array(
					'entry-content-ol-stack'    => array(
						'label'     => __( 'Font Stack', 'gppro-entry-content' ),
						'input'     => 'font-stack',
						'target'    => '.entry-content ol',
						'builder'   => 'GP_Pro_Builder::stack_css',
						'selector'  => 'font-family'
					),
					'entry-content-ol-size' => array(
						'label'     => __( 'Font Size', 'gppro-entry-content' ),
						'sub'       => __( 'px', 'gppro' ),
						'input'     => 'font-size',
						'scale'     => 'text',
						'target'    => '.entry-content ol',
						'builder'   => 'GP_Pro_Builder::px_css',
						'selector'  => 'font-size',
					),
					'entry-content-ol-weight'   => array(
						'label'     => __( 'Font Weight', 'gppro-entry-content' ),
						'input'     => 'font-weight',
						'target'    => '.entry-content ol',
						'builder'   => 'GP_Pro_Builder::number_css',
						'selector'  => 'font-weight',
						'tip'       => __( 'Certain fonts will not display every weight.', 'gppro-entry-content' )
					),
				),
			),

			'entry-content-ol-appearance-setup' => array(
				'title'     => __( 'Text Appearance', 'gppro-entry-content' ),
				'data'      => array(
					'entry-content-ol-margin-left'  => array(
						'label'     => __( 'Margin Left', 'gppro-entry-content' ),
						'input'     => 'spacing',
						'target'    => '.entry-content ol',
						'builder'   => 'GP_Pro_Builder::px_css',
						'selector'  => 'margin-left',
						'min'       => '0',
						'max'       => '60',
						'step'      => '1'
					),
					'entry-content-ol-margin-bottom'    => array(
						'label'     => __( 'Margin Bottom', 'gppro-entry-content' ),
						'input'     => 'spacing',
						'target'    => '.entry-content ol',
						'builder'   => 'GP_Pro_Builder::px_css',
						'selector'  => 'margin-bottom',
						'min'       => '0',
						'max'       => '60',
						'step'      => '1'
					),
					'entry-content-ol-padding-left' => array(
						'label'     => __( 'Padding Left', 'gppro-entry-content' ),
						'input'     => 'spacing',
						'target'    => '.entry-content ol',
						'builder'   => 'GP_Pro_Builder::px_css',
						'selector'  => 'padding-left',
						'min'       => '0',
						'max'       => '60',
						'step'      => '1'
					),
					'entry-content-ol-padding-bottom'   => array(
						'label'     => __( 'Padding Bottom', 'gppro-entry-content' ),
						'input'     => 'spacing',
						'target'    => '.entry-content ol',
						'builder'   => 'GP_Pro_Builder::px_css',
						'selector'  => 'padding-bottom',
						'min'       => '0',
						'max'       => '60',
						'step'      => '1'
					),
					'entry-content-ol-list-style'   => array(
						'label'     => __( 'List Style', 'gppro' ),
						'input'     => 'lists',
						'target'    => '.entry-content ol li',
						'builder'   => 'GP_Pro_Builder::text_css',
						'selector'  => 'list-style-type',
					),
					'entry-content-ol-transform'    => array(
						'label'     => __( 'Text Appearance', 'gppro-entry-content' ),
						'input'     => 'text-transform',
						'target'    => '.entry-content ol',
						'builder'   => 'GP_Pro_Builder::text_css',
						'selector'  => 'text-transform'
					),
					'entry-content-ol-align'    => array(
						'label'     => __( 'Text Alignment', 'gppro-entry-content' ),
						'input'     => 'text-align',
						'target'    => '.entry-content ol',
						'builder'   => 'GP_Pro_Builder::text_css',
						'selector'  => 'text-align'
					),
					'entry-content-ol-link-dec' => array(
						'label'     => __( 'Link Style', 'gppro-entry-content' ),
						'sub'       => __( 'Base', 'gppro-entry-content' ),
						'input'     => 'text-decoration',
						'target'    => '.entry-content ol a',
						'builder'   => 'GP_Pro_Entry_Content::link_decorations',
						'selector'  => 'text-decoration'
					),
					'entry-content-ol-link-dec-hov' => array(
						'label'     => __( 'Link Style', 'gppro-entry-content' ),
						'sub'       => __( 'Hover', 'gppro-entry-content' ),
						'input'     => 'text-decoration',
						'target'    => array( '.entry-content ol a:hover', '.entry-content ol a:focus' ),
						'builder'   => 'GP_Pro_Entry_Content::link_decorations',
						'selector'  => 'text-decoration'
					),
				),
			),

			'section-break-entry-content-cap'   => array(
				'break' => array(
					'type'  => 'thin',
					'title' => __( 'Image Captions', 'gppro-entry-content' ),
				),
			),

			'entry-content-cap-color-setup' => array(
				'title'     => __( 'Colors', 'gppro-entry-content' ),
				'data'      => array(
					'entry-content-cap-color-text'  => array(
						'label'     => __( 'Base Color', 'gppro-entry-content' ),
						'input'     => 'color',
						'target'    => '.entry-content .wp-caption-text',
						'builder'   => 'GP_Pro_Builder::hexcolor_css',
						'selector'  => 'color'
					),
					'entry-content-cap-color-link'  => array(
						'label'     => __( 'Link Color', 'gppro-entry-content' ),
						'sub'       => __( 'Base', 'gppro-entry-content' ),
						'input'     => 'color',
						'target'    => '.entry-content .wp-caption-text a',
						'builder'   => 'GP_Pro_Builder::hexcolor_css',
						'selector'  => 'color'
					),
					'entry-content-cap-color-link-hov'  => array(
						'label'     => __( 'Link Color', 'gppro-entry-content' ),
						'sub'       => __( 'Hover', 'gppro-entry-content' ),
						'input'     => 'color',
						'target'    => array( '.entry-content .wp-caption-text a:hover', '.entry-content .wp-caption-text a:focus' ),
						'builder'   => 'GP_Pro_Builder::hexcolor_css',
						'selector'  => 'color'
					),
				),
			),

			'entry-content-cap-type-setup'  => array(
				'title'     => __( 'Typography', 'gppro-entry-content' ),
				'data'      => array(
					'entry-content-cap-stack'   => array(
						'label'     => __( 'Font Stack', 'gppro-entry-content' ),
						'input'     => 'font-stack',
						'target'    => '.entry-content .wp-caption-text',
						'builder'   => 'GP_Pro_Builder::stack_css',
						'selector'  => 'font-family'
					),
					'entry-content-cap-size'    => array(
						'label'     => __( 'Font Size', 'gppro-entry-content' ),
						'sub'       => __( 'px', 'gppro' ),
						'input'     => 'font-size',
						'scale'     => 'text',
						'target'    => '.entry-content .wp-caption-text',
						'builder'   => 'GP_Pro_Builder::px_css',
						'selector'  => 'font-size',
					),
					'entry-content-cap-weight'  => array(
						'label'     => __( 'Font Weight', 'gppro-entry-content' ),
						'input'     => 'font-weight',
						'target'    => '.entry-content .wp-caption-text',
						'builder'   => 'GP_Pro_Builder::number_css',
						'selector'  => 'font-weight',
						'tip'       => __( 'Certain fonts will not display every weight.', 'gppro-entry-content' )
					),
				),
			),

			'entry-content-cap-appearance-setup'    => array(
				'title'     => __( 'Text Appearance', 'gppro-entry-content' ),
				'data'      => array(
					'entry-content-cap-margin-bottom'   => array(
						'label'     => __( 'Margin Bottom', 'gppro-entry-content' ),
						'input'     => 'spacing',
						'target'    => '.entry-content .wp-caption-text',
						'builder'   => 'GP_Pro_Builder::px_css',
						'selector'  => 'margin-bottom',
						'min'       => '0',
						'max'       => '60',
						'step'      => '1'
					),
					'entry-content-cap-padding-bottom'  => array(
						'label'     => __( 'Padding Bottom', 'gppro-entry-content' ),
						'input'     => 'spacing',
						'target'    => '.entry-content .wp-caption-text',
						'builder'   => 'GP_Pro_Builder::px_css',
						'selector'  => 'padding-bottom',
						'min'       => '0',
						'max'       => '60',
						'step'      => '1'
					),
					'entry-content-cap-transform'   => array(
						'label'     => __( 'Text Appearance', 'gppro-entry-content' ),
						'input'     => 'text-transform',
						'target'    => '.entry-content .wp-caption-text',
						'builder'   => 'GP_Pro_Builder::text_css',
						'selector'  => 'text-transform'
					),
					'entry-content-cap-align'   => array(
						'label'     => __( 'Text Alignment', 'gppro-entry-content' ),
						'input'     => 'text-align',
						'target'    => '.entry-content .wp-caption-text',
						'builder'   => 'GP_Pro_Builder::text_css',
						'selector'  => 'text-align'
					),
					'entry-content-cap-link-dec' => array(
						'label'     => __( 'Link Style', 'gppro-entry-content' ),
						'sub'       => __( 'Base', 'gppro-entry-content' ),
						'input'     => 'text-decoration',
						'target'    => '.entry-content .wp-caption-text a',
						'builder'   => 'GP_Pro_Entry_Content::link_decorations',
						'selector'  => 'text-decoration'
					),
					'entry-content-cap-link-dec-hov' => array(
						'label'     => __( 'Link Style', 'gppro-entry-content' ),
						'sub'       => __( 'Hover', 'gppro-entry-content' ),
						'input'     => 'text-decoration',
						'target'    => '.entry-content .wp-caption-text a:hover',
						'builder'   => 'GP_Pro_Entry_Content::link_decorations',
						'selector'  => 'text-decoration'
					),
				),
			),

			'section-break-entry-content-code'  => array(
				'break' => array(
					'type'  => 'thin',
					'title' => __( 'Code Blocks', 'gppro-entry-content' ),
				),
			),

			'entry-content-code-color-setup'    => array(
				'title'     => __( 'Colors', 'gppro-entry-content' ),
				'data'      => array(
					'entry-content-code-background' => array(
						'label'     => __( 'Background Color', 'gppro-entry-content' ),
						'input'     => 'color',
						'target'    => '.entry-content code',
						'builder'   => 'GP_Pro_Builder::hexcolor_css',
						'selector'  => 'background-color'
					),
					'entry-content-code-color-text' => array(
						'label'     => __( 'Base Color', 'gppro-entry-content' ),
						'input'     => 'color',
						'target'    => '.entry-content code',
						'builder'   => 'GP_Pro_Builder::hexcolor_css',
						'selector'  => 'color'
					),
				),
			),

			'entry-content-code-type-setup' => array(
				'title'     => __( 'Typography', 'gppro-entry-content' ),
				'data'      => array(
					'entry-content-code-stack'  => array(
						'label'     => __( 'Font Stack', 'gppro-entry-content' ),
						'input'     => 'font-stack',
						'target'    => '.entry-content code',
						'builder'   => 'GP_Pro_Builder::stack_css',
						'selector'  => 'font-family'
					),
					'entry-content-code-size'   => array(
						'label'     => __( 'Font Size', 'gppro-entry-content' ),
						'sub'       => __( 'px', 'gppro' ),
						'input'     => 'font-size',
						'scale'     => 'text',
						'target'    => '.entry-content code',
						'builder'   => 'GP_Pro_Builder::px_css',
						'selector'  => 'font-size',
					),
					'entry-content-code-weight' => array(
						'label'     => __( 'Font Weight', 'gppro-entry-content' ),
						'input'     => 'font-weight',
						'target'    => '.entry-content code',
						'builder'   => 'GP_Pro_Builder::number_css',
						'selector'  => 'font-weight',
						'tip'       => __( 'Certain fonts will not display every weight.', 'gppro-entry-content' )
					),
				),
			),

		); // end section

		// check for inline add-ons
		$sections['entry_content']  = apply_filters( 'gppro_section_inline_entry_content', $sections['entry_content'], $class );

		// return the section
		return $sections;
	}

	/**
	 * Custom builder for link decoration styles
	 *
	 * Remove bottom borders when using text-decoration setting
	 *
	 * @since 1.0.1
	 *
	 * @param  string  $selector
	 * @param  mixed  $value
	 * @return string
	 */
	public static function link_decorations( $selector, $value ) {

		// set the empty
		$css = '';

		// switch our selector
		switch ( $selector ) {

			case 'text-decoration':

				$css .= GP_Pro_Builder::text_css( 'text-decoration', $value );
				$css .= GP_Pro_Builder::text_css( 'border-bottom-style', 'none' );

				break;
			default:

				$css .= '';
				break;
		}

		// return the CSS to the builder
		return $css;
	}

/// end class
}

// Instantiate our class
$GP_Pro_Entry_Content = GP_Pro_Entry_Content::getInstance();

