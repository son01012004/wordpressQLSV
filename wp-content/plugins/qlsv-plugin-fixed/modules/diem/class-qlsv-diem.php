<?php
/**
 * Class quản lý điểm và các chức năng liên quan
 */
class QLSV_Diem {

    /**
     * Loader để đăng ký các hooks
     *
     * @var QLSV_Loader
     */
    private $loader;

    /**
     * Khởi tạo class điểm
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
        
        // Xử lý lưu dữ liệu khi lưu post
        $this->loader->add_action('acf/save_post', $this, 'luu_du_lieu_bang_diem', 20);
        
        // Thêm metabox hiển thị bảng điểm
        $this->loader->add_action('add_meta_boxes', $this, 'add_diem_meta_boxes');
        
        // Đăng ký template tùy chỉnh cho archive diem
        $this->loader->add_filter('archive_template', $this, 'register_diem_archive_template');
        
        // Đăng ký template tùy chỉnh cho single diem
        $this->loader->add_filter('single_template', $this, 'register_diem_single_template');
    }
    
    /**
     * Đăng ký các shortcodes
     */
    private function register_shortcodes() {
        add_shortcode('qlsv_bang_diem', array($this, 'bang_diem_shortcode'));
        add_shortcode('qlsv_tim_kiem_diem', array($this, 'tim_kiem_diem_shortcode'));
    }
    
