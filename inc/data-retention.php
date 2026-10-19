<?php
/**
 * SPEK personal-data retention — SAFE DEFAULT: DISABLED.
 *
 * Explicit activation, after legal review and backup assessment, requires:
 * define('SPEK_RETENTION_CLEANUP_ENABLED', true);
 *
 * A newsletter subscription MUST have its own separate record and retention
 * purpose. Contact enquiry records expire independently of subscription.
 *
 * This task removes the WordPress submission record only. Host logs, backup
 * snapshots, mailboxes and newsletter providers need separate policies.
 */
if (!defined('ABSPATH')) { exit; }

function spek_retention_cleanup_enabled(): bool {
    return defined('SPEK_RETENTION_CLEANUP_ENABLED') && SPEK_RETENTION_CLEANUP_ENABLED === true;
}

function spek_retention_schedule(): void {
    $hook = 'spek_retention_cleanup';
    if (!spek_retention_cleanup_enabled()) {
        if (wp_next_scheduled($hook)) {
            wp_clear_scheduled_hook($hook);
        }
        return;
    }
    if (!wp_next_scheduled($hook)) {
        wp_schedule_event(time() + HOUR_IN_SECONDS, 'daily', $hook);
    }
}
add_action('init', 'spek_retention_schedule', 40);

function spek_retention_run(): void {
    if (!spek_retention_cleanup_enabled()) { return; }

    $periods = [
        'spek_contact' => 12,
        'spek_job_app' => 9,
    ];

    foreach ($periods as $post_type => $months) {
        $cutoff = gmdate('Y-m-d H:i:s', strtotime('-' . $months . ' months'));
        $items = get_posts([
            'post_type' => $post_type,
            'post_status' => 'publish',
            'numberposts' => 100,
            'orderby' => 'date',
            'order' => 'ASC',
            'fields' => 'ids',
            'suppress_filters' => true,
            'date_query' => [
                ['column' => 'post_date_gmt', 'before' => $cutoff, 'inclusive' => false],
            ],
            // Manual legal hold marker: exclude individually retained records.
            'meta_query' => [
                'relation' => 'OR',
                ['key' => '_spek_retention_hold', 'compare' => 'NOT EXISTS'],
                ['key' => '_spek_retention_hold', 'value' => '1', 'compare' => '!='],
            ],
        ]);

        foreach ($items as $post_id) {
            if (get_post_type($post_id) !== $post_type) { continue; }
            // For career applications, inc/career-applications.php has an
            // existing before_delete_post hook to remove private CV files.
            wp_delete_post((int) $post_id, true);
        }
    }
}
add_action('spek_retention_cleanup', 'spek_retention_run');
