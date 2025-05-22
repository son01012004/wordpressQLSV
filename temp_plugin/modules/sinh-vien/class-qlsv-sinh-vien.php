<?php
/**
 * Class quản lý sinh viên và các chức năng liên quan
 */
class QLSV_Sinh_Vien {

    /**
     * Loader để đăng ký các hooks
     *
     * @var QLSV_Loader
     */
    private $loader;

    /**
     * Khởi tạo class sinh viên
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
        
        // Xử lý hiển thị trong admin
        $this->loader->add_action('add_meta_boxes', $this, 'add_sinh_vien_meta_boxes');
        
        // Đăng ký hook xử lý template archive và single
        $this->loader->add_filter('archive_template', $this, 'archive_sinh_vien_template');
        $this->loader->add_filter('single_template', $this, 'single_sinh_vien_template');
    }
    
    /**
     * Đăng ký các shortcodes
     */
    private function register_shortcodes() {
        add_shortcode('qlsv_danh_sach_sinh_vien', array($this, 'danh_sach_sinh_vien_shortcode'));
        add_shortcode('qlsv_thong_tin_sinh_vien', array($this, 'thong_tin_sinh_vien_shortcode'));
    }
    
    /**
     * Đăng ký ACF Fields cho sinh viên
     */
    public function register_acf_fields() {
        if (!function_exists('acf_add_local_field_group')) {
            return;
        }

        acf_add_local_field_group(array(
            'key' => 'group_sinh_vien_info',
            'title' => 'Thông tin sinh viên',
            'fields' => array(
                array(
                    'key' => 'field_ma_sinh_vien',
                    'label' => 'Mã sinh viên',
                    'name' => 'ma_sinh_vien',
                    'type' => 'text',
                    'instructions' => 'Nhập mã sinh viên',
                    'required' => 1,
                ),
                array(
                    'key' => 'field_ngay_sinh',
                    'label' => 'Ngày sinh',
                    'name' => 'ngay_sinh',
                    'type' => 'date_picker',
                    'instructions' => 'Chọn ngày sinh',
                    'required' => 1,
                    'display_format' => 'd/m/Y',
                    'return_format' => 'Y-m-d',
                ),
                array(
                    'key' => 'field_lop_sv',
                    'label' => 'Lớp',
                    'name' => 'lop',
                    'type' => 'post_object',
                    'instructions' => 'Chọn lớp',
                    'required' => 0,
                    'post_type' => array('lop'),
                    'return_format' => 'id',
                    'ui' => 1,
                ),
                array(
                    'key' => 'field_khoa_sv',
                    'label' => 'Khoa',
                    'name' => 'khoa',
                    'type' => 'text',
                    'instructions' => 'Nhập khoa',
                    'required' => 0,
                ),
                array(
                    'key' => 'field_email_sv',
                    'label' => 'Email',
                    'name' => 'email',
                    'type' => 'email',
                    'instructions' => 'Nhập email',
                    'required' => 0,
                ),
                array(
                    'key' => 'field_so_dien_thoai',
                    'label' => 'Số điện thoại',
                    'name' => 'so_dien_thoai',
                    'type' => 'text',
                    'instructions' => 'Nhập số điện thoại',
                    'required' => 0,
                ),
                array(
                    'key' => 'field_dia_chi',
                    'label' => 'Địa chỉ',
                    'name' => 'dia_chi',
                    'type' => 'textarea',
                    'instructions' => 'Nhập địa chỉ',
                    'required' => 0,
                ),
                array(
                    'key' => 'field_anh',
                    'label' => 'Ảnh',
                    'name' => 'anh',
                    'type' => 'image',
                    'instructions' => 'Tải lên ảnh sinh viên',
                    'required' => 0,
                    'return_format' => 'id',
                    'preview_size' => 'medium',
                    'library' => 'all',
                ),
                array(
                    'key' => 'field_trang_thai',
                    'label' => 'Trạng thái',
                    'name' => 'trang_thai',
                    'type' => 'select',
                    'instructions' => 'Chọn trạng thái sinh viên',
                    'required' => 0,
                    'choices' => array(
                        'Đang học' => 'Đang học',
                        'Bảo lưu' => 'Bảo lưu',
                        'Đã tốt nghiệp' => 'Đã tốt nghiệp',
                        'Nghỉ học' => 'Nghỉ học',
                    ),
                    'default_value' => 'Đang học',
                ),
            ),
            'location' => array(
                array(
                    array(
                        'param' => 'post_type',
                        'operator' => '==',
                        'value' => 'sinhvien',
                    ),
                ),
            ),
            'menu_order' => 0,
            'position' => 'normal',
            'style' => 'default',
            'label_placement' => 'top',
            'instruction_placement' => 'label',
        ));
    }
    
