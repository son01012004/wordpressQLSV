<?php
/**
 * Script to check and report on the Điểm Danh feature configuration
 */

// Load WordPress with no output
define('WP_USE_THEMES', false);
require_once('../wp-load.php');

// Check if user is logged in and is admin
if (!is_user_logged_in() || !current_user_can('manage_options')) {
    wp_die('You need to be logged in as an administrator to run this script.');
}

echo '<h1>Điểm Danh Feature Status Check</h1>';

// Check 1: Verify post type exists and configuration
$post_type_exists = post_type_exists('diemdanh');
echo '<h2>Post Type Configuration</h2>';
echo '<p><strong>Post Type Exists:</strong> ' . ($post_type_exists ? 'Yes ✓' : 'No ✗') . '</p>';

if ($post_type_exists) {
    global $wp_post_types;
    $post_type_object = $wp_post_types['diemdanh'];
    
    echo '<p><strong>Rewrite Slug:</strong> ' . $post_type_object->rewrite['slug'] . '</p>';
    echo '<p><strong>Has Archive:</strong> ' . ($post_type_object->has_archive ? 'Yes ✓' : 'No ✗') . '</p>';
    echo '<p><strong>Public:</strong> ' . ($post_type_object->public ? 'Yes ✓' : 'No ✗') . '</p>';
    echo '<p><strong>Publicly Queryable:</strong> ' . ($post_type_object->publicly_queryable ? 'Yes ✓' : 'No ✗') . '</p>';
}

// Check 2: Verify query vars are registered
global $wp;
$lop_var_registered = in_array('lop', $wp->public_query_vars);
$mon_hoc_var_registered = in_array('mon_hoc', $wp->public_query_vars);

echo '<h2>Query Variables</h2>';
echo '<p><strong>lop Query Var:</strong> ' . ($lop_var_registered ? 'Registered ✓' : 'Not Registered ✗') . '</p>';
echo '<p><strong>mon_hoc Query Var:</strong> ' . ($mon_hoc_var_registered ? 'Registered ✓' : 'Not Registered ✗') . '</p>';

// Check 3: Verify rewrite rules
global $wp_rewrite;
$rewrite_rules = $wp_rewrite->wp_rewrite_rules();

echo '<h2>Rewrite Rules</h2>';

$diemdanh_rule_count = 0;
$diemdanh_param_rule_count = 0;

if (!empty($rewrite_rules)) {
    foreach ($rewrite_rules as $rule => $redirect) {
        if (strpos($rule, 'diemdanh') !== false) {
            $diemdanh_rule_count++;
            if (strpos($redirect, 'lop=$matches') !== false || strpos($redirect, 'mon_hoc=$matches') !== false) {
                $diemdanh_param_rule_count++;
            }
        }
    }
}

echo '<p><strong>Total Diemdanh Rules Found:</strong> ' . $diemdanh_rule_count . '</p>';
echo '<p><strong>Parameter Rules Found:</strong> ' . $diemdanh_param_rule_count . '</p>';

if ($diemdanh_param_rule_count == 0) {
    echo '<p style="color:red;">❌ No rules for parameters found. The refresh-diemdanh.php script should be run.</p>';
} else {
    echo '<p style="color:green;">✓ Parameter rules found - should be working correctly.</p>';
}

// Check 4: Check page exists
$diemdanh_page = get_page_by_path('diemdanhh');
echo '<h2>Điểm Danh Page</h2>';

if ($diemdanh_page) {
    echo '<p><strong>Page ID:</strong> ' . $diemdanh_page->ID . '</p>';
    echo '<p><strong>Page Title:</strong> ' . $diemdanh_page->post_title . '</p>';
    echo '<p><strong>Page Slug:</strong> ' . $diemdanh_page->post_name . '</p>';
    echo '<p><strong>Page Status:</strong> ' . $diemdanh_page->post_status . '</p>';
    echo '<p><strong>Page URL:</strong> <a href="' . get_permalink($diemdanh_page->ID) . '" target="_blank">' . get_permalink($diemdanh_page->ID) . '</a></p>';
    
    // Check if template is being used
    $template = get_post_meta($diemdanh_page->ID, '_wp_page_template', true);
    echo '<p><strong>Page Template:</strong> ' . (!empty($template) ? $template : 'Default Template') . '</p>';
    
    // Check if shortcode is in content
    if (strpos($diemdanh_page->post_content, '[qlsv_diemdanh_dashboard]') !== false) {
        echo '<p style="color:green;">✓ Page contains the correct shortcode.</p>';
    } else {
        echo '<p style="color:red;">❌ Page does not contain [qlsv_diemdanh_dashboard] shortcode.</p>';
    }
} else {
    echo '<p style="color:red;">❌ Điểm Danh page not found!</p>';
}

