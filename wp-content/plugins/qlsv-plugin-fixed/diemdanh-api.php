<?php
/**
 * API Điểm danh - Kiểm tra trực tiếp chức năng điểm danh
 */

// Tải WordPress
define('WP_USE_THEMES', false);
require_once('../../../wp-load.php');

// Kiểm tra người dùng đăng nhập
if (!is_user_logged_in()) {
    wp_die('Bạn cần đăng nhập để sử dụng API này');
}

// Lấy tham số
$action = isset($_REQUEST['action']) ? sanitize_text_field($_REQUEST['action']) : '';
$lop_id = isset($_REQUEST['lop']) ? intval($_REQUEST['lop']) : 0;
$mon_hoc_id = isset($_REQUEST['mon_hoc']) ? intval($_REQUEST['mon_hoc']) : 0;

header('Content-Type: application/json');

// Hàm lấy danh sách sinh viên theo lớp
function get_students_by_class($lop_id) {
    $students = array();
    
    $args = array(
        'post_type' => 'sinhvien',
        'posts_per_page' => -1,
        'meta_query' => array(
            array(
                'key' => 'lop',
                'value' => $lop_id,
                'compare' => '='
            )
        ),
        'orderby' => 'title',
        'order' => 'ASC'
    );
    
    $query = new WP_Query($args);
    
    if ($query->have_posts()) {
        while ($query->have_posts()) {
            $query->the_post();
            $students[] = array(
                'id' => get_the_ID(),
                'name' => get_the_title(),
                'email' => get_field('email', get_the_ID()),
                'mssv' => get_field('ma_sinh_vien', get_the_ID()),
            );
        }
        wp_reset_postdata();
    }
    
    return $students;
}

// Hàm lấy buổi học điểm danh
function get_attendance_sessions($lop_id, $mon_hoc_id) {
    $sessions = array();
    
    $args = array(
        'post_type' => 'diemdanh',
        'posts_per_page' => -1,
        'meta_query' => array(
            'relation' => 'AND',
            array(
                'key' => 'lop',
                'value' => $lop_id,
                'compare' => '='
            ),
            array(
                'key' => 'mon_hoc',
                'value' => $mon_hoc_id,
                'compare' => '='
            )
        ),
        'orderby' => 'meta_value',
        'meta_key' => 'ngay',
        'order' => 'DESC'
    );
    
    $query = new WP_Query($args);
    
    if ($query->have_posts()) {
        while ($query->have_posts()) {
            $query->the_post();
            $diemdanh_id = get_the_ID();
            $ngay = get_field('ngay', $diemdanh_id);
            $sessions[] = array(
                'id' => $diemdanh_id,
                'title' => get_the_title(),
                'date' => $ngay,
                'formatted_date' => date_i18n('d/m/Y', strtotime($ngay)),
            );
        }
        wp_reset_postdata();
    }
    
    return $sessions;
}

switch ($action) {
    case 'get_students':
        if (empty($lop_id)) {
            echo json_encode(array('success' => false, 'message' => 'Không có ID lớp'));
            exit;
        }
        
        $students = get_students_by_class($lop_id);
        echo json_encode(array('success' => true, 'data' => $students));
        break;
        
    case 'get_sessions':
        if (empty($lop_id) || empty($mon_hoc_id)) {
            echo json_encode(array('success' => false, 'message' => 'Thiếu ID lớp hoặc môn học'));
            exit;
        }
        
        $sessions = get_attendance_sessions($lop_id, $mon_hoc_id);
        echo json_encode(array('success' => true, 'data' => $sessions));
        break;
        
    case 'get_diemdanh_data':
        if (empty($lop_id) || empty($mon_hoc_id)) {
            echo json_encode(array('success' => false, 'message' => 'Thiếu ID lớp hoặc môn học'));
            exit;
        }
        
        $students = get_students_by_class($lop_id);
        $sessions = get_attendance_sessions($lop_id, $mon_hoc_id);
        
        $lop_title = get_the_title($lop_id);
        $monhoc_title = get_the_title($mon_hoc_id);
        
        echo json_encode(array(
            'success' => true,
            'data' => array(
                'lop' => array('id' => $lop_id, 'title' => $lop_title),
                'monhoc' => array('id' => $mon_hoc_id, 'title' => $monhoc_title),
                'students' => $students,
                'sessions' => $sessions
            )
        ));
        break;
        
    default:
        echo json_encode(array('success' => false, 'message' => 'Hành động không hợp lệ'));
}
?> 