<?php namespace Lean\Metadata;

/**
 * A suite of utils functions for the metadata package.
 */
class Utils {
	/**
	 * Get text trimmed to the nearest word.
	 *
	 * @param string $text 		 The text to trim.
	 * @param int    $char_limit The maximum chars to allow.
	 * @return string
	 */
	public static function trim_to_nearest_word( $text, $char_limit ) {
		if ( strlen( $text ) <= $char_limit ) {
			return $text;
		}

		$wrapped_text = explode( '\n', wordwrap( $text , $char_limit, '\n' ) );

		return is_array( $wrapped_text ) ? $wrapped_text[0] : substr( $text, 0, $char_limit );
	}
}
