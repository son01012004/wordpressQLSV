<?php
/**
 * Class quản lý thời khóa biểu và các chức năng liên quan
 */
class QLSV_ThoiKhoaBieu {

    /**
     * Loader để đăng ký các hooks
     *
     * @var QLSV_Loader
     */
    private $loader;

    /**
     * Khởi tạo class thời khóa biểu
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
        // Đăng ký custom post type
        $this->loader->add_action('init', $this, 'register_post_type');
        
        // Đăng ký ACF fields
        $this->loader->add_action('acf/init', $this, 'register_acf_fields');
        
        // Thêm metabox
        $this->loader->add_action('add_meta_boxes', $this, 'add_meta_boxes');
        
        // Xử lý lưu dữ liệu khi lưu post
        $this->loader->add_action('acf/save_post', $this, 'update_tkb_title', 20);
        
        // Đăng ký template tùy chỉnh
        $this->loader->add_filter('template_include', $this, 'register_custom_templates');
    }
    
    /**
     * Đăng ký các shortcodes
     */
    private function register_shortcodes() {
        add_shortcode('qlsv_thoikhoabieu', array($this, 'thoikhoabieu_shortcode'));
        add_shortcode('qlsv_tkb_lop', array($this, 'tkb_lop_shortcode'));
    }
    
    /**
     * Đăng ký post type thời khóa biểu
     */
    public function register_post_type() {
        $labels = array(
            'name'               => _x('Thời khóa biểu', 'post type general name', 'qlsv'),
            'singular_name'      => _x('Thời khóa biểu', 'post type singular name', 'qlsv'),
            'menu_name'          => _x('Thời khóa biểu', 'admin menu', 'qlsv'),
            'name_admin_bar'     => _x('Thời khóa biểu', 'add new on admin bar', 'qlsv'),
            'add_new'            => _x('Thêm mới', 'thoikhoabieu', 'qlsv'),
            'add_new_item'       => __('Thêm thời khóa biểu mới', 'qlsv'),
            'new_item'           => __('Thời khóa biểu mới', 'qlsv'),
            'edit_item'          => __('Sửa thời khóa biểu', 'qlsv'),
            'view_item'          => __('Xem thời khóa biểu', 'qlsv'),
            'all_items'          => __('Tất cả thời khóa biểu', 'qlsv'),
            'search_items'       => __('Tìm thời khóa biểu', 'qlsv'),
            'parent_item_colon'  => __('Thời khóa biểu cha:', 'qlsv'),
            'not_found'          => __('Không tìm thấy thời khóa biểu.', 'qlsv'),
            'not_found_in_trash' => __('Không có thời khóa biểu nào trong thùng rác.', 'qlsv')
        );

        $args = array(
            'labels'             => $labels,
            'description'        => __('Thời khóa biểu các lớp học và môn học', 'qlsv'),
            'public'             => true,
            'publicly_queryable' => true,
            'show_ui'            => true,
            'show_in_menu'       => true,
            'query_var'          => true,
            'rewrite'            => array('slug' => 'thoikhoabieu'),
            'capability_type'    => 'post',
            'has_archive'        => true,
            'hierarchical'       => false,
            'menu_position'      => null,
            'supports'           => array('title'),
            'menu_icon'          => 'dashicons-calendar-alt',
        );

        register_post_type('thoikhoabieu', $args);
    }
    
