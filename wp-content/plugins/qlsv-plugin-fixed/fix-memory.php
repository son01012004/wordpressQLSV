<?php
/**
 * Script khắc phục lỗi memory limit trong plugin QLSV
 * Sử dụng file này để tối ưu truy vấn dữ liệu và tăng giới hạn bộ nhớ
 */

// Load WordPress
define('WP_USE_THEMES', false);
require_once('../../../wp-load.php');

// Kiểm tra quyền truy cập
if (!current_user_can('manage_options')) {
    wp_die('Bạn cần có quyền quản trị để truy cập trang này.');
}

// Tăng giới hạn bộ nhớ PHP
ini_set('memory_limit', '1024M');

// Tiêu đề trang
echo '<div style="padding: 20px; font-family: Arial, sans-serif;">';
echo '<h1>Công cụ khắc phục lỗi bộ nhớ cho Plugin QLSV</h1>';

// Kiểm tra xem có hành động nào được yêu cầu không
$action = isset($_POST['action']) ? $_POST['action'] : '';

if ($action === 'fix_memory') {
    // Cập nhật các tùy chọn để tối ưu hóa truy vấn
    update_option('qlsv_query_limit', 20);
    update_option('qlsv_optimize_queries', 'yes');
    
    // Khắc phục vấn đề với shortcode
    $tim_kiem_shortcode = <<<'EOD'
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
                return $this->bang_diem_shortcode(array('sinhvien_id' => $student_id, 'limit' => 20));
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
            
            // PHẦN BỊ SỬA ĐỔI - Giới hạn số lượng mục trong các dropdown
            // Lấy danh sách sinh viên - GIỚI HẠN SỐ LƯỢNG
            $students = get_posts([
                'post_type' => 'sinhvien',
                'posts_per_page' => 100, // Giới hạn số lượng
                'orderby' => 'title',
                'order' => 'ASC'
            ]);
            
            // Lấy danh sách môn học - GIỚI HẠN SỐ LƯỢNG
            $courses = get_posts([
                'post_type' => 'monhoc',
                'posts_per_page' => 50, // Giới hạn số lượng
                'orderby' => 'title',
                'order' => 'ASC'
            ]);
            
            // Lấy danh sách lớp - GIỚI HẠN SỐ LƯỢNG
            $classes = get_posts([
                'post_type' => 'lop',
                'posts_per_page' => 50, // Giới hạn số lượng
                'orderby' => 'title',
                'order' => 'ASC'
            ]);
            
            // Lấy các tham số tìm kiếm từ URL (nếu có)
            $selected_student = isset($_GET['sinhvien']) ? intval($_GET['sinhvien']) : 0;
            $selected_course = isset($_GET['monhoc']) ? intval($_GET['monhoc']) : 0;
            $selected_class = isset($_GET['lop']) ? intval($_GET['lop']) : 0;
            $page = isset($_GET['bangdiem_page']) ? intval($_GET['bangdiem_page']) : 1;
            
            // Load template cho form tìm kiếm
            $template_form_path = QLSV_PLUGIN_DIR . 'templates/tim-kiem-diem-form.php';
            
            if (file_exists($template_form_path)) {
                include $template_form_path;
            } else {
                echo 'Template form không tồn tại.';
            }
            
            // Luôn hiển thị kết quả tìm kiếm, không cần kiểm tra submit
            // Tạo shortcode với các tham số tìm kiếm
            $shortcode_atts = array(
                'limit' => 20, // Giới hạn số lượng kết quả
                'page' => $page
            );
            
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
EOD;

    // Khắc phục vấn đề với hàm bang_diem_shortcode
    $bang_diem_shortcode = <<<'EOD'
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
            'limit' => 20,          // Số lượng kết quả, mặc định giới hạn 20 kết quả
            'page' => 1,            // Trang hiện tại
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
        
        // Tính offset cho phân trang
        $offset = ($atts['page'] - 1) * $atts['limit'];
        
        // Tạo tham số query
        $args = array(
            'post_type' => 'diem',
            'posts_per_page' => $atts['limit'],
            'offset' => $offset,
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
EOD;

    // Cập nhật file dữ liệu
    $code_file = QLSV_PLUGIN_DIR . 'modules/diem/class-qlsv-diem.php';
    $current_code = file_get_contents($code_file);
    
    // Tìm và thay thế các hàm
    // Lưu ý: Dùng preg_replace thế thông minh hơn str_replace vì có thể phù hợp với các phiên bản khác nhau
    $pattern_tim_kiem = '/public\s+function\s+tim_kiem_diem_shortcode\s*\([^\)]*\)\s*\{.*?\}/s';
    $pattern_bang_diem = '/public\s+function\s+bang_diem_shortcode\s*\([^\)]*\)\s*\{.*?\}/s';
    
    if (preg_match($pattern_tim_kiem, $current_code)) {
        $updated_code = preg_replace($pattern_tim_kiem, $tim_kiem_shortcode, $current_code, 1);
        if ($updated_code !== $current_code) {
            file_put_contents($code_file, $updated_code);
            echo '<div style="background-color: #d4edda; color: #155724; padding: 15px; margin-bottom: 20px; border: 1px solid #c3e6cb; border-radius: 4px;">';
            echo '<h3>Đã cập nhật hàm tim_kiem_diem_shortcode!</h3>';
            echo '<p>Hàm tìm kiếm điểm đã được tối ưu hóa để sử dụng ít bộ nhớ hơn.</p>';
            echo '</div>';
            
            // Cập nhật hàm bang_diem_shortcode
            $current_code = $updated_code;
            if (preg_match($pattern_bang_diem, $current_code)) {
                $updated_code = preg_replace($pattern_bang_diem, $bang_diem_shortcode, $current_code, 1);
                if ($updated_code !== $current_code) {
                    file_put_contents($code_file, $updated_code);
                    echo '<div style="background-color: #d4edda; color: #155724; padding: 15px; margin-bottom: 20px; border: 1px solid #c3e6cb; border-radius: 4px;">';
                    echo '<h3>Đã cập nhật hàm bang_diem_shortcode!</h3>';
                    echo '<p>Hàm hiển thị bảng điểm đã được tối ưu hóa để sử dụng ít bộ nhớ hơn.</p>';
                    echo '</div>';
                }
            }
        }
    }
    
    // Cập nhật template để thêm phân trang
    $template_file = QLSV_PLUGIN_DIR . 'templates/bang-diem-list.php';
    if (file_exists($template_file)) {
        $template_content = file_get_contents($template_file);
        
        // Kiểm tra xem đã có phân trang chưa
        if (strpos($template_content, 'class="pagination"') === false) {
            // Thêm phân trang vào cuối template
            $pagination_code = <<<'EOD'

<!-- Phân trang -->
<?php
    // Tính tổng số trang
    $total_posts_query = new WP_Query(array(
        'post_type' => 'diem',
        'posts_per_page' => -1,
        'fields' => 'ids',
        'meta_query' => $args['meta_query']
    ));
    $total_posts = $total_posts_query->found_posts;
    $total_pages = ceil($total_posts / $atts['limit']);
    
    // URL hiện tại
    $current_url = add_query_arg(array());
    
    // Hiển thị phân trang nếu có nhiều hơn 1 trang
    if ($total_pages > 1):
?>
<div class="pagination" style="margin-top: 20px; text-align: center;">
    <?php
        // Hiển thị nút Previous
        if ($atts['page'] > 1): 
            $prev_url = add_query_arg('bangdiem_page', $atts['page'] - 1, $current_url);
    ?>
        <a href="<?php echo esc_url($prev_url); ?>" class="page-link" style="margin: 0 5px; padding: 5px 10px; border: 1px solid #ddd; text-decoration: none;">&laquo; Trước</a>
    <?php endif; ?>
    
    <?php
        // Hiển thị các trang
        $start_page = max(1, $atts['page'] - 2);
        $end_page = min($total_pages, $atts['page'] + 2);
        
        for ($i = $start_page; $i <= $end_page; $i++):
            $page_url = add_query_arg('bangdiem_page', $i, $current_url);
            $current_class = ($i == $atts['page']) ? 'current-page' : '';
    ?>
        <a href="<?php echo esc_url($page_url); ?>" class="page-link <?php echo $current_class; ?>" style="margin: 0 5px; padding: 5px 10px; border: 1px solid #ddd; text-decoration: none; <?php if ($i == $atts['page']) echo 'background-color: #f0f0f0; font-weight: bold;'; ?>"><?php echo $i; ?></a>
    <?php endfor; ?>
    
    <?php
        // Hiển thị nút Next
        if ($atts['page'] < $total_pages): 
            $next_url = add_query_arg('bangdiem_page', $atts['page'] + 1, $current_url);
    ?>
        <a href="<?php echo esc_url($next_url); ?>" class="page-link" style="margin: 0 5px; padding: 5px 10px; border: 1px solid #ddd; text-decoration: none;">Sau &raquo;</a>
    <?php endif; ?>
</div>
<?php endif; ?>
EOD;
            
            // Thêm mã phân trang vào cuối file template
            $template_content .= $pagination_code;
            file_put_contents($template_file, $template_content);
            
            echo '<div style="background-color: #d4edda; color: #155724; padding: 15px; margin-bottom: 20px; border: 1px solid #c3e6cb; border-radius: 4px;">';
            echo '<h3>Đã thêm phân trang!</h3>';
            echo '<p>Template bảng điểm đã được cập nhật để hỗ trợ phân trang, giảm tải dữ liệu cần xử lý.</p>';
            echo '</div>';
        }
    }
    
    echo '<div style="background-color: #d4edda; color: #155724; padding: 15px; margin-bottom: 20px; border: 1px solid #c3e6cb; border-radius: 4px;">';
    echo '<h3>Đã khắc phục lỗi bộ nhớ!</h3>';
    echo '<p>Các thay đổi đã được áp dụng để tối ưu hóa bộ nhớ:</p>';
    echo '<ul>';
    echo '<li>Giới hạn số lượng kết quả hiển thị mỗi lần</li>';
    echo '<li>Thêm phân trang cho bảng điểm</li>';
    echo '<li>Giới hạn dữ liệu trong các dropdown</li>';
    echo '</ul>';
    echo '<p>Hãy truy cập vào trang <a href="' . home_url('/ket-qua-hoc-tap/') . '" target="_blank">Kết quả học tập</a> để kiểm tra.</p>';
    echo '</div>';
} elseif ($action === 'increase_memory') {
    // Tăng bộ nhớ
    $php_ini_content = <<<EOD
; Tăng giới hạn bộ nhớ cho WordPress
memory_limit = 1024M
max_execution_time = 300
post_max_size = 64M
upload_max_filesize = 64M
EOD;

    // Tạo file php.ini trong thư mục plugin
    $php_ini_file = QLSV_PLUGIN_DIR . 'php.ini';
    file_put_contents($php_ini_file, $php_ini_content);
    
    // Tạo file .user.ini (cho một số máy chủ như Nginx)
    $user_ini_file = QLSV_PLUGIN_DIR . '.user.ini';
    file_put_contents($user_ini_file, $php_ini_content);
    
    // Tạo file .htaccess để tăng giới hạn bộ nhớ (cho Apache)
    $htaccess_content = <<<EOD
# BEGIN QLSV Memory Fix
<IfModule mod_php7.c>
    php_value memory_limit 1024M
    php_value max_execution_time 300
    php_value post_max_size 64M
    php_value upload_max_filesize 64M
</IfModule>
<IfModule mod_php.c>
    php_value memory_limit 1024M
    php_value max_execution_time 300
    php_value post_max_size 64M
    php_value upload_max_filesize 64M
</IfModule>
# END QLSV Memory Fix
EOD;

    // Thêm vào .htaccess trong thư mục plugin
    $htaccess_file = QLSV_PLUGIN_DIR . '.htaccess';
    file_put_contents($htaccess_file, $htaccess_content);
    
    echo '<div style="background-color: #d4edda; color: #155724; padding: 15px; margin-bottom: 20px; border: 1px solid #c3e6cb; border-radius: 4px;">';
    echo '<h3>Đã tăng giới hạn bộ nhớ!</h3>';
    echo '<p>Các file cấu hình đã được tạo để tăng giới hạn bộ nhớ PHP:</p>';
    echo '<ul>';
    echo '<li>' . $php_ini_file . '</li>';
    echo '<li>' . $user_ini_file . '</li>';
    echo '<li>' . $htaccess_file . '</li>';
    echo '</ul>';
    echo '<p><strong>Lưu ý:</strong> Bạn có thể cần phải khởi động lại máy chủ web để các thay đổi có hiệu lực.</p>';
    echo '</div>';
}

// Hiển thị form các hành động
echo '<h2>Các hành động sửa chữa</h2>';

echo '<div style="display: flex; gap: 20px; flex-wrap: wrap;">';

// Form sửa memory_limit
echo '<div style="background-color: #f5f5f5; border-radius: 8px; padding: 20px; flex: 1; min-width: 300px;">';
echo '<h3>1. Khắc phục lỗi bộ nhớ</h3>';
echo '<p>Sửa chức năng tìm kiếm điểm để sử dụng ít bộ nhớ hơn, thêm phân trang và giới hạn số lượng kết quả hiển thị.</p>';
echo '<form method="post">';
echo '<input type="hidden" name="action" value="fix_memory">';
echo '<button type="submit" style="background-color: #0073aa; color: white; border: none; padding: 10px 15px; border-radius: 4px; cursor: pointer;">Khắc phục bộ nhớ</button>';
echo '</form>';
echo '</div>';

// Form tăng bộ nhớ
echo '<div style="background-color: #f5f5f5; border-radius: 8px; padding: 20px; flex: 1; min-width: 300px;">';
echo '<h3>2. Tăng giới hạn bộ nhớ</h3>';
echo '<p>Tạo các file cấu hình để tăng giới hạn bộ nhớ PHP lên 1GB.</p>';
echo '<form method="post">';
echo '<input type="hidden" name="action" value="increase_memory">';
echo '<button type="submit" style="background-color: #0073aa; color: white; border: none; padding: 10px 15px; border-radius: 4px; cursor: pointer;">Tăng giới hạn bộ nhớ</button>';
echo '</form>';
echo '</div>';

echo '</div>';

// Hướng dẫn thủ công
echo '<h2 style="margin-top: 30px;">Hướng dẫn khắc phục thủ công</h2>';
echo '<div style="background-color: #fff3cd; color: #856404; padding: 15px; margin-bottom: 20px; border: 1px solid #ffeeba; border-radius: 4px;">';

echo '<p><strong>1. Tăng giới hạn bộ nhớ trong wp-config.php:</strong></p>';
echo '<pre style="background: #f8f9fa; padding: 10px; border: 1px solid #eee;">define("WP_MEMORY_LIMIT", "1024M");</pre>';

echo '<p><strong>2. Thêm giới hạn kích thước truy vấn:</strong></p>';
echo '<p>Sửa file class-qlsv-diem.php, hạn chế số lượng kết quả trả về cho mỗi truy vấn.</p>';

echo '<p><strong>3. Tối ưu hóa truy vấn:</strong></p>';
echo '<ul>';
echo '<li>Sử dụng phân trang</li>';
echo '<li>Chỉ tải các dữ liệu cần thiết</li>';
echo '<li>Tải dữ liệu theo batch</li>';
echo '</ul>';

echo '<p><strong>4. Tăng giới hạn bộ nhớ trong php.ini:</strong></p>';
echo '<pre style="background: #f8f9fa; padding: 10px; border: 1px solid #eee;">memory_limit = 1024M</pre>';

echo '</div>';

// Link quay lại
echo '<p style="margin-top: 20px;"><a href="' . admin_url() . '" style="background-color: #6c757d; color: white; text-decoration: none; padding: 10px 15px; border-radius: 4px;">Quay lại trang quản trị</a></p>';

echo '</div>'; // End main container
?> 