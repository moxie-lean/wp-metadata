<?php namespace Lean\Metadata;

use Lean\Elements\Collection\SiteIdentity;
use Lean\Acf;

/**
 * A suite of functions for working with a post's metadata.
 * Uses data entered via the Yoast SEO plugin's UI by default, with a suitable fallback.
 *
 * Class Post.
 *
 * @package Lean\Utils
 */
class Post {

	const HOOK_PREFIX = 'lean_metadata_post_';

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
			[ 'name' => 'twitter:card',			'content' => self::get_twitter_card_type() ],
			[ 'name' => 'twitter:title',		'content' => self::get_post_twitter_title( $post ) ],
			[ 'name' => 'twitter:description',	'content' => self::get_post_twitter_description( $post ) ],
		];

		$og_image = self::get_post_og_image( $post );
		$twitter_image = self::get_post_twitter_image( $post );

		if ( ! empty( $og_image ) ) {
			$og_image_size = getimagesize( $og_image );

			$tags = array_merge( $tags, [
				[ 'property' => 'og:image',			'content' => $og_image ],
				[ 'property' => 'og:image:width',	'content' => $og_image_size[0] ],
				[ 'property' => 'og:image:height',	'content' => $og_image_size[1] ],
			] );
		}

		if ( ! empty( $twitter_image ) ) {
			$twitter_image_size = getimagesize( $twitter_image );

			$tags = array_merge( $tags, [
				[ 'name' => 'twitter:image',		'content' => $twitter_image ],
				[ 'name' => 'twitter:image:width',	'content' => $twitter_image_size[0] ],
				[ 'name' => 'twitter:image:height',	'content' => $twitter_image_size[1] ],
			] );
		}

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
			$description = Utils::trim_to_nearest_word( wp_strip_all_tags( $post->post_content ), 160 );
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
		$image = apply_filters(
			self::HOOK_PREFIX . 'og_image',
			get_post_meta( $post->ID, '_yoast_wpseo_opengraph-image', true ),
			$post
		);

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
		$image = apply_filters(
			self::HOOK_PREFIX . 'twitter_image',
			get_post_meta( $post->ID, '_yoast_wpseo_twitter-image', true ),
			$post
		);

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

	/**
	 * Get twitter card type.
	 *
	 * @return string
	 */
	public static function get_twitter_card_type() {
		$card = 'summary';
		$social = get_option( 'wpseo_social' );

		if ( ! empty( $social ) && ! empty( $social['twitter_card_type'] ) ) {
			$card = $social['twitter_card_type'];
		}

		return $card;
	}
}
