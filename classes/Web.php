<?php

namespace Alphred;

class http {

	// public function simple_download( $url, $destination = '', $mkdir = false ) {
	// 	// Function to download a URL easily.
	// 	$url = filter_var( $url, FILTER_SANITIZE_URL );
	// 	if ( empty( $destination ) ) {
	// 		return file_get_contents( $url ); }
	// 	else {
	// 		if ( file_exists( $destination ) && is_dir( $destination ) ) {
	// 			$destination = $destination . '/' . basename( parse_url( $url, PHP_URL_PATH ) );
	// 		}
	// 		file_put_contents( $destination, file_get_contents( $url ) );
	// 	}
	// 	return $destination;
	// }

	// public function get_favicon( $url, $destination = '', $cache = true, $ttl = 604800 ) {
	// 	$url = parse_url( $url );
	// 	$domain = $url['host'];
	// 	if ( $cache && $file = $this->cache( "{$domain}.png", 'favicons', $ttl ) ) {
	// 		return $file;
	// 	}
	// 	$favicon = file_get_contents( "https://www.google.com/s2/favicons?domain={$domain}" );
	// 	if ( empty( $destination ) ) {
	// 		$destination = Globals::get( 'alfred_workflow_cache' ) . '/favicons';
	// 	}
	// 	if ( ! file_exists( $destination ) && substr( $destination, -4 ) !== '.png' ) {
	// 		mkdir( $destination, 0755, true );
	// 	}
	// 	if ( file_exists( $destination ) && is_dir( $desintation ) ) {
	// 		$destination .= "/{$domain}.png";
	// 	}

	// 	file_put_contents( $destination, $favicon );
	// 	return $destination;
	// }

}