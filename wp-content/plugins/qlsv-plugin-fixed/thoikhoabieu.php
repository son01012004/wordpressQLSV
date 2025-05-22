<?php
// Hàm lấy danh sách môn học
function qlsv_get_mon_hoc_options() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'qlsv_mon_hoc';
    $results = $wpdb->get_results("SELECT id, ten_mon_hoc FROM $table_name ORDER BY ten_mon_hoc ASC", ARRAY_A);
    $options = array();
    if ($results) {
        foreach ($results as $result) {
            $options[$result['id']] = $result['ten_mon_hoc'];
        }
    }
    return $options;
}

// Hàm lấy danh sách lớp
function qlsv_get_lop_options() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'qlsv_lop';
    $results = $wpdb->get_results("SELECT id, ten_lop FROM $table_name ORDER BY ten_lop ASC", ARRAY_A);
    $options = array();
    if ($results) {
        foreach ($results as $result) {
            $options[$result['id']] = $result['ten_lop'];
        }
    }
    return $options;
}

// Hàm lấy danh sách giảng viên
function qlsv_get_giang_vien_options() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'qlsv_giang_vien';
    $results = $wpdb->get_results("SELECT id, ho_ten FROM $table_name ORDER BY ho_ten ASC", ARRAY_A);
    $options = array();
    if ($results) {
        foreach ($results as $result) {
            $options[$result['id']] = $result['ho_ten'];
        }
    }
    return $options;
}

// Thêm popup form sửa thời khóa biểu 
function qlsv_tkb_edit_popup() {
    // Chỉ hiển thị popup cho admin
    if (!current_user_can('administrator')) {
        return;
    }
    
    $mon_hoc_options = qlsv_get_mon_hoc_options();
    $lop_options = qlsv_get_lop_options();
    $giang_vien_options = qlsv_get_giang_vien_options();
    ?>
    <div id="tkb-edit-popup" class="qlsv-popup">
        <div class="popup-header">
            <h2><?php _e('Sửa lịch học', 'qlsv'); ?></h2>
            <span class="close-popup">&times;</span>
        </div>
        <div class="popup-content">
            <form id="tkb-edit-form" method="post">
                <input type="hidden" name="tkb_id" value="">
                
                <div class="form-group">
                    <label for="edit-mon-hoc"><?php _e('Môn học', 'qlsv'); ?> <span class="required">*</span></label>
                    <select name="mon_hoc" id="edit-mon-hoc" required>
                        <option value=""><?php _e('-- Chọn môn học --', 'qlsv'); ?></option>
                        <?php foreach ($mon_hoc_options as $id => $name): ?>
                            <option value="<?php echo esc_attr($id); ?>"><?php echo esc_html($name); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="edit-lop"><?php _e('Lớp', 'qlsv'); ?> <span class="required">*</span></label>
                    <select name="lop" id="edit-lop" required>
                        <option value=""><?php _e('-- Chọn lớp --', 'qlsv'); ?></option>
                        <?php foreach ($lop_options as $id => $name): ?>
                            <option value="<?php echo esc_attr($id); ?>"><?php echo esc_html($name); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="edit-giang-vien"><?php _e('Giảng viên', 'qlsv'); ?> <span class="required">*</span></label>
                    <select name="giang_vien" id="edit-giang-vien" required>
                        <option value=""><?php _e('-- Chọn giảng viên --', 'qlsv'); ?></option>
                        <?php foreach ($giang_vien_options as $id => $name): ?>
                            <option value="<?php echo esc_attr($id); ?>"><?php echo esc_html($name); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="edit-thu"><?php _e('Thứ', 'qlsv'); ?> <span class="required">*</span></label>
                    <select name="thu" id="edit-thu" required>
                        <option value=""><?php _e('-- Chọn thứ --', 'qlsv'); ?></option>
                        <?php for ($i = 2; $i <= 8; $i++): ?>
                            <option value="<?php echo $i; ?>"><?php printf(__('Thứ %s', 'qlsv'), $i == 8 ? 'CN' : $i); ?></option>
                        <?php endfor; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="edit-gio-bat-dau"><?php _e('Giờ bắt đầu', 'qlsv'); ?> <span class="required">*</span></label>
                    <input type="time" name="gio_bat_dau" id="edit-gio-bat-dau" required>
                </div>
                
                <div class="form-group">
                    <label for="edit-gio-ket-thuc"><?php _e('Giờ kết thúc', 'qlsv'); ?> <span class="required">*</span></label>
                    <input type="time" name="gio_ket_thuc" id="edit-gio-ket-thuc" required>
                </div>
                
                <div class="form-group">
                    <label for="edit-phong"><?php _e('Phòng học', 'qlsv'); ?> <span class="required">*</span></label>
                    <input type="text" name="phong" id="edit-phong" required>
                </div>
                
                <div class="form-group">
                    <label for="edit-tuan-hoc"><?php _e('Tuần học (VD: 1-10,12,15)', 'qlsv'); ?> <span class="required">*</span></label>
                    <input type="text" name="tuan_hoc" id="edit-tuan-hoc" placeholder="VD: 1-10,12,15" required>
                </div>
                
                <div class="popup-actions">
                    <button type="submit" class="button button-primary" name="submit_edit_tkb"><?php _e('Lưu thay đổi', 'qlsv'); ?></button>
                    <button type="button" class="button button-delete" id="delete-tkb-btn"><?php _e('Xóa lịch học', 'qlsv'); ?></button>
                    <button type="button" class="button" id="cancel-edit-tkb"><?php _e('Hủy bỏ', 'qlsv'); ?></button>
                </div>
            </form>
        </div>
    </div>
    <div class="popup-overlay"></div>
    <?php
}
add_action('wp_footer', 'qlsv_tkb_edit_popup');
add_action('admin_footer', 'qlsv_tkb_edit_popup');

