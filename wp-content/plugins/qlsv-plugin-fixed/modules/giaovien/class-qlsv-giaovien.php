<?php
/**
 * Class quản lý giáo viên và các chức năng liên quan
 */
class QLSV_GiaoVien {

    /**
     * Loader để đăng ký các hooks
     *
     * @var QLSV_Loader
     */
    private $loader;

    /**
     * Khởi tạo class quản lý giáo viên
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
        // Đăng ký custom user role cho giáo viên
        $this->loader->add_action('init', $this, 'register_teacher_role');
        
        // Đăng ký custom fields cho giáo viên
        $this->loader->add_action('acf/init', $this, 'register_acf_fields');
        
        // Thêm menu quản lý giáo viên trong admin
        $this->loader->add_action('admin_menu', $this, 'add_admin_menu');
        
        // Thêm filter cho user list để hiển thị chỉ giáo viên
        $this->loader->add_action('pre_get_users', $this, 'filter_users_by_role');
        
        // Đăng ký template tùy chỉnh
        $this->loader->add_filter('template_include', $this, 'register_custom_templates');
    }
    
    /**
     * Đăng ký các shortcodes
     */
    private function register_shortcodes() {
        add_shortcode('qlsv_giaovien_list', array($this, 'giaovien_list_shortcode'));
        add_shortcode('qlsv_giaovien_profile', array($this, 'giaovien_profile_shortcode'));
        add_shortcode('qlsv_giaovien_tkb', array($this, 'giaovien_tkb_shortcode'));
    }
    
    /**
     * Đăng ký role giáo viên
     */
    public function register_teacher_role() {
        // Kiểm tra xem role đã tồn tại chưa
        if (!get_role('giaovien')) {
            // Thêm role mới với các capabilities tương tự như author
            add_role('giaovien', 'Giáo viên', array(
                'read' => true,
                'upload_files' => true,
                'publish_posts' => true,
                'edit_posts' => true,
                'edit_published_posts' => true,
                'delete_posts' => true,
                'delete_published_posts' => true,
            ));
        }
    }
    