    /**
     * Đăng ký ACF Fields cho thời khóa biểu
     */
    public function register_acf_fields() {
        if (!function_exists('acf_add_local_field_group')) {
            error_log('ACF không được kích hoạt, không thể đăng ký fields cho thời khóa biểu');
            return;
        }

        acf_add_local_field_group(array(
            'key' => 'group_thoikhoabieu',
            'title' => 'Thông tin thời khóa biểu',
            'fields' => array(
                array(
                    'key' => 'field_mon_hoc_tkb',
                    'label' => 'Môn học',
                    'name' => 'mon_hoc',
                    'type' => 'post_object',
                    'instructions' => 'Chọn môn học',
                    'required' => 1,
                    'post_type' => array('monhoc'),
                    'return_format' => 'id',
                    'ui' => 1,
                ),
                array(
                    'key' => 'field_lop_tkb',
                    'label' => 'Lớp',
                    'name' => 'lop',
                    'type' => 'post_object',
                    'instructions' => 'Chọn lớp',
                    'required' => 1,
                    'post_type' => array('lop'),
                    'return_format' => 'id',
                    'ui' => 1,
                ),
                array(
                    'key' => 'field_giang_vien',
                    'label' => 'Giảng viên',
                    'name' => 'giang_vien',
                    'type' => 'user',
                    'instructions' => 'Chọn giảng viên',
                    'required' => 0,
                    'role' => '', // Có thể giới hạn theo role nếu cần
                    'return_format' => 'id',
                ),
                array(
                    'key' => 'field_thu',
                    'label' => 'Thứ',
                    'name' => 'thu',
                    'type' => 'select',
                    'instructions' => 'Chọn thứ',
                    'required' => 1,
                    'choices' => array(
                        'Thứ 2' => 'Thứ 2',
                        'Thứ 3' => 'Thứ 3',
                        'Thứ 4' => 'Thứ 4',
                        'Thứ 5' => 'Thứ 5',
                        'Thứ 6' => 'Thứ 6',
                        'Thứ 7' => 'Thứ 7',
                        'Chủ nhật' => 'Chủ nhật',
                    ),
                    'default_value' => 'Thứ 2',
                ),
                array(
                    'key' => 'field_gio_bat_dau',
                    'label' => 'Giờ bắt đầu',
                    'name' => 'gio_bat_dau',
                    'type' => 'time_picker',
                    'instructions' => 'Chọn giờ bắt đầu',
                    'required' => 1,
                    'display_format' => 'H:i',
                    'return_format' => 'H:i',
                ),
                array(
                    'key' => 'field_gio_ket_thuc',
                    'label' => 'Giờ kết thúc',
                    'name' => 'gio_ket_thuc',
                    'type' => 'time_picker',
                    'instructions' => 'Chọn giờ kết thúc',
                    'required' => 1,
                    'display_format' => 'H:i',
                    'return_format' => 'H:i',
                ),
                array(
                    'key' => 'field_phong',
                    'label' => 'Phòng',
                    'name' => 'phong',
                    'type' => 'text',
                    'instructions' => 'Nhập tên phòng học',
                    'required' => 0,
                ),
                array(
                    'key' => 'field_tuan_hoc',
                    'label' => 'Tuần học',
                    'name' => 'tuan_hoc',
                    'type' => 'text',
                    'instructions' => 'Nhập các tuần học (VD: 1-10, 12, 15)',
                    'required' => 0,
                ),
            ),
            'location' => array(
                array(
                    array(
                        'param' => 'post_type',
                        'operator' => '==',
                        'value' => 'thoikhoabieu',
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
     * Thêm metabox
     */
    public function add_meta_boxes() {
        add_meta_box(
            'tkb_info',
            'Thông tin lịch học',
            array($this, 'render_meta_box'),
            'thoikhoabieu',
            'normal',
            'high'
        );
    }
    
    /**
     * Render metabox thông tin thời khóa biểu
     */
    public function render_meta_box($post) {
        // ACF đã xử lý hiển thị form
        echo '<p>Vui lòng cung cấp đầy đủ thông tin thời khóa biểu bên dưới.</p>';
    }
    
    /**
     * Cập nhật tiêu đề thời khóa biểu tự động
     */
    public function update_tkb_title($post_id) {
        // Chỉ xử lý post type 'thoikhoabieu'
        if (get_post_type($post_id) !== 'thoikhoabieu') {
            return;
        }

        // Cập nhật tiêu đề dựa trên dữ liệu đã chọn
        $mon_hoc_id = get_field('mon_hoc', $post_id);
        $lop_id = get_field('lop', $post_id);
        $thu = get_field('thu', $post_id);
        $gio_bat_dau = get_field('gio_bat_dau', $post_id);
        $gio_ket_thuc = get_field('gio_ket_thuc', $post_id);
        
        if ($mon_hoc_id && $lop_id && $thu && $gio_bat_dau && $gio_ket_thuc) {
            $mon_hoc = get_the_title($mon_hoc_id);
            $lop = get_the_title($lop_id);
            
            // Cập nhật tiêu đề
            $title = sprintf('%s - %s - %s (%s-%s)', $mon_hoc, $lop, $thu, $gio_bat_dau, $gio_ket_thuc);
            
            // Cập nhật post mà không trigger save_post hook
            remove_action('acf/save_post', array($this, 'update_tkb_title'), 20);
            
            wp_update_post(array(
                'ID' => $post_id,
                'post_title' => $title,
            ));
            
            add_action('acf/save_post', array($this, 'update_tkb_title'), 20);
        }
    }
    
    /**
     * Shortcode hiển thị thời khóa biểu tổng hợp
     */
    public function thoikhoabieu_shortcode($atts) {
        // Tham số mặc định
        $atts = shortcode_atts(array(
            'lop_id' => 0,      // ID lớp (nếu cần lọc theo lớp)
            'monhoc_id' => 0,   // ID môn học (nếu cần lọc theo môn)
            'giang_vien' => 0,  // ID giảng viên
            'loai_view' => 'tuan' // 'tuan' hoặc 'danh_sach'
        ), $atts);
        
        // Lấy tham số từ URL nếu có
        $lop_from_get = isset($_GET['lop']) ? intval($_GET['lop']) : 0;
        if ($lop_from_get > 0) {
            $atts['lop_id'] = $lop_from_get;
        }
        
        $monhoc_from_get = isset($_GET['monhoc']) ? intval($_GET['monhoc']) : 0;
        if ($monhoc_from_get > 0) {
            $atts['monhoc_id'] = $monhoc_from_get;
        }
        
        $teacher_from_get = isset($_GET['teacher']) ? intval($_GET['teacher']) : 0;
        if ($teacher_from_get > 0) {
            $atts['giang_vien'] = $teacher_from_get;
        }
        
        $view_from_get = isset($_GET['view']) ? sanitize_text_field($_GET['view']) : '';
        if (in_array($view_from_get, array('tuan', 'danh_sach'))) {
            $atts['loai_view'] = $view_from_get;
        }

        // Load template tương ứng theo loại view
        ob_start();
        
        if ($atts['loai_view'] == 'tuan') {
            $template_path = QLSV_PLUGIN_DIR . 'templates/thoikhoabieu-tuan.php';
        } else {
            $template_path = QLSV_PLUGIN_DIR . 'templates/thoikhoabieu-list.php';
        }
        
        if (file_exists($template_path)) {
            // Kiểm tra xem ACF có được kích hoạt hay không
            if (!function_exists('get_field')) {
                echo '<div class="error-message" style="background-color: #f8d7da; color: #721c24; padding: 10px; border: 1px solid #f5c6cb; margin-bottom: 20px;">';
                echo '<strong>Lỗi:</strong> Plugin Advanced Custom Fields chưa được kích hoạt. Vui lòng kích hoạt plugin này để hiển thị thời khóa biểu.';
                echo '</div>';
                return ob_get_clean();
            }

            // Truy vấn dữ liệu thời khóa biểu
            $args = array(
                'post_type' => 'thoikhoabieu',
                'posts_per_page' => -1,
                'meta_query' => array('relation' => 'AND')
            );
            
            // Lọc theo lớp
            if (!empty($atts['lop_id'])) {
                $args['meta_query'][] = array(
                    'key' => 'lop',
                    'value' => $atts['lop_id'],
                    'compare' => '='
                );
            }
            
            // Lọc theo môn học
            if (!empty($atts['monhoc_id'])) {
                $args['meta_query'][] = array(
                    'key' => 'mon_hoc',
                    'value' => $atts['monhoc_id'],
                    'compare' => '='
                );
            }
            
            // Lọc theo giảng viên
            if (!empty($atts['giang_vien'])) {
                $args['meta_query'][] = array(
                    'key' => 'giang_vien',
                    'value' => $atts['giang_vien'],
                    'compare' => '='
                );
            }
            
            // Debug query
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log('TKB Query: ' . print_r($args, true));
            }
            
            $tkb_query = new WP_Query($args);
            
            // Chuẩn bị dữ liệu để hiển thị trong template
            $tkb_data = array();
            $thu_order = array(
                'Thứ 2' => 0,
                'Thứ 3' => 1,
                'Thứ 4' => 2,
                'Thứ 5' => 3,
                'Thứ 6' => 4,
                'Thứ 7' => 5,
                'Chủ nhật' => 6
            );
            
            while ($tkb_query->have_posts()) {
                $tkb_query->the_post();
                $post_id = get_the_ID();
                
                // Lấy các trường từ ACF
                $mon_hoc_id = get_field('mon_hoc', $post_id);
                $lop_id = get_field('lop', $post_id);
                $giang_vien_id = get_field('giang_vien', $post_id);
                $thu = get_field('thu', $post_id);
                $gio_bat_dau = get_field('gio_bat_dau', $post_id);
                $gio_ket_thuc = get_field('gio_ket_thuc', $post_id);
                $phong = get_field('phong', $post_id);
                $tuan_hoc = get_field('tuan_hoc', $post_id);
                
                // Lấy và kiểm tra tên môn học
                $mon_hoc = 'Không xác định';
                if (!empty($mon_hoc_id)) {
                    $mon_title = get_the_title($mon_hoc_id);
                    if (!empty($mon_title)) {
                        $mon_hoc = $mon_title;
                    }
                    
                    if (defined('WP_DEBUG') && WP_DEBUG) {
                        error_log('Môn học ID: ' . $mon_hoc_id . ', Title: ' . $mon_title);
                    }
                }
                
                // Lấy và kiểm tra thông tin lớp
                $lop = 'Không xác định';
                $lop_info = array(
                    'ten_lop' => '',
                    'khoa' => ''
                );
                
                if (!empty($lop_id)) {
                    // Kiểm tra xem post có tồn tại không
                    $lop_post = get_post($lop_id);
                    
                    if ($lop_post && !is_wp_error($lop_post)) {
                        $lop_title = $lop_post->post_title;
                        if (!empty($lop_title)) {
                            $lop = $lop_title;
                        }
                        
                        $ten_lop = get_field('ten_lop', $lop_id);
                        if (!empty($ten_lop)) {
                            $lop = $ten_lop;
                        }
                        
                        $khoa = get_field('khoa', $lop_id);
                        
                        $lop_info = array(
                            'ten_lop' => $ten_lop,
                            'khoa' => $khoa
                        );
                    }
                    
                    if (defined('WP_DEBUG') && WP_DEBUG) {
                        error_log('Lớp ID: ' . $lop_id . ', Title: ' . $lop . ', Post exists: ' . ($lop_post ? 'Yes' : 'No'));
                    }
                }
                
                // Lấy thông tin giảng viên
                $giang_vien = 'Không xác định';
                if (!empty($giang_vien_id)) {
                    $user_data = get_userdata($giang_vien_id);
                    if ($user_data) {
                        $giang_vien = $user_data->display_name;
                        
                        // Thêm học vị nếu có
                        $hoc_vi = get_field('hoc_vi', 'user_' . $giang_vien_id);
                        if (!empty($hoc_vi)) {
                            $giang_vien = $hoc_vi . ' ' . $giang_vien;
                        }
                    }
                    
                    if (defined('WP_DEBUG') && WP_DEBUG) {
                        error_log('Giảng viên ID: ' . $giang_vien_id . ', Name: ' . ($user_data ? $user_data->display_name : 'Not found'));
                    }
                }
                
                // Tạo item tkb
                $tkb_item = array(
                    'id' => $post_id,
                    'mon_hoc' => $mon_hoc,
                    'mon_hoc_id' => $mon_hoc_id,
                    'lop' => $lop,
                    'lop_id' => $lop_id,
                    'lop_info' => $lop_info,
                    'giang_vien' => $giang_vien,
                    'giang_vien_id' => $giang_vien_id,
                    'thu' => $thu,
                    'thu_order' => isset($thu_order[$thu]) ? $thu_order[$thu] : 99,
                    'gio_bat_dau' => $gio_bat_dau,
                    'gio_ket_thuc' => $gio_ket_thuc,
                    'phong' => $phong,
                    'tuan_hoc' => $tuan_hoc
                );
                
                if ($atts['loai_view'] == 'tuan') {
                    // Phân loại theo thứ trong tuần
                    $tkb_data[$thu][] = $tkb_item;
                } else {
                    // Danh sách đơn giản
                    $tkb_data[] = $tkb_item;
                }
            }
            
            // Sắp xếp theo thứ tự các ngày trong tuần
            if ($atts['loai_view'] == 'tuan') {
                uksort($tkb_data, function($a, $b) use ($thu_order) {
                    $order_a = isset($thu_order[$a]) ? $thu_order[$a] : 99;
                    $order_b = isset($thu_order[$b]) ? $thu_order[$b] : 99;
                    return $order_a - $order_b;
                });
                
                // Sắp xếp theo giờ bắt đầu trong mỗi ngày
                foreach ($tkb_data as $thu => $items) {
                    usort($items, function($a, $b) {
                        return strcmp($a['gio_bat_dau'], $b['gio_bat_dau']);
                    });
                    $tkb_data[$thu] = $items;
                }
            } else {
                // Sắp xếp theo thứ, rồi đến giờ
                usort($tkb_data, function($a, $b) {
                    if ($a['thu_order'] != $b['thu_order']) {
                        return $a['thu_order'] - $b['thu_order'];
                    }
                    return strcmp($a['gio_bat_dau'], $b['gio_bat_dau']);
                });
            }
            
            // Lấy danh sách lớp và môn học cho bộ lọc
            $all_classes = get_posts(array(
                'post_type' => 'lop',
                'posts_per_page' => -1,
                'orderby' => 'title',
                'order' => 'ASC',
                'post_status' => 'publish'
            ));
            
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log('Số lượng lớp: ' . count($all_classes));
            }
            
            $all_courses = get_posts(array(
                'post_type' => 'monhoc',
                'posts_per_page' => -1,
                'orderby' => 'title',
                'order' => 'ASC',
                'post_status' => 'publish'
            ));
            
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log('Số lượng môn học: ' . count($all_courses));
            }
            
            // Lấy danh sách giáo viên có role 'giaovien', 'teacher' hoặc 'administrator'
            $teacher_roles = array('giaovien', 'teacher', 'administrator');
            $all_teachers = get_users(array(
                'role__in' => $teacher_roles,
                'orderby' => 'display_name',
                'order' => 'ASC',
                'number' => 100 // Giới hạn số lượng để tránh quá tải
            ));
            
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log('Số lượng giáo viên: ' . count($all_teachers));
                foreach ($all_teachers as $teacher) {
                    error_log('Giáo viên: ID=' . $teacher->ID . ', Name=' . $teacher->display_name);
                }
            }
            
            // Chuẩn bị template data
            $template_data = array(
                'tkb_data' => $tkb_data,
                'all_classes' => $all_classes,
                'all_courses' => $all_courses,
                'all_teachers' => $all_teachers,
                'atts' => $atts,
                'selected_class' => $atts['lop_id'],
                'selected_course' => $atts['monhoc_id'],
                'selected_teacher' => $atts['giang_vien']
            );
            
            // Extract dữ liệu để sử dụng trong template
            extract($template_data);
            
            include $template_path;
            
            wp_reset_postdata();
        } else {
            echo 'Template không tồn tại.';
        }
        
        return ob_get_clean();
    }
    
    /**
     * Shortcode hiển thị thời khóa biểu theo lớp
     */
    public function tkb_lop_shortcode($atts) {
        // Wrapper shortcode, sử dụng lại thoikhoabieu_shortcode với tham số cố định
        $atts = shortcode_atts(array(
            'lop_id' => 0, // ID lớp muốn hiển thị mặc định
        ), $atts);
        
        return $this->thoikhoabieu_shortcode(array(
            'lop_id' => $atts['lop_id'],
            'loai_view' => 'tuan'
        ));
    }
    
    /**
     * Đăng ký template tùy chỉnh cho post type thời khóa biểu
     */
    public function register_custom_templates($template) {
        // Debug để kiểm tra template đang được sử dụng
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log('Template request: ' . $template);
        }
        
        if (is_singular('thoikhoabieu')) {
            $custom_template = $this->locate_template('single-thoikhoabieu.php');
            
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log('Trying to load single template: ' . $custom_template);
                error_log('File exists: ' . (file_exists($custom_template) ? 'Yes' : 'No'));
            }
            
            if ($custom_template) {
                return $custom_template;
            }
        }
        
        if (is_post_type_archive('thoikhoabieu')) {
            $archive_template = $this->locate_template('archive-thoikhoabieu.php');
            
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log('Trying to load archive template: ' . $archive_template);
                error_log('File exists: ' . (file_exists($archive_template) ? 'Yes' : 'No'));
            }
            
            if ($archive_template) {
                return $archive_template;
            }
        }
        
        return $template;
    }
    
    /**
     * Tìm template của plugin từ các vị trí có thể có
     * 
     * @param string $template_name Tên file template cần tìm
     * @return string|bool Đường dẫn đến file template, hoặc false nếu không tìm thấy
     */
    private function locate_template($template_name) {
        // Các vị trí có thể chứa template (thứ tự ưu tiên)
        $template_paths = array(
            // Template trong thư mục theme con (nếu có)
            trailingslashit(get_stylesheet_directory()) . 'qlsv/' . $template_name,
            // Template trong thư mục theme cha (nếu dùng child theme)
            trailingslashit(get_template_directory()) . 'qlsv/' . $template_name,
            // Template trong thư mục templates của plugin
            QLSV_PLUGIN_DIR . 'templates/' . $template_name,
        );
        
        // Kiểm tra từng vị trí theo thứ tự ưu tiên
        foreach ($template_paths as $template_path) {
            if (file_exists($template_path)) {
                return $template_path;
            }
        }
        
        return false;
    }
} 