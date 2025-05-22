<?php
/**
 * Rishi Custom Functions and definitions
 *
 * @link https://developer.wordpress.org/themes/basics/theme-functions/
 *
 * @package Rishi
 */

$theme_data = wp_get_theme();
if( ! defined( 'RISHI_VERSION' ) ) define( 'RISHI_VERSION', $theme_data->get( 'Version' ) );
if( ! defined( 'RISHI_NAME' ) ) define( 'RISHI_NAME', $theme_data->get( 'Name' ) );
if( ! defined( 'RISHI_TEXTDOMAIN' ) ) define( 'RISHI_TEXTDOMAIN', $theme_data->get( 'TextDomain' ) );   


// Customizer Builder directory.
defined( 'THEME_CUSTOMIZER_BUILDER_DIR_' ) || define( 'THEME_CUSTOMIZER_BUILDER_DIR_', get_template_directory() . '/customizer' );

/**
 * Custom template tags for this theme.
 */
require get_template_directory() . '/inc/template-tags.php';

/**
 * Google Fonts.
 */
require get_template_directory() . '/inc/typography/google-fonts.php';

/**
 * Custom Functions for the theme
 */
require get_template_directory() . '/inc/custom-functions.php';

/**
 * Extras Code
 */
require get_template_directory() . '/inc/extras.php';

/**
 * Functions which enhance the theme by hooking into WordPress.
 */
require get_template_directory() . '/inc/template-functions.php';

/**
 * Load Jetpack compatibility file.
 */
if ( defined( 'JETPACK__VERSION' ) ) {
	require get_template_directory() . '/inc/jetpack.php';
}
/**
 * Customizer Init Files
 */
require get_template_directory() . '/customizer/init.php';

/**
 * Dynamic Editor Styles
 */
require get_template_directory() . '/inc/editor.php';

/**
 * Elementor Compatibility for the theme
 */
if ( rishi_is_elementor_activated() ) require get_template_directory() . '/inc/elementor-compatibility.php';

/**
 * Load WooCommerce compatibility file.
 */
if ( class_exists( 'WooCommerce' ) ) require get_template_directory() . '/inc/woocommerce.php';
/**
 * Load google fonts locally
 */
require get_template_directory() . '/inc/class-webfont-loader.php';

/** 
* Custom Dashboard Functions here
*/
require get_template_directory() . '/inc/classes/class-dashboard.php';

/**
 * Schema Markup here
 */
require get_template_directory() . '/inc/classes/class-microdata.php';

/**
 * Theme Updater
*/
require get_template_directory() . '/updater/theme-updater.php';

/**
 * Static CSS 
 *
 * Requires all the path of static_css folder
 *
 * @since 1.0.0
 */
foreach ( glob( get_template_directory() . '/inc/assets/css/static_css/*.php' ) as $file ) {
    require $file;
}

/**
 * Notices
 */
require get_template_directory() . '/updater/notice.php';

