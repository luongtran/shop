<?php
/*
Plugin Name: Category Thumbnail
Plugin URI: https://github.com/pathawks/Category-Thumbnail
Description: Adds shortcode to embed a category thumbnail image link
Author: Pat Hawks
Author URI: http://pathawks.com
License: GPLv2
License URI: http://www.gnu.org/licenses/gpl-2.0.html
Version: 1.03

  Copyright 2014 Pat Hawks  (email : pat@pathawks.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

// [catthumb id="Category ID" link="ture"]

function dirtysuds_category_thumb( $atts ) {
	extract( shortcode_atts( array(
		'id' => 1,
		'link' => 'true',
	), $atts ) );

	$embed = get_transient( 'dirtysuds_category_thumb' . $id . $link );
	if( $embed ) return $embed;

	$embed = '';
	$posts = get_posts('cat='.$id.'&showposts=1&meta_key=_thumbnail_id');
	if( $posts[0] ) {
		$embed = get_the_post_thumbnail($posts[0]->ID,'thumbnail');
		if ($link == 'true') {
			$embed = '<a href="'.get_category_link($id).'" title="'.get_cat_name($id).'">' . $embed . '</a>';
		}
	}
	set_transient( 'dirtysuds_category_thumb' . $id . $link, $embed, 5 * MINUTE_IN_SECONDS );

	return $embed;
}

add_shortcode( 'catthumb', 'dirtysuds_category_thumb' );
