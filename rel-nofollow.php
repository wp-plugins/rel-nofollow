<?php

/**
 * @author Stefano Ottolenghi
 * @copyright 2014
 */

/*
Plugin Name: Rel nofollow
Plugin URI: http://www.thecrowned.org/wordpress-plugins/rel-nofollow
Description: Adds rel="nofollow" to posts links unless specified otherwise.
Author: Stefano Ottolenghi
Version: 1.0
Author URI: http://www.thecrowned.org/
*/

/* Copyright Stefano Ottolenghi 2014
 * 
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */
 
 class rel_nofollow {
	
	/**
	 * Hooks to post saving and adds metabox in post editing page.
     *
	 * @access  public
     * @since   1.0
     */
	 
	function __construct() {
        add_filter( 'wp_insert_post_data', array( &$this, 'post_content_add_nofollow' ), 10, 2 );
		add_action( 'add_meta_boxes', array( &$this, 'post_page_metabox' ) );
		add_filter( 'plugin_row_meta', array( &$this, 'donate_meta_link' ), 10, 2 );
	}
	
	/**
	 * Adds plugin metabox in post editing page.
     *
	 * @access  public
     * @since   1.0
     */
	 
	function post_page_metabox() {
		add_meta_box( 'rel_nofollow', 'Rel Nofollow', array( $this, 'metabox_post_content' ), 'post', 'side', 'default' );
	}
	
	/**
	 * Adds content to post page metabox.
	 * 
	 * @access  public
     * @since   1.0
     */
	
	function metabox_post_content( $post ) {
		wp_nonce_field( 'rnf_metabox_nonce', 'rnf_metabox_nonce' );
		
		$checked = '';
		if( get_post_meta( $post->ID, '_rnf_exclude_post', true ) ) {
			$checked = ' checked="checked"';
		}
		
		echo '<p>';
		echo '<input type="checkbox" name="rnf_exclude_post" id="rnf_exclude_post" value="rnf_exclude_post"'.$checked.' />';
		echo '<label for="rnf_exclude_post">'.__( "Exclude post", 'rfn' ).'</label>';
		echo '</p>';
	}
	
	/**
	 * Adds rel nofollow to post content if option was checked, removes if unchecked.
	 * 
	 * @access  public
     * @since   1.0
	 *
	 * @param array $post_data WP post data
	 * @param array $postarr an array of elements that make up a post to update or insert
     */
	
    function post_content_add_nofollow( $post_data, $raw_post_data ) {
        wp_verify_nonce( 'rnf_metabox_nonce', 'rnf_metabox_nonce' );
		
		if( ! isset( $raw_post_data['rnf_exclude_post'] ) ) {
			$post_data['post_content'] = wp_rel_nofollow( $post_data['post_content'] );
			
			delete_post_meta( $raw_post_data['ID'], '_rnf_exclude_post' );
		} else {
			$post_data['post_content'] = addslashes( preg_replace( '~<a([^>]*?)rel="nofollow"([^>]*)>(.*?)</a>~i', '<a${1}${2}>${3}</a>', stripslashes( $post_data['post_content'] ) ) );
			
			update_post_meta( $raw_post_data['ID'], '_rnf_exclude_post', 'yes' );
		}
		
		return $post_data;
    }
	
	/**
     * Shows the "Donate" link in the plugins list (under the description)
     *
     * @access  public
     * @since   2.0
     * @param   $links array links already in place
     * @param   $file string current plugin-file
    */
    
    function donate_meta_link( $links, $file ) {
       if( $file == plugin_basename( __FILE__ ) ) {
            $links[] = '<a href="https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=2WDRMXGHWCCUY" title="'.__( 'Donate' ).'">'.__( 'Donate' ).'</a>';
       }
     
        return $links;
    }
 
}

new rel_nofollow();

?>