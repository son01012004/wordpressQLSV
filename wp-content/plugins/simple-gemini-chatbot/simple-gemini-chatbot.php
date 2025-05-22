<?php
/**
 * Plugin Name: Simple Gemini Floating Chatbot (No AJAX)
 * Description: Plugin đơn giản để tích hợp Gemini API làm chatbot nổi ở góc phải trên tất cả các trang, không dùng AJAX.
 * Version: 1.1.3
 * Author: TenBanCuaBan
 * License: GPLv2 or later
 * Text Domain: simple-gemini-floating-chatbot
 */

// Ngăn chặn truy cập trực tiếp vào tệp
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * FAQ về Trường Đại học Điện lực
 */
function epu_faq_data() {
    return [
        'giới thiệu trường' => 'Trường Đại học Điện lực là trường đại học công lập đa ngành, trực thuộc Bộ Công Thương, chuyên đào tạo và nghiên cứu trong lĩnh vực năng lượng, đặc biệt là điện lực. Trường có hai cơ sở tại 235 Hoàng Quốc Việt, Hà Nội và Tân Minh, Sóc Sơn, Hà Nội. Với sứ mệnh đào tạo nguồn nhân lực chất lượng cao và nghiên cứu khoa học công nghệ, trường đã đạt nhiều thành tựu, bao gồm Huân chương Độc lập hạng Ba và Huân chương Lao động các hạng. Trường hiện đào tạo 32 ngành đại học, 7 ngành tiến sĩ, 10 ngành thạc sĩ với quy mô gần 20.000 người học.',
        'tên trường là gì' => 'Trường tôi là Đại học Điện lực.',
        'địa chỉ trường ở đâu' => 'Đại học Điện lực có 2 cơ sở: CS1 tại 235 Hoàng Quốc Việt, Hà Nội; CS2 tại Tân Minh, Sóc Sơn, Hà Nội.',
        'số điện thoại tuyển sinh' => 'Số điện thoại tuyển sinh là (024) 2245 2662.',
        'số điện thoại phòng tổ chức hành chính' => 'Số điện thoại Phòng Tổ chức - Hành chính là (024) 2218 5629.',
        'fax của trường' => 'Số fax của trường là (024) 3836 2065.',
        'email của trường' => 'Email của trường là info@epu.edu.vn.',
        'sứ mệnh của trường' => 'Trường Đại học Điện lực là trường đại học công lập đa ngành, đào tạo nguồn nhân lực chất lượng cao; nghiên cứu khoa học, phát triển công nghệ, tư vấn chính sách, chuyển giao tri thức, đặc biệt trong lĩnh vực năng lượng, góp phần xây dựng, phát triển đất nước và hội nhập quốc tế.',
        'tầm nhìn của trường' => 'Là trường đại học đa ngành theo định hướng ứng dụng có uy tín trong và ngoài nước, từng bước khẳng định vị thế hàng đầu Việt Nam trong lĩnh vực năng lượng.',
        'giá trị cốt lõi' => 'Trách nhiệm - Sáng tạo - Hiệu quả.',
        'mục tiêu của trường' => 'Trở thành trường đại học theo hướng ứng dụng hàng đầu Việt Nam, theo mô hình tự chủ toàn diện, hội nhập với nền giáo dục tiên tiến khu vực và quốc tế. Người học được đào tạo toàn diện, đáp ứng tốt yêu cầu của thị trường lao động, có khả năng học tập suốt đời, có năng lực sáng tạo và khởi nghiệp. Kết quả nghiên cứu khoa học đáp ứng tốt yêu cầu thực tiễn, góp phần vào sự nghiệp công nghiệp hóa, hiện đại hóa đất nước.',
        'triết lý giáo dục' => 'Giáo dục toàn diện, vững nền tảng, bền tương lai.',
        'hiệu trưởng là ai' => 'Hiệu trưởng là PGS.TS. Đinh Văn Châu. Ông lãnh đạo và điều hành chung hoạt động của Nhà trường, phụ trách các lĩnh vực như xây dựng chiến lược, quy hoạch, kế hoạch phát triển, công tác tuyển sinh, tổ chức cán bộ, và các đơn vị như Khoa Kỹ thuật điện, Khoa Điều khiển và Tự động hóa, Khoa Quản trị kinh doanh và Du lịch, Khoa Kế toán – Tài chính, Trung tâm Giáo dục thể chất – Quốc phòng an ninh.',
        'phó hiệu trưởng nguyễn lê cường phụ trách gì' => 'PGS.TS. Nguyễn Lê Cường phụ trách nghiên cứu khoa học, hợp tác quốc tế, liên kết đào tạo quốc tế, đào tạo thường xuyên, khảo thí và đảm bảo chất lượng, công tác học liệu, bình đẳng giới. Ông trực tiếp chỉ đạo các đơn vị như Khoa Điện tử viễn thông, Khoa Công nghệ thông tin, Khoa Ngoại ngữ, Khoa Khoa học tự nhiên, Khoa Lý luận chính trị và Pháp luật, Trung tâm Nghiên cứu ứng dụng và Chuyển giao công nghệ, Trung tâm Đào tạo nâng cao, Trung tâm Đào tạo thường xuyên, Trung tâm Công nghệ Thông tin, Trung tâm Thực hành – Thí nghiệm, và Thư viện.',
        'phó hiệu trưởng dương trung kiên phụ trách gì' => 'TS. Dương Trung Kiên phụ trách đào tạo trình độ đại học và sau đại học, công tác sinh viên, truyền thông, quan hệ công chúng, mua sắm, đầu tư, xây dựng cơ bản, quản trị cơ sở vật chất, phòng chống cháy nổ, bão lụt, thiên tai, dịch bệnh, an toàn lao động, an toàn học đường và vệ sinh môi trường. Ông là Người Phát ngôn của trường và trực tiếp chỉ đạo các đơn vị như Khoa Quản lý công nghiệp và Năng lượng, Khoa Năng lượng mới, Khoa Cơ khí – Ô tô và Xây dựng, Trung tâm Truyền thông và Quan hệ doanh nghiệp.',
    ];
}

