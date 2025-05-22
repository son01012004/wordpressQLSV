/**
 * JavaScript cho QLSV Plugin
 */
jQuery(document).ready(function($) {
    // Đảm bảo khởi tạo thư viện phương tiện nếu cần
    if (typeof wp !== 'undefined' && wp.media && wp.media.editor) {
        // WordPress Media Library đã được load
        console.log('WordPress Media Library sẵn sàng');
    }
    
    // Xử lý form upload avatar
    if ($('.avatar-upload-container').length) {
        // Toggle hiển thị form khi click vào avatar
        $(".sinh-vien-anh, .giaovien-profile-avatar, .qlsv-user-avatar, .avatar-preview").on("click", function() {
            var container = $(".avatar-upload-container");
            
            // Toggle the form với animation
            if(container.is(":visible")) {
                container.slideUp(300);
            } else {
                container.slideDown(300);
                
                // Scroll đến form
                $("html, body").animate({
                    scrollTop: container.offset().top - 100
                }, 500);
            }
        });
        
        // Xử lý chọn file mới
        $('input[type="file"]').on('change', function() {
            var input = $(this);
            var preview = input.closest('form').siblings('.avatar-preview');
            
            if (!preview.length) {
                preview = $('.avatar-preview');
            }
            
            if (input[0].files && input[0].files[0]) {
                var reader = new FileReader();
                
                reader.onload = function(e) {
                    var img = preview.find('img');
                    
                    if (img.length) {
                        img.attr('src', e.target.result);
                    } else {
                        preview.html('<img src="' + e.target.result + '" alt="Avatar Preview">');
                    }
                    
                    // Cập nhật ảnh chính nếu có
                    var mainAvatar = $('.sinh-vien-anh img, .giaovien-profile-avatar img, .qlsv-user-avatar img');
                    if (mainAvatar.length) {
                        mainAvatar.attr('src', e.target.result);
                        
                        // Add a subtle animation to show the change
                        mainAvatar.css('opacity', '0.7').animate({opacity: 1}, 500);
                    }
                    
                    // Bật nút submit
                    $('#qlsv_avatar_submit').prop('disabled', false);
                };
                
                reader.readAsDataURL(input[0].files[0]);
            }
        });
        
        // Xử lý chọn ảnh từ thư viện
        $('#qlsv_choose_from_library').on('click', function(e) {
            e.preventDefault();
            
            // Nếu đã có frame media trước đó
            if (window.wp && window.wp.media && window.wp.media.frame) {
                window.wp.media.frame.open();
                return;
            }
            
            // Nếu WordPress Media Library API có sẵn
            if (window.wp && window.wp.media) {
                // Tạo media frame
                window.wp.media.frame = window.wp.media({
                    title: 'Chọn ảnh đại diện',
                    button: {
                        text: 'Sử dụng ảnh này'
                    },
                    multiple: false,
                    library: {
                        type: 'image'
                    }
                });
                
                // Xử lý khi chọn ảnh
                window.wp.media.frame.on('select', function() {
                    var attachment = window.wp.media.frame.state().get('selection').first().toJSON();
                    
                    // Hiển thị ảnh đã chọn
                    var preview = $('.avatar-preview');
                    if (preview.length) {
                        var img = preview.find('img');
                        var imgUrl = attachment.sizes.thumbnail ? attachment.sizes.thumbnail.url : attachment.url;
                        
                        if (img.length) {
                            img.attr('src', imgUrl);
                        } else {
                            preview.html('<img src="' + imgUrl + '" alt="Avatar">');
                        }
                    }
                    
                    // Cập nhật ảnh chính nếu có
                    var mainAvatar = $('.sinh-vien-anh img, .giaovien-profile-avatar img, .qlsv-user-avatar img');
                    if (mainAvatar.length) {
                        mainAvatar.attr('src', imgUrl);
                        mainAvatar.css('opacity', '0.7').animate({opacity: 1}, 500);
                    }
                    
                    // Lưu ID ảnh đã chọn
                    $('#qlsv_avatar_attachment_id').val(attachment.id);
                    
                    // Bật nút submit
                    $('#qlsv_avatar_submit').prop('disabled', false);
                });
                
                // Mở media frame
                window.wp.media.frame.open();
            } else {
                console.log('WordPress Media Library không có sẵn');
            }
        });
        
        // Xử lý khi submit form
        $('.avatar-form').on('submit', function() {
            var hasFile = $('input[type="file"]').val() !== '';
            var hasAttachment = $('#qlsv_avatar_attachment_id').val() !== '';
            
            // Kiểm tra xem đã chọn file hoặc attachment chưa
            if (!hasFile && !hasAttachment) {
                alert('Vui lòng chọn ảnh đại diện trước khi cập nhật');
                return false;
            }
            
            return true;
        });
    }
    
    // Xử lý thông báo thành công
    if (window.location.href.indexOf('avatar_updated=1') > -1) {
        // Hiển thị form upload khi cập nhật thành công
        $('.avatar-upload-container').slideDown(300);
        
        // Tránh cache cho hình ảnh
        var timestamp = new Date().getTime();
        
        // Cập nhật ảnh avatar với timestamp mới
        $('.sinh-vien-anh img, .sinh-vien-anh .avatar, .avatar-preview img, .avatar-preview .avatar').each(function() {
            var img = $(this);
            var currentSrc = img.attr('src');
            
            if (currentSrc) {
                // Thêm hoặc cập nhật timestamp
                if (currentSrc.indexOf('?') > -1) {
                    // Đã có tham số trong URL
                    img.attr('src', currentSrc.split('?')[0] + '?v=' + timestamp);
                } else {
                    img.attr('src', currentSrc + '?v=' + timestamp);
                }
            }
        });
        
        // Tự động xóa tham số avatar_updated sau 2 giây
        setTimeout(function() {
            window.history.replaceState({}, document.title, window.location.pathname);
        }, 2000);
        
        // Thêm hiệu ứng highlight cho ảnh đại diện
        $('.sinh-vien-anh img, .giaovien-profile-avatar img, .qlsv-user-avatar img').css({
            'transition': 'all 0.5s',
            'box-shadow': '0 0 15px rgba(0, 115, 170, 0.8)'
        }).delay(1000).queue(function() {
            $(this).css('box-shadow', 'none').dequeue();
        });
    }
}); 