<?php
/**
 * Class quản lý điểm danh và các chức năng liên quan
 */
class QLSV_DiemDanh {

    /**
     * Loader để đăng ký các hooks
     *
     * @var QLSV_Loader
     */
    private $loader;

    /**
     * Khởi tạo class điểm danh
     */
    public function __construct($loader) {
        $this->loader = $loader;
        
        // Only register hooks if loader is provided
        if ($this->loader !== null) {
        // Đăng ký các hooks
        $this->register_hooks();
        
        // Đăng ký các shortcodes
        $this->register_shortcodes();
        }
    }
    
    /**
     * Đăng ký các hooks cần thiết
     */
    private function register_hooks() {
        // Đăng ký custom post type
        $this->loader->add_action('init', $this, 'register_post_type');
        
        // Xử lý lưu dữ liệu khi lưu post
        $this->loader->add_action('save_post', $this, 'update_diemdanh_title', 20, 2);
        
        // Xử lý form điểm danh
        $this->loader->add_action('init', $this, 'handle_diemdanh_form');
        
        // Đăng ký template tùy chỉnh cho archive diemdanh
        $this->loader->add_filter('archive_template', $this, 'register_diemdanh_archive_template');
        
        // Đăng ký template tùy chỉnh cho single diemdanh
        $this->loader->add_filter('single_template', $this, 'register_diemdanh_single_template');
        
        // Filter nội dung trang điểm danh - sử dụng priority cao (999) để chắc chắn chạy sau các filter khác
        $this->loader->add_filter('the_content', $this, 'display_diemdanh_dashboard', 999);
        
        // Sử dụng template riêng cho trang điểm danh
        $this->loader->add_filter('template_include', $this, 'diemdanh_template', 999);
        
        // Ẩn tiêu đề gốc và thêm CSS
        $this->loader->add_action('wp_head', $this, 'add_diemdanh_css');
        
        // Đăng ký AJAX để xử lý cập nhật điểm danh
        $this->loader->add_action('wp_ajax_update_diemdanh', $this, 'ajax_update_diemdanh');
        $this->loader->add_action('wp_ajax_nopriv_update_diemdanh', $this, 'ajax_update_diemdanh');
        
        // Fix 404 errors for query parameters
        $this->loader->add_action('pre_get_posts', $this, 'handle_diemdanh_queries');
    }
    
    /**
     * Đăng ký các shortcode
     */
    private function register_shortcodes() {
        add_shortcode('qlsv_diemdanh_dashboard', array($this, 'diemdanh_dashboard_shortcode'));
        add_shortcode('qlsv_diemdanh_form', array($this, 'diemdanh_form_shortcode'));
        add_shortcode('qlsv_diemdanh_list', array($this, 'diemdanh_list_shortcode'));
    }
    
    /**
     * Đăng ký post type điểm danh
     */
    public function register_post_type() {
        $labels = array(
            'name'               => _x('Điểm danh', 'post type general name', 'qlsv'),
            'singular_name'      => _x('Điểm danh', 'post type singular name', 'qlsv'),
            'menu_name'          => _x('Điểm danh', 'admin menu', 'qlsv'),
            'name_admin_bar'     => _x('Điểm danh', 'add new on admin bar', 'qlsv'),
            'add_new'            => _x('Thêm mới', 'diemdanh', 'qlsv'),
            'add_new_item'       => __('Thêm buổi điểm danh mới', 'qlsv'),
            'new_item'           => __('Buổi điểm danh mới', 'qlsv'),
            'edit_item'          => __('Sửa buổi điểm danh', 'qlsv'),
            'view_item'          => __('Xem buổi điểm danh', 'qlsv'),
            'all_items'          => __('Tất cả buổi điểm danh', 'qlsv'),
            'search_items'       => __('Tìm buổi điểm danh', 'qlsv'),
            'parent_item_colon'  => __('Điểm danh cha:', 'qlsv'),
            'not_found'          => __('Không tìm thấy buổi điểm danh nào.', 'qlsv'),
            'not_found_in_trash' => __('Không có buổi điểm danh nào trong thùng rác.', 'qlsv')
        );

        $args = array(
            'labels'             => $labels,
            'description'        => __('Quản lý điểm danh sinh viên', 'qlsv'),
            'public'             => true,
            'publicly_queryable' => true,
            'show_ui'            => true,
            'show_in_menu'       => true,
            'query_var'          => true,
            'rewrite'            => array(
                'slug' => 'diemdanh',
                'with_front' => false,
                'feeds' => false,
                'pages' => false,
                'ep_mask' => EP_PERMALINK
            ),
            'capability_type'    => 'post',
            'has_archive'        => true,
            'hierarchical'       => false,
            'menu_position'      => null,
            'supports'           => array('title'),
            'menu_icon'          => 'dashicons-clipboard',
            'show_in_rest'       => false  // Disable Gutenberg editor
        );

        register_post_type('diemdanh', $args);

        // Đăng ký thêm các tham số query cho custom post type này
        $this->loader->add_filter('query_vars', $this, 'add_query_vars');
    }
    
    /**
     * Thêm các biến query để sử dụng trong URL
     */
    public function add_query_vars($vars) {
        $vars[] = 'lop';
        $vars[] = 'mon_hoc';
        return $vars;
    }
    
    /**
     * Cập nhật tiêu đề điểm danh tự động
     */
    public function update_diemdanh_title($post_id, $post) {
        // Chỉ xử lý post type 'diemdanh'
        if ($post->post_type !== 'diemdanh') {
            return;
        }

        // Cập nhật tiêu đề dựa trên dữ liệu đã chọn
        $mon_hoc_id = get_field('mon_hoc', $post_id);
        $lop_id = get_field('lop', $post_id);
        $ngay = get_field('ngay', $post_id);
        
        if ($mon_hoc_id && $lop_id && $ngay) {
            $mon_hoc = get_the_title($mon_hoc_id);
            $lop = get_the_title($lop_id);
            $ngay_format = date_i18n('d/m/Y', strtotime($ngay));
            
            // Cập nhật tiêu đề
            $title = sprintf('Điểm danh %s - %s - %s', $lop, $mon_hoc, $ngay_format);
            
            // Cập nhật post mà không trigger save_post hook
            remove_action('save_post', array($this, 'update_diemdanh_title'), 20);
            
            wp_update_post(array(
                'ID' => $post_id,
                'post_title' => $title,
            ));
            
            add_action('save_post', array($this, 'update_diemdanh_title'), 20, 2);
        }
    }
    
