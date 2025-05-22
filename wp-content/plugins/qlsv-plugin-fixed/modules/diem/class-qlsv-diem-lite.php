<?php
/**
 * Class quản lý điểm - phiên bản nhẹ với tối ưu hóa bộ nhớ
 */
class QLSV_Diem_Lite {

    /**
     * Loader để đăng ký các hooks
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
        
        // Filter để tối ưu hóa truy vấn trước khi chạy
        $this->loader->add_action('pre_get_posts', $this, 'optimize_diem_queries');
        
        // Xử lý query vars
        $this->loader->add_filter('query_vars', $this, 'add_query_vars');
        
        // Đăng ký template tùy chỉnh cho post type diem
        $this->loader->add_filter('archive_template', $this, 'register_diem_archive_template');
        $this->loader->add_filter('single_template', $this, 'register_diem_single_template');
        $this->loader->add_filter('page_template', $this, 'register_ket_qua_hoc_tap_template');
        
        // Đăng ký custom rewrite rules
        $this->loader->add_action('init', $this, 'add_rewrite_rules');
    }
    
    /**
     * Thêm các rewrite rules cho tìm kiếm điểm
     */
    public function add_rewrite_rules() {
        // Đăng ký rewrite rule cho tìm kiếm điểm
        add_rewrite_rule(
            'diem/search/?$',
            'index.php?post_type=diem&search_diem=1',
            'top'
        );
        
        // Đăng ký rewrite rule cho tìm kiếm điểm với sinh viên
        add_rewrite_rule(
            'diem/search/sinhvien/([0-9]+)/?$',
            'index.php?post_type=diem&search_diem=1&sinhvien=$matches[1]',
            'top'
        );
        
        // Đăng ký rewrite rule cho tìm kiếm điểm với môn học
        add_rewrite_rule(
            'diem/search/monhoc/([0-9]+)/?$',
            'index.php?post_type=diem&search_diem=1&monhoc=$matches[1]',
            'top'
        );
        
        // Đăng ký rewrite rule cho tìm kiếm điểm với lớp
        add_rewrite_rule(
            'diem/search/lop/([0-9]+)/?$',
            'index.php?post_type=diem&search_diem=1&lop=$matches[1]',
            'top'
        );
        
        // Đăng ký rewrite rule cho tìm kiếm điểm kết hợp
        add_rewrite_rule(
            'diem/search/sinhvien/([0-9]+)/monhoc/([0-9]+)/?$',
            'index.php?post_type=diem&search_diem=1&sinhvien=$matches[1]&monhoc=$matches[2]',
            'top'
        );
        
        add_rewrite_rule(
            'diem/search/sinhvien/([0-9]+)/lop/([0-9]+)/?$',
            'index.php?post_type=diem&search_diem=1&sinhvien=$matches[1]&lop=$matches[2]',
            'top'
        );
        
        add_rewrite_rule(
            'diem/search/monhoc/([0-9]+)/lop/([0-9]+)/?$',
            'index.php?post_type=diem&search_diem=1&monhoc=$matches[1]&lop=$matches[2]',
            'top'
        );
        
        add_rewrite_rule(
            'diem/search/sinhvien/([0-9]+)/monhoc/([0-9]+)/lop/([0-9]+)/?$',
            'index.php?post_type=diem&search_diem=1&sinhvien=$matches[1]&monhoc=$matches[2]&lop=$matches[3]',
            'top'
        );
    }
    
    /**
     * Đăng ký các shortcodes
     */
    private function register_shortcodes() {
        add_shortcode('qlsv_bang_diem_lite', array($this, 'bang_diem_lite_shortcode'));
        add_shortcode('qlsv_tim_kiem_diem_lite', array($this, 'tim_kiem_diem_lite_shortcode'));
    }
    
    /**
     * Đăng ký post type điểm
     */
    public function register_post_type() {
        // Chỉ đăng ký nếu chưa tồn tại
        if (!post_type_exists('diem')) {
            register_post_type('diem', array(
                'public' => true,
                'has_archive' => true,
                'rewrite' => array('slug' => 'diem', 'with_front' => false),
                'supports' => array('title'),
            ));
        }
    }
    
    /**
     * Tối ưu hóa các truy vấn điểm để giảm bộ nhớ sử dụng
     */
    public function optimize_diem_queries($query) {
        // Chỉ áp dụng cho truy vấn điểm
        if (!is_admin() && $query->get('post_type') === 'diem') {
            // Ghi log để debug các tham số query
            error_log('QLSV Debug - Query vars sinhvien: ' . $query->get('sinhvien'));
            error_log('QLSV Debug - URL Query sinhvien: ' . (isset($_GET['sinhvien']) ? $_GET['sinhvien'] : 'not set'));
            error_log('QLSV Debug - Query vars monhoc: ' . $query->get('monhoc'));
            error_log('QLSV Debug - URL Query monhoc: ' . (isset($_GET['monhoc']) ? $_GET['monhoc'] : 'not set'));
            error_log('QLSV Debug - Query vars lop: ' . $query->get('lop'));
            error_log('QLSV Debug - URL Query lop: ' . (isset($_GET['lop']) ? $_GET['lop'] : 'not set'));
            
            // Nếu có các tham số tìm kiếm trong URL, sử dụng chúng
            if (isset($_GET['sinhvien']) && !empty($_GET['sinhvien'])) {
                $query->set('sinhvien', intval($_GET['sinhvien']));
            }
            
            if (isset($_GET['monhoc']) && !empty($_GET['monhoc'])) {
                $query->set('monhoc', intval($_GET['monhoc']));
            }
            
            if (isset($_GET['lop']) && !empty($_GET['lop'])) {
                $query->set('lop', intval($_GET['lop']));
            }
            
            // Giới hạn số lượng bản ghi
            if (!$query->get('posts_per_page') || $query->get('posts_per_page') < 0) {
                $query->set('posts_per_page', 20);
            }
            
            // Giới hạn các trường cần lấy để giảm bộ nhớ
            $query->set('fields', 'ids');
            
            // Tắt các filters không cần thiết
            $query->set('suppress_filters', true);
        }
    }
    
    /**
     * Thêm các biến query để sử dụng trong URL
     */
    public function add_query_vars($vars) {
        $vars[] = 'sinhvien';
        $vars[] = 'monhoc';
        $vars[] = 'lop';
        $vars[] = 'diem_page';
        $vars[] = 'search_diem';
        return $vars;
    }
    
    /**
     * Kiểm tra xem email có phải của sinh viên không
     */
    private function is_student_by_email($email) {
        if (empty($email)) {
            return false;
        }
        
        global $wpdb;
        $posts_table = $wpdb->posts;
        $meta_table = $wpdb->postmeta;
        
        $sql = $wpdb->prepare(
            "SELECT p.ID 
            FROM {$posts_table} p 
            INNER JOIN {$meta_table} pm ON p.ID = pm.post_id 
            WHERE p.post_type = 'sinhvien' 
            AND p.post_status = 'publish' 
            AND pm.meta_key = 'email' 
            AND pm.meta_value = %s 
            LIMIT 1",
            $email
        );
        
        return $wpdb->get_var($sql);
    }