/**
 * Tìm câu trả lời từ FAQ trước khi gọi API
 */
function get_epu_faq_answer( $user_message ) {
    $faq = epu_faq_data();
    $user_message = strtolower( trim( $user_message ) );
    
    foreach ( $faq as $question => $answer ) {
        if ( strpos( $user_message, $question ) !== false ) {
            return $answer;
        }
    }
    return false;
}

/**
 * Gọi Gemini API cho chatbot
 */
function call_gemini_api_chatbot_simple( $conversation_history = [] ) {
    if ( ! defined( 'GEMINI_API_KEY' ) || empty( GEMINI_API_KEY ) ) {
        error_log('Lỗi Gemini API: Khóa API chưa được định nghĩa trong wp-config.php.');
        return new WP_Error( 'api_key_missing', 'Lỗi cấu hình: Khóa API Gemini chưa được thiết lập.' );
    }
    $api_key = GEMINI_API_KEY;
    $api_url = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key=' . $api_key;

    $request_data = [
        'contents' => $conversation_history
    ];

    $args = [
        'method'    => 'POST',
        'headers'   => ['Content-Type' => 'application/json'],
        'body'      => json_encode( $request_data ),
        'timeout'   => 60,
        'sslverify' => true,
    ];

    $response = wp_remote_post( $api_url, $args );

    if ( is_wp_error( $response ) ) {
        error_log('Lỗi wp_remote_post khi gọi Gemini: ' . $response->get_error_message());
        return new WP_Error( 'wp_remote_post_error', 'Lỗi kết nối đến dịch vụ AI: ' . $response->get_error_message() );
    }

    $response_body = wp_remote_retrieve_body( $response );
    $response_code = wp_remote_retrieve_response_code( $response );

    error_log('Phản hồi từ Gemini API: Code ' . $response_code . ' | Body: ' . $response_body);

    $response_data = json_decode( $response_body, true );

    if ( $response_code !== 200 || isset( $response_data['error'] ) ) {
        $error_message = 'Lỗi từ Gemini API: ';
        if ( isset( $response_data['error']['message'] ) ) $error_message .= $response_data['error']['message'];
        elseif ( $response_body ) $error_message .= 'Phản hồi không hợp lệ. Mã lỗi HTTP: ' . $response_code;
        else $error_message .= 'Không nhận được phản hồi từ API. Mã lỗi HTTP: ' . $response_code;
        error_log( $error_message );
        return new WP_Error( 'gemini_api_error', $error_message );
    }

    if ( empty( $response_data['candidates'] ) && isset( $response_data['promptFeedback']['blockReason'] ) ) {
        $block_reason = $response_data['promptFeedback']['blockReason'];
        error_log('Gemini API - Prompt bị chặn vì lý do: ' . $block_reason);
        return new WP_Error('prompt_blocked', 'Yêu cầu của bạn đã bị chặn vì lý do an toàn: ' . $block_reason);
    }

    if ( isset( $response_data['candidates'][0]['finishReason'] ) && $response_data['candidates'][0]['finishReason'] === 'SAFETY' ) {
        error_log('Gemini API - Phản hồi được tạo đã bị chặn do cài đặt an toàn.');
        return new WP_Error('safety_block_chatbot', 'Phản hồi được tạo đã bị chặn do cài đặt an toàn.');
    }

    if ( isset( $response_data['candidates'][0]['content']['parts'][0]['text'] ) ) {
        return $response_data['candidates'][0]['content']['parts'][0]['text'];
    }

    error_log('Gemini API - Cấu trúc phản hồi không mong đợi: ' . $response_body);
    return new WP_Error('api_response_format_error', 'Không thể trích xuất nội dung từ phản hồi của Gemini.');
}