    /**
     * Thêm metabox cho sinh viên trong admin
     */
    public function add_sinh_vien_meta_boxes() {
        add_meta_box(
            'sinh_vien_info',
            'Thông tin sinh viên',
            array($this, 'render_meta_box'),
            'sinhvien',
            'normal',
            'high'
        );
    }
    
    /**
     * Render metabox
     */
    public function render_meta_box($post) {
        // ACF đã xử lý
    }
    
    /**
     * Shortcode hiển thị danh sách sinh viên
     */
    public function danh_sach_sinh_vien_shortcode($atts) {
        // Tham số mặc định
        $atts = shortcode_atts(array(
            'limit' => -1,       // Số lượng sinh viên hiển thị, -1 = tất cả
            'lop_id' => 0,      // ID của lớp (nếu muốn lọc theo lớp)
            'orderby' => 'title', // Sắp xếp theo tên
            'order' => 'ASC',    // Thứ tự sắp xếp
        ), $atts);
        
        // Tham số truy vấn
        $args = array(
            'post_type' => 'sinhvien',
            'posts_per_page' => $atts['limit'],
            'orderby' => $atts['orderby'],
            'order' => $atts['order'],
        );
        
        // Lấy lớp từ query parameter nếu có
        $lop_from_get = isset($_GET['lop']) ? intval($_GET['lop']) : 0;
        if ($lop_from_get > 0) {
            $lop_id = $lop_from_get;
        } else {
            $lop_id = $atts['lop_id'];
        }
        
        // Thêm điều kiện lọc theo lớp nếu có
        if (!empty($lop_id)) {
            $args['meta_query'] = array(
                array(
                    'key' => 'lop',
                    'value' => $lop_id,
                    'compare' => '=',
                )
            );
        }
        
        // Thực hiện truy vấn
        $query = new WP_Query($args);
        $students = $query->posts;
        
        // Lấy danh sách lớp để lọc
        $classes = get_posts([
            'post_type' => 'lop',
            'posts_per_page' => -1,
            'orderby' => 'title',
            'order' => 'ASC'
        ]);
        
        // Load template
        ob_start();
        $template_path = QLSV_PLUGIN_DIR . 'templates/sinh-vien-list.php';
        
        if (file_exists($template_path)) {
            include $template_path;
        } else {
            echo 'Template không tồn tại.';
        }
        
        wp_reset_postdata();
        
        return ob_get_clean();
    }
    