    /**
     * Lấy dữ liệu điểm đã được tối ưu hóa
     */
    private function get_optimized_diem_data($args = array()) {
        // Đặt giá trị mặc định
        $defaults = array(
            'sinhvien_id' => 0,
            'monhoc_id' => 0,
            'lop_id' => 0,
            'limit' => 10,
            'page' => 1,
            'fields' => 'all',
            'orderby' => 'ID',
            'order' => 'DESC'
        );
        
        // Merge với tham số đầu vào
        $args = wp_parse_args($args, $defaults);
        
        // Chuyển đổi kiểu dữ liệu cho các tham số ID
        $args['sinhvien_id'] = intval($args['sinhvien_id']);
        $args['monhoc_id'] = intval($args['monhoc_id']);
        $args['lop_id'] = intval($args['lop_id']);
        
        // Ghi log debug
        error_log('QLSV Debug - get_optimized_diem_data input args: ' . print_r($args, true));
        
        // Tính offset cho phân trang
        $offset = ($args['page'] - 1) * $args['limit'];
        
        // Đảm bảo memory_limit đủ cao
        $current_limit = ini_get('memory_limit');
        $current_limit_int = intval($current_limit);
        if ($current_limit_int < 256 && strpos($current_limit, 'M') !== false) {
            ini_set('memory_limit', '256M');
        }
        
        // Tạo truy vấn SQL trực tiếp thay vì dùng WP_Query để tối ưu hóa
        global $wpdb;
        
        // Bảng posts và postmeta
        $posts_table = $wpdb->posts;
        $meta_table = $wpdb->postmeta;
        
        // Các điều kiện WHERE cho truy vấn
        $where_conditions = array("p.post_type = 'diem'", "p.post_status = 'publish'");
        $join_conditions = array();
        $query_args = array();
        
        // Thêm điều kiện lọc theo sinh viên
        if (!empty($args['sinhvien_id'])) {
            $join_conditions[] = "INNER JOIN {$meta_table} pm_sv ON pm_sv.post_id = p.ID AND pm_sv.meta_key = 'sinh_vien'";
            $where_conditions[] = "pm_sv.meta_value = %d";
            $query_args[] = intval($args['sinhvien_id']);
            error_log('QLSV Debug - Filtering by sinh vien ID: ' . intval($args['sinhvien_id']));
        }
        
        // Thêm điều kiện lọc theo môn học
        if (!empty($args['monhoc_id'])) {
            $join_conditions[] = "INNER JOIN {$meta_table} pm_mh ON pm_mh.post_id = p.ID AND pm_mh.meta_key = 'mon_hoc'";
            $where_conditions[] = "pm_mh.meta_value = %d";
            $query_args[] = intval($args['monhoc_id']);
            error_log('QLSV Debug - Filtering by mon hoc ID: ' . intval($args['monhoc_id']));
        }
        
        // Thêm điều kiện lọc theo lớp
        if (!empty($args['lop_id'])) {
            $join_conditions[] = "INNER JOIN {$meta_table} pm_lop ON pm_lop.post_id = p.ID AND pm_lop.meta_key = 'lop'";
            $where_conditions[] = "pm_lop.meta_value = %d";
            $query_args[] = intval($args['lop_id']);
            error_log('QLSV Debug - Filtering by lop ID: ' . intval($args['lop_id']));
        }
        
        // Debug: Ghi log các thông số tìm kiếm nếu cần
        error_log('QLSV Debug - Search params: ' . print_r($args, true));
        
        // Tạo phần JOIN của câu SQL
        $join_sql = implode(" ", $join_conditions);
        
        // Tạo phần WHERE của câu SQL
        $where_sql = "WHERE " . implode(" AND ", $where_conditions);
        
        // Xử lý orderby và order
        $order_column = 'p.ID'; // Mặc định sắp xếp theo ID
        if ($args['orderby'] === 'sinh_vien') {
            $order_column = 'sv_title';
            $join_conditions[] = "LEFT JOIN {$meta_table} ord_sv ON ord_sv.post_id = p.ID AND ord_sv.meta_key = 'sinh_vien'";
            $join_conditions[] = "LEFT JOIN {$posts_table} ord_sv_p ON ord_sv_p.ID = ord_sv.meta_value";
        } elseif ($args['orderby'] === 'mon_hoc') {
            $order_column = 'mh_title';
            $join_conditions[] = "LEFT JOIN {$meta_table} ord_mh ON ord_mh.post_id = p.ID AND ord_mh.meta_key = 'mon_hoc'";
            $join_conditions[] = "LEFT JOIN {$posts_table} ord_mh_p ON ord_mh_p.ID = ord_mh.meta_value";
        } elseif ($args['orderby'] === 'lop') {
            $order_column = 'lop_title';
            $join_conditions[] = "LEFT JOIN {$meta_table} ord_lop ON ord_lop.post_id = p.ID AND ord_lop.meta_key = 'lop'";
            $join_conditions[] = "LEFT JOIN {$posts_table} ord_lop_p ON ord_lop_p.ID = ord_lop.meta_value";
        } elseif ($args['orderby'] === 'diem_tb') {
            // Tạm thời vẫn sắp xếp theo ID vì cần tính toán điểm TB phía server
            $order_column = 'p.ID';
        }
        
        // Đảm bảo order chỉ là ASC hoặc DESC
        $order_dir = (strtoupper($args['order']) === 'ASC') ? 'ASC' : 'DESC';
        
        // Cập nhật lại join_sql với các điều kiện sắp xếp
        $join_sql = implode(" ", $join_conditions);
        
        // Đếm tổng số kết quả để phân trang
        $count_sql = "SELECT COUNT(DISTINCT p.ID) FROM {$posts_table} p {$join_sql} {$where_sql}";
        
        // Thêm các tham số vào câu SQL đếm
        if (!empty($query_args)) {
            $count_sql = $wpdb->prepare($count_sql, $query_args);
        }
        
        // Debug: Ghi log SQL query
        error_log('QLSV Debug - SQL Count Query: ' . $count_sql);
        
        // Thực hiện truy vấn đếm
        $total_items = $wpdb->get_var($count_sql);
        $total_pages = ceil($total_items / $args['limit']);
        
        // Xây dựng truy vấn chính để lấy IDs
        $sql = "SELECT DISTINCT p.ID FROM {$posts_table} p {$join_sql} {$where_sql} ORDER BY {$order_column} {$order_dir} LIMIT %d OFFSET %d";
        
        // Thêm các tham số vào câu SQL chính
        $all_args = array_merge($query_args, array($args['limit'], $offset));
        $sql = $wpdb->prepare($sql, $all_args);
        
        // Debug: Ghi log SQL query
        error_log('QLSV Debug - SQL Main Query: ' . $sql);
        
        // Thực hiện truy vấn
        $ids = $wpdb->get_col($sql);
        
        // Debug: Ghi log kết quả
        error_log('QLSV Debug - Results Count: ' . count($ids));
        
        // Khởi tạo mảng kết quả
        $results = array(
            'ids' => $ids,
            'total' => $total_items,
            'total_pages' => $total_pages,
            'current_page' => $args['page'],
            'query_args' => $args,
            'time_start' => microtime(true)
        );
        
        // Chỉ lấy IDs nếu được yêu cầu
        if ($args['fields'] === 'ids') {
            $results['time_end'] = microtime(true);
            $results['query_time'] = $results['time_end'] - $results['time_start'];
            return $results;
        }
        
        // Nếu không có kết quả nào, trả về ngay
        if (empty($ids)) {
            $results['items'] = array();
            $results['time_end'] = microtime(true);
            $results['query_time'] = $results['time_end'] - $results['time_start'];
            return $results;
        }
        
        // Lấy dữ liệu chi tiết với một truy vấn duy nhất cho mỗi loại meta
        $id_list = implode(',', $ids);
        
        // Lấy tất cả các meta cần thiết trong một truy vấn
        $meta_keys = array('sinh_vien', 'mon_hoc', 'lop', 'diem_thanh_phan_1_', 'diem_thanh_phan_2_', 'diem_cuoi_ki_');
        $meta_list = "'" . implode("','", $meta_keys) . "'";
        
        $meta_sql = "
            SELECT post_id, meta_key, meta_value
            FROM {$meta_table}
            WHERE post_id IN ({$id_list})
            AND meta_key IN ({$meta_list})
        ";
        
        $meta_results = $wpdb->get_results($meta_sql);
        
        // Tổ chức meta theo post_id
        $meta_data = array();
        foreach ($meta_results as $meta) {
            $meta_data[$meta->post_id][$meta->meta_key] = $meta->meta_value;
        }
        
        // Lấy thông tin sinh viên, môn học, lớp trong một truy vấn
        $entity_ids = array();
        foreach ($meta_data as $post_id => $metas) {
            if (!empty($metas['sinh_vien'])) $entity_ids[] = $metas['sinh_vien'];
            if (!empty($metas['mon_hoc'])) $entity_ids[] = $metas['mon_hoc'];
            if (!empty($metas['lop'])) $entity_ids[] = $metas['lop'];
        }
        
        $entity_ids = array_unique(array_filter($entity_ids));
        
        // Nếu không có entity nào, trả về mảng rỗng
        if (empty($entity_ids)) {
            $results['items'] = array();
            $results['time_end'] = microtime(true);
            $results['query_time'] = $results['time_end'] - $results['time_start'];
            return $results;
        }
        
        $entity_list = implode(',', $entity_ids);
        $titles_sql = "
            SELECT p.ID, p.post_title, p.post_type, pm.meta_key, pm.meta_value
            FROM {$posts_table} p
            LEFT JOIN {$meta_table} pm ON p.ID = pm.post_id AND pm.meta_key IN ('ma_sv', 'email', 'ngay_sinh', 'so_tin_chi', 'so_tiet_ly_thuyet', 'so_tiet_thuc_hanh')
            WHERE p.ID IN ({$entity_list})
        ";
        
        $titles_results = $wpdb->get_results($titles_sql);
        
        // Tổ chức titles và meta theo ID và post_type
        $entities_data = array();
        foreach ($titles_results as $row) {
            if (!isset($entities_data[$row->ID])) {
                $entities_data[$row->ID] = array(
                    'title' => $row->post_title,
                    'type' => $row->post_type,
                    'meta' => array()
                );
            }
            
            if (!empty($row->meta_key)) {
                $entities_data[$row->ID]['meta'][$row->meta_key] = $row->meta_value;
            }
        }
        
        // Lấy dữ liệu chi tiết từ các mảng đã tổ chức
        $items = array();
        
        foreach ($ids as $post_id) {
            $metas = isset($meta_data[$post_id]) ? $meta_data[$post_id] : array();
            
            $sinh_vien_id = isset($metas['sinh_vien']) ? $metas['sinh_vien'] : 0;
            $mon_hoc_id = isset($metas['mon_hoc']) ? $metas['mon_hoc'] : 0;
            $lop_id = isset($metas['lop']) ? $metas['lop'] : 0;
            $diem_tp1 = isset($metas['diem_thanh_phan_1_']) ? $metas['diem_thanh_phan_1_'] : '';
            $diem_tp2 = isset($metas['diem_thanh_phan_2_']) ? $metas['diem_thanh_phan_2_'] : '';
            $diem_cuoi_ki = isset($metas['diem_cuoi_ki_']) ? $metas['diem_cuoi_ki_'] : '';
            
            // Lấy thông tin từ mảng entities_data
            $sinh_vien_data = isset($entities_data[$sinh_vien_id]) ? $entities_data[$sinh_vien_id] : array('title' => '', 'meta' => array());
            $mon_hoc_data = isset($entities_data[$mon_hoc_id]) ? $entities_data[$mon_hoc_id] : array('title' => '', 'meta' => array());
            $lop_data = isset($entities_data[$lop_id]) ? $entities_data[$lop_id] : array('title' => '', 'meta' => array());
            
            $sinh_vien_name = $sinh_vien_data['title'];
            $mon_hoc_name = $mon_hoc_data['title'];
            $lop_name = $lop_data['title'];
            
            // Lấy metadata bổ sung
            $ma_sv = isset($sinh_vien_data['meta']['ma_sv']) ? $sinh_vien_data['meta']['ma_sv'] : '';
            $email = isset($sinh_vien_data['meta']['email']) ? $sinh_vien_data['meta']['email'] : '';
            $ngay_sinh = isset($sinh_vien_data['meta']['ngay_sinh']) ? $sinh_vien_data['meta']['ngay_sinh'] : '';
            $so_tin_chi = isset($mon_hoc_data['meta']['so_tin_chi']) ? $mon_hoc_data['meta']['so_tin_chi'] : '';
            $so_tiet_ly_thuyet = isset($mon_hoc_data['meta']['so_tiet_ly_thuyet']) ? $mon_hoc_data['meta']['so_tiet_ly_thuyet'] : '';
            $so_tiet_thuc_hanh = isset($mon_hoc_data['meta']['so_tiet_thuc_hanh']) ? $mon_hoc_data['meta']['so_tiet_thuc_hanh'] : '';
            
            // Tính điểm trung bình
            $diem_tb = '';
            if (is_numeric($diem_tp1) && is_numeric($diem_tp2) && is_numeric($diem_cuoi_ki)) {
                $diem_tb = round(($diem_tp1 * 0.2 + $diem_tp2 * 0.2 + $diem_cuoi_ki * 0.6), 2);
            }
            
            // Xếp loại
            $xep_loai = '';
            if ($diem_tb !== '') {
                if ($diem_tb >= 8.5) $xep_loai = 'Giỏi';
                elseif ($diem_tb >= 7) $xep_loai = 'Khá';
                elseif ($diem_tb >= 5.5) $xep_loai = 'Trung bình';
                else $xep_loai = 'Yếu';
            }
            
            // Đánh giá đỗ/trượt
            $danhgia = '';
            if ($diem_tb !== '') {
                $danhgia = ($diem_tb >= 5.5) ? 'Đạt' : 'Không đạt';
            }
            
            // Thêm vào kết quả
            $items[] = array(
                'ID' => $post_id,
                'sinh_vien_id' => $sinh_vien_id,
                'sinh_vien_name' => $sinh_vien_name,
                'ma_sv' => $ma_sv,
                'email' => $email,
                'ngay_sinh' => $ngay_sinh,
                'mon_hoc_id' => $mon_hoc_id,
                'mon_hoc_name' => $mon_hoc_name,
                'so_tin_chi' => $so_tin_chi,
                'so_tiet_ly_thuyet' => $so_tiet_ly_thuyet,
                'so_tiet_thuc_hanh' => $so_tiet_thuc_hanh,
                'lop_id' => $lop_id,
                'lop_name' => $lop_name,
                'diem_tp1' => $diem_tp1,
                'diem_tp2' => $diem_tp2,
                'diem_cuoi_ki' => $diem_cuoi_ki,
                'diem_tb' => $diem_tb,
                'xep_loai' => $xep_loai,
                'danh_gia' => $danhgia
            );
        }
        
        // Sắp xếp lại theo điểm TB nếu cần
        if ($args['orderby'] === 'diem_tb') {
            usort($items, function($a, $b) use ($order_dir) {
                if ($a['diem_tb'] === '' && $b['diem_tb'] === '') return 0;
                if ($a['diem_tb'] === '') return 1;
                if ($b['diem_tb'] === '') return -1;
                
                if ($order_dir === 'ASC') {
                    return $a['diem_tb'] <=> $b['diem_tb'];
                } else {
                    return $b['diem_tb'] <=> $a['diem_tb'];
                }
            });
        }
        
        $results['items'] = $items;
        $results['time_end'] = microtime(true);
        $results['query_time'] = $results['time_end'] - $results['time_start'];
        
        return $results;
    }
    
