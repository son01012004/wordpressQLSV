jQuery(document).ready(function($) {
    // Xử lý sự kiện khi nhấn nút sửa thời khóa biểu
    $(document).on('click', '.tkb-edit-btn', function(e) {
        e.preventDefault();
        
        // Lấy thông tin từ data attributes
        var tkbId = $(this).data('tkb-id');
        var monHocId = $(this).data('mon-hoc');
        var lopId = $(this).data('lop');
        var giangVienId = $(this).data('giang-vien');
        var thu = $(this).data('thu');
        var gioBatDau = $(this).data('gio-bat-dau');
        var gioKetThuc = $(this).data('gio-ket-thuc');
        var phong = $(this).data('phong');
        var tuanHoc = $(this).data('tuan-hoc');
        
        // Thiết lập giá trị cho các trường trong popup
        var $editForm = $('#tkb-edit-popup');
        $editForm.find('input[name="tkb_id"]').val(tkbId);
        
        // Set giá trị cho dropdown môn học
        $editForm.find('select[name="mon_hoc"]').val(monHocId).trigger('change');
        
        // Set giá trị cho dropdown lớp
        $editForm.find('select[name="lop"]').val(lopId).trigger('change');
        
        // Set giá trị cho dropdown giảng viên
        $editForm.find('select[name="giang_vien"]').val(giangVienId).trigger('change');
        
        // Set giá trị cho dropdown thứ
        $editForm.find('select[name="thu"]').val(thu).trigger('change');
        
        // Set giá trị cho giờ bắt đầu
        $editForm.find('input[name="gio_bat_dau"]').val(gioBatDau);
        
        // Set giá trị cho giờ kết thúc
        $editForm.find('input[name="gio_ket_thuc"]').val(gioKetThuc);
        
        // Set giá trị cho phòng học
        $editForm.find('input[name="phong"]').val(phong);
        
        // Set giá trị cho tuần học
        $editForm.find('input[name="tuan_hoc"]').val(tuanHoc);
        
        // Hiển thị popup
        $editForm.fadeIn(300);
        $('.popup-overlay').fadeIn(300);
    });
    
    // Đóng popup khi click vào nút Hủy bỏ
    $(document).on('click', '#cancel-edit-tkb, .close-popup', function(e) {
        e.preventDefault();
        $('.qlsv-popup, .popup-overlay').fadeOut(300);
    });
    
    // Xử lý sự kiện khi submit form chỉnh sửa
    $(document).on('submit', '#tkb-edit-form', function(e) {
        e.preventDefault();
        
        var formData = $(this).serialize();
        
        $.ajax({
            type: 'POST',
            url: qlsv_ajax_params.ajax_url,
            data: {
                action: 'qlsv_update_tkb',
                nonce: qlsv_ajax_params.nonce,
                form_data: formData
            },
            beforeSend: function() {
                // Hiển thị loading
                $('#tkb-edit-form button').prop('disabled', true);
            },
            success: function(response) {
                if (response.success) {
                    // Hiển thị thông báo thành công
                    alert(response.data.message);
                    
                    // Đóng popup
                    $('.qlsv-popup, .popup-overlay').fadeOut(300);
                    
                    // Làm mới trang
                    location.reload();
                } else {
                    // Hiển thị thông báo lỗi
                    alert(response.data.message);
                }
            },
            error: function(xhr, status, error) {
                // Hiển thị thông báo lỗi
                alert('Đã xảy ra lỗi: ' + error);
            },
            complete: function() {
                // Kết thúc loading
                $('#tkb-edit-form button').prop('disabled', false);
            }
        });
    });
    
    // Xử lý sự kiện khi nhấn nút xóa lịch học
    $(document).on('click', '#delete-tkb-btn', function(e) {
        e.preventDefault();
        
        if (!confirm('Bạn có chắc chắn muốn xóa lịch học này không?')) {
            return;
        }
        
        var tkbId = $('#tkb-edit-form input[name="tkb_id"]').val();
        
        $.ajax({
            type: 'POST',
            url: qlsv_ajax_params.ajax_url,
            data: {
                action: 'qlsv_delete_tkb',
                nonce: qlsv_ajax_params.nonce,
                tkb_id: tkbId
            },
            beforeSend: function() {
                // Hiển thị loading
                $('#tkb-edit-form button').prop('disabled', true);
            },
            success: function(response) {
                if (response.success) {
                    // Hiển thị thông báo thành công
                    alert(response.data.message);
                    
                    // Đóng popup
                    $('.qlsv-popup, .popup-overlay').fadeOut(300);
                    
                    // Làm mới trang
                    location.reload();
                } else {
                    // Hiển thị thông báo lỗi
                    alert(response.data.message);
                }
            },
            error: function(xhr, status, error) {
                // Hiển thị thông báo lỗi
                alert('Đã xảy ra lỗi: ' + error);
            },
            complete: function() {
                // Kết thúc loading
                $('#tkb-edit-form button').prop('disabled', false);
            }
        });
    });
    
    // Xử lý sự kiện khi nhấn nút sửa thời khóa biểu từ giao diện theo tuần
    $(document).on('click', '.tkb-edit-link', function(e) {
        e.preventDefault();
        
        // Lấy thông tin từ data attributes
        var tkbId = $(this).data('tkb-id');
        var monHocId = $(this).data('mon-hoc');
        var lopId = $(this).data('lop');
        var giangVienId = $(this).data('giang-vien');
        var thu = $(this).data('thu');
        var gioBatDau = $(this).data('gio-bat-dau');
        var gioKetThuc = $(this).data('gio-ket-thuc');
        var phong = $(this).data('phong');
        var tuanHoc = $(this).data('tuan-hoc');
        
        // Thiết lập giá trị cho các trường trong popup
        var $editForm = $('#tkb-edit-popup');
        $editForm.find('input[name="tkb_id"]').val(tkbId);
        
        // Set giá trị cho dropdown môn học
        $editForm.find('select[name="mon_hoc"]').val(monHocId).trigger('change');
        
        // Set giá trị cho dropdown lớp
        $editForm.find('select[name="lop"]').val(lopId).trigger('change');
        
        // Set giá trị cho dropdown giảng viên
        $editForm.find('select[name="giang_vien"]').val(giangVienId).trigger('change');
        
        // Set giá trị cho dropdown thứ
        $editForm.find('select[name="thu"]').val(thu).trigger('change');
        
        // Set giá trị cho giờ bắt đầu
        $editForm.find('input[name="gio_bat_dau"]').val(gioBatDau);
        
        // Set giá trị cho giờ kết thúc
        $editForm.find('input[name="gio_ket_thuc"]').val(gioKetThuc);
        
        // Set giá trị cho phòng học
        $editForm.find('input[name="phong"]').val(phong);
        
        // Set giá trị cho tuần học
        $editForm.find('input[name="tuan_hoc"]').val(tuanHoc);
        
        // Hiển thị popup
        $editForm.fadeIn(300);
        $('.popup-overlay').fadeIn(300);
    });
}); 