    /**
     * Shortcode hiển thị thông tin sinh viên
     */
    public function thong_tin_sinh_vien_shortcode() {
        // Kiểm tra người dùng đã đăng nhập chưa
        if (!is_user_logged_in()) {
            return '<div class="thong-bao">Bạn cần đăng nhập để xem thông tin sinh viên.</div>';
        }
        
        // Lấy thông tin người dùng hiện tại
        $current_user = wp_get_current_user();
        $user_email = $current_user->user_email;
        
        // Tìm sinh viên có email trùng với email người dùng
        $args = array(
            'post_type' => 'sinhvien',
            'posts_per_page' => 1,
            'meta_query' => array(
                array(
                    'key' => 'email',
                    'value' => $user_email,
                    'compare' => '='
                )
            )
        );
        
        $query = new WP_Query($args);
        
        // Nếu không tìm thấy sinh viên
        if (!$query->have_posts()) {
            return '<div class="thong-bao">Không tìm thấy thông tin sinh viên cho tài khoản này.</div>';
        }
        
        // Lấy thông tin sinh viên
        $query->the_post();
        $post_id = get_the_ID();
        
        // Lấy các trường thông tin
        $ho_ten = get_the_title($post_id);
        $ma_sinh_vien = get_field('ma_sinh_vien', $post_id);
        $ngay_sinh = get_field('ngay_sinh', $post_id);
        $lop_id = get_field('lop', $post_id);
        $khoa = get_field('khoa', $post_id);
        $email = get_field('email', $post_id);
        $so_dien_thoai = get_field('so_dien_thoai', $post_id);
        $dia_chi = get_field('dia_chi', $post_id);
        $anh_id = get_field('anh', $post_id);
        $trang_thai = get_field('trang_thai', $post_id);
        
        // Format ngày sinh
        $ngay_sinh_format = '';
        if ($ngay_sinh) {
            $ngay_sinh_format = date_i18n('d/m/Y', strtotime($ngay_sinh));
        }
        
        // Lấy tên lớp
        $ten_lop = '';
        if ($lop_id) {
            $ten_lop = get_the_title($lop_id);
        }
        
        // Lấy URL ảnh
        $anh_url = '';
        $anh_html = '';
        if ($anh_id) {
            $anh_url = wp_get_attachment_image_url($anh_id, 'medium');
            $anh_html = '<div class="sinh-vien-anh"><img src="' . esc_url($anh_url) . '" alt="' . esc_attr($ho_ten) . '"></div>';
        }
        
        // Tạo output
        $output = '<div class="thong-tin-sinh-vien-container">';
        
        // Phần header với ảnh và tên
        $output .= '<div class="sinh-vien-header">';
        if ($anh_html) {
            $output .= $anh_html;
        } else {
            $output .= '<div class="sinh-vien-anh placeholder"><i class="dashicons dashicons-admin-users"></i></div>';
        }
        $output .= '<h3 class="sinh-vien-ten">' . esc_html($ho_ten) . '</h3>';
        if ($trang_thai) {
            $output .= '<div class="sinh-vien-trang-thai">' . esc_html($trang_thai) . '</div>';
        }
        $output .= '</div>';
        
        // Phần thông tin chi tiết
        $output .= '<div class="sinh-vien-info">';
        $output .= '<table class="sinh-vien-table">';
        
        // Mã sinh viên
        if ($ma_sinh_vien) {
            $output .= '<tr>';
            $output .= '<th>Mã sinh viên:</th>';
            $output .= '<td>' . esc_html($ma_sinh_vien) . '</td>';
            $output .= '</tr>';
        }
        
        // Ngày sinh
        if ($ngay_sinh_format) {
            $output .= '<tr>';
            $output .= '<th>Ngày sinh:</th>';
            $output .= '<td>' . esc_html($ngay_sinh_format) . '</td>';
            $output .= '</tr>';
        }
        
        // Lớp
        if ($ten_lop) {
            $output .= '<tr>';
            $output .= '<th>Lớp:</th>';
            $output .= '<td>' . esc_html($ten_lop) . '</td>';
            $output .= '</tr>';
        }
        
        // Khoa
        if ($khoa) {
            $output .= '<tr>';
            $output .= '<th>Khoa:</th>';
            $output .= '<td>' . esc_html($khoa) . '</td>';
            $output .= '</tr>';
        }
        
        // Email
        if ($email) {
            $output .= '<tr>';
            $output .= '<th>Email:</th>';
            $output .= '<td>' . esc_html($email) . '</td>';
            $output .= '</tr>';
        }
        
        // Số điện thoại
        if ($so_dien_thoai) {
            $output .= '<tr>';
            $output .= '<th>Số điện thoại:</th>';
            $output .= '<td>' . esc_html($so_dien_thoai) . '</td>';
            $output .= '</tr>';
        }
        
        // Địa chỉ
        if ($dia_chi) {
            $output .= '<tr>';
            $output .= '<th>Địa chỉ:</th>';
            $output .= '<td>' . nl2br(esc_html($dia_chi)) . '</td>';
            $output .= '</tr>';
        }
        
        $output .= '</table>';
        $output .= '</div>';
        
        // Thêm nút xem bảng điểm
        $output .= '<div class="sinh-vien-actions">';
        $output .= '<a href="' . esc_url(add_query_arg('sinhvien', $post_id, get_permalink(get_page_by_path('tra-cuu-diem')))) . '" class="button button-primary">Xem bảng điểm</a>';
        $output .= '</div>';
        
        $output .= '</div>';
        
        // CSS cho hiển thị thông tin
        $output .= '<style>
            .thong-tin-sinh-vien-container {
                max-width: 800px;
                margin: 0 auto;
                background: #fff;
                border-radius: 8px;
                box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
                padding: 20px;
                overflow: hidden;
            }
            .sinh-vien-header {
                display: flex;
                align-items: center;
                padding-bottom: 20px;
                margin-bottom: 20px;
                border-bottom: 1px solid #eee;
                flex-wrap: wrap;
            }
            .sinh-vien-anh {
                width: 100px;
                height: 100px;
                border-radius: 50%;
                overflow: hidden;
                margin-right: 20px;
                flex-shrink: 0;
            }
            .sinh-vien-anh img {
                width: 100%;
                height: 100%;
                object-fit: cover;
            }
            .sinh-vien-anh.placeholder {
                background-color: #f1f1f1;
                display: flex;
                align-items: center;
                justify-content: center;
            }
            .sinh-vien-anh.placeholder i {
                font-size: 50px;
                color: #ccc;
            }
            .sinh-vien-ten {
                font-size: 24px;
                margin: 0;
                flex: 1;
            }
            .sinh-vien-trang-thai {
                background: #e7f7ed;
                color: #28a745;
                padding: 5px 10px;
                border-radius: 4px;
                font-weight: bold;
                font-size: 14px;
            }
            .sinh-vien-info {
                margin-bottom: 20px;
            }
            .sinh-vien-table {
                width: 100%;
                border-collapse: collapse;
            }
            .sinh-vien-table th {
                font-weight: bold;
                text-align: left;
                padding: 10px;
                width: 150px;
                vertical-align: top;
            }
            .sinh-vien-table td {
                padding: 10px;
                vertical-align: top;
            }
            .sinh-vien-table tr:nth-child(even) {
                background-color: #f9f9f9;
            }
            .sinh-vien-actions {
                margin-top: 20px;
                text-align: center;
            }
            .sinh-vien-actions .button {
                display: inline-block;
                background-color: #0073aa;
                color: #fff;
                padding: 10px 20px;
                text-decoration: none;
                border-radius: 4px;
                font-weight: bold;
                transition: background-color 0.3s;
            }
            .sinh-vien-actions .button:hover {
                background-color: #005177;
            }
            .thong-bao {
                padding: 15px;
                background-color: #f8d7da;
                color: #721c24;
                border: 1px solid #f5c6cb;
                border-radius: 4px;
                margin-bottom: 20px;
            }
            @media (max-width: 600px) {
                .sinh-vien-header {
                    flex-direction: column;
                    text-align: center;
                }
                .sinh-vien-anh {
                    margin: 0 auto 15px;
                }
                .sinh-vien-ten {
                    margin-bottom: 10px;
                }
                .sinh-vien-table th,
                .sinh-vien-table td {
                    display: block;
                    width: 100%;
                }
                .sinh-vien-table th {
                    padding-bottom: 0;
                }
            }
        </style>';
        
        wp_reset_postdata();
        
        return $output;
    }
    
    /**
     * Xử lý template cho archive sinh viên
     */
    public function archive_sinh_vien_template($template) {
        if (is_post_type_archive('sinhvien')) {
            // Sử dụng template danh sách sinh viên
            $new_template = QLSV_PLUGIN_DIR . 'templates/archive-sinhvien.php';
            if (file_exists($new_template)) {
                return $new_template;
            }
        }
        return $template;
    }
    
    /**
     * Xử lý template cho single sinh viên
     */
    public function single_sinh_vien_template($template) {
        if (is_singular('sinhvien')) {
            // Sử dụng template chi tiết sinh viên
            $new_template = QLSV_PLUGIN_DIR . 'templates/single-sinhvien.php';
            if (file_exists($new_template)) {
                return $new_template;
            }
        }
        return $template;
    }
} 