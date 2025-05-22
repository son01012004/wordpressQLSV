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
        $this->loader->add_action('acf/save_post', $this, 'update_diemdanh_title', 20);
        
        // Đăng ký AJAX để xử lý cập nhật điểm danh
        $this->loader->add_action('wp_ajax_update_diemdanh', $this, 'ajax_update_diemdanh');
        
        // Đăng ký AJAX để xử lý hiển thị chi tiết điểm danh
        $this->loader->add_action('wp_ajax_get_diemdanh_detail', $this, 'ajax_get_diemdanh_detail');
        $this->loader->add_action('wp_ajax_nopriv_get_diemdanh_detail', $this, 'ajax_get_diemdanh_detail');
        
        // Xử lý form điểm danh
        $this->loader->add_action('init', $this, 'handle_diemdanh_form');
        
        // Đăng ký page template
        $this->loader->add_filter('theme_page_templates', $this, 'add_page_template');
        $this->loader->add_filter('template_include', $this, 'load_page_template');
    }
    
    /**
     * Đăng ký các shortcodes
     */
    private function register_shortcodes() {
        add_shortcode('qlsv_diemdanh', array($this, 'diemdanh_shortcode'));
        add_shortcode('qlsv_diemdanh_sinhvien', array($this, 'diemdanh_sinhvien_shortcode'));
        add_shortcode('qlsv_diemdanh_form', array($this, 'diemdanh_form_shortcode'));
        add_shortcode('qlsv_diemdanh_dashboard', array($this, 'diemdanh_dashboard_shortcode'));
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
                'slug' => 'diemdanh-record',
                'with_front' => false
            ),
            'capability_type'    => 'post',
            'has_archive'        => false,
            'hierarchical'       => false,
            'menu_position'      => null,
            'supports'           => array('title'),
            'menu_icon'          => 'dashicons-clipboard',
        );

        register_post_type('diemdanh', $args);
    }
    
    /**
     * Đăng ký ACF Fields cho điểm danh
     */
    public function register_acf_fields() {
        if (!function_exists('acf_add_local_field_group')) {
            return;
        }

        acf_add_local_field_group(array(
            'key' => 'group_diemdanh',
            'title' => 'Thông tin buổi điểm danh',
            'fields' => array(
                array(
                    'key' => 'field_mon_hoc_dd',
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
                    'key' => 'field_lop_dd',
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
                    'key' => 'field_giang_vien_dd',
                    'label' => 'Giảng viên',
                    'name' => 'giang_vien',
                    'type' => 'user',
                    'instructions' => 'Chọn giảng viên',
                    'required' => 0,
                    'role' => '',
                    'return_format' => 'id',
                ),
                array(
                    'key' => 'field_ngay_dd',
                    'label' => 'Ngày',
                    'name' => 'ngay',
                    'type' => 'date_picker',
                    'instructions' => 'Chọn ngày điểm danh',
                    'required' => 1,
                    'display_format' => 'd/m/Y',
                    'return_format' => 'Y-m-d',
                ),
                array(
                    'key' => 'field_buoi_hoc',
                    'label' => 'Buổi học',
                    'name' => 'buoi_hoc',
                    'type' => 'number',
                    'instructions' => 'Nhập số buổi học',
                    'required' => 1,
                    'default_value' => 1,
                    'min' => 1,
                    'step' => 1,
                ),
                array(
                    'key' => 'field_ghi_chu',
                    'label' => 'Ghi chú',
                    'name' => 'ghi_chu',
                    'type' => 'textarea',
                    'instructions' => 'Nhập ghi chú về buổi học (nếu có)',
                    'required' => 0,
                ),
                array(
                    'key' => 'field_sinh_vien_dd',
                    'label' => 'Sinh viên',
                    'name' => 'sinh_vien_dd',
                    'type' => 'repeater',
                    'instructions' => 'Danh sách sinh viên điểm danh',
                    'required' => 0,
                    'layout' => 'table',
                    'sub_fields' => array(
                        array(
                            'key' => 'field_sv_id',
                            'label' => 'Sinh viên',
                            'name' => 'sinh_vien_id',
                            'type' => 'post_object',
                            'instructions' => '',
                            'required' => 1,
                            'post_type' => array('sinhvien'),
                            'return_format' => 'id',
                            'ui' => 1,
                        ),
                        array(
                            'key' => 'field_trang_thai',
                            'label' => 'Trạng thái',
                            'name' => 'trang_thai',
                            'type' => 'select',
                            'instructions' => '',
                            'required' => 1,
                            'choices' => array(
                                'co_mat' => 'Có mặt',
                                'vang' => 'Vắng',
                                'di_muon' => 'Đi muộn',
                                've_som' => 'Về sớm',
                                'co_phep' => 'Có phép',
                            ),
                            'default_value' => 'co_mat',
                        ),
                        array(
                            'key' => 'field_ghi_chu_sv',
                            'label' => 'Ghi chú',
                            'name' => 'ghi_chu',
                            'type' => 'text',
                            'instructions' => '',
                            'required' => 0,
                        ),
                    ),
                ),
            ),
            'location' => array(
                array(
                    array(
                        'param' => 'post_type',
                        'operator' => '==',
                        'value' => 'diemdanh',
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
            'diemdanh_info',
            'Thông tin điểm danh',
            array($this, 'render_meta_box'),
            'diemdanh',
            'normal',
            'high'
        );
    }
    
    /**
     * Render metabox thông tin điểm danh
     */
    public function render_meta_box($post) {
        // Lấy thông tin cơ bản
        $mon_hoc_id = get_field('mon_hoc', $post->ID);
        $lop_id = get_field('lop', $post->ID);
        $ngay = get_field('ngay', $post->ID);
        $buoi_hoc = get_field('buoi_hoc', $post->ID);

        // Hiển thị tóm tắt thông tin
        echo '<div class="diemdanh-summary">';
        
        if ($mon_hoc_id) {
            echo '<p><strong>Môn học:</strong> ' . get_the_title($mon_hoc_id) . '</p>';
        }
        
        if ($lop_id) {
            echo '<p><strong>Lớp:</strong> ' . get_the_title($lop_id) . '</p>';
        }
        
        if ($ngay) {
            echo '<p><strong>Ngày:</strong> ' . date_i18n('d/m/Y', strtotime($ngay)) . '</p>';
        }
        
        if ($buoi_hoc) {
            echo '<p><strong>Buổi học số:</strong> ' . $buoi_hoc . '</p>';
        }
        
        echo '</div>';
        
        // Thêm nút "Cập nhật danh sách sinh viên" nếu đã có lớp được chọn
        if ($lop_id && $lop_id > 0) {
            echo '<div class="diemdanh-actions">';
            echo '<button type="button" id="update-student-list" class="button button-primary" data-lop-id="' . $lop_id . '" data-post-id="' . $post->ID . '">Cập nhật danh sách sinh viên</button>';
            echo '<span class="spinner" style="float:none;"></span>';
            echo '</div>';
            
            // Thêm JavaScript để xử lý cập nhật danh sách sinh viên
            ?>
            <script>
            jQuery(document).ready(function($) {
                $('#update-student-list').on('click', function() {
                    var button = $(this);
                    var spinner = button.next('.spinner');
                    var lopId = button.data('lop-id');
                    var postId = button.data('post-id');
                    
                    spinner.addClass('is-active');
                    button.prop('disabled', true);
                    
                    $.ajax({
                        url: ajaxurl,
                        type: 'POST',
                        data: {
                            action: 'update_diemdanh',
                            lop_id: lopId,
                            post_id: postId,
                            security: '<?php echo wp_create_nonce('update_diemdanh_nonce'); ?>'
                        },
                        success: function(response) {
                            if (response.success) {
                                alert('Đã cập nhật danh sách sinh viên thành công!');
                                window.location.reload();
                            } else {
                                alert('Có lỗi xảy ra: ' + response.data);
                            }
                        },
                        error: function() {
                            alert('Đã xảy ra lỗi khi kết nối với server.');
                        },
                        complete: function() {
                            spinner.removeClass('is-active');
                            button.prop('disabled', false);
                        }
                    });
                });
            });
            </script>
            <?php
        }
    }
    
    /**
     * Cập nhật tiêu đề điểm danh tự động
     */
    public function update_diemdanh_title($post_id) {
        // Chỉ xử lý post type 'diemdanh'
        if (get_post_type($post_id) !== 'diemdanh') {
            return;
        }

        // Cập nhật tiêu đề dựa trên dữ liệu đã chọn
        $mon_hoc_id = get_field('mon_hoc', $post_id);
        $lop_id = get_field('lop', $post_id);
        $ngay = get_field('ngay', $post_id);
        $buoi_hoc = get_field('buoi_hoc', $post_id);
        
        if ($mon_hoc_id && $lop_id && $ngay) {
            $mon_hoc = get_the_title($mon_hoc_id);
            $lop = get_the_title($lop_id);
            $ngay_format = date_i18n('d/m/Y', strtotime($ngay));
            
            // Cập nhật tiêu đề
            $title = sprintf('Điểm danh %s - %s - %s - Buổi %s', $lop, $mon_hoc, $ngay_format, $buoi_hoc);
            
            // Cập nhật post mà không trigger save_post hook
            remove_action('acf/save_post', array($this, 'update_diemdanh_title'), 20);
            
            wp_update_post(array(
                'ID' => $post_id,
                'post_title' => $title,
            ));
            
            add_action('acf/save_post', array($this, 'update_diemdanh_title'), 20);
        }
    }
    
    /**
     * Xử lý AJAX cập nhật danh sách sinh viên
     */
    public function ajax_update_diemdanh() {
        // Kiểm tra nonce bảo mật
        check_ajax_referer('update_diemdanh_nonce', 'security');
        
        // Lấy dữ liệu
        $lop_id = isset($_POST['lop_id']) ? intval($_POST['lop_id']) : 0;
        $post_id = isset($_POST['post_id']) ? intval($_POST['post_id']) : 0;
        
        // Kiểm tra dữ liệu hợp lệ
        if (!$lop_id || !$post_id) {
            wp_send_json_error('Dữ liệu không hợp lệ.');
            return;
        }
        
        // Query sinh viên thuộc lớp
        $args = array(
            'post_type' => 'sinhvien',
            'posts_per_page' => -1,
            'meta_query' => array(
                array(
                    'key' => 'lop',
                    'value' => $lop_id,
                    'compare' => '='
                )
            )
        );
        
        $students = get_posts($args);
        
        if (empty($students)) {
            wp_send_json_error('Không có sinh viên nào thuộc lớp này.');
            return;
        }
        
        // Tạo danh sách sinh viên điểm danh
        $student_list = array();
        
        foreach ($students as $student) {
            $student_list[] = array(
                'sinh_vien_id' => $student->ID,
                'trang_thai' => 'co_mat',
                'ghi_chu' => ''
            );
        }
        
        // Cập nhật ACF field
        update_field('sinh_vien_dd', $student_list, $post_id);
        
        wp_send_json_success('Đã cập nhật danh sách sinh viên thành công.');
    }
    
    /**
     * Xử lý AJAX hiển thị chi tiết điểm danh
     */
    public function ajax_get_diemdanh_detail() {
        $post_id = isset($_POST['post_id']) ? intval($_POST['post_id']) : 0;
        
        if (!$post_id) {
            wp_send_json_error('ID không hợp lệ.');
            return;
        }
        
        // Kiểm tra loại post
        if (get_post_type($post_id) !== 'diemdanh') {
            wp_send_json_error('Không phải bản ghi điểm danh.');
            return;
        }
        
        // Lấy thông tin cơ bản
        $mon_hoc_id = get_field('mon_hoc', $post_id);
        $lop_id = get_field('lop', $post_id);
        $ngay = get_field('ngay', $post_id);
        $buoi_hoc = get_field('buoi_hoc', $post_id);
        $giang_vien_id = get_field('giang_vien', $post_id);
        $ghi_chu = get_field('ghi_chu', $post_id);
        $sinh_vien_dd = get_field('sinh_vien_dd', $post_id);
        
        // Lấy tên môn học và lớp
        $mon_hoc = $mon_hoc_id ? get_the_title($mon_hoc_id) : 'N/A';
        $lop = $lop_id ? get_the_title($lop_id) : 'N/A';
        
        // Format ngày
        $ngay_format = $ngay ? date_i18n('d/m/Y', strtotime($ngay)) : 'N/A';
        
        // Lấy tên giảng viên
        $giang_vien = '';
        if ($giang_vien_id) {
            $user_data = get_userdata($giang_vien_id);
            if ($user_data) {
                $giang_vien = $user_data->display_name;
            }
        }
        
        // HTML output
        ob_start();
        ?>
        <div class="diemdanh-detail">
            <h3><?php echo esc_html(get_the_title($post_id)); ?></h3>
            
            <div class="diemdanh-info">
                <div class="info-item">
                    <span class="info-label"><?php esc_html_e('Môn học:', 'qlsv'); ?></span>
                    <span class="info-value"><?php echo esc_html($mon_hoc); ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label"><?php esc_html_e('Lớp:', 'qlsv'); ?></span>
                    <span class="info-value"><?php echo esc_html($lop); ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label"><?php esc_html_e('Ngày:', 'qlsv'); ?></span>
                    <span class="info-value"><?php echo esc_html($ngay_format); ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label"><?php esc_html_e('Buổi học số:', 'qlsv'); ?></span>
                    <span class="info-value"><?php echo esc_html($buoi_hoc); ?></span>
                </div>
                <?php if (!empty($giang_vien)) : ?>
                <div class="info-item">
                    <span class="info-label"><?php esc_html_e('Giảng viên:', 'qlsv'); ?></span>
                    <span class="info-value"><?php echo esc_html($giang_vien); ?></span>
                </div>
                <?php endif; ?>
            </div>
            
            <?php if (!empty($ghi_chu)) : ?>
            <div class="diemdanh-ghi-chu">
                <h4><?php esc_html_e('Ghi chú buổi học:', 'qlsv'); ?></h4>
                <div class="ghi-chu-content"><?php echo nl2br(esc_html($ghi_chu)); ?></div>
            </div>
            <?php endif; ?>
            
            <div class="diemdanh-students">
                <h4><?php esc_html_e('Danh sách sinh viên:', 'qlsv'); ?></h4>
                
                <?php if (empty($sinh_vien_dd)) : ?>
                    <p><?php esc_html_e('Chưa có dữ liệu điểm danh.', 'qlsv'); ?></p>
                <?php else : ?>
                    <table class="students-table">
                        <thead>
                            <tr>
                                <th><?php esc_html_e('STT', 'qlsv'); ?></th>
                                <th><?php esc_html_e('Mã SV', 'qlsv'); ?></th>
                                <th><?php esc_html_e('Họ tên', 'qlsv'); ?></th>
                                <th><?php esc_html_e('Trạng thái', 'qlsv'); ?></th>
                                <th><?php esc_html_e('Ghi chú', 'qlsv'); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $stt = 1;
                            foreach ($sinh_vien_dd as $sv_dd) :
                                $sv_id = $sv_dd['sinh_vien_id'];
                                $trang_thai = $sv_dd['trang_thai'];
                                $ghi_chu_sv = isset($sv_dd['ghi_chu']) ? $sv_dd['ghi_chu'] : '';
                                
                                // Lấy thông tin sinh viên
                                $sinh_vien = get_post($sv_id);
                                if (!$sinh_vien) continue;
                                
                                $ma_sv = get_field('ma_sinh_vien', $sv_id);
                                $ho_ten = $sinh_vien->post_title;
                                
                                // Hiển thị trạng thái dễ đọc
                                $trang_thai_text = '';
                                $trang_thai_class = '';
                                
                                switch ($trang_thai) {
                                    case 'co_mat':
                                        $trang_thai_text = __('Có mặt', 'qlsv');
                                        $trang_thai_class = 'status-present';
                                        break;
                                    case 'vang':
                                        $trang_thai_text = __('Vắng', 'qlsv');
                                        $trang_thai_class = 'status-absent';
                                        break;
                                    case 'di_muon':
                                        $trang_thai_text = __('Đi muộn', 'qlsv');
                                        $trang_thai_class = 'status-late';
                                        break;
                                    case 've_som':
                                        $trang_thai_text = __('Về sớm', 'qlsv');
                                        $trang_thai_class = 'status-early';
                                        break;
                                    case 'co_phep':
                                        $trang_thai_text = __('Có phép', 'qlsv');
                                        $trang_thai_class = 'status-excused';
                                        break;
                                    default:
                                        $trang_thai_text = __('Không xác định', 'qlsv');
                                        $trang_thai_class = '';
                                        break;
                                }
                            ?>
                            <tr class="<?php echo esc_attr($trang_thai_class); ?>">
                                <td><?php echo $stt++; ?></td>
                                <td><?php echo esc_html($ma_sv); ?></td>
                                <td><?php echo esc_html($ho_ten); ?></td>
                                <td><span class="trang-thai-label"><?php echo esc_html($trang_thai_text); ?></span></td>
                                <td><?php echo esc_html($ghi_chu_sv); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </div>
        
        <style>
            .diemdanh-detail h3 {
                margin-top: 0;
                margin-bottom: 15px;
                border-bottom: 1px solid #eee;
                padding-bottom: 10px;
            }
            .diemdanh-info {
                display: flex;
                flex-wrap: wrap;
                margin-bottom: 20px;
                background: #f9f9f9;
                padding: 15px;
                border-radius: 5px;
            }
            .info-item {
                flex: 1 0 calc(50% - 20px);
                margin-bottom: 10px;
                padding-right: 20px;
            }
            .info-label {
                font-weight: bold;
                margin-right: 5px;
            }
            .diemdanh-ghi-chu {
                margin-bottom: 20px;
            }
            .ghi-chu-content {
                background: #f9f9f9;
                padding: 10px;
                border-radius: 5px;
                font-style: italic;
            }
            .students-table {
                width: 100%;
                border-collapse: collapse;
            }
            .students-table th, 
            .students-table td {
                padding: 8px;
                border: 1px solid #ddd;
                text-align: left;
            }
            .students-table th {
                background-color: #f2f2f2;
                font-weight: bold;
            }
            .trang-thai-label {
                padding: 3px 8px;
                border-radius: 3px;
                font-size: 12px;
                font-weight: bold;
                white-space: nowrap;
            }
            tr.status-present .trang-thai-label {
                background-color: #e8f8f0;
                color: #27ae60;
            }
            tr.status-absent .trang-thai-label {
                background-color: #fdedeb;
                color: #c0392b;
            }
            tr.status-late .trang-thai-label {
                background-color: #fef6e7;
                color: #d35400;
            }
            tr.status-early .trang-thai-label {
                background-color: #f4ecf7;
                color: #8e44ad;
            }
            tr.status-excused .trang-thai-label {
                background-color: #e8f6f3;
                color: #16a085;
            }
            /* Thống kê dưới bảng */
            .diemdanh-summary {
                margin-top: 20px;
                display: flex;
                flex-wrap: wrap;
                gap: 10px;
            }
            .summary-item {
                flex: 1 1 calc(20% - 10px);
                min-width: 110px;
                padding: 8px;
                text-align: center;
                border-radius: 4px;
                font-size: 13px;
                font-weight: bold;
            }
            .summary-present {
                background-color: #e8f8f0;
                color: #27ae60;
            }
            .summary-absent {
                background-color: #fdedeb;
                color: #c0392b;
            }
            .summary-late {
                background-color: #fef6e7;
                color: #d35400;
            }
            .summary-early {
                background-color: #f4ecf7;
                color: #8e44ad;
            }
            .summary-excused {
                background-color: #e8f6f3;
                color: #16a085;
            }
            @media (max-width: 768px) {
                .info-item {
                    flex: 1 0 100%;
                }
            }
        </style>
        <?php
        
        $html = ob_get_clean();
        
        wp_send_json_success($html);
    }
    
    /**
     * Shortcode hiển thị bảng điểm danh
     */
    public function diemdanh_shortcode($atts) {
        // Tham số mặc định
        $atts = shortcode_atts(array(
            'lop_id' => 0,        // ID lớp
            'monhoc_id' => 0,     // ID môn học
            'giangvien_id' => 0,  // ID giảng viên
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

        // Load template
        ob_start();
        
        $template_path = QLSV_PLUGIN_DIR . 'templates/diemdanh-list.php';
        
        if (file_exists($template_path)) {
            // Truy vấn dữ liệu điểm danh
            $args = array(
                'post_type' => 'diemdanh',
                'posts_per_page' => -1,
                'meta_query' => array('relation' => 'AND'),
                'meta_key' => 'ngay',
                'orderby' => 'meta_value',
                'order' => 'DESC'
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
            if (!empty($atts['giangvien_id'])) {
                $args['meta_query'][] = array(
                    'key' => 'giang_vien',
                    'value' => $atts['giangvien_id'],
                    'compare' => '='
                );
            }
            
            $diemdanh_query = new WP_Query($args);
            
            // Lấy danh sách lớp và môn học cho bộ lọc
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
            
            include $template_path;
            
            wp_reset_postdata();
        } else {
            echo 'Template không tồn tại.';
        }
        
        return ob_get_clean();
    }
    
    /**
     * Shortcode hiển thị thống kê điểm danh của sinh viên
     */
    public function diemdanh_sinhvien_shortcode($atts) {
        // Tham số mặc định
        $atts = shortcode_atts(array(
            'sinhvien_id' => 0,   // ID sinh viên, nếu 0 sẽ lấy từ user hiện tại
            'monhoc_id' => 0,     // ID môn học, nếu 0 sẽ hiển thị tất cả
        ), $atts);
        
        // Kiểm tra sinh viên
        $sinh_vien_id = intval($atts['sinhvien_id']);
        
        // Nếu không có ID sinh viên, thử lấy từ user hiện tại
        if ($sinh_vien_id <= 0 && is_user_logged_in()) {
            $current_user = wp_get_current_user();
            $user_email = $current_user->user_email;
            
            // Tìm sinh viên có email trùng với email user
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
            
            $student_query = new WP_Query($args);
            
            if ($student_query->have_posts()) {
                $student_query->the_post();
                $sinh_vien_id = get_the_ID();
            }
            
            wp_reset_postdata();
        }
        
        // Nếu không có sinh viên nào được xác định
        if ($sinh_vien_id <= 0) {
            return '<div class="diemdanh-thongbao">Không thể xác định sinh viên. Vui lòng đăng nhập hoặc chỉ định ID sinh viên.</div>';
        }

        // Load template
        ob_start();
        
        $template_path = QLSV_PLUGIN_DIR . 'templates/diemdanh-sinhvien.php';
        
        if (file_exists($template_path)) {
            // Lấy thông tin sinh viên
            $sinh_vien = get_post($sinh_vien_id);
            
            if (!$sinh_vien) {
                return '<div class="diemdanh-thongbao">Không tìm thấy thông tin sinh viên.</div>';
            }
            
            // Danh sách môn học để lọc
            $mon_hoc_list = array();
            
            // Nếu có môn học cụ thể
            $monhoc_id = intval($atts['monhoc_id']);
            if ($monhoc_id > 0) {
                $mon_hoc = get_post($monhoc_id);
                if ($mon_hoc) {
                    $mon_hoc_list[$mon_hoc->ID] = $mon_hoc->post_title;
                }
            } else {
                // Lấy tất cả các buổi điểm danh của sinh viên
                $diemdanh_args = array(
                    'post_type' => 'diemdanh',
                    'posts_per_page' => -1,
                    'meta_query' => array(
                        array(
                            'key' => 'sinh_vien_dd',
                            'value' => '"' . $sinh_vien_id . '"',
                            'compare' => 'LIKE'
                        )
                    )
                );
                
                $diemdanh_query = new WP_Query($diemdanh_args);
                
                $monhoc_ids = array();
                while ($diemdanh_query->have_posts()) {
                    $diemdanh_query->the_post();
                    $monhoc_id = get_field('mon_hoc', get_the_ID());
                    if ($monhoc_id && !in_array($monhoc_id, $monhoc_ids)) {
                        $monhoc_ids[] = $monhoc_id;
                    }
                }
                
                wp_reset_postdata();
                
                // Lấy tên các môn học
                foreach ($monhoc_ids as $id) {
                    $mon_hoc = get_post($id);
                    if ($mon_hoc) {
                        $mon_hoc_list[$mon_hoc->ID] = $mon_hoc->post_title;
                    }
                }
            }
            
            // Lấy thống kê điểm danh cho từng môn học
            $diemdanh_stats = array();
            
            foreach ($mon_hoc_list as $mon_hoc_id => $mon_hoc_name) {
                // Lấy tất cả buổi điểm danh của môn học này
                $diemdanh_args = array(
                    'post_type' => 'diemdanh',
                    'posts_per_page' => -1,
                    'meta_query' => array(
                        'relation' => 'AND',
                        array(
                            'key' => 'mon_hoc',
                            'value' => $mon_hoc_id,
                            'compare' => '='
                        )
                    ),
                    'meta_key' => 'ngay',
                    'orderby' => 'meta_value',
                    'order' => 'ASC'
                );
                
                $diemdanh_query = new WP_Query($diemdanh_args);
                
                $buoi_list = array();
                $stats = array(
                    'tong_so_buoi' => 0,
                    'co_mat' => 0,
                    'vang' => 0,
                    'di_muon' => 0,
                    've_som' => 0,
                    'co_phep' => 0
                );
                
                while ($diemdanh_query->have_posts()) {
                    $diemdanh_query->the_post();
                    $post_id = get_the_ID();
                    
                    // Lấy thông tin cơ bản của buổi điểm danh
                    $lop_id = get_field('lop', $post_id);
                    $ngay = get_field('ngay', $post_id);
                    $buoi_hoc = get_field('buoi_hoc', $post_id);
                    
                    // Lấy danh sách sinh viên điểm danh
                    $sinh_vien_dd = get_field('sinh_vien_dd', $post_id);
                    
                    if ($sinh_vien_dd) {
                        $stats['tong_so_buoi']++;
                        
                        $trang_thai = 'vang'; // Mặc định là vắng nếu không tìm thấy
                        
                        foreach ($sinh_vien_dd as $sv_dd) {
                            if ($sv_dd['sinh_vien_id'] == $sinh_vien_id) {
                                $trang_thai = $sv_dd['trang_thai'];
                                $ghi_chu = isset($sv_dd['ghi_chu']) ? $sv_dd['ghi_chu'] : '';
                                
                                // Cập nhật thống kê
                                $stats[$trang_thai]++;
                                
                                // Thêm vào danh sách buổi
                                $buoi_list[] = array(
                                    'id' => $post_id,
                                    'buoi' => $buoi_hoc,
                                    'ngay' => $ngay,
                                    'ngay_format' => date_i18n('d/m/Y', strtotime($ngay)),
                                    'trang_thai' => $trang_thai,
                                    'ghi_chu' => $ghi_chu
                                );
                                
                                break;
                            }
                        }
                    }
                }
                
                wp_reset_postdata();
                
                $diemdanh_stats[$mon_hoc_id] = array(
                    'ten_mon' => $mon_hoc_name,
                    'buoi_list' => $buoi_list,
                    'stats' => $stats
                );
            }
            
            include $template_path;
        } else {
            echo 'Template không tồn tại.';
        }
        
        return ob_get_clean();
    }
    
    /**
     * Shortcode hiển thị form điểm danh
     */
    public function diemdanh_form_shortcode($atts) {
        // Tham số mặc định
        $atts = shortcode_atts(array(
            'lop_id' => 0,       // ID lớp mặc định
            'monhoc_id' => 0,    // ID môn học mặc định
        ), $atts);
        
        // Chỉ hiển thị form cho giáo viên và quản trị viên
        if (!is_user_logged_in() || (!current_user_can('edit_posts') && !current_user_can('manage_options'))) {
            return '<div class="diemdanh-error-message">
                <p>' . __('Bạn không có quyền truy cập chức năng này.', 'qlsv') . '</p>
            </div>';
        }
        
        // Lấy danh sách lớp và môn học
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
        
        // Tham số từ form
        $lop_id = isset($_POST['lop_id']) ? intval($_POST['lop_id']) : intval($atts['lop_id']);
        $mon_hoc_id = isset($_POST['mon_hoc_id']) ? intval($_POST['mon_hoc_id']) : intval($atts['monhoc_id']);
        $selected_date = isset($_POST['ngay_diemdanh']) ? sanitize_text_field($_POST['ngay_diemdanh']) : date('Y-m-d');
        $buoi_hoc = isset($_POST['buoi_hoc']) ? intval($_POST['buoi_hoc']) : 1;
        
        // Thông báo thành công
        $success_message = '';
        if (isset($_GET['diemdanh_saved']) && $_GET['diemdanh_saved'] === '1') {
            $success_message = __('Điểm danh đã được lưu thành công!', 'qlsv');
        } else if (isset($_GET['diemdanh_updated']) && $_GET['diemdanh_updated'] === '1') {
            $success_message = __('Điểm danh đã được cập nhật thành công!', 'qlsv');
        }
        
        // Load template
        ob_start();
        
        $template_path = QLSV_PLUGIN_DIR . 'templates/diemdanh-form.php';
        
        if (file_exists($template_path)) {
            include $template_path;
        } else {
            echo 'Template điểm danh không tồn tại.';
        }
        
        return ob_get_clean();
    }
    
    /**
     * Xử lý form điểm danh
     */
    public function handle_diemdanh_form() {
        // Kiểm tra quyền truy cập
        if (!is_user_logged_in() || (!current_user_can('edit_posts') && !current_user_can('manage_options'))) {
            return;
        }
        
        // Xử lý lưu điểm danh
        if (
            isset($_POST['action']) && 
            $_POST['action'] === 'save_diemdanh' && 
            isset($_POST['save_diemdanh_nonce']) && 
            wp_verify_nonce($_POST['save_diemdanh_nonce'], 'save_diemdanh_nonce')
        ) {
            // Lấy dữ liệu từ form
            $lop_id = isset($_POST['lop_id']) ? intval($_POST['lop_id']) : 0;
            $mon_hoc_id = isset($_POST['mon_hoc_id']) ? intval($_POST['mon_hoc_id']) : 0;
            $ngay_diemdanh = isset($_POST['ngay_diemdanh']) ? sanitize_text_field($_POST['ngay_diemdanh']) : '';
            $buoi_hoc = isset($_POST['buoi_hoc']) ? intval($_POST['buoi_hoc']) : 1;
            $ghi_chu = isset($_POST['ghi_chu']) ? sanitize_textarea_field($_POST['ghi_chu']) : '';
            $students = isset($_POST['students']) ? $_POST['students'] : array();
            
            // Kiểm tra dữ liệu
            if (!$lop_id || !$mon_hoc_id || empty($ngay_diemdanh) || empty($students)) {
                return;
            }
            
            // Kiểm tra nếu đã có bản ghi điểm danh
            $existing_id = isset($_POST['existing_id']) ? intval($_POST['existing_id']) : 0;
            
            // Chuẩn bị dữ liệu sinh viên
            $student_data = array();
            foreach ($students as $student_id => $student) {
                if (!isset($student['id']) || !isset($student['status'])) {
                    continue;
                }
                
                $student_data[] = array(
                    'sinh_vien_id' => intval($student['id']),
                    'trang_thai' => sanitize_text_field($student['status']),
                    'ghi_chu' => isset($student['note']) ? sanitize_text_field($student['note']) : ''
                );
            }
            
            // Nếu có ID hiện có, cập nhật post
            if ($existing_id > 0) {
                // Cập nhật ACF fields
                update_field('lop', $lop_id, $existing_id);
                update_field('mon_hoc', $mon_hoc_id, $existing_id);
                update_field('ngay', $ngay_diemdanh, $existing_id);
                update_field('buoi_hoc', $buoi_hoc, $existing_id);
                update_field('ghi_chu', $ghi_chu, $existing_id);
                update_field('sinh_vien_dd', $student_data, $existing_id);
                
                // Cập nhật tiêu đề
                $this->update_diemdanh_title($existing_id);
                
                // Chuyển hướng với thông báo
                wp_redirect(add_query_arg('diemdanh_updated', '1', remove_query_arg('diemdanh_saved', $_SERVER['REQUEST_URI'])));
                exit;
            } else {
                // Tạo post mới
                $post_data = array(
                    'post_title' => sprintf(
                        'Điểm danh %s - %s - %s - Buổi %d', 
                        get_the_title($lop_id), 
                        get_the_title($mon_hoc_id), 
                        date_i18n('d/m/Y', strtotime($ngay_diemdanh)),
                        $buoi_hoc
                    ),
                    'post_status' => 'publish',
                    'post_type' => 'diemdanh'
                );
                
                $post_id = wp_insert_post($post_data);
                
                if (!is_wp_error($post_id)) {
                    // Cập nhật ACF fields
                    update_field('lop', $lop_id, $post_id);
                    update_field('mon_hoc', $mon_hoc_id, $post_id);
                    update_field('ngay', $ngay_diemdanh, $post_id);
                    update_field('buoi_hoc', $buoi_hoc, $post_id);
                    update_field('ghi_chu', $ghi_chu, $post_id);
                    update_field('sinh_vien_dd', $student_data, $post_id);
                    
                    // Cập nhật giảng viên nếu là người dùng hiện tại
                    $current_user_id = get_current_user_id();
                    if ($current_user_id > 0 && current_user_can('edit_posts')) {
                        update_field('giang_vien', $current_user_id, $post_id);
                    }
                    
                    // Chuyển hướng với thông báo
                    wp_redirect(add_query_arg('diemdanh_saved', '1', remove_query_arg('diemdanh_updated', $_SERVER['REQUEST_URI'])));
                    exit;
                }
            }
        }
    }
    
    /**
     * Shortcode hiển thị bảng điều khiển điểm danh
     */
    public function diemdanh_dashboard_shortcode($atts) {
        // Tham số mặc định
        $atts = shortcode_atts(array(
            'default_tab' => '', // Tab mặc định (form, view, stats)
        ), $atts);
        
        // Load template
        ob_start();
        
        $template_path = QLSV_PLUGIN_DIR . 'templates/diemdanh-dashboard.php';
        
        if (file_exists($template_path)) {
            include $template_path;
        } else {
            echo 'Template dashboard điểm danh không tồn tại.';
        }
        
        return ob_get_clean();
    }
    
    /**
     * Thêm page template
     */
    public function add_page_template($templates) {
        $templates['diemdanh-page.php'] = __('Trang Điểm Danh', 'qlsv');
        return $templates;
    }
    
    /**
     * Load page template
     */
    public function load_page_template($template) {
        global $post;
        
        if (!$post) {
            return $template;
        }
        
        // Lấy template được chọn
        $template_name = get_post_meta($post->ID, '_wp_page_template', true);
        
        // Kiểm tra xem có phải template điểm danh không
        if ('diemdanh-page.php' === $template_name) {
            $template_path = QLSV_PLUGIN_DIR . 'pages/diemdanh-page.php';
            
            if (file_exists($template_path)) {
                return $template_path;
            }
        }
        
        // Xử lý trường hợp URL trùng với archive của post type
        if (is_post_type_archive('diemdanh')) {
            $archive_template = QLSV_PLUGIN_DIR . 'pages/diemdanh-page.php';
            if (file_exists($archive_template)) {
                return $archive_template;
            }
        }
        
        // Nếu đang ở trang có slug là "diemdanh"
        if ($post && $post->post_name === 'diemdanh') {
            $diemdanh_template = QLSV_PLUGIN_DIR . 'pages/diemdanh-page.php';
            if (file_exists($diemdanh_template)) {
                return $diemdanh_template;
            }
        }
        
        return $template;
    }
} 