    /**
     * Thêm CSS cho trang điểm danh
     */
    public function add_diemdanh_css() {
        // Chỉ áp dụng cho trang điểm danh
        if (!$this->is_diemdanh_page()) {
            return;
        }
        
        echo '<style>
        .page-header, .entry-header, .entry-meta, .post-thumbnail, article > header {
            display: none !important;
        }
        .entry-content, #main, #primary {
            width: 100% !important;
            max-width: 100% !important;
            margin: 0 !important;
            padding: 20px !important;
        }
        .entry-title.diemdanh-title {
            display: block !important;
            font-size: 24px;
            margin-bottom: 20px;
            font-weight: bold;
        }
        </style>';
    }
    
    /**
     * Kiểm tra xem có phải trang điểm danh không
     */
    private function is_diemdanh_page() {
        return is_page('diemdanhh') || 
               is_page('diem-danh') || 
               is_page() && (strpos($_SERVER['REQUEST_URI'], '/diemdanhh') !== false || 
                             strpos($_SERVER['REQUEST_URI'], '/diem-danh') !== false);
    }
    
    /**
     * Filter template cho trang điểm danh
     */
    public function diemdanh_template($template) {
        // Nếu là trang điểm danh và template tồn tại
        if ($this->is_diemdanh_page()) {
            $diemdanh_template = plugin_dir_path(dirname(dirname(__FILE__))) . 'templates/page-diemdanh.php';
            
            if (file_exists($diemdanh_template)) {
                return $diemdanh_template;
            }
        }
        
        return $template;
    }
    
    /**
     * Filter để hiển thị nội dung trang điểm danh
     */
    public function display_diemdanh_dashboard($content) {
        // Kiểm tra hiện tại có phải trang điểm danh không
        if (!$this->is_diemdanh_page()) {
            return $content;
        }
        
        // Đặt tiêu đề trang
        global $post;
        if ($post) {
            $post->post_title = 'Điểm Danh';
        }
        
        // Buộc giao diện điểm danh
        ob_start();
        
        // Tiêu đề trang
        echo '<h1 class="entry-title diemdanh-title">Điểm Danh</h1>';
        
        // Kiểm tra người dùng đã đăng nhập chưa
        if (!is_user_logged_in()) {
            echo '<div class="qlsv-thong-bao">';
            echo '<p>' . __('Bạn cần đăng nhập để sử dụng tính năng này.', 'qlsv') . '</p>';
            echo '<p><a href="' . wp_login_url('http://localhost/wordpressQLSV/') . '" class="button">Đăng nhập</a></p>';
            echo '</div>';
            return ob_get_clean();
        }
        
        // Hiển thị dashboard
        echo do_shortcode('[qlsv_diemdanh_dashboard]');
        
        // Thay thế toàn bộ nội dung
        return ob_get_clean();
    }
    
    /**
     * Shortcode hiển thị dashboard điểm danh
     */
    public function diemdanh_dashboard_shortcode($atts) {
        // Tham số mặc định
        $atts = shortcode_atts(array(), $atts);
        
        ob_start();
        
        // CSS cho giao diện
        echo '<style>
            .diemdanh-dashboard {
                font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen-Sans, Ubuntu, Cantarell, "Helvetica Neue", sans-serif;
                margin-bottom: 30px;
                background: #fff;
                padding: 20px;
                border-radius: 5px;
                box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            }
            .diemdanh-tabs {
                margin: 20px 0;
                border-bottom: 1px solid #ccc;
            }
            .diemdanh-tabs ul {
                display: flex;
                list-style: none;
                margin: 0;
                padding: 0;
            }
            .diemdanh-tabs li {
                margin: 0 5px 0 0;
            }
            .diemdanh-tabs a {
                display: block;
                padding: 10px 15px;
                text-decoration: none;
                background: #f5f5f5;
                color: #333;
                border: 1px solid #ccc;
                border-bottom: none;
            }
            .diemdanh-tabs li.active a {
                background: #fff;
                position: relative;
                border-bottom: 1px solid #fff;
                margin-bottom: -1px;
            }
            .diemdanh-content {
                padding: 20px;
                border: 1px solid #ccc;
                border-top: none;
                background: #fff;
            }
            .diemdanh-form {
                margin: 20px 0;
            }
            .diemdanh-form .form-group {
                margin-bottom: 15px;
            }
            .diemdanh-form label {
                display: block;
                margin-bottom: 5px;
                font-weight: bold;
            }
            .diemdanh-form select,
            .diemdanh-form input[type="date"] {
                width: 100%;
                padding: 8px;
                border: 1px solid #ddd;
                border-radius: 4px;
                max-width: 300px;
            }
            .diemdanh-form button {
                padding: 10px 15px;
                background: #0073aa;
                color: #fff;
                border: none;
                border-radius: 4px;
                cursor: pointer;
            }
            .diemdanh-table {
                width: 100%;
                border-collapse: collapse;
                margin-top: 20px;
            }
            .diemdanh-table th, 
            .diemdanh-table td {
                padding: 10px;
                border: 1px solid #ddd;
                text-align: left;
            }
            .diemdanh-table th {
                background-color: #f2f2f2;
            }
            .diemdanh-error {
                color: #a94442;
                background-color: #f2dede;
                padding: 15px;
                margin-bottom: 20px;
                border: 1px solid #ebccd1;
                border-radius: 4px;
            }
            .diemdanh-success {
                color: #3c763d;
                background-color: #dff0d8;
                padding: 15px;
                margin-bottom: 20px;
                border: 1px solid #d6e9c6;
                border-radius: 4px;
            }
            .status-present {
                color: #3c763d;
                font-weight: bold;
            }
            .status-absent {
                color: #a94442;
                font-weight: bold;
            }
        </style>';
        
        echo '<div class="diemdanh-dashboard">';
        echo '<h2>Quản lý điểm danh</h2>';
        
        // Kiểm tra người dùng đăng nhập
        if (!is_user_logged_in()) {
            echo '<div class="diemdanh-error">';
            echo '<p>Bạn cần đăng nhập để sử dụng chức năng này.</p>';
            echo '<p><a href="' . wp_login_url('http://localhost/wordpressQLSV/') . '" class="button">Đăng nhập</a></p>';
            echo '</div>';
            echo '</div>';
            return ob_get_clean();
        }
        
        // Xác định vai trò người dùng
        $current_user = wp_get_current_user();
        $is_admin = current_user_can('manage_options');
        $is_teacher = current_user_can('edit_posts') || $is_admin;  // Giả sử giáo viên có quyền edit_posts
        $is_student = !$is_teacher; // Nếu không phải giáo viên hoặc admin thì là sinh viên
        
        // Tabs
        $current_tab = isset($_GET['tab']) ? sanitize_text_field($_GET['tab']) : ($is_student ? 'view' : 'form');
        
        echo '<div class="diemdanh-tabs">';
        echo '<ul>';
        
        // Chỉ hiển thị tab tạo điểm danh cho giáo viên và admin
        if ($is_teacher) {
            echo '<li class="' . ($current_tab === 'form' ? 'active' : '') . '"><a href="?tab=form">Tạo điểm danh</a></li>';
        }
        
        echo '<li class="' . ($current_tab === 'view' ? 'active' : '') . '"><a href="?tab=view">Xem điểm danh</a></li>';
        
        // Chỉ hiển thị tab báo cáo cho giáo viên và admin
        if ($is_teacher) {
            echo '<li class="' . ($current_tab === 'report' ? 'active' : '') . '"><a href="?tab=report">Báo cáo</a></li>';
        }
        
        echo '</ul>';
        echo '</div>';
        
        echo '<div class="diemdanh-content">';
        
        // Tab content
        if ($current_tab === 'form') {
            if ($is_teacher) {
                // Hiển thị form điểm danh cho giáo viên/admin
                echo do_shortcode('[qlsv_diemdanh_form]');
            } else {
                // Không có quyền
                echo '<div class="diemdanh-error">';
                echo '<p>Bạn không có quyền tạo điểm danh. Chỉ giáo viên và quản trị viên mới có thể tạo điểm danh.</p>';
                echo '</div>';
            }
        } elseif ($current_tab === 'view') {
            // Hiển thị danh sách điểm danh (tất cả người dùng đều có thể xem)
            echo do_shortcode('[qlsv_diemdanh_list]');
        } elseif ($current_tab === 'report' && $is_teacher) {
            // Hiển thị báo cáo cho giáo viên/admin
            echo '<h3>Báo cáo điểm danh</h3>';
            
            // Hiển thị chọn lớp và môn học
            echo '<form class="diemdanh-form" method="get">';
            echo '<input type="hidden" name="tab" value="report">';
            
            // Lớp học
            echo '<div class="form-group">';
            echo '<label for="lop">Lớp học:</label>';
            echo '<select name="lop" id="lop">';
            echo '<option value="">Chọn lớp</option>';
            
            // Lấy danh sách lớp
            $lop_query = new WP_Query(array(
                'post_type' => 'lop',
                'posts_per_page' => -1,
                'orderby' => 'title',
                'order' => 'ASC'
            ));
            
            $selected_lop = isset($_GET['lop']) ? intval($_GET['lop']) : 0;
            
            if ($lop_query->have_posts()) {
                while ($lop_query->have_posts()) {
                    $lop_query->the_post();
                    $selected = ($selected_lop === get_the_ID()) ? 'selected' : '';
                    echo '<option value="' . get_the_ID() . '" ' . $selected . '>' . get_the_title() . '</option>';
                }
            }
            wp_reset_postdata();
            
            echo '</select>';
            echo '</div>';
            
            // Môn học
            echo '<div class="form-group">';
            echo '<label for="mon_hoc">Môn học:</label>';
            echo '<select name="mon_hoc" id="mon_hoc">';
            echo '<option value="">Chọn môn học</option>';
            
            // Lấy danh sách môn học
            $monhoc_query = new WP_Query(array(
                'post_type' => 'monhoc',
                'posts_per_page' => -1,
                'orderby' => 'title',
                'order' => 'ASC'
            ));
            
            $selected_monhoc = isset($_GET['mon_hoc']) ? intval($_GET['mon_hoc']) : 0;
            
            if ($monhoc_query->have_posts()) {
                while ($monhoc_query->have_posts()) {
                    $monhoc_query->the_post();
                    $selected = ($selected_monhoc === get_the_ID()) ? 'selected' : '';
                    echo '<option value="' . get_the_ID() . '" ' . $selected . '>' . get_the_title() . '</option>';
                }
            }
            wp_reset_postdata();
            
            echo '</select>';
            echo '</div>';
            
            echo '<button type="submit">Xem báo cáo</button>';
            echo '</form>';
            
            // Hiển thị báo cáo nếu đã chọn lớp và môn học
            if ($selected_lop > 0 && $selected_monhoc > 0) {
                $this->generate_diemdanh_report($selected_lop, $selected_monhoc);
            } else {
                echo '<p>Vui lòng chọn lớp và môn học để xem báo cáo điểm danh.</p>';
            }
        }
        
        echo '</div>'; // End .diemdanh-content
        echo '</div>'; // End .diemdanh-dashboard
        
        return ob_get_clean();
    }
    
    /**
     * Tạo báo cáo điểm danh
     */
    private function generate_diemdanh_report($lop_id, $mon_hoc_id) {
        // Lấy thông tin lớp và môn học
        $lop_name = get_the_title($lop_id);
        $mon_hoc_name = get_the_title($mon_hoc_id);
        
        echo '<h3>Báo cáo điểm danh: ' . $lop_name . ' - ' . $mon_hoc_name . '</h3>';
        
        // Lấy tất cả các bản ghi điểm danh của lớp và môn học
        $diemdanh_query = new WP_Query(array(
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
            'orderby' => 'date',
            'order' => 'ASC'
        ));
        
        if (!$diemdanh_query->have_posts()) {
            echo '<p>Không tìm thấy dữ liệu điểm danh cho lớp và môn học này.</p>';
            return;
        }
        
        // Lấy danh sách sinh viên của lớp
        $sinh_vien_array = array();
        $sv_query = new WP_Query(array(
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
        ));
        
        if (!$sv_query->have_posts()) {
            echo '<p>Không tìm thấy sinh viên nào trong lớp này.</p>';
            return;
        }
        
        while ($sv_query->have_posts()) {
            $sv_query->the_post();
            $sinh_vien_array[get_the_ID()] = get_the_title();
        }
        wp_reset_postdata();
        
        // Tạo mảng lưu ngày điểm danh
        $diemdanh_dates = array();
        $diemdanh_data = array();
        
        // Thu thập dữ liệu điểm danh
        while ($diemdanh_query->have_posts()) {
            $diemdanh_query->the_post();
            $diemdanh_id = get_the_ID();
            $ngay = get_field('ngay', $diemdanh_id);
            $ngay_format = date_i18n('d/m/Y', strtotime($ngay));
            
            $diemdanh_dates[$diemdanh_id] = $ngay_format;
            
            // Lấy trạng thái điểm danh
            $sinh_vien_status = get_post_meta($diemdanh_id, 'sinh_vien_status', true);
            
            if (!empty($sinh_vien_status)) {
                foreach ($sinh_vien_array as $sv_id => $sv_name) {
                    $diemdanh_data[$sv_id][$diemdanh_id] = isset($sinh_vien_status[$sv_id]) ? $sinh_vien_status[$sv_id] : 'absent';
                }
            }
        }
        wp_reset_postdata();
        
        // Hiển thị báo cáo
        echo '<div style="overflow-x: auto;">';
        echo '<table class="diemdanh-table">';
        echo '<thead>';
        echo '<tr>';
        echo '<th>STT</th>';
        echo '<th>Sinh viên</th>';
        
        // Headers ngày điểm danh
        foreach ($diemdanh_dates as $dd_id => $dd_date) {
            echo '<th>' . $dd_date . '</th>';
        }
        
        echo '<th>Tổng vắng</th>';
        echo '</tr>';
        echo '</thead>';
        echo '<tbody>';
        
        $stt = 1;
        foreach ($sinh_vien_array as $sv_id => $sv_name) {
            echo '<tr>';
            echo '<td>' . $stt++ . '</td>';
            echo '<td>' . $sv_name . '</td>';
            
            $absent_count = 0;
            
            // Trạng thái điểm danh từng ngày
            foreach ($diemdanh_dates as $dd_id => $dd_date) {
                $status = isset($diemdanh_data[$sv_id][$dd_id]) ? $diemdanh_data[$sv_id][$dd_id] : 'absent';
                
                if ($status === 'absent') {
                    $absent_count++;
                    echo '<td class="status-absent">Vắng</td>';
                } else {
                    echo '<td class="status-present">Có mặt</td>';
                }
            }
            
            // Tổng số buổi vắng
            echo '<td>' . $absent_count . '</td>';
            echo '</tr>';
        }
        
        echo '</tbody>';
        echo '</table>';
        echo '</div>';
    }
    
    /**
     * Shortcode hiển thị form điểm danh
     */
    public function diemdanh_form_shortcode($atts) {
        // Tham số mặc định
        $atts = shortcode_atts(array(
            'lop_id' => 0,
            'mon_hoc_id' => 0
        ), $atts);
        
        $lop_id = intval($atts['lop_id']);
        $mon_hoc_id = intval($atts['mon_hoc_id']);
        
        ob_start();
        
        // Kiểm tra quyền truy cập (chỉ giáo viên và admin)
        if (!current_user_can('edit_posts') && !current_user_can('manage_options')) {
            echo '<div class="diemdanh-error">';
            echo '<p>Bạn không có quyền sử dụng chức năng này.</p>';
            echo '</div>';
            return ob_get_clean();
        }
        
        // Xử lý form submit
        $form_message = '';
        if (isset($_POST['submit_diemdanh']) && isset($_POST['diemdanh_nonce']) && wp_verify_nonce($_POST['diemdanh_nonce'], 'submit_diemdanh')) {
            $form_lop_id = isset($_POST['lop']) ? intval($_POST['lop']) : 0;
            $form_mon_hoc_id = isset($_POST['mon_hoc']) ? intval($_POST['mon_hoc']) : 0;
            $ngay = isset($_POST['ngay']) ? sanitize_text_field($_POST['ngay']) : '';
            
            // Kiểm tra dữ liệu hợp lệ
            if ($form_lop_id > 0 && $form_mon_hoc_id > 0 && !empty($ngay)) {
                // Kiểm tra xem đã có điểm danh cho ngày này chưa
                $existing_diemdanh = get_posts(array(
                    'post_type' => 'diemdanh',
                    'posts_per_page' => 1,
                    'meta_query' => array(
                        'relation' => 'AND',
                        array(
                    'key' => 'lop',
                            'value' => $form_lop_id,
                    'compare' => '='
                        ),
                        array(
                    'key' => 'mon_hoc',
                            'value' => $form_mon_hoc_id,
                    'compare' => '='
                        ),
                        array(
                            'key' => 'ngay',
                            'value' => $ngay,
                            'compare' => '='
                        )
                    )
                ));
                
                $diemdanh_id = 0;
                
                if (!empty($existing_diemdanh)) {
                    $diemdanh_id = $existing_diemdanh[0]->ID;
                    $form_message = '<div class="diemdanh-success">Đã tìm thấy điểm danh hiện có cho ngày này. Bạn có thể xem chi tiết.</div>';
                } else {
                    // Tạo bản ghi điểm danh mới
                    $diemdanh_id = wp_insert_post(array(
                        'post_title' => 'Đang tạo điểm danh mới...',
                        'post_status' => 'publish',
                        'post_type' => 'diemdanh'
                    ));
                    
                    if ($diemdanh_id > 0) {
                        // Cập nhật trường ACF
                        update_field('lop', $form_lop_id, $diemdanh_id);
                        update_field('mon_hoc', $form_mon_hoc_id, $diemdanh_id);
                        update_field('ngay', $ngay, $diemdanh_id);
                        
                        // Cập nhật tiêu đề
                        $this->update_diemdanh_title($diemdanh_id, get_post($diemdanh_id));
                        
                        $form_message = '<div class="diemdanh-success">Đã tạo bản ghi điểm danh mới. Vui lòng cập nhật thông tin sinh viên.</div>';
                    } else {
                        $form_message = '<div class="diemdanh-error">Có lỗi khi tạo bản ghi điểm danh.</div>';
                    }
                }
                
                // Chuyển đến trang xem điểm danh hoặc chỉnh sửa
                if ($diemdanh_id > 0) {
                    $view_link = get_permalink($diemdanh_id);
                    echo '<script>window.location.href = "' . esc_url($view_link) . '";</script>';
                    echo '<div class="diemdanh-success">Đang chuyển hướng đến trang điểm danh...</div>';
                    echo '<p><a href="' . esc_url($view_link) . '">Nhấn vào đây nếu không tự chuyển hướng.</a></p>';
                    return ob_get_clean();
                }
            } else {
                $form_message = '<div class="diemdanh-error">Vui lòng điền đầy đủ thông tin.</div>';
            }
        }
        
        // Hiển thị form
        echo '<h3>Tạo điểm danh mới</h3>';
        
        if (!empty($form_message)) {
            echo $form_message;
        }
        
        echo '<form class="diemdanh-form" method="post">';
        
        // Lớp học
        echo '<div class="form-group">';
        echo '<label for="lop">Lớp học:</label>';
        
        if ($lop_id > 0) {
            // Nếu đã có lớp được chọn từ tham số, hiển thị dưới dạng hidden field
            $lop_name = get_the_title($lop_id);
            echo '<input type="hidden" name="lop" value="' . esc_attr($lop_id) . '">';
            echo '<p><strong>' . esc_html($lop_name) . '</strong></p>';
        } else {
            // Nếu chưa có lớp, hiển thị dropdown để chọn
            echo '<select name="lop" id="lop" required>';
            echo '<option value="">Chọn lớp</option>';
            
            // Lấy danh sách lớp
            $lop_query = new WP_Query(array(
                'post_type' => 'lop',
                'posts_per_page' => -1,
                'orderby' => 'title',
                'order' => 'ASC'
            ));
            
            if ($lop_query->have_posts()) {
                while ($lop_query->have_posts()) {
                    $lop_query->the_post();
                    $selected = (isset($_POST['lop']) && intval($_POST['lop']) === get_the_ID()) ? 'selected' : '';
                    echo '<option value="' . get_the_ID() . '" ' . $selected . '>' . get_the_title() . '</option>';
                }
            }
            wp_reset_postdata();
            
            echo '</select>';
        }
        echo '</div>';
        
        // Môn học
        echo '<div class="form-group">';
        echo '<label for="mon_hoc">Môn học:</label>';
        
        if ($mon_hoc_id > 0) {
            // Nếu đã có môn học được chọn từ tham số, hiển thị dưới dạng hidden field
            $mon_hoc_name = get_the_title($mon_hoc_id);
            echo '<input type="hidden" name="mon_hoc" value="' . esc_attr($mon_hoc_id) . '">';
            echo '<p><strong>' . esc_html($mon_hoc_name) . '</strong></p>';
        } else {
            // Nếu chưa có môn học, hiển thị dropdown để chọn
            echo '<select name="mon_hoc" id="mon_hoc" required>';
            echo '<option value="">Chọn môn học</option>';
            
            // Lấy danh sách môn học
            $monhoc_query = new WP_Query(array(
                'post_type' => 'monhoc',
                'posts_per_page' => -1,
                'orderby' => 'title',
                'order' => 'ASC'
            ));
            
            if ($monhoc_query->have_posts()) {
                while ($monhoc_query->have_posts()) {
                    $monhoc_query->the_post();
                    $selected = (isset($_POST['mon_hoc']) && intval($_POST['mon_hoc']) === get_the_ID()) ? 'selected' : '';
                    echo '<option value="' . get_the_ID() . '" ' . $selected . '>' . get_the_title() . '</option>';
                }
            }
            wp_reset_postdata();
            
            echo '</select>';
        }
        echo '</div>';
        
        // Ngày điểm danh
        $today = date('Y-m-d');
        echo '<div class="form-group">';
        echo '<label for="ngay">Ngày điểm danh:</label>';
        echo '<input type="date" name="ngay" id="ngay" value="' . (isset($_POST['ngay']) ? esc_attr($_POST['ngay']) : $today) . '" required>';
        echo '</div>';
        
        // Nonce field
        wp_nonce_field('submit_diemdanh', 'diemdanh_nonce');
        
        // Submit button
        echo '<button type="submit" name="submit_diemdanh" class="button button-primary">Tạo điểm danh</button>';
        echo '</form>';
        
        // Hiển thị danh sách sinh viên trong lớp nếu có lớp được chọn
        if ($lop_id > 0) {
            // Lấy danh sách sinh viên của lớp này
            $students = $this->get_students_by_class($lop_id);
            
            if (!empty($students)) {
                echo '<h3>Danh sách sinh viên lớp: ' . get_the_title($lop_id) . '</h3>';
                echo '<table class="widefat striped">';
                echo '<thead>';
                echo '<tr>';
                echo '<th>STT</th>';
                echo '<th>MSSV</th>';
                echo '<th>Họ và tên</th>';
                echo '<th>Email</th>';
                echo '<th>Ngày sinh</th>';
                echo '<th>Khoa</th>';
                echo '</tr>';
                echo '</thead>';
                echo '<tbody>';
                
                $stt = 1;
                foreach ($students as $student) {
                    echo '<tr>';
                    echo '<td>' . $stt++ . '</td>';
                    echo '<td>' . esc_html($student['mssv']) . '</td>';
                    echo '<td>' . esc_html($student['name']) . '</td>';
                    echo '<td>' . esc_html($student['email']) . '</td>';
                    echo '<td>' . esc_html($student['dob']) . '</td>';
                    echo '<td>' . esc_html($student['khoa']) . '</td>';
                    echo '</tr>';
                }
                
                echo '</tbody>';
                echo '</table>';
        } else {
                echo '<div class="diemdanh-error">';
                echo '<p>Không tìm thấy sinh viên nào trong lớp này.</p>';
                echo '</div>';
            }
        }
        
        return ob_get_clean();
    }
    
    /**
     * Shortcode hiển thị danh sách điểm danh
     */
    public function diemdanh_list_shortcode($atts) {
        // Tham số mặc định
        $atts = shortcode_atts(array(
            'sinhvien_id' => 0,
        ), $atts);
        
        $sinhvien_id = intval($atts['sinhvien_id']);
        
        ob_start();
        
        // Xác định vai trò người dùng
            $current_user = wp_get_current_user();
        $is_admin = current_user_can('manage_options');
        $is_teacher = current_user_can('edit_posts') || $is_admin;
        $is_student = !$is_teacher;
        
        // Lớp học và môn học để lọc
        $lop_id = isset($_GET['lop']) ? intval($_GET['lop']) : 0;
        $mon_hoc_id = isset($_GET['mon_hoc']) ? intval($_GET['mon_hoc']) : 0;
        
        // SINH VIÊN hoặc xem theo sinh viên cụ thể
        if ($sinhvien_id > 0 || $is_student) {
            // Nếu có sinhvien_id được chỉ định, sử dụng nó
            if ($sinhvien_id > 0) {
                $sv_id = $sinhvien_id;
                $student_lop_id = get_field('lop', $sv_id);
                $student_name = get_the_title($sv_id);
            } else {
                // Nếu không có sinhvien_id, tìm sinh viên từ user hiện tại
                $sv_args = array(
                'post_type' => 'sinhvien',
                'posts_per_page' => 1,
                'meta_query' => array(
                    array(
                        'key' => 'email',
                            'value' => $current_user->user_email,
                        'compare' => '='
                    )
                )
            );
            
                $sv_query = new WP_Query($sv_args);
                
                if ($sv_query->have_posts()) {
                    $sv_query->the_post();
                    $sv_id = get_the_ID();
                    $student_lop_id = get_field('lop', $sv_id);
                    $student_name = get_the_title();
                    
                    // Reset post data
            wp_reset_postdata();
                } else {
                    echo '<div class="diemdanh-error">';
                    echo '<p>Không tìm thấy thông tin sinh viên trong hệ thống.</p>';
                    echo '</div>';
                    return ob_get_clean();
                }
            }
            
            echo '<h3>Thông tin điểm danh sinh viên ' . esc_html($student_name) . '</h3>';
            
            if ($student_lop_id) {
                echo '<p>Lớp: <strong>' . get_the_title($student_lop_id) . '</strong></p>';
                
                // Form chọn môn học
                echo '<form class="diemdanh-filter-form" method="get">';
                if ($sinhvien_id > 0) {
                    echo '<input type="hidden" name="sinhvien_id" value="' . $sinhvien_id . '">';
                }
                echo '<input type="hidden" name="tab" value="view">';
                
                // Môn học
                echo '<div class="form-group">';
                echo '<label for="mon_hoc">Chọn môn học:</label>';
                echo '<select name="mon_hoc" id="mon_hoc">';
                echo '<option value="">Tất cả môn học</option>';
                
                // Lấy danh sách môn học
                $monhoc_query = new WP_Query(array(
                    'post_type' => 'monhoc',
                    'posts_per_page' => -1,
                    'orderby' => 'title',
                    'order' => 'ASC'
                ));
                
                if ($monhoc_query->have_posts()) {
                    while ($monhoc_query->have_posts()) {
                        $monhoc_query->the_post();
                        $selected = ($mon_hoc_id === get_the_ID()) ? 'selected' : '';
                        echo '<option value="' . get_the_ID() . '" ' . $selected . '>' . get_the_title() . '</option>';
                    }
                }
                wp_reset_postdata();
                
                echo '</select>';
                echo '</div>';
                
                echo '<button type="submit">Xem</button>';
                echo '</form>';
                
                // Hiển thị thông tin điểm danh
                $this->display_student_attendance($sv_id, $student_lop_id, $mon_hoc_id);
            } else {
                echo '<p>Không tìm thấy thông tin lớp của sinh viên.</p>';
                }
            } else {
            // GIÁO VIÊN và ADMIN xem tất cả điểm danh
            
            // Form filter
            echo '<form class="diemdanh-filter-form" method="get">';
            echo '<input type="hidden" name="tab" value="view">';
            
            // Lớp học
            echo '<div class="form-group">';
            echo '<label for="lop">Lớp học:</label>';
            echo '<select name="lop" id="lop">';
            echo '<option value="">Tất cả lớp</option>';
            
            // Lấy danh sách lớp
            $lop_query = new WP_Query(array(
                'post_type' => 'lop',
                    'posts_per_page' => -1,
                'orderby' => 'title',
                'order' => 'ASC'
            ));
            
            if ($lop_query->have_posts()) {
                while ($lop_query->have_posts()) {
                    $lop_query->the_post();
                    $selected = ($lop_id === get_the_ID()) ? 'selected' : '';
                    echo '<option value="' . get_the_ID() . '" ' . $selected . '>' . get_the_title() . '</option>';
                }
            }
            wp_reset_postdata();
            
            echo '</select>';
            echo '</div>';
            
            // Môn học
            echo '<div class="form-group">';
            echo '<label for="mon_hoc">Môn học:</label>';
            echo '<select name="mon_hoc" id="mon_hoc">';
            echo '<option value="">Tất cả môn học</option>';
            
            // Lấy danh sách môn học
            $monhoc_query = new WP_Query(array(
                'post_type' => 'monhoc',
                'posts_per_page' => -1,
                'orderby' => 'title',
                'order' => 'ASC'
            ));
            
            if ($monhoc_query->have_posts()) {
                while ($monhoc_query->have_posts()) {
                    $monhoc_query->the_post();
                    $selected = ($mon_hoc_id === get_the_ID()) ? 'selected' : '';
                    echo '<option value="' . get_the_ID() . '" ' . $selected . '>' . get_the_title() . '</option>';
                }
            }
            wp_reset_postdata();
            
            echo '</select>';
            echo '</div>';
            
            // Submit button
            echo '<button type="submit">Lọc</button>';
            echo '</form>';
            
            // Danh sách điểm danh
            $args = array(
                'post_type' => 'diemdanh',
                'posts_per_page' => 20,
                'orderby' => 'date',
                'order' => 'DESC'
            );
            
            // Thêm điều kiện lọc
            $meta_query = array();
            
            if ($lop_id > 0) {
                $meta_query[] = array(
                    'key' => 'lop',
                    'value' => $lop_id,
                    'compare' => '='
                );
            }
            
            if ($mon_hoc_id > 0) {
                $meta_query[] = array(
                    'key' => 'mon_hoc',
                    'value' => $mon_hoc_id,
                    'compare' => '='
                );
            }
            
            if (!empty($meta_query)) {
                $args['meta_query'] = array(
                    'relation' => 'AND',
                    $meta_query
                );
            }
            
            $diemdanh_query = new WP_Query($args);
            
            if ($diemdanh_query->have_posts()) {
                echo '<table class="diemdanh-table">';
                echo '<thead>';
                echo '<tr>';
                echo '<th>Tiêu đề</th>';
                echo '<th>Lớp</th>';
                echo '<th>Môn học</th>';
                echo '<th>Ngày</th>';
                echo '<th>Thao tác</th>';
                echo '</tr>';
                echo '</thead>';
                echo '<tbody>';
                
                while ($diemdanh_query->have_posts()) {
                    $diemdanh_query->the_post();
                    $post_id = get_the_ID();
                    
                    $lop_id = get_field('lop', $post_id);
                    $mon_hoc_id = get_field('mon_hoc', $post_id);
                    $ngay = get_field('ngay', $post_id);
                    
                    $lop_name = $lop_id ? get_the_title($lop_id) : 'N/A';
                    $mon_hoc_name = $mon_hoc_id ? get_the_title($mon_hoc_id) : 'N/A';
                    $ngay_format = $ngay ? date_i18n('d/m/Y', strtotime($ngay)) : 'N/A';
                    
                    echo '<tr>';
                    echo '<td>' . get_the_title() . '</td>';
                    echo '<td>' . $lop_name . '</td>';
                    echo '<td>' . $mon_hoc_name . '</td>';
                    echo '<td>' . $ngay_format . '</td>';
                    echo '<td>';
                    echo '<a href="' . get_edit_post_link($post_id) . '" class="button">Chỉnh sửa</a>';
                    echo '</td>';
                    echo '</tr>';
                }
                
                echo '</tbody>';
                echo '</table>';
                
                // Pagination
                $big = 999999999;
                echo '<div class="pagination">';
                echo paginate_links(array(
                    'base' => str_replace($big, '%#%', esc_url(get_pagenum_link($big))),
                    'format' => '?paged=%#%',
                    'current' => max(1, get_query_var('paged')),
                    'total' => $diemdanh_query->max_num_pages
                ));
                echo '</div>';
                
            } else {
                echo '<p>Không tìm thấy bản ghi điểm danh nào.</p>';
                }
                
                wp_reset_postdata();
        }
        
        return ob_get_clean();
    }
    
    /**
     * Hiển thị thông tin điểm danh của sinh viên
     */
    private function display_student_attendance($sv_id, $lop_id, $mon_hoc_id = 0) {
        // Args để lấy điểm danh
        $args = array(
                    'post_type' => 'diemdanh',
                    'posts_per_page' => -1,
                    'meta_query' => array(
                        array(
                    'key' => 'lop',
                    'value' => $lop_id,
                            'compare' => '='
                        )
                    ),
            'orderby' => 'date',
            'order' => 'DESC'
        );
        
        // Thêm lọc theo môn học nếu có
        if ($mon_hoc_id > 0) {
            $args['meta_query'][] = array(
                'key' => 'mon_hoc',
                'value' => $mon_hoc_id,
                'compare' => '='
            );
        }
        
        $diemdanh_query = new WP_Query($args);
        
        if ($diemdanh_query->have_posts()) {
            echo '<table class="diemdanh-table">';
            echo '<thead>';
            echo '<tr>';
            echo '<th>Môn học</th>';
            echo '<th>Ngày</th>';
            echo '<th>Trạng thái</th>';
            echo '</tr>';
            echo '</thead>';
            echo '<tbody>';
            
            $absent_count = 0;
            $total_count = 0;
                
                while ($diemdanh_query->have_posts()) {
                    $diemdanh_query->the_post();
                $diemdanh_id = get_the_ID();
                $mon_id = get_field('mon_hoc', $diemdanh_id);
                $mon_name = get_the_title($mon_id);
                $ngay = get_field('ngay', $diemdanh_id);
                $ngay_format = date_i18n('d/m/Y', strtotime($ngay));
                
                // Lấy trạng thái điểm danh của sinh viên này
                $sinh_vien_status = get_post_meta($diemdanh_id, 'sinh_vien_status', true);
                $status = isset($sinh_vien_status[$sv_id]) ? $sinh_vien_status[$sv_id] : 'absent';
                
                $status_text = $status === 'present' ? 'Có mặt' : 'Vắng mặt';
                $status_class = $status === 'present' ? 'status-present' : 'status-absent';
                
                if ($status === 'absent') {
                    $absent_count++;
                }
                $total_count++;
                
                echo '<tr>';
                echo '<td>' . $mon_name . '</td>';
                echo '<td>' . $ngay_format . '</td>';
                echo '<td class="' . $status_class . '">' . $status_text . '</td>';
                echo '</tr>';
            }
            
            echo '</tbody>';
            echo '</table>';
            
            // Tổng kết
            echo '<div style="margin-top: 15px;">';
            echo '<p><strong>Tổng số buổi học:</strong> ' . $total_count . '</p>';
            echo '<p><strong>Số buổi vắng:</strong> ' . $absent_count . '</p>';
            echo '<p><strong>Tỷ lệ vắng:</strong> ' . ($total_count > 0 ? round(($absent_count / $total_count) * 100, 2) : 0) . '%</p>';
            echo '</div>';
            
        } else {
            echo '<p>Chưa có dữ liệu điểm danh nào.</p>';
        }
        
        wp_reset_postdata();
    }
    
    /**
     * Xử lý form điểm danh
     */
    public function handle_diemdanh_form() {
        if (!isset($_POST['diemdanh_action']) || !isset($_POST['diemdanh_nonce']) || !wp_verify_nonce($_POST['diemdanh_nonce'], 'diemdanh_action')) {
            return;
        }

        // Xử lý cập nhật điểm danh
        if ($_POST['diemdanh_action'] === 'update' && isset($_POST['diemdanh_id']) && isset($_POST['sinh_vien_status'])) {
            $diemdanh_id = intval($_POST['diemdanh_id']);
            $sinh_vien_status = $_POST['sinh_vien_status'];
            
            // Mảng để lưu thông tin cập nhật
            $updated_data = [];
            
            foreach ($sinh_vien_status as $sv_id => $status) {
                $sv_id = intval($sv_id);
                $status = sanitize_text_field($status);
                
                // Lưu vào mảng
                if ($sv_id > 0) {
                    $updated_data[$sv_id] = $status;
                }
            }
            
            // Cập nhật meta field cho post điểm danh
            if (!empty($updated_data)) {
                update_post_meta($diemdanh_id, 'sinh_vien_status', $updated_data);
                
                // Chuyển hướng lại với thông báo thành công
                wp_redirect(add_query_arg('updated', '1', wp_get_referer()));
                exit;
            }
        }
    }
    
    /**
     * Xử lý AJAX cập nhật điểm danh
     */
    public function ajax_update_diemdanh() {
        // Kiểm tra nonce
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'diemdanh_ajax_nonce')) {
            wp_send_json_error('Lỗi bảo mật!');
        }
        
        // Kiểm tra dữ liệu
        if (!isset($_POST['diemdanh_id']) || !isset($_POST['sinh_vien_id']) || !isset($_POST['status'])) {
            wp_send_json_error('Thiếu dữ liệu cần thiết!');
        }
        
        $diemdanh_id = intval($_POST['diemdanh_id']);
        $sinh_vien_id = intval($_POST['sinh_vien_id']);
        $status = sanitize_text_field($_POST['status']);
        
        // Lấy dữ liệu hiện tại
        $current_data = get_post_meta($diemdanh_id, 'sinh_vien_status', true);
        if (empty($current_data) || !is_array($current_data)) {
            $current_data = array();
        }
        
        // Cập nhật dữ liệu
        $current_data[$sinh_vien_id] = $status;
        
        // Lưu lại
        $updated = update_post_meta($diemdanh_id, 'sinh_vien_status', $current_data);
        
        if ($updated) {
            wp_send_json_success('Đã cập nhật trạng thái điểm danh.');
        } else {
            wp_send_json_error('Không thể cập nhật trạng thái điểm danh!');
        }
    }

    /**
     * Đăng ký template tùy chỉnh cho trang archive điểm danh
     */
    public function register_diemdanh_archive_template($template) {
        if (is_post_type_archive('diemdanh')) {
            $custom_template = QLSV_PLUGIN_DIR . 'templates/archive-diemdanh.php';
            if (file_exists($custom_template)) {
                return $custom_template;
            }
        }
        return $template;
    }

    /**
     * Đăng ký template tùy chỉnh cho trang single điểm danh
     */
    public function register_diemdanh_single_template($template) {
        if (is_singular('diemdanh')) {
            $custom_template = QLSV_PLUGIN_DIR . 'templates/single-diemdanh.php';
            if (file_exists($custom_template)) {
                return $custom_template;
            }
        }
        return $template;
    }

    /**
     * Handle diemdanh queries to prevent 404 errors with params
     */
    public function handle_diemdanh_queries($query) {
        // Xử lý các tham số lop và mon_hoc
        $lop_id = isset($_GET['lop']) ? intval($_GET['lop']) : 0;
        $mon_hoc_id = isset($_GET['mon_hoc']) ? intval($_GET['mon_hoc']) : 0;
        
        // Kiểm tra nếu trang hiện tại là archive diemdanh hoặc có tham số lop/mon_hoc
        if (($query->is_main_query() && $query->is_post_type_archive('diemdanh')) || 
            ($query->is_main_query() && !empty($lop_id) && !empty($mon_hoc_id))) {
            
            // Thiết lập các query var cần thiết
            $query->set('post_type', 'diemdanh');
            
            // Đặt là archive diemdanh để sử dụng template đúng
            $query->is_archive = true;
            $query->is_post_type_archive = true;
            $query->is_singular = false;
            $query->is_single = false;
            $query->is_page = false;
            
            // Đánh dấu là không phải 404
            $query->is_404 = false;
            
            // Không thiết lập số lượng bài viết, sẽ được xử lý trong template
            if (!empty($lop_id) && !empty($mon_hoc_id)) {
                $query->set('lop', $lop_id);
                $query->set('mon_hoc', $mon_hoc_id);
                
                // Bắt lấy dữ liệu từ query var
                set_query_var('lop', $lop_id);
                set_query_var('mon_hoc', $mon_hoc_id);
            }
            
            // Tạo dummy post để đảm bảo không có lỗi từ theme
            global $post, $wp_query;
            if (!isset($wp_query->posts) || empty($wp_query->posts)) {
                $dummy_post = new stdClass();
                $dummy_post->ID = 0;
                $dummy_post->post_type = 'diemdanh';
                $dummy_post->post_title = 'Điểm Danh';
                $dummy_post->post_name = 'diemdanh';
                $dummy_post->post_content = '';
                $dummy_post->comment_count = 0;
                $dummy_post->post_status = 'publish';
                $dummy_post->comment_status = 'closed';
                $dummy_post->post_author = 1;
                $dummy_post->post_date = date('Y-m-d H:i:s');
                $dummy_post->post_date_gmt = date('Y-m-d H:i:s');
                
                // Thiết lập post đầu tiên trong danh sách posts
                $wp_query->posts = array($dummy_post);
                $wp_query->post_count = 1;
                $wp_query->found_posts = 1;
                $post = $dummy_post;
            }
            
            return;
        }
    }

    /**
     * Generate proper URL for diemdanh with parameters
     * 
     * @param int $lop_id The class ID
     * @param int $mon_hoc_id The subject ID
     * @return string The URL
     */
    public function get_diemdanh_url($lop_id, $mon_hoc_id) {
        // Use direct query parameters which are more reliable
        return add_query_arg(
            array(
                'lop' => $lop_id,
                'mon_hoc' => $mon_hoc_id
            ),
            home_url('/diemdanh/')
        );
    }

    /**
     * Lấy danh sách sinh viên theo lớp
     * 
     * @param int $lop_id ID của lớp cần lấy sinh viên
     * @return array Danh sách sinh viên của lớp
     */
    public function get_students_by_class($lop_id) {
        $students = array();
        
        if (!$lop_id) {
            return $students;
        }
        
        // Query để lấy sinh viên theo lớp
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
            'orderby' => 'meta_value',
            'meta_key' => 'ho_ten',
            'order' => 'ASC'
        );
        
        $student_query = new WP_Query($args);
        
        if ($student_query->have_posts()) {
            while ($student_query->have_posts()) {
                $student_query->the_post();
                $student_id = get_the_ID();
                
                // Lấy thông tin từ các trường ACF
                $ho_ten = get_field('ho_ten', $student_id) ? get_field('ho_ten', $student_id) : get_the_title($student_id);
                $ma_sinh_vien = get_field('ma_sinh_vien', $student_id);
                $email = get_field('email', $student_id);
                $so_dien_thoai = get_field('so_dien_thoai', $student_id);
                $ngay_sinh = get_field('ngay_sinh', $student_id);
                $khoa = get_field('khoa', $student_id);
                
                // Thêm sinh viên vào mảng kết quả
                $students[] = array(
                    'id' => $student_id,
                    'name' => $ho_ten,
                    'mssv' => $ma_sinh_vien,
                    'email' => $email,
                    'phone' => $so_dien_thoai,
                    'dob' => $ngay_sinh,
                    'khoa' => $khoa,
                    'lop_id' => $lop_id
                );
            }
            wp_reset_postdata();
        }
        
        return $students;
    }
} 