<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views;

use Org\Wplake\Advanced_Views\Cards\Cpt\Cards_Cpt;
use Org\Wplake\Advanced_Views\Dashboard\Dashboard;
use Org\Wplake\Advanced_Views\Groups\Card_Data;
use Org\Wplake\Advanced_Views\Groups\View_Data;
use Org\Wplake\Advanced_Views\Parents\Hooks_Interface;
use Org\Wplake\Advanced_Views\Views\Cpt\Views_Cpt;

defined( 'ABSPATH' ) || exit;

class Plugin implements Hooks_Interface {
	const DOCS_URL          = 'https://docs.acfviews.com/';
	const PRO_VERSION_URL   = 'https://wplake.org/advanced-views-pro/';
	const PRO_PRICING_URL   = 'https://wplake.org/advanced-views-pro/#pricing';
	const BASIC_VERSION_URL = 'https://wplake.org/advanced-views-lite/';
	const SURVEY_URL        = 'https://forms.gle/Wjb16B4mzgLEQvru6';
	const CONFLICTS_URL     = 'https://docs.acfviews.com/troubleshooting/compatibility#conflicts';

	private string $slug       = 'acf-views/acf-views.php';
	private string $short_slug = 'acf-views';
	private string $version;
	private bool $is_pro_version = false;
	private bool $is_switching_versions;
	private string $plugin_url;
	private string $plugin_path;

	private Options $options;
	private Settings $settings;

	public function __construct( string $main_file, Options $options, Settings $settings ) {
		$this->plugin_url            = plugin_dir_url( $main_file );
		$this->plugin_path           = plugin_dir_path( $main_file );
		$this->version               = $this->detect_plugin_version_number( $main_file );
		$this->options               = $options;
		$this->settings              = $settings;
		$this->is_switching_versions = false;
	}

	// static, as called also in AcfGroup.
	public static function is_acf_pro_plugin_available(): bool {
		return class_exists( 'acf_pro' );
	}

	public static function get_theme_text_domain(): string {
		/**
		 * @var string|false $theme_text_domain
		 */
		$theme_text_domain = wp_get_theme()->get( 'TextDomain' );

		return is_string( $theme_text_domain ) ?
			$theme_text_domain :
			'';
	}

	public static function get_label_translation( string $label, string $text_domain = '' ): string {
		$text_domain = '' !== $text_domain ?
			$text_domain :
			self::get_theme_text_domain();

		// escape quotes to keep compatibility with the generated translation file
		// (quotes there escaped to prevent breaking the PHP string).
		$label = str_replace( "'", '&#039;', $label );
		$label = str_replace( '"', '&quot;', $label );

		// phpcs:ignore
		$translation = __( $label, $text_domain );

		$translation = str_replace( '&#039;', "'", $translation );
		$translation = str_replace( '&quot;', '"', $translation );

		return $translation;
	}

	/**
	 * @param array<string,mixed> $field
	 *
	 * @return array<string,mixed>
	 */
	protected function amend_pro_field_label_and_instruction( array $field ): array {
		$is_pro_field      = key_exists( 'a-pro', $field ) &&
							$this->is_pro_field_locked();
		$is_acf_pro_field  = true === key_exists( 'a-acf-pro', $field ) &&
							false === $this->is_acf_plugin_available( true );
		$is_mb_block_field = true === key_exists( 'a-mb-blocks', $field ) &&
							false === defined( 'MB_BLOCKS_VER' );

		if ( ! $is_pro_field &&
			! $is_acf_pro_field ) {
			return $field;
		}

		$type           = $field['type'] ?? '';
		$field['label'] = $field['label'] ?? '';

		$instructions = key_exists( 'instructions', $field ) &&
						is_string( $field['instructions'] ) ?
			$field['instructions'] :
			'';

		$field['instructions'] = $instructions;

		if ( $is_pro_field ) {
			$field['label'] = $field['label'] . ' (Pro)';
			if ( 'tab' !== $type ) {
				$label                 = ! $this->is_pro_version() ?
					__( 'Upgrade to Pro', 'acf-views' ) :
					__( 'Activate your license', 'acf-views' );
				$link                  = ! $this->is_pro_version() ?
					self::PRO_VERSION_URL :
					$this->get_admin_url( Dashboard::PAGE_PRO );
				$field['instructions'] = sprintf(
					'<a href="%s" target="_blank">%s</a> %s %s',
					$link,
					$label,
					__( 'to unlock.', 'acf-views' ),
					'<br>' . $field['instructions']
				);
			}
		}

		if ( $is_acf_pro_field ) {
			$field['instructions'] = sprintf(
				'(<a href="%s" target="_blank">%s</a> %s) %s',
				'https://www.advancedcustomfields.com/pro/',
				__( 'ACF Pro', 'acf-views' ),
				__( 'version is required for this feature', 'acf-views' ),
				$field['instructions']
			);
		}

		if ( $is_mb_block_field ) {
			$field['instructions'] = sprintf(
				'(<a href="%s" target="_blank">%s</a> %s) %s',
				'https://metabox.io/plugins/mb-blocks/',
				__( 'MB Blocks', 'acf-views' ),
				__( 'extension is required for this feature', 'acf-views' ),
				$field['instructions']
			);
		}

		return $field;
	}