    /**
     * Hiển thị bảng điểm với tối ưu hóa bộ nhớ
     */
    private function display_optimized_diem_table($diem_data) {
        $output = '<div class="diem-list-container">';
        
        if (empty($diem_data['items'])) {
            $output .= '<p>Không có dữ liệu điểm nào phù hợp với tiêu chí tìm kiếm.</p>';
        } else {
            // Thông tin tổng quan về kết quả
            $output .= '<div class="diem-summary" style="background-color: #f8f9fa; padding: 15px; border-left: 4px solid #0073aa; margin-bottom: 20px; border-radius: 4px;">
                <h3 style="margin-top: 0; color: #0073aa;">Thông tin kết quả</h3>
                <div style="display: flex; flex-wrap: wrap; gap: 20px;">
                    <div style="flex: 1; min-width: 200px;"><strong>Số lượng kết quả:</strong> ' . $diem_data['total'] . '</div>
                    <div style="flex: 1; min-width: 200px;"><strong>Trang:</strong> ' . $diem_data['current_page'] . '/' . $diem_data['total_pages'] . '</div>
                    <div style="flex: 1; min-width: 200px;"><strong>Thời gian truy vấn:</strong> ' . round($diem_data['query_time'], 4) . ' giây</div>
                </div>
            </div>';
            
            // Bảng điểm chính
            $output .= '<div class="diem-table-container" style="overflow-x: auto;">
                <table class="diem-table" style="width: 100%; border-collapse: collapse; margin-top: 20px; border: 1px solid #dee2e6;">
                <thead>
                    <tr style="background-color: #f2f2f2;">
                        <th style="border: 1px solid #ddd; padding: 12px; text-align: center; font-weight: 600;">STT</th>
                        <th style="border: 1px solid #ddd; padding: 12px; text-align: left; font-weight: 600;">Mã SV</th>
                        <th style="border: 1px solid #ddd; padding: 12px; text-align: left; font-weight: 600;">Họ và tên</th>
                        <th style="border: 1px solid #ddd; padding: 12px; text-align: left; font-weight: 600;">Lớp</th>
                        <th style="border: 1px solid #ddd; padding: 12px; text-align: left; font-weight: 600;">Môn học</th>
                        <th style="border: 1px solid #ddd; padding: 12px; text-align: center; font-weight: 600;">Số TC</th>
                        <th style="border: 1px solid #ddd; padding: 12px; text-align: center; font-weight: 600;">TP1 (20%)</th>
                        <th style="border: 1px solid #ddd; padding: 12px; text-align: center; font-weight: 600;">TP2 (20%)</th>
                        <th style="border: 1px solid #ddd; padding: 12px; text-align: center; font-weight: 600;">Cuối kỳ (60%)</th>
                        <th style="border: 1px solid #ddd; padding: 12px; text-align: center; font-weight: 600;">TB</th>
                        <th style="border: 1px solid #ddd; padding: 12px; text-align: center; font-weight: 600;">Xếp loại</th>
                        <th style="border: 1px solid #ddd; padding: 12px; text-align: center; font-weight: 600;">Kết quả</th>
                    </tr>
                </thead>
                <tbody>';
                
            $stt = ($diem_data['current_page'] - 1) * 10 + 1;
            $total_sinhvien = 0;
            $total_dat = 0;
            $total_khongdat = 0;
            
            foreach ($diem_data['items'] as $item) {
                $total_sinhvien++;
                
                // Xác định màu nền dựa trên kết quả
                $row_style = '';
                if (!empty($item['danh_gia'])) {
                    if ($item['danh_gia'] == 'Đạt') {
                        $row_style = 'background-color: #f0fff0;';
                        $total_dat++;
                    } else {
                        $row_style = 'background-color: #fff0f0;';
                        $total_khongdat++;
                    }
                }
                
                $output .= '<tr style="' . $row_style . '">
                    <td style="border: 1px solid #ddd; padding: 10px; text-align: center;">' . $stt . '</td>
                    <td style="border: 1px solid #ddd; padding: 10px;">' . esc_html($item['ma_sv']) . '</td>
                    <td style="border: 1px solid #ddd; padding: 10px; font-weight: 500;">' . esc_html($item['sinh_vien_name']) . '</td>
                    <td style="border: 1px solid #ddd; padding: 10px;">' . esc_html($item['lop_name']) . '</td>
                    <td style="border: 1px solid #ddd; padding: 10px;">' . esc_html($item['mon_hoc_name']) . '</td>
                    <td style="border: 1px solid #ddd; padding: 10px; text-align: center;">' . esc_html($item['so_tin_chi']) . '</td>
                    <td style="border: 1px solid #ddd; padding: 10px; text-align: center; font-weight: bold;">' . esc_html($item['diem_tp1']) . '</td>
                    <td style="border: 1px solid #ddd; padding: 10px; text-align: center; font-weight: bold;">' . esc_html($item['diem_tp2']) . '</td>
                    <td style="border: 1px solid #ddd; padding: 10px; text-align: center; font-weight: bold;">' . esc_html($item['diem_cuoi_ki']) . '</td>
                    <td style="border: 1px solid #ddd; padding: 10px; text-align: center; font-weight: bold; ' . ($item['diem_tb'] < 5.5 ? 'color: #dc3545;' : 'color: #28a745;') . '">' . esc_html($item['diem_tb']) . '</td>
                    <td style="border: 1px solid #ddd; padding: 10px; text-align: center; ' . $this->get_xep_loai_style($item['xep_loai']) . '">' . esc_html($item['xep_loai']) . '</td>
                    <td style="border: 1px solid #ddd; padding: 10px; text-align: center; ' . ($item['danh_gia'] == 'Đạt' ? 'background-color: #d4edda; color: #155724;' : 'background-color: #f8d7da; color: #721c24;') . ' font-weight: bold;">' . esc_html($item['danh_gia']) . '</td>
                </tr>';
                $stt++;
            }
            
            $output .= '</tbody></table></div>';
            
            // Hiển thị phân trang
            if ($diem_data['total_pages'] > 1) {
                $output .= $this->render_pagination($diem_data);
            }
            
            // Hiển thị thống kê
            $output .= '<div class="diem-statistics" style="display: flex; flex-wrap: wrap; gap: 20px; margin-top: 30px; padding: 20px; background-color: #f8f9fa; border-radius: 6px; border: 1px solid #dee2e6;">';
            
            // Thống kê xếp loại
            $output .= '<div style="flex: 1; min-width: 300px;">
                <h3 style="margin-top: 0; margin-bottom: 15px; color: #0073aa;">Thống kê xếp loại</h3>
                <div style="display: flex; flex-wrap: wrap; gap: 10px;">';
            
            $stats = array(
                'Giỏi' => 0,
                'Khá' => 0,
                'Trung bình' => 0,
                'Yếu' => 0
            );
            
            foreach ($diem_data['items'] as $item) {
                if (!empty($item['xep_loai']) && isset($stats[$item['xep_loai']])) {
                    $stats[$item['xep_loai']]++;
                }
            }
            
            foreach ($stats as $loai => $count) {
                $percent = ($total_sinhvien > 0) ? round(($count / $total_sinhvien) * 100, 1) : 0;
                $style = $this->get_xep_loai_style($loai);
                $output .= '<div style="flex: 1; min-width: 100px; padding: 10px; border-radius: 4px; text-align: center; ' . $style . '">
                    <div style="font-size: 24px; font-weight: bold;">' . $count . '</div>
                    <div>' . $loai . ' (' . $percent . '%)</div>
                </div>';
            }
            
            $output .= '</div></div>';
            
            // Thống kê tỷ lệ đạt
            $percent_dat = ($total_sinhvien > 0) ? round(($total_dat / $total_sinhvien) * 100, 1) : 0;
            $percent_khongdat = 100 - $percent_dat;
            
            $output .= '<div style="flex: 1; min-width: 300px;">
                <h3 style="margin-top: 0; margin-bottom: 15px; color: #0073aa;">Thống kê kết quả</h3>
                <div style="display: flex; flex-wrap: wrap; gap: 10px;">
                    <div style="flex: 1; min-width: 120px; padding: 10px; border-radius: 4px; text-align: center; background-color: #d4edda; color: #155724; font-weight: bold;">
                        <div style="font-size: 24px; font-weight: bold;">' . $total_dat . '</div>
                        <div>Đạt (' . $percent_dat . '%)</div>
                    </div>
                    <div style="flex: 1; min-width: 120px; padding: 10px; border-radius: 4px; text-align: center; background-color: #f8d7da; color: #721c24; font-weight: bold;">
                        <div style="font-size: 24px; font-weight: bold;">' . $total_khongdat . '</div>
                        <div>Không đạt (' . $percent_khongdat . '%)</div>
                    </div>
                </div>
                
                <div style="margin-top: 15px; padding: 10px; background-color: #e7f5ff; border-radius: 4px; border-left: 4px solid #0073aa;">
                    <p style="margin: 0;"><strong>Tổng số sinh viên:</strong> ' . $total_sinhvien . '</p>
                </div>
            </div>';
            
            $output .= '</div>'; // End diem-statistics
        }
        
        $output .= '</div>'; // End diem-list-container
        
        return $output;
    }
    
