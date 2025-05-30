# Plugin Quản Lý Sinh Viên (QLSV) - Hướng dẫn sử dụng

## Giới thiệu
Plugin Quản Lý Sinh Viên (QLSV) là một giải pháp toàn diện để quản lý thông tin sinh viên, lớp học, môn học, điểm số và điểm danh trong môi trường giáo dục. Plugin này được phát triển đặc biệt cho WordPress, giúp các cơ sở giáo dục dễ dàng quản lý dữ liệu sinh viên.

## Các chức năng chính
- Quản lý thông tin sinh viên
- Quản lý lớp học và môn học
- Quản lý điểm số
- Quản lý điểm danh
- Báo cáo và thống kê

## Hướng dẫn sử dụng chức năng điểm danh

### 1. Tổng quan về chức năng điểm danh
Chức năng điểm danh giúp giáo viên và quản trị viên theo dõi sự tham gia của sinh viên trong các buổi học. Hệ thống hỗ trợ các trạng thái điểm danh sau:
- Có mặt
- Vắng mặt
- Đi muộn
- Về sớm
- Có phép

### 2. Các shortcode điểm danh

#### Bảng điều khiển điểm danh tổng hợp
```
[qlsv_diemdanh_dashboard]
```
Shortcode này hiển thị bảng điều khiển đầy đủ với tất cả các chức năng điểm danh, bao gồm:
- Form điểm danh (cho giáo viên)
- Xem điểm danh (cho tất cả người dùng)
- Thống kê điểm danh (cho giáo viên)

#### Form điểm danh
```
[qlsv_diemdanh_form]
```
Shortcode này hiển thị form điểm danh cho giáo viên, cho phép họ:
- Chọn lớp và môn học
- Chọn ngày và buổi học
- Đánh dấu trạng thái điểm danh cho từng sinh viên
- Thêm ghi chú cho từng sinh viên

#### Xem danh sách điểm danh
```
[qlsv_diemdanh lop_id="123" monhoc_id="456"]
```
Shortcode này hiển thị danh sách điểm danh của một lớp và môn học cụ thể. Các tham số:
- `lop_id`: ID của lớp (tùy chọn)
- `monhoc_id`: ID của môn học (tùy chọn)

#### Xem thống kê điểm danh sinh viên
```
[qlsv_diemdanh_sinhvien sinhvien_id="123"]
```
Shortcode này hiển thị thống kê điểm danh chi tiết của một sinh viên cụ thể. Các tham số:
- `sinhvien_id`: ID của sinh viên (bắt buộc)

### 3. Hướng dẫn sử dụng cho giáo viên

#### Cách điểm danh sinh viên:
1. Đăng nhập vào hệ thống với tài khoản giáo viên
2. Truy cập trang có chứa shortcode `[qlsv_diemdanh_dashboard]` hoặc `[qlsv_diemdanh_form]`
3. Chọn lớp và môn học từ danh sách
4. Chọn ngày và buổi học
5. Đánh dấu trạng thái điểm danh cho từng sinh viên
6. Thêm ghi chú nếu cần thiết
7. Nhấn nút "Lưu điểm danh"

#### Cách xem thống kê điểm danh:
1. Đăng nhập vào hệ thống với tài khoản giáo viên
2. Truy cập trang có chứa shortcode `[qlsv_diemdanh_dashboard]`
3. Chuyển sang tab "Thống kê"
4. Xem biểu đồ và số liệu thống kê tổng quan
5. Xem danh sách các lớp có tỷ lệ vắng cao nhất

### 4. Hướng dẫn sử dụng cho sinh viên

#### Cách xem điểm danh cá nhân:
1. Đăng nhập vào hệ thống với tài khoản sinh viên
2. Truy cập trang có chứa shortcode `[qlsv_diemdanh_dashboard]`
3. Hệ thống sẽ tự động hiển thị thống kê điểm danh của sinh viên đó
4. Sinh viên có thể xem tổng hợp điểm danh và chi tiết từng buổi học

### 5. Tùy chỉnh hiển thị

Bạn có thể tùy chỉnh giao diện điểm danh bằng cách thêm CSS tùy chỉnh vào theme của bạn. Các class CSS chính:
- `.diemdanh-dashboard-container`: Container chính của bảng điều khiển
- `.diemdanh-form-container`: Container của form điểm danh
- `.diemdanh-sinhvien-container`: Container của thống kê sinh viên
- `.diemdanh-list-container`: Container của danh sách điểm danh

## Hỗ trợ và phát triển thêm
Nếu bạn cần hỗ trợ hoặc muốn yêu cầu tính năng mới, vui lòng liên hệ với chúng tôi qua email: support@example.com

## Tính năng điểm danh

Tính năng điểm danh cho phép:
- Giáo viên theo dõi điểm danh của sinh viên theo lớp và môn học
- Sinh viên xem thông tin điểm danh của mình
- Thống kê tỷ lệ điểm danh theo môn học và thời gian

**Truy cập trang điểm danh:** `http://your-site/diemdanhh/`

**Quản lý điểm danh theo lớp và môn học:** `http://your-site/diemdanh/?lop=ID&mon_hoc=ID`

### Fix cho Tính năng Điểm Danh (19/05/2024)

Cập nhật mới nhất đã khắc phục lỗi 404 khi truy cập trang điểm danh với tham số lớp và môn học. 
Để áp dụng bản fix này:

1. Đảm bảo tất cả các file đã được cập nhật đúng cách
2. Truy cập trang công cụ: `http://your-site/wp-content/plugins/qlsv-plugin-fixed/`
3. Chạy công cụ "Refresh Diemdanh Settings" để làm mới cấu hình
4. Vào Settings > Permalinks và nhấn "Save Changes" để cập nhật rewrite rules

Xem hướng dẫn đầy đủ trong file `diemdanh-fix-instructions.md`

## Quản lý Điểm số

Tính năng quản lý điểm số cho phép:
- Giáo viên nhập và quản lý điểm số của sinh viên
- Sinh viên xem điểm số của mình
- Thống kê điểm số theo lớp và môn học

## Quản lý Thời khóa biểu

Tính năng quản lý thời khóa biểu cho phép:
- Lập lịch học cho các lớp và môn học
- Xem thời khóa biểu theo lớp hoặc theo giáo viên
- Thông báo lịch học tới sinh viên và giáo viên

## Hỗ trợ

Nếu bạn gặp vấn đề khi sử dụng plugin, vui lòng tạo issue trên trang GitHub của dự án hoặc liên hệ với đội hỗ trợ. 