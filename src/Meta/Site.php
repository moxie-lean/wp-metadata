<?php namespace Lean\Utils\Meta;

/**
 * General data from the site:
 */
class Site {
	/**
	 * Services with each value used on the meta description based on the name
	 * of each option.
	 *
	 * @var array
	 */
	static $services = [
		'alexaverify'	=> 'alexaVerifyID',
		'googleverify'	=> 'google-site-verification',
		'msverify'		=> 'msvalidate.01',
		'yandexverify'	=> 'yandex-verification',
	];

	public static function webmaster_tools() {
		$options = [];
		if ( defined( 'WPSEO_VERSION' ) ) {
			$options = self::get_options();
		}
		return self::get_verification_data( $options );
	}

	protected static function get_verification_data( $services ) {
		$data = [];
		foreach ( $services as $name => $value ) {
			if ( self::is_valid( $name, $value ) ) {
				$data[] = [
					'name' => self::$services[ $name ],
					'content' => $value,
				];
			}
		}
		return $data;
	}

	protected static function is_valid( $name, $value ) {
		return self::is_webmaster_tool( $name ) && ! empty( $value );
	}

	protected static function get_options() {
		return get_option( 'wpseo', [] );
	}

	protected static function is_webmaster_tool( $name = '', $tool = 'verify' ) {
		return strpos( $name, $tool ) !== false;
	}
}