    /**
     * Tạo CSS cho xếp loại
     */
    private function get_xep_loai_style($xep_loai) {
        switch ($xep_loai) {
            case 'Giỏi':
                return 'background-color: #d4edda; color: #155724; font-weight: bold;';
            case 'Khá':
                return 'background-color: #d1ecf1; color: #0c5460; font-weight: bold;';
            case 'Trung bình':
                return 'background-color: #fff3cd; color: #856404; font-weight: bold;';
            case 'Yếu':
                return 'background-color: #f8d7da; color: #721c24; font-weight: bold;';
            default:
                return '';
        }
    }
    
    /**
     * Tạo thống kê theo xếp loại
     */
    private function render_xep_loai_statistics($items) {
        $stats = array(
            'Giỏi' => 0,
            'Khá' => 0,
            'Trung bình' => 0,
            'Yếu' => 0,
            'Chưa có' => 0
        );
        
        // Tính toán thống kê
        foreach ($items as $item) {
            if (!empty($item['xep_loai'])) {
                if (isset($stats[$item['xep_loai']])) {
                    $stats[$item['xep_loai']]++;
                }
            } else {
                $stats['Chưa có']++;
            }
        }
        
        // Chỉ hiển thị thống kê nếu có ít nhất 1 điểm
        if (count($items) > 0) {
            $output = '<div class="diem-statistics" style="margin-top: 30px; padding: 15px; background-color: #f8f9fa; border-radius: 6px;">';
            $output .= '<h3 style="margin-top: 0;">Thống kê xếp loại</h3>';
            $output .= '<div style="display: flex; flex-wrap: wrap; gap: 15px;">';
            
            foreach ($stats as $loai => $count) {
                if ($count > 0) {
                    $style = $this->get_xep_loai_style($loai);
                    $output .= '<div style="flex: 1; min-width: 120px; padding: 10px; border-radius: 4px; text-align: center; ' . $style . '">
                        <div style="font-size: 24px; font-weight: bold;">' . $count . '</div>
                        <div>' . $loai . '</div>
                    </div>';
                }
            }
            
            $output .= '</div>';
            $output .= '</div>';
            
            return $output;
        }
        
        return '';
    }
    
