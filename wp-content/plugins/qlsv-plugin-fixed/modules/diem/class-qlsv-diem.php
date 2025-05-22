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
        // Đăng ký custom post type
        $this->loader->add_action('init', $this, 'register_post_type');
        
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
        
        // Fix 404 errors
        $this->loader->add_action('pre_get_posts', $this, 'handle_diem_queries');
        
        // Kiểm tra quyền người dùng trước khi cho phép chỉnh sửa
        $this->loader->add_filter('user_has_cap', $this, 'restrict_diem_editing', 10, 3);
    }
    
    /**
     * Đăng ký các shortcodes
     */
    private function register_shortcodes() {
        add_shortcode('qlsv_bang_diem', array($this, 'bang_diem_shortcode'));
        add_shortcode('qlsv_tim_kiem_diem', array($this, 'tim_kiem_diem_shortcode'));
        add_shortcode('qlsv_nhap_diem', array($this, 'nhap_diem_shortcode'));
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
            ob_start();
            
            // Hiển thị form nhập điểm trước cho giáo viên và admin
            echo '<div class="tabs-container">';
            echo '<ul class="tabs-nav">';
            echo '<li class="tab-active"><a href="#tab-tim-kiem">Tìm kiếm điểm</a></li>';
            echo '<li><a href="#tab-nhap-diem">Nhập điểm</a></li>';
            echo '</ul>';
            
            echo '<div class="tabs-content">';
            echo '<div id="tab-tim-kiem" class="tab-content tab-active">';
            
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
            
            echo '</div>';
            
            echo '<div id="tab-nhap-diem" class="tab-content">';
            echo do_shortcode('[qlsv_nhap_diem]');
            echo '</div>';
            
            echo '</div>'; // End tabs-content
            echo '</div>'; // End tabs-container
            
            // Thêm JavaScript để quản lý tabs
            echo '<script>
                document.addEventListener("DOMContentLoaded", function() {
                    // Lấy tất cả các tab nav links
                    const tabLinks = document.querySelectorAll(".tabs-nav a");
                    
                    // Thêm event listener cho mỗi tab
                    tabLinks.forEach(function(link) {
                        link.addEventListener("click", function(e) {
                            e.preventDefault();
                            
                            // Xóa active class từ tất cả tabs
                            document.querySelectorAll(".tabs-nav li").forEach(function(li) {
                                li.classList.remove("tab-active");
                            });
                            document.querySelectorAll(".tab-content").forEach(function(content) {
                                content.classList.remove("tab-active");
                            });
                            
                            // Thêm active class cho tab được click
                            this.parentElement.classList.add("tab-active");
                            
                            // Hiển thị nội dung tương ứng
                            const targetId = this.getAttribute("href");
                            document.querySelector(targetId).classList.add("tab-active");
                        });
                    });
                });
            </script>';
            
            // Thêm CSS cho tabs
            echo '<style>
                .tabs-container {
                    margin-bottom: 30px;
                }
                .tabs-nav {
                    list-style: none;
                    padding: 0;
                    margin: 0;
                    display: flex;
                    border-bottom: 1px solid #ddd;
                }
                .tabs-nav li {
                    margin-right: 5px;
                }
                .tabs-nav a {
                    display: block;
                    padding: 10px 15px;
                    text-decoration: none;
                    background: #f5f5f5;
                    color: #333;
                    border: 1px solid #ddd;
                    border-bottom: none;
                    border-radius: 3px 3px 0 0;
                }
                .tabs-nav li.tab-active a {
                    background: #fff;
                    position: relative;
                    bottom: -1px;
                    border-bottom: 1px solid #fff;
                }
                .tab-content {
                    display: none;
                    padding-top: 20px;
                }
                .tab-content.tab-active {
                    display: block;
                }
            </style>';
            
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

    /**
     * Đăng ký post type điểm
     */
    public function register_post_type() {
        $labels = array(
            'name'               => _x('Điểm', 'post type general name', 'qlsv'),
            'singular_name'      => _x('Điểm', 'post type singular name', 'qlsv'),
            'menu_name'          => _x('Điểm', 'admin menu', 'qlsv'),
            'name_admin_bar'     => _x('Điểm', 'add new on admin bar', 'qlsv'),
            'add_new'            => _x('Thêm mới', 'diem', 'qlsv'),
            'add_new_item'       => __('Thêm điểm mới', 'qlsv'),
            'new_item'           => __('Điểm mới', 'qlsv'),
            'edit_item'          => __('Sửa điểm', 'qlsv'),
            'view_item'          => __('Xem điểm', 'qlsv'),
            'all_items'          => __('Tất cả điểm', 'qlsv'),
            'search_items'       => __('Tìm điểm', 'qlsv'),
            'not_found'          => __('Không tìm thấy điểm nào.', 'qlsv'),
            'not_found_in_trash' => __('Không có điểm nào trong thùng rác.', 'qlsv')
        );

        $args = array(
            'labels'             => $labels,
            'description'        => __('Quản lý điểm sinh viên', 'qlsv'),
            'public'             => true,
            'publicly_queryable' => true,
            'show_ui'            => true,
            'show_in_menu'       => true,
            'query_var'          => true,
            'rewrite'            => array(
                'slug' => 'diem',
                'with_front' => false,
                'pages' => true,
                'feeds' => false,
                'ep_mask' => EP_PERMALINK
            ),
            'capability_type'    => 'post',
            'has_archive'        => true,
            'hierarchical'       => false,
            'menu_position'      => null,
            'supports'           => array('title'),
            'menu_icon'          => 'dashicons-welcome-learn-more'
        );

        register_post_type('diem', $args);
        
        // Đăng ký thêm các tham số query
        $this->loader->add_filter('query_vars', $this, 'add_query_vars');
        
        // Đảm bảo flush rewrite rules
        if (get_option('qlsv_diem_flush_rewrite') != true) {
            flush_rewrite_rules();
            update_option('qlsv_diem_flush_rewrite', true);
        }
    }
    
    /**
     * Thêm các biến query để sử dụng trong URL
     */
    public function add_query_vars($vars) {
        $vars[] = 'sinhvien';
        $vars[] = 'monhoc';
        $vars[] = 'lop';
        return $vars;
    }

    /**
     * Xử lý điểm queries để ngăn lỗi 404
     */
    public function handle_diem_queries($query) {
        // Kiểm tra nếu đang xem trang điểm
        if ($query->is_main_query() && (is_post_type_archive('diem') || $query->get('post_type') === 'diem')) {
            // Thiết lập post type
            $query->set('post_type', 'diem');
            
            // Đảm bảo không phải 404
            $query->is_404 = false;
            $query->is_archive = true;
            $query->is_post_type_archive = true;
            
            // Tạo dummy post nếu cần
            if (!$query->have_posts()) {
                global $wp_query, $post;
                if (empty($post)) {
                    $dummy = new stdClass();
                    $dummy->ID = 0;
                    $dummy->post_title = 'Điểm';
                    $dummy->post_type = 'diem';
                    $dummy->post_status = 'publish';
                    $post = $dummy;
                }
            }
        }
    }
    
    /**
     * Hạn chế quyền chỉnh sửa điểm
     */
    public function restrict_diem_editing($allcaps, $caps, $args) {
        // Kiểm tra nếu không phải admin hoặc giáo viên
        if (!current_user_can('administrator') && !current_user_can('giaovien')) {
            // Loại bỏ các capability liên quan đến chỉnh sửa điểm
            $edit_caps = array(
                'edit_diem',
                'edit_diems',
                'edit_published_diems',
                'edit_private_diems',
                'edit_others_diems',
                'publish_diems',
                'delete_diems',
                'delete_published_diems',
                'delete_private_diems',
                'delete_others_diems'
            );
            
            foreach ($edit_caps as $cap) {
                if (isset($allcaps[$cap])) {
                    $allcaps[$cap] = false;
                }
            }
            
            // Loại bỏ quyền chỉnh sửa các post thuộc type 'diem'
            if (isset($args[0]) && $args[0] === 'edit_post' && isset($args[2])) {
                $post_id = $args[2];
                if (get_post_type($post_id) === 'diem') {
                    if (isset($allcaps['edit_posts'])) {
                        $allcaps['edit_posts'] = false;
                    }
                }
            }
        }
        
        return $allcaps;
    }
    
    /**
     * Shortcode tạo form nhập điểm
     */
    public function nhap_diem_shortcode($atts) {
        // Kiểm tra quyền truy cập (chỉ admin và giáo viên)
        if (!current_user_can('edit_posts') && !current_user_can('manage_options') && !current_user_can('giaovien')) {
            return '<div class="qlsv-thong-bao">
                <p>' . __('Bạn không có quyền nhập điểm. Chỉ giáo viên và quản trị viên mới thực hiện được chức năng này.', 'qlsv') . '</p>
            </div>';
        }
        
        $atts = shortcode_atts(array(), $atts);
        
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
        
        // Xử lý form submit
        $message = '';
        if (isset($_POST['submit_diem']) && isset($_POST['diem_nonce']) && wp_verify_nonce($_POST['diem_nonce'], 'submit_diem')) {
            $sinh_vien_id = isset($_POST['sinh_vien']) ? intval($_POST['sinh_vien']) : 0;
            $mon_hoc_id = isset($_POST['mon_hoc']) ? intval($_POST['mon_hoc']) : 0;
            $lop_id = isset($_POST['lop']) ? intval($_POST['lop']) : 0;
            $diem_tp1 = isset($_POST['diem_tp1']) ? floatval($_POST['diem_tp1']) : '';
            $diem_tp2 = isset($_POST['diem_tp2']) ? floatval($_POST['diem_tp2']) : '';
            $diem_cuoi_ki = isset($_POST['diem_cuoi_ki']) ? floatval($_POST['diem_cuoi_ki']) : '';
            
            if ($sinh_vien_id > 0 && $mon_hoc_id > 0) {
                // Kiểm tra xem đã có điểm cho sinh viên và môn học này chưa
                $args = array(
                    'post_type' => 'diem',
                    'posts_per_page' => 1,
                    'meta_query' => array(
                        'relation' => 'AND',
                        array(
                            'key' => 'sinh_vien',
                            'value' => $sinh_vien_id,
                            'compare' => '='
                        ),
                        array(
                            'key' => 'mon_hoc',
                            'value' => $mon_hoc_id,
                            'compare' => '='
                        )
                    )
                );
                
                $existing_grades = get_posts($args);
                $post_id = 0;
                
                if (empty($existing_grades)) {
                    // Tạo bản ghi điểm mới
                    $sinh_vien_name = get_the_title($sinh_vien_id);
                    $mon_hoc_name = get_the_title($mon_hoc_id);
                    $post_title = sprintf('Điểm - %s - %s', $sinh_vien_name, $mon_hoc_name);
                    
                    $post_id = wp_insert_post(array(
                        'post_title' => $post_title,
                        'post_status' => 'publish',
                        'post_type' => 'diem'
                    ));
                } else {
                    $post_id = $existing_grades[0]->ID;
                }
                
                if ($post_id > 0) {
                    // Cập nhật thông tin điểm
                    update_field('sinh_vien', $sinh_vien_id, $post_id);
                    update_field('mon_hoc', $mon_hoc_id, $post_id);
                    
                    if ($lop_id > 0) {
                        update_field('lop', $lop_id, $post_id);
                    }
                    
                    update_field('diem_thanh_phan_1_', $diem_tp1, $post_id);
                    update_field('diem_thanh_phan_2_', $diem_tp2, $post_id);
                    update_field('diem_cuoi_ki_', $diem_cuoi_ki, $post_id);
                    
                    // Tính điểm trung bình và xếp loại
                    $tb = 0;
                    if (is_numeric($diem_tp1) && is_numeric($diem_tp2) && is_numeric($diem_cuoi_ki)) {
                        $tb = round(($diem_tp1 * 0.2 + $diem_tp2 * 0.2 + $diem_cuoi_ki * 0.6), 2);
                        
                        // Xếp loại
                        $xeploai = '';
                        if ($tb >= 8.5) $xeploai = 'Giỏi';
                        elseif ($tb >= 7) $xeploai = 'Khá';
                        elseif ($tb >= 5.5) $xeploai = 'Trung bình';
                        else $xeploai = 'Yếu';
                        
                        // Lưu điểm trung bình và xếp loại nếu có field
                        if(function_exists('update_field')) {
                            update_field('diem_trung_binh_', $tb, $post_id);
                            update_field('xep_loai', $xeploai, $post_id);
                        }
                    }
                    
                    $message = '<div class="diem-success">Đã cập nhật điểm thành công!</div>';
                } else {
                    $message = '<div class="diem-error">Có lỗi xảy ra khi lưu điểm.</div>';
                }
            } else {
                $message = '<div class="diem-error">Vui lòng chọn sinh viên và môn học.</div>';
            }
        }
        
        // Output form nhập điểm
        ob_start();
        
        echo '<div class="nhap-diem-container">';
        
        if (!empty($message)) {
            echo $message;
        }
        
        echo '<h3>' . __('Nhập điểm sinh viên', 'qlsv') . '</h3>';
        echo '<form class="nhap-diem-form" method="post" action="' . esc_url($_SERVER['REQUEST_URI']) . '">';
        
        // Sinh viên
        echo '<div class="form-group">';
        echo '<label for="sinh_vien">' . __('Sinh viên:', 'qlsv') . '</label>';
        echo '<select name="sinh_vien" id="sinh_vien" required>';
        echo '<option value="">' . __('-- Chọn sinh viên --', 'qlsv') . '</option>';
        foreach ($students as $student) {
            $selected = (isset($_POST['sinh_vien']) && $_POST['sinh_vien'] == $student->ID) ? 'selected' : '';
            echo '<option value="' . $student->ID . '" ' . $selected . '>' . $student->post_title . '</option>';
        }
        echo '</select>';
        echo '</div>';
        
        // Môn học
        echo '<div class="form-group">';
        echo '<label for="mon_hoc">' . __('Môn học:', 'qlsv') . '</label>';
        echo '<select name="mon_hoc" id="mon_hoc" required>';
        echo '<option value="">' . __('-- Chọn môn học --', 'qlsv') . '</option>';
        foreach ($courses as $course) {
            $selected = (isset($_POST['mon_hoc']) && $_POST['mon_hoc'] == $course->ID) ? 'selected' : '';
            echo '<option value="' . $course->ID . '" ' . $selected . '>' . $course->post_title . '</option>';
        }
        echo '</select>';
        echo '</div>';
        
        // Lớp
        echo '<div class="form-group">';
        echo '<label for="lop">' . __('Lớp:', 'qlsv') . '</label>';
        echo '<select name="lop" id="lop">';
        echo '<option value="">' . __('-- Chọn lớp --', 'qlsv') . '</option>';
        foreach ($classes as $class) {
            $selected = (isset($_POST['lop']) && $_POST['lop'] == $class->ID) ? 'selected' : '';
            echo '<option value="' . $class->ID . '" ' . $selected . '>' . $class->post_title . '</option>';
        }
        echo '</select>';
        echo '</div>';
        
        // Điểm thành phần 1
        echo '<div class="form-group">';
        echo '<label for="diem_tp1">' . __('Điểm thành phần 1:', 'qlsv') . '</label>';
        $diem_tp1_value = isset($_POST['diem_tp1']) ? esc_attr($_POST['diem_tp1']) : '';
        echo '<input type="number" step="0.1" min="0" max="10" name="diem_tp1" id="diem_tp1" value="' . $diem_tp1_value . '" />';
        echo '</div>';
        
        // Điểm thành phần 2
        echo '<div class="form-group">';
        echo '<label for="diem_tp2">' . __('Điểm thành phần 2:', 'qlsv') . '</label>';
        $diem_tp2_value = isset($_POST['diem_tp2']) ? esc_attr($_POST['diem_tp2']) : '';
        echo '<input type="number" step="0.1" min="0" max="10" name="diem_tp2" id="diem_tp2" value="' . $diem_tp2_value . '" />';
        echo '</div>';
        
        // Điểm cuối kỳ
        echo '<div class="form-group">';
        echo '<label for="diem_cuoi_ki">' . __('Điểm cuối kỳ:', 'qlsv') . '</label>';
        $diem_cuoi_ki_value = isset($_POST['diem_cuoi_ki']) ? esc_attr($_POST['diem_cuoi_ki']) : '';
        echo '<input type="number" step="0.1" min="0" max="10" name="diem_cuoi_ki" id="diem_cuoi_ki" value="' . $diem_cuoi_ki_value . '" />';
        echo '</div>';
        
        // Nonce field
        wp_nonce_field('submit_diem', 'diem_nonce');
        
        // Submit button
        echo '<button type="submit" name="submit_diem" value="1" class="button button-primary">' . __('Lưu điểm', 'qlsv') . '</button>';
        
        echo '</form>';
        
        echo '<style>
            .nhap-diem-container {
                margin-bottom: 30px;
                padding: 20px;
                background: #f9f9f9;
                border-radius: 5px;
                border: 1px solid #ddd;
            }
            .nhap-diem-form .form-group {
                margin-bottom: 15px;
            }
            .nhap-diem-form label {
                display: block;
                margin-bottom: 5px;
                font-weight: bold;
            }
            .nhap-diem-form select,
            .nhap-diem-form input[type="number"] {
                width: 100%;
                max-width: 300px;
                padding: 8px;
                border: 1px solid #ddd;
                border-radius: 4px;
            }
            .nhap-diem-form button {
                background-color: #0073aa;
                color: #fff;
                border: none;
                padding: 10px 15px;
                border-radius: 4px;
                cursor: pointer;
                font-weight: bold;
            }
            .diem-success {
                padding: 10px;
                margin-bottom: 15px;
                background-color: #dff0d8;
                border: 1px solid #d6e9c6;
                border-radius: 4px;
                color: #3c763d;
            }
            .diem-error {
                padding: 10px;
                margin-bottom: 15px;
                background-color: #f2dede;
                border: 1px solid #ebccd1;
                border-radius: 4px;
                color: #a94442;
            }
        </style>';
        
        echo '</div>';
        
        return ob_get_clean();
    }
} 