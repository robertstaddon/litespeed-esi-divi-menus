<?php
/*
	Plugin Name: LiteSpeed ESI Divi Menus
	Description: Replace Divi primary and secondary header menus with LiteSpeed ESI blocks
	Version: 1.1
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
    private static $_instance;

    private $esi_divi_primary_menu_ttl = 43200;
    private $esi_divi_secondary_menu_ttl = 43200;

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
            LiteSpeed_Cache_API::hook_tpl_not_esi( array( $this, 'is_not_esi' ) );
            LiteSpeed_Cache_API::hook_tpl_esi( self::ESI_DIVI_PRIMARY_MENU_BLOCK, array( $this, 'load_primary_menu' ) );
            LiteSpeed_Cache_API::hook_tpl_esi( self::ESI_DIVI_SECONDARY_MENU_BLOCK, array( $this, 'load_secondary_menu' ) );

            add_action( 'wp_update_nav_menu', array( $this, 'purge_esi' ) );
        }
    }

    /**
     * Hooked to LiteSpeed_Cache_API::hook_tpl_not_esi
     *
     * If the request is not an esi request, hook to the replace_wp_nav_menu filter to replace it as an esi block. 
     */
    public function is_not_esi()
    {
        add_filter( 'wp_nav_menu', array( $this, 'replace_wp_nav_menu' ), 999, 2) ;
    }


    /*
     * Hooked to 'wp_nav_menu' filter
     * 
     * The Divi primary and secondary menus contain user-specific content. Replace them with esi blocks.
     */
    public function replace_wp_nav_menu( $nav_menu_html, $args )
    {
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
    public function load_primary_menu( )
    {
        LiteSpeed_Cache_API::debug( "Divi ESI Menus set primary menu TTL to " . $this->esi_divi_primary_menu_ttl ) ;
        LiteSpeed_Cache_API::set_ttl( $this->esi_divi_primary_menu_ttl );
        LiteSpeed_Cache_API::add_private( LiteSpeed_Cache_Tag::TYPE_ESI . self::DIVI_PRIMARY_MENU_ID ) ;  // Tag ESI block for easy purging if needed

        wp_nav_menu( array(
            'theme_location' => 'primary-menu',
            'container'      => '',
            'fallback_cb'    => '',
            'menu_class'     => 'nav',
            'menu_id'        => 'top-menu'
        ) );
    }
    public function load_secondary_menu( )
    {
        LiteSpeed_Cache_API::debug( "Divi ESI Menus set seconary TTL to " . $this->esi_divi_secondary_menu_ttl ) ;
        LiteSpeed_Cache_API::set_ttl( $this->esi_divi_secondary_menu_ttl ) ;
        LiteSpeed_Cache_API::add_private( LiteSpeed_Cache_Tag::TYPE_ESI . self::DIVI_SECONDARY_MENU_ID ) ; // Tag ESI block for easy purging if needed

        wp_nav_menu( array(
            'theme_location' => 'secondary-menu',
            'container'      => '',
            'fallback_cb'    => '',
            'menu_id'        => 'et-secondary-nav'
        ) );
    }

    
	/**
	 * Purge esi private tag
	 */
	public function purge_esi()
	{
		LiteSpeed_Cache_API::debug( 'Divi ESI Menus purge ESI' ) ;
		LiteSpeed_Cache_API::purge_private( LiteSpeed_Cache_Tag::TYPE_ESI . self::DIVI_PRIMARY_MENU_ID ) ;
        LiteSpeed_Cache_API::purge_private( LiteSpeed_Cache_Tag::TYPE_ESI . self::DIVI_SECONDARY_MENU_ID ) ;
	}

    /**
     * Get the current instance object.
     */
    public static function get_instance()
    {
        if ( ! isset( self::$_instance ) ) {
            self::$_instance = new self() ;
        }

        return self::$_instance ;
    }
}

LiteSpeed_ESI_Divi_Menus::get_instance() ;
