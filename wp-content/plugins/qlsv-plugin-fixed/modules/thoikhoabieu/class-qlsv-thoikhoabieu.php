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
        
        // Đăng ký CSS
        $this->loader->add_action('wp_enqueue_scripts', $this, 'enqueue_styles');
        $this->loader->add_action('admin_enqueue_scripts', $this, 'enqueue_styles');
        
        // Xử lý form thêm/sửa thời khóa biểu
        $this->loader->add_action('init', $this, 'process_tkb_form');
        $this->loader->add_action('wp', $this, 'handle_tkb_view_requests');
        
        // Thêm trang cài đặt
        $this->loader->add_action('admin_menu', $this, 'add_settings_page');
        $this->loader->add_action('admin_init', $this, 'register_settings');
    }
    
    /**
     * Đăng ký các shortcodes
     */
    private function register_shortcodes() {
        add_shortcode('qlsv_thoikhoabieu', array($this, 'thoikhoabieu_shortcode'));
        add_shortcode('qlsv_tkb_lop', array($this, 'tkb_lop_shortcode'));
        add_shortcode('qlsv_tkb_giaovien', array($this, 'tkb_giaovien_shortcode'));
        add_shortcode('qlsv_tkb_add_edit', array($this, 'tkb_add_edit_shortcode'));
        add_shortcode('qlsv_tkb_detail', array($this, 'tkb_detail_shortcode'));
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
     * Shortcode hiển thị thời khóa biểu
     */
    public function thoikhoabieu_shortcode($atts) {
        // Xử lý các thuộc tính shortcode
        $atts = shortcode_atts(array(
            'lop' => 0,
            'monhoc' => 0,
            'user_id' => 0
        ), $atts, 'qlsv_thoikhoabieu');
        
        // Lấy thông tin lọc từ URL nếu có
        $selected_class = isset($_GET['lop']) ? intval($_GET['lop']) : intval($atts['lop']);
        $selected_course = isset($_GET['monhoc']) ? intval($_GET['monhoc']) : intval($atts['monhoc']);
        $selected_teacher = isset($_GET['teacher']) ? intval($_GET['teacher']) : 0;
        $view_type = isset($_GET['view']) ? sanitize_text_field($_GET['view']) : 'danh_sach';
        
        // Kiểm tra role của user hiện tại
        $current_user = wp_get_current_user();
        $user_roles = $current_user->roles;
        $is_admin = current_user_can('administrator');
        $is_teacher = in_array('giaovien', $user_roles);
        $is_student = in_array('sinhvien', $user_roles) || (!$is_admin && !$is_teacher && is_user_logged_in());
        
        // Lấy tất cả lớp và môn học để hiển thị trong dropdown
        $all_classes = get_posts(array(
            'post_type' => 'lop',
            'posts_per_page' => -1,
            'orderby' => 'title',
            'order' => 'ASC'
        ));
        
        $all_courses = get_posts(array(
            'post_type' => 'monhoc',
            'posts_per_page' => -1,
            'orderby' => 'title',
            'order' => 'ASC'
        ));
        
        // Lấy danh sách giáo viên
        $all_teachers = get_users(array(
            'role' => 'giaovien',
            'orderby' => 'display_name',
            'order' => 'ASC'
        ));
        
        // Xử lý theo role
        if ($is_teacher && !$selected_teacher) {
            // Giáo viên mặc định xem lịch dạy của chính mình
            $selected_teacher = $current_user->ID;
        } elseif ($is_student && !$selected_class) {
            // Sinh viên mặc định xem lịch học của lớp mình
            $current_user_email = $current_user->user_email;
            
            // Tìm sinh viên theo email
            $student_query = new WP_Query(array(
                'post_type' => 'sinhvien',
                'posts_per_page' => 1,
                'meta_query' => array(
                    array(
                        'key' => 'email',
                        'value' => $current_user_email,
                        'compare' => '='
                    )
                )
            ));
            
            if ($student_query->have_posts()) {
                $student_query->the_post();
                $student_id = get_the_ID();
                $selected_class = get_field('lop', $student_id);
                wp_reset_postdata();
            }
        }
        
        // Xây dựng tham số truy vấn
        $args = array(
            'post_type' => 'thoikhoabieu',
            'posts_per_page' => -1,
            'orderby' => 'meta_value',
            'meta_key' => 'thu',
            'order' => 'ASC',
            'meta_query' => array(
                'relation' => 'AND'
            )
        );
        
        // Thêm điều kiện lọc theo role
        if ($is_teacher && !$is_admin) {
            // Giáo viên chỉ xem được lịch dạy của mình
            $args['meta_query'][] = array(
                'key' => 'giang_vien',
                'value' => $current_user->ID,
                'compare' => '='
            );
        } elseif ($is_student && !$is_admin && $selected_class) {
            // Sinh viên chỉ xem được lịch học của lớp mình
            $args['meta_query'][] = array(
                'key' => 'lop',
                'value' => $selected_class,
                'compare' => '='
            );
        } else {
            // Admin có thể xem tất cả, nhưng vẫn áp dụng bộ lọc nếu có
            if ($selected_class) {
                $args['meta_query'][] = array(
                    'key' => 'lop',
                    'value' => $selected_class,
                    'compare' => '='
                );
            }
            
            if ($selected_course) {
                $args['meta_query'][] = array(
                    'key' => 'mon_hoc',
                    'value' => $selected_course,
                    'compare' => '='
                );
            }
            
            if ($selected_teacher) {
                $args['meta_query'][] = array(
                    'key' => 'giang_vien',
                    'value' => $selected_teacher,
                    'compare' => '='
                );
            }
        }
        
        // Thực hiện truy vấn
        $tkb_query = new WP_Query($args);
        
        // Khởi tạo mảng dữ liệu thời khóa biểu
        $tkb_data = array();
        
        // Xử lý kết quả truy vấn
        if ($tkb_query->have_posts()) {
            while ($tkb_query->have_posts()) {
                $tkb_query->the_post();
                $tkb_id = get_the_ID();
                
                // Lấy thông tin thứ, giờ học
                $thu = get_field('thu', $tkb_id);
                $gio_bat_dau = get_field('gio_bat_dau', $tkb_id);
                $gio_ket_thuc = get_field('gio_ket_thuc', $tkb_id);
                
                // Nếu hiển thị theo tuần, tổ chức dữ liệu theo ngày trong tuần
                if ($view_type === 'tuan') {
                    if (!isset($tkb_data[$thu])) {
                        $tkb_data[$thu] = array();
                    }
                    
                    $tkb_data[$thu][] = array(
                        'ID' => $tkb_id,
                        'thu' => $thu,
                        'gio_bat_dau' => $gio_bat_dau,
                        'gio_ket_thuc' => $gio_ket_thuc
                    );
                } else {
                    // Hiển thị dạng danh sách, tổ chức dữ liệu theo thứ tự thời gian
                    $tkb_data[] = array(
                        'ID' => $tkb_id,
                        'thu' => $thu,
                        'gio_bat_dau' => $gio_bat_dau,
                        'gio_ket_thuc' => $gio_ket_thuc
                    );
                }
            }
            
            // Nếu hiển thị dạng danh sách, sắp xếp theo thứ và giờ học
            if ($view_type !== 'tuan') {
                usort($tkb_data, function($a, $b) {
                    $days_order = array(
                        'Thứ 2' => 1,
                        'Thứ 3' => 2,
                        'Thứ 4' => 3,
                        'Thứ 5' => 4,
                        'Thứ 6' => 5,
                        'Thứ 7' => 6,
                        'Chủ nhật' => 7
                    );
                    
                    // So sánh theo thứ
                    $day_comparison = $days_order[$a['thu']] - $days_order[$b['thu']];
                    
                    if ($day_comparison !== 0) {
                        return $day_comparison;
                    }
                    
                    // Nếu cùng thứ, so sánh theo giờ bắt đầu
                    return strcmp($a['gio_bat_dau'], $b['gio_bat_dau']);
                });
            } else {
                // Nếu hiển thị theo tuần, sắp xếp các slot trong cùng một ngày theo giờ
                foreach ($tkb_data as $day => $items) {
                    usort($tkb_data[$day], function($a, $b) {
                        return strcmp($a['gio_bat_dau'], $b['gio_bat_dau']);
                    });
                }
            }
        }
        
        wp_reset_postdata();
        
        // Bắt đầu output buffer
        ob_start();
        
        // Chuẩn bị các biến cho template
        $template_vars = array(
            'tkb_data' => $tkb_data,
            'all_classes' => $all_classes,
            'all_courses' => $all_courses,
            'all_teachers' => $all_teachers,
            'selected_class' => $selected_class,
            'selected_course' => $selected_course,
            'selected_teacher' => $selected_teacher,
            'is_admin' => $is_admin,
            'is_teacher' => $is_teacher,
            'is_student' => $is_student
        );
        
        // Lựa chọn template dựa vào loại hiển thị
        if ($view_type === 'tuan') {
            $template_path = $this->locate_template('thoikhoabieu-tuan.php');
        } else {
            $template_path = $this->locate_template('thoikhoabieu-list.php');
        }
        
        // Kiểm tra xem template có tồn tại không
        if ($template_path && file_exists($template_path)) {
            // Extract để sử dụng các biến trong template
            extract($template_vars);
            include $template_path;
        } else {
            echo 'Template không tồn tại.';
        }
        
        // Trả về kết quả
        return ob_get_clean();
    }
    
    /**
     * Shortcode hiển thị thời khóa biểu theo lớp
     */
    public function tkb_lop_shortcode($atts) {
        // Wrapper shortcode, sử dụng lại thoikhoabieu_shortcode với tham số cố định
        $atts = shortcode_atts(array(
            'lop' => 0, // ID lớp muốn hiển thị mặc định
        ), $atts);
        
        // Kiểm tra quyền
        $current_user = wp_get_current_user();
        $user_roles = $current_user->roles;
        
        // Chỉ admin và giáo viên mới có thể xem lớp bất kỳ 
        // Sinh viên chỉ được xem lớp của mình
        if (!current_user_can('administrator') && !in_array('giaovien', $user_roles)) {
            // Nếu không phải admin hoặc giáo viên, kiểm tra lớp của sinh viên
            $student_query = new WP_Query(array(
                'post_type' => 'sinhvien',
                'posts_per_page' => 1,
                'meta_query' => array(
                    array(
                        'key' => 'email',
                        'value' => $current_user->user_email,
                        'compare' => '='
                    )
                )
            ));
            
            if ($student_query->have_posts()) {
                $student_query->the_post();
                $student_id = get_the_ID();
                $student_class = get_field('lop', $student_id);
                wp_reset_postdata();
                
                // Nếu sinh viên không thuộc lớp đang muốn xem và không yêu cầu lớp mặc định
                if ($atts['lop'] != 0 && $student_class != $atts['lop']) {
                    return '<div class="qlsv-error">Bạn không có quyền xem thời khóa biểu của lớp này.</div>';
                }
                
                // Set lớp của sinh viên nếu không chỉ định lớp
                if ($atts['lop'] == 0) {
                    $atts['lop'] = $student_class;
                }
            }
        }
        
        return $this->thoikhoabieu_shortcode(array(
            'lop' => $atts['lop'],
            'view' => 'tuan'
        ));
    }
    
    /**
     * Shortcode hiển thị thời khóa biểu của giáo viên
     */
    public function tkb_giaovien_shortcode($atts) {
        // Xử lý các thuộc tính shortcode
        $atts = shortcode_atts(array(
            'teacher_id' => 0, // ID giáo viên muốn hiển thị (0 = giáo viên đang đăng nhập)
        ), $atts, 'qlsv_tkb_giaovien');
        
        // Kiểm tra quyền
        $current_user = wp_get_current_user();
        $user_roles = $current_user->roles;
        $is_admin = current_user_can('administrator');
        $is_teacher = in_array('giaovien', $user_roles);
        
        // Xác định teacher_id từ người dùng hiện tại hoặc tham số
        $teacher_id = intval($atts['teacher_id']);
        if ($teacher_id == 0 && $is_teacher) {
            $teacher_id = $current_user->ID;
        }
        
        // Kiểm tra quyền xem lịch dạy
        if (!$is_admin && !$is_teacher) {
            return '<div class="qlsv-error">Bạn không có quyền xem lịch giảng dạy.</div>';
        }
        
        // Nếu không phải admin và không xem lịch của chính mình
        if (!$is_admin && $is_teacher && $teacher_id != $current_user->ID) {
            return '<div class="qlsv-error">Bạn chỉ có thể xem lịch giảng dạy của chính mình.</div>';
        }
        
        // Sử dụng lại shortcode thoikhoabieu với teacher_id
        return $this->thoikhoabieu_shortcode(array(
            'teacher' => $teacher_id,
            'view' => 'tuan'
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
    
    /**
     * Đăng ký CSS cho module thời khóa biểu
     */
    public function enqueue_styles() {
        wp_enqueue_style(
            'qlsv-style', 
            QLSV_PLUGIN_URL . 'assets/css/qlsv-style.css',
            array(),
            QLSV_VERSION
        );
        
        // Luôn đăng ký dashicons, cần thiết cho các icon trong form
        wp_enqueue_style('dashicons');
    }
    
    /**
     * Shortcode hiển thị form thêm/sửa thời khóa biểu
     */
    public function tkb_add_edit_shortcode($atts) {
        // Xử lý các thuộc tính shortcode
        $atts = shortcode_atts(array(
            'redirect' => '', // URL chuyển hướng sau khi lưu
        ), $atts, 'qlsv_tkb_add_edit');
        
        // Lấy thông tin ID và hành động từ GET
        $tkb_id = isset($_GET['tkb_id']) ? intval($_GET['tkb_id']) : 0;
        $action = isset($_GET['action']) ? sanitize_text_field($_GET['action']) : 'add';
        $redirect_url = !empty($atts['redirect']) ? $atts['redirect'] : get_permalink();
        
        // Debug info
        if (defined('WP_DEBUG') && WP_DEBUG && current_user_can('administrator')) {
            echo '<div class="tkb-debug">';
            echo '<p>Shortcode Debug:</p>';
            echo '<p>action: ' . $action . '</p>';
            echo '<p>tkb_id: ' . $tkb_id . '</p>';
            echo '<p>redirect_url: ' . $redirect_url . '</p>';
            echo '</div>';
        }
        
        // Nếu đang xem chi tiết thay vì thêm/sửa
        if ($action == 'view' && $tkb_id > 0) {
            return $this->tkb_detail_shortcode(array('tkb_id' => $tkb_id));
        }
        
        // Chỉ admin mới có quyền thêm/sửa
        if (($action == 'add' || $action == 'edit') && !current_user_can('administrator')) {
            return '<div class="qlsv-error">' . esc_html__('Bạn không có quyền truy cập chức năng này.', 'qlsv') . '</div>';
        }
        
        // Bắt đầu output buffer
        ob_start();
        
        // Chuẩn bị các biến cho template
        $template_vars = array(
            'tkb_id' => $tkb_id,
            'redirect_url' => $redirect_url
        );
        
        // Tìm template
        $template_path = $this->locate_template('thoikhoabieu-add-edit.php');
        
        // Kiểm tra xem template có tồn tại không
        if ($template_path && file_exists($template_path)) {
            // Extract để sử dụng các biến trong template
            extract($template_vars);
            include $template_path;
        } else {
            echo '<div class="qlsv-error">' . esc_html__('Template không tồn tại.', 'qlsv') . '</div>';
        }
        
        // Trả về kết quả
        return ob_get_clean();
    }
    
    /**
     * Shortcode hiển thị chi tiết thời khóa biểu
     */
    public function tkb_detail_shortcode($atts) {
        // Xử lý các thuộc tính shortcode
        $atts = shortcode_atts(array(
            'tkb_id' => 0, // ID thời khóa biểu muốn hiển thị
        ), $atts, 'qlsv_tkb_detail');
        
        // Lấy ID từ shortcode hoặc từ URL
        $tkb_id = !empty($atts['tkb_id']) ? intval($atts['tkb_id']) : 0;
        if (!$tkb_id && isset($_GET['tkb_id'])) {
            $tkb_id = intval($_GET['tkb_id']);
        }
        
        // Bắt đầu output buffer
        ob_start();
        
        // Chuẩn bị các biến cho template
        $template_vars = array(
            'tkb_id' => $tkb_id
        );
        
        // Tìm template
        $template_path = $this->locate_template('thoikhoabieu-detail.php');
        
        // Kiểm tra xem template có tồn tại không
        if ($template_path && file_exists($template_path)) {
            // Extract để sử dụng các biến trong template
            extract($template_vars);
            include $template_path;
        } else {
            echo '<div class="qlsv-error">' . esc_html__('Template không tồn tại.', 'qlsv') . '</div>';
        }
        
        // Trả về kết quả
        return ob_get_clean();
    }
    
    /**
     * Xử lý form thêm/sửa thời khóa biểu
     */
    public function process_tkb_form() {
        // Kiểm tra nếu form được submit
        if (isset($_POST['tkb_action']) && isset($_POST['tkb_nonce'])) {
            $action = sanitize_text_field($_POST['tkb_action']);
            $tkb_id = isset($_POST['tkb_id']) ? intval($_POST['tkb_id']) : 0;
            
            // Log cho debug
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log("TKB FORM SUBMITTED: Action=" . $action . ", ID=" . $tkb_id);
                error_log("POST DATA: " . print_r($_POST, true));
            }
            
            // Kiểm tra nonce
            if (!wp_verify_nonce($_POST['tkb_nonce'], 'tkb_save_action')) {
                if (defined('WP_DEBUG') && WP_DEBUG) {
                    error_log("TKB FORM ERROR: Invalid nonce");
                }
                wp_die('Lỗi bảo mật. Vui lòng thử lại.');
            }
            
            // Kiểm tra quyền quản trị
            if (!current_user_can('administrator')) {
                if (defined('WP_DEBUG') && WP_DEBUG) {
                    error_log("TKB FORM ERROR: Permission denied for user " . get_current_user_id());
                }
                wp_die('Bạn không có quyền thực hiện thao tác này.');
            }
            
            // Redirect URL sau khi lưu
            $redirect_url = isset($_POST['redirect_url']) && !empty($_POST['redirect_url']) 
                ? esc_url_raw($_POST['redirect_url']) 
                : get_permalink();
                
            if (empty($redirect_url)) {
                $redirect_url = home_url();
            }
            
            // Dữ liệu cho thời khóa biểu
            $mon_hoc = isset($_POST['mon_hoc']) ? intval($_POST['mon_hoc']) : 0;
            $lop = isset($_POST['lop']) ? intval($_POST['lop']) : 0;
            $giang_vien = isset($_POST['giang_vien']) ? intval($_POST['giang_vien']) : '';
            $thu = isset($_POST['thu']) ? sanitize_text_field($_POST['thu']) : '';
            $gio_bat_dau = isset($_POST['gio_bat_dau']) ? sanitize_text_field($_POST['gio_bat_dau']) : '';
            $gio_ket_thuc = isset($_POST['gio_ket_thuc']) ? sanitize_text_field($_POST['gio_ket_thuc']) : '';
            $phong = isset($_POST['phong']) ? sanitize_text_field($_POST['phong']) : '';
            $tuan_hoc = isset($_POST['tuan_hoc']) ? sanitize_text_field($_POST['tuan_hoc']) : '';
            
            // Kiểm tra dữ liệu bắt buộc
            if (empty($mon_hoc) || empty($lop) || empty($thu) || empty($gio_bat_dau) || empty($gio_ket_thuc)) {
                if (defined('WP_DEBUG') && WP_DEBUG) {
                    error_log("TKB FORM ERROR: Missing required fields");
                    error_log("mon_hoc=$mon_hoc, lop=$lop, thu=$thu, gio_bat_dau=$gio_bat_dau, gio_ket_thuc=$gio_ket_thuc");
                }
                wp_redirect(add_query_arg('message', 'error', $redirect_url));
                exit;
            }
            
            // Tạo hoặc cập nhật post
            if ($action === 'create') {
                // Tạo post mới
                $post_args = array(
                    'post_title' => 'Thời khóa biểu mới',
                    'post_status' => 'publish',
                    'post_type' => 'thoikhoabieu'
                );
                
                if (defined('WP_DEBUG') && WP_DEBUG) {
                    error_log("Creating new TKB with args: " . print_r($post_args, true));
                }
                
                $tkb_id = wp_insert_post($post_args);
                
                if (is_wp_error($tkb_id)) {
                    if (defined('WP_DEBUG') && WP_DEBUG) {
                        error_log("Error creating TKB: " . $tkb_id->get_error_message());
                    }
                    wp_redirect(add_query_arg('message', 'error', $redirect_url));
                    exit;
                }
            }
            
            if ($tkb_id && $tkb_id > 0) {
                // Cập nhật các trường ACF
                if (defined('WP_DEBUG') && WP_DEBUG) {
                    error_log("Updating ACF fields for TKB ID: $tkb_id");
                }
                
                // Kiểm tra ACF function
                if (!function_exists('update_field')) {
                    if (defined('WP_DEBUG') && WP_DEBUG) {
                        error_log("ACF functions not available");
                    }
                    wp_redirect(add_query_arg('message', 'error', $redirect_url));
                    exit;
                }
                
                update_field('mon_hoc', $mon_hoc, $tkb_id);
                update_field('lop', $lop, $tkb_id);
                if (!empty($giang_vien)) {
                    update_field('giang_vien', $giang_vien, $tkb_id);
                }
                update_field('thu', $thu, $tkb_id);
                update_field('gio_bat_dau', $gio_bat_dau, $tkb_id);
                update_field('gio_ket_thuc', $gio_ket_thuc, $tkb_id);
                update_field('phong', $phong, $tkb_id);
                update_field('tuan_hoc', $tuan_hoc, $tkb_id);
                
                // Cập nhật tiêu đề tự động
                $title_update = wp_update_post(array(
                    'ID' => $tkb_id,
                    'post_title' => $this->generate_tkb_title($tkb_id)
                ));
                
                if (defined('WP_DEBUG') && WP_DEBUG) {
                    error_log("Title update result: " . print_r($title_update, true));
                    error_log("Redirecting to success: $redirect_url");
                }
                
                // Chuyển hướng về trang chỉ định với thông báo thành công
                wp_redirect(add_query_arg('message', 'success', $redirect_url));
                exit;
            } else {
                // Lỗi khi lưu
                if (defined('WP_DEBUG') && WP_DEBUG) {
                    error_log("Invalid TKB ID: $tkb_id");
                }
                wp_redirect(add_query_arg('message', 'error', $redirect_url));
                exit;
            }
        }
    }
    
    /**
     * Tạo tiêu đề thời khóa biểu
     */
    public function generate_tkb_title($post_id) {
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
            return $title;
        }
        
        return 'Thời khóa biểu';
    }
    
    /**
     * Đăng ký trang cài đặt
     */
    public function add_settings_page() {
        add_submenu_page(
            'edit.php?post_type=thoikhoabieu',
            __('Cài đặt thời khóa biểu', 'qlsv'),
            __('Cài đặt', 'qlsv'),
            'manage_options',
            'tkb-settings',
            array($this, 'render_settings_page')
        );
    }
    
    /**
     * Đăng ký các cài đặt
     */
    public function register_settings() {
        register_setting('tkb_settings_group', 'qlsv_tkb_add_edit_page');
        register_setting('tkb_settings_group', 'qlsv_tkb_list_page');
        
        add_settings_section(
            'tkb_settings_section',
            __('Cài đặt trang thời khóa biểu', 'qlsv'),
            array($this, 'tkb_settings_section_callback'),
            'tkb-settings'
        );
        
        add_settings_field(
            'qlsv_tkb_add_edit_page',
            __('Trang thêm/sửa thời khóa biểu', 'qlsv'),
            array($this, 'tkb_add_edit_page_callback'),
            'tkb-settings',
            'tkb_settings_section'
        );
        
        add_settings_field(
            'qlsv_tkb_list_page',
            __('Trang danh sách thời khóa biểu', 'qlsv'),
            array($this, 'tkb_list_page_callback'),
            'tkb-settings',
            'tkb_settings_section'
        );
    }
    
    /**
     * Callback cho section
     */
    public function tkb_settings_section_callback() {
        echo '<p>' . __('Cấu hình các trang hiển thị thời khóa biểu. Hãy chọn các trang WordPress đã được tạo và thêm shortcode tương ứng.', 'qlsv') . '</p>';
        if (defined('WP_DEBUG') && WP_DEBUG) {
            echo '<p><strong>Debug:</strong> Trang thêm/sửa: ' . get_option('qlsv_tkb_add_edit_page', 'Chưa cài đặt') . '</p>';
            echo '<p><strong>Debug:</strong> Trang danh sách: ' . get_option('qlsv_tkb_list_page', 'Chưa cài đặt') . '</p>';
        }
    }
    
    /**
     * Callback cho field chọn trang thêm/sửa
     */
    public function tkb_add_edit_page_callback() {
        $tkb_add_edit_page = get_option('qlsv_tkb_add_edit_page');
        
        // Lấy danh sách trang
        $pages = get_pages();
        
        echo '<select name="qlsv_tkb_add_edit_page" id="qlsv_tkb_add_edit_page">';
        echo '<option value="">' . __('-- Chọn trang --', 'qlsv') . '</option>';
        
        foreach ($pages as $page) {
            echo '<option value="' . esc_attr($page->ID) . '" ' . selected($tkb_add_edit_page, $page->ID, false) . '>' . esc_html($page->post_title) . '</option>';
        }
        
        echo '</select>';
        echo '<p class="description">' . __('Chọn trang sử dụng shortcode [qlsv_tkb_add_edit] để thêm/sửa thời khóa biểu', 'qlsv') . '</p>';
    }
    
    /**
     * Callback cho field chọn trang danh sách
     */
    public function tkb_list_page_callback() {
        $tkb_list_page = get_option('qlsv_tkb_list_page');
        
        // Lấy danh sách trang
        $pages = get_pages();
        
        echo '<select name="qlsv_tkb_list_page" id="qlsv_tkb_list_page">';
        echo '<option value="">' . __('-- Chọn trang --', 'qlsv') . '</option>';
        
        foreach ($pages as $page) {
            echo '<option value="' . esc_attr($page->ID) . '" ' . selected($tkb_list_page, $page->ID, false) . '>' . esc_html($page->post_title) . '</option>';
        }
        
        echo '</select>';
        echo '<p class="description">' . __('Chọn trang sử dụng shortcode [qlsv_thoikhoabieu] để hiển thị danh sách thời khóa biểu', 'qlsv') . '</p>';
    }
    
    /**
     * Render trang cài đặt
     */
    public function render_settings_page() {
        ?>
        <div class="wrap">
            <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
            
            <form method="post" action="options.php">
                <?php
                settings_fields('tkb_settings_group');
                do_settings_sections('tkb-settings');
                submit_button();
                ?>
            </form>
            
            <h2><?php _e('Hướng dẫn sử dụng', 'qlsv'); ?></h2>
            <ol>
                <li><?php _e('Tạo hai trang WordPress mới:', 'qlsv'); ?>
                    <ul>
                        <li><?php _e('Trang danh sách thời khóa biểu - thêm shortcode [qlsv_thoikhoabieu]', 'qlsv'); ?></li>
                        <li><?php _e('Trang thêm/sửa thời khóa biểu - thêm shortcode [qlsv_tkb_add_edit]', 'qlsv'); ?></li>
                    </ul>
                </li>
                <li><?php _e('Chọn các trang này trong cài đặt ở trên', 'qlsv'); ?></li>
                <li><?php _e('Khi đó, các nút thêm/sửa thời khóa biểu sẽ trỏ đến trang frontend thay vì trang admin', 'qlsv'); ?></li>
                <li><?php _e('Lưu ý: Chỉ admin mới có quyền thêm và sửa thời khóa biểu', 'qlsv'); ?></li>
            </ol>
            
            <?php
            // Kiểm tra xem các trang đã được cài đặt chưa
            $add_edit_page_id = get_option('qlsv_tkb_add_edit_page', 0);
            $list_page_id = get_option('qlsv_tkb_list_page', 0);
            
            if (!$add_edit_page_id || !$list_page_id): 
            ?>
            <div class="notice notice-warning">
                <p><strong><?php _e('Cảnh báo:', 'qlsv'); ?></strong> <?php _e('Bạn chưa cấu hình đầy đủ các trang. Vui lòng chọn các trang cho thời khóa biểu.', 'qlsv'); ?></p>
            </div>
            <?php endif; ?>
            
            <?php if ($add_edit_page_id): ?>
            <div class="notice notice-info">
                <p><strong><?php _e('Trang thêm/sửa:', 'qlsv'); ?></strong> <a href="<?php echo esc_url(get_permalink($add_edit_page_id)); ?>" target="_blank"><?php echo esc_html(get_the_title($add_edit_page_id)); ?></a></p>
            </div>
            <?php endif; ?>
            
            <?php if ($list_page_id): ?>
            <div class="notice notice-info">
                <p><strong><?php _e('Trang danh sách:', 'qlsv'); ?></strong> <a href="<?php echo esc_url(get_permalink($list_page_id)); ?>" target="_blank"><?php echo esc_html(get_the_title($list_page_id)); ?></a></p>
            </div>
            <?php endif; ?>
            
            <?php if (WP_DEBUG): ?>
            <div class="notice notice-info">
                <p><?php _e('Chế độ debug đang bật. Thông tin lỗi sẽ được ghi vào log.', 'qlsv'); ?></p>
            </div>
            <?php endif; ?>
        </div>
        <?php
    }
    
    /**
     * Xử lý các yêu cầu xem chi tiết/sửa/thêm mới
     */
    public function handle_tkb_view_requests() {
        // Kiểm tra xem có đang ở trang tùy chỉnh không
        if (!is_page()) {
            return;
        }
        
        $current_page_id = get_the_ID();
        $add_edit_page_id = get_option('qlsv_tkb_add_edit_page', 0);
        
        // Nếu không phải trang thêm/sửa thì không xử lý
        if ($current_page_id != $add_edit_page_id) {
            return;
        }
        
        // Kiểm tra tham số action
        $action = isset($_GET['action']) ? sanitize_text_field($_GET['action']) : '';
        $tkb_id = isset($_GET['tkb_id']) ? intval($_GET['tkb_id']) : 0;
        
        // Chỉ ghi log nếu là WP_DEBUG
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log("TKB Action: " . $action . ", ID: " . $tkb_id . ", Page ID: " . $current_page_id);
        }
    }
} 