function hien_thi_bang_diem_html($post) {
    // Lấy dữ liệu ACF
    $diem1 = get_field('diem_thanh_phan_1_', $post->ID);
    $diem2 = get_field('diem_thanh_phan_2_', $post->ID);
    $cuoiki = get_field('diem_cuoi_ki_', $post->ID);

    // Tính trung bình (ví dụ: 20% + 20% + 60%)
    $tb = '';
    if (is_numeric($diem1) && is_numeric($diem2) && is_numeric($cuoiki)) {
        $tb = round(($diem1 * 0.2 + $diem2 * 0.2 + $cuoiki * 0.6), 2);
    }

    // Xếp loại
    $xeploai = '';
    if ($tb !== '') {
        if ($tb >= 8.5) $xeploai = 'Giỏi';
        elseif ($tb >= 7) $xeploai = 'Khá';
        elseif ($tb >= 5.5) $xeploai = 'Trung bình';
        else $xeploai = 'Yếu';
    }

    echo "<p><strong>Điểm trung bình:</strong> " . ($tb !== '' ? $tb : 'Chưa đủ dữ liệu') . "</p>";
    echo "<p><strong>Xếp loại:</strong> " . ($xeploai ?: 'Chưa xác định') . "</p>";
    
    // Hiển thị form nhập điểm
    echo '<hr><h3>Nhập điểm sinh viên</h3>';
    
    // Lấy sinh viên đã chọn
    $selected_student = get_field('sinh_vien', $post->ID);
    $selected_course = get_field('mon_hoc', $post->ID);
    $selected_class = get_field('lop', $post->ID);
    
    // Form nhập điểm
    ?>
    <div class="bang-diem-form">
        <table class="form-table">
            <tr>
                <th><label for="sinh_vien">Sinh viên:</label></th>
                <td>
                    <?php
                    // Query sinh viên
                    $students = get_posts([
                        'post_type' => 'sinhvien',
                        'posts_per_page' => -1,
                        'orderby' => 'title',
                        'order' => 'ASC'
                    ]);
                    
                    if ($students) {
                        echo '<select name="acf[sinh_vien]" id="sinh_vien" required>';
                        echo '<option value="">-- Chọn sinh viên --</option>';
                        foreach ($students as $student) {
                            $selected = ($selected_student && $selected_student == $student->ID) ? 'selected' : '';
                            echo '<option value="' . $student->ID . '" ' . $selected . '>' . $student->post_title . '</option>';
                        }
                        echo '</select>';
                    } else {
                        echo 'Không có sinh viên nào.';
                    }
                    ?>
                </td>
            </tr>
            <tr>
                <th><label for="mon_hoc">Môn học:</label></th>
                <td>
                    <?php
                    // Query môn học
                    $courses = get_posts([
                        'post_type' => 'monhoc',
                        'posts_per_page' => -1,
                        'orderby' => 'title',
                        'order' => 'ASC'
                    ]);
                    
                    if ($courses) {
                        echo '<select name="acf[mon_hoc]" id="mon_hoc" required>';
                        echo '<option value="">-- Chọn môn học --</option>';
                        foreach ($courses as $course) {
                            $selected = ($selected_course && $selected_course == $course->ID) ? 'selected' : '';
                            echo '<option value="' . $course->ID . '" ' . $selected . '>' . $course->post_title . '</option>';
                        }
                        echo '</select>';
                    } else {
                        echo 'Không có môn học nào.';
                    }
                    ?>
                </td>
            </tr>
            <tr>
                <th><label for="lop">Lớp:</label></th>
                <td>
                    <?php
                    // Query lớp
                    $classes = get_posts([
                        'post_type' => 'lop',
                        'posts_per_page' => -1,
                        'orderby' => 'title',
                        'order' => 'ASC'
                    ]);
                    
                    if ($classes) {
                        echo '<select name="acf[lop]" id="lop" required>';
                        echo '<option value="">-- Chọn lớp --</option>';
                        foreach ($classes as $class) {
                            $selected = ($selected_class && $selected_class == $class->ID) ? 'selected' : '';
                            echo '<option value="' . $class->ID . '" ' . $selected . '>' . $class->post_title . '</option>';
                        }
                        echo '</select>';
                    } else {
                        echo 'Không có lớp nào.';
                    }
                    ?>
                </td>
            </tr>
            <tr>
                <th><label for="diem_thanh_phan_1">Điểm thành phần 1:</label></th>
                <td>
                    <input type="number" id="diem_thanh_phan_1" name="acf[diem_thanh_phan_1_]" value="<?php echo esc_attr($diem1); ?>" min="0" max="10" step="0.1">
                </td>
            </tr>
            <tr>
                <th><label for="diem_thanh_phan_2">Điểm thành phần 2:</label></th>
                <td>
                    <input type="number" id="diem_thanh_phan_2" name="acf[diem_thanh_phan_2_]" value="<?php echo esc_attr($diem2); ?>" min="0" max="10" step="0.1">
                </td>
            </tr>
            <tr>
                <th><label for="diem_cuoi_ki">Điểm cuối kỳ:</label></th>
                <td>
                    <input type="number" id="diem_cuoi_ki" name="acf[diem_cuoi_ki_]" value="<?php echo esc_attr($cuoiki); ?>" min="0" max="10" step="0.1">
                </td>
            </tr>
        </table>
    </div>
    
    <style>
        .bang-diem-form table {
            width: 100%;
            border-collapse: collapse;
        }
        .bang-diem-form th {
            text-align: left;
            padding: 10px;
            width: 150px;
        }
        .bang-diem-form td {
            padding: 10px;
        }
        .bang-diem-form select, 
        .bang-diem-form input {
            width: 100%;
            max-width: 300px;
            padding: 8px;
        }
    </style>
    <?php
}