    /**
     * Tạo phân trang
     */
    private function render_pagination($data) {
        $output = '<div class="pagination" style="margin-top: 20px; text-align: center;">';
        
        // URL hiện tại với tất cả các tham số
        $current_url = add_query_arg(array());
        
        // Hiển thị nút Previous
        if ($data['current_page'] > 1) {
            $prev_url = add_query_arg('diem_page', $data['current_page'] - 1, $current_url);
            $output .= '<a href="' . esc_url($prev_url) . '" class="page-link" style="margin: 0 5px; padding: 5px 10px; border: 1px solid #ddd; text-decoration: none;">&laquo; Trước</a>';
        }
        
        // Hiển thị các trang
        $start_page = max(1, $data['current_page'] - 2);
        $end_page = min($data['total_pages'], $data['current_page'] + 2);
        
        for ($i = $start_page; $i <= $end_page; $i++) {
            $page_url = add_query_arg('diem_page', $i, $current_url);
            $current_class = ($i == $data['current_page']) ? 'current-page' : '';
            $style = ($i == $data['current_page']) ? ' background-color: #f0f0f0; font-weight: bold;' : '';
            $output .= '<a href="' . esc_url($page_url) . '" class="page-link ' . $current_class . '" style="margin: 0 5px; padding: 5px 10px; border: 1px solid #ddd; text-decoration: none;' . $style . '">' . $i . '</a>';
        }
        
        // Hiển thị nút Next
        if ($data['current_page'] < $data['total_pages']) {
            $next_url = add_query_arg('diem_page', $data['current_page'] + 1, $current_url);
            $output .= '<a href="' . esc_url($next_url) . '" class="page-link" style="margin: 0 5px; padding: 5px 10px; border: 1px solid #ddd; text-decoration: none;">Sau &raquo;</a>';
        }
        
        $output .= '</div>';
        
        return $output;
    }
    
    /**
     * Shortcode hiển thị bảng điểm đã được tối ưu hóa
     */
    public function bang_diem_lite_shortcode($atts) {
        // Kiểm tra quyền truy cập
        if (!is_user_logged_in()) {
            return '<div class="qlsv-thong-bao"><p>Bạn cần đăng nhập để xem bảng điểm.</p></div>';
        }
        
        // Lấy thông tin người dùng hiện tại
        $current_user = wp_get_current_user();
        $user_roles = $current_user->roles;
        $is_admin = in_array('administrator', $user_roles);
        $is_teacher = in_array('giaovien', $user_roles);
        $is_student = in_array('student', $user_roles);
        
        // Nếu chưa có vai trò student, kiểm tra theo email
        if (!$is_student) {
            $student_id = $this->is_student_by_email($current_user->user_email);
            $is_student = ($student_id !== false);
        }
        
        // Tham số mặc định
        $atts = shortcode_atts(array(
            'sinhvien_id' => 0,
            'monhoc_id' => 0,
            'lop_id' => 0,
            'limit' => 10,
            'page' => isset($_GET['diem_page']) ? intval($_GET['diem_page']) : 1
        ), $atts);
        
        // Đảm bảo page > 0
        if ($atts['page'] < 1) $atts['page'] = 1;
        
        // Nếu là sinh viên và không phải admin/giáo viên, chỉ cho xem điểm của mình
        if ($is_student && !$is_admin && !$is_teacher) {
            if ($student_id === false) {
                // Tìm ID sinh viên dựa trên email
                $student_id = $this->is_student_by_email($current_user->user_email);
            }
            
            if ($student_id) {
                $atts['sinhvien_id'] = $student_id;
            } else {
                return '<div class="qlsv-thong-bao"><p>Không tìm thấy thông tin sinh viên cho tài khoản này.</p></div>';
            }
        }
        
        // Lấy dữ liệu điểm
        $diem_data = $this->get_optimized_diem_data($atts);
        
        // Hiển thị bảng điểm
        return $this->display_optimized_diem_table($diem_data);
    }
    