    /**
     * Đăng ký ACF Fields cho bảng điểm
     */
    public function register_acf_fields() {
        if (!function_exists('acf_add_local_field_group')) {
            return;
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
    
    /**
     * Thêm metabox cho bảng điểm trong admin
     */
    public function add_diem_meta_boxes() {
        add_meta_box(
            'diem_info',
            'Thông tin điểm',
            array($this, 'render_meta_box'),
            'diem',
            'normal',
            'high'
        );
    }
    
    /**
     * Render metabox bảng điểm
     */
    public function render_meta_box($post) {
        $this->hien_thi_bang_diem_html($post);
    }
    
    /**
     * Hiển thị HTML bảng điểm
     */
    public function hien_thi_bang_diem_html($post) {
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
    
    /**
     * Lưu dữ liệu ACF khi lưu post
     */
    public function luu_du_lieu_bang_diem($post_id) {
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
            remove_action('acf/save_post', array($this, 'luu_du_lieu_bang_diem'), 20);
            
            wp_update_post([
                'ID' => $post_id,
                'post_title' => $title,
            ]);
            
            add_action('acf/save_post', array($this, 'luu_du_lieu_bang_diem'), 20);
        }
    }
    
    /**
     * Shortcode hiển thị bảng điểm
     */
    public function bang_diem_shortcode($atts) {
        // Kiểm tra quyền truy cập
        if (!is_user_logged_in()) {
            return '<div class="qlsv-thong-bao">
                <p>' . __('Bạn cần đăng nhập để xem bảng điểm.', 'qlsv') . '</p>
                <p><a href="' . esc_url(wp_login_url(get_permalink())) . '" class="button">' . __('Đăng nhập', 'qlsv') . '</a></p>
            </div>';
        }
        
        // Lấy thông tin người dùng hiện tại
        $current_user = wp_get_current_user();
        $user_roles = $current_user->roles;
        $is_admin = in_array('administrator', $user_roles);
        $is_teacher = in_array('giaovien', $user_roles);
        $is_student = in_array('student', $user_roles) || $this->is_student_by_email($current_user->user_email);
        
        // Tham số mặc định
        $atts = shortcode_atts(array(
            'sinhvien_id' => 0,     // ID sinh viên, 0 = hiển thị tất cả
            'monhoc_id' => 0,       // ID môn học, 0 = hiển thị tất cả
            'lop_id' => 0,          // ID lớp, 0 = hiển thị tất cả
            'limit' => -1,          // Số lượng kết quả, -1 = không giới hạn
        ), $atts);
        
        // Nếu là sinh viên, chỉ cho phép xem điểm của chính mình
        if ($is_student && !$is_admin && !$is_teacher) {
            // Tìm ID sinh viên dựa trên email
            $args = array(
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
            
            $student_query = new WP_Query($args);
            if ($student_query->have_posts()) {
                $student_query->the_post();
                $student_id = get_the_ID();
                wp_reset_postdata();
                
                // Ghi đè tham số, chỉ cho phép xem điểm của chính mình
                $atts['sinhvien_id'] = $student_id;
            } else {
                return '<div class="qlsv-thong-bao">
                    <p>' . __('Không tìm thấy thông tin sinh viên cho tài khoản này.', 'qlsv') . '</p>
                </div>';
            }
        }
        
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
        $diem_list = new WP_Query($args);
        
        // Load template
        ob_start();
        $template_path = QLSV_PLUGIN_DIR . 'templates/bang-diem-list.php';
        
        if (file_exists($template_path)) {
            include $template_path;
        } else {
            echo 'Template không tồn tại.';
        }
        
        return ob_get_clean();
    }
    
    /**
     * Kiểm tra xem email có phải của sinh viên không
     */
    private function is_student_by_email($email) {
        // Tìm sinh viên có email trùng với email người dùng
        $args = array(
            'post_type' => 'sinhvien',
            'posts_per_page' => 1,
            'meta_query' => array(
                array(
                    'key' => 'email',
                    'value' => $email,
                    'compare' => '='
                )
            )
        );
        
        $query = new WP_Query($args);
        return $query->have_posts();
    }
    
    /**
     * Shortcode tạo form tìm kiếm điểm
     */
    public function tim_kiem_diem_shortcode($atts) {
        // Kiểm tra quyền truy cập
        if (!is_user_logged_in()) {
            return '<div class="qlsv-thong-bao">
                <p>' . __('Bạn cần đăng nhập để tìm kiếm bảng điểm.', 'qlsv') . '</p>
                <p><a href="' . esc_url(wp_login_url(get_permalink())) . '" class="button">' . __('Đăng nhập', 'qlsv') . '</a></p>
            </div>';
        }
        
        // Lấy thông tin người dùng hiện tại
        $current_user = wp_get_current_user();
        $user_roles = $current_user->roles;
        $is_admin = in_array('administrator', $user_roles);
        $is_teacher = in_array('giaovien', $user_roles);
        $is_student = in_array('student', $user_roles) || $this->is_student_by_email($current_user->user_email);
        
        // Nếu là sinh viên, chỉ cho phép xem điểm của chính mình
        if ($is_student && !$is_admin && !$is_teacher) {
            // Tìm ID sinh viên dựa trên email
            $args = array(
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
            
            $student_query = new WP_Query($args);
            if ($student_query->have_posts()) {
                $student_query->the_post();
                $student_id = get_the_ID();
                wp_reset_postdata();
                
                // Hiển thị thông tin sinh viên và bảng điểm
                return $this->bang_diem_shortcode(array('sinhvien_id' => $student_id));
            } else {
                return '<div class="qlsv-thong-bao">
                    <p>' . __('Không tìm thấy thông tin sinh viên cho tài khoản này.', 'qlsv') . '</p>
                </div>';
            }
        }
        
        // Chỉ admin và giáo viên mới có thể sử dụng form tìm kiếm
        if ($is_admin || $is_teacher) {
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
            
            // Load template cho form tìm kiếm
            ob_start();
            $template_form_path = QLSV_PLUGIN_DIR . 'templates/tim-kiem-diem-form.php';
            
            if (file_exists($template_form_path)) {
                include $template_form_path;
            } else {
                echo 'Template form không tồn tại.';
            }
            
            // Luôn hiển thị kết quả tìm kiếm, không cần kiểm tra submit
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
            echo $this->bang_diem_shortcode($shortcode_atts);
            
            return ob_get_clean();
        } else {
            // Trường hợp khác (không phải admin, giáo viên hoặc sinh viên)
            return '<div class="qlsv-thong-bao">
                <p>' . __('Bạn không có quyền xem bảng điểm.', 'qlsv') . '</p>
            </div>';
        }
    }
    
    /**
     * Đăng ký template tùy chỉnh cho trang archive điểm
     */
    public function register_diem_archive_template($template) {
        if (is_post_type_archive('diem')) {
            $custom_template = QLSV_PLUGIN_DIR . 'templates/archive-diem.php';
            if (file_exists($custom_template)) {
                return $custom_template;
            }
        }
        return $template;
    }
    
    /**
     * Đăng ký template tùy chỉnh cho trang single điểm
     */
    public function register_diem_single_template($template) {
        if (is_singular('diem')) {
            $custom_template = QLSV_PLUGIN_DIR . 'templates/single-diem.php';
            if (file_exists($custom_template)) {
                return $custom_template;
            }
        }
        return $template;
    }
} 