// Xử lý AJAX để cập nhật thời khóa biểu
function qlsv_update_tkb_callback() {
    // Kiểm tra nonce
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'qlsv_ajax_nonce')) {
        wp_send_json_error(array('message' => __('Lỗi bảo mật', 'qlsv')));
    }
    
    // Kiểm tra quyền quản trị
    if (!current_user_can('administrator')) {
        wp_send_json_error(array('message' => __('Bạn không có quyền thực hiện thao tác này', 'qlsv')));
    }
    
    // Lấy dữ liệu form
    parse_str($_POST['form_data'], $form_data);
    
    // Xác thực dữ liệu
    if (!isset($form_data['tkb_id']) || !isset($form_data['mon_hoc']) || !isset($form_data['lop']) || 
        !isset($form_data['giang_vien']) || !isset($form_data['thu']) || !isset($form_data['gio_bat_dau']) || 
        !isset($form_data['gio_ket_thuc']) || !isset($form_data['phong']) || !isset($form_data['tuan_hoc'])) {
        wp_send_json_error(array('message' => __('Dữ liệu không hợp lệ', 'qlsv')));
    }
    
    // Lấy ID của thời khóa biểu
    $tkb_id = intval($form_data['tkb_id']);
    
    // Chuẩn bị dữ liệu cập nhật
    $data = array(
        'mon_hoc_id' => intval($form_data['mon_hoc']),
        'lop_id' => intval($form_data['lop']),
        'giang_vien_id' => intval($form_data['giang_vien']),
        'thu' => intval($form_data['thu']),
        'gio_bat_dau' => sanitize_text_field($form_data['gio_bat_dau']),
        'gio_ket_thuc' => sanitize_text_field($form_data['gio_ket_thuc']),
        'phong' => sanitize_text_field($form_data['phong']),
        'tuan_hoc' => sanitize_text_field($form_data['tuan_hoc']),
        'updated_at' => current_time('mysql')
    );
    
    // Điều kiện cập nhật
    $where = array('id' => $tkb_id);
    
    // Cập nhật vào cơ sở dữ liệu
    global $wpdb;
    $table_name = $wpdb->prefix . 'qlsv_thoikhoabieu';
    $result = $wpdb->update($table_name, $data, $where);
    
    if ($result === false) {
        wp_send_json_error(array('message' => __('Lỗi khi cập nhật thời khóa biểu', 'qlsv')));
    } else {
        wp_send_json_success(array('message' => __('Cập nhật thời khóa biểu thành công', 'qlsv')));
    }
}
add_action('wp_ajax_qlsv_update_tkb', 'qlsv_update_tkb_callback');

// Xử lý AJAX để xóa thời khóa biểu
function qlsv_delete_tkb_callback() {
    // Kiểm tra nonce
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'qlsv_ajax_nonce')) {
        wp_send_json_error(array('message' => __('Lỗi bảo mật', 'qlsv')));
    }
    
    // Kiểm tra quyền quản trị
    if (!current_user_can('administrator')) {
        wp_send_json_error(array('message' => __('Bạn không có quyền thực hiện thao tác này', 'qlsv')));
    }
    
    // Lấy ID của thời khóa biểu
    if (!isset($_POST['tkb_id'])) {
        wp_send_json_error(array('message' => __('Dữ liệu không hợp lệ', 'qlsv')));
    }
    
    $tkb_id = intval($_POST['tkb_id']);
    
    // Xóa từ cơ sở dữ liệu
    global $wpdb;
    $table_name = $wpdb->prefix . 'qlsv_thoikhoabieu';
    $result = $wpdb->delete($table_name, array('id' => $tkb_id));
    
    if ($result === false) {
        wp_send_json_error(array('message' => __('Lỗi khi xóa thời khóa biểu', 'qlsv')));
    } else {
        wp_send_json_success(array('message' => __('Xóa thời khóa biểu thành công', 'qlsv')));
    }
}
add_action('wp_ajax_qlsv_delete_tkb', 'qlsv_delete_tkb_callback'); 