    /**
     * Shortcode tạo form tìm kiếm điểm đã được tối ưu hóa
     */
    public function tim_kiem_diem_lite_shortcode($atts) {
        // Kiểm tra quyền truy cập
        if (!is_user_logged_in()) {
            return '<div class="qlsv-thong-bao qlsv-error"><p>Bạn cần đăng nhập để tìm kiếm bảng điểm.</p><p><a href="' . esc_url(wp_login_url('http://localhost/wordpressQLSV/')) . '" class="button button-primary">Đăng nhập</a></p></div>';
        }
        
        // Lấy thông tin người dùng hiện tại
        $current_user = wp_get_current_user();
        $user_roles = $current_user->roles;
        $is_admin = in_array('administrator', $user_roles);
        $is_teacher = in_array('giaovien', $user_roles);
        $is_student = in_array('student', $user_roles);
        
        // Nếu chưa có vai trò student, kiểm tra theo email
        if (!$is_student) {
            $student_id = $this->is_student_by_email($current_user->user_email);
            $is_student = ($student_id !== false);
        }
        
        // Nếu là sinh viên, chỉ cho xem điểm của mình
        if ($is_student) {
            if (!isset($student_id)) {
                $student_id = $this->is_student_by_email($current_user->user_email);
            }
            
            if ($student_id) {
                // Khởi tạo output buffer
                ob_start();
                
                // Hiển thị thông tin sinh viên
                $student_post = get_post($student_id);
                if ($student_post) {
                    echo '<div class="student-info-card" style="margin-bottom: 30px; padding: 20px; background-color: #f8f9fa; border-radius: 8px; border-left: 4px solid #0073aa;">';
                    echo '<h3 style="margin-top: 0;">Thông tin sinh viên</h3>';
                    
                    // Lấy và hiển thị thông tin sinh viên
                    $email = get_post_meta($student_id, 'email', true);
                    $ma_sv = get_post_meta($student_id, 'ma_sv', true);
                    $ngay_sinh = get_post_meta($student_id, 'ngay_sinh', true);
                    $lop_id = get_post_meta($student_id, 'lop', true);
                    $lop_name = '';
                    
                    if ($lop_id) {
                        $lop_post = get_post($lop_id);
                        if ($lop_post) {
                            $lop_name = $lop_post->post_title;
                        }
                    }
                    
                    echo '<div style="display: flex; flex-wrap: wrap; gap: 20px;">';
                    echo '<div style="flex: 1; min-width: 200px;"><strong>Họ và tên:</strong> ' . esc_html($student_post->post_title) . '</div>';
                    echo '<div style="flex: 1; min-width: 200px;"><strong>Mã sinh viên:</strong> ' . esc_html($ma_sv) . '</div>';
                    echo '<div style="flex: 1; min-width: 200px;"><strong>Email:</strong> ' . esc_html($email) . '</div>';
                    echo '<div style="flex: 1; min-width: 200px;"><strong>Ngày sinh:</strong> ' . esc_html($ngay_sinh) . '</div>';
                    echo '<div style="flex: 1; min-width: 200px;"><strong>Lớp:</strong> ' . esc_html($lop_name) . '</div>';
                    echo '</div>';
                    
                    echo '</div>';
                }
                
                // Hiển thị điểm sinh viên với tiêu đề
                echo '<h3>Bảng điểm sinh viên</h3>';
                echo '<p>Dưới đây là bảng điểm chi tiết của bạn:</p>';
                echo $this->bang_diem_lite_shortcode(array('sinhvien_id' => $student_id));
                
                return ob_get_clean();
            } else {
                return '<div class="qlsv-thong-bao qlsv-error"><p>Không tìm thấy thông tin sinh viên cho tài khoản này.</p></div>';
            }
        }
        
        // Cho phép cả admin và giáo viên sử dụng form tìm kiếm
        if ($is_admin || $is_teacher) {
            // Khởi tạo output buffer
            ob_start();
            
            // Hiển thị form tìm kiếm và nhập điểm với giao diện cải tiến
            echo '<div class="tabs-container" style="background: #fff; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); margin-bottom: 30px;">
                <ul class="tabs-nav" style="list-style: none; padding: 0; margin: 0; display: flex; border-bottom: 1px solid #ddd; background: #f8f9fa; border-radius: 8px 8px 0 0;">
                    <li class="tab-active" style="margin-right: 0;"><a href="#tab-tim-kiem" style="display: block; padding: 15px 20px; text-decoration: none; color: #333; border-radius: 8px 0 0 0; font-weight: 500;">Tìm kiếm điểm</a></li>
                    <li><a href="#tab-nhap-diem" style="display: block; padding: 15px 20px; text-decoration: none; color: #333; font-weight: 500;">Nhập điểm</a></li>
                </ul>
                <div class="tabs-content" style="padding: 20px;">
                    <div id="tab-tim-kiem" class="tab-content tab-active">';
            
            // Lấy các tham số tìm kiếm từ URL
            $selected_student = isset($_GET['sinhvien']) ? intval($_GET['sinhvien']) : 0;
            $selected_course = isset($_GET['monhoc']) ? intval($_GET['monhoc']) : 0;
            $selected_class = isset($_GET['lop']) ? intval($_GET['lop']) : 0;
            
            // Form tìm kiếm sửa đổi - dùng URL tới archive-diem.php
            echo '<form method="get" action="' . esc_url(home_url('/diem/')) . '" class="search-diem-form" style="margin-bottom: 30px; background: #f8f9fa; padding: 20px; border-radius: 8px;">
                <h3 style="margin-top: 0; margin-bottom: 15px; color: #0073aa;">Tìm kiếm kết quả học tập</h3>
                <div style="display: flex; flex-wrap: wrap; gap: 15px;">
                    <div style="flex: 1; min-width: 250px;">
                        <label for="sinhvien" style="display: block; margin-bottom: 8px; font-weight: 500;">Sinh viên:</label>
                        <select name="sinhvien" id="sinhvien" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px;">
                            <option value="">-- Tất cả sinh viên --</option>';
            
            // Lấy danh sách sinh viên bằng SQL trực tiếp
            global $wpdb;
            $posts_table = $wpdb->posts;
            
            // Nếu đã chọn lớp, chỉ hiển thị sinh viên của lớp đó
            if ($selected_class) {
                $students = $this->get_students_by_class($selected_class);
                
                foreach ($students as $student) {
                    $selected = ($selected_student == $student->ID) ? 'selected' : '';
                    echo '<option value="' . $student->ID . '" ' . $selected . '>' . $student->post_title . '</option>';
                }
            } else {
                // Nếu chưa chọn lớp, hiển thị tối đa 100 sinh viên
                $sql = "
                    SELECT ID, post_title 
                    FROM {$posts_table} 
                    WHERE post_type = 'sinhvien' 
                    AND post_status = 'publish' 
                    ORDER BY post_title ASC 
                    LIMIT 100
                ";
                
                $students = $wpdb->get_results($sql);
                
                foreach ($students as $student) {
                    $selected = ($selected_student == $student->ID) ? 'selected' : '';
                    echo '<option value="' . $student->ID . '" ' . $selected . '>' . $student->post_title . '</option>';
                }
            }
            
            echo '</select>
                </div>
                
                <div style="flex: 1; min-width: 250px;">
                    <label for="monhoc" style="display: block; margin-bottom: 8px; font-weight: 500;">Môn học:</label>
                    <select name="monhoc" id="monhoc" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px;">
                        <option value="">-- Tất cả môn học --</option>';
            
            // Lấy danh sách môn học bằng SQL trực tiếp
            $sql = "
                SELECT ID, post_title 
                FROM {$posts_table} 
                WHERE post_type = 'monhoc' 
                AND post_status = 'publish' 
                ORDER BY post_title ASC 
                LIMIT 100
            ";
            
            $courses = $wpdb->get_results($sql);
            
            foreach ($courses as $course) {
                $selected = ($selected_course == $course->ID) ? 'selected' : '';
                echo '<option value="' . $course->ID . '" ' . $selected . '>' . $course->post_title . '</option>';
            }
            
            echo '</select>
                </div>
                
                <div style="flex: 1; min-width: 250px;">
                    <label for="lop" style="display: block; margin-bottom: 8px; font-weight: 500;">Lớp:</label>
                    <select name="lop" id="lop" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px;">
                        <option value="">-- Tất cả lớp --</option>';
            
            // Lấy danh sách lớp bằng SQL trực tiếp
            $sql = "
                SELECT ID, post_title 
                FROM {$posts_table} 
                WHERE post_type = 'lop' 
                AND post_status = 'publish' 
                ORDER BY post_title ASC 
                LIMIT 100
            ";
            
            $classes = $wpdb->get_results($sql);
            
            foreach ($classes as $class) {
                $selected = ($selected_class == $class->ID) ? 'selected' : '';
                echo '<option value="' . $class->ID . '" ' . $selected . '>' . $class->post_title . '</option>';
            }
            
            echo '</select>
                </div>
                
                <div style="flex: 1; align-self: flex-end; min-width: 250px;">
                    <input type="hidden" name="search_diem" value="1">
                    <button type="submit" style="width: 100%; background: #0073aa; border: none; color: white; padding: 12px; cursor: pointer; border-radius: 4px; font-weight: 500;">Tìm kiếm</button>
                </div>
            </div>
            </form>';
            
            // Thông tin về phiên bản tối ưu
            echo '<div class="version-info" style="margin-bottom: 20px; padding: 12px; background-color: #e7f5ff; border-radius: 4px; border-left: 4px solid #0073aa;">
                <p style="margin: 0;"><strong>Phiên bản tối ưu hóa:</strong> Đang sử dụng phiên bản nhẹ của module điểm để tránh lỗi bộ nhớ.</p>
            </div>';
            
            // Hiển thị kết quả tìm kiếm
            $search_atts = array(
                'limit' => 10,
                'page' => isset($_GET['diem_page']) ? intval($_GET['diem_page']) : 1
            );
            
            if ($selected_student) {
                $search_atts['sinhvien_id'] = $selected_student;
            }
            
            if ($selected_course) {
                $search_atts['monhoc_id'] = $selected_course;
            }
            
            if ($selected_class) {
                $search_atts['lop_id'] = $selected_class;
            }
            
            // Hiển thị tiêu đề kết quả nếu có tìm kiếm
            if ($selected_student || $selected_course || $selected_class || isset($_GET['search_diem'])) {
                echo '<h3 style="margin-top: 30px; border-bottom: 2px solid #0073aa; padding-bottom: 8px;">Kết quả tìm kiếm</h3>';
            }
            
            // Lấy và hiển thị kết quả tìm kiếm
            $diem_data = $this->get_optimized_diem_data($search_atts);
            echo $this->display_optimized_diem_table($diem_data);
            
            echo '</div>'; // End tab-tim-kiem
            
            // Tab nhập điểm - Chỉ giáo viên và admin mới được nhập điểm
            echo '<div id="tab-nhap-diem" class="tab-content">';
            if ($is_admin || $is_teacher) {
                echo '<div class="nhap-diem-form" style="background: #f8f9fa; padding: 20px; border-radius: 8px; margin-bottom: 20px;">
                    <h3 style="margin-top: 0; margin-bottom: 15px; color: #0073aa;">Nhập điểm mới</h3>
                    <form method="post" action="" style="display: flex; flex-direction: column; gap: 15px;">
                        <div style="display: flex; flex-wrap: wrap; gap: 15px;">
                            <div style="flex: 1; min-width: 250px;">
                                <label for="sv_id" style="display: block; margin-bottom: 8px; font-weight: 500;">Sinh viên:</label>
                                <select name="sv_id" id="sv_id" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px;" required>
                                    <option value="">-- Chọn sinh viên --</option>';
                
                // Lấy danh sách sinh viên
                $sql = "
                    SELECT ID, post_title 
                    FROM {$posts_table} 
                    WHERE post_type = 'sinhvien' 
                    AND post_status = 'publish' 
                    ORDER BY post_title ASC 
                    LIMIT 100
                ";
                
                $students = $wpdb->get_results($sql);
                
                foreach ($students as $student) {
                    echo '<option value="' . $student->ID . '">' . $student->post_title . '</option>';
                }
                
                echo '</select>
                            </div>
                            
                            <div style="flex: 1; min-width: 250px;">
                                <label for="mh_id" style="display: block; margin-bottom: 8px; font-weight: 500;">Môn học:</label>
                                <select name="mh_id" id="mh_id" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px;" required>
                                    <option value="">-- Chọn môn học --</option>';
                
                // Lấy danh sách môn học
                foreach ($courses as $course) {
                    echo '<option value="' . $course->ID . '">' . $course->post_title . '</option>';
                }
                
                echo '</select>
                            </div>
                            
                            <div style="flex: 1; min-width: 250px;">
                                <label for="lop_id" style="display: block; margin-bottom: 8px; font-weight: 500;">Lớp:</label>
                                <select name="lop_id" id="lop_id" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px;" required>
                                    <option value="">-- Chọn lớp --</option>';
                
                // Lấy danh sách lớp
                foreach ($classes as $class) {
                    echo '<option value="' . $class->ID . '">' . $class->post_title . '</option>';
                }
                
                echo '</select>
                            </div>
                        </div>
                        
                        <div style="display: flex; flex-wrap: wrap; gap: 15px;">
                            <div style="flex: 1; min-width: 150px;">
                                <label for="diem_tp1" style="display: block; margin-bottom: 8px; font-weight: 500;">Điểm TP1 (20%):</label>
                                <input type="number" name="diem_tp1" id="diem_tp1" min="0" max="10" step="0.1" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px;" required>
                            </div>
                            
                            <div style="flex: 1; min-width: 150px;">
                                <label for="diem_tp2" style="display: block; margin-bottom: 8px; font-weight: 500;">Điểm TP2 (20%):</label>
                                <input type="number" name="diem_tp2" id="diem_tp2" min="0" max="10" step="0.1" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px;" required>
                            </div>
                            
                            <div style="flex: 1; min-width: 150px;">
                                <label for="diem_ck" style="display: block; margin-bottom: 8px; font-weight: 500;">Điểm cuối kỳ (60%):</label>
                                <input type="number" name="diem_ck" id="diem_ck" min="0" max="10" step="0.1" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px;" required>
                            </div>
                        </div>
                        
                        <div>
                            <input type="hidden" name="action" value="nhap_diem_lite">
                            <input type="hidden" name="nonce" value="' . wp_create_nonce('nhap_diem_lite_nonce') . '">
                            <button type="submit" style="background: #0073aa; border: none; color: white; padding: 12px 20px; cursor: pointer; border-radius: 4px; font-weight: 500;">Lưu điểm</button>
                        </div>
                    </form>
                </div>';
                
                // Xử lý form nhập điểm khi submit
                if (isset($_POST['action']) && $_POST['action'] === 'nhap_diem_lite' && wp_verify_nonce($_POST['nonce'], 'nhap_diem_lite_nonce')) {
                    $sv_id = isset($_POST['sv_id']) ? intval($_POST['sv_id']) : 0;
                    $mh_id = isset($_POST['mh_id']) ? intval($_POST['mh_id']) : 0;
                    $lop_id = isset($_POST['lop_id']) ? intval($_POST['lop_id']) : 0;
                    $diem_tp1 = isset($_POST['diem_tp1']) ? floatval($_POST['diem_tp1']) : 0;
                    $diem_tp2 = isset($_POST['diem_tp2']) ? floatval($_POST['diem_tp2']) : 0;
                    $diem_ck = isset($_POST['diem_ck']) ? floatval($_POST['diem_ck']) : 0;
                    
                    // Kiểm tra các giá trị hợp lệ
                    if ($sv_id && $mh_id && $lop_id && $diem_tp1 >= 0 && $diem_tp1 <= 10 && $diem_tp2 >= 0 && $diem_tp2 <= 10 && $diem_ck >= 0 && $diem_ck <= 10) {
                        // Kiểm tra xem điểm đã tồn tại chưa
                        $args = array(
                            'post_type' => 'diem',
                            'posts_per_page' => 1,
                            'meta_query' => array(
                                'relation' => 'AND',
                                array(
                                    'key' => 'sinh_vien',
                                    'value' => $sv_id,
                                    'compare' => '='
                                ),
                                array(
                                    'key' => 'mon_hoc',
                                    'value' => $mh_id,
                                    'compare' => '='
                                )
                            )
                        );
                        
                        $existing_query = new WP_Query($args);
                        
                        if ($existing_query->have_posts()) {
                            // Cập nhật điểm hiện có
                            $existing_query->the_post();
                            $diem_id = get_the_ID();
                            wp_reset_postdata();
                            
                            // Cập nhật meta data
                            update_post_meta($diem_id, 'diem_thanh_phan_1_', $diem_tp1);
                            update_post_meta($diem_id, 'diem_thanh_phan_2_', $diem_tp2);
                            update_post_meta($diem_id, 'diem_cuoi_ki_', $diem_ck);
                            update_post_meta($diem_id, 'lop', $lop_id);
                            
                            echo '<div class="notice notice-success" style="padding: 15px; background-color: #d4edda; border-left: 4px solid #28a745; margin: 20px 0;">
                                <p><strong>Thành công!</strong> Đã cập nhật điểm cho sinh viên.</p>
                            </div>';
                        } else {
                            // Tạo bản ghi điểm mới
                            $sv_post = get_post($sv_id);
                            $mh_post = get_post($mh_id);
                            
                            if ($sv_post && $mh_post) {
                                // Tạo tiêu đề cho bản ghi điểm
                                $diem_title = $sv_post->post_title . ' - ' . $mh_post->post_title;
                                
                                // Tạo post mới
                                $diem_id = wp_insert_post(array(
                                    'post_title' => $diem_title,
                                    'post_type' => 'diem',
                                    'post_status' => 'publish'
                                ));
                                
                                if ($diem_id && !is_wp_error($diem_id)) {
                                    // Thêm meta data
                                    update_post_meta($diem_id, 'sinh_vien', $sv_id);
                                    update_post_meta($diem_id, 'mon_hoc', $mh_id);
                                    update_post_meta($diem_id, 'lop', $lop_id);
                                    update_post_meta($diem_id, 'diem_thanh_phan_1_', $diem_tp1);
                                    update_post_meta($diem_id, 'diem_thanh_phan_2_', $diem_tp2);
                                    update_post_meta($diem_id, 'diem_cuoi_ki_', $diem_ck);
                                    
                                    echo '<div class="notice notice-success" style="padding: 15px; background-color: #d4edda; border-left: 4px solid #28a745; margin: 20px 0;">
                                        <p><strong>Thành công!</strong> Đã thêm điểm mới cho sinh viên.</p>
                                    </div>';
                                } else {
                                    echo '<div class="notice notice-error" style="padding: 15px; background-color: #f8d7da; border-left: 4px solid #dc3545; margin: 20px 0;">
                                        <p><strong>Lỗi!</strong> Không thể tạo bản ghi điểm mới.</p>
                                    </div>';
                                }
                            } else {
                                echo '<div class="notice notice-error" style="padding: 15px; background-color: #f8d7da; border-left: 4px solid #dc3545; margin: 20px 0;">
                                    <p><strong>Lỗi!</strong> Không tìm thấy sinh viên hoặc môn học.</p>
                                </div>';
                            }
                        }
                    } else {
                        echo '<div class="notice notice-error" style="padding: 15px; background-color: #f8d7da; border-left: 4px solid #dc3545; margin: 20px 0;">
                            <p><strong>Lỗi!</strong> Vui lòng nhập đầy đủ thông tin và điểm hợp lệ (0-10).</p>
                        </div>';
                    }
                }
            } else {
                echo '<div class="notice notice-warning" style="padding: 15px; background-color: #fff3cd; border-left: 4px solid #ffc107; margin: 20px 0;">
                    <p><strong>Chức năng nhập điểm chỉ dành cho giáo viên và quản trị viên.</strong></p>
                    <p>Vui lòng liên hệ quản trị viên nếu bạn cần quyền nhập điểm.</p>
                </div>';
            }
            echo '</div>'; // End tab-nhap-diem
            
            echo '</div>'; // End tabs-content
            echo '</div>'; // End tabs-container
            
            // JavaScript cho tabs - cải tiến để hoạt động tốt hơn
            echo '<script>
                document.addEventListener("DOMContentLoaded", function() {
                    const tabLinks = document.querySelectorAll(".tabs-nav a");
                    const tabContents = document.querySelectorAll(".tab-content");
                    
                    function activateTab(tabId) {
                        // Ẩn tất cả tab content và bỏ chọn tất cả tab links
                        document.querySelectorAll(".tabs-nav li").forEach(function(li) {
                            li.classList.remove("tab-active");
                        });
                        
                        tabContents.forEach(function(content) {
                            content.classList.remove("tab-active");
                        });
                        
                        // Hiển thị tab được chọn
                        const selectedTab = document.querySelector(tabId);
                        const selectedLink = document.querySelector("a[href=\'" + tabId + "\']");
                        
                        if (selectedTab && selectedLink) {
                            selectedTab.classList.add("tab-active");
                            selectedLink.parentElement.classList.add("tab-active");
                        }
                        
                        // Lưu tab đã chọn vào localStorage
                        localStorage.setItem("selectedDiemTab", tabId);
                    }
                    
                    // Xử lý sự kiện click
                    tabLinks.forEach(function(link) {
                        link.addEventListener("click", function(e) {
                            e.preventDefault();
                            const tabId = this.getAttribute("href");
                            activateTab(tabId);
                        });
                    });
                    
                    // Khôi phục tab đã chọn từ localStorage hoặc từ tham số URL
                    const urlParams = new URLSearchParams(window.location.search);
                    if (urlParams.has("action") && urlParams.get("action") === "nhap_diem_lite") {
                        activateTab("#tab-nhap-diem");
                    } else {
                        const savedTab = localStorage.getItem("selectedDiemTab");
                        if (savedTab && document.querySelector(savedTab)) {
                            activateTab(savedTab);
                        }
                    }
                });
            </script>';
            
            return ob_get_clean();
        } else {
            return '<div class="qlsv-thong-bao qlsv-error"><p>Bạn không có quyền xem bảng điểm.</p></div>';
        }
    }
    
