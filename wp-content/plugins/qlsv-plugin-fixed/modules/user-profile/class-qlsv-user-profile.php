<?php
/**
 * Class quản lý hiển thị thông tin người dùng theo vai trò
 */
class QLSV_User_Profile {

    /**
     * Loader để đăng ký các hooks
     *
     * @var QLSV_Loader
     */
    private $loader;

    /**
     * Khởi tạo class quản lý profile người dùng
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
        // Đăng ký template tùy chỉnh
        $this->loader->add_filter('template_include', $this, 'register_custom_templates');
        
        // Thêm hook chuyển hướng người dùng
        $this->loader->add_action('template_redirect', $this, 'redirect_user_to_profile');
        
        // Xử lý form upload avatar
        $this->loader->add_action('init', $this, 'handle_avatar_upload');
        
        // Thêm scripts và styles
        $this->loader->add_action('wp_enqueue_scripts', $this, 'enqueue_scripts');
    }
    
    /**
     * Đăng ký các shortcodes
     */
    private function register_shortcodes() {
        add_shortcode('qlsv_user_profile', array($this, 'user_profile_shortcode'));
    }
    
    /**
     * Shortcode hiển thị thông tin người dùng theo vai trò
     */
    public function user_profile_shortcode($atts) {
        // Tham số mặc định
        $atts = shortcode_atts(array(
            'id' => 0,     // ID của người dùng (nếu cần hiển thị người dùng cụ thể)
        ), $atts);
        
        // Kiểm tra người dùng đã đăng nhập chưa
        if (!is_user_logged_in()) {
            return '<div class="qlsv-thong-bao">
                <p>' . __('Bạn cần đăng nhập để xem thông tin cá nhân.', 'qlsv') . '</p>
                <p><a href="' . esc_url(wp_login_url(get_permalink())) . '" class="button">' . __('Đăng nhập', 'qlsv') . '</a></p>
            </div>';
        }
        
        // Lấy thông tin người dùng
        $user_id = intval($atts['id']);
        if (empty($user_id)) {
            // Nếu không có ID được chỉ định, sử dụng ID người dùng hiện tại
            $user_id = get_current_user_id();
        } else if ($user_id != get_current_user_id() && !current_user_can('edit_users')) {
            // Nếu người dùng đang cố xem thông tin của người khác mà không có quyền
            return '<div class="qlsv-thong-bao">
                <p>' . __('Bạn không có quyền xem thông tin của người dùng khác.', 'qlsv') . '</p>
            </div>';
        }
        
        // Lấy đối tượng user
        $user = get_userdata($user_id);
        if (!$user) {
            return '<div class="qlsv-thong-bao">
                <p>' . __('Không tìm thấy thông tin người dùng.', 'qlsv') . '</p>
            </div>';
        }
        
        // Kiểm tra vai trò người dùng và hiển thị thông tin tương ứng
        ob_start();
        
        if (in_array('giaovien', $user->roles)) {
            // Hiển thị thông tin giáo viên
            return $this->display_teacher_info($user);
        } else if (in_array('student', $user->roles) || $this->is_student_by_email($user->user_email)) {
            // Hiển thị thông tin sinh viên
            return $this->display_student_info($user);
        } else {
            // Người dùng có vai trò khác (admin, editor, etc.)
            return $this->display_default_user_info($user);
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
     * Hiển thị thông tin giáo viên
     */
    public function display_teacher_info($user) {
        // Kiểm tra ACF
        if (!function_exists('get_field')) {
            return '<div class="qlsv-thong-bao">
                <p>' . __('Plugin Advanced Custom Fields chưa được kích hoạt.', 'qlsv') . '</p>
            </div>';
        }
        
        // Lấy thông tin giáo viên từ ACF
        $ma_gv = get_field('ma_giaovien', 'user_' . $user->ID);
        $hoc_vi = get_field('hoc_vi', 'user_' . $user->ID);
        $khoa = get_field('khoa', 'user_' . $user->ID);
        $chuyen_mon = get_field('chuyen_mon', 'user_' . $user->ID);
        $sdt = get_field('so_dien_thoai', 'user_' . $user->ID);
        $email_gv = get_field('email_gv', 'user_' . $user->ID);
        $hinh_anh = get_field('hinh_anh', 'user_' . $user->ID);
        $gioi_thieu = get_field('gioi_thieu', 'user_' . $user->ID);
        
        // Thêm tham số ngẫu nhiên để tránh cache khi cập nhật avatar
        $cache_bust = isset($_GET['avatar_updated']) ? '?v=' . time() : '';
        
        // Hiển thị template giáo viên
        $template_path = QLSV_PLUGIN_DIR . 'templates/giaovien-profile.php';
        
        if (file_exists($template_path)) {
            // Dữ liệu cho template
            $teacher = $user;
            
            // Trích xuất các biến để sử dụng trong template
            extract(compact('teacher', 'ma_gv', 'hoc_vi', 'khoa', 'chuyen_mon', 'sdt', 'email_gv', 'hinh_anh', 'gioi_thieu', 'cache_bust'));
            
            ob_start();
            include $template_path;
            $output = ob_get_clean();
            
            // Thêm form upload avatar nếu là người dùng hiện tại
            if ($user->ID == get_current_user_id()) {
                // CSS cho thông báo
                $notice_css = '<style>
                    .qlsv-notice {
                        padding: 15px;
                        margin: 15px 0;
                        border-left: 4px solid #ddd;
                        background: #f8f8f8;
                    }
                    .qlsv-notice-success {
                        border-left-color: #46b450;
                        background: #ecf7ed;
                    }
                </style>';
                
                // Form upload avatar (sẽ được ẩn ban đầu và hiển thị khi click vào avatar)
                $avatar_form = $this->get_teacher_avatar_form($user, $hinh_anh);
                
                // Thông báo upload thành công
                $success_notice = '';
                if (isset($_GET['avatar_updated']) && $_GET['avatar_updated'] == '1') {
                    $success_notice = '<div class="qlsv-notice qlsv-notice-success">
                        <p>' . __('Cập nhật ảnh đại diện thành công!', 'qlsv') . '</p>
                    </div>';
                    
                    // Thêm JavaScript để reload trang sau 2 giây
                    $success_notice .= '<script>
                        if (window.location.href.indexOf("avatar_updated=1") > -1) {
                            setTimeout(function() {
                                window.location.href = window.location.href.split("?")[0];
                            }, 2000);
                        }
                    </script>';
                }
                
                // Chèn form vào output (vị trí sau thẻ h2)
                $output = preg_replace('/<\/h2>/', '</h2>' . $success_notice, $output);
                
                // Chèn form sau phần hiển thị avatar
                $output = preg_replace('/(<div class="giaovien-profile-avatar".*?<\/div>)/s', '$1' . $avatar_form, $output);
                
                // Thêm CSS
                $output = $notice_css . $output;
            }
            
            return $output;
        } else {
            return '<div class="qlsv-thong-bao">
                <p>' . __('Không tìm thấy template hiển thị thông tin giáo viên.', 'qlsv') . '</p>
            </div>';
        }
    }
    
    /**
     * Hiển thị thông tin sinh viên
     */
    public function display_student_info($user) {
        // Tìm sinh viên có email trùng với email người dùng
        $args = array(
            'post_type' => 'sinhvien',
            'posts_per_page' => 1,
            'meta_query' => array(
                array(
                    'key' => 'email',
                    'value' => $user->user_email,
                    'compare' => '='
                )
            )
        );
        
        $query = new WP_Query($args);
        
        // Nếu không tìm thấy sinh viên
        if (!$query->have_posts()) {
            return '<div class="qlsv-thong-bao">
                <p>' . __('Không tìm thấy thông tin sinh viên cho tài khoản này.', 'qlsv') . '</p>
            </div>';
        }
        
        // Lấy thông tin sinh viên
        $query->the_post();
        $post_id = get_the_ID();
        
        // Kiểm tra ACF
        if (!function_exists('get_field')) {
            wp_reset_postdata();
            return '<div class="qlsv-thong-bao">
                <p>' . __('Plugin Advanced Custom Fields chưa được kích hoạt.', 'qlsv') . '</p>
            </div>';
        }
        
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
        
        // Thêm tham số ngẫu nhiên để tránh cache khi cập nhật avatar
        $cache_bust = isset($_GET['avatar_updated']) ? '?v=' . time() : '';
        
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
            if ($anh_url) {
                $anh_url = $anh_url . $cache_bust;
                $anh_html = '<div class="sinh-vien-anh"><img src="' . esc_url($anh_url) . '" alt="' . esc_attr($ho_ten) . '"></div>';
            }
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
        
        // Form upload avatar (chỉ hiển thị cho người dùng đang xem thông tin của chính họ)
        if ($user->ID == get_current_user_id()) {
            // Đặt form upload avatar ở đây nhưng nó sẽ ẩn đi ban đầu (CSS display: none được thiết lập trong enqueue_scripts)
            $output .= $this->get_student_avatar_form($user, $post_id, $anh_id);
            
            // Hiển thị thông báo khi upload thành công
            if (isset($_GET['avatar_updated']) && $_GET['avatar_updated'] == '1') {
                $output .= '<div class="qlsv-notice qlsv-notice-success">
                    <p>' . __('Cập nhật ảnh đại diện thành công!', 'qlsv') . '</p>
                </div>';
                
                // Thêm JavaScript để reload trang sau 2 giây để cập nhật ảnh đại diện
                $output .= '<script>
                    if (window.location.href.indexOf("avatar_updated=1") > -1) {
                        setTimeout(function() {
                            window.location.href = window.location.href.split("?")[0];
                        }, 2000);
                    }
                </script>';
            }
        }
        
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
        $output .= '<a href="' . esc_url(add_query_arg('sinhvien', $post_id, home_url('/wordpressQLSV/diem/'))) . '" class="button button-primary">Xem bảng điểm</a>';
        $output .= '</div>';
        
        $output .= '</div>';
        
        // Thêm CSS
        $output .= $this->get_student_css();
        
        wp_reset_postdata();
        return $output;
    }
    
    /**
     * Hiển thị thông tin người dùng mặc định
     */
    public function display_default_user_info($user) {
        $avatar_id = get_user_meta($user->ID, 'qlsv_user_avatar', true);
        $cache_bust = isset($_GET['avatar_updated']) ? '?v=' . time() : '';
        
        $output = '<div class="qlsv-user-profile">';
        $output .= '<h2>' . __('Thông tin người dùng', 'qlsv') . '</h2>';
        
        // Hiển thị thông báo khi upload thành công
        if (isset($_GET['avatar_updated']) && $_GET['avatar_updated'] == '1') {
            $output .= '<div class="qlsv-notice qlsv-notice-success">
                <p>' . __('Cập nhật ảnh đại diện thành công!', 'qlsv') . '</p>
            </div>';
            
            // Thêm JavaScript để reload trang sau 2 giây để cập nhật ảnh đại diện
            $output .= '<script>
                if (window.location.href.indexOf("avatar_updated=1") > -1) {
                    setTimeout(function() {
                        window.location.href = window.location.href.split("?")[0];
                    }, 2000);
                }
            </script>';
        }
        
        $output .= '<div class="qlsv-user-info">';
        $output .= '<div class="qlsv-user-avatar">';
        
        if ($avatar_id) {
            $img_url = wp_get_attachment_image_url($avatar_id, 'thumbnail');
            if ($img_url) {
                $output .= '<img src="' . esc_url($img_url . $cache_bust) . '" alt="Avatar" class="avatar">';
            } else {
                $output .= get_avatar($user->ID, 100);
            }
        } else {
            $output .= get_avatar($user->ID, 100);
        }
        
        $output .= '</div>';
        
        // Form upload avatar (chỉ hiển thị cho người dùng đang xem thông tin của chính họ)
        if ($user->ID == get_current_user_id()) {
            $output .= $this->get_default_user_avatar_form($user);
        }
        
        $output .= '<table class="qlsv-user-table">';
        
        $output .= '<tr>';
        $output .= '<th>' . __('Tên:', 'qlsv') . '</th>';
        $output .= '<td>' . esc_html($user->display_name) . '</td>';
        $output .= '</tr>';
        
        $output .= '<tr>';
        $output .= '<th>' . __('Email:', 'qlsv') . '</th>';
        $output .= '<td>' . esc_html($user->user_email) . '</td>';
        $output .= '</tr>';
        
        $output .= '<tr>';
        $output .= '<th>' . __('Vai trò:', 'qlsv') . '</th>';
        $output .= '<td>' . esc_html($this->get_user_role_display($user)) . '</td>';
        $output .= '</tr>';
        
        $output .= '</table>';
        $output .= '</div>';
        $output .= '</div>';
        
        // Thêm CSS cho thông báo và avatar upload
        $output .= '<style>
            .qlsv-notice {
                padding: 15px;
                margin: 15px 0;
                border-left: 4px solid #ddd;
                background: #f8f8f8;
            }
            .qlsv-notice-success {
                border-left-color: #46b450;
                background: #ecf7ed;
            }
            .qlsv-user-avatar {
                margin-bottom: 20px;
                text-align: center;
            }
            .qlsv-user-avatar img.avatar {
                border-radius: 50%;
                width: 100px;
                height: 100px;
                object-fit: cover;
                display: inline-block;
            }
        </style>';
        
        // Thêm CSS
        $output .= $this->get_default_user_css();
        
        return $output;
    }
    
    /**
     * Lấy tên hiển thị của vai trò người dùng
     */
    private function get_user_role_display($user) {
        $role_names = array(
            'administrator' => __('Quản trị viên', 'qlsv'),
            'editor' => __('Biên tập viên', 'qlsv'),
            'author' => __('Tác giả', 'qlsv'),
            'contributor' => __('Cộng tác viên', 'qlsv'),
            'subscriber' => __('Thành viên', 'qlsv'),
            'giaovien' => __('Giáo viên', 'qlsv'),
            'student' => __('Sinh viên', 'qlsv')
        );
        
        foreach ($user->roles as $role) {
            if (isset($role_names[$role])) {
                return $role_names[$role];
            }
        }
        
        return __('Người dùng', 'qlsv');
    }
    
    /**
     * CSS cho hiển thị thông tin sinh viên
     */
    private function get_student_css() {
        return '<style>
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
                color: #777;
            }
            .sinh-vien-anh.placeholder i {
                font-size: 40px;
            }
            .sinh-vien-ten {
                margin: 0;
                font-size: 24px;
                flex-grow: 1;
            }
            .sinh-vien-trang-thai {
                background: #e8f5e9;
                color: #2e7d32;
                padding: 5px 10px;
                border-radius: 4px;
                font-size: 14px;
                margin-top: 5px;
            }
            .sinh-vien-info {
                margin-bottom: 20px;
            }
            .sinh-vien-table {
                width: 100%;
                border-collapse: collapse;
            }
            .sinh-vien-table th,
            .sinh-vien-table td {
                padding: 12px 15px;
                border-bottom: 1px solid #eee;
                text-align: left;
            }
            .sinh-vien-table th {
                width: 30%;
                color: #666;
                font-weight: 600;
                vertical-align: top;
            }
            .sinh-vien-actions {
                margin-top: 20px;
                display: flex;
                justify-content: flex-start;
            }
            .sinh-vien-actions .button {
                padding: 8px 20px;
                text-decoration: none;
            }
            
            /* CSS cho form upload avatar */
            .avatar-upload-container {
                margin: 20px 0;
                background: #f9f9f9;
                padding: 20px;
                border-radius: 8px;
                text-align: center;
            }
            .avatar-upload-container h3 {
                margin-top: 0;
                margin-bottom: 15px;
                color: #333;
                font-size: 18px;
            }
            .avatar-preview {
                width: 150px;
                height: 150px;
                border-radius: 50%;
                overflow: hidden;
                margin: 0 auto 15px;
                position: relative;
                border: 3px solid #f0f0f0;
                background: #fff;
            }
            .avatar-preview img {
                width: 100%;
                height: 100%;
                object-fit: cover;
            }
            .avatar-preview .no-avatar {
                width: 100%;
                height: 100%;
                display: flex;
                align-items: center;
                justify-content: center;
                background: #f5f5f5;
                color: #777;
                font-size: 40px;
            }
            .avatar-actions {
                display: flex;
                flex-direction: column;
                align-items: center;
                gap: 15px;
            }
            .avatar-actions input[type="file"] {
                width: 100%;
                max-width: 250px;
            }
            .avatar-actions .button {
                padding: 8px 20px;
            }
            
            /* Thông báo */
            .qlsv-notice {
                padding: 15px;
                margin: 15px 0;
                border-left: 4px solid #ddd;
                background: #f8f8f8;
            }
            .qlsv-notice-success {
                border-left-color: #46b450;
                background: #ecf7ed;
            }
            
            @media (max-width: 768px) {
                .sinh-vien-header {
                    flex-direction: column;
                    align-items: flex-start;
                }
                .sinh-vien-anh {
                    margin-bottom: 15px;
                }
                .sinh-vien-table th {
                    width: 40%;
                }
            }
        </style>';
    }
    
    /**
     * CSS cho hiển thị thông tin người dùng mặc định
     */
    private function get_default_user_css() {
        return '<style>
            .qlsv-user-profile {
                max-width: 800px;
                margin: 0 auto;
                background: #fff;
                border-radius: 8px;
                box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
                padding: 20px;
            }
            .qlsv-user-profile h2 {
                margin-top: 0;
                border-bottom: 1px solid #eee;
                padding-bottom: 10px;
                margin-bottom: 20px;
            }
            .qlsv-user-info {
                display: flex;
                flex-wrap: wrap;
            }
            .qlsv-user-avatar {
                margin-right: 20px;
                margin-bottom: 20px;
            }
            .qlsv-user-avatar img {
                border-radius: 50%;
            }
            .qlsv-user-table {
                flex-grow: 1;
                width: 100%;
                border-collapse: collapse;
            }
            .qlsv-user-table th,
            .qlsv-user-table td {
                padding: 12px 15px;
                border-bottom: 1px solid #eee;
                text-align: left;
            }
            .qlsv-user-table th {
                width: 30%;
                color: #666;
                font-weight: 600;
                vertical-align: top;
            }
            .qlsv-thong-bao {
                background: #f8f8f8;
                border-left: 4px solid #ccc;
                padding: 12px 15px;
                margin: 20px 0;
            }
            .qlsv-thong-bao p {
                margin: 0 0 10px;
            }
            .qlsv-thong-bao p:last-child {
                margin-bottom: 0;
            }
        </style>';
    }
    
    /**
     * Đăng ký template tùy chỉnh
     */
    public function register_custom_templates($template) {
        // Kiểm tra nếu đang xem trang sinh viên
        if (is_singular('sinhvien') || is_post_type_archive('sinhvien')) {
            // Nếu người dùng đã đăng nhập, sử dụng template thông tin người dùng
            if (is_user_logged_in()) {
                $custom_template = QLSV_PLUGIN_DIR . 'templates/user-profile.php';
                if (file_exists($custom_template)) {
                    return $custom_template;
                }
            }
        }
        
        return $template;
    }
    
    /**
     * Chuyển hướng người dùng đến trang thông tin cá nhân
     */
    public function redirect_user_to_profile() {
        // Kiểm tra nếu đang ở trang sinh viên
        if ((is_post_type_archive('sinhvien') || is_singular('sinhvien')) && is_user_logged_in()) {
            // Xử lý trường hợp đặc biệt khi đã có param sinhvien trên URL (dùng cho tra cứu điểm)
            if (isset($_GET['sinhvien'])) {
                return; // Không chuyển hướng nếu đang xem thông tin điểm của sinh viên cụ thể
            }
            
            // Sử dụng template đã được sửa đổi trong hàm register_custom_templates
        }
    }

    /**
     * Đăng ký scripts và styles
     */
    public function enqueue_scripts() {
        wp_enqueue_media();
        wp_enqueue_script('jquery');
        
        // CSS cho avatar upload
        wp_add_inline_style('dashicons', '
            .avatar-upload-container {
                margin: 20px 0;
                text-align: center;
                display: none;
                opacity: 0;
                height: 0;
                transition: opacity 0.3s, height 0.3s;
            }
            
            .avatar-upload-container.visible {
                opacity: 1;
                height: auto;
            }
            
            .avatar-preview {
                width: 150px;
                height: 150px;
                border-radius: 50%;
                overflow: hidden;
                margin: 0 auto 15px;
                position: relative;
                border: 3px solid #f0f0f0;
                cursor: pointer;
            }
            
            .avatar-preview img {
                width: 100%;
                height: 100%;
                object-fit: cover;
            }
            
            .avatar-preview::after {
                content: "\\f332"; /* camera icon */
                font-family: dashicons;
                position: absolute;
                bottom: 0;
                right: 0;
                background: rgba(0, 115, 170, 0.8);
                color: white;
                width: 30px;
                height: 30px;
                border-radius: 50%;
                display: flex;
                align-items: center;
                justify-content: center;
                font-size: 18px;
                opacity: 0;
                transition: opacity 0.3s;
            }
            
            .avatar-preview:hover::after {
                opacity: 1;
            }
            
            .avatar-actions {
                display: flex;
                flex-direction: column;
                align-items: center;
                gap: 10px;
                padding: 10px 0;
            }
            
            .sinh-vien-anh, .giaovien-profile-avatar {
                cursor: pointer;
                position: relative;
            }
            
            .sinh-vien-anh::after, .giaovien-profile-avatar::after {
                content: "\\f332"; /* camera icon */
                font-family: dashicons;
                position: absolute;
                bottom: 0;
                right: 0;
                background: rgba(0, 115, 170, 0.8);
                color: white;
                width: 30px;
                height: 30px;
                border-radius: 50%;
                display: flex;
                align-items: center;
                justify-content: center;
                font-size: 18px;
                opacity: 0;
                transition: opacity 0.3s;
            }
            
            .sinh-vien-anh:hover::after, .giaovien-profile-avatar:hover::after {
                opacity: 1;
            }
            
            .qlsv-user-avatar {
                cursor: pointer;
                position: relative;
            }
            
            .qlsv-user-avatar::after {
                content: "\\f332"; /* camera icon */
                font-family: dashicons;
                position: absolute;
                bottom: 0;
                right: 0;
                background: rgba(0, 115, 170, 0.8);
                color: white;
                width: 30px;
                height: 30px;
                border-radius: 50%;
                display: flex;
                align-items: center;
                justify-content: center;
                font-size: 18px;
                opacity: 0;
                transition: opacity 0.3s;
            }
            
            .qlsv-user-avatar:hover::after {
                opacity: 1;
            }
            
            .qlsv-notice {
                padding: 15px;
                margin: 15px 0;
                border-left: 4px solid #ddd;
                background: #f8f8f8;
            }
            
            .qlsv-notice-success {
                border-left-color: #46b450;
                background: #ecf7ed;
            }
        ');
        
        // JavaScript cho avatar preview
        wp_add_inline_script('jquery', '
            jQuery(document).ready(function($) {
                // Toggle form visibility when clicking on avatar
                $(".sinh-vien-anh, .giaovien-profile-avatar, .qlsv-user-avatar, .avatar-preview").on("click", function() {
                    var container = $(".avatar-upload-container");
                    
                    // Toggle the form with a slide animation
                    if(container.is(":visible")) {
                        container.slideUp(300, function() {
                            container.removeClass("visible");
                        });
                    } else {
                        container.slideDown(300, function() {
                            container.addClass("visible");
                            
                            // Scroll to form
                            $("html, body").animate({
                                scrollTop: container.offset().top - 100
                            }, 500);
                        });
                    }
                });
                
                // Preview avatar before upload
                $("input#qlsv_avatar").on("change", function() {
                    var input = this;
                    var preview = $(this).closest("form").siblings(".avatar-preview");
                    
                    if (!preview.length) {
                        preview = $(".avatar-preview");
                    }
                    
                    if (input.files && input.files[0]) {
                        var reader = new FileReader();
                        
                        reader.onload = function(e) {
                            var img = preview.find("img");
                            
                            if (img.length) {
                                img.attr("src", e.target.result);
                            } else {
                                preview.html("<img src=\"" + e.target.result + "\" alt=\"Avatar Preview\">");
                            }
                            
                            // Also update the main avatar if exists
                            var mainAvatar = $(".sinh-vien-anh img, .giaovien-profile-avatar img, .qlsv-user-avatar img");
                            
                            if (mainAvatar.length) {
                                mainAvatar.attr("src", e.target.result);
                                
                                // Add a subtle animation to show the change
                                mainAvatar.css("opacity", "0.7").animate({opacity: 1}, 500);
                            }
                        };
                        
                        reader.readAsDataURL(input.files[0]);
                    }
                });
                
                // Show the form automatically when avatar_updated param is present
                if (window.location.href.indexOf("avatar_updated=1") > -1) {
                    var avatarImgs = $(".sinh-vien-anh img, .giaovien-profile-avatar img, .qlsv-user-avatar img, .avatar-preview img");
                    
                    avatarImgs.each(function() {
                        var img = $(this);
                        var currentSrc = img.attr("src");
                        
                        // Add timestamp to bypass cache
                        if (currentSrc.indexOf("?") > -1) {
                            img.attr("src", currentSrc.split("?")[0] + "?v=" + new Date().getTime());
                        } else {
                            img.attr("src", currentSrc + "?v=" + new Date().getTime());
                        }
                    });
                }
            });
        ');
    }

    /**
     * Xử lý việc upload avatar
     */
    public function handle_avatar_upload() {
        if (!isset($_POST['qlsv_avatar_upload_nonce']) || !wp_verify_nonce($_POST['qlsv_avatar_upload_nonce'], 'qlsv_avatar_upload')) {
            return;
        }

        if (!is_user_logged_in()) {
            return;
        }

        // Kiểm tra xem có file được tải lên không
        if (!isset($_FILES['qlsv_avatar']) || $_FILES['qlsv_avatar']['error'] == UPLOAD_ERR_NO_FILE) {
            return;
        }

        $user_id = get_current_user_id();
        
        // Kiểm tra lỗi upload
        if ($_FILES['qlsv_avatar']['error'] !== UPLOAD_ERR_OK) {
            add_action('admin_notices', function() {
                echo '<div class="notice notice-error is-dismissible"><p>' . __('Có lỗi khi tải lên avatar. Vui lòng thử lại.', 'qlsv') . '</p></div>';
            });
            return;
        }

        // Kiểm tra loại file
        $allowed_types = array('image/jpeg', 'image/png', 'image/gif');
        if (!in_array($_FILES['qlsv_avatar']['type'], $allowed_types)) {
            add_action('admin_notices', function() {
                echo '<div class="notice notice-error is-dismissible"><p>' . __('Chỉ chấp nhận file ảnh (JPG, PNG, GIF).', 'qlsv') . '</p></div>';
            });
            return;
        }

        // Upload file vào thư viện media
        require_once(ABSPATH . 'wp-admin/includes/image.php');
        require_once(ABSPATH . 'wp-admin/includes/file.php');
        require_once(ABSPATH . 'wp-admin/includes/media.php');

        $attachment_id = media_handle_upload('qlsv_avatar', 0);

        if (is_wp_error($attachment_id)) {
            add_action('admin_notices', function() use ($attachment_id) {
                echo '<div class="notice notice-error is-dismissible"><p>' . $attachment_id->get_error_message() . '</p></div>';
            });
            return;
        }

        // Xác định vai trò người dùng và cập nhật avatar tương ứng
        $user = get_userdata($user_id);
        
        if (in_array('giaovien', $user->roles)) {
            // Cập nhật avatar cho giáo viên
            update_field('hinh_anh', $attachment_id, 'user_' . $user_id);
        } else if (in_array('student', $user->roles) || $this->is_student_by_email($user->user_email)) {
            // Tìm sinh viên có email trùng với email người dùng
            $args = array(
                'post_type' => 'sinhvien',
                'posts_per_page' => 1,
                'meta_query' => array(
                    array(
                        'key' => 'email',
                        'value' => $user->user_email,
                        'compare' => '='
                    )
                )
            );
            
            $query = new WP_Query($args);
            
            if ($query->have_posts()) {
                $query->the_post();
                $post_id = get_the_ID();
                update_field('anh', $attachment_id, $post_id);
                wp_reset_postdata();
            }
        } else {
            // Đối với người dùng thông thường, cập nhật avatar WordPress
            update_user_meta($user_id, 'qlsv_user_avatar', $attachment_id);
        }

        // Chuyển hướng lại trang thông tin cá nhân
        wp_redirect(add_query_arg('avatar_updated', '1', wp_get_referer()));
        exit;
    }

    /**
     * Form upload avatar cho sinh viên
     */
    private function get_student_avatar_form($user, $student_id, $anh_id) {
        // Đảm bảo có dashicons
        wp_enqueue_style('dashicons');
        
        // Thêm tham số ngẫu nhiên để tránh cache
        $cache_bust = isset($_GET['avatar_updated']) ? '?v=' . time() : '';
        
        ob_start();
        ?>
        <div class="avatar-upload-container">
            <h3><?php esc_html_e('Cập nhật ảnh đại diện', 'qlsv'); ?></h3>
            <div class="avatar-preview">
                <?php 
                if ($anh_id) {
                    // Thêm timestamp để tránh cache
                    $img_url = wp_get_attachment_image_url($anh_id, 'thumbnail');
                    if ($img_url) {
                        echo '<img src="' . esc_url($img_url . $cache_bust) . '" alt="Avatar">';
                    } else {
                        echo '<div class="no-avatar"><i class="dashicons dashicons-admin-users"></i></div>';
                    }
                } else {
                    echo '<div class="no-avatar"><i class="dashicons dashicons-admin-users"></i></div>';
                }
                ?>
            </div>
            <form method="post" enctype="multipart/form-data" class="avatar-form">
                <?php wp_nonce_field('qlsv_avatar_upload', 'qlsv_avatar_upload_nonce'); ?>
                <div class="avatar-actions">
                    <div class="file-input-wrapper">
                        <input type="file" name="qlsv_avatar" id="qlsv_avatar" accept="image/*" />
                        <label for="qlsv_avatar" class="button"><?php esc_html_e('Chọn ảnh', 'qlsv'); ?></label>
                    </div>
                    <button type="submit" class="button button-primary"><?php esc_html_e('Cập nhật', 'qlsv'); ?></button>
                </div>
            </form>
            <style>
                .file-input-wrapper {
                    position: relative;
                    overflow: hidden;
                    display: inline-block;
                    margin-bottom: 10px;
                }
                .file-input-wrapper input[type="file"] {
                    position: absolute;
                    left: 0;
                    top: 0;
                    opacity: 0;
                    width: 0;
                    height: 0;
                }
                .file-input-wrapper label {
                    display: inline-block;
                    padding: 6px 12px;
                    cursor: pointer;
                }
                .no-avatar {
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    width: 100%;
                    height: 100%;
                    background-color: #f0f0f0;
                }
                .no-avatar .dashicons {
                    font-size: 50px;
                    color: #999;
                }
                .avatar-upload-container h3 {
                    margin-bottom: 15px;
                }
            </style>
            <script>
            // Script để hiển thị ảnh ngay sau khi upload
            document.addEventListener('DOMContentLoaded', function() {
                const form = document.querySelector('.avatar-form');
                const fileInput = document.getElementById('qlsv_avatar');
                const fileLabel = document.querySelector('.file-input-wrapper label');
                
                // Cập nhật tên file được chọn
                if (fileInput && fileLabel) {
                    fileInput.addEventListener('change', function() {
                        if (this.files.length > 0) {
                            const fileName = this.files[0].name;
                            fileLabel.textContent = fileName.length > 20 ? fileName.substring(0, 17) + '...' : fileName;
                        } else {
                            fileLabel.textContent = '<?php esc_html_e('Chọn ảnh', 'qlsv'); ?>';
                        }
                    });
                }
                
                if (form) {
                    form.addEventListener('submit', function() {
                        const fileInput = this.querySelector('input[type="file"]');
                        if (fileInput.files.length > 0) {
                            localStorage.setItem('avatar_updated', '1');
                        }
                    });
                }
                
                // Hiển thị ảnh mới khi reload
                if (window.location.href.indexOf("avatar_updated=1") > -1) {
                    const avatarImgs = document.querySelectorAll('.sinh-vien-anh img, .avatar-preview img');
                    avatarImgs.forEach(function(img) {
                        const currentSrc = img.getAttribute('src');
                        if (currentSrc.indexOf('?') > -1) {
                            img.setAttribute('src', currentSrc.split('?')[0] + '?v=' + new Date().getTime());
                        } else {
                            img.setAttribute('src', currentSrc + '?v=' + new Date().getTime());
                        }
                    });
                }
            });
            </script>
        </div>
        <?php
        return ob_get_clean();
    }

    /**
     * Form upload avatar cho giáo viên
     */
    private function get_teacher_avatar_form($user, $hinh_anh) {
        // Đảm bảo có dashicons
        wp_enqueue_style('dashicons');
        
        // Thêm tham số ngẫu nhiên để tránh cache
        $cache_bust = isset($_GET['avatar_updated']) ? '?v=' . time() : '';
        
        ob_start();
        ?>
        <div class="avatar-upload-container">
            <h3><?php esc_html_e('Cập nhật ảnh đại diện', 'qlsv'); ?></h3>
            <div class="avatar-preview">
                <?php 
                if ($hinh_anh) {
                    $img_url = wp_get_attachment_image_url($hinh_anh, 'thumbnail');
                    if ($img_url) {
                        $img_url = $img_url . $cache_bust;
                        echo '<img src="' . esc_url($img_url) . '" alt="Avatar">';
                    } else {
                        echo '<div class="no-avatar"><i class="dashicons dashicons-admin-users"></i></div>';
                    }
                } else {
                    echo '<div class="no-avatar"><i class="dashicons dashicons-admin-users"></i></div>';
                }
                ?>
            </div>
            <form method="post" enctype="multipart/form-data" class="avatar-form">
                <?php wp_nonce_field('qlsv_avatar_upload', 'qlsv_avatar_upload_nonce'); ?>
                <div class="avatar-actions">
                    <div class="file-input-wrapper">
                        <input type="file" name="qlsv_avatar" id="qlsv_avatar_teacher" accept="image/*" />
                        <label for="qlsv_avatar_teacher" class="button"><?php esc_html_e('Chọn ảnh', 'qlsv'); ?></label>
                    </div>
                    <button type="submit" class="button button-primary"><?php esc_html_e('Cập nhật', 'qlsv'); ?></button>
                </div>
            </form>
            <script>
            // Script để hiển thị ảnh ngay sau khi chọn
            document.addEventListener('DOMContentLoaded', function() {
                const form = document.querySelector('.avatar-form');
                const fileInput = document.getElementById('qlsv_avatar_teacher');
                const fileLabel = fileInput.nextElementSibling;
                
                // Cập nhật tên file được chọn
                if (fileInput && fileLabel) {
                    fileInput.addEventListener('change', function() {
                        if (this.files.length > 0) {
                            const fileName = this.files[0].name;
                            fileLabel.textContent = fileName.length > 20 ? fileName.substring(0, 17) + '...' : fileName;
                            
                            // Preview ảnh
                            const reader = new FileReader();
                            reader.onload = function(e) {
                                const previewImg = document.querySelector('.avatar-preview img');
                                const mainImg = document.querySelector('.giaovien-profile-avatar img');
                                
                                if (previewImg) {
                                    previewImg.src = e.target.result;
                                }
                                
                                if (mainImg) {
                                    mainImg.src = e.target.result;
                                    mainImg.style.opacity = '0.7';
                                    setTimeout(() => {
                                        mainImg.style.opacity = '1';
                                    }, 500);
                                }
                            };
                            reader.readAsDataURL(this.files[0]);
                        } else {
                            fileLabel.textContent = '<?php esc_html_e('Chọn ảnh', 'qlsv'); ?>';
                        }
                    });
                }
            });
            </script>
        </div>
        <?php
        return ob_get_clean();
    }

    /**
     * Form upload avatar cho người dùng thông thường
     */
    private function get_default_user_avatar_form($user) {
        // Đảm bảo có dashicons
        wp_enqueue_style('dashicons');
        
        $avatar_id = get_user_meta($user->ID, 'qlsv_user_avatar', true);
        $cache_bust = isset($_GET['avatar_updated']) ? '?v=' . time() : '';
        
        ob_start();
        ?>
        <div class="avatar-upload-container">
            <h3><?php esc_html_e('Cập nhật ảnh đại diện', 'qlsv'); ?></h3>
            <div class="avatar-preview">
                <?php 
                if ($avatar_id) {
                    $img_url = wp_get_attachment_image_url($avatar_id, 'thumbnail');
                    if ($img_url) {
                        echo '<img src="' . esc_url($img_url . $cache_bust) . '" alt="Avatar">';
                    } else {
                        echo '<div class="no-avatar">' . get_avatar($user->ID, 150) . '</div>';
                    }
                } else {
                    echo '<div class="no-avatar">' . get_avatar($user->ID, 150) . '</div>';
                }
                ?>
            </div>
            <form method="post" enctype="multipart/form-data" class="avatar-form">
                <?php wp_nonce_field('qlsv_avatar_upload', 'qlsv_avatar_upload_nonce'); ?>
                <div class="avatar-actions">
                    <div class="file-input-wrapper">
                        <input type="file" name="qlsv_avatar" id="qlsv_avatar_default" accept="image/*" />
                        <label for="qlsv_avatar_default" class="button"><?php esc_html_e('Chọn ảnh', 'qlsv'); ?></label>
                    </div>
                    <button type="submit" class="button button-primary"><?php esc_html_e('Cập nhật', 'qlsv'); ?></button>
                </div>
            </form>
            <script>
            // Script để hiển thị ảnh ngay sau khi chọn
            document.addEventListener('DOMContentLoaded', function() {
                const form = document.querySelector('.avatar-form');
                const fileInput = document.getElementById('qlsv_avatar_default');
                const fileLabel = fileInput ? fileInput.nextElementSibling : null;
                
                // Cập nhật tên file được chọn
                if (fileInput && fileLabel) {
                    fileInput.addEventListener('change', function() {
                        if (this.files.length > 0) {
                            const fileName = this.files[0].name;
                            fileLabel.textContent = fileName.length > 20 ? fileName.substring(0, 17) + '...' : fileName;
                            
                            // Preview ảnh
                            const reader = new FileReader();
                            reader.onload = function(e) {
                                const previewImg = document.querySelector('.avatar-preview img');
                                const mainImg = document.querySelector('.qlsv-user-avatar img');
                                
                                if (previewImg) {
                                    previewImg.src = e.target.result;
                                }
                                
                                if (mainImg) {
                                    mainImg.src = e.target.result;
                                    mainImg.style.opacity = '0.7';
                                    setTimeout(() => {
                                        mainImg.style.opacity = '1';
                                    }, 500);
                                }
                            };
                            reader.readAsDataURL(this.files[0]);
                        } else {
                            fileLabel.textContent = '<?php esc_html_e('Chọn ảnh', 'qlsv'); ?>';
                        }
                    });
                }
            });
            </script>
        </div>
        <?php
        return ob_get_clean();
    }
} 