<?php

declare(strict_types=1);

/**
 * Filters the value of a network option before it is updated.
 *
 * This filter is called before a network option is updated in the database. It allows
 * modification of the option value before it is stored.
 *
 * Possible hook names include:
 *
 *  - `default_action_example`
 *  - `default_action_test`
 *
 * @param  mixed  $value  The new, unserialized option value.
 * @param  string $option Name of the network option.
 * @return mixed  The filtered value of the network option.
 *
 * @since 4.4.0
 * @deprecated 5.5.0 Use update_network_option instead.
 * @link https://core.trac.wordpress.org/ticket/19321
 * @global wpdb $wpdb
 * @private
 * @todo Add better validation for $option parameter
 * @ignore Internal use only
 * @see test()
 */
do_action('default_action', $value, $option);

/**
 * Filters the value of a network option before it is updated.
 *
 * This filter is called before a network option is updated in the database. It allows
 * modification of the option value before it is stored.
 *
 * Possible hook names include:
 *
 *  - `default_filter_example`
 *  - `default_filter_test`
 *
 * @param  mixed  $value  The new, unserialized option value.
 * @param  string $option Name of the network option.
 * @return mixed  The filtered value of the network option.
 *
 * @since 4.4.0
 * @deprecated 5.5.0 Use update_network_option instead.
 * @link https://core.trac.wordpress.org/ticket/19321
 * @global wpdb $wpdb
 * @private
 * @todo Add better validation for $option parameter
 * @ignore Internal use only
 * @see test()
 */
apply_filters('default_filter', $value, $option);