    /**
     * Đăng ký ACF Fields cho giáo viên
     */
    public function register_acf_fields() {
        if (!function_exists('acf_add_local_field_group')) {
            error_log('ACF không được kích hoạt, không thể đăng ký fields cho giáo viên');
            return;
        }

        acf_add_local_field_group(array(
            'key' => 'group_giaovien',
            'title' => 'Thông tin giáo viên',
            'fields' => array(
                array(
                    'key' => 'field_ma_giaovien',
                    'label' => 'Mã giáo viên',
                    'name' => 'ma_giaovien',
                    'type' => 'text',
                    'instructions' => 'Nhập mã giáo viên',
                    'required' => 1,
                ),
                array(
                    'key' => 'field_hoc_vi',
                    'label' => 'Học vị',
                    'name' => 'hoc_vi',
                    'type' => 'select',
                    'instructions' => 'Chọn học vị',
                    'required' => 0,
                    'choices' => array(
                        'Cử nhân' => 'Cử nhân',
                        'Thạc sĩ' => 'Thạc sĩ',
                        'Tiến sĩ' => 'Tiến sĩ',
                        'Phó Giáo sư' => 'Phó Giáo sư',
                        'Giáo sư' => 'Giáo sư',
                    ),
                    'default_value' => 'Cử nhân',
                ),
                array(
                    'key' => 'field_khoa_gv',
                    'label' => 'Khoa/Bộ môn',
                    'name' => 'khoa',
                    'type' => 'text',
                    'instructions' => 'Nhập tên khoa hoặc bộ môn',
                    'required' => 0,
                ),
                array(
                    'key' => 'field_chuyen_mon',
                    'label' => 'Chuyên môn',
                    'name' => 'chuyen_mon',
                    'type' => 'text',
                    'instructions' => 'Nhập lĩnh vực chuyên môn',
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
                    'key' => 'field_email_gv',
                    'label' => 'Email',
                    'name' => 'email_gv',
                    'type' => 'email',
                    'instructions' => 'Nhập địa chỉ email',
                    'required' => 0,
                ),
                array(
                    'key' => 'field_hinh_anh',
                    'label' => 'Hình ảnh',
                    'name' => 'hinh_anh',
                    'type' => 'image',
                    'instructions' => 'Chọn hình ảnh đại diện',
                    'required' => 0,
                    'return_format' => 'array',
                ),
                array(
                    'key' => 'field_gioi_thieu',
                    'label' => 'Giới thiệu',
                    'name' => 'gioi_thieu',
                    'type' => 'wysiwyg',
                    'instructions' => 'Nhập thông tin giới thiệu về giáo viên',
                    'required' => 0,
                ),
            ),
            'location' => array(
                array(
                    array(
                        'param' => 'user_role',
                        'operator' => '==',
                        'value' => 'giaovien',
                    ),
                ),
                array(
                    array(
                        'param' => 'user_role',
                        'operator' => '==',
                        'value' => 'administrator',
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
     * Thêm menu quản lý giáo viên trong admin
     */
    public function add_admin_menu() {
        add_menu_page(
            'Quản lý giáo viên',
            'Giáo viên',
            'edit_users',
            'giaovien',
            array($this, 'render_admin_page'),
            'dashicons-businessman',
            30
        );
    }
    
    /**
     * Hiển thị trang quản lý giáo viên trong admin
     */
    public function render_admin_page() {
        // Kiểm tra quyền
        if (!current_user_can('edit_users')) {
            wp_die(__('Bạn không có quyền truy cập trang này.', 'qlsv'));
        }
        
        // URL tới trang Users để lọc giáo viên
        $teachers_url = admin_url('users.php?role=giaovien');
        $add_teacher_url = admin_url('user-new.php');
        
        // Hiển thị trang admin
        ?>
        <div class="wrap">
            <h1><?php echo esc_html__('Quản lý giáo viên', 'qlsv'); ?></h1>
            
            <div class="card">
                <h2><?php echo esc_html__('Thông tin về quản lý giáo viên', 'qlsv'); ?></h2>
                <p><?php echo esc_html__('Quản lý thông tin giáo viên, lịch giảng dạy và các dữ liệu liên quan.', 'qlsv'); ?></p>
                
                <p>
                    <a href="<?php echo esc_url($teachers_url); ?>" class="button button-primary">
                        <?php echo esc_html__('Xem danh sách giáo viên', 'qlsv'); ?>
                    </a>
                    
                    <a href="<?php echo esc_url($add_teacher_url); ?>" class="button">
                        <?php echo esc_html__('Thêm giáo viên mới', 'qlsv'); ?>
                    </a>
                </p>
            </div>
            
            <div class="card" style="margin-top: 20px;">
                <h2><?php echo esc_html__('Hướng dẫn sử dụng', 'qlsv'); ?></h2>
                <ol>
                    <li><?php echo esc_html__('Thêm giáo viên mới bằng cách tạo người dùng với vai trò "Giáo viên".', 'qlsv'); ?></li>
                    <li><?php echo esc_html__('Điền đầy đủ thông tin giáo viên trong phần thông tin cá nhân.', 'qlsv'); ?></li>
                    <li><?php echo esc_html__('Sử dụng shortcode [qlsv_giaovien_list] để hiển thị danh sách giáo viên.', 'qlsv'); ?></li>
                    <li><?php echo esc_html__('Sử dụng shortcode [qlsv_giaovien_tkb id="123"] để hiển thị thời khóa biểu của giáo viên.', 'qlsv'); ?></li>
                </ol>
            </div>
        </div>
        <?php
    }
    
    /**
     * Lọc danh sách user để hiển thị chỉ giáo viên
     */
    public function filter_users_by_role($query) {
        global $pagenow;
        
        // Chỉ áp dụng trong trang users.php và khi có param role=giaovien
        if (is_admin() && 'users.php' == $pagenow && isset($_GET['role']) && 'giaovien' == $_GET['role']) {
            // Đảm bảo chỉ lọc user có role là giaovien
            $query->set('role', 'giaovien');
        }
    }
    
    /**
     * Shortcode hiển thị danh sách giáo viên
     */
    public function giaovien_list_shortcode($atts) {
        // Tham số mặc định
        $atts = shortcode_atts(array(
            'khoa' => '',      // Lọc theo khoa
            'limit' => 20,     // Số lượng hiển thị
            'orderby' => 'display_name', // Sắp xếp theo tên
            'order' => 'ASC',  // Thứ tự sắp xếp
        ), $atts);
        
        ob_start();
        
        // Tạo query để lấy danh sách giáo viên
        $args = array(
            'role' => 'giaovien',
            'number' => intval($atts['limit']),
            'orderby' => $atts['orderby'],
            'order' => $atts['order']
        );
        
        // Lọc theo khoa nếu có
        if (!empty($atts['khoa'])) {
            $args['meta_query'] = array(
                array(
                    'key' => 'khoa',
                    'value' => $atts['khoa'],
                    'compare' => 'LIKE'
                )
            );
        }
        
        // Lấy danh sách giáo viên
        $teachers = get_users($args);
        
        // Kiểm tra nếu ACF chưa được kích hoạt
        if (!function_exists('get_field')) {
            echo '<div class="error-message">';
            echo __('Plugin Advanced Custom Fields chưa được kích hoạt. Vui lòng kích hoạt plugin này để hiển thị thông tin giáo viên.', 'qlsv');
            echo '</div>';
            return ob_get_clean();
        }
        
        // Nạp template
        $template_path = QLSV_PLUGIN_DIR . 'templates/giaovien-list.php';
        
        if (file_exists($template_path)) {
            // Dữ liệu cho template
            $data = array(
                'teachers' => $teachers,
                'atts' => $atts
            );
            
            // Extract dữ liệu để sử dụng trong template
            extract($data);
            
            // Include template
            include $template_path;
        } else {
            echo '<p>Template không tồn tại.</p>';
            
            // Hiển thị danh sách giáo viên cơ bản
            if (!empty($teachers)) {
                echo '<div class="giaovien-list">';
                foreach ($teachers as $teacher) {
                    $ma_gv = get_field('ma_giaovien', 'user_' . $teacher->ID);
                    $hoc_vi = get_field('hoc_vi', 'user_' . $teacher->ID);
                    
                    echo '<div class="giaovien-item">';
                    echo '<h3>' . esc_html($hoc_vi . ' ' . $teacher->display_name) . '</h3>';
                    echo '<p>Mã GV: ' . esc_html($ma_gv) . '</p>';
                    echo '<p>' . esc_html($teacher->user_email) . '</p>';
                    echo '</div>';
                }
                echo '</div>';
            } else {
                echo '<p>Không tìm thấy giáo viên nào.</p>';
            }
        }
        
        return ob_get_clean();
    }
    
    /**
     * Shortcode hiển thị trang cá nhân giáo viên
     */
    public function giaovien_profile_shortcode($atts) {
        // Tham số mặc định
        $atts = shortcode_atts(array(
            'id' => 0,     // ID của giáo viên
        ), $atts);
        
        // Kiểm tra ID
        $teacher_id = intval($atts['id']);
        if (empty($teacher_id)) {
            // Lấy từ query string nếu có
            $teacher_id = isset($_GET['gv']) ? intval($_GET['gv']) : 0;
        }
        
        // Nếu không có ID, trả về thông báo lỗi
        if (empty($teacher_id)) {
            return '<p>Không tìm thấy thông tin giáo viên.</p>';
        }
        
        // Kiểm tra nếu ACF chưa được kích hoạt
        if (!function_exists('get_field')) {
            return '<div class="error-message">' . 
                   __('Plugin Advanced Custom Fields chưa được kích hoạt. Vui lòng kích hoạt plugin này để hiển thị thông tin giáo viên.', 'qlsv') . 
                   '</div>';
        }
        
        // Lấy thông tin giáo viên
        $teacher = get_userdata($teacher_id);
        
        // Kiểm tra xem user có tồn tại và là giáo viên không
        if (!$teacher || !in_array('giaovien', $teacher->roles)) {
            return '<p>Không tìm thấy thông tin giáo viên.</p>';
        }
        
        ob_start();
        
        // Nạp template
        $template_path = QLSV_PLUGIN_DIR . 'templates/giaovien-profile.php';
        
        if (file_exists($template_path)) {
            // Lấy các trường thông tin
            $ma_gv = get_field('ma_giaovien', 'user_' . $teacher_id);
            $hoc_vi = get_field('hoc_vi', 'user_' . $teacher_id);
            $khoa = get_field('khoa', 'user_' . $teacher_id);
            $chuyen_mon = get_field('chuyen_mon', 'user_' . $teacher_id);
            $sdt = get_field('so_dien_thoai', 'user_' . $teacher_id);
            $email_gv = get_field('email_gv', 'user_' . $teacher_id);
            $hinh_anh = get_field('hinh_anh', 'user_' . $teacher_id);
            $gioi_thieu = get_field('gioi_thieu', 'user_' . $teacher_id);
            
            // Dữ liệu cho template
            $data = array(
                'teacher' => $teacher,
                'ma_gv' => $ma_gv,
                'hoc_vi' => $hoc_vi,
                'khoa' => $khoa,
                'chuyen_mon' => $chuyen_mon,
                'sdt' => $sdt,
                'email_gv' => $email_gv,
                'hinh_anh' => $hinh_anh,
                'gioi_thieu' => $gioi_thieu
            );
            
            // Extract dữ liệu để sử dụng trong template
            extract($data);
            
            // Include template
            include $template_path;
        } else {
            // Hiển thị thông tin cơ bản nếu không có template
            echo '<div class="giaovien-profile">';
            echo '<h2>' . esc_html($hoc_vi . ' ' . $teacher->display_name) . '</h2>';
            
            if (!empty($ma_gv)) {
                echo '<p><strong>Mã giáo viên:</strong> ' . esc_html($ma_gv) . '</p>';
            }
            
            if (!empty($khoa)) {
                echo '<p><strong>Khoa/Bộ môn:</strong> ' . esc_html($khoa) . '</p>';
            }
            
            if (!empty($chuyen_mon)) {
                echo '<p><strong>Chuyên môn:</strong> ' . esc_html($chuyen_mon) . '</p>';
            }
            
            if (!empty($sdt)) {
                echo '<p><strong>Số điện thoại:</strong> ' . esc_html($sdt) . '</p>';
            }
            
            if (!empty($email_gv)) {
                echo '<p><strong>Email:</strong> ' . esc_html($email_gv) . '</p>';
            }
            
            if (!empty($gioi_thieu)) {
                echo '<div class="giaovien-intro">';
                echo '<h3>Giới thiệu</h3>';
                echo wpautop($gioi_thieu);
                echo '</div>';
            }
            
            echo '</div>';
        }
        
        return ob_get_clean();
    }
    
    /**
     * Shortcode hiển thị thời khóa biểu của giáo viên
     */
    public function giaovien_tkb_shortcode($atts) {
        // Tham số mặc định
        $atts = shortcode_atts(array(
            'id' => 0,     // ID của giáo viên
        ), $atts);
        
        // Kiểm tra ID
        $teacher_id = intval($atts['id']);
        if (empty($teacher_id)) {
            // Lấy từ query string nếu có
            $teacher_id = isset($_GET['gv']) ? intval($_GET['gv']) : 0;
        }
        
        // Nếu không có ID, trả về thông báo lỗi
        if (empty($teacher_id)) {
            return '<p>Không tìm thấy thông tin giáo viên.</p>';
        }
        
        // Kiểm tra nếu user không phải giáo viên
        $teacher = get_userdata($teacher_id);
        if (!$teacher || !in_array('giaovien', $teacher->roles)) {
            return '<p>Người dùng không phải là giáo viên.</p>';
        }
        
        // Sử dụng shortcode có sẵn của thời khóa biểu, chỉ lọc theo giáo viên
        return do_shortcode('[qlsv_thoikhoabieu giang_vien="' . $teacher_id . '"]');
    }
    
    /**
     * Đăng ký template tùy chỉnh
     */
    public function register_custom_templates($template) {
        // Xử lý template khi cần (nếu muốn thêm trang profile giáo viên)
        return $template;
    }
} 