// Lưu dữ liệu ACF khi lưu post
add_action('acf/save_post', 'luu_du_lieu_bang_diem', 20);
function luu_du_lieu_bang_diem($post_id) {
    // Chỉ xử lý post type 'diem'
    if (get_post_type($post_id) !== 'diem') {
        return;
    }

    // Cập nhật tiêu đề post dựa trên dữ liệu đã chọn
    $sinh_vien_id = get_field('sinh_vien', $post_id);
    $mon_hoc_id = get_field('mon_hoc', $post_id);
    $lop_id = get_field('lop', $post_id);
    
    if ($sinh_vien_id && $mon_hoc_id) {
        $sinh_vien = get_the_title($sinh_vien_id);
        $mon_hoc = get_the_title($mon_hoc_id);
        $lop = $lop_id ? get_the_title($lop_id) : '';
        
        // Cập nhật tiêu đề
        $title = $sinh_vien . ' - ' . $mon_hoc;
        if ($lop) {
            $title .= ' - ' . $lop;
        }
        
        // Cập nhật post mà không trigger save_post hook
        remove_action('acf/save_post', 'luu_du_lieu_bang_diem', 20);
        
        wp_update_post([
            'ID' => $post_id,
            'post_title' => $title,
        ]);
        
        add_action('acf/save_post', 'luu_du_lieu_bang_diem', 20);
    }
}

// Đăng ký ACF fields cho bảng điểm nếu ACF Pro không được kích hoạt
function register_bang_diem_acf_fields() {
    if (!function_exists('acf_add_local_field_group')) {
        return; // ACF không được kích hoạt
    }

    acf_add_local_field_group([
        'key' => 'group_bang_diem',
        'title' => 'Thông tin bảng điểm',
        'fields' => [
            [
                'key' => 'field_sinh_vien',
                'label' => 'Sinh viên',
                'name' => 'sinh_vien',
                'type' => 'post_object',
                'instructions' => 'Chọn sinh viên',
                'required' => 1,
                'post_type' => ['sinhvien'],
                'return_format' => 'id',
                'ui' => 1,
            ],
            [
                'key' => 'field_mon_hoc',
                'label' => 'Môn học',
                'name' => 'mon_hoc',
                'type' => 'post_object',
                'instructions' => 'Chọn môn học',
                'required' => 1,
                'post_type' => ['monhoc'],
                'return_format' => 'id',
                'ui' => 1,
            ],
            [
                'key' => 'field_lop',
                'label' => 'Lớp',
                'name' => 'lop',
                'type' => 'post_object',
                'instructions' => 'Chọn lớp',
                'required' => 0,
                'post_type' => ['lop'],
                'return_format' => 'id',
                'ui' => 1,
            ],
            [
                'key' => 'field_diem_thanh_phan_1',
                'label' => 'Điểm thành phần 1',
                'name' => 'diem_thanh_phan_1_',
                'type' => 'number',
                'instructions' => 'Nhập điểm thành phần 1',
                'required' => 0,
                'min' => 0,
                'max' => 10,
                'step' => 0.1,
            ],
            [
                'key' => 'field_diem_thanh_phan_2',
                'label' => 'Điểm thành phần 2',
                'name' => 'diem_thanh_phan_2_',
                'type' => 'number',
                'instructions' => 'Nhập điểm thành phần 2',
                'required' => 0,
                'min' => 0,
                'max' => 10,
                'step' => 0.1,
            ],
            [
                'key' => 'field_diem_cuoi_ki',
                'label' => 'Điểm cuối kỳ',
                'name' => 'diem_cuoi_ki_',
                'type' => 'number',
                'instructions' => 'Nhập điểm cuối kỳ',
                'required' => 0,
                'min' => 0,
                'max' => 10,
                'step' => 0.1,
            ],
        ],
        'location' => [
            [
                [
                    'param' => 'post_type',
                    'operator' => '==',
                    'value' => 'diem',
                ],
            ],
        ],
        'menu_order' => 0,
        'position' => 'normal',
        'style' => 'default',
        'label_placement' => 'top',
        'instruction_placement' => 'label',
        'hide_on_screen' => '',
        'active' => true,
        'description' => '',
    ]);
}
add_action('acf/init', 'register_bang_diem_acf_fields');

