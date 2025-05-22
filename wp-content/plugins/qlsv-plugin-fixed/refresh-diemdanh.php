<?php
/**
 * Script to refresh diemdanh permalinks and test URLs
 */

// Load WordPress with no output
define('WP_USE_THEMES', false);
require_once('../wp-load.php');

// Check if user is logged in and is admin
if (!is_user_logged_in() || !current_user_can('manage_options')) {
    wp_die('You need to be logged in as an administrator to run this script.');
}

echo '<h1>Refreshing Điểm Danh Settings</h1>';

// Step 1: Clear any existing rewrite rules
flush_rewrite_rules(true);
echo '<p>Existing rewrite rules cleared.</p>';

// Step 2: Re-register the diemdanh post type
if (post_type_exists('diemdanh')) {
    global $wp_post_types;
    if (isset($wp_post_types['diemdanh'])) {
        $wp_post_types['diemdanh']->rewrite = array(
            'slug' => 'diemdanh',
            'with_front' => false,
            'feeds' => false,
            'pages' => false,
            'ep_mask' => EP_PERMALINK
        );
        // Ensure archive is enabled
        $wp_post_types['diemdanh']->has_archive = true;
        
        echo '<p>Diemdanh post type updated with correct settings.</p>';
    }
}

// Step 3: Add our custom rewrite rules
add_rewrite_rule(
    'diemdanh/lop/([0-9]+)/mon-hoc/([0-9]+)/?$',
    'index.php?post_type=diemdanh&lop=$matches[1]&mon_hoc=$matches[2]',
    'top'
);

add_rewrite_rule(
    'diemdanh/?$',
    'index.php?post_type=diemdanh',
    'top'
);

// Step 4: Register query vars
global $wp;
$wp->add_query_var('lop');
$wp->add_query_var('mon_hoc');

echo '<p>Custom rewrite rules and query vars added.</p>';

// Step 5: Flush rewrite rules again to ensure changes take effect
flush_rewrite_rules(false);
echo '<p>Rewrite rules flushed with new settings.</p>';

// Step 6: Test URLs generation
echo '<h2>Testing URL Generation</h2>';

// Check if DiemDanh class is available
if (class_exists('QLSV_DiemDanh')) {
    $diemdanh = new QLSV_DiemDanh(null);
    
    // Get a sample lop and monhoc for testing
    $lop_args = array(
        'post_type' => 'lop',
        'posts_per_page' => 1
    );
    
    $monhoc_args = array(
        'post_type' => 'monhoc',
        'posts_per_page' => 1
    );
    
    $lop_query = new WP_Query($lop_args);
    $monhoc_query = new WP_Query($monhoc_args);
    
    $lop_id = 0;
    $monhoc_id = 0;
    
    if ($lop_query->have_posts()) {
        $lop_query->the_post();
        $lop_id = get_the_ID();
        $lop_title = get_the_title();
        wp_reset_postdata();
    }
    
    if ($monhoc_query->have_posts()) {
        $monhoc_query->the_post();
        $monhoc_id = get_the_ID();
        $monhoc_title = get_the_title();
        wp_reset_postdata();
    }
    
    if ($lop_id && $monhoc_id) {
        $url = $diemdanh->get_diemdanh_url($lop_id, $monhoc_id);
        echo '<p>Generated URL for Lop "' . esc_html($lop_title) . '" and Mon Hoc "' . esc_html($monhoc_title) . '":</p>';
        echo '<p><a href="' . esc_url($url) . '" target="_blank">' . esc_html($url) . '</a></p>';
        echo '<p>Click the link to test if it works correctly.</p>';
    } else {
        echo '<p>Could not find sample lop and monhoc for testing.</p>';
    }
} else {
    echo '<p>QLSV_DiemDanh class not available.</p>';
}

// Step 7: Show direct archive URL for testing
$archive_url = home_url('/diemdanh/');
echo '<p>Direct archive URL: <a href="' . esc_url($archive_url) . '" target="_blank">' . esc_html($archive_url) . '</a></p>';

// Step 8: Provide link back to admin
echo '<p><a href="' . admin_url() . '">Return to admin dashboard</a></p>';
?> 