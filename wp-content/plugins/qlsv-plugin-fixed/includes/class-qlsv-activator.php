<?php
/**
 * Class được kích hoạt khi plugin được kích hoạt
 */
class QLSV_Activator {

    /**
     * Phương thức được gọi khi plugin được kích hoạt
     */
    public static function activate() {
        // Đăng ký Custom Post Types
        self::register_post_types();
        
        // Tạo trang mặc định
        self::create_default_pages();
        
        // Flush rewrite rules để cập nhật permalink
        flush_rewrite_rules();
    }
    
    /**
     * Đăng ký các Custom Post Types
     */
    private static function register_post_types() {
        // Custom Post Type Sinh viên
        register_post_type('sinhvien', array(
            'labels' => array(
                'name'               => 'Sinh viên',
                'singular_name'      => 'Sinh viên',
                'menu_name'          => 'Sinh viên',
                'add_new'            => 'Thêm mới',
                'add_new_item'       => 'Thêm sinh viên mới',
                'edit_item'          => 'Sửa sinh viên',
                'new_item'           => 'Sinh viên mới',
                'view_item'          => 'Xem sinh viên',
                'search_items'       => 'Tìm kiếm sinh viên',
                'not_found'          => 'Không tìm thấy sinh viên',
                'not_found_in_trash' => 'Không có sinh viên nào trong thùng rác'
            ),
            'public'             => true,
            'publicly_queryable' => true,
            'show_ui'            => true,
            'show_in_menu'       => true,
            'query_var'          => true,
            'rewrite'            => array('slug' => 'sinhvien'),
            'capability_type'    => 'post',
            'has_archive'        => true,
            'hierarchical'       => false,
            'menu_position'      => 5,
            'menu_icon'          => 'dashicons-id',
            'supports'           => array('title', 'editor', 'thumbnail')
        ));
        
        // Custom Post Type Lớp
        register_post_type('lop', array(
            'labels' => array(
                'name'               => 'Lớp',
                'singular_name'      => 'Lớp',
                'menu_name'          => 'Lớp',
                'add_new'            => 'Thêm mới',
                'add_new_item'       => 'Thêm lớp mới',
                'edit_item'          => 'Sửa lớp',
                'new_item'           => 'Lớp mới',
                'view_item'          => 'Xem lớp',
                'search_items'       => 'Tìm kiếm lớp',
                'not_found'          => 'Không tìm thấy lớp',
                'not_found_in_trash' => 'Không có lớp nào trong thùng rác'
            ),
            'public'             => true,
            'publicly_queryable' => true,
            'show_ui'            => true,
            'show_in_menu'       => true,
            'query_var'          => true,
            'rewrite'            => array('slug' => 'lop'),
            'capability_type'    => 'post',
            'has_archive'        => true,
            'hierarchical'       => false,
            'menu_position'      => 6,
            'menu_icon'          => 'dashicons-groups',
            'supports'           => array('title', 'editor')
        ));
        
        // Custom Post Type Môn học
        register_post_type('monhoc', array(
            'labels' => array(
                'name'               => 'Môn học',
                'singular_name'      => 'Môn học',
                'menu_name'          => 'Môn học',
                'add_new'            => 'Thêm mới',
                'add_new_item'       => 'Thêm môn học mới',
                'edit_item'          => 'Sửa môn học',
                'new_item'           => 'Môn học mới',
                'view_item'          => 'Xem môn học',
                'search_items'       => 'Tìm kiếm môn học',
                'not_found'          => 'Không tìm thấy môn học',
                'not_found_in_trash' => 'Không có môn học nào trong thùng rác'
            ),
            'public'             => true,
            'publicly_queryable' => true,
            'show_ui'            => true,
            'show_in_menu'       => true,
            'query_var'          => true,
            'rewrite'            => array('slug' => 'monhoc'),
            'capability_type'    => 'post',
            'has_archive'        => true,
            'hierarchical'       => false,
            'menu_position'      => 7,
            'menu_icon'          => 'dashicons-book',
            'supports'           => array('title', 'editor')
        ));
        
        // Custom Post Type Điểm
        register_post_type('diem', array(
            'labels' => array(
                'name'               => 'Điểm',
                'singular_name'      => 'Điểm',
                'menu_name'          => 'Điểm',
                'add_new'            => 'Thêm mới',
                'add_new_item'       => 'Thêm điểm mới',
                'edit_item'          => 'Sửa điểm',
                'new_item'           => 'Điểm mới',
                'view_item'          => 'Xem điểm',
                'search_items'       => 'Tìm kiếm điểm',
                'not_found'          => 'Không tìm thấy điểm',
                'not_found_in_trash' => 'Không có điểm nào trong thùng rác'
            ),
            'public'             => true,
            'publicly_queryable' => true,
            'show_ui'            => true,
            'show_in_menu'       => true,
            'query_var'          => true,
            'rewrite'            => array(
                'slug' => 'diem-record',
                'with_front' => false
            ),
            'capability_type'    => 'post',
            'has_archive'        => false,
            'hierarchical'       => false,
            'menu_position'      => 8,
            'menu_icon'          => 'dashicons-chart-bar',
            'supports'           => array('title')
        ));
        
        // Custom Post Type Thời khóa biểu
        register_post_type('thoikhoabieu', array(
            'labels' => array(
                'name'               => 'Thời khóa biểu',
                'singular_name'      => 'Thời khóa biểu',
                'menu_name'          => 'Thời khóa biểu',
                'add_new'            => 'Thêm mới',
                'add_new_item'       => 'Thêm lịch học mới',
                'edit_item'          => 'Sửa lịch học',
                'new_item'           => 'Lịch học mới',
                'view_item'          => 'Xem lịch học',
                'search_items'       => 'Tìm kiếm lịch học',
                'not_found'          => 'Không tìm thấy lịch học',
                'not_found_in_trash' => 'Không có lịch học nào trong thùng rác'
            ),
            'public'             => true,
            'publicly_queryable' => true,
            'show_ui'            => true,
            'show_in_menu'       => true,
            'query_var'          => true,
            'rewrite'            => array('slug' => 'thoikhoabieu'),
            'capability_type'    => 'post',
            'has_archive'        => true,
            'hierarchical'       => false,
            'menu_position'      => 9,
            'menu_icon'          => 'dashicons-calendar-alt',
            'supports'           => array('title')
        ));
        
        // Custom Post Type Điểm danh
        register_post_type('diemdanh', array(
            'labels' => array(
                'name'               => 'Điểm danh',
                'singular_name'      => 'Điểm danh',
                'menu_name'          => 'Điểm danh',
                'add_new'            => 'Thêm mới',
                'add_new_item'       => 'Thêm buổi điểm danh mới',
                'edit_item'          => 'Sửa buổi điểm danh',
                'new_item'           => 'Buổi điểm danh mới',
                'view_item'          => 'Xem buổi điểm danh',
                'search_items'       => 'Tìm kiếm buổi điểm danh',
                'not_found'          => 'Không tìm thấy buổi điểm danh',
                'not_found_in_trash' => 'Không có buổi điểm danh nào trong thùng rác'
            ),
            'public'             => true,
            'publicly_queryable' => true,
            'show_ui'            => true,
            'show_in_menu'       => true,
            'query_var'          => true,
            'rewrite'            => array(
                'slug' => 'diemdanh-record',
                'with_front' => false
            ),
            'capability_type'    => 'post',
            'has_archive'        => false,
            'hierarchical'       => false,
            'menu_position'      => 10,
            'menu_icon'          => 'dashicons-clipboard',
            'supports'           => array('title')
        ));
    }
    