// Shortcode hiển thị bảng điểm
function bang_diem_shortcode($atts) {
    // Tham số mặc định
    $atts = shortcode_atts(array(
        'sinhvien_id' => 0,     // ID sinh viên, 0 = hiển thị tất cả
        'monhoc_id' => 0,       // ID môn học, 0 = hiển thị tất cả
        'lop_id' => 0,          // ID lớp, 0 = hiển thị tất cả
        'limit' => -1,          // Số lượng kết quả, -1 = không giới hạn
    ), $atts);
    
    // Tạo tham số query
    $args = array(
        'post_type' => 'diem',
        'posts_per_page' => $atts['limit'],
        'meta_query' => array('relation' => 'AND')
    );
    
    // Thêm điều kiện lọc theo sinh viên
    if (!empty($atts['sinhvien_id'])) {
        $args['meta_query'][] = array(
            'key' => 'sinh_vien',
            'value' => $atts['sinhvien_id'],
            'compare' => '='
        );
    }
    
    // Thêm điều kiện lọc theo môn học
    if (!empty($atts['monhoc_id'])) {
        $args['meta_query'][] = array(
            'key' => 'mon_hoc',
            'value' => $atts['monhoc_id'],
            'compare' => '='
        );
    }
    
    // Thêm điều kiện lọc theo lớp
    if (!empty($atts['lop_id'])) {
        $args['meta_query'][] = array(
            'key' => 'lop',
            'value' => $atts['lop_id'],
            'compare' => '='
        );
    }
    
    // Thực hiện query
    $query = new WP_Query($args);
    
    // Nếu không có kết quả
    if (!$query->have_posts()) {
        return '<p>Không có dữ liệu điểm nào.</p>';
    }
    
    // Tạo bảng điểm
    $output = '<div class="bang-diem-container">';
    $output .= '<table class="bang-diem-table">';
    $output .= '<thead>';
    $output .= '<tr>';
    $output .= '<th>STT</th>';
    $output .= '<th>Sinh viên</th>';
    $output .= '<th>Môn học</th>';
    $output .= '<th>Lớp</th>';
    $output .= '<th>Điểm TP1</th>';
    $output .= '<th>Điểm TP2</th>';
    $output .= '<th>Điểm cuối kỳ</th>';
    $output .= '<th>Điểm TB</th>';
    $output .= '<th>Xếp loại</th>';
    $output .= '</tr>';
    $output .= '</thead>';
    $output .= '<tbody>';
    
    $stt = 1;
    while ($query->have_posts()) {
        $query->the_post();
        $post_id = get_the_ID();
        
        // Lấy dữ liệu
        $sinh_vien_id = get_field('sinh_vien', $post_id);
        $mon_hoc_id = get_field('mon_hoc', $post_id);
        $lop_id = get_field('lop', $post_id);
        $diem1 = get_field('diem_thanh_phan_1_', $post_id);
        $diem2 = get_field('diem_thanh_phan_2_', $post_id);
        $cuoiki = get_field('diem_cuoi_ki_', $post_id);
        
        // Tên sinh viên, môn học và lớp
        $sinh_vien = $sinh_vien_id ? get_the_title($sinh_vien_id) : 'N/A';
        $mon_hoc = $mon_hoc_id ? get_the_title($mon_hoc_id) : 'N/A';
        $lop = $lop_id ? get_the_title($lop_id) : 'N/A';
        
        // Tính trung bình
        $tb = '';
        if (is_numeric($diem1) && is_numeric($diem2) && is_numeric($cuoiki)) {
            $tb = round(($diem1 * 0.2 + $diem2 * 0.2 + $cuoiki * 0.6), 2);
        }
        
        // Xếp loại
        $xeploai = '';
        if ($tb !== '') {
            if ($tb >= 8.5) $xeploai = 'Giỏi';
            elseif ($tb >= 7) $xeploai = 'Khá';
            elseif ($tb >= 5.5) $xeploai = 'Trung bình';
            else $xeploai = 'Yếu';
        }
        
        // Thêm hàng vào bảng
        $output .= '<tr>';
        $output .= '<td>' . $stt . '</td>';
        $output .= '<td>' . esc_html($sinh_vien) . '</td>';
        $output .= '<td>' . esc_html($mon_hoc) . '</td>';
        $output .= '<td>' . esc_html($lop) . '</td>';
        $output .= '<td>' . ($diem1 !== '' ? esc_html($diem1) : 'N/A') . '</td>';
        $output .= '<td>' . ($diem2 !== '' ? esc_html($diem2) : 'N/A') . '</td>';
        $output .= '<td>' . ($cuoiki !== '' ? esc_html($cuoiki) : 'N/A') . '</td>';
        $output .= '<td>' . ($tb !== '' ? esc_html($tb) : 'N/A') . '</td>';
        $output .= '<td>' . ($xeploai !== '' ? esc_html($xeploai) : 'N/A') . '</td>';
        $output .= '</tr>';
        
        $stt++;
    }
    
    $output .= '</tbody>';
    $output .= '</table>';
    
    // CSS cho bảng điểm
    $output .= '<style>
        .bang-diem-container {
            overflow-x: auto;
            margin-bottom: 20px;
        }
        .bang-diem-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 14px;
            text-align: center;
        }
        .bang-diem-table th, 
        .bang-diem-table td {
            padding: 8px;
            border: 1px solid #ddd;
        }
        .bang-diem-table th {
            background-color: #f2f2f2;
            font-weight: bold;
        }
        .bang-diem-table tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .bang-diem-table tr:hover {
            background-color: #f5f5f5;
        }
    </style>';
    
    $output .= '</div>';
    
    wp_reset_postdata();
    
    return $output;
}
add_shortcode('bang_diem', 'bang_diem_shortcode');