	/**
	 * @param array<string,mixed> $field
	 *
	 * @return array<string,mixed>
	 */
	protected function add_deprecated_field_class( array $field ): array {
		if ( false === key_exists( 'a-deprecated', $field ) ) {
			return $field;
		}

		if ( false === key_exists( 'wrapper', $field ) ||
			false === is_array( $field['wrapper'] ) ) {
			$field['wrapper'] = array();
		}

		if ( ! key_exists( 'class', $field['wrapper'] ) ) {
			$field['wrapper']['class'] = '';
		}

		$field['wrapper']['class'] .= ' acf-field--deprecated';

		return $field;
	}

	protected function detect_plugin_version_number( string $plugin_file ): string {
		// @phpcs:ignore
		$plugin_file_content = (string)file_get_contents($plugin_file);

		preg_match( '/Version:(.*)/', $plugin_file_content, $matches );

		$current_version_number = $matches[1] ?? '1.0.0';

		return trim( $current_version_number );
	}

	protected function print_opcache_compatibility_warning(): void {
		$detected_message = __(
			'Compatibility issue detected! "Advanced Views" plugin requires "PHPDoc" comments in code.',
			'acf-views'
		);
		$action_message   = __(
			'Please change the "opcache.save_comments" option in your php.ini file to the default value of "1" on your hosting.',
			'acf-views'
		);
		printf(
			'<div class="notice notice-error"><p>%s 
<br>%s <a target="_blank" href="%s">%s</a>
</p></div>',
			esc_html( $detected_message ),
			esc_html( $action_message ),
			esc_url( self::CONFLICTS_URL ),
			esc_html( __( 'Read more', 'acf-views' ) ),
		);
	}

	protected function get_plugin_url(): string {
		return $this->plugin_url;
	}

	protected function get_plugin_path(): string {
		return $this->plugin_path;
	}

	public function is_pro_field_locked(): bool {
		return false === $this->is_pro_version() ||
				false === $this->settings->is_active_license();
	}

	public function get_name(): string {
		return __( 'Advanced Views Lite', 'acf-views' );
	}

	public function get_slug(): string {
		return $this->slug;
	}

	public function get_short_slug(): string {
		return $this->short_slug;
	}

	public function get_version(): string {
		return $this->version;
	}

	public function is_pro_version(): bool {
		return $this->is_pro_version;
	}

	public function get_assets_url( string $file ): string {
		return $this->plugin_url . 'src/Assets/' . $file;
	}

	public function get_assets_path( string $file ): string {
		return $this->plugin_path . 'src/Assets/' . $file;
	}

	public function get_acf_internal_assets_url( string $file ): string {
		return $this->plugin_url . 'vendor/acf-internal-features/assets/' . $file;
	}

	public function is_acf_plugin_available( bool $is_pro_only = false ): bool {
		// don't use 'is_plugin_active()' as the function available lately.
		return static::is_acf_pro_plugin_available() ||
				( ! $is_pro_only && class_exists( 'ACF' ) );
	}

