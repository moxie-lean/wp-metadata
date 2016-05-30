<?php namespace Lean\Utils\Meta;

use Lean\Elements\Collection\SiteIdentity;
use Lean\Acf;
use Lean\Utils\Text;

/**
 * A suite of functions for working with a post's metadata.
 * Uses data entered via the Yoast SEO plugin's UI by default, with a suitable fallback.
 *
 * Class Post.
 *
 * @package Lean\Utils
 */
class Post {
	/**
	 * Get all metadata for a post.
	 *
	 * @param \WP_Post $post The post.
	 * @return array
	 */
	public static function get_all_post_meta( $post ) {
		$tags = [
			[ 'name' => 'description',			'content' => self::get_post_meta_description( $post ) ],
			[ 'property' => 'og:locale',		'content' => get_locale() ],
			[ 'property' => 'og:type',			'content' => 'article' ],
			[ 'property' => 'og:title',			'content' => self::get_post_og_title( $post ) ],
			[ 'property' => 'og:description',	'content' => self::get_post_og_description( $post ) ],
			[ 'property' => 'og:url',			'content' => get_permalink( $post->ID ) ],
			[ 'property' => 'og:site_name',		'content' => get_bloginfo( 'title' ) ],
			[ 'property' => 'og:updated_time',	'content' => get_post_modified_time( 'c', true, $post ) ],
			[ 'property' => 'og:image',			'content' => self::get_post_og_image( $post ) ],
			[ 'name' => 'twitter:card',			'content' => 'summary' ],
			[ 'name' => 'twitter:title',		'content' => self::get_post_twitter_title( $post ) ],
			[ 'name' => 'twitter:description',	'content' => self::get_post_twitter_description( $post ) ],
			[ 'name' => 'twitter:image',		'content' => self::get_post_twitter_image( $post ) ],
		];
		$tags = array_merge( $tags, Site::webmaster_tools() );

		return [
			'title' => self::get_post_meta_title( $post ),
			'tags' => $tags,
		];
	}

	/**
	 * Get the post's meta title.
	 *
	 * @param \WP_Post $post The post.
	 * @return string
	 */
	public static function get_post_meta_title( $post ) {
		$title = get_post_meta( $post->ID, '_yoast_wpseo_title', true );

		if ( empty( $title ) ) {
			if ( (int) get_option( 'page_on_front' ) === $post->ID ) {
				$title = get_bloginfo( 'title' );
			} else {
				$title = $post->post_title . ' - ' . get_bloginfo( 'title' );
			}
		}

		return $title;
	}

	/**
	 * Get the post's meta description.
	 *
	 * @param \WP_Post $post The post.
	 * @return string
	 */
	public static function get_post_meta_description( $post ) {
		$description = get_post_meta( $post->ID, '_yoast_wpseo_metadesc', true );

		if ( empty( $description ) ) {
			$description = Text::trim_to_nearest_word( wp_strip_all_tags( $post->post_content ), 160 );
		}

		return $description;
	}

	/**
	 * Get the post's og title.
	 *
	 * @param \WP_Post $post The post.
	 * @return string
	 */
	public static function get_post_og_title( $post ) {
		$title = get_post_meta( $post->ID, '_yoast_wpseo_opengraph-title', true );

		if ( empty( $title ) ) {
			$title = self::get_post_meta_title( $post );
		}

		return $title;
	}

	/**
	 * Get the post's og description.
	 *
	 * @param \WP_Post $post The post.
	 * @return string
	 */
	public static function get_post_og_description( $post ) {
		$description = get_post_meta( $post->ID, '_yoast_wpseo_opengraph-description', true );

		if ( empty( $description ) ) {
			$description = self::get_post_meta_description( $post );
		}

		return $description;
	}

	/**
	 * Get the post's og image.
	 *
	 * @param \WP_Post $post The post.
	 * @return string
	 */
	public static function get_post_og_image( $post ) {
		$image = get_post_meta( $post->ID, '_yoast_wpseo_opengraph-image', true );

		if ( empty( $image ) ) {
			$image = self::get_fallback_image( $post );
		}

		return $image;
	}

	/**
	 * Get the post's twitter title.
	 *
	 * @param \WP_Post $post The post.
	 * @return string
	 */
	public static function get_post_twitter_title( $post ) {
		$title = get_post_meta( $post->ID, '_yoast_wpseo_twitter-title', true );

		if ( empty( $title ) ) {
			$title = self::get_post_meta_title( $post );
		}

		return $title;
	}

	/**
	 * Get the post's twitter description.
	 *
	 * @param \WP_Post $post The post.
	 * @return string
	 */
	public static function get_post_twitter_description( $post ) {
		$description = get_post_meta( $post->ID, '_yoast_wpseo_twitter-description', true );

		if ( empty( $description ) ) {
			$description = self::get_post_meta_description( $post );
		}

		return $description;
	}

	/**
	 * Get the post's twitter image.
	 *
	 * @param \WP_Post $post The post.
	 * @return string
	 */
	public static function get_post_twitter_image( $post ) {
		$image = get_post_meta( $post->ID, '_yoast_wpseo_twitter-image', true );

		if ( empty( $image ) ) {
			$image = self::get_fallback_image( $post );
		}

		return $image;
	}

	/**
	 * Get a fallback image for the post.
	 *
	 * @param \WP_Post $post The post.
	 * @return mixed
	 */
	private static function get_fallback_image( $post ) {
		$image = wp_get_attachment_url( get_post_thumbnail_id( $post->ID ) );

		if ( empty( $image ) ) {
			$logo = Acf::get_option_field( SiteIdentity::LOGO_KEY );
			$image = is_array( $logo ) ? $logo['src'] : get_site_icon_url();
		}

		return $image;
	}
}