/**
 * Hiển thị HTML, CSS và xử lý form cho chatbot nổi ở chân trang.
 */
function display_floating_gemini_chatbot_no_ajax() {
    $outer_container_id = 'gemini-floating-chatbot-wrapper';
    $chat_display_id = 'gemini-chat-display-simple';
    $user_input_id = 'gemini-user-input-simple';
    $send_button_id = 'gemini-send-button-simple';
    $clear_button_id = 'gemini-clear-button-simple';

    // Khởi tạo session để lưu lịch sử hội thoại
    if ( ! session_id() ) {
        session_start();
    }

    // Xử lý xóa lịch sử hội thoại
    if ( isset( $_POST['clear_chat'] ) && $_POST['clear_chat'] === '1' ) {
        $_SESSION['epu_chat_history'] = [
            ['role' => 'model', 'parts' => [['text' => 'Xin chào! Tôi là chatbot của Trường Đại học Điện lực. Tôi có thể giúp gì cho bạn hôm nay?']]]
        ];
    }

    // Khởi tạo lịch sử hội thoại từ session hoặc mặc định
    if ( ! isset( $_SESSION['epu_chat_history'] ) ) {
        $_SESSION['epu_chat_history'] = [
            ['role' => 'model', 'parts' => [['text' => 'Xin chào! Tôi là chatbot của Trường Đại học Điện lực. Tôi có thể giúp gì cho bạn hôm nay?']]]
        ];
    }
    $conversation_history = $_SESSION['epu_chat_history'];
    $bot_response = '';

    // Xử lý khi người dùng gửi form
    if ( isset( $_POST['user_message'] ) && ! empty( $_POST['user_message'] ) ) {
        $user_message = sanitize_text_field( $_POST['user_message'] );

        // Thêm tin nhắn người dùng vào lịch sử
        $conversation_history[] = [
            'role' => 'user',
            'parts' => [['text' => $user_message]]
        ];

        // Kiểm tra FAQ trước
        $faq_answer = get_epu_faq_answer( $user_message );
        if ( $faq_answer ) {
            $bot_response = $faq_answer;
        } else {
            // Nếu không có trong FAQ, gọi Gemini API
            $response = call_gemini_api_chatbot_simple( $conversation_history );
            if ( ! is_wp_error( $response ) ) {
                $bot_response = $response;
            } else {
                $bot_response = 'Lỗi: ' . $response->get_error_message();
            }
        }

        // Thêm phản hồi của bot vào lịch sử
        $conversation_history[] = [
            'role' => 'model',
            'parts' => [['text' => $bot_response]]
        ];

        // Giới hạn lịch sử hội thoại (20 tin nhắn gần nhất)
        if ( count( $conversation_history ) > 20 ) {
            $conversation_history = array_slice( $conversation_history, -20 );
        }

        // Lưu lịch sử vào session
        $_SESSION['epu_chat_history'] = $conversation_history;
    }
    ?>
    <div id="<?php echo esc_attr($outer_container_id); ?>">
        <div class="gemini-chatbot-inner-window">
            <div class="gemini-chatbot-header" role="button" tabindex="0" aria-expanded="true" aria-controls="<?php echo esc_attr($chat_display_id); ?>">
                <span>Chat với Trợ lý AI - Đại học Điện lực</span>
                <div class="gemini-chatbot-header-buttons">
                    <button type="button" class="gemini-chatbot-clear-button" aria-label="Xóa lịch sử chat">🗑</button>
                    <button type="button" class="gemini-chatbot-toggle-button" aria-label="Thu nhỏ hoặc mở rộng chatbot">–</button>
                </div>
            </div>
            <div id="<?php echo esc_attr($chat_display_id); ?>" class="gemini-chat-content-area">
                <?php
                // Hiển thị lịch sử hội thoại từ session
                foreach ( $conversation_history as $message ) {
                    $sender = $message['role'] === 'user' ? 'user-message-simple' : 'bot-message-simple';
                    $text = esc_html( $message['parts'][0]['text'] );
                    echo '<div class="chat-message-simple ' . esc_attr( $sender ) . '">' . nl2br( $text ) . '</div>';
                }
                // Hiển thị phản hồi lỗi nếu có
                if ( ! empty( $bot_response ) && strpos( $bot_response, 'Lỗi: ' ) === 0 ) {
                    echo '<div class="chat-message-simple bot-message-simple chat-error-message-simple">' . esc_html( $bot_response ) . '</div>';
                }
                ?>
            </div>
            <div class="gemini-chat-input-area-simple">
                <form method="post" action="">
                    <div class="input-container">
                        <textarea id="<?php echo esc_attr($user_input_id); ?>" name="user_message" placeholder="Nhập câu hỏi..." rows="1" aria-label="Nhập tin nhắn cho chatbot"></textarea>
                        <button id="<?php echo esc_attr($send_button_id); ?>" type="submit" aria-label="Gửi tin nhắn">Gửi</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <style type="text/css">
    #<?php echo esc_attr($outer_container_id); ?> {
        position: fixed; bottom: 25px; right: 25px; width: 100%; max-width: 360px;
        z-index: 99999; box-shadow: 0 6px 20px rgba(0,0,0,0.2); border-radius: 10px;
        overflow: hidden; background-color: #fff; font-family: Arial, sans-serif;
        transition: transform 0.3s ease-in-out, opacity 0.3s ease-in-out, height 0.3s ease-in-out;
        height: 520px;
    }
    #<?php echo esc_attr($outer_container_id); ?>.gemini-chatbot-collapsed {
        height: 50px; overflow: hidden;
    }
    #<?php echo esc_attr($outer_container_id); ?>.gemini-chatbot-collapsed .gemini-chat-content-area,
    #<?php echo esc_attr($outer_container_id); ?>.gemini-chatbot-collapsed .gemini-chat-input-area-simple {
        display: none;
    }
    .gemini-chatbot-inner-window {
        width: 100%; height: 100%; display: flex; flex-direction: column;
    }
    .gemini-chatbot-header {
        background-color: #0073aa; color: white; padding: 12px 15px; font-size: 15px;
        font-weight: bold; display: flex; justify-content: space-between; align-items: center;
        cursor: pointer; height: 50px; box-sizing: border-box;
    }
    .gemini-chatbot-header-buttons {
        display: flex; align-items: center; gap: 8px;
    }
    .gemini-chatbot-toggle-button, .gemini-chatbot-clear-button {
        background: transparent; border: none; color: white; font-size: 18px;
        cursor: pointer; padding: 0 5px; line-height: 1;
    }
    .gemini-chatbot-toggle-button:focus, .gemini-chatbot-clear-button:focus {
        outline: 1px dotted white;
    }
    #<?php echo esc_attr($chat_display_id); ?> {
        flex-grow: 1; padding: 15px; overflow-y: auto; background-color: #fdfdfd; border-bottom: 1px solid #eee;
    }
    .chat-message-simple {
        padding: 9px 14px; margin-bottom: 10px; border-radius: 18px; max-width: 88%;
        line-height: 1.45; word-wrap: break-word; font-size: 14px;
    }
    .user-message-simple {
        background-color: #0073aa; color: white; margin-left: auto; border-bottom-right-radius: 6px;
    }
    .bot-message-simple {
        background-color: #f0f0f0; color: #333; margin-right: auto; border-bottom-left-radius: 6px;
    }
    .gemini-chat-input-area-simple {
        padding: 10px; border-top: 1px solid #eee; background-color: #fff;
    }
    .input-container {
        display: flex; align-items: center; gap: 8px;
    }
    #<?php echo esc_attr($user_input_id); ?> {
        flex-grow: 1; padding: 10px 15px; border: 1px solid #ccc; border-radius: 20px;
        resize: none; font-size: 14px; line-height: 1.4; max-height: 80px; overflow-y: auto;
        height: 42px; /* Đảm bảo chiều cao cố định ban đầu */
    }
    #<?php echo esc_attr($user_input_id); ?>:focus {
        border-color: #0073aa; outline: none; box-shadow: 0 0 0 2px rgba(0,115,170,.2);
    }
    #<?php echo esc_attr($send_button_id); ?> {
        padding: 10px 18px; background-color: #0073aa; color: white; border: none;
        border-radius: 20px; cursor: pointer; font-size: 14px; font-weight: 500;
        height: 42px; /* Cùng chiều cao với textarea */
    }
    #<?php echo esc_attr($send_button_id); ?>:hover {
        background-color: #005a87;
    }
    .chat-error-message-simple {
        color: #d9534f !important; background-color: #f2dede !important; border: 1px solid #ebccd1; font-size: 13px;
    }
    </style>
    <script type="text/javascript">
    jQuery(document).ready(function($) {
        const chatWrapper = $('#<?php echo esc_js($outer_container_id); ?>');
        const chatDisplay = $('#<?php echo esc_js($chat_display_id); ?>');
        const userInput = $('#<?php echo esc_js($user_input_id); ?>');
        const toggleButton = chatWrapper.find('.gemini-chatbot-toggle-button');
        const clearButton = chatWrapper.find('.gemini-chatbot-clear-button');
        const header = chatWrapper.find('.gemini-chatbot-header');
        const form = $('.gemini-chat-input-area-simple form');

        // Xử lý đóng/mở chatbot
        function toggleChatWindow() {
            chatWrapper.toggleClass('gemini-chatbot-collapsed');
            if (chatWrapper.hasClass('gemini-chatbot-collapsed')) {
                toggleButton.html('+');
                header.attr('aria-expanded', 'false');
            } else {
                toggleButton.html('–');
                header.attr('aria-expanded', 'true');
                userInput.focus();
                chatDisplay.scrollTop(chatDisplay[0].scrollHeight);
            }
        }
        header.on('click', function(e) {
            if ($(e.target).is('.gemini-chatbot-clear-button, .gemini-chatbot-toggle-button')) {
                return;
            }
            toggleChatWindow();
        });

        // Xử lý xóa lịch sử chat
        clearButton.on('click', function() {
            $('<form>', {
                'method': 'POST',
                'action': '',
                'html': '<input type="hidden" name="clear_chat" value="1">'
            }).appendTo('body').submit();
        });

        // Tự động điều chỉnh chiều cao textarea
        userInput.on('input', function () {
            this.style.height = 'auto';
            this.style.height = Math.min(this.scrollHeight, 80) + 'px';
        });

        // Gửi form khi nhấn Enter
        userInput.on('keypress', function(e) {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                form.submit();
            }
        });

        // Cuộn xuống cuối sau khi gửi tin nhắn
        chatDisplay.scrollTop(chatDisplay[0].scrollHeight);
    });
    </script>
    <?php
}
add_action('wp_footer', 'display_floating_gemini_chatbot_no_ajax');
?>