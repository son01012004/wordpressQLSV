=== Quản lý Sinh viên ===
Contributors: your_name
Tags: education, student, management
Requires at least: 5.0
Tested up to: 6.4
Stable tag: 1.0.0
Requires PHP: 7.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Plugin quản lý sinh viên, điểm số, lớp học, môn học, thời khóa biểu và điểm danh.

== Description ==

Plugin "Quản lý Sinh viên" cung cấp các tính năng quản lý đầy đủ cho sinh viên, điểm số, lớp học, môn học, thời khóa biểu và điểm danh trong môi trường giáo dục:

= Tính năng chính =

* **Quản lý sinh viên:** Lưu trữ thông tin cá nhân, ảnh đại diện, và trạng thái của sinh viên.
* **Quản lý điểm số:** Nhập và hiển thị điểm thành phần, điểm cuối kỳ và tính điểm trung bình tự động.
* **Quản lý lớp:** Tạo và quản lý danh sách lớp học với thông tin về khoa, cố vấn học tập.
* **Quản lý môn học:** Thêm các môn học với thông tin về số tín chỉ, môn tiên quyết.
* **Thời khóa biểu:** Quản lý lịch học với thông tin về thời gian, phòng học, giảng viên và lớp.
* **Điểm danh:** Theo dõi tình trạng điểm danh của sinh viên, bao gồm vắng mặt, đi muộn, về sớm.
* **Tìm kiếm nâng cao:** Cho phép lọc theo nhiều tiêu chí như lớp, khoa, môn học.
* **Hiển thị dưới dạng bảng:** Hiển thị dữ liệu rõ ràng và có thể sắp xếp.

= Shortcodes =

* `[qlsv_danh_sach_sinh_vien]` - Hiển thị danh sách sinh viên
* `[qlsv_danh_sach_lop]` - Hiển thị danh sách lớp
* `[qlsv_danh_sach_mon_hoc]` - Hiển thị danh sách môn học
* `[qlsv_bang_diem]` - Hiển thị bảng điểm
* `[qlsv_tim_kiem_diem]` - Hiển thị form tìm kiếm điểm
* `[qlsv_thong_tin_sinh_vien]` - Hiển thị thông tin sinh viên của người dùng đã đăng nhập
* `[qlsv_thoikhoabieu]` - Hiển thị thời khóa biểu (có thể theo tuần hoặc dạng danh sách)
* `[qlsv_tkb_lop]` - Hiển thị thời khóa biểu của một lớp cụ thể
* `[qlsv_diemdanh]` - Hiển thị danh sách điểm danh với khả năng lọc
* `[qlsv_diemdanh_sinhvien]` - Hiển thị thống kê điểm danh của sinh viên

= Yêu cầu =

* Plugin này yêu cầu Advanced Custom Fields (ACF) để hoạt động đầy đủ
* WordPress 5.0 trở lên
* PHP 7.0 trở lên

= Tương thích =

* Hoạt động với hầu hết các theme WordPress

== Installation ==

1. Tải lên thư mục `qlsv-plugin` vào `/wp-content/plugins/`
2. Kích hoạt plugin thông qua menu 'Plugins' trong WordPress
3. Cài đặt và kích hoạt plugin Advanced Custom Fields
4. Các mẫu trang để hiển thị dữ liệu sẽ được tạo tự động
5. Sử dụng các shortcode để hiển thị dữ liệu trên trang web của bạn

== Frequently Asked Questions ==

= Plugin có yêu cầu plugin khác không? =

Có, plugin này yêu cầu Advanced Custom Fields (ACF) để hoạt động đầy đủ.

= Làm cách nào để hiển thị danh sách sinh viên? =

Sử dụng shortcode `[qlsv_danh_sach_sinh_vien]` trên bất kỳ trang hoặc bài viết nào.

= Tôi có thể lọc sinh viên theo lớp không? =

Có, bạn có thể sử dụng tham số `lop_id` trong shortcode hoặc sử dụng bộ lọc được hiển thị trong giao diện.

= Làm cách nào để hiển thị thời khóa biểu? =

Sử dụng shortcode `[qlsv_thoikhoabieu]` và tùy chọn các tham số như `lop_id`, `monhoc_id`, và `loai_view` để tùy chỉnh hiển thị.

= Làm cách nào để thực hiện điểm danh cho sinh viên? =

Sử dụng giao diện quản trị để tạo buổi điểm danh mới, chọn lớp học tương ứng và hệ thống sẽ tự động tạo danh sách sinh viên từ lớp đó để điểm danh.

== Screenshots ==

1. Quản lý sinh viên
2. Nhập điểm
3. Trang tìm kiếm điểm
4. Danh sách lớp
5. Thời khóa biểu theo tuần
6. Quản lý điểm danh

== Changelog ==

= 1.0.0 =
* Phát hành bản đầu tiên với đầy đủ tính năng quản lý sinh viên, điểm số, lớp học, môn học, thời khóa biểu và điểm danh.

== Upgrade Notice ==

= 1.0.0 =
Phát hành bản đầu tiên với đầy đủ tính năng. 