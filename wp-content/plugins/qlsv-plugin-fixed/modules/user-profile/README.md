# Hướng dẫn sử dụng tính năng Thông tin người dùng

## Giới thiệu

Module này cung cấp tính năng hiển thị thông tin người dùng dựa theo vai trò của họ. Khi người dùng đăng nhập, họ sẽ thấy thông tin cá nhân của mình được hiển thị phù hợp với vai trò của họ trong hệ thống.

## Các vai trò được hỗ trợ

1. **Giáo viên**: Hiển thị thông tin giảng viên, bao gồm mã giáo viên, học vị, khoa, chuyên môn, và thời khóa biểu giảng dạy.
2. **Sinh viên**: Hiển thị thông tin sinh viên, bao gồm thông tin cá nhân, lớp, khoa, và có thể xem bảng điểm.
3. **Người dùng khác**: Hiển thị thông tin cơ bản từ tài khoản WordPress.

## Cách sử dụng

### Sử dụng shortcode

Đặt shortcode này trên bất kỳ trang hoặc bài viết nào để hiển thị thông tin người dùng đang đăng nhập:

```
[qlsv_user_profile]
```

Để hiển thị thông tin của một người dùng cụ thể (chỉ quản trị viên mới có quyền xem):

```
[qlsv_user_profile id="123"]
```

### Tạo trang thông tin cá nhân

1. Tạo một trang mới trong WordPress
2. Đặt tên trang (ví dụ: "Thông tin cá nhân")
3. Thêm shortcode `[qlsv_user_profile]` vào nội dung
4. Xuất bản trang

## Liên kết với các phần khác

- Sinh viên có thể truy cập bảng điểm từ trang thông tin cá nhân
- Giáo viên có thể xem thời khóa biểu giảng dạy
- Hệ thống tự động xác định vai trò và hiển thị thông tin phù hợp

## Yêu cầu

- Plugin Advanced Custom Fields phải được kích hoạt
- Người dùng phải đăng nhập để xem thông tin cá nhân

## Hỗ trợ

Nếu bạn gặp vấn đề với module này, vui lòng liên hệ với quản trị viên hệ thống. 