// Shortcode tạo form tìm kiếm điểm
function tim_kiem_diem_shortcode($atts) {
    // Lấy danh sách sinh viên
    $students = get_posts([
        'post_type' => 'sinhvien',
        'posts_per_page' => -1,
        'orderby' => 'title',
        'order' => 'ASC'
    ]);
    
    // Lấy danh sách môn học
    $courses = get_posts([
        'post_type' => 'monhoc',
        'posts_per_page' => -1,
        'orderby' => 'title',
        'order' => 'ASC'
    ]);
    
    // Lấy danh sách lớp
    $classes = get_posts([
        'post_type' => 'lop',
        'posts_per_page' => -1,
        'orderby' => 'title',
        'order' => 'ASC'
    ]);
    
    // Lấy các tham số tìm kiếm từ URL (nếu có)
    $selected_student = isset($_GET['sinhvien']) ? intval($_GET['sinhvien']) : 0;
    $selected_course = isset($_GET['monhoc']) ? intval($_GET['monhoc']) : 0;
    $selected_class = isset($_GET['lop']) ? intval($_GET['lop']) : 0;
    
    // Tạo form tìm kiếm
    $output = '<div class="tim-kiem-diem-container">';
    $output .= '<h3>Tìm kiếm bảng điểm</h3>';
    $output .= '<form class="tim-kiem-diem-form" method="get">';
    
    // Giữ các tham số URL khác (nếu cần)
    foreach ($_GET as $key => $value) {
        if (!in_array($key, ['sinhvien', 'monhoc', 'lop', 'submit'])) {
            $output .= '<input type="hidden" name="'.esc_attr($key).'" value="'.esc_attr($value).'">';
        }
    }
    
    $output .= '<div class="form-row">';
    $output .= '<div class="form-group">';
    $output .= '<label for="sinhvien">Sinh viên:</label>';
    $output .= '<select name="sinhvien" id="sinhvien">';
    $output .= '<option value="0">-- Tất cả sinh viên --</option>';
    
    foreach ($students as $student) {
        $selected = ($selected_student == $student->ID) ? 'selected' : '';
        $output .= '<option value="'.esc_attr($student->ID).'" '.$selected.'>'.esc_html($student->post_title).'</option>';
    }
    
    $output .= '</select>';
    $output .= '</div>';
    
    $output .= '<div class="form-group">';
    $output .= '<label for="monhoc">Môn học:</label>';
    $output .= '<select name="monhoc" id="monhoc">';
    $output .= '<option value="0">-- Tất cả môn học --</option>';
    
    foreach ($courses as $course) {
        $selected = ($selected_course == $course->ID) ? 'selected' : '';
        $output .= '<option value="'.esc_attr($course->ID).'" '.$selected.'>'.esc_html($course->post_title).'</option>';
    }
    
    $output .= '</select>';
    $output .= '</div>';
    
    $output .= '<div class="form-group">';
    $output .= '<label for="lop">Lớp:</label>';
    $output .= '<select name="lop" id="lop">';
    $output .= '<option value="0">-- Tất cả lớp --</option>';
    
    foreach ($classes as $class) {
        $selected = ($selected_class == $class->ID) ? 'selected' : '';
        $output .= '<option value="'.esc_attr($class->ID).'" '.$selected.'>'.esc_html($class->post_title).'</option>';
    }
    
    $output .= '</select>';
    $output .= '</div>';
    
    $output .= '<div class="form-group">';
    $output .= '<label>&nbsp;</label>';
    $output .= '<button type="submit" name="submit" class="search-btn">Tìm kiếm</button>';
    $output .= '</div>';
    $output .= '</div>';
    $output .= '</form>';
    
    // CSS cho form tìm kiếm
    $output .= '<style>
        .tim-kiem-diem-container {
            margin-bottom: 30px;
            padding: 20px;
            background: #f9f9f9;
            border-radius: 5px;
            border: 1px solid #ddd;
        }
        .tim-kiem-diem-container h3 {
            margin-top: 0;
            margin-bottom: 20px;
        }
        .tim-kiem-diem-form .form-row {
            display: flex;
            flex-wrap: wrap;
            margin: 0 -10px;
        }
        .tim-kiem-diem-form .form-group {
            padding: 0 10px;
            margin-bottom: 15px;
            flex: 1;
            min-width: 200px;
        }
        .tim-kiem-diem-form label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        .tim-kiem-diem-form select {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        .tim-kiem-diem-form .search-btn {
            background-color: #0073aa;
            color: #fff;
            border: none;
            padding: 8px 15px;
            border-radius: 4px;
            cursor: pointer;
            font-weight: bold;
            transition: background-color 0.3s;
            width: 100%;
        }
        .tim-kiem-diem-form .search-btn:hover {
            background-color: #005177;
        }
        @media (max-width: 768px) {
            .tim-kiem-diem-form .form-group {
                min-width: 100%;
            }
        }
    </style>';
    
    // Hiển thị kết quả tìm kiếm nếu đã submit form
    if (isset($_GET['submit'])) {
        // Tạo shortcode với các tham số tìm kiếm
        $shortcode_atts = array();
        
        if ($selected_student) {
            $shortcode_atts['sinhvien_id'] = $selected_student;
        }
        
        if ($selected_course) {
            $shortcode_atts['monhoc_id'] = $selected_course;
        }
        
        if ($selected_class) {
            $shortcode_atts['lop_id'] = $selected_class;
        }
        
        // Thực hiện shortcode bảng điểm với các tham số đã chọn
        $output .= bang_diem_shortcode($shortcode_atts);
    }
    
    $output .= '</div>';
    
    return $output;
}
add_shortcode('tim_kiem_diem', 'tim_kiem_diem_shortcode');