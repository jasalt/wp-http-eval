<?php

add_action('admin_init', function() {
    if (defined('WP_HTTP_EVAL_TOKEN') && defined('WP_HTTP_EVAL_WIDGET')) {
        add_action('wp_ajax_wp_http_eval', 'handle_eval_request');
    }
});

function handle_eval_request() {
    if (!current_user_can('administrator')) {
        wp_send_json_error('Permission denied', 403);
    }

    // Verify nonce
    if (!wp_verify_nonce($_GET['_ajax_nonce'], 'wp_http_eval_nonce')) {
        wp_send_json_error('Invalid nonce', 403);
    }

    $code = file_get_contents('php://input');
    // error_log("Evaluating: " . $code);
    if (empty(trim($code))) {
        wp_send_json_error('No code provided', 400);
    }

    $result = evalPhel($code);
    wp_send_json($result);
}

add_action('wp_dashboard_setup', function() {
    if (current_user_can('administrator') && defined('WP_HTTP_EVAL_TOKEN') &&
		defined('WP_HTTP_EVAL_WIDGET') && WP_HTTP_EVAL_WIDGET == true) {
        wp_add_dashboard_widget(
            'wp_http_eval_widget',
            'Phel Code Evaluator',
            'render_eval_widget'
        );
    }
});

function render_eval_widget() {
    wp_enqueue_script('wp-http-eval', plugins_url('admin-widget.js', __FILE__), ['jquery'], '1.0', true);
    wp_localize_script('wp-http-eval', 'wpHttpEval', [
        'ajaxUrl' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('wp_http_eval_nonce')
    ]);
    ?>
    <div class="wp-http-eval-widget">
        <textarea id="wp-http-eval-code" rows="10" style="width: 100%;" placeholder="Enter Phel code here"></textarea>
        <button id="wp-http-eval-submit" class="button button-primary">Evaluate</button>
        <div id="wp-http-eval-result" style="margin-top: 15px; padding: 10px; border: 1px solid #ddd; background: #f9f9f9; display: none;"></div>
    </div>
    <?php
}


// Hide all default dashboard widgets
function hide_default_dashboard_widgets() {
    // Remove default widgets
    remove_meta_box('dashboard_right_now', 'dashboard', 'normal');
    remove_meta_box('dashboard_activity', 'dashboard', 'normal');
    remove_meta_box('dashboard_quick_press', 'dashboard', 'side');
    remove_meta_box('dashboard_primary', 'dashboard', 'side');
    remove_meta_box('dashboard_secondary', 'dashboard', 'side');
    remove_meta_box('dashboard_incoming_links', 'dashboard', 'normal');
    remove_meta_box('dashboard_plugins', 'dashboard', 'normal');
    remove_meta_box('dashboard_recent_drafts', 'dashboard', 'side');
    remove_meta_box('dashboard_recent_comments', 'dashboard', 'normal');
	remove_meta_box('dashboard_site_health', 'dashboard', 'normal'); // Site Health Status
}

add_action('wp_dashboard_setup', 'hide_default_dashboard_widgets');

// Hide welcome panel
function hide_welcome_panel() {
    remove_action('welcome_panel', 'wp_welcome_panel');
}

add_action('wp_dashboard_setup', 'hide_welcome_panel');
