<?php
/**
 * Class quản lý lớp và các chức năng liên quan
 */
class QLSV_Lop {

    /**
     * Loader để đăng ký các hooks
     *
     * @var QLSV_Loader
     */
    private $loader;

    /**
     * Khởi tạo class lớp
     */
    public function __construct($loader) {
        $this->loader = $loader;
        
        // Đăng ký các hooks
        $this->register_hooks();
        
        // Đăng ký các shortcodes
        $this->register_shortcodes();
    }
    
    /**
     * Đăng ký các hooks cần thiết
     */
    private function register_hooks() {
        // Đăng ký ACF fields
        $this->loader->add_action('acf/init', $this, 'register_acf_fields');
        
        // Thêm metabox cho lớp
        $this->loader->add_action('add_meta_boxes', $this, 'add_lop_meta_boxes');
    }
    
    /**
     * Đăng ký các shortcodes
     */
    private function register_shortcodes() {
        add_shortcode('qlsv_danh_sach_lop', array($this, 'danh_sach_lop_shortcode'));
    }
    
    /**
     * Đăng ký ACF Fields cho lớp
     */
    public function register_acf_fields() {
        if (!function_exists('acf_add_local_field_group')) {
            return;
        }

        acf_add_local_field_group(array(
            'key' => 'group_thong_tin_lop',
            'title' => 'Thông tin lớp',
            'fields' => array(
                array(
                    'key' => 'field_ten_lop',
                    'label' => 'Tên lớp',
                    'name' => 'ten_lop',
                    'type' => 'text',
                    'instructions' => 'Nhập tên lớp',
                    'required' => 1,
                ),
                array(
                    'key' => 'field_khoa',
                    'label' => 'Khoa',
                    'name' => 'khoa',
                    'type' => 'text',
                    'instructions' => 'Nhập khoa',
                    'required' => 0,
                ),
                array(
                    'key' => 'field_co_van_hoc_tap',
                    'label' => 'Cố vấn học tập',
                    'name' => 'co_van_hoc_tap',
                    'type' => 'user',
                    'instructions' => 'Chọn cố vấn học tập',
                    'required' => 0,
                ),
                array(
                    'key' => 'field_si_so',
                    'label' => 'Sĩ số',
                    'name' => 'si_so',
                    'type' => 'number',
                    'instructions' => 'Nhập sĩ số lớp',
                    'required' => 0,
                ),
            ),
            'location' => array(
                array(
                    array(
                        'param' => 'post_type',
                        'operator' => '==',
                        'value' => 'lop',
                    ),
                ),
            ),
            'menu_order' => 0,
            'position' => 'normal',
            'style' => 'default',
            'label_placement' => 'top',
            'instruction_placement' => 'label',
            'hide_on_screen' => '',
            'active' => true,
            'description' => '',
        ));
    }
    
    /**
     * Thêm metabox cho lớp
     */
    public function add_lop_meta_boxes() {
        add_meta_box(
            'lop_info',
            'Thông tin lớp',
            array($this, 'render_meta_box'),
            'lop',
            'normal',
            'high'
        );
        
        add_meta_box(
            'lop_students',
            'Danh sách sinh viên trong lớp',
            array($this, 'render_students_meta_box'),
            'lop',
            'normal',
            'default'
        );
    }
    
    /**
     * Render metabox thông tin lớp
     */
    public function render_meta_box($post) {
        // ACF đã xử lý
    }
    
