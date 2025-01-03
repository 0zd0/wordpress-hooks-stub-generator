<?php

/**
 * Fires when an 'action' request variable is sent.
 *
 * The dynamic portion of the hook name, `$action`, refers to
 * the action derived from the `GET` or `POST` request.
 *
 * @since 2.6.0
 */
do_action( "admin_action_{$action}" );