	public function maybe_show_compatibility_warnings(): void {
		if ( true === function_exists( 'ini_get' ) &&
			'0' === ini_get( 'opcache.save_comments' ) ) {
			$this->print_opcache_compatibility_warning();
		}
	}

	/**
	 * @param string[] $target_base
	 */
	public function is_cpt_screen( string $scpt_name, array $target_base = array( 'post', 'add' ) ): bool {
		$current_screen = get_current_screen();

		if ( null === $current_screen ) {
			return false;
		}

		$is_target_post = in_array( $current_screen->id, array( $scpt_name ), true ) ||
							in_array( $current_screen->post_type, array( $scpt_name ), true );

		// base = edit (list management), post (editing), add (adding).
		return $is_target_post &&
				in_array( $current_screen->base, $target_base, true );
	}

	public function deactivate_other_instances( string $activated_plugin ): void {
		if ( ! in_array(
			$activated_plugin,
			array( 'acf-views/acf-views.php', 'acf-views-pro/acf-views-pro.php' ),
			true
		) ) {
			return;
		}

		$plugin_to_deactivate  = 'acf-views/acf-views.php';
		$deactivated_notice_id = 1;

		// If we just activated the free version, deactivate the pro version.
		if ( $activated_plugin === $plugin_to_deactivate ) {
			$plugin_to_deactivate  = 'acf-views-pro/acf-views-pro.php';
			$deactivated_notice_id = 2;
		}

		if ( is_multisite() &&
			is_network_admin() ) {
			$active_plugins = (array) get_site_option( 'active_sitewide_plugins', array() );
			$active_plugins = array_keys( $active_plugins );
		} else {
			$active_plugins = (array) get_option( 'active_plugins', array() );
		}

		foreach ( $active_plugins as $plugin_basename ) {
			if ( $plugin_to_deactivate !== $plugin_basename ) {
				continue;
			}

			$this->options->set_transient(
				Options::TRANSIENT_DEACTIVATED_OTHER_INSTANCES,
				$deactivated_notice_id,
				1 * HOUR_IN_SECONDS
			);
			// flag that allows to detect this switching. E.g. Twig won't remove the templates dir.
			$this->is_switching_versions = true;

			deactivate_plugins( $plugin_basename );

			return;
		}
	}

	// notice when either Basic or Pro was automatically deactivated.
	public function show_plugin_deactivated_notice(): void {
		$deactivate_notice_id = $this->options->get_transient( Options::TRANSIENT_DEACTIVATED_OTHER_INSTANCES );
		$deactivate_notice_id = is_numeric( $deactivate_notice_id ) ?
			(int) $deactivate_notice_id :
			0;

		// not set = false = 0.
		if ( ! in_array( $deactivate_notice_id, array( 1, 2 ), true ) ) {
			return;
		}

		$message = sprintf(
			'%s "%s".',
			__(
				"'Advanced Views Lite' and 'Advanced Views Pro' should not be active at the same time. We've automatically deactivated",
				'acf-views'
			),
			1 === $deactivate_notice_id ?
				__( 'Advanced Views Lite', 'acf-views' ) :
				__( 'Advanced Views Pro', 'acf-views' )
		);

		$this->options->delete_transient( Options::TRANSIENT_DEACTIVATED_OTHER_INSTANCES );

		printf(
			'<div class="notice notice-warning">' .
			'<p>%s</p>' .
			'</div>',
			esc_html( $message )
		);
	}

	/**
	 * @param array<string,mixed> $field
	 *
	 * @return array<string,mixed>
	 */
	public function amend_field_settings( array $field ): array {
		$field = $this->amend_pro_field_label_and_instruction( $field );
		$field = $this->add_deprecated_field_class( $field );

		return $field;
	}

