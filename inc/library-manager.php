<?php
namespace Demo\Elementor;

use Elementor\Core\Common\Modules\Ajax\Module as Ajax;

defined('ABSPATH') || die();

class Library_Manager {

	protected static $source = null;

	public static $instance;

    public static function instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
			self::$instance->init();
		}
		return self::$instance;
	}

	public static function init() {
		add_action( 'elementor/editor/footer', [ __CLASS__, 'print_template_views' ] );
		add_action('elementor/editor/after_enqueue_scripts', [__CLASS__, 'enqueue_assets']);
		add_action( 'elementor/ajax/register_actions', [ __CLASS__, 'register_ajax_actions' ] );
	}

	public static function wp_elementor(){
		return \Elementor\Plugin::instance();
	}

	public static function print_template_views() {
		// echo 'templatedo';
		include_once WP_TEMPLATES_PATH . '/templates/template-library/templates.php';
	}

	public static function enqueue_assets() {
		wp_register_style(
			'wptemplates-templates-library',
			WP_TEMPLATES_ASSETS . '/admin/css/template-library.css',
			[
				'elementor-editor',
			],
			WP_TEMPLATES_VERSION.time()
		);

		wp_enqueue_script(
			'wptemplates-templates-library',
			WP_TEMPLATES_ASSETS . '/admin/js/template-library.js',
			[
				'elementor-editor',
				'jquery-hover-intent',
			],
			WP_TEMPLATES_VERSION.time(),
			true
		);
		$data = '
		.elementor-add-new-section{
			display: inline-flex !important;
			flex-wrap: wrap;
			align-items: center;
			justify-content: center;
		}
		.elementor-add-section-drag-title{
			flex-basis: 100%;
		}
		.elementor-add-new-section .elementor-add-ha-button {
			background-color: #5636d1;
			margin-left: 5px;
			font-size: 20px;
		}
		';
		wp_add_inline_style('wptemplates-templates-library', $data);
	}

	/**
	 * Undocumented function
	 *
	 * @return Library_Source
	 */
	public static function get_source() {
		if ( is_null( self::$source ) ) {
			self::$source = new Library_Source();
		}

		return self::$source;
	}

	public static function register_ajax_actions( Ajax $ajax ) {
		$ajax->register_ajax_action( 'get_ha_library_data', function( $data ) {
			if ( ! current_user_can( 'edit_posts' ) ) {
				throw new \Exception( 'Access Denied' );
			}

			if ( ! empty( $data['editor_post_id'] ) ) {
				$editor_post_id = absint( $data['editor_post_id'] );

				if ( ! get_post( $editor_post_id ) ) {
					throw new \Exception( __( 'Post not found.', 'happy-elementor-addons' ) );
				}

				self::wp_elementor()->db->switch_to_post( $editor_post_id );
			}

			$result = self::get_library_data( $data );

			return $result;
		} );

		$ajax->register_ajax_action( 'get_ha_template_data', function( $data ) {
			if ( ! current_user_can( 'edit_posts' ) ) {
				throw new \Exception( 'Access Denied' );
			}

			if ( ! empty( $data['editor_post_id'] ) ) {
				$editor_post_id = absint( $data['editor_post_id'] );

				if ( ! get_post( $editor_post_id ) ) {
					throw new \Exception( __( 'Post not found', 'happy-elementor-addons' ) );
				}

				self::wp_elementor()->db->switch_to_post( $editor_post_id );
			}

			if ( empty( $data['template_id'] ) ) {
				throw new \Exception( __( 'Template id missing', 'happy-elementor-addons' ) );
			}

			$result = self::get_template_data( $data );

			return $result;
		} );
	}

	public static function get_template_data( array $args ) {
		$source = self::get_source();
		$data = $source->get_data( $args );
		return $data;
	}

	/**
	 * Get library data from cache or remote
	 *
	 * type_tags has been added in version 2.15.0
	 *
	 * @param array $args
	 *
	 * @return array
	 */
	public static function get_library_data( array $args ) {
		$source = self::get_source();

		if ( ! empty( $args['sync'] ) ) {
			Library_Source::get_library_data( true );
		}

		return [
			'templates' => $source->get_items(),
			'tags'      => $source->get_tags(),
			'type_tags' => $source->get_type_tags(),
		];
	}
}

Library_Manager::instance();
