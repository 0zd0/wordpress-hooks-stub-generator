<?php

declare(strict_types=1);

/**
 * @since 3.3.0
 *
 * @param $this          Refund
 * @param $args          array
 * @param $vendor_refund float
 */
do_action('dokan_refund_approve_before_insert', $this, $args, $vendor_refund);
