<?php namespace Lean\Utils\Meta;

/**
 * A suite of functions for working with a collection's metadata.
 * Uses data entered via the Yoast SEO plugin's UI by default, with a suitable fallback.
 *
 * Class Collection.
 *
 * @package Lean\Utils
 */
class Collection {

	const TITLE_FILTER = 'ln_utils_meta_collection_%s_title';

	/**
	 * Get all metadata for a collection.
	 *
	 * @param \WP_Post $lead_post The lead post from which to take the metadata.
	 * @return array
	 */
	public static function get_all_collection_meta( $lead_post ) {
		$title = self::get_collection_title( $lead_post->post_type );
		$tags = [
			[ 'name' => 'description',			'content' => Post::get_post_meta_description( $lead_post ) ],
			[ 'property' => 'og:locale',		'content' => get_locale() ],
			[ 'property' => 'og:type',			'content' => 'summary' ],
			[ 'property' => 'og:title',			'content' => $title ],
			[ 'property' => 'og:description',	'content' => Post::get_post_og_description( $lead_post ) ],
			[ 'property' => 'og:url',			'content' => get_permalink( $lead_post->ID ) ],
			[ 'property' => 'og:site_name',		'content' => get_bloginfo( 'title' ) ],
			[ 'name' => 'twitter:card',			'content' => 'summary' ],
			[ 'name' => 'twitter:title',		'content' => $title ],
			[ 'name' => 'twitter:description',	'content' => Post::get_post_twitter_description( $lead_post ) ],
		];
		$tags = array_merge( $tags, Site::webmaster_tools() );
		return [
			'title' => $title,
			'tags' => $tags,
		];
	}

	/**
	 * Get the title for a collection.
	 *
	 * @param string $post_type The post type.
	 * @return mixed
	 */
	public static function get_collection_title( $post_type ) {
		$post_type_name = 'Blog';

		if ( 'post' !== $post_type ) {
			$type = get_post_type_object( $post_type );
			$post_type_name = $type->labels->name;
		}

		$filter_name = sprintf(
			self::TITLE_FILTER,
			is_string( $post_type ) ? $post_type : $post_type_name
		);
		$collection_title = sprintf(
			'%s - %s',
			$post_type_name, get_bloginfo( 'title' )
		);
		return apply_filters( $filter_name, $collection_title );
	}
}