// Check 5: Check if template files exist
echo '<h2>Template Files</h2>';

$template_files = [
    QLSV_PLUGIN_DIR . 'templates/page-diemdanh.php',
    QLSV_PLUGIN_DIR . 'templates/archive-diemdanh.php',
    QLSV_PLUGIN_DIR . 'templates/single-diemdanh.php'
];

foreach ($template_files as $file) {
    $file_name = basename($file);
    $file_exists = file_exists($file);
    echo '<p><strong>' . $file_name . ':</strong> ' . ($file_exists ? 'Exists ✓' : 'Missing ✗') . '</p>';
}

// Check 6: Test URL generation
echo '<h2>Test URL Generation</h2>';

if (class_exists('QLSV_DiemDanh')) {
    $diemdanh = new QLSV_DiemDanh(null);
    
    // Get a sample lop and monhoc for testing
    $lop_query = new WP_Query([
        'post_type' => 'lop',
        'posts_per_page' => 1
    ]);
    
    $monhoc_query = new WP_Query([
        'post_type' => 'monhoc',
        'posts_per_page' => 1
    ]);
    
    if ($lop_query->have_posts() && $monhoc_query->have_posts()) {
        $lop_query->the_post();
        $lop_id = get_the_ID();
        $lop_name = get_the_title();
        wp_reset_postdata();
        
        $monhoc_query->the_post();
        $monhoc_id = get_the_ID();
        $monhoc_name = get_the_title();
        wp_reset_postdata();
        
        $test_url = $diemdanh->get_diemdanh_url($lop_id, $monhoc_id);
        echo '<p><strong>Generated URL:</strong> <a href="' . esc_url($test_url) . '" target="_blank">' . esc_html($test_url) . '</a></p>';
        
        // Direct archive URL test
        $archive_url = home_url('/diemdanh/');
        echo '<p><strong>Archive URL:</strong> <a href="' . esc_url($archive_url) . '" target="_blank">' . esc_html($archive_url) . '</a></p>';
    } else {
        echo '<p>Unable to find sample lop and monhoc for URL testing.</p>';
    }
} else {
    echo '<p>QLSV_DiemDanh class not available.</p>';
}

// Check 7: Permalink structure
echo '<h2>WordPress Settings</h2>';
echo '<p><strong>Permalink Structure:</strong> ' . get_option('permalink_structure') . '</p>';
echo '<p><strong>Home URL:</strong> ' . home_url() . '</p>';

// Recommendations
echo '<h2>Recommendations</h2>';
$recommendations = [];

if (!$post_type_exists) {
    $recommendations[] = 'The diemdanh post type is not registered. Please check the plugin activation.';
}

if (!$lop_var_registered || !$mon_hoc_var_registered) {
    $recommendations[] = 'Query variables are not properly registered. Run the refresh-diemdanh.php script.';
}

if ($diemdanh_param_rule_count == 0) {
    $recommendations[] = 'No rewrite rules found for diemdanh parameters. Run the refresh-diemdanh.php script.';
}

if (!$diemdanh_page) {
    $recommendations[] = 'Điểm Danh page is missing. Create a new page with the slug "diemdanhh" and add the shortcode [qlsv_diemdanh_dashboard].';
}

if (empty(get_option('permalink_structure'))) {
    $recommendations[] = 'Permalink structure is not set! Go to Settings > Permalinks and choose a permalink structure (Post name recommended).';
}

if (empty($recommendations)) {
    echo '<p style="color:green;">✓ All checks passed. The Điểm Danh feature should be working properly!</p>';
    echo '<p>If you are still experiencing issues, try the following:</p>';
    echo '<ol>';
    echo '<li>Run the <code>refresh-diemdanh.php</code> script</li>';
    echo '<li>Go to WordPress Settings > Permalinks and click "Save Changes" (even without making changes)</li>';
    echo '<li>Clear your browser cache</li>';
    echo '<li>Try accessing the URLs in an incognito/private browsing window</li>';
    echo '</ol>';
} else {
    echo '<ul style="color:red;">';
    foreach ($recommendations as $recommendation) {
        echo '<li>' . $recommendation . '</li>';
    }
    echo '</ul>';
}

// Footer with links
echo '<p style="margin-top: 30px;">';
echo '<a href="' . admin_url('edit.php?post_type=diemdanh') . '">Manage Điểm Danh</a> | ';
echo '<a href="' . home_url('/diemdanhh/') . '">View Điểm Danh Page</a> | ';
echo '<a href="' . home_url('/diemdanh/') . '">View Điểm Danh Archive</a> | ';
echo '<a href="' . admin_url('options-permalink.php') . '">Permalink Settings</a> | ';
echo '<a href="' . admin_url() . '">Return to Dashboard</a>';
echo '</p>';
?> 