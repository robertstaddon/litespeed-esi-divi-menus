<?php
/*
	Plugin Name: LiteSpeed ESI Divi Menus
	Description: Replace Divi primary and secondary header menus with LiteSpeed ESI blocks
	Version: 1.0
	Author: Abundant Designs LLC
	Author URI: https://www.abundantdesigns.com
	License: GPLv2 or later
	Text Domain: litespeed-esi-divi-menus
 */
 
if ( ! defined('ABSPATH') ) {
    die() ;
}


class LiteSpeed_ESI_Divi_Menus
{
    private static $_instance ;
    
    const DIVI_PRIMARY_MENU_ID = "top-menu";
    const DIVI_SECONDARY_MENU_ID = "et-secondary-nav";
    const ESI_DIVI_PRIMARY_MENU_BLOCK = "litespeed_esi_divi_menu_primary";
    const ESI_DIVI_SECONDARY_MENU_BLOCK = "litespeed_esi_divi_menu_secondary";

    /**
	 * Define the core functionality of the plugin.
	 */
    private function __construct()
    {
        add_action( 'init', array( $this, 'init' ) );
    }


    /**
     * Initialize hooks 
     */
    public function init() 
    {
        if ( ! defined( 'ET_CORE' ) ) return ;
        
        if ( method_exists( 'LiteSpeed_Cache_API', 'esi_enabled' ) && LiteSpeed_Cache_API::esi_enabled() ) {
            LiteSpeed_Cache_API::hook_tpl_not_esi( 'LiteSpeed_ESI_Divi_Menus::is_not_esi' ) ;
            LiteSpeed_Cache_API::hook_tpl_esi( self::ESI_DIVI_PRIMARY_MENU_BLOCK, 'LiteSpeed_ESI_Divi_Menus::load_primary_menu' ) ;
            LiteSpeed_Cache_API::hook_tpl_esi( self::ESI_DIVI_SECONDARY_MENU_BLOCK, 'LiteSpeed_ESI_Divi_Menus::load_secondary_menu' ) ;
        }
    }

    /**
     * Hooked to LiteSpeed_Cache_API::hook_tpl_not_esi
     *
     * If the request is not an ESI request, hook to the add to wishlist button filter to replace it as an esi block. 
     */
    public static function is_not_esi()
    {
        add_filter( 'wp_nav_menu', 'LiteSpeed_ESI_Divi_Menus::replace_wp_nav_menu', 999, 2) ;
    }


    /*
     * Hooked to 'wp_nav_menu' filter
     * 
     * The Divi primary and secondary menus contain user-specific content. Replace them with ESI blocks.
     */
    public static function replace_wp_nav_menu( $nav_menu_html, $args ) {
        if ( $args->menu_id == self::DIVI_PRIMARY_MENU_ID ) {
            return LiteSpeed_Cache_API::esi_url( self::ESI_DIVI_PRIMARY_MENU_BLOCK, 'LiteSpeed Divi Primary Menu' ) ;
        }
        if ( $args->menu_id == self::DIVI_SECONDARY_MENU_ID ) {
            return LiteSpeed_Cache_API::esi_url( self::ESI_DIVI_SECONDARY_MENU_BLOCK, 'LiteSpeed Divi Secondary Menu' ) ;
        }
    
        return $nav_menu_html;
    }

    /**
     * Hooked to LiteSpeed_Cache_API::hook_tpl_esi 
     */
    public static function load_primary_menu( ) {
        wp_nav_menu( array(
            'theme_location' => 'primary-menu',
            'container'      => '',
            'fallback_cb'    => '',
            'menu_class'     => 'nav',
            'menu_id'        => 'top-menu'
        ) );
    }
    public static function load_secondary_menu( ) {
        wp_nav_menu( array(
            'theme_location' => 'secondary-menu',
            'container'      => '',
            'fallback_cb'    => '',
            'menu_id'        => 'et-secondary-nav'
        ) );
    }


    /**
     * Get the current instance object.
     */
    public static function get_instance()
    {
        if ( ! isset(self::$_instance) ) {
            self::$_instance = new self() ;
        }

        return self::$_instance ;
    }
}

LiteSpeed_ESI_Divi_Menus::get_instance() ;
