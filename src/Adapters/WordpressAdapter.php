<?php

namespace PicupTechnologies\WooPicupShipping\Adapters;

use PicupTechnologies\WooPicupShipping\Interfaces\WordpressAdapterInterface;

/**
 * Adapter to handle all wordpress-specific methods
 *
 * @package PicupTechnologies\WooPicupShipping\Adapters
 */
final class WordpressAdapter implements WordpressAdapterInterface
{
    /**
     * Fetches a setting
     *
     * @param $key
     *
     * @return mixed|void
     */
    public function getOption($key)
    {
        return get_option($key);
    }

    /**
     * Updates a setting
     *
     * @param $key
     * @param $value
     */
    public function updateOption($key, $value): void
    {
        update_option($key, $value);
    }

    /**
     * Redirects the users browser
     *
     * @param $url
     */
    public function redirect($url): void
    {
        wp_redirect($url);
    }

    /**
     * Adds a key/value to a post's metadata
     *
     * @param int    $id
     * @param string $key
     * @param        $value
     */
    public function addPostMeta(int $id, string $key, $value): void
    {
        add_post_meta($id, $key, $value);
    }

    /**
     * Returns a value from the post metadata
     *
     * @param int    $id
     * @param string $key
     * @param bool   $single
     *
     * @return mixed
     */
    public function getPostMeta(int $id, string $key, bool $single = true)
    {
        return get_post_meta($id, $key, $single);
    }

     /**
     * Returns a value from the post metadata
     *
     * @param int    $id
     *
     * @return mixed
     */
    public function getAllPostMeta(int $id)
    {
        return get_post_meta($id);
    }

    /**
     * Updates a value in a posts metadata
     *
     * @param int    $id
     * @param string $key
     * @param string $value
     */
    public function updatePostMeta(int $id, string $key, string $value): void
    {
        update_post_meta($id, $key, $value);
    }

    /**
     * Register a setting and its data.
     *
     * @param       $optionGroup
     * @param       $optionName
     * @param array $args
     */
    public function registerSetting($optionGroup, $optionName, $args = []): void
    {
        register_setting($optionGroup, $optionName, $args);
    }

    /**
     * Link to Wordpress add_menu_page()
     *
     * Add a top-level menu page.
     *
     * This function takes a capability which will be used to determine whether
     * or not a page is included in the menu.
     *
     * The function which is hooked in to handle the output of the page must check
     * that the user has the required capability as well.
     *
     * @param        $page_title
     * @param        $menu_title
     * @param        $capability
     * @param        $menu_slug
     * @param string $function
     * @param string $icon_url
     * @param null   $position
     */
    public function addMenuPage($page_title, $menu_title, $capability, $menu_slug, $function = '', $icon_url = '', $position = null): void
    {
        add_menu_page($page_title, $menu_title, $capability, $menu_slug, $function, $icon_url, $position);
    }

    /**
     * Link to Wordpress add_meta_box()
     *
     * Adds a meta box to one or more screens.
     *
     * @param        $id
     * @param        $title
     * @param        $callback
     * @param null   $screen
     * @param string $context
     * @param string $priority
     * @param null   $callback_args
     */
    public function addMetaBox($id, $title, $callback, $screen = null, $context = 'advanced', $priority = 'default', $callback_args = null): void
    {
        add_meta_box($id, $title, $callback, $screen, $context, $priority, $callback_args);
    }
}