    /**
     * Tạo các trang mặc định
     */
    private static function create_default_pages() {
        // Trang Danh sách sinh viên
        if (!get_page_by_path('danh-sach-sinh-vien')) {
            wp_insert_post(array(
                'post_title'     => 'Danh sách sinh viên',
                'post_name'      => 'danh-sach-sinh-vien',
                'post_content'   => '[qlsv_danh_sach_sinh_vien]',
                'post_status'    => 'publish',
                'post_type'      => 'page',
                'comment_status' => 'closed'
            ));
        }
        
        // Trang Tra cứu điểm
        if (!get_page_by_path('tra-cuu-diem')) {
            wp_insert_post(array(
                'post_title'     => 'Tra cứu điểm',
                'post_name'      => 'tra-cuu-diem',
                'post_content'   => '[qlsv_tim_kiem_diem]',
                'post_status'    => 'publish',
                'post_type'      => 'page',
                'comment_status' => 'closed'
            ));
        }
        
        // Trang Thông tin sinh viên
        if (!get_page_by_path('thong-tin-sinh-vien')) {
            wp_insert_post(array(
                'post_title'     => 'Thông tin sinh viên',
                'post_name'      => 'thong-tin-sinh-vien',
                'post_content'   => '[qlsv_thong_tin_sinh_vien]',
                'post_status'    => 'publish',
                'post_type'      => 'page',
                'comment_status' => 'closed'
            ));
        }
        
        // Trang Thời khóa biểu
        if (!get_page_by_path('thoi-khoa-bieu')) {
            wp_insert_post(array(
                'post_title'     => 'Thời khóa biểu',
                'post_name'      => 'thoi-khoa-bieu',
                'post_content'   => '[qlsv_thoikhoabieu]',
                'post_status'    => 'publish',
                'post_type'      => 'page',
                'comment_status' => 'closed'
            ));
        }
        
        // Trang Điểm danh
        if (!get_page_by_path('diemdanhh')) {
            $diemdanh_page_id = wp_insert_post(array(
                'post_title'     => 'Điểm danh',
                'post_name'      => 'diemdanhh',
                'post_content'   => '[qlsv_diemdanh_dashboard]',
                'post_status'    => 'publish',
                'post_type'      => 'page',
                'comment_status' => 'closed'
            ));
            
            if ($diemdanh_page_id && !is_wp_error($diemdanh_page_id)) {
                update_post_meta($diemdanh_page_id, '_wp_page_template', 'diemdanh-page.php');
            }
        }
        
        // Trang thông tin cá nhân
        if (!get_page_by_path('thong-tin-ca-nhan')) {
            wp_insert_post(array(
                'post_title'     => 'Thông tin cá nhân',
                'post_name'      => 'thong-tin-ca-nhan',
                'post_content'   => '[qlsv_user_profile]',
                'post_status'    => 'publish',
                'post_type'      => 'page',
                'comment_status' => 'closed'
            ));
        }
        
        // Trang Kết quả học tập
        if (!get_page_by_path('ket-qua-hoc-tap')) {
            $ketqua_page_id = wp_insert_post(array(
                'post_title'     => 'Kết Quả Học Tập',
                'post_name'      => 'ket-qua-hoc-tap',
                'post_content'   => '[qlsv_tim_kiem_diem]',
                'post_status'    => 'publish',
                'post_type'      => 'page',
                'comment_status' => 'closed'
            ));
            
            if ($ketqua_page_id && !is_wp_error($ketqua_page_id)) {
                update_post_meta($ketqua_page_id, '_wp_page_template', 'ket-qua-hoc-tap-template.php');
            }
        }
    }
} 