    /**
     * Lấy danh sách sinh viên theo lớp
     */
    public function get_students_by_class($lop_id) {
        if (empty($lop_id)) {
            return array();
        }
        
        global $wpdb;
        $posts_table = $wpdb->posts;
        $meta_table = $wpdb->postmeta;
        
        $sql = $wpdb->prepare(
            "SELECT p.ID, p.post_title
            FROM {$posts_table} p
            INNER JOIN {$meta_table} pm ON p.ID = pm.post_id
            WHERE p.post_type = 'sinhvien'
            AND p.post_status = 'publish'
            AND pm.meta_key = 'lop'
            AND pm.meta_value = %d
            ORDER BY p.post_title ASC
            LIMIT 100",
            $lop_id
        );
        
        return $wpdb->get_results($sql);
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
     * Đăng ký template tùy chỉnh cho trang kết quả học tập
     */
    public function register_ket_qua_hoc_tap_template($template) {
        if (is_page('ket-qua-hoc-tap') || is_page('kết-quả-học-tập') || is_page('diem') || is_page('điểm')) {
            $custom_template = QLSV_PLUGIN_DIR . 'templates/ket-qua-hoc-tap-template.php';
            if (file_exists($custom_template)) {
                return $custom_template;
            }
        }
        return $template;
    }
}

/**
 * Function để đăng ký shortcode mới và thay thế các shortcode cũ
 */
function register_qlsv_diem_lite() {
    global $qlsv_loader;
    
    if (isset($qlsv_loader)) {
        // Khởi tạo class và đăng ký shortcodes
        $diem_lite = new QLSV_Diem_Lite($qlsv_loader);
        
        // Thông báo phiên bản nhẹ đã được kích hoạt
        add_action('admin_notices', 'qlsv_diem_lite_admin_notice');
        
        // Flush rewrite rules khi cần
        if (isset($_GET['qlsv_flush_rules']) && current_user_can('manage_options')) {
            flush_rewrite_rules();
            add_action('admin_notices', 'qlsv_flush_rules_notice');
        }
    }
}

/**
 * Hiển thị thông báo trong admin về phiên bản nhẹ
 */
function qlsv_diem_lite_admin_notice() {
    ?>
    <div class="notice notice-success is-dismissible">
        <p><strong>QLSV Lite:</strong> Phiên bản nhẹ của module Điểm đã được kích hoạt để tối ưu hóa bộ nhớ.</p>
        <p>Sử dụng shortcode <code>[qlsv_tim_kiem_diem_lite]</code> và <code>[qlsv_bang_diem_lite]</code> để hiển thị bảng điểm mà không gặp lỗi bộ nhớ.</p>
        <p>Nếu gặp lỗi 404 khi tìm kiếm, hãy <a href="<?php echo esc_url(add_query_arg('qlsv_flush_rules', '1')); ?>">nhấn vào đây</a> để cập nhật cấu hình URL.</p>
    </div>
    <?php
}

/**
 * Hiển thị thông báo trong admin về flush rewrite rules
 */
function qlsv_flush_rules_notice() {
    ?>
    <div class="notice notice-success is-dismissible">
        <p><strong>QLSV:</strong> Rewrite rules đã được cập nhật thành công.</p>
    </div>
    <?php
}

// Kích hoạt phiên bản nhẹ
add_action('plugins_loaded', 'register_qlsv_diem_lite', 20);
?> 