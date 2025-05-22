<?php
/**
 * Class quản lý môn học và các chức năng liên quan
 */
class QLSV_MonHoc {

    /**
     * Loader để đăng ký các hooks
     *
     * @var QLSV_Loader
     */
    private $loader;

    /**
     * Khởi tạo class môn học
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
        
        // Thêm metabox cho môn học
        $this->loader->add_action('add_meta_boxes', $this, 'add_monhoc_meta_boxes');
    }
    
    /**
     * Đăng ký các shortcodes
     */
    private function register_shortcodes() {
        add_shortcode('qlsv_danh_sach_mon_hoc', array($this, 'danh_sach_mon_hoc_shortcode'));
    }
    
    /**
     * Đăng ký ACF Fields cho môn học
     */
    public function register_acf_fields() {
        if (!function_exists('acf_add_local_field_group')) {
            return;
        }

        acf_add_local_field_group(array(
            'key' => 'group_mon_hoc',
            'title' => 'Thông tin môn học',
            'fields' => array(
                array(
                    'key' => 'field_ma_mon',
                    'label' => 'Mã môn',
                    'name' => 'ma_mon',
                    'type' => 'text',
                    'instructions' => 'Nhập mã môn học',
                    'required' => 1,
                ),
                array(
                    'key' => 'field_so_tin_chi',
                    'label' => 'Số tín chỉ',
                    'name' => 'so_tin_chi',
                    'type' => 'number',
                    'instructions' => 'Nhập số tín chỉ',
                    'required' => 1,
                    'min' => 1,
                    'max' => 10,
                    'step' => 1,
                ),
                array(
                    'key' => 'field_khoa',
                    'label' => 'Khoa',
                    'name' => 'khoa',
                    'type' => 'text',
                    'instructions' => 'Nhập khoa phụ trách',
                    'required' => 0,
                ),
                array(
                    'key' => 'field_mo_ta',
                    'label' => 'Mô tả',
                    'name' => 'mo_ta',
                    'type' => 'textarea',
                    'instructions' => 'Mô tả môn học',
                    'required' => 0,
                ),
                array(
                    'key' => 'field_dieu_kien',
                    'label' => 'Điều kiện tiên quyết',
                    'name' => 'dieu_kien',
                    'type' => 'post_object',
                    'instructions' => 'Chọn môn học tiên quyết (nếu có)',
                    'required' => 0,
                    'post_type' => array('monhoc'),
                    'multiple' => 1,
                    'return_format' => 'id',
                    'ui' => 1,
                ),
            ),
            'location' => array(
                array(
                    array(
                        'param' => 'post_type',
                        'operator' => '==',
                        'value' => 'monhoc',
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
     * Thêm metabox cho môn học
     */
    public function add_monhoc_meta_boxes() {
        add_meta_box(
            'monhoc_info',
            'Thông tin môn học',
            array($this, 'render_meta_box'),
            'monhoc',
            'normal',
            'high'
        );
        
        add_meta_box(
            'monhoc_diem',
            'Danh sách điểm môn học',
            array($this, 'render_diem_meta_box'),
            'monhoc',
            'normal',
            'default'
        );
    }
    
    /**
     * Render metabox thông tin môn học
     */
    public function render_meta_box($post) {
        // ACF đã xử lý
    }
    
    /**
     * Render metabox danh sách điểm môn học
     */
    public function render_diem_meta_box($post) {
        $monhoc_id = $post->ID;
        
        // Query điểm liên quan đến môn học
        $args = array(
            'post_type' => 'diem',
            'posts_per_page' => -1,
            'meta_query' => array(
                array(
                    'key' => 'mon_hoc',
                    'value' => $monhoc_id,
                    'compare' => '='
                )
            )
        );
        
        $query = new WP_Query($args);
        
        echo '<div class="monhoc-diem-list">';
        
        // Nếu không có điểm nào
        if (!$query->have_posts()) {
            echo '<p>Không có thông tin điểm nào cho môn học này.</p>';
        } else {
            echo '<table class="wp-list-table widefat fixed striped">';
            echo '<thead>';
            echo '<tr>';
            echo '<th>STT</th>';
            echo '<th>Sinh viên</th>';
            echo '<th>Lớp</th>';
            echo '<th>Điểm TP1</th>';
            echo '<th>Điểm TP2</th>';
            echo '<th>Điểm cuối kỳ</th>';
            echo '<th>Điểm TB</th>';
            echo '<th>Xếp loại</th>';
            echo '<th>Hành động</th>';
            echo '</tr>';
            echo '</thead>';
            echo '<tbody>';
            
            $stt = 1;
            while ($query->have_posts()) {
                $query->the_post();
                $diem_id = get_the_ID();
                
                // Lấy thông tin điểm
                $sinh_vien_id = get_field('sinh_vien', $diem_id);
                $lop_id = get_field('lop', $diem_id);
                $diem1 = get_field('diem_thanh_phan_1_', $diem_id);
                $diem2 = get_field('diem_thanh_phan_2_', $diem_id);
                $cuoiki = get_field('diem_cuoi_ki_', $diem_id);
                
                // Tên sinh viên và lớp
                $sinh_vien = $sinh_vien_id ? get_the_title($sinh_vien_id) : 'N/A';
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
                
                echo '<tr>';
                echo '<td>' . $stt . '</td>';
                echo '<td>' . esc_html($sinh_vien) . '</td>';
                echo '<td>' . esc_html($lop) . '</td>';
                echo '<td>' . ($diem1 !== '' ? esc_html($diem1) : 'N/A') . '</td>';
                echo '<td>' . ($diem2 !== '' ? esc_html($diem2) : 'N/A') . '</td>';
                echo '<td>' . ($cuoiki !== '' ? esc_html($cuoiki) : 'N/A') . '</td>';
                echo '<td>' . ($tb !== '' ? esc_html($tb) : 'N/A') . '</td>';
                echo '<td>' . ($xeploai !== '' ? esc_html($xeploai) : 'N/A') . '</td>';
                echo '<td><a href="' . get_edit_post_link($diem_id) . '" class="button button-small">Sửa</a></td>';
                echo '</tr>';
                
                $stt++;
            }
            
            echo '</tbody>';
            echo '</table>';
        }
        
        echo '<p><a href="' . admin_url('post-new.php?post_type=diem&monhoc_id=' . $monhoc_id) . '" class="button">Thêm điểm mới</a></p>';
        echo '</div>';
        
        wp_reset_postdata();
    }
    
    /**
     * Shortcode hiển thị danh sách môn học
     */
    public function danh_sach_mon_hoc_shortcode($atts) {
        // Tham số mặc định
        $atts = shortcode_atts(array(
            'khoa' => '',       // Lọc theo khoa
            'limit' => -1,      // Số lượng môn học hiển thị, -1 = tất cả
            'orderby' => 'title', // Sắp xếp theo tên
            'order' => 'ASC',    // Thứ tự sắp xếp
        ), $atts);
        
        // Tham số truy vấn
        $args = array(
            'post_type' => 'monhoc',
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
        
        // Lấy tham số tín chỉ nếu có
        $tin_chi = isset($_GET['tin_chi']) ? intval($_GET['tin_chi']) : 0;
        if ($tin_chi > 0) {
            $args['meta_query'] = isset($args['meta_query']) ? $args['meta_query'] : array();
            $args['meta_query'][] = array(
                'key' => 'so_tin_chi',
                'value' => $tin_chi,
                'compare' => '=',
                'type' => 'NUMERIC'
            );
        }
        
        // Thực hiện truy vấn
        $query = new WP_Query($args);
        
        // Lấy danh sách khoa để lọc
        $khoa_list = array();
        $khoa_query = new WP_Query([
            'post_type' => 'monhoc',
            'posts_per_page' => -1,
            'fields' => 'ids'
        ]);
        
        if ($khoa_query->have_posts()) {
            foreach ($khoa_query->posts as $monhoc_id) {
                $khoa_value = get_field('khoa', $monhoc_id);
                if ($khoa_value && !in_array($khoa_value, $khoa_list)) {
                    $khoa_list[] = $khoa_value;
                }
            }
        }
        sort($khoa_list);
        
        // Lấy danh sách tín chỉ để lọc
        $tin_chi_list = array();
        $tin_chi_query = new WP_Query([
            'post_type' => 'monhoc',
            'posts_per_page' => -1,
            'fields' => 'ids'
        ]);
        
        if ($tin_chi_query->have_posts()) {
            foreach ($tin_chi_query->posts as $monhoc_id) {
                $tin_chi_value = get_field('so_tin_chi', $monhoc_id);
                if ($tin_chi_value && !in_array($tin_chi_value, $tin_chi_list)) {
                    $tin_chi_list[] = $tin_chi_value;
                }
            }
        }
        sort($tin_chi_list);
        
        // Tạo output HTML
        $output = '<div class="danh-sach-mon-hoc-container">';
        
        // Tạo bộ lọc
        $output .= '<div class="mon-hoc-filter">';
        $output .= '<form class="filter-form" method="get">';
        
        // Giữ các tham số URL khác (nếu cần)
        foreach ($_GET as $key => $value) {
            if (!in_array($key, ['khoa', 'tin_chi'])) {
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
        $output .= '<label for="tin_chi_filter">Tín chỉ:</label>';
        $output .= '<select name="tin_chi" id="tin_chi_filter">';
        $output .= '<option value="0">-- Tất cả tín chỉ --</option>';
        
        foreach ($tin_chi_list as $tin_chi_item) {
            $selected = ($tin_chi == $tin_chi_item) ? 'selected' : '';
            $output .= '<option value="' . esc_attr($tin_chi_item) . '" ' . $selected . '>' . esc_html($tin_chi_item) . '</option>';
        }
        
        $output .= '</select>';
        $output .= '</div>';
        
        $output .= '<div class="filter-group">';
        $output .= '<button type="submit" class="filter-btn">Lọc</button>';
        $output .= '</div>';
        
        $output .= '</form>';
        $output .= '</div>'; // End filter
        
        // Nếu không có môn học nào
        if (!$query->have_posts()) {
            $output .= '<p>Không có môn học nào.</p>';
            $output .= '</div>'; // Close container
            return $output;
        }
        
        // Tạo bảng danh sách môn học
        $output .= '<table class="mon-hoc-table">';
        $output .= '<thead>';
        $output .= '<tr>';
        $output .= '<th>STT</th>';
        $output .= '<th>Tên môn học</th>';
        $output .= '<th>Mã môn</th>';
        $output .= '<th>Số tín chỉ</th>';
        $output .= '<th>Khoa</th>';
        $output .= '<th>Môn tiên quyết</th>';
        $output .= '</tr>';
        $output .= '</thead>';
        $output .= '<tbody>';
        
        $stt = 1;
        while ($query->have_posts()) {
            $query->the_post();
            $post_id = get_the_ID();
            
            // Lấy thông tin môn học
            $ten_mon = get_the_title();
            $ma_mon = get_field('ma_mon', $post_id);
            $so_tin_chi = get_field('so_tin_chi', $post_id);
            $khoa_value = get_field('khoa', $post_id);
            $dieu_kien = get_field('dieu_kien', $post_id);
            
            // Xử lý điều kiện tiên quyết
            $dieu_kien_text = '';
            if ($dieu_kien) {
                $dieu_kien_arr = array();
                if (!is_array($dieu_kien)) {
                    $dieu_kien = array($dieu_kien);
                }
                foreach ($dieu_kien as $monhoc_id) {
                    $dieu_kien_arr[] = get_the_title($monhoc_id);
                }
                $dieu_kien_text = implode(', ', $dieu_kien_arr);
            } else {
                $dieu_kien_text = 'Không có';
            }
            
            $output .= '<tr>';
            $output .= '<td>' . $stt . '</td>';
            $output .= '<td>' . esc_html($ten_mon) . '</td>';
            $output .= '<td>' . esc_html($ma_mon) . '</td>';
            $output .= '<td>' . esc_html($so_tin_chi) . '</td>';
            $output .= '<td>' . esc_html($khoa_value) . '</td>';
            $output .= '<td>' . esc_html($dieu_kien_text) . '</td>';
            $output .= '</tr>';
            
            $stt++;
        }
        
        $output .= '</tbody>';
        $output .= '</table>';
        
        // CSS cho bảng và bộ lọc
        $output .= '<style>
            .danh-sach-mon-hoc-container {
                margin-bottom: 30px;
            }
            .mon-hoc-filter {
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
            .mon-hoc-table {
                width: 100%;
                border-collapse: collapse;
                margin-top: 20px;
            }
            .mon-hoc-table th, 
            .mon-hoc-table td {
                padding: 10px;
                border: 1px solid #ddd;
                text-align: left;
            }
            .mon-hoc-table th {
                background-color: #f2f2f2;
                font-weight: bold;
            }
            .mon-hoc-table tr:hover {
                background-color: #f5f5f5;
            }
        </style>';
        
        $output .= '</div>'; // End container
        
        wp_reset_postdata();
        
        return $output;
    }
} 