    /**
     * Render metabox danh sách sinh viên trong lớp
     */
    public function render_students_meta_box($post) {
        $lop_id = $post->ID;
        
        // Query sinh viên thuộc lớp
        $args = array(
            'post_type' => 'sinhvien',
            'posts_per_page' => -1,
            'orderby' => 'title',
            'order' => 'ASC',
            'meta_query' => array(
                array(
                    'key' => 'lop',
                    'value' => $lop_id,
                    'compare' => '='
                )
            )
        );
        
        $query = new WP_Query($args);
        
        echo '<div class="lop-students-list">';
        
        // Nếu không có sinh viên nào
        if (!$query->have_posts()) {
            echo '<p>Không có sinh viên nào thuộc lớp này.</p>';
        } else {
            echo '<table class="wp-list-table widefat fixed striped">';
            echo '<thead>';
            echo '<tr>';
            echo '<th>STT</th>';
            echo '<th>Họ và tên</th>';
            echo '<th>Mã sinh viên</th>';
            echo '<th>Email</th>';
            echo '<th>Trạng thái</th>';
            echo '<th>Hành động</th>';
            echo '</tr>';
            echo '</thead>';
            echo '<tbody>';
            
            $stt = 1;
            while ($query->have_posts()) {
                $query->the_post();
                $student_id = get_the_ID();
                
                // Lấy thông tin sinh viên
                $ma_sv = get_field('ma_sinh_vien', $student_id);
                $email = get_field('email', $student_id);
                $trang_thai = get_field('trang_thai', $student_id) ?: 'Đang học';
                
                echo '<tr>';
                echo '<td>' . $stt . '</td>';
                echo '<td><a href="' . get_edit_post_link($student_id) . '">' . get_the_title($student_id) . '</a></td>';
                echo '<td>' . esc_html($ma_sv) . '</td>';
                echo '<td>' . esc_html($email) . '</td>';
                echo '<td>' . esc_html($trang_thai) . '</td>';
                echo '<td><a href="' . get_edit_post_link($student_id) . '" class="button button-small">Sửa</a></td>';
                echo '</tr>';
                
                $stt++;
            }
            
            echo '</tbody>';
            echo '</table>';
        }
        
        echo '<p><a href="' . admin_url('post-new.php?post_type=sinhvien&lop_id=' . $lop_id) . '" class="button">Thêm sinh viên mới vào lớp</a></p>';
        echo '</div>';
        
        wp_reset_postdata();
    }
    
