<?php
/*
Plugin Name: Genesis Design Palette Pro - Entry Content
Plugin URI: https://genesisdesignpro.com/
Description: Fine tune the look of the content inside posts and pages in Genesis Design Palette Pro
Author: Reaktiv Studios
Version: 1.0.0
Requires at least: 3.7
Author URI: http://andrewnorcross.com
*/
/*  Copyright 2014 Andrew Norcross

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

if( !defined( 'GPECN_BASE' ) )
	define( 'GPECN_BASE', plugin_basename(__FILE__) );

if( !defined( 'GPECN_DIR' ) )
	define( 'GPECN_DIR', dirname( __FILE__ ) );

if( !defined( 'GPECN_VER' ) )
	define( 'GPECN_VER', '1.0.0' );


class GP_Pro_Entry_Content
{

	/**
	 * Static property to hold our singleton instance
	 * @var GP_Pro_Post_Content
	 */
	static $instance = false;

	/**
	 * This is our constructor
	 *
	 * @return GP_Pro_Post_Content
	 */
	private function __construct() {

		// general backend
		add_action		(	'plugins_loaded',						array(	$this,	'textdomain'					)			);
		add_action		(	'admin_notices',						array(	$this,	'gppro_active_check'			),	10		);

		// GP Pro specific
		add_filter		(	'gppro_set_defaults',					array(	$this,	'entry_defaults_base'			),	30		);
		add_filter		(	'gppro_admin_block_add',				array(	$this,	'entry_content_block'			),	35		);
		add_filter		(	'gppro_section_inline_post_content',	array(	$this,	'entry_inline_post_content'		),	15,	2	);
		add_filter		(	'gppro_sections',						array(	$this,	'entry_content_sections'		),	10,	2	);
		add_filter		(	'gppro_css_inline_post_content',		array(	$this,	'entry_css_post_content'		),	15,	3	);
	}

	/**
	 * If an instance exists, this returns it.  If not, it creates one and
	 * retuns it.
	 *
	 * @return GP_Pro_Post_Content
	 */

	public static function getInstance() {
		if ( !self::$instance )
			self::$instance = new self;
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
	 * @return GP_Pro_Post_Content
	 */

	public function gppro_active_check() {

		$screen = get_current_screen();

		if ( $screen->parent_file !== 'plugins.php' )
			return;

		// look for our flag
		$coreactive	= get_option( 'gppro_core_active' );

		// not active. show message
		if ( ! $coreactive ) :

			echo '<div id="message" class="error fade below-h2"><p><strong>'.__( 'This plugin requires Genesis Design Palette Pro to function and cannot be activated.', 'gppro-entry-content' ).'</strong></p></div>';

			// hide activation method
			unset( $_GET['activate'] );

			// deactivate YOURSELF
			deactivate_plugins( plugin_basename( __FILE__ ) );

		endif;

		return;

	}


	/**
	 * swap default values
	 *
	 * @return string $defaults
	 */

	static function entry_defaults_base( $defaults ) {

		// fetch the existing defaults
		$title_base			= isset( $defaults[ 'post-title-text' ] )		? $defaults[ 'post-title-text' ]		: '';
		$title_stack		= isset( $defaults[ 'post-title-stack' ] )		? $defaults[ 'post-title-stack' ]		: '';

		$text_base			= isset( $defaults[ 'post-entry-text' ] )		? $defaults[ 'post-entry-text' ]		: '';
		$text_link			= isset( $defaults[ 'post-entry-link' ] ) 		? $defaults[ 'post-entry-link' ]		: '';
		$text_link_hov		= isset( $defaults[ 'post-entry-link-hov' ] )	? $defaults[ 'post-entry-link-hov' ]	: '';
		$text_stack			= isset( $defaults[ 'post-entry-stack' ] )		? $defaults[ 'post-entry-stack' ]		: '';
		$text_size			= isset( $defaults[ 'post-entry-size' ] )		? $defaults[ 'post-entry-size' ]		: '';
		$text_weight		= isset( $defaults[ 'post-entry-weight' ] )		? $defaults[ 'post-entry-weight' ]		: '';

		$list_ul_style		= isset( $defaults[ 'post-entry-list-ul' ] )	? $defaults[ 'post-entry-list-ul' ]		: '';
		$list_ol_style		= isset( $defaults[ 'post-entry-list-ol' ] )	? $defaults[ 'post-entry-list-ol' ]		: '';

		$cap_base			= isset( $defaults[ 'post-entry-caption-text' ] )		? $defaults[ 'post-entry-caption-text' ]		: '';
		$cap_link			= isset( $defaults[ 'post-entry-caption-link' ] ) 		? $defaults[ 'post-entry-caption-link' ]		: '';
		$cap_link_hov		= isset( $defaults[ 'post-entry-caption-link-hov' ] )	? $defaults[ 'post-entry-caption-link-hov' ]	: '';

		// general body
		$changes	= array(
			'entry-content-h1-color-text'		=> $title_base,
			'entry-content-h1-color-link'		=> $text_link,
			'entry-content-h1-color-link-hov'	=> $text_link_hov,
			'entry-content-h1-stack'			=> $title_stack,
			'entry-content-h1-size'				=> '36',
			'entry-content-h1-weight'			=> '700',
			'entry-content-h1-margin-bottom'	=> '16',
			'entry-content-h1-padding-bottom'	=> '0',
			'entry-content-h1-transform'		=> 'none',
			'entry-content-h1-align'			=> 'left',
			'entry-content-h1-link-dec'			=> 'underline',
			'entry-content-h1-link-dec-hov'		=> 'underline',

			'entry-content-h2-color-text'		=> $title_base,
			'entry-content-h2-color-link'		=> $text_link,
			'entry-content-h2-color-link-hov'	=> $text_link_hov,
			'entry-content-h2-stack'			=> $title_stack,
			'entry-content-h2-size'				=> '30',
			'entry-content-h2-weight'			=> '700',
			'entry-content-h2-margin-bottom'	=> '16',
			'entry-content-h2-padding-bottom'	=> '0',
			'entry-content-h2-transform'		=> 'none',
			'entry-content-h2-align'			=> 'left',
			'entry-content-h2-link-dec'			=> 'underline',
			'entry-content-h2-link-dec-hov'		=> 'underline',

			'entry-content-h3-color-text'		=> $title_base,
			'entry-content-h3-color-link'		=> $text_link,
			'entry-content-h3-color-link-hov'	=> $text_link_hov,
			'entry-content-h3-stack'			=> $title_stack,
			'entry-content-h3-size'				=> '24',
			'entry-content-h3-weight'			=> '700',
			'entry-content-h3-margin-bottom'	=> '16',
			'entry-content-h3-padding-bottom'	=> '0',
			'entry-content-h3-transform'		=> 'none',
			'entry-content-h3-align'			=> 'left',
			'entry-content-h3-link-dec'			=> 'underline',
			'entry-content-h3-link-dec-hov'		=> 'underline',

			'entry-content-h4-color-text'		=> $title_base,
			'entry-content-h4-color-link'		=> $text_link,
			'entry-content-h4-color-link-hov'	=> $text_link_hov,
			'entry-content-h4-stack'			=> $title_stack,
			'entry-content-h4-size'				=> '20',
			'entry-content-h4-weight'			=> '700',
			'entry-content-h4-margin-bottom'	=> '16',
			'entry-content-h4-padding-bottom'	=> '0',
			'entry-content-h4-transform'		=> 'none',
			'entry-content-h4-align'			=> 'left',
			'entry-content-h4-link-dec'			=> 'underline',
			'entry-content-h4-link-dec-hov'		=> 'underline',

			'entry-content-h5-color-text'		=> $title_base,
			'entry-content-h5-color-link'		=> $text_link,
			'entry-content-h5-color-link-hov'	=> $text_link_hov,
			'entry-content-h5-stack'			=> $title_stack,
			'entry-content-h5-size'				=> '18',
			'entry-content-h5-weight'			=> '700',
			'entry-content-h5-margin-bottom'	=> '16',
			'entry-content-h5-padding-bottom'	=> '0',
			'entry-content-h5-transform'		=> 'none',
			'entry-content-h5-align'			=> 'left',
			'entry-content-h5-link-dec'			=> 'underline',
			'entry-content-h5-link-dec-hov'		=> 'underline',

			'entry-content-h6-color-text'		=> $title_base,
			'entry-content-h6-color-link'		=> $text_link,
			'entry-content-h6-color-link-hov'	=> $text_link_hov,
			'entry-content-h6-stack'			=> $title_stack,
			'entry-content-h6-size'				=> '16',
			'entry-content-h6-weight'			=> '700',
			'entry-content-h6-margin-bottom'	=> '16',
			'entry-content-h6-padding-bottom'	=> '0',
			'entry-content-h6-transform'		=> 'none',
			'entry-content-h6-align'			=> 'left',
			'entry-content-h6-link-dec'			=> 'underline',
			'entry-content-h6-link-dec-hov'		=> 'underline',

			'entry-content-p-color-text'		=> $text_base,
			'entry-content-p-color-link'		=> $text_link,
			'entry-content-p-color-link-hov'	=> $text_link_hov,
			'entry-content-p-stack'				=> $text_stack,
			'entry-content-p-size'				=> $text_size,
			'entry-content-p-weight'			=> $text_weight,
			'entry-content-p-margin-bottom'		=> '26',
			'entry-content-p-padding-bottom'	=> '0',
			'entry-content-p-transform'			=> 'none',
			'entry-content-p-align'				=> 'left',
			'entry-content-p-link-dec'			=> 'underline',
			'entry-content-p-link-dec-hov'		=> 'underline',

			'entry-content-ul-color-text'		=> $text_base,
			'entry-content-ul-color-link'		=> $text_link,
			'entry-content-ul-color-link-hov'	=> $text_link_hov,
			'entry-content-ul-stack'			=> $text_stack,
			'entry-content-ul-size'				=> $text_size,
			'entry-content-ul-weight'			=> $text_weight,
			'entry-content-ul-margin-left'		=> '40',
			'entry-content-ul-margin-bottom'	=> '26',
			'entry-content-ul-padding-left'		=> '0',
			'entry-content-ul-padding-bottom'	=> '0',
			'entry-content-ul-list-style'		=> $list_ul_style,
			'entry-content-ul-transform'		=> 'none',
			'entry-content-ul-align'			=> 'left',
			'entry-content-ul-link-dec'			=> 'underline',
			'entry-content-ul-link-dec-hov'		=> 'underline',

			'entry-content-ol-color-text'		=> $text_base,
			'entry-content-ol-color-link'		=> $text_link,
			'entry-content-ol-color-link-hov'	=> $text_link_hov,
			'entry-content-ol-stack'			=> $text_stack,
			'entry-content-ol-size'				=> $text_size,
			'entry-content-ol-weight'			=> $text_weight,
			'entry-content-ol-margin-left'		=> '40',
			'entry-content-ol-margin-bottom'	=> '26',
			'entry-content-ol-padding-left'		=> '0',
			'entry-content-ol-padding-bottom'	=> '0',
			'entry-content-ol-list-style'		=> $list_ol_style,
			'entry-content-ol-transform'		=> 'none',
			'entry-content-ol-align'			=> 'left',
			'entry-content-ol-link-dec'			=> 'underline',
			'entry-content-ol-link-dec-hov'		=> 'underline',

			'entry-content-cap-color-text'		=> $cap_base,
			'entry-content-cap-color-link'		=> $cap_link,
			'entry-content-cap-color-link-hov'	=> $cap_link_hov,
			'entry-content-cap-stack'			=> $text_stack,
			'entry-content-cap-size'			=> $text_size,
			'entry-content-cap-weight'			=> $text_weight,
			'entry-content-cap-transform'		=> 'none',
			'entry-content-cap-margin-bottom'	=> '26',
			'entry-content-cap-padding-bottom'	=> '0',
			'entry-content-cap-transform'		=> 'none',
			'entry-content-cap-align'			=> 'center',
			'entry-content-cap-link-dec'		=> 'underline',
			'entry-content-cap-link-dec-hov'	=> 'underline',

			'entry-content-code-color-text'		=> '#dddddd',
			'entry-content-code-background'		=> '#333333',
			'entry-content-code-stack'			=> 'monospace',
			'entry-content-code-size'			=> '16',
			'entry-content-code-weight'			=> '400',

		);

		// put into key value pair
		foreach ( $changes as $key => $value ) :
			$defaults[ $key ]	= $value;
		endforeach;

		// send them back
		return $defaults;

	}

	/**
	 * add and filter options in the post content area
	 *
	 * @return array|string $sections
	 */

	static function entry_inline_post_content( $sections, $class ) {

		// remove the default post content settings in favor of our new ones
		unset( $sections['post-entry-color-setup']['title'] );
		unset( $sections['post-entry-type-setup'] );

		// info about new area
		$sections['post-entry-color-setup']['data']	= array(
			'post-entry-plugin-active'	=> array(
				'input'	=> 'description',
				'desc'	=> __( 'You are currently using the Entry Content add on, so all settings are now available there.', 'gppro-entry-content' ),
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
			'tab'		=> __( 'Entry Content', 'gppro-entry-content' ),
			'title'		=> __( 'Entry Content', 'gppro-entry-content' ),
			'intro'		=> __( 'Fine tune the look of the content inside posts and pages.', 'gppro-entry-content' ),
			'slug'		=> 'entry_content',
		);

		return $blocks;

	}

	/**
	 * add section to side
	 *
	 * @return
	 */

	public function entry_content_sections( $sections, $class ) {

		$sections['entry_content']	= array(

			'section-break-entry-content-h1'	=> array(
				'break'	=> array(
					'type'	=> 'thin',
					'title'	=> __( 'H1 Headers', 'gppro-entry-content' ),
				),
			),

			'entry-content-h1-color-setup'	=> array(
				'title'		=> __( 'Colors', 'gppro-entry-content' ),
				'data'		=> array(
					'entry-content-h1-color-text'	=> array(
						'label'		=> __( 'Base Color', 'gppro-entry-content' ),
						'input'		=> 'color',
						'target'	=> $class.' .entry-content h1',
						'selector'	=> 'color'
					),
					'entry-content-h1-color-link'	=> array(
						'label'		=> __( 'Link Color', 'gppro-entry-content' ),
						'sub'		=> __( 'Base', 'gppro-entry-content' ),
						'input'		=> 'color',
						'target'	=> $class.' .entry-content h1 a',
						'selector'	=> 'color'
					),
					'entry-content-h1-color-link-hov'	=> array(
						'label'		=> __( 'Link Color', 'gppro-entry-content' ),
						'sub'		=> __( 'Hover', 'gppro-entry-content' ),
						'input'		=> 'color',
						'target'	=> $class.' .entry-content h1 a:hover',
						'selector'	=> 'color'
					),
				),
			),

			'entry-content-h1-type-setup'	=> array(
				'title'		=> __( 'Typography', 'gppro-entry-content' ),
				'data'		=> array(
					'entry-content-h1-stack'	=> array(
						'label'		=> __( 'Font Stack', 'gppro-entry-content' ),
						'input'		=> 'font-stack',
						'target'	=> $class.' .entry-content h1',
						'selector'	=> 'font-family'
					),
					'entry-content-h1-size'	=> array(
						'label'		=> __( 'Font Size', 'gppro-entry-content' ),
						'input'		=> 'font-size',
						'scale'		=> 'title',
						'target'	=> $class.' .entry-content h1',
						'selector'	=> 'font-size',
					),
					'entry-content-h1-weight'	=> array(
						'label'		=> __( 'Font Weight', 'gppro-entry-content' ),
						'input'		=> 'font-weight',
						'target'	=> $class.' .entry-content h1',
						'selector'	=> 'font-weight',
						'tip'		=> __( 'Certain fonts will not display every weight.', 'gppro-entry-content' )
					),
				),
			),

			'entry-content-h1-appearance-setup'	=> array(
				'title'		=> __( 'Text Appearance', 'gppro-entry-content' ),
				'data'		=> array(
					'entry-content-h1-margin-bottom'	=> array(
						'label'		=> __( 'Margin Bottom', 'gppro-entry-content' ),
						'input'		=> 'spacing',
						'target'	=> $class.' .entry-content h1',
						'selector'	=> 'margin-bottom',
						'min'		=> '0',
						'max'		=> '60',
						'step'		=> '1'
					),
					'entry-content-h1-padding-bottom'	=> array(
						'label'		=> __( 'Padding Bottom', 'gppro-entry-content' ),
						'input'		=> 'spacing',
						'target'	=> $class.' .entry-content h1',
						'selector'	=> 'padding-bottom',
						'min'		=> '0',
						'max'		=> '60',
						'step'		=> '1'
					),
					'entry-content-h1-transform'	=> array(
						'label'		=> __( 'Text Appearance', 'gppro-entry-content' ),
						'input'		=> 'text-transform',
						'target'	=> $class.' .entry-content h1',
						'selector'	=> 'text-transform'
					),
					'entry-content-h1-align'	=> array(
						'label'		=> __( 'Text Alignment', 'gppro-entry-content' ),
						'input'		=> 'text-align',
						'target'	=> $class.' .entry-content h1',
						'selector'	=> 'text-align'
					),
					'entry-content-h1-link-dec' => array(
						'label'		=> __( 'Link Style', 'gppro-entry-content' ),
						'sub'		=> __( 'Base', 'gppro-entry-content' ),
						'input'		=> 'text-decoration',
						'target'	=> $class.' .entry-content h1 a',
						'selector'	=> 'text-decoration'
					),
					'entry-content-h1-link-dec-hov' => array(
						'label'		=> __( 'Link Style', 'gppro-entry-content' ),
						'sub'		=> __( 'Hover', 'gppro-entry-content' ),
						'input'		=> 'text-decoration',
						'target'	=> $class.' .entry-content h1 a:hover',
						'selector'	=> 'text-decoration'
					),
				),
			),

			'section-break-entry-content-h2'	=> array(
				'break'	=> array(
					'type'	=> 'thin',
					'title'	=> __( 'H2 Headers', 'gppro-entry-content' ),
				),
			),

			'entry-content-h2-color-setup'	=> array(
				'title'		=> __( 'Colors', 'gppro-entry-content' ),
				'data'		=> array(
					'entry-content-h2-color-text'	=> array(
						'label'		=> __( 'Base Color', 'gppro-entry-content' ),
						'input'		=> 'color',
						'target'	=> $class.' .entry-content h2',
						'selector'	=> 'color'
					),
					'entry-content-h2-color-link'	=> array(
						'label'		=> __( 'Link Color', 'gppro-entry-content' ),
						'sub'		=> __( 'Base', 'gppro-entry-content' ),
						'input'		=> 'color',
						'target'	=> $class.' .entry-content h2 a',
						'selector'	=> 'color'
					),
					'entry-content-h2-color-link-hov'	=> array(
						'label'		=> __( 'Link Color', 'gppro-entry-content' ),
						'sub'		=> __( 'Hover', 'gppro-entry-content' ),
						'input'		=> 'color',
						'target'	=> $class.' .entry-content h2 a:hover',
						'selector'	=> 'color'
					),
				),
			),

			'entry-content-h2-type-setup'	=> array(
				'title'		=> __( 'Typography', 'gppro-entry-content' ),
				'data'		=> array(
					'entry-content-h2-stack'	=> array(
						'label'		=> __( 'Font Stack', 'gppro-entry-content' ),
						'input'		=> 'font-stack',
						'target'	=> $class.' .entry-content h2',
						'selector'	=> 'font-family'
					),
					'entry-content-h2-size'	=> array(
						'label'		=> __( 'Font Size', 'gppro-entry-content' ),
						'input'		=> 'font-size',
						'scale'		=> 'title',
						'target'	=> $class.' .entry-content h2',
						'selector'	=> 'font-size',
					),
					'entry-content-h2-weight'	=> array(
						'label'		=> __( 'Font Weight', 'gppro-entry-content' ),
						'input'		=> 'font-weight',
						'target'	=> $class.' .entry-content h2',
						'selector'	=> 'font-weight',
						'tip'		=> __( 'Certain fonts will not display every weight.', 'gppro-entry-content' )
					),
				),
			),

			'entry-content-h2-appearance-setup'	=> array(
				'title'		=> __( 'Text Appearance', 'gppro-entry-content' ),
				'data'		=> array(
					'entry-content-h2-margin-bottom'	=> array(
						'label'		=> __( 'Margin Bottom', 'gppro-entry-content' ),
						'input'		=> 'spacing',
						'target'	=> $class.' .entry-content h2',
						'selector'	=> 'margin-bottom',
						'min'		=> '0',
						'max'		=> '60',
						'step'		=> '1'
					),
					'entry-content-h2-padding-bottom'	=> array(
						'label'		=> __( 'Padding Bottom', 'gppro-entry-content' ),
						'input'		=> 'spacing',
						'target'	=> $class.' .entry-content h2',
						'selector'	=> 'padding-bottom',
						'min'		=> '0',
						'max'		=> '60',
						'step'		=> '1'
					),
					'entry-content-h2-transform'	=> array(
						'label'		=> __( 'Text Appearance', 'gppro-entry-content' ),
						'input'		=> 'text-transform',
						'target'	=> $class.' .entry-content h2',
						'selector'	=> 'text-transform'
					),
					'entry-content-h2-align'	=> array(
						'label'		=> __( 'Text Alignment', 'gppro-entry-content' ),
						'input'		=> 'text-align',
						'target'	=> $class.' .entry-content h2',
						'selector'	=> 'text-align'
					),
					'entry-content-h2-link-dec' => array(
						'label'		=> __( 'Link Style', 'gppro-entry-content' ),
						'sub'		=> __( 'Base', 'gppro-entry-content' ),
						'input'		=> 'text-decoration',
						'target'	=> $class.' .entry-content h2 a',
						'selector'	=> 'text-decoration'
					),
					'entry-content-h2-link-dec-hov' => array(
						'label'		=> __( 'Link Style', 'gppro-entry-content' ),
						'sub'		=> __( 'Hover', 'gppro-entry-content' ),
						'input'		=> 'text-decoration',
						'target'	=> $class.' .entry-content h2 a:hover',
						'selector'	=> 'text-decoration'
					),
				),
			),

			'section-break-entry-content-h3'	=> array(
				'break'	=> array(
					'type'	=> 'thin',
					'title'	=> __( 'H3 Headers', 'gppro-entry-content' ),
				),
			),

			'entry-content-h3-color-setup'	=> array(
				'title'		=> __( 'Colors', 'gppro-entry-content' ),
				'data'		=> array(
					'entry-content-h3-color-text'	=> array(
						'label'		=> __( 'Base Color', 'gppro-entry-content' ),
						'input'		=> 'color',
						'target'	=> $class.' .entry-content h3',
						'selector'	=> 'color'
					),
					'entry-content-h3-color-link'	=> array(
						'label'		=> __( 'Link Color', 'gppro-entry-content' ),
						'sub'		=> __( 'Base', 'gppro-entry-content' ),
						'input'		=> 'color',
						'target'	=> $class.' .entry-content h3 a',
						'selector'	=> 'color'
					),
					'entry-content-h3-color-link-hov'	=> array(
						'label'		=> __( 'Link Color', 'gppro-entry-content' ),
						'sub'		=> __( 'Hover', 'gppro-entry-content' ),
						'input'		=> 'color',
						'target'	=> $class.' .entry-content h3 a:hover',
						'selector'	=> 'color'
					),
				),
			),

			'entry-content-h3-type-setup'	=> array(
				'title'		=> __( 'Typography', 'gppro-entry-content' ),
				'data'		=> array(
					'entry-content-h3-stack'	=> array(
						'label'		=> __( 'Font Stack', 'gppro-entry-content' ),
						'input'		=> 'font-stack',
						'target'	=> $class.' .entry-content h3',
						'selector'	=> 'font-family'
					),
					'entry-content-h3-size'	=> array(
						'label'		=> __( 'Font Size', 'gppro-entry-content' ),
						'input'		=> 'font-size',
						'scale'		=> 'title',
						'target'	=> $class.' .entry-content h3',
						'selector'	=> 'font-size',
					),
					'entry-content-h3-weight'	=> array(
						'label'		=> __( 'Font Weight', 'gppro-entry-content' ),
						'input'		=> 'font-weight',
						'target'	=> $class.' .entry-content h3',
						'selector'	=> 'font-weight',
						'tip'		=> __( 'Certain fonts will not display every weight.', 'gppro-entry-content' )
					),
				),
			),

			'entry-content-h3-appearance-setup'	=> array(
				'title'		=> __( 'Text Appearance', 'gppro-entry-content' ),
				'data'		=> array(
					'entry-content-h3-margin-bottom'	=> array(
						'label'		=> __( 'Margin Bottom', 'gppro-entry-content' ),
						'input'		=> 'spacing',
						'target'	=> $class.' .entry-content h3',
						'selector'	=> 'margin-bottom',
						'min'		=> '0',
						'max'		=> '60',
						'step'		=> '1'
					),
					'entry-content-h3-padding-bottom'	=> array(
						'label'		=> __( 'Padding Bottom', 'gppro-entry-content' ),
						'input'		=> 'spacing',
						'target'	=> $class.' .entry-content h3',
						'selector'	=> 'padding-bottom',
						'min'		=> '0',
						'max'		=> '60',
						'step'		=> '1'
					),
					'entry-content-h3-transform'	=> array(
						'label'		=> __( 'Text Appearance', 'gppro-entry-content' ),
						'input'		=> 'text-transform',
						'target'	=> $class.' .entry-content h3',
						'selector'	=> 'text-transform'
					),
					'entry-content-h3-align'	=> array(
						'label'		=> __( 'Text Alignment', 'gppro-entry-content' ),
						'input'		=> 'text-align',
						'target'	=> $class.' .entry-content h3',
						'selector'	=> 'text-align'
					),
					'entry-content-h3-link-dec' => array(
						'label'		=> __( 'Link Style', 'gppro-entry-content' ),
						'sub'		=> __( 'Base', 'gppro-entry-content' ),
						'input'		=> 'text-decoration',
						'target'	=> $class.' .entry-content h3 a',
						'selector'	=> 'text-decoration'
					),
					'entry-content-h3-link-dec-hov' => array(
						'label'		=> __( 'Link Style', 'gppro-entry-content' ),
						'sub'		=> __( 'Hover', 'gppro-entry-content' ),
						'input'		=> 'text-decoration',
						'target'	=> $class.' .entry-content h3 a:hover',
						'selector'	=> 'text-decoration'
					),
				),
			),

			'section-break-entry-content-h4'	=> array(
				'break'	=> array(
					'type'	=> 'thin',
					'title'	=> __( 'H4 Headers', 'gppro-entry-content' ),
				),
			),

			'entry-content-h4-color-setup'	=> array(
				'title'		=> __( 'Colors', 'gppro-entry-content' ),
				'data'		=> array(
					'entry-content-h4-color-text'	=> array(
						'label'		=> __( 'Base Color', 'gppro-entry-content' ),
						'input'		=> 'color',
						'target'	=> $class.' .entry-content h4',
						'selector'	=> 'color'
					),
					'entry-content-h4-color-link'	=> array(
						'label'		=> __( 'Link Color', 'gppro-entry-content' ),
						'sub'		=> __( 'Base', 'gppro-entry-content' ),
						'input'		=> 'color',
						'target'	=> $class.' .entry-content h4 a',
						'selector'	=> 'color'
					),
					'entry-content-h4-color-link-hov'	=> array(
						'label'		=> __( 'Link Color', 'gppro-entry-content' ),
						'sub'		=> __( 'Hover', 'gppro-entry-content' ),
						'input'		=> 'color',
						'target'	=> $class.' .entry-content h4 a:hover',
						'selector'	=> 'color'
					),
				),
			),

			'entry-content-h4-type-setup'	=> array(
				'title'		=> __( 'Typography', 'gppro-entry-content' ),
				'data'		=> array(
					'entry-content-h4-stack'	=> array(
						'label'		=> __( 'Font Stack', 'gppro-entry-content' ),
						'input'		=> 'font-stack',
						'target'	=> $class.' .entry-content h4',
						'selector'	=> 'font-family'
					),
					'entry-content-h4-size'	=> array(
						'label'		=> __( 'Font Size', 'gppro-entry-content' ),
						'input'		=> 'font-size',
						'scale'		=> 'text',
						'target'	=> $class.' .entry-content h4',
						'selector'	=> 'font-size',
					),
					'entry-content-h4-weight'	=> array(
						'label'		=> __( 'Font Weight', 'gppro-entry-content' ),
						'input'		=> 'font-weight',
						'target'	=> $class.' .entry-content h4',
						'selector'	=> 'font-weight',
						'tip'		=> __( 'Certain fonts will not display every weight.', 'gppro-entry-content' )
					),
				),
			),

			'entry-content-h4-appearance-setup'	=> array(
				'title'		=> __( 'Text Appearance', 'gppro-entry-content' ),
				'data'		=> array(
					'entry-content-h4-margin-bottom'	=> array(
						'label'		=> __( 'Margin Bottom', 'gppro-entry-content' ),
						'input'		=> 'spacing',
						'target'	=> $class.' .entry-content h4',
						'selector'	=> 'margin-bottom',
						'min'		=> '0',
						'max'		=> '60',
						'step'		=> '1'
					),
					'entry-content-h4-padding-bottom'	=> array(
						'label'		=> __( 'Padding Bottom', 'gppro-entry-content' ),
						'input'		=> 'spacing',
						'target'	=> $class.' .entry-content h4',
						'selector'	=> 'padding-bottom',
						'min'		=> '0',
						'max'		=> '60',
						'step'		=> '1'
					),
					'entry-content-h4-transform'	=> array(
						'label'		=> __( 'Text Appearance', 'gppro-entry-content' ),
						'input'		=> 'text-transform',
						'target'	=> $class.' .entry-content h4',
						'selector'	=> 'text-transform'
					),
					'entry-content-h4-align'	=> array(
						'label'		=> __( 'Text Alignment', 'gppro-entry-content' ),
						'input'		=> 'text-align',
						'target'	=> $class.' .entry-content h4',
						'selector'	=> 'text-align'
					),
					'entry-content-h4-link-dec' => array(
						'label'		=> __( 'Link Style', 'gppro-entry-content' ),
						'sub'		=> __( 'Base', 'gppro-entry-content' ),
						'input'		=> 'text-decoration',
						'target'	=> $class.' .entry-content h4 a',
						'selector'	=> 'text-decoration'
					),
					'entry-content-h4-link-dec-hov' => array(
						'label'		=> __( 'Link Style', 'gppro-entry-content' ),
						'sub'		=> __( 'Hover', 'gppro-entry-content' ),
						'input'		=> 'text-decoration',
						'target'	=> $class.' .entry-content h4 a:hover',
						'selector'	=> 'text-decoration'
					),
				),
			),

			'section-break-entry-content-h5'	=> array(
				'break'	=> array(
					'type'	=> 'thin',
					'title'	=> __( 'H5 Headers', 'gppro-entry-content' ),
				),
			),

			'entry-content-h5-color-setup'	=> array(
				'title'		=> __( 'Colors', 'gppro-entry-content' ),
				'data'		=> array(
					'entry-content-h5-color-text'	=> array(
						'label'		=> __( 'Base Color', 'gppro-entry-content' ),
						'input'		=> 'color',
						'target'	=> $class.' .entry-content h5',
						'selector'	=> 'color'
					),
					'entry-content-h5-color-link'	=> array(
						'label'		=> __( 'Link Color', 'gppro-entry-content' ),
						'sub'		=> __( 'Base', 'gppro-entry-content' ),
						'input'		=> 'color',
						'target'	=> $class.' .entry-content h5 a',
						'selector'	=> 'color'
					),
					'entry-content-h5-color-link-hov'	=> array(
						'label'		=> __( 'Link Color', 'gppro-entry-content' ),
						'sub'		=> __( 'Hover', 'gppro-entry-content' ),
						'input'		=> 'color',
						'target'	=> $class.' .entry-content h5 a:hover',
						'selector'	=> 'color'
					),
				),
			),

			'entry-content-h5-type-setup'	=> array(
				'title'		=> __( 'Typography', 'gppro-entry-content' ),
				'data'		=> array(
					'entry-content-h5-stack'	=> array(
						'label'		=> __( 'Font Stack', 'gppro-entry-content' ),
						'input'		=> 'font-stack',
						'target'	=> $class.' .entry-content h5',
						'selector'	=> 'font-family'
					),
					'entry-content-h5-size'	=> array(
						'label'		=> __( 'Font Size', 'gppro-entry-content' ),
						'input'		=> 'font-size',
						'scale'		=> 'text',
						'target'	=> $class.' .entry-content h5',
						'selector'	=> 'font-size',
					),
					'entry-content-h5-weight'	=> array(
						'label'		=> __( 'Font Weight', 'gppro-entry-content' ),
						'input'		=> 'font-weight',
						'target'	=> $class.' .entry-content h5',
						'selector'	=> 'font-weight',
						'tip'		=> __( 'Certain fonts will not display every weight.', 'gppro-entry-content' )
					),
				),
			),

			'entry-content-h5-appearance-setup'	=> array(
				'title'		=> __( 'Text Appearance', 'gppro-entry-content' ),
				'data'		=> array(
					'entry-content-h5-margin-bottom'	=> array(
						'label'		=> __( 'Margin Bottom', 'gppro-entry-content' ),
						'input'		=> 'spacing',
						'target'	=> $class.' .entry-content h5',
						'selector'	=> 'margin-bottom',
						'min'		=> '0',
						'max'		=> '60',
						'step'		=> '1'
					),
					'entry-content-h5-padding-bottom'	=> array(
						'label'		=> __( 'Padding Bottom', 'gppro-entry-content' ),
						'input'		=> 'spacing',
						'target'	=> $class.' .entry-content h5',
						'selector'	=> 'padding-bottom',
						'min'		=> '0',
						'max'		=> '60',
						'step'		=> '1'
					),
					'entry-content-h5-transform'	=> array(
						'label'		=> __( 'Text Appearance', 'gppro-entry-content' ),
						'input'		=> 'text-transform',
						'target'	=> $class.' .entry-content h5',
						'selector'	=> 'text-transform'
					),
					'entry-content-h5-align'	=> array(
						'label'		=> __( 'Text Alignment', 'gppro-entry-content' ),
						'input'		=> 'text-align',
						'target'	=> $class.' .entry-content h5',
						'selector'	=> 'text-align'
					),
					'entry-content-h5-link-dec' => array(
						'label'		=> __( 'Link Style', 'gppro-entry-content' ),
						'sub'		=> __( 'Base', 'gppro-entry-content' ),
						'input'		=> 'text-decoration',
						'target'	=> $class.' .entry-content h5 a',
						'selector'	=> 'text-decoration'
					),
					'entry-content-h5-link-dec-hov' => array(
						'label'		=> __( 'Link Style', 'gppro-entry-content' ),
						'sub'		=> __( 'Hover', 'gppro-entry-content' ),
						'input'		=> 'text-decoration',
						'target'	=> $class.' .entry-content h5 a:hover',
						'selector'	=> 'text-decoration'
					),
				),
			),

			'section-break-entry-content-h6'	=> array(
				'break'	=> array(
					'type'	=> 'thin',
					'title'	=> __( 'H6 Headers', 'gppro-entry-content' ),
				),
			),

			'entry-content-h6-color-setup'	=> array(
				'title'		=> __( 'Colors', 'gppro-entry-content' ),
				'data'		=> array(
					'entry-content-h6-color-text'	=> array(
						'label'		=> __( 'Base Color', 'gppro-entry-content' ),
						'input'		=> 'color',
						'target'	=> $class.' .entry-content h6',
						'selector'	=> 'color'
					),
					'entry-content-h6-color-link'	=> array(
						'label'		=> __( 'Link Color', 'gppro-entry-content' ),
						'sub'		=> __( 'Base', 'gppro-entry-content' ),
						'input'		=> 'color',
						'target'	=> $class.' .entry-content h6 a',
						'selector'	=> 'color'
					),
					'entry-content-h6-color-link-hov'	=> array(
						'label'		=> __( 'Link Color', 'gppro-entry-content' ),
						'sub'		=> __( 'Hover', 'gppro-entry-content' ),
						'input'		=> 'color',
						'target'	=> $class.' .entry-content h6 a:hover',
						'selector'	=> 'color'
					),
				),
			),

			'entry-content-h6-type-setup'	=> array(
				'title'		=> __( 'Typography', 'gppro-entry-content' ),
				'data'		=> array(
					'entry-content-h6-stack'	=> array(
						'label'		=> __( 'Font Stack', 'gppro-entry-content' ),
						'input'		=> 'font-stack',
						'target'	=> $class.' .entry-content h6',
						'selector'	=> 'font-family'
					),
					'entry-content-h6-size'	=> array(
						'label'		=> __( 'Font Size', 'gppro-entry-content' ),
						'input'		=> 'font-size',
						'scale'		=> 'text',
						'target'	=> $class.' .entry-content h6',
						'selector'	=> 'font-size',
					),
					'entry-content-h6-weight'	=> array(
						'label'		=> __( 'Font Weight', 'gppro-entry-content' ),
						'input'		=> 'font-weight',
						'target'	=> $class.' .entry-content h6',
						'selector'	=> 'font-weight',
						'tip'		=> __( 'Certain fonts will not display every weight.', 'gppro-entry-content' )
					),
				),
			),

			'entry-content-h6-appearance-setup'	=> array(
				'title'		=> __( 'Text Appearance', 'gppro-entry-content' ),
				'data'		=> array(
					'entry-content-h6-margin-bottom'	=> array(
						'label'		=> __( 'Margin Bottom', 'gppro-entry-content' ),
						'input'		=> 'spacing',
						'target'	=> $class.' .entry-content h6',
						'selector'	=> 'margin-bottom',
						'min'		=> '0',
						'max'		=> '60',
						'step'		=> '1'
					),
					'entry-content-h6-padding-bottom'	=> array(
						'label'		=> __( 'Padding Bottom', 'gppro-entry-content' ),
						'input'		=> 'spacing',
						'target'	=> $class.' .entry-content h6',
						'selector'	=> 'padding-bottom',
						'min'		=> '0',
						'max'		=> '60',
						'step'		=> '1'
					),
					'entry-content-h6-transform'	=> array(
						'label'		=> __( 'Text Appearance', 'gppro-entry-content' ),
						'input'		=> 'text-transform',
						'target'	=> $class.' .entry-content h6',
						'selector'	=> 'text-transform'
					),
					'entry-content-h6-align'	=> array(
						'label'		=> __( 'Text Alignment', 'gppro-entry-content' ),
						'input'		=> 'text-align',
						'target'	=> $class.' .entry-content h6',
						'selector'	=> 'text-align'
					),
					'entry-content-h6-link-dec' => array(
						'label'		=> __( 'Link Style', 'gppro-entry-content' ),
						'sub'		=> __( 'Base', 'gppro-entry-content' ),
						'input'		=> 'text-decoration',
						'target'	=> $class.' .entry-content h6 a',
						'selector'	=> 'text-decoration'
					),
					'entry-content-h6-link-dec-hov' => array(
						'label'		=> __( 'Link Style', 'gppro-entry-content' ),
						'sub'		=> __( 'Hover', 'gppro-entry-content' ),
						'input'		=> 'text-decoration',
						'target'	=> $class.' .entry-content h6 a:hover',
						'selector'	=> 'text-decoration'
					),
				),
			),

			'section-break-entry-content-p'	=> array(
				'break'	=> array(
					'type'	=> 'thin',
					'title'	=> __( 'Paragraphs', 'gppro-entry-content' ),
				),
			),

			'entry-content-p-color-setup'	=> array(
				'title'		=> __( 'Colors', 'gppro-entry-content' ),
				'data'		=> array(
					'entry-content-p-color-text'	=> array(
						'label'		=> __( 'Base Color', 'gppro-entry-content' ),
						'input'		=> 'color',
						'target'	=> $class.' .entry-content p',
						'selector'	=> 'color'
					),
					'entry-content-p-color-link'	=> array(
						'label'		=> __( 'Link Color', 'gppro-entry-content' ),
						'sub'		=> __( 'Base', 'gppro-entry-content' ),
						'input'		=> 'color',
						'target'	=> $class.' .entry-content p a',
						'selector'	=> 'color'
					),
					'entry-content-p-color-link-hov'	=> array(
						'label'		=> __( 'Link Color', 'gppro-entry-content' ),
						'sub'		=> __( 'Hover', 'gppro-entry-content' ),
						'input'		=> 'color',
						'target'	=> $class.' .entry-content p a:hover',
						'selector'	=> 'color'
					),
				),
			),

			'entry-content-p-type-setup'	=> array(
				'title'		=> __( 'Typography', 'gppro-entry-content' ),
				'data'		=> array(
					'entry-content-p-stack'	=> array(
						'label'		=> __( 'Font Stack', 'gppro-entry-content' ),
						'input'		=> 'font-stack',
						'target'	=> $class.' .entry-content p',
						'selector'	=> 'font-family'
					),
					'entry-content-p-size'	=> array(
						'label'		=> __( 'Font Size', 'gppro-entry-content' ),
						'input'		=> 'font-size',
						'scale'		=> 'text',
						'target'	=> $class.' .entry-content p',
						'selector'	=> 'font-size',
					),
					'entry-content-p-weight'	=> array(
						'label'		=> __( 'Font Weight', 'gppro-entry-content' ),
						'input'		=> 'font-weight',
						'target'	=> $class.' .entry-content p',
						'selector'	=> 'font-weight',
						'tip'		=> __( 'Certain fonts will not display every weight.', 'gppro-entry-content' )
					),
				),
			),

			'entry-content-p-appearance-setup'	=> array(
				'title'		=> __( 'Text Appearance', 'gppro-entry-content' ),
				'data'		=> array(
					'entry-content-p-margin-bottom'	=> array(
						'label'		=> __( 'Margin Bottom', 'gppro-entry-content' ),
						'input'		=> 'spacing',
						'target'	=> $class.' .entry-content p',
						'selector'	=> 'margin-bottom',
						'min'		=> '0',
						'max'		=> '60',
						'step'		=> '1'
					),
					'entry-content-p-padding-bottom'	=> array(
						'label'		=> __( 'Padding Bottom', 'gppro-entry-content' ),
						'input'		=> 'spacing',
						'target'	=> $class.' .entry-content p',
						'selector'	=> 'padding-bottom',
						'min'		=> '0',
						'max'		=> '60',
						'step'		=> '1'
					),
					'entry-content-p-transform'	=> array(
						'label'		=> __( 'Text Appearance', 'gppro-entry-content' ),
						'input'		=> 'text-transform',
						'target'	=> $class.' .entry-content p',
						'selector'	=> 'text-transform'
					),
					'entry-content-p-align'	=> array(
						'label'		=> __( 'Text Alignment', 'gppro-entry-content' ),
						'input'		=> 'text-align',
						'target'	=> $class.' .entry-content p',
						'selector'	=> 'text-align'
					),
					'entry-content-p-link-dec' => array(
						'label'		=> __( 'Link Style', 'gppro-entry-content' ),
						'sub'		=> __( 'Base', 'gppro-entry-content' ),
						'input'		=> 'text-decoration',
						'target'	=> $class.' .entry-content p a',
						'selector'	=> 'text-decoration'
					),
					'entry-content-p-link-dec-hov' => array(
						'label'		=> __( 'Link Style', 'gppro-entry-content' ),
						'sub'		=> __( 'Hover', 'gppro-entry-content' ),
						'input'		=> 'text-decoration',
						'target'	=> $class.' .entry-content p a:hover',
						'selector'	=> 'text-decoration'
					),
				),
			),


			'section-break-entry-content-ul'	=> array(
				'break'	=> array(
					'type'	=> 'thin',
					'title'	=> __( 'Unordered Lists (<ul>)', 'gppro-entry-content' ),
				),
			),

			'entry-content-ul-color-setup'	=> array(
				'title'		=> __( 'Colors', 'gppro-entry-content' ),
				'data'		=> array(
					'entry-content-ul-color-text'	=> array(
						'label'		=> __( 'Base Color', 'gppro-entry-content' ),
						'input'		=> 'color',
						'target'	=> $class.' .entry-content ul',
						'selector'	=> 'color'
					),
					'entry-content-ul-color-link'	=> array(
						'label'		=> __( 'Link Color', 'gppro-entry-content' ),
						'sub'		=> __( 'Base', 'gppro-entry-content' ),
						'input'		=> 'color',
						'target'	=> $class.' .entry-content ul a',
						'selector'	=> 'color'
					),
					'entry-content-ul-color-link-hov'	=> array(
						'label'		=> __( 'Link Color', 'gppro-entry-content' ),
						'sub'		=> __( 'Hover', 'gppro-entry-content' ),
						'input'		=> 'color',
						'target'	=> $class.' .entry-content ul a:hover',
						'selector'	=> 'color'
					),
				),
			),

			'entry-content-ul-type-setup'	=> array(
				'title'		=> __( 'Typography', 'gppro-entry-content' ),
				'data'		=> array(
					'entry-content-ul-stack'	=> array(
						'label'		=> __( 'Font Stack', 'gppro-entry-content' ),
						'input'		=> 'font-stack',
						'target'	=> $class.' .entry-content ul',
						'selector'	=> 'font-family'
					),
					'entry-content-ul-size'	=> array(
						'label'		=> __( 'Font Size', 'gppro-entry-content' ),
						'input'		=> 'font-size',
						'scale'		=> 'text',
						'target'	=> $class.' .entry-content ul',
						'selector'	=> 'font-size',
					),
					'entry-content-ul-weight'	=> array(
						'label'		=> __( 'Font Weight', 'gppro-entry-content' ),
						'input'		=> 'font-weight',
						'target'	=> $class.' .entry-content ul',
						'selector'	=> 'font-weight',
						'tip'		=> __( 'Certain fonts will not display every weight.', 'gppro-entry-content' )
					),
				),
			),

			'entry-content-ul-appearance-setup'	=> array(
				'title'		=> __( 'Text Appearance', 'gppro-entry-content' ),
				'data'		=> array(
					'entry-content-ul-margin-left'	=> array(
						'label'		=> __( 'Margin Left', 'gppro-entry-content' ),
						'input'		=> 'spacing',
						'target'	=> $class.' .entry-content ul',
						'selector'	=> 'margin-left',
						'min'		=> '0',
						'max'		=> '60',
						'step'		=> '1'
					),
					'entry-content-ul-margin-bottom'	=> array(
						'label'		=> __( 'Margin Bottom', 'gppro-entry-content' ),
						'input'		=> 'spacing',
						'target'	=> $class.' .entry-content ul',
						'selector'	=> 'margin-bottom',
						'min'		=> '0',
						'max'		=> '60',
						'step'		=> '1'
					),
					'entry-content-ul-padding-left'	=> array(
						'label'		=> __( 'Padding Left', 'gppro-entry-content' ),
						'input'		=> 'spacing',
						'target'	=> $class.' .entry-content ul',
						'selector'	=> 'padding-left',
						'min'		=> '0',
						'max'		=> '60',
						'step'		=> '1'
					),
					'entry-content-ul-padding-bottom'	=> array(
						'label'		=> __( 'Padding Bottom', 'gppro-entry-content' ),
						'input'		=> 'spacing',
						'target'	=> $class.' .entry-content ul',
						'selector'	=> 'padding-bottom',
						'min'		=> '0',
						'max'		=> '60',
						'step'		=> '1'
					),
					'entry-content-ul-list-style'	=> array(
						'label'		=> __( 'List Style', 'gppro' ),
						'input'		=> 'lists',
						'target'	=> $class.' .entry-content ul li',
						'selector'	=> 'list-style-type',
					),
					'entry-content-ul-transform'	=> array(
						'label'		=> __( 'Text Appearance', 'gppro-entry-content' ),
						'input'		=> 'text-transform',
						'target'	=> $class.' .entry-content ul',
						'selector'	=> 'text-transform'
					),
					'entry-content-ul-align'	=> array(
						'label'		=> __( 'Text Alignment', 'gppro-entry-content' ),
						'input'		=> 'text-align',
						'target'	=> $class.' .entry-content ul',
						'selector'	=> 'text-align'
					),
					'entry-content-ul-link-dec' => array(
						'label'		=> __( 'Link Style', 'gppro-entry-content' ),
						'sub'		=> __( 'Base', 'gppro-entry-content' ),
						'input'		=> 'text-decoration',
						'target'	=> $class.' .entry-content ul a',
						'selector'	=> 'text-decoration'
					),
					'entry-content-ul-link-dec-hov' => array(
						'label'		=> __( 'Link Style', 'gppro-entry-content' ),
						'sub'		=> __( 'Hover', 'gppro-entry-content' ),
						'input'		=> 'text-decoration',
						'target'	=> $class.' .entry-content ul a:hover',
						'selector'	=> 'text-decoration'
					),
				),
			),

			'section-break-entry-content-ol'	=> array(
				'break'	=> array(
					'type'	=> 'thin',
					'title'	=> __( 'Ordered Lists (<ol>)', 'gppro-entry-content' ),
				),
			),

			'entry-content-ol-color-setup'	=> array(
				'title'		=> __( 'Colors', 'gppro-entry-content' ),
				'data'		=> array(
					'entry-content-ol-color-text'	=> array(
						'label'		=> __( 'Base Color', 'gppro-entry-content' ),
						'input'		=> 'color',
						'target'	=> $class.' .entry-content ol',
						'selector'	=> 'color'
					),
					'entry-content-ol-color-link'	=> array(
						'label'		=> __( 'Link Color', 'gppro-entry-content' ),
						'sub'		=> __( 'Base', 'gppro-entry-content' ),
						'input'		=> 'color',
						'target'	=> $class.' .entry-content ol a',
						'selector'	=> 'color'
					),
					'entry-content-ol-color-link-hov'	=> array(
						'label'		=> __( 'Link Color', 'gppro-entry-content' ),
						'sub'		=> __( 'Hover', 'gppro-entry-content' ),
						'input'		=> 'color',
						'target'	=> $class.' .entry-content ol a:hover',
						'selector'	=> 'color'
					),
				),
			),

			'entry-content-ol-type-setup'	=> array(
				'title'		=> __( 'Typography', 'gppro-entry-content' ),
				'data'		=> array(
					'entry-content-ol-stack'	=> array(
						'label'		=> __( 'Font Stack', 'gppro-entry-content' ),
						'input'		=> 'font-stack',
						'target'	=> $class.' .entry-content ol',
						'selector'	=> 'font-family'
					),
					'entry-content-ol-size'	=> array(
						'label'		=> __( 'Font Size', 'gppro-entry-content' ),
						'input'		=> 'font-size',
						'scale'		=> 'text',
						'target'	=> $class.' .entry-content ol',
						'selector'	=> 'font-size',
					),
					'entry-content-ol-weight'	=> array(
						'label'		=> __( 'Font Weight', 'gppro-entry-content' ),
						'input'		=> 'font-weight',
						'target'	=> $class.' .entry-content ol',
						'selector'	=> 'font-weight',
						'tip'		=> __( 'Certain fonts will not display every weight.', 'gppro-entry-content' )
					),
				),
			),

			'entry-content-ol-appearance-setup'	=> array(
				'title'		=> __( 'Text Appearance', 'gppro-entry-content' ),
				'data'		=> array(
					'entry-content-ol-margin-left'	=> array(
						'label'		=> __( 'Margin Left', 'gppro-entry-content' ),
						'input'		=> 'spacing',
						'target'	=> $class.' .entry-content ol',
						'selector'	=> 'margin-left',
						'min'		=> '0',
						'max'		=> '60',
						'step'		=> '1'
					),
					'entry-content-ol-margin-bottom'	=> array(
						'label'		=> __( 'Margin Bottom', 'gppro-entry-content' ),
						'input'		=> 'spacing',
						'target'	=> $class.' .entry-content ol',
						'selector'	=> 'margin-bottom',
						'min'		=> '0',
						'max'		=> '60',
						'step'		=> '1'
					),
					'entry-content-ol-padding-left'	=> array(
						'label'		=> __( 'Padding Left', 'gppro-entry-content' ),
						'input'		=> 'spacing',
						'target'	=> $class.' .entry-content ol',
						'selector'	=> 'padding-left',
						'min'		=> '0',
						'max'		=> '60',
						'step'		=> '1'
					),
					'entry-content-ol-padding-bottom'	=> array(
						'label'		=> __( 'Padding Bottom', 'gppro-entry-content' ),
						'input'		=> 'spacing',
						'target'	=> $class.' .entry-content ol',
						'selector'	=> 'padding-bottom',
						'min'		=> '0',
						'max'		=> '60',
						'step'		=> '1'
					),
					'entry-content-ol-list-style'	=> array(
						'label'		=> __( 'List Style', 'gppro' ),
						'input'		=> 'lists',
						'target'	=> $class.' .entry-content ol li',
						'selector'	=> 'list-style-type',
					),
					'entry-content-ol-transform'	=> array(
						'label'		=> __( 'Text Appearance', 'gppro-entry-content' ),
						'input'		=> 'text-transform',
						'target'	=> $class.' .entry-content ol',
						'selector'	=> 'text-transform'
					),
					'entry-content-ol-align'	=> array(
						'label'		=> __( 'Text Alignment', 'gppro-entry-content' ),
						'input'		=> 'text-align',
						'target'	=> $class.' .entry-content ol',
						'selector'	=> 'text-align'
					),
					'entry-content-ol-link-dec' => array(
						'label'		=> __( 'Link Style', 'gppro-entry-content' ),
						'sub'		=> __( 'Base', 'gppro-entry-content' ),
						'input'		=> 'text-decoration',
						'target'	=> $class.' .entry-content ol a',
						'selector'	=> 'text-decoration'
					),
					'entry-content-ol-link-dec-hov' => array(
						'label'		=> __( 'Link Style', 'gppro-entry-content' ),
						'sub'		=> __( 'Hover', 'gppro-entry-content' ),
						'input'		=> 'text-decoration',
						'target'	=> $class.' .entry-content ol a:hover',
						'selector'	=> 'text-decoration'
					),
				),
			),

			'section-break-entry-content-cap'	=> array(
				'break'	=> array(
					'type'	=> 'thin',
					'title'	=> __( 'Image Captions', 'gppro-entry-content' ),
				),
			),

			'entry-content-cap-color-setup'	=> array(
				'title'		=> __( 'Colors', 'gppro-entry-content' ),
				'data'		=> array(
					'entry-content-cap-color-text'	=> array(
						'label'		=> __( 'Base Color', 'gppro-entry-content' ),
						'input'		=> 'color',
						'target'	=> $class.' .entry-content .wp-caption-text',
						'selector'	=> 'color'
					),
					'entry-content-cap-color-link'	=> array(
						'label'		=> __( 'Link Color', 'gppro-entry-content' ),
						'sub'		=> __( 'Base', 'gppro-entry-content' ),
						'input'		=> 'color',
						'target'	=> $class.' .entry-content .wp-caption-text a',
						'selector'	=> 'color'
					),
					'entry-content-cap-color-link-hov'	=> array(
						'label'		=> __( 'Link Color', 'gppro-entry-content' ),
						'sub'		=> __( 'Hover', 'gppro-entry-content' ),
						'input'		=> 'color',
						'target'	=> $class.' .entry-content .wp-caption-text a:hover',
						'selector'	=> 'color'
					),
				),
			),

			'entry-content-cap-type-setup'	=> array(
				'title'		=> __( 'Typography', 'gppro-entry-content' ),
				'data'		=> array(
					'entry-content-cap-stack'	=> array(
						'label'		=> __( 'Font Stack', 'gppro-entry-content' ),
						'input'		=> 'font-stack',
						'target'	=> $class.' .entry-content .wp-caption-text',
						'selector'	=> 'font-family'
					),
					'entry-content-cap-size'	=> array(
						'label'		=> __( 'Font Size', 'gppro-entry-content' ),
						'input'		=> 'font-size',
						'scale'		=> 'text',
						'target'	=> $class.' .entry-content .wp-caption-text',
						'selector'	=> 'font-size',
					),
					'entry-content-cap-weight'	=> array(
						'label'		=> __( 'Font Weight', 'gppro-entry-content' ),
						'input'		=> 'font-weight',
						'target'	=> $class.' .entry-content .wp-caption-text',
						'selector'	=> 'font-weight',
						'tip'		=> __( 'Certain fonts will not display every weight.', 'gppro-entry-content' )
					),
				),
			),

			'entry-content-cap-appearance-setup'	=> array(
				'title'		=> __( 'Text Appearance', 'gppro-entry-content' ),
				'data'		=> array(
					'entry-content-cap-margin-bottom'	=> array(
						'label'		=> __( 'Margin Bottom', 'gppro-entry-content' ),
						'input'		=> 'spacing',
						'target'	=> $class.' .entry-content .wp-caption-text',
						'selector'	=> 'margin-bottom',
						'min'		=> '0',
						'max'		=> '60',
						'step'		=> '1'
					),
					'entry-content-cap-padding-bottom'	=> array(
						'label'		=> __( 'Padding Bottom', 'gppro-entry-content' ),
						'input'		=> 'spacing',
						'target'	=> $class.' .entry-content .wp-caption-text',
						'selector'	=> 'padding-bottom',
						'min'		=> '0',
						'max'		=> '60',
						'step'		=> '1'
					),
					'entry-content-cap-transform'	=> array(
						'label'		=> __( 'Text Appearance', 'gppro-entry-content' ),
						'input'		=> 'text-transform',
						'target'	=> $class.' .entry-content .wp-caption-text',
						'selector'	=> 'text-transform'
					),
					'entry-content-cap-align'	=> array(
						'label'		=> __( 'Text Alignment', 'gppro-entry-content' ),
						'input'		=> 'text-align',
						'target'	=> $class.' .entry-content .wp-caption-text',
						'selector'	=> 'text-align'
					),
					'entry-content-cap-link-dec' => array(
						'label'		=> __( 'Link Style', 'gppro-entry-content' ),
						'sub'		=> __( 'Base', 'gppro-entry-content' ),
						'input'		=> 'text-decoration',
						'target'	=> $class.' .entry-content .wp-caption-text a',
						'selector'	=> 'text-decoration'
					),
					'entry-content-cap-link-dec-hov' => array(
						'label'		=> __( 'Link Style', 'gppro-entry-content' ),
						'sub'		=> __( 'Hover', 'gppro-entry-content' ),
						'input'		=> 'text-decoration',
						'target'	=> $class.' .entry-content .wp-caption-text a:hover',
						'selector'	=> 'text-decoration'
					),
				),
			),

			'section-break-entry-content-code'	=> array(
				'break'	=> array(
					'type'	=> 'thin',
					'title'	=> __( 'Code Blocks', 'gppro-entry-content' ),
				),
			),

			'entry-content-code-color-setup'	=> array(
				'title'		=> __( 'Colors', 'gppro-entry-content' ),
				'data'		=> array(
					'entry-content-code-background'	=> array(
						'label'		=> __( 'Background Color', 'gppro-entry-content' ),
						'input'		=> 'color',
						'target'	=> $class.' .entry-content code',
						'selector'	=> 'background-color'
					),
					'entry-content-code-color-text'	=> array(
						'label'		=> __( 'Base Color', 'gppro-entry-content' ),
						'input'		=> 'color',
						'target'	=> $class.' .entry-content code',
						'selector'	=> 'color'
					),
				),
			),

			'entry-content-code-type-setup'	=> array(
				'title'		=> __( 'Typography', 'gppro-entry-content' ),
				'data'		=> array(
					'entry-content-code-stack'	=> array(
						'label'		=> __( 'Font Stack', 'gppro-entry-content' ),
						'input'		=> 'font-stack',
						'target'	=> $class.' .entry-content code',
						'selector'	=> 'font-family'
					),
					'entry-content-code-size'	=> array(
						'label'		=> __( 'Font Size', 'gppro-entry-content' ),
						'input'		=> 'font-size',
						'scale'		=> 'text',
						'target'	=> $class.' .entry-content code',
						'selector'	=> 'font-size',
					),
					'entry-content-code-weight'	=> array(
						'label'		=> __( 'Font Weight', 'gppro-entry-content' ),
						'input'		=> 'font-weight',
						'target'	=> $class.' .entry-content code',
						'selector'	=> 'font-weight',
						'tip'		=> __( 'Certain fonts will not display every weight.', 'gppro-entry-content' )
					),
				),
			),

		); // end section

		return $sections;

	}


	/**
	 * add freeform CSS to builder file
	 *
	 * @return
	 */

	public function entry_css_post_content( $css, $data, $class ) {

		$css	.= '/* detailed entry content CSS */'."\n";

		// H1 setup
		$css	.= $class.' .entry-content h1 { ';

			if ( GP_Pro_Builder::build_check( $data, 'entry-content-h1-color-text' ) )
				$css	.= GP_Pro_Builder::hexcolor_css( 'color', $data['entry-content-h1-color-text'] );

			if ( GP_Pro_Builder::build_check( $data, 'entry-content-h1-stack' ) )
				$css	.= GP_Pro_Builder::stack_css( 'font-family', $data['entry-content-h1-stack'] );

			if ( GP_Pro_Builder::build_check( $data, 'entry-content-h1-size' ) )
				$css	.= GP_Pro_Builder::px_rem_css( 'font-size', $data['entry-content-h1-size'] );

			if ( GP_Pro_Builder::build_check( $data, 'entry-content-h1-weight' ) )
				$css	.= GP_Pro_Builder::number_css( 'font-weight', $data['entry-content-h1-weight'] );

			if ( GP_Pro_Builder::build_check( $data, 'entry-content-h1-margin-bottom' ) )
				$css	.= GP_Pro_Builder::px_rem_css( 'margin-bottom', $data['entry-content-h1-margin-bottom'] );

			if ( GP_Pro_Builder::build_check( $data, 'entry-content-h1-padding-bottom' ) )
				$css	.= GP_Pro_Builder::px_rem_css( 'padding-bottom', $data['entry-content-h1-padding-bottom'] );

			if ( GP_Pro_Builder::build_check( $data, 'entry-content-h1-transform' ) )
				$css	.= GP_Pro_Builder::text_css( 'text-transform', $data['entry-content-h1-transform'] );

			if ( GP_Pro_Builder::build_check( $data, 'entry-content-h1-align' ) )
				$css	.= GP_Pro_Builder::text_css( 'text-align', $data['entry-content-h1-align'] );

		$css	.= '}'."\n";

		// H1 link standard
		$css	.= $class.' .entry-content h1 a { ';

			if ( GP_Pro_Builder::build_check( $data, 'entry-content-h1-color-link' ) )
				$css	.= GP_Pro_Builder::hexcolor_css( 'color', $data['entry-content-h1-color-link'] );

			if ( GP_Pro_Builder::build_check( $data, 'entry-content-h1-link-dec' ) )
				$css	.= GP_Pro_Builder::text_css( 'text-decoration', $data['entry-content-h1-link-dec'] );

			if ( GP_Pro_Builder::build_check( $data, 'entry-content-h1-link-dec' ) )
				$css	.= GP_Pro_Builder::text_css( 'border-bottom-style', 'none' );

		$css	.= '}'."\n";

		// H1 link hover / focus
		$css	.= $class.' .entry-content h1 a:hover, '.$class.' .entry-content h1 a:focus { ';

			if ( GP_Pro_Builder::build_check( $data, 'entry-content-h1-color-link-hov' ) )
				$css	.= GP_Pro_Builder::hexcolor_css( 'color', $data['entry-content-h1-color-link-hov'] );

			if ( GP_Pro_Builder::build_check( $data, 'entry-content-h1-link-dec-hov' ) )
				$css	.= GP_Pro_Builder::text_css( 'text-decoration', $data['entry-content-h1-link-dec-hov'] );

			if ( GP_Pro_Builder::build_check( $data, 'entry-content-h1-link-dec-hov' ) )
				$css	.= GP_Pro_Builder::text_css( 'border-bottom-style', 'none' );

		$css	.= '}'."\n";

		// H2 setup
		$css	.= $class.' .entry-content h2 { ';

			if ( GP_Pro_Builder::build_check( $data, 'entry-content-h2-color-text' ) )
				$css	.= GP_Pro_Builder::hexcolor_css( 'color', $data['entry-content-h2-color-text'] );

			if ( GP_Pro_Builder::build_check( $data, 'entry-content-h2-stack' ) )
				$css	.= GP_Pro_Builder::stack_css( 'font-family', $data['entry-content-h2-stack'] );

			if ( GP_Pro_Builder::build_check( $data, 'entry-content-h2-size' ) )
				$css	.= GP_Pro_Builder::px_rem_css( 'font-size', $data['entry-content-h2-size'] );

			if ( GP_Pro_Builder::build_check( $data, 'entry-content-h2-weight' ) )
				$css	.= GP_Pro_Builder::number_css( 'font-weight', $data['entry-content-h2-weight'] );

			if ( GP_Pro_Builder::build_check( $data, 'entry-content-h2-margin-bottom' ) )
				$css	.= GP_Pro_Builder::px_rem_css( 'margin-bottom', $data['entry-content-h2-margin-bottom'] );

			if ( GP_Pro_Builder::build_check( $data, 'entry-content-h2-padding-bottom' ) )
				$css	.= GP_Pro_Builder::px_rem_css( 'padding-bottom', $data['entry-content-h2-padding-bottom'] );

			if ( GP_Pro_Builder::build_check( $data, 'entry-content-h2-transform' ) )
				$css	.= GP_Pro_Builder::text_css( 'text-transform', $data['entry-content-h2-transform'] );

			if ( GP_Pro_Builder::build_check( $data, 'entry-content-h2-align' ) )
				$css	.= GP_Pro_Builder::text_css( 'text-align', $data['entry-content-h2-align'] );

		$css	.= '}'."\n";

		// H2 link standard
		$css	.= $class.' .entry-content h2 a { ';

			if ( GP_Pro_Builder::build_check( $data, 'entry-content-h2-color-link' ) )
				$css	.= GP_Pro_Builder::hexcolor_css( 'color', $data['entry-content-h2-color-link'] );

			if ( GP_Pro_Builder::build_check( $data, 'entry-content-h2-link-dec' ) )
				$css	.= GP_Pro_Builder::text_css( 'text-decoration', $data['entry-content-h2-link-dec'] );

			if ( GP_Pro_Builder::build_check( $data, 'entry-content-h2-link-dec' ) )
				$css	.= GP_Pro_Builder::text_css( 'border-bottom-style', 'none' );

		$css	.= '}'."\n";

		// H2 link hover / focus
		$css	.= $class.' .entry-content h2 a:hover, '.$class.' .entry-content h2 a:focus { ';

			if ( GP_Pro_Builder::build_check( $data, 'entry-content-h2-color-link-hov' ) )
				$css	.= GP_Pro_Builder::hexcolor_css( 'color', $data['entry-content-h2-color-link-hov'] );

			if ( GP_Pro_Builder::build_check( $data, 'entry-content-h2-link-dec-hov' ) )
				$css	.= GP_Pro_Builder::text_css( 'text-decoration', $data['entry-content-h2-link-dec-hov'] );

			if ( GP_Pro_Builder::build_check( $data, 'entry-content-h2-link-dec-hov' ) )
				$css	.= GP_Pro_Builder::text_css( 'border-bottom-style', 'none' );

		$css	.= '}'."\n";

		// H3 setup
		$css	.= $class.' .entry-content h3 { ';

			if ( GP_Pro_Builder::build_check( $data, 'entry-content-h3-color-text' ) )
				$css	.= GP_Pro_Builder::hexcolor_css( 'color', $data['entry-content-h3-color-text'] );

			if ( GP_Pro_Builder::build_check( $data, 'entry-content-h3-stack' ) )
				$css	.= GP_Pro_Builder::stack_css( 'font-family', $data['entry-content-h3-stack'] );

			if ( GP_Pro_Builder::build_check( $data, 'entry-content-h3-size' ) )
				$css	.= GP_Pro_Builder::px_rem_css( 'font-size', $data['entry-content-h3-size'] );

			if ( GP_Pro_Builder::build_check( $data, 'entry-content-h3-weight' ) )
				$css	.= GP_Pro_Builder::number_css( 'font-weight', $data['entry-content-h3-weight'] );

			if ( GP_Pro_Builder::build_check( $data, 'entry-content-h3-margin-bottom' ) )
				$css	.= GP_Pro_Builder::px_rem_css( 'margin-bottom', $data['entry-content-h3-margin-bottom'] );

			if ( GP_Pro_Builder::build_check( $data, 'entry-content-h3-padding-bottom' ) )
				$css	.= GP_Pro_Builder::px_rem_css( 'padding-bottom', $data['entry-content-h3-padding-bottom'] );

			if ( GP_Pro_Builder::build_check( $data, 'entry-content-h3-transform' ) )
				$css	.= GP_Pro_Builder::text_css( 'text-transform', $data['entry-content-h3-transform'] );

			if ( GP_Pro_Builder::build_check( $data, 'entry-content-h3-align' ) )
				$css	.= GP_Pro_Builder::text_css( 'text-align', $data['entry-content-h3-align'] );

		$css	.= '}'."\n";

		// H3 link standard
		$css	.= $class.' .entry-content h3 a { ';

			if ( GP_Pro_Builder::build_check( $data, 'entry-content-h3-color-link' ) )
				$css	.= GP_Pro_Builder::hexcolor_css( 'color', $data['entry-content-h3-color-link'] );

			if ( GP_Pro_Builder::build_check( $data, 'entry-content-h3-link-dec' ) )
				$css	.= GP_Pro_Builder::text_css( 'text-decoration', $data['entry-content-h3-link-dec'] );

			if ( GP_Pro_Builder::build_check( $data, 'entry-content-h3-link-dec' ) )
				$css	.= GP_Pro_Builder::text_css( 'border-bottom-style', 'none' );

		$css	.= '}'."\n";

		// H3 link hover / focus
		$css	.= $class.' .entry-content h3 a:hover, '.$class.' .entry-content h3 a:focus { ';

			if ( GP_Pro_Builder::build_check( $data, 'entry-content-h3-color-link-hov' ) )
				$css	.= GP_Pro_Builder::hexcolor_css( 'color', $data['entry-content-h3-color-link-hov'] );

			if ( GP_Pro_Builder::build_check( $data, 'entry-content-h3-link-dec-hov' ) )
				$css	.= GP_Pro_Builder::text_css( 'text-decoration', $data['entry-content-h3-link-dec-hov'] );

			if ( GP_Pro_Builder::build_check( $data, 'entry-content-h3-link-dec-hov' ) )
				$css	.= GP_Pro_Builder::text_css( 'border-bottom-style', 'none' );

		$css	.= '}'."\n";

		// H4 setup
		$css	.= $class.' .entry-content h4 { ';

			if ( GP_Pro_Builder::build_check( $data, 'entry-content-h4-color-text' ) )
				$css	.= GP_Pro_Builder::hexcolor_css( 'color', $data['entry-content-h4-color-text'] );

			if ( GP_Pro_Builder::build_check( $data, 'entry-content-h4-stack' ) )
				$css	.= GP_Pro_Builder::stack_css( 'font-family', $data['entry-content-h4-stack'] );

			if ( GP_Pro_Builder::build_check( $data, 'entry-content-h4-size' ) )
				$css	.= GP_Pro_Builder::px_rem_css( 'font-size', $data['entry-content-h4-size'] );

			if ( GP_Pro_Builder::build_check( $data, 'entry-content-h4-weight' ) )
				$css	.= GP_Pro_Builder::number_css( 'font-weight', $data['entry-content-h4-weight'] );

			if ( GP_Pro_Builder::build_check( $data, 'entry-content-h4-margin-bottom' ) )
				$css	.= GP_Pro_Builder::px_rem_css( 'margin-bottom', $data['entry-content-h4-margin-bottom'] );

			if ( GP_Pro_Builder::build_check( $data, 'entry-content-h4-padding-bottom' ) )
				$css	.= GP_Pro_Builder::px_rem_css( 'padding-bottom', $data['entry-content-h4-padding-bottom'] );

			if ( GP_Pro_Builder::build_check( $data, 'entry-content-h4-transform' ) )
				$css	.= GP_Pro_Builder::text_css( 'text-transform', $data['entry-content-h4-transform'] );

			if ( GP_Pro_Builder::build_check( $data, 'entry-content-h4-align' ) )
				$css	.= GP_Pro_Builder::text_css( 'text-align', $data['entry-content-h4-align'] );

		$css	.= '}'."\n";

		// H4 link standard
		$css	.= $class.' .entry-content h4 a { ';

			if ( GP_Pro_Builder::build_check( $data, 'entry-content-h4-color-link' ) )
				$css	.= GP_Pro_Builder::hexcolor_css( 'color', $data['entry-content-h4-color-link'] );

			if ( GP_Pro_Builder::build_check( $data, 'entry-content-h4-link-dec' ) )
				$css	.= GP_Pro_Builder::text_css( 'text-decoration', $data['entry-content-h4-link-dec'] );

			if ( GP_Pro_Builder::build_check( $data, 'entry-content-h4-link-dec' ) )
				$css	.= GP_Pro_Builder::text_css( 'border-bottom-style', 'none' );

		$css	.= '}'."\n";

		// H4 link hover / focus
		$css	.= $class.' .entry-content h4 a:hover, '.$class.' .entry-content h4 a:focus { ';

			if ( GP_Pro_Builder::build_check( $data, 'entry-content-h4-color-link-hov' ) )
				$css	.= GP_Pro_Builder::hexcolor_css( 'color', $data['entry-content-h4-color-link-hov'] );

			if ( GP_Pro_Builder::build_check( $data, 'entry-content-h4-link-dec-hov' ) )
				$css	.= GP_Pro_Builder::text_css( 'text-decoration', $data['entry-content-h4-link-dec-hov'] );

			if ( GP_Pro_Builder::build_check( $data, 'entry-content-h4-link-dec-hov' ) )
				$css	.= GP_Pro_Builder::text_css( 'border-bottom-style', 'none' );

		$css	.= '}'."\n";

		// H5 setup
		$css	.= $class.' .entry-content h5 { ';

			if ( GP_Pro_Builder::build_check( $data, 'entry-content-h5-color-text' ) )
				$css	.= GP_Pro_Builder::hexcolor_css( 'color', $data['entry-content-h5-color-text'] );

			if ( GP_Pro_Builder::build_check( $data, 'entry-content-h5-stack' ) )
				$css	.= GP_Pro_Builder::stack_css( 'font-family', $data['entry-content-h5-stack'] );

			if ( GP_Pro_Builder::build_check( $data, 'entry-content-h5-size' ) )
				$css	.= GP_Pro_Builder::px_rem_css( 'font-size', $data['entry-content-h5-size'] );

			if ( GP_Pro_Builder::build_check( $data, 'entry-content-h5-weight' ) )
				$css	.= GP_Pro_Builder::number_css( 'font-weight', $data['entry-content-h5-weight'] );

			if ( GP_Pro_Builder::build_check( $data, 'entry-content-h5-margin-bottom' ) )
				$css	.= GP_Pro_Builder::px_rem_css( 'margin-bottom', $data['entry-content-h5-margin-bottom'] );

			if ( GP_Pro_Builder::build_check( $data, 'entry-content-h5-padding-bottom' ) )
				$css	.= GP_Pro_Builder::px_rem_css( 'padding-bottom', $data['entry-content-h5-padding-bottom'] );

			if ( GP_Pro_Builder::build_check( $data, 'entry-content-h5-transform' ) )
				$css	.= GP_Pro_Builder::text_css( 'text-transform', $data['entry-content-h5-transform'] );

			if ( GP_Pro_Builder::build_check( $data, 'entry-content-h5-align' ) )
				$css	.= GP_Pro_Builder::text_css( 'text-align', $data['entry-content-h5-align'] );

		$css	.= '}'."\n";

		// H5 link standard
		$css	.= $class.' .entry-content h5 a { ';

			if ( GP_Pro_Builder::build_check( $data, 'entry-content-h5-color-link' ) )
				$css	.= GP_Pro_Builder::hexcolor_css( 'color', $data['entry-content-h5-color-link'] );

			if ( GP_Pro_Builder::build_check( $data, 'entry-content-h5-link-dec' ) )
				$css	.= GP_Pro_Builder::text_css( 'text-decoration', $data['entry-content-h5-link-dec'] );

			if ( GP_Pro_Builder::build_check( $data, 'entry-content-h5-link-dec' ) )
				$css	.= GP_Pro_Builder::text_css( 'border-bottom-style', 'none' );

		$css	.= '}'."\n";

		// H5 link hover / focus
		$css	.= $class.' .entry-content h5 a:hover, '.$class.' .entry-content h5 a:focus { ';

			if ( GP_Pro_Builder::build_check( $data, 'entry-content-h5-color-link-hov' ) )
				$css	.= GP_Pro_Builder::hexcolor_css( 'color', $data['entry-content-h5-color-link-hov'] );

			if ( GP_Pro_Builder::build_check( $data, 'entry-content-h5-link-dec-hov' ) )
				$css	.= GP_Pro_Builder::text_css( 'text-decoration', $data['entry-content-h5-link-dec-hov'] );

			if ( GP_Pro_Builder::build_check( $data, 'entry-content-h5-link-dec-hov' ) )
				$css	.= GP_Pro_Builder::text_css( 'border-bottom-style', 'none' );

		$css	.= '}'."\n";

		// H6 setup
		$css	.= $class.' .entry-content h6 { ';

			if ( GP_Pro_Builder::build_check( $data, 'entry-content-h6-color-text' ) )
				$css	.= GP_Pro_Builder::hexcolor_css( 'color', $data['entry-content-h6-color-text'] );

			if ( GP_Pro_Builder::build_check( $data, 'entry-content-h6-stack' ) )
				$css	.= GP_Pro_Builder::stack_css( 'font-family', $data['entry-content-h6-stack'] );

			if ( GP_Pro_Builder::build_check( $data, 'entry-content-h6-size' ) )
				$css	.= GP_Pro_Builder::px_rem_css( 'font-size', $data['entry-content-h6-size'] );

			if ( GP_Pro_Builder::build_check( $data, 'entry-content-h6-weight' ) )
				$css	.= GP_Pro_Builder::number_css( 'font-weight', $data['entry-content-h6-weight'] );

			if ( GP_Pro_Builder::build_check( $data, 'entry-content-h6-margin-bottom' ) )
				$css	.= GP_Pro_Builder::px_rem_css( 'margin-bottom', $data['entry-content-h6-margin-bottom'] );

			if ( GP_Pro_Builder::build_check( $data, 'entry-content-h6-padding-bottom' ) )
				$css	.= GP_Pro_Builder::px_rem_css( 'padding-bottom', $data['entry-content-h6-padding-bottom'] );

			if ( GP_Pro_Builder::build_check( $data, 'entry-content-h6-transform' ) )
				$css	.= GP_Pro_Builder::text_css( 'text-transform', $data['entry-content-h6-transform'] );

			if ( GP_Pro_Builder::build_check( $data, 'entry-content-h6-align' ) )
				$css	.= GP_Pro_Builder::text_css( 'text-align', $data['entry-content-h6-align'] );

		$css	.= '}'."\n";

		// H6 link standard
		$css	.= $class.' .entry-content h6 a { ';

			if ( GP_Pro_Builder::build_check( $data, 'entry-content-h6-color-link' ) )
				$css	.= GP_Pro_Builder::hexcolor_css( 'color', $data['entry-content-h6-color-link'] );

			if ( GP_Pro_Builder::build_check( $data, 'entry-content-h6-link-dec' ) )
				$css	.= GP_Pro_Builder::text_css( 'text-decoration', $data['entry-content-h6-link-dec'] );

			if ( GP_Pro_Builder::build_check( $data, 'entry-content-h6-link-dec' ) )
				$css	.= GP_Pro_Builder::text_css( 'border-bottom-style', 'none' );

		$css	.= '}'."\n";

		// H6 link hover / focus
		$css	.= $class.' .entry-content h6 a:hover, '.$class.' .entry-content h6 a:focus { ';

			if ( GP_Pro_Builder::build_check( $data, 'entry-content-h6-color-link-hov' ) )
				$css	.= GP_Pro_Builder::hexcolor_css( 'color', $data['entry-content-h6-color-link-hov'] );

			if ( GP_Pro_Builder::build_check( $data, 'entry-content-h6-link-dec-hov' ) )
				$css	.= GP_Pro_Builder::text_css( 'text-decoration', $data['entry-content-h6-link-dec-hov'] );

			if ( GP_Pro_Builder::build_check( $data, 'entry-content-h6-link-dec-hov' ) )
				$css	.= GP_Pro_Builder::text_css( 'border-bottom-style', 'none' );

		$css	.= '}'."\n";

		// p setup
		$css	.= $class.' .entry-content p { ';

			if ( GP_Pro_Builder::build_check( $data, 'entry-content-p-color-text' ) )
				$css	.= GP_Pro_Builder::hexcolor_css( 'color', $data['entry-content-p-color-text'] );

			if ( GP_Pro_Builder::build_check( $data, 'entry-content-p-stack' ) )
				$css	.= GP_Pro_Builder::stack_css( 'font-family', $data['entry-content-p-stack'] );

			if ( GP_Pro_Builder::build_check( $data, 'entry-content-p-size' ) )
				$css	.= GP_Pro_Builder::px_rem_css( 'font-size', $data['entry-content-p-size'] );

			if ( GP_Pro_Builder::build_check( $data, 'entry-content-p-weight' ) )
				$css	.= GP_Pro_Builder::number_css( 'font-weight', $data['entry-content-p-weight'] );

			if ( GP_Pro_Builder::build_check( $data, 'entry-content-p-margin-bottom' ) )
				$css	.= GP_Pro_Builder::px_rem_css( 'margin-bottom', $data['entry-content-p-margin-bottom'] );

			if ( GP_Pro_Builder::build_check( $data, 'entry-content-p-padding-bottom' ) )
				$css	.= GP_Pro_Builder::px_rem_css( 'padding-bottom', $data['entry-content-p-padding-bottom'] );

			if ( GP_Pro_Builder::build_check( $data, 'entry-content-p-transform' ) )
				$css	.= GP_Pro_Builder::text_css( 'text-transform', $data['entry-content-p-transform'] );

			if ( GP_Pro_Builder::build_check( $data, 'entry-content-p-align' ) )
				$css	.= GP_Pro_Builder::text_css( 'text-align', $data['entry-content-p-align'] );

		$css	.= '}'."\n";

		// p link standard
		$css	.= $class.' .entry-content p a { ';

			if ( GP_Pro_Builder::build_check( $data, 'entry-content-p-color-link' ) )
				$css	.= GP_Pro_Builder::hexcolor_css( 'color', $data['entry-content-p-color-link'] );

			if ( GP_Pro_Builder::build_check( $data, 'entry-content-p-link-dec' ) )
				$css	.= GP_Pro_Builder::text_css( 'text-decoration', $data['entry-content-p-link-dec'] );

			if ( GP_Pro_Builder::build_check( $data, 'entry-content-p-link-dec' ) )
				$css	.= GP_Pro_Builder::text_css( 'border-bottom-style', 'none' );

		$css	.= '}'."\n";

		// p link hover / focus
		$css	.= $class.' .entry-content p a:hover, '.$class.' .entry-content p a:focus { ';

			if ( GP_Pro_Builder::build_check( $data, 'entry-content-p-color-link-hov' ) )
				$css	.= GP_Pro_Builder::hexcolor_css( 'color', $data['entry-content-p-color-link-hov'] );

			if ( GP_Pro_Builder::build_check( $data, 'entry-content-p-link-dec-hov' ) )
				$css	.= GP_Pro_Builder::text_css( 'text-decoration', $data['entry-content-p-link-dec-hov'] );

			if ( GP_Pro_Builder::build_check( $data, 'entry-content-p-link-dec-hov' ) )
				$css	.= GP_Pro_Builder::text_css( 'border-bottom-style', 'none' );

		$css	.= '}'."\n";


		// ul setup
		$css	.= $class.' .entry-content ul { ';

			if ( GP_Pro_Builder::build_check( $data, 'entry-content-ul-color-text' ) )
				$css	.= GP_Pro_Builder::hexcolor_css( 'color', $data['entry-content-ul-color-text'] );

			if ( GP_Pro_Builder::build_check( $data, 'entry-content-ul-stack' ) )
				$css	.= GP_Pro_Builder::stack_css( 'font-family', $data['entry-content-ul-stack'] );

			if ( GP_Pro_Builder::build_check( $data, 'entry-content-ul-size' ) )
				$css	.= GP_Pro_Builder::px_rem_css( 'font-size', $data['entry-content-ul-size'] );

			if ( GP_Pro_Builder::build_check( $data, 'entry-content-ul-weight' ) )
				$css	.= GP_Pro_Builder::number_css( 'font-weight', $data['entry-content-ul-weight'] );

			if ( GP_Pro_Builder::build_check( $data, 'entry-content-ul-margin-left' ) )
				$css	.= GP_Pro_Builder::px_rem_css( 'margin-left', $data['entry-content-ul-margin-left'] );

			if ( GP_Pro_Builder::build_check( $data, 'entry-content-ul-margin-bottom' ) )
				$css	.= GP_Pro_Builder::px_rem_css( 'margin-bottom', $data['entry-content-ul-margin-bottom'] );

			if ( GP_Pro_Builder::build_check( $data, 'entry-content-ul-padding-left' ) )
				$css	.= GP_Pro_Builder::px_rem_css( 'padding-left', $data['entry-content-ul-padding-left'] );

			if ( GP_Pro_Builder::build_check( $data, 'entry-content-ul-padding-bottom' ) )
				$css	.= GP_Pro_Builder::px_rem_css( 'padding-bottom', $data['entry-content-ul-padding-bottom'] );

			if ( GP_Pro_Builder::build_check( $data, 'entry-content-ul-list-style' ) )
				$css	.= GP_Pro_Builder::text_css( 'list-style-type', $data['entry-content-ul-list-style'] );

			if ( GP_Pro_Builder::build_check( $data, 'entry-content-ul-transform' ) )
				$css	.= GP_Pro_Builder::text_css( 'text-transform', $data['entry-content-ul-transform'] );

			if ( GP_Pro_Builder::build_check( $data, 'entry-content-ul-align' ) )
				$css	.= GP_Pro_Builder::text_css( 'text-align', $data['entry-content-ul-align'] );


		$css	.= '}'."\n";

		// ul link standard
		$css	.= $class.' .entry-content ul a { ';

			if ( GP_Pro_Builder::build_check( $data, 'entry-content-ul-color-link' ) )
				$css	.= GP_Pro_Builder::hexcolor_css( 'color', $data['entry-content-ul-color-link'] );

			if ( GP_Pro_Builder::build_check( $data, 'entry-content-ul-link-dec' ) )
				$css	.= GP_Pro_Builder::text_css( 'text-decoration', $data['entry-content-ul-link-dec'] );

			if ( GP_Pro_Builder::build_check( $data, 'entry-content-ul-link-dec' ) )
				$css	.= GP_Pro_Builder::text_css( 'border-bottom-style', 'none' );

		$css	.= '}'."\n";

		// ul link hover / focus
		$css	.= $class.' .entry-content ul a:hover, '.$class.' .entry-content ul a:focus { ';

			if ( GP_Pro_Builder::build_check( $data, 'entry-content-ul-color-link-hov' ) )
				$css	.= GP_Pro_Builder::hexcolor_css( 'color', $data['entry-content-ul-color-link-hov'] );

			if ( GP_Pro_Builder::build_check( $data, 'entry-content-ul-link-dec-hov' ) )
				$css	.= GP_Pro_Builder::text_css( 'text-decoration', $data['entry-content-ul-link-dec-hov'] );

			if ( GP_Pro_Builder::build_check( $data, 'entry-content-ul-link-dec-hov' ) )
				$css	.= GP_Pro_Builder::text_css( 'border-bottom-style', 'none' );

		$css	.= '}'."\n";

		// ul list style
		$css	.= $class.' .entry-content ul li { ';

			if ( GP_Pro_Builder::build_check( $data, 'entry-content-ul-list-style' ) )
				$css	.= GP_Pro_Builder::text_css( 'list-style-type', $data['entry-content-ul-list-style'] );

		$css	.= '}'."\n";

		// ol setup
		$css	.= $class.' .entry-content ol { ';

			if ( GP_Pro_Builder::build_check( $data, 'entry-content-ol-color-text' ) )
				$css	.= GP_Pro_Builder::hexcolor_css( 'color', $data['entry-content-ol-color-text'] );

			if ( GP_Pro_Builder::build_check( $data, 'entry-content-ol-stack' ) )
				$css	.= GP_Pro_Builder::stack_css( 'font-family', $data['entry-content-ol-stack'] );

			if ( GP_Pro_Builder::build_check( $data, 'entry-content-ol-size' ) )
				$css	.= GP_Pro_Builder::px_rem_css( 'font-size', $data['entry-content-ol-size'] );

			if ( GP_Pro_Builder::build_check( $data, 'entry-content-ol-weight' ) )
				$css	.= GP_Pro_Builder::number_css( 'font-weight', $data['entry-content-ol-weight'] );

			if ( GP_Pro_Builder::build_check( $data, 'entry-content-ol-margin-left' ) )
				$css	.= GP_Pro_Builder::px_rem_css( 'margin-left', $data['entry-content-ol-margin-left'] );

			if ( GP_Pro_Builder::build_check( $data, 'entry-content-ol-margin-bottom' ) )
				$css	.= GP_Pro_Builder::px_rem_css( 'margin-bottom', $data['entry-content-ol-margin-bottom'] );

			if ( GP_Pro_Builder::build_check( $data, 'entry-content-ol-padding-left' ) )
				$css	.= GP_Pro_Builder::px_rem_css( 'padding-left', $data['entry-content-ol-padding-left'] );

			if ( GP_Pro_Builder::build_check( $data, 'entry-content-ol-padding-bottom' ) )
				$css	.= GP_Pro_Builder::px_rem_css( 'padding-bottom', $data['entry-content-ol-padding-bottom'] );

			if ( GP_Pro_Builder::build_check( $data, 'entry-content-ol-transform' ) )
				$css	.= GP_Pro_Builder::text_css( 'text-transform', $data['entry-content-ol-transform'] );

			if ( GP_Pro_Builder::build_check( $data, 'entry-content-ol-align' ) )
				$css	.= GP_Pro_Builder::text_css( 'text-align', $data['entry-content-ol-align'] );

		$css	.= '}'."\n";

		// ol link standard
		$css	.= $class.' .entry-content ol a { ';

			if ( GP_Pro_Builder::build_check( $data, 'entry-content-ol-color-link' ) )
				$css	.= GP_Pro_Builder::hexcolor_css( 'color', $data['entry-content-ol-color-link'] );

			if ( GP_Pro_Builder::build_check( $data, 'entry-content-ol-link-dec' ) )
				$css	.= GP_Pro_Builder::text_css( 'text-decoration', $data['entry-content-ol-link-dec'] );

			if ( GP_Pro_Builder::build_check( $data, 'entry-content-ol-link-dec' ) )
				$css	.= GP_Pro_Builder::text_css( 'border-bottom-style', 'none' );

		$css	.= '}'."\n";

		// ol link hover / focus
		$css	.= $class.' .entry-content ol a:hover, '.$class.' .entry-content ol a:focus { ';

			if ( GP_Pro_Builder::build_check( $data, 'entry-content-ol-color-link-hov' ) )
				$css	.= GP_Pro_Builder::hexcolor_css( 'color', $data['entry-content-ol-color-link-hov'] );

			if ( GP_Pro_Builder::build_check( $data, 'entry-content-ol-link-dec-hov' ) )
				$css	.= GP_Pro_Builder::text_css( 'text-decoration', $data['entry-content-ol-link-dec-hov'] );

			if ( GP_Pro_Builder::build_check( $data, 'entry-content-ol-link-dec-hov' ) )
				$css	.= GP_Pro_Builder::text_css( 'border-bottom-style', 'none' );

		$css	.= '}'."\n";

		// ol list style
		$css	.= $class.' .entry-content ol li { ';

			if ( GP_Pro_Builder::build_check( $data, 'entry-content-ol-list-style' ) )
				$css	.= GP_Pro_Builder::text_css( 'list-style-type', $data['entry-content-ol-list-style'] );

		$css	.= '}'."\n";

		// image caption setup
		$css	.= $class.' .entry-content .wp-caption-text { ';

			if ( GP_Pro_Builder::build_check( $data, 'entry-content-cap-color-text' ) )
				$css	.= GP_Pro_Builder::hexcolor_css( 'color', $data['entry-content-cap-color-text'] );

			if ( GP_Pro_Builder::build_check( $data, 'entry-content-cap-stack' ) )
				$css	.= GP_Pro_Builder::stack_css( 'font-family', $data['entry-content-cap-stack'] );

			if ( GP_Pro_Builder::build_check( $data, 'entry-content-cap-size' ) )
				$css	.= GP_Pro_Builder::px_rem_css( 'font-size', $data['entry-content-cap-size'] );

			if ( GP_Pro_Builder::build_check( $data, 'entry-content-cap-weight' ) )
				$css	.= GP_Pro_Builder::number_css( 'font-weight', $data['entry-content-cap-weight'] );

			if ( GP_Pro_Builder::build_check( $data, 'entry-content-cap-margin-bottom' ) )
				$css	.= GP_Pro_Builder::px_rem_css( 'margin-bottom', $data['entry-content-cap-margin-bottom'] );

			if ( GP_Pro_Builder::build_check( $data, 'entry-content-cap-padding-bottom' ) )
				$css	.= GP_Pro_Builder::px_rem_css( 'padding-bottom', $data['entry-content-cap-padding-bottom'] );

			if ( GP_Pro_Builder::build_check( $data, 'entry-content-cap-transform' ) )
				$css	.= GP_Pro_Builder::text_css( 'text-transform', $data['entry-content-cap-transform'] );

			if ( GP_Pro_Builder::build_check( $data, 'entry-content-cap-align' ) )
				$css	.= GP_Pro_Builder::text_css( 'text-align', $data['entry-content-cap-align'] );

		$css	.= '}'."\n";

		// caption link standard
		$css	.= $class.' .entry-content .wp-caption-text a { ';

			if ( GP_Pro_Builder::build_check( $data, 'entry-content-cap-color-link' ) )
				$css	.= GP_Pro_Builder::hexcolor_css( 'color', $data['entry-content-cap-color-link'] );

			if ( GP_Pro_Builder::build_check( $data, 'entry-content-cap-link-dec' ) )
				$css	.= GP_Pro_Builder::text_css( 'text-decoration', $data['entry-content-cap-link-dec'] );

			if ( GP_Pro_Builder::build_check( $data, 'entry-content-cap-link-dec' ) )
				$css	.= GP_Pro_Builder::text_css( 'border-bottom-style', 'none' );

		$css	.= '}'."\n";

		// caption link hover / focus
		$css	.= $class.' .entry-content .wp-caption-text a:hover, '.$class.' .entry-content .wp-caption-text a:focus { ';

			if ( GP_Pro_Builder::build_check( $data, 'entry-content-cap-color-link-hov' ) )
				$css	.= GP_Pro_Builder::hexcolor_css( 'color', $data['entry-content-cap-color-link-hov'] );

			if ( GP_Pro_Builder::build_check( $data, 'entry-content-cap-link-dec-hov' ) )
				$css	.= GP_Pro_Builder::text_css( 'text-decoration', $data['entry-content-cap-link-dec-hov'] );

			if ( GP_Pro_Builder::build_check( $data, 'entry-content-cap-link-dec-hov' ) )
				$css	.= GP_Pro_Builder::text_css( 'border-bottom-style', 'none' );

		$css	.= '}'."\n";

		// code blocks
		$css	.= $class.' .entry-content code { ';

			if ( GP_Pro_Builder::build_check( $data, 'entry-content-code-color-text' ) )
				$css	.= GP_Pro_Builder::hexcolor_css( 'color', $data['entry-content-code-color-text'] );

			if ( GP_Pro_Builder::build_check( $data, 'entry-content-code-background' ) )
				$css	.= GP_Pro_Builder::hexcolor_css( 'background-color', $data['entry-content-code-background'] );

			if ( GP_Pro_Builder::build_check( $data, 'entry-content-code-stack' ) )
				$css	.= GP_Pro_Builder::stack_css( 'font-family', $data['entry-content-code-stack'] );

			if ( GP_Pro_Builder::build_check( $data, 'entry-content-code-size' ) )
				$css	.= GP_Pro_Builder::px_rem_css( 'font-size', $data['entry-content-code-size'] );

			if ( GP_Pro_Builder::build_check( $data, 'entry-content-code-weight' ) )
				$css	.= GP_Pro_Builder::number_css( 'font-weight', $data['entry-content-code-weight'] );

		$css	.= '}'."\n";

		// send all the CSS back
		return $css;

	}

/// end class
}

// Instantiate our class
$GP_Pro_Entry_Content = GP_Pro_Entry_Content::getInstance();

