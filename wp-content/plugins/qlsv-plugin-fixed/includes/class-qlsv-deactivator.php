<?php
/**
 * Class được gọi khi plugin bị vô hiệu hóa
 */
class QLSV_Deactivator {

    /**
     * Phương thức được gọi khi plugin bị vô hiệu hóa
     */
    public static function deactivate() {
        // Flush rewrite rules để cập nhật permalink
        flush_rewrite_rules();
        
        // Ghi log deactivate nếu cần
        error_log('QLSV Plugin đã bị vô hiệu hóa.');
    }
} 