    /**
     * Shortcode danh sách lớp
     */
    public function danh_sach_lop_shortcode($atts) {
        // Tham số mặc định
        $atts = shortcode_atts(array(
            'khoa' => '',       // Lọc theo khoa
            'limit' => -1,      // Số lượng lớp hiển thị, -1 = tất cả
            'orderby' => 'title', // Sắp xếp theo tên
            'order' => 'ASC',    // Thứ tự sắp xếp
        ), $atts);
        
        // Tham số truy vấn
        $args = array(
            'post_type' => 'lop',
            'posts_per_page' => $atts['limit'],
            'orderby' => $atts['orderby'],
            'order' => $atts['order'],
        );
        
        // Lấy khoa từ query parameter nếu có
        $khoa_from_get = isset($_GET['khoa']) ? sanitize_text_field($_GET['khoa']) : '';
        if ($khoa_from_get) {
            $khoa = $khoa_from_get;
        } else {
            $khoa = $atts['khoa'];
        }
        
        // Thêm điều kiện lọc theo khoa nếu có
        if (!empty($khoa)) {
            $args['meta_query'] = array(
                array(
                    'key' => 'khoa',
                    'value' => $khoa,
                    'compare' => 'LIKE',
                )
            );
        }
        
        // Thực hiện truy vấn
        $query = new WP_Query($args);
        
        // Lấy danh sách khoa để lọc
        $khoa_list = array();
        $khoa_query = new WP_Query([
            'post_type' => 'lop',
            'posts_per_page' => -1,
            'fields' => 'ids'
        ]);
        
        if ($khoa_query->have_posts()) {
            foreach ($khoa_query->posts as $lop_id) {
                $khoa_value = get_field('khoa', $lop_id);
                if ($khoa_value && !in_array($khoa_value, $khoa_list)) {
                    $khoa_list[] = $khoa_value;
                }
            }
        }
        sort($khoa_list);
        
        // Tạo output HTML
        $output = '<div class="danh-sach-lop-container">';
        
        // Tạo bộ lọc
        $output .= '<div class="lop-filter">';
        $output .= '<form class="filter-form" method="get">';
        
        // Giữ các tham số URL khác (nếu cần)
        foreach ($_GET as $key => $value) {
            if ($key !== 'khoa') {
                $output .= '<input type="hidden" name="' . esc_attr($key) . '" value="' . esc_attr($value) . '">';
            }
        }
        
        $output .= '<div class="filter-group">';
        $output .= '<label for="khoa_filter">Khoa:</label>';
        $output .= '<select name="khoa" id="khoa_filter">';
        $output .= '<option value="">-- Tất cả khoa --</option>';
        
        foreach ($khoa_list as $khoa_item) {
            $selected = ($khoa === $khoa_item) ? 'selected' : '';
            $output .= '<option value="' . esc_attr($khoa_item) . '" ' . $selected . '>' . esc_html($khoa_item) . '</option>';
        }
        
        $output .= '</select>';
        $output .= '</div>';
        
        $output .= '<div class="filter-group">';
        $output .= '<button type="submit" class="filter-btn">Lọc</button>';
        $output .= '</div>';
        
        $output .= '</form>';
        $output .= '</div>'; // End filter
        
        // Nếu không có lớp nào
        if (!$query->have_posts()) {
            $output .= '<p>Không có lớp nào.</p>';
            $output .= '</div>'; // Close container
            return $output;
        }
        
        // Tạo bảng danh sách lớp
        $output .= '<table class="lop-table">';
        $output .= '<thead>';
        $output .= '<tr>';
        $output .= '<th>STT</th>';
        $output .= '<th>Tên lớp</th>';
        $output .= '<th>Khoa</th>';
        $output .= '<th>Cố vấn học tập</th>';
        $output .= '<th>Sĩ số</th>';
        $output .= '<th>Danh sách sinh viên</th>';
        $output .= '</tr>';
        $output .= '</thead>';
        $output .= '<tbody>';
        
        $stt = 1;
        while ($query->have_posts()) {
            $query->the_post();
            $post_id = get_the_ID();
            
            // Lấy thông tin lớp
            $ten_lop = get_the_title();
            $khoa_value = get_field('khoa', $post_id);
            $co_van_id = get_field('co_van_hoc_tap', $post_id);
            $si_so = get_field('si_so', $post_id);
            
            // Lấy tên cố vấn
            $co_van_name = '';
            if ($co_van_id) {
                $co_van = get_userdata($co_van_id);
                if ($co_van) {
                    $co_van_name = $co_van->display_name;
                }
            }
            
            // Đếm số sinh viên thực tế trong lớp
            $student_count = 0;
            $student_query = new WP_Query([
                'post_type' => 'sinhvien',
                'posts_per_page' => -1,
                'fields' => 'ids',
                'meta_query' => [
                    [
                        'key' => 'lop',
                        'value' => $post_id,
                        'compare' => '='
                    ]
                ]
            ]);
            $student_count = $student_query->found_posts;
            
            $output .= '<tr>';
            $output .= '<td>' . $stt . '</td>';
            $output .= '<td>' . esc_html($ten_lop) . '</td>';
            $output .= '<td>' . esc_html($khoa_value) . '</td>';
            $output .= '<td>' . esc_html($co_van_name) . '</td>';
            $output .= '<td>' . ($student_count ?: ($si_so ?: '0')) . '</td>';
            $output .= '<td><a href="' . esc_url(add_query_arg(['lop' => $post_id], get_permalink(get_page_by_path('danh-sach-sinh-vien')))) . '" class="view-btn">Xem danh sách</a></td>';
            $output .= '</tr>';
            
            $stt++;
        }
        
        $output .= '</tbody>';
        $output .= '</table>';
        
        // CSS cho bảng và bộ lọc
        $output .= '<style>
            .danh-sach-lop-container {
                margin-bottom: 30px;
            }
            .lop-filter {
                margin-bottom: 20px;
                padding: 15px;
                background: #f5f5f5;
                border-radius: 5px;
            }
            .filter-form {
                display: flex;
                flex-wrap: wrap;
                align-items: flex-end;
            }
            .filter-group {
                margin-right: 15px;
                margin-bottom: 10px;
            }
            .filter-group label {
                display: block;
                margin-bottom: 5px;
                font-weight: bold;
            }
            .filter-group select {
                padding: 8px;
                min-width: 200px;
                border: 1px solid #ddd;
                border-radius: 4px;
            }
            .filter-btn {
                background: #0073aa;
                color: #fff;
                border: none;
                padding: 10px 20px;
                border-radius: 4px;
                cursor: pointer;
                font-weight: bold;
            }
            .filter-btn:hover {
                background: #005177;
            }
            .lop-table {
                width: 100%;
                border-collapse: collapse;
                margin-top: 20px;
            }
            .lop-table th, 
            .lop-table td {
                padding: 10px;
                border: 1px solid #ddd;
                text-align: left;
            }
            .lop-table th {
                background-color: #f2f2f2;
                font-weight: bold;
            }
            .lop-table tr:hover {
                background-color: #f5f5f5;
            }
            .view-btn {
                display: inline-block;
                background: #4CAF50;
                color: white;
                text-decoration: none;
                padding: 5px 10px;
                border-radius: 4px;
                text-align: center;
            }
            .view-btn:hover {
                background: #45a049;
                color: white;
            }
        </style>';
        
        $output .= '</div>'; // End container
        
        wp_reset_postdata();
        
        return $output;
    }
} 