<?php
/**
 * Template Name: Bảng điểm sinh viên
 */
get_header(); ?>

<div class="container">
    <h2>Bảng điểm sinh viên</h2>
    <table border="1" cellpadding="8">
        <tr>
            <th>STT</th>
            <th>Sinh viên</th>
            <th>Môn học</th>
            <th>Điểm TP1</th>
            <th>TP2</th>
            <th>Cuối kỳ</th>
            <th>Trung bình</th>
            <th>Xếp loại</th>
        </tr>

        <?php
        $args = array(
            'post_type' => 'diem',
            'posts_per_page' => -1,
        );
        $query = new WP_Query($args);
        $stt = 1;
        if ($query->have_posts()) :
            while ($query->have_posts()) : $query->the_post();
                $sinh_vien = get_field('sinh_vien_');
                $mon_hoc = get_field('mon_hoc_');
                ?>
                <tr>
                    <td><?php echo $stt++; ?></td>
                    <td><?php echo $sinh_vien ? get_the_title($sinh_vien[0]) : ''; ?></td>
                    <td><?php echo $mon_hoc ? get_the_title($mon_hoc[0]) : ''; ?></td>
                    <td><?php the_field('diem_thanh_phan_1_'); ?></td>
                    <td><?php the_field('diem_thanh_phan_2_'); ?></td>
                    <td><?php the_field('diem_cuoi_ki_'); ?></td>
                    <td><?php the_field('diem_trung_binh_'); ?></td>
                    <td><?php the_field('xep_loai'); ?></td>
                </tr>
            <?php
            endwhile;
            wp_reset_postdata();
        else :
            echo '<tr><td colspan="8">Không có dữ liệu.</td></tr>';
        endif;
        ?>
    </table>
</div>

<?php get_footer(); ?>