	/**
	 * @param array<string,mixed> $field
	 *
	 * @return array<string,mixed>
	 */
	public function set_global_defaults_for_field( array $field ): array {
		$field_name = $field['key'] ?? '';

		switch ( $field_name ) {
			case View_Data::getAcfFieldName( View_Data::FIELD_TEMPLATE_ENGINE ):
			case Card_Data::getAcfFieldName( Card_Data::FIELD_TEMPLATE_ENGINE ):
				$field['value'] = $this->settings->get_template_engine();
				break;
			case View_Data::getAcfFieldName( View_Data::FIELD_WEB_COMPONENT ):
			case Card_Data::getAcfFieldName( Card_Data::FIELD_WEB_COMPONENT ):
				$web_components_type = $this->settings->get_web_components_type();

				if ( '' !== $web_components_type ) {
					$field['value'] = $web_components_type;
				}
				break;
			case View_Data::getAcfFieldName( View_Data::FIELD_CLASSES_GENERATION ):
			case Card_Data::getAcfFieldName( Card_Data::FIELD_CLASSES_GENERATION ):
				$field['value'] = $this->settings->get_classes_generation();
				break;
			case View_Data::getAcfFieldName( View_Data::FIELD_SASS_CODE ):
			case Card_Data::getAcfFieldName( Card_Data::FIELD_SASS_CODE ):
				$field['value'] = $this->settings->get_sass_template();
				break;
			case View_Data::getAcfFieldName( View_Data::FIELD_TS_CODE ):
			case Card_Data::getAcfFieldName( Card_Data::FIELD_TS_CODE ):
				$field['value'] = $this->settings->get_ts_template();
				break;
		}

		return $field;
	}

	/**
	 * @param array<string,mixed> $wrapper
	 * @param array<string,mixed> $field
	 *
	 * @return array<string,mixed>
	 */
	public function add_class_to_admin_pro_field_classes( array $wrapper, array $field ): array {
		$is_pro_field       = key_exists( 'a-pro', $field ) &&
								$this->is_pro_field_locked();
		$is_acf_pro_field   = true === key_exists( 'a-acf-pro', $field ) &&
								false === $this->is_acf_plugin_available( true );
		$is_mb_blocks_field = true === key_exists( 'a-mb-blocks', $field ) &&
								false === defined( 'MB_BLOCKS_VER' );

		if ( false === $is_pro_field &&
			false === $is_acf_pro_field &&
			false === $is_mb_blocks_field ) {
			return $wrapper;
		}

		if ( ! key_exists( 'class', $wrapper ) ) {
			$wrapper['class'] = '';
		}

		$wrapper['class'] .= ' acf-views-pro';

		return $wrapper;
	}

	public function get_admin_url(
		string $page = '',
		string $cpt_name = Views_Cpt::NAME,
		string $base = 'edit.php'
	): string {
		$page_arg = '' !== $page ?
			'&page=' . $page :
			'';

		// don't use just '/wp-admin/x' as some websites can have custom admin url, like 'wp.org/wordpress/wp-admin'.
		$page_url = get_admin_url( null, $base . '?post_type=' );

		return $page_url . $cpt_name . $page_arg;
	}

	public function is_switching_versions(): bool {
		return $this->is_switching_versions;
	}

	// for some reason, ACF ajax form validation doesn't work on the wordpress.com hosting. So need to use a special approach.
	public function is_wordpress_com_hosting(): bool {
		return defined( 'WPCOMSH_VERSION' ) ||
				defined( 'WPCOM_CORE_ATOMIC_PLUGINS' );
	}

	public function set_hooks( Current_Screen $current_screen ): void {
		if ( false === $current_screen->is_admin() ) {
			return;
		}

		add_action( 'admin_notices', array( $this, 'maybe_show_compatibility_warnings' ) );
		add_action( 'activated_plugin', array( $this, 'deactivate_other_instances' ) );
		add_action( 'pre_current_active_plugins', array( $this, 'show_plugin_deactivated_notice' ) );

		add_filter( 'acf/prepare_field', array( $this, 'amend_field_settings' ) );
		add_filter( 'acf/field_wrapper_attributes', array( $this, 'add_class_to_admin_pro_field_classes' ), 10, 2 );

		if ( true === $current_screen->is_admin_cpt_related( Views_Cpt::NAME, Current_Screen::CPT_ADD ) ||
			true === $current_screen->is_admin_cpt_related( Cards_Cpt::NAME, Current_Screen::CPT_ADD ) ) {
			add_filter( 'acf/prepare_field', array( $this, 'set_global_defaults_for_field' ) );
		}
	}
}
