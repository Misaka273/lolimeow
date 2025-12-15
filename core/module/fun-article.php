<?php
/**
 * @link https://www.boxmoe.com
 * @package lolimeow
 */

// 安全设置--------------------------boxmoe.com--------------------------
if(!defined('ABSPATH')){
    echo'Look your sister';
    exit;
}

// 文章新窗口打开开关--------------------------boxmoe.com--------------------------
function boxmoe_article_new_window() {
    return get_boxmoe('boxmoe_article_new_window_switch', true) ? 'target="_blank"' : '';
}

// 🔗 文章编辑按钮新窗口打开
function boxmoe_edit_post_link_new_tab($link) {
    if (get_boxmoe('boxmoe_article_edit_target_blank')) {
        return str_replace('<a ', '<a target="_blank" ', $link);
    }
    return $link;
}
add_filter('edit_post_link', 'boxmoe_edit_post_link_new_tab');

// 开启所有文章形式支持--------------------------boxmoe.com--------------------------
if(get_boxmoe('boxmoe_article_support_switch')){
    add_theme_support('post-formats', array('image', 'video', 'audio', 'quote', 'link'));
}

//开启特色文章缩略图
    add_theme_support('post-thumbnails');
	

// 缩略图尺寸设定--------------------------boxmoe.com--------------------------
if(get_boxmoe('boxmoe_article_thumbnail_size_switch')){
function boxmoe_article_thumbnail_size($size) {
    $width  = intval(get_boxmoe('boxmoe_article_thumbnail_width')) ?: 300; 
    $height = intval(get_boxmoe('boxmoe_article_thumbnail_height')) ?: 200;
    return array($width, $height); 
}
add_filter('post_thumbnail_size', 'boxmoe_article_thumbnail_size');
}

// 文章缩略图逻辑--------------------------boxmoe.com--------------------------
function boxmoe_article_thumbnail_src() {
    global $post;
    $src='';
    if ($thumbnail_id = get_post_thumbnail_id()) {
        $src=wp_get_attachment_image_url($thumbnail_id, 'full');
    }elseif ($thumbnail_url = get_post_meta(get_the_ID(), '_thumbnail', true)) {
        $src=$thumbnail_url;
    }elseif (preg_match_all('/<img.+src=[\'"]([^\'"]+)[\'"].*>/i', $post->post_content, $matches)) {
        $src=$matches[1][0]; 
    }else{
        if(get_boxmoe('boxmoe_article_thumbnail_random_api')){
            $src=get_boxmoe('boxmoe_article_thumbnail_random_api_url');
        }else{
            $random_images = glob(get_template_directory().'/assets/images/random/*.{jpg,jpeg,png,gif}', GLOB_BRACE);   
            if (!empty($random_images)) {
                $random_key = array_rand($random_images);
                $src = str_replace(get_template_directory(), get_template_directory_uri(), $random_images[$random_key]);
            } else {
                $src = boxmoe_theme_url().'/assets/images/default-thumbnail.jpg';
            }
        }
    }
    return $src ?: boxmoe_theme_url().'/assets/images/default-thumbnail.jpg';
}

//文章点击数换算K--------------------------boxmoe.com--------------------------
function restyle_text($number){
    if ($number >= 1000) {
                  return round($number / 1000, 2) . 'k';
              } else {
                  return $number;
              }
  }
  //文章点击数--------------------------boxmoe.com--------------------------
  function getPostViews($postID){
      $count_key = 'post_views_count';
      $count = get_post_meta($postID, $count_key, true);
      if($count==''){
          delete_post_meta($postID, $count_key);
          add_post_meta($postID, $count_key, '0');
          return "0 View";
      }
      return restyle_text($count);
  }
  function setPostViews($postID) {
      $count_key = 'post_views_count';
      $count = get_post_meta($postID, $count_key, true);
      if($count==''){
          $count = 0;
          delete_post_meta($postID, $count_key);
          add_post_meta($postID, $count_key, '0');
      }else{
          $count++;
          update_post_meta($postID, $count_key, $count);
      }
  }


//修剪标记--------------------------boxmoe.com--------------------------
function _str_cut($str, $start, $width, $trimmarker) {
	$output = preg_replace('/^(?:[\x00-\x7F]|[\xC0-\xFF][\x80-\xBF]+){0,' . $start . '}((?:[\x00-\x7F]|[\xC0-\xFF][\x80-\xBF]+){0,' . $width . '}).*/s', '\1', $str);
	return $output . $trimmarker;
}

//自定义段长度--------------------------boxmoe.com--------------------------
function custom_excerpt_length( $length ){
return 200;
}
add_filter( 'excerpt_length', 'custom_excerpt_length');

//文章、评论内容缩短--------------------------boxmoe.com--------------------------
function _get_excerpt($limit = 60, $after = '...') { 
    if ( post_password_required() ) {
        $fallback = '无法提供摘要。这是一篇受保护的文章。';
        $text = get_boxmoe('boxmoe_article_password_excerpt_text', $fallback);
        return $text;
    }
    $excerpt = get_the_excerpt();
    if (mb_strlen($excerpt) > $limit) {
        return _str_cut(strip_tags($excerpt), 0, $limit, $after);
    }
    return $excerpt;
}

// 表格替换--------------------------boxmoe.com--------------------------
function boxmoe_table_replace($text){
	$replace = array( '<table>' => '<div class="table-responsive"><table class="table" >','</table>' => '</table></div>' );
	$text = str_replace(array_keys($replace), $replace, $text);
	return $text;}
add_filter('the_content', 'boxmoe_table_replace');

//防止代码转义--------------------------boxmoe.com--------------------------
function boxmoe_prettify_esc_html($content){
    $regex = '/(<pre\s+[^>]*?class\s*?=\s*?[",\'].*?prettyprint.*?[",\'].*?>)(.*?)(<\/pre>)/sim';
    return preg_replace_callback($regex, 'boxmoe_prettify_esc_callback', $content);}
function boxmoe_prettify_esc_callback($matches){
    $tag_open = $matches[1];
    $content = $matches[2];
    $tag_close = $matches[3];
    $content = esc_html($content);
    return $tag_open . $content . $tag_close;}
add_filter('the_content', 'boxmoe_prettify_esc_html', 2);
add_filter('comment_text', 'boxmoe_prettify_esc_html', 2);

//强制兼容--------------------------boxmoe.com--------------------------
function boxmoe_prettify_replace($text){
	$replace = array( '<pre>' => '<pre class="prettyprint linenums" >','<pre class="prettyprint">' => '<pre class="prettyprint linenums" >' );
	$text = str_replace(array_keys($replace), $replace, $text);
	return $text;}
add_filter('the_content', 'boxmoe_prettify_replace');

// 自动设置特色图片--------------------------boxmoe.com--------------------------
function autoset_featured_image() {
    global $post;
    if (!is_object($post)) return;
    $already_has_thumb = has_post_thumbnail($post->ID);
    if (!$already_has_thumb)  {
        $attached_image = get_children( "post_parent=$post->ID&post_type=attachment&post_mime_type=image&numberposts=1" );
        if ($attached_image) {
            foreach ($attached_image as $attachment_id => $attachment) {
                set_post_thumbnail($post->ID, $attachment_id);
            }
        }
    }
}
add_action( 'the_post', 'autoset_featured_image' );
add_action( 'save_post', 'autoset_featured_image' );
add_action( 'draft_to_publish', 'autoset_featured_image' );
add_action( 'new_to_publish', 'autoset_featured_image' );
add_action( 'pending_to_publish', 'autoset_featured_image' );
add_action( 'future_to_publish', 'autoset_featured_image' );


// 自适应图片--------------------------boxmoe.com--------------------------
function boxmoe_remove_width_height($content) {
    preg_match_all('/<[img|IMG].*?src=[\'|"](.*?(?:[\.gif|\.jpg|\.png\.bmp\.webp]))[\'|"].*?[\/]?>/', $content, $images);
    if (!empty($images)) {
        foreach ($images[0] as $index => $value) {
            $new_img = preg_replace('/(width|height)="\d*"\s/', "", $images[0][$index]);
            $content = str_replace($images[0][$index], $new_img, $content);
        }
    }
    return $content;
}
add_filter('the_content', 'boxmoe_remove_width_height', 99);


// 图片懒加载--------------------------boxmoe.com--------------------------
function boxmoe_lazy_content_load_images($content) {
    $content = preg_replace_callback('/<img([^>]*?)src=([\'"])([^\'"]+)\2/i', 
        function($matches) {
            if (strpos($matches[0], 'data-src') !== false) {
                return $matches[0];
            }
            return '<img' . $matches[1] 
                . ' src="' . boxmoe_lazy_load_images() . '"' 
                . ' data-src="' . $matches[3] . '"'
                . ' class="lazy"'
                . ' loading="lazy"';
        },
        $content);
    return $content;
}
if(!is_admin()){
    add_filter('the_content', 'boxmoe_lazy_content_load_images', 99);
}

function boxmoe_disable_lazy_for_gifs($content) {
    $content = preg_replace_callback('/<img[^>]*>/i', function($imgTag) {
        $tag = $imgTag[0];
        if (strpos($tag, 'data-src') === false) return $tag;
        if (!preg_match('/data-src=([\'\"])([^\'\"]+)\1/i', $tag, $m)) return $tag;
        $dataSrc = $m[2];
        if (!preg_match('/\.gif(\?.*)?$/i', $dataSrc)) return $tag;
        $base = $dataSrc;
        $query = '';
        if (preg_match('/^(.*?)(\?.*)$/', $dataSrc, $qm)) { $base = $qm[1]; $query = $qm[2]; }
        $base = preg_replace('/-\d+x\d+(?=\.gif$)/i', '', $base);
        $dataSrc = $base . $query;
        $updated = $tag;
        $updated = preg_replace_callback('/\sclass=([\'\"])([^\'\"]*)\blazy\b([^\'\"]*)\1/i', function($cm){
            $cls = trim(preg_replace('/\blazy\b/i', '', $cm[2].$cm[3]));
            return $cls ? ' class="'.$cls.'"' : '';
        }, $updated);
        $updated = preg_replace('/\sloading=([\'\"])lazy\1/i', '', $updated);
        $updated = preg_replace('/\ssrcset=([\'\"])([^\'\"]+)\1/i', '', $updated);
        $updated = preg_replace('/\ssizes=([\'\"])([^\'\"]+)\1/i', '', $updated);
        $updated = preg_replace('/\sdata-src=([\'\"])([^\'\"]+)\1/i', '', $updated);
        if (preg_match('/\ssrc=([\'\"])([^\'\"]+)\1/i', $updated)) {
            $updated = preg_replace('/\ssrc=([\'\"])([^\'\"]+)\1/i', ' src="'.$dataSrc.'"', $updated);
        } else {
            $updated = preg_replace('/^<img/i', '<img src="'.$dataSrc.'"', $updated);
        }
        return $updated;
    }, $content);
    return $content;
}
if(!is_admin()){
    add_filter('the_content', 'boxmoe_disable_lazy_for_gifs', 100);
}

function boxmoe_disable_lazy_for_images($content) {
    $content = preg_replace_callback('/<img[^>]*>/i', function($imgTag) {
        $tag = $imgTag[0];
        if (strpos($tag, 'data-src') === false) return $tag;
        if (!preg_match('/data-src=([\'\"])([^\'\"]+)\1/i', $tag, $m)) return $tag;
        $dataSrc = $m[2];
        if (preg_match('/\.gif(\?.*)?$/i', $dataSrc)) return $tag;
        $updated = $tag;
        $updated = preg_replace_callback('/\sclass=([\'\"])([^\'\"]*)\blazy\b([^\'\"]*)\1/i', function($cm){
            $cls = trim(preg_replace('/\blazy\b/i', '', $cm[2].$cm[3]));
            return $cls ? ' class="'.$cls.'"' : '';
        }, $updated);
        $updated = preg_replace('/\sloading=(["\'])lazy\1/i', '', $updated);
        $updated = preg_replace('/\ssrcset=([\'\"])([^\'\"]+)\1/i', '', $updated);
        $updated = preg_replace('/\ssizes=([\'\"])([^\'\"]+)\1/i', '', $updated);
        $updated = preg_replace('/\sdata-src=([\'\"])([^\'\"]+)\1/i', '', $updated);
        if (preg_match('/\ssrc=([\'\"])([^\'\"]+)\1/i', $updated)) {
            $updated = preg_replace('/\ssrc=([\'\"])([^\'\"]+)\1/i', ' src="'.$dataSrc.'"', $updated);
        } else {
            $updated = preg_replace('/^<img/i', '<img src="'.$dataSrc.'"', $updated);
        }
        return $updated;
    }, $content);
    return $content;
}
if(!is_admin()){
    add_filter('the_content', 'boxmoe_disable_lazy_for_images', 101);
}

// fancybox--------------------------boxmoe.com--------------------------
function boxmoe_fancybox_replace ($content) {
    global $post;
    $pattern = "/<a(.*?)href=('|\")([A-Za-z0-9\/_\.\~\:-]*?)(-\d+x\d+)?(\.(?:bmp|gif|jpeg|png|jpg|webp))('|\")([^\>]*?)>/i";
    $replacement = '<a$1href=$2$3$5$6$7 class="fancybox" data-fancybox="gallery" data-src="$3$5">';
    $content = preg_replace($pattern, $replacement, $content);
    return $content;
}
add_filter('the_content', 'boxmoe_fancybox_replace', 99);

// fancybox-erphpdown
//add_filter('the_content', 'erphpdownbuy_replace', 99);
function erphpdownbuy_replace ($content) {
	global $post;
	$pattern = "/<a(.*?)class=\"erphpdown-iframe erphpdown-buy\"(.*?)>/i";
	$replacement = '<a$1$2$3$4$5$6 class="fancybox" data-fancybox data-type="iframe" class="erphpdown-buy">';
	$content = preg_replace($pattern, $replacement, $content);
	return $content;
}

// 分页导航函数--------------------------boxmoe.com--------------------------
if ( ! function_exists( 'boxmoe_pagination' ) ) :
function boxmoe_pagination($query = null) {
    $paging_type = get_boxmoe('boxmoe_article_paging_type');
    if($paging_type == 'multi'){
        $p = 1;
        if ( is_singular() ) return;
        global $wp_query, $paged;
        $max_page = $wp_query->max_num_pages;
        echo '<div class="col-lg-12 col-md-12 pagenav">';
        echo '<nav class="d-flex justify-content-center">';
        echo '<ul class="pagination">';
        if ( empty( $paged ) ) $paged = 1;
        if($paged !== 1 ) p_link(0);
        $start = max(1, $paged - $p);
        $end = min($paged + ($p * 1), $max_page);
        if ($start > 1) {
            p_link(1);
            if ($start > 1) echo "<li class=\"page-item\"><a class=\"page-link\">···</a></li>";
        }
        for( $i = $start; $i <= $end; $i++ ) { 
            if ( $i > 0 && $i <= $max_page ) {
                $i == $paged ? print "<li class=\"page-item active\"><a class=\"page-link\" href=\"#\">{$i}</a></li>" : p_link( $i );
            }
        }
        if ($end < $max_page) {
            if ($end < $max_page - 1) echo "<li class=\"page-item\"><a class=\"page-link\">···</a></li>";
            p_link($max_page, '', 1);
        }
        echo '</ul>
        </nav>
      </div>';
    }elseif($paging_type == 'next'){
        global $wp_query;
        $query = $query ?: $wp_query;
        $current = max(1, get_query_var('paged'));
        $total = $query->max_num_pages;
        
        echo '<nav class="pagination-next-prev"><ul class="pagination justify-content-center">';
        if ($current > 1) {
            echo '<li class="page-item">';
            previous_posts_link('<span class="page-link"><i class="fa fa-arrow-left"></i> '.__('上一页', 'boxmoe').'</span>');
            echo '</li>';
        }
        if ($current < $total) {
            echo '<li class="page-item ms-2">';
            next_posts_link('<span class="page-link">'.__('下一页', 'boxmoe').' <i class="fa fa-arrow-right"></i></span>', $total);
            echo '</li>';
        }
        echo '</ul></nav>';
    }elseif($paging_type == 'loadmore'){
    }
}
function p_link( $i, $title = '', $w='' ) {
    if ( $title == '' ) $title = __('页', 'boxmoe-com')." {$i}";
    $itext = $i;
    if( $i == 0 ){
        $itext = __('<i class="fa fa-angle-double-left"></i>', 'boxmoe-com');
    }
    if( $w ){
        $itext = __('<i class="fa fa-angle-double-right"></i>', 'boxmoe-com');
    }
    echo "<li class=\"page-item\"><a class=\"page-link\" href='", esc_html( get_pagenum_link( $i ) ), "'>{$itext}</a></li>";
}
endif;


// 文章点赞数获取
function getPostLikes($postID) {
    $count_key = 'post_likes_count';
    $count = get_post_meta($postID, $count_key, true);
    if($count == ''){
        delete_post_meta($postID, $count_key);
        add_post_meta($postID, $count_key, '0');
        return "0";
    }
    return $count;
}

function boxmoe_post_like() {
    $post_id = isset($_POST['post_id']) ? absint($_POST['post_id']) : 0;
    
    if (!$post_id) {
        wp_send_json_error(['message' => 'Invalid post ID']);
        return;
    }

    if (!get_post($post_id)) {
        wp_send_json_error(['message' => '文章不存在']);
        return;
    }

    $user_ip = $_SERVER['REMOTE_ADDR'];
    $transient_key = 'post_like_' . $post_id . '_' . md5($user_ip);

    if (false === get_transient($transient_key)) {
        $count = (int)get_post_meta($post_id, 'post_likes_count', true);
        $count++;
        update_post_meta($post_id, 'post_likes_count', $count);
        set_transient($transient_key, '1', DAY_IN_SECONDS);
        
        wp_send_json_success([
            'count' => $count,
            'message' => '点赞成功'
        ]);
    } else {
        wp_send_json_error(['message' => '您已经点过赞了']);
    }
}

add_action('wp_ajax_post_like', 'boxmoe_post_like');
add_action('wp_ajax_nopriv_post_like', 'boxmoe_post_like');

// 检查文章是否被收藏
function isPostFavorited($post_id) {
    if (!is_user_logged_in()) return false;
    
    $user_id = get_current_user_id();
    $favorites = get_user_meta($user_id, 'user_favorites', true);
    
    if (!is_array($favorites)) {
        $favorites = array();
    }
    
    return in_array($post_id, $favorites);
}

// 处理文章收藏
function boxmoe_post_favorite() {
    if (!is_user_logged_in()) {
        wp_send_json_error(['message' => '请先登录']);
        return;
    }

    $post_id = isset($_POST['post_id']) ? absint($_POST['post_id']) : 0;
    
    if (!$post_id) {
        wp_send_json_error(['message' => '无效的文章ID']);
        return;
    }

    if (!get_post($post_id)) {
        wp_send_json_error(['message' => '文章不存在']);
        return;
    }

    $user_id = get_current_user_id();
    $favorites = get_user_meta($user_id, 'user_favorites', true);
    
    if (!is_array($favorites)) {
        $favorites = array();
    }

    $is_favorited = in_array($post_id, $favorites);
    
    if ($is_favorited) {
        $favorites = array_diff($favorites, array($post_id));
        $message = '取消收藏成功';
        $status = false;
        // Update post favorites count
        $count = (int)get_post_meta($post_id, 'post_favorites_count', true);
        $count = max(0, $count - 1);
        update_post_meta($post_id, 'post_favorites_count', $count);
    } else {
        $favorites[] = $post_id;
        $message = '收藏成功';
        $status = true;
        // Update post favorites count
        $count = (int)get_post_meta($post_id, 'post_favorites_count', true);
        $count++;
        update_post_meta($post_id, 'post_favorites_count', $count);
    }
    update_user_meta($user_id, 'user_favorites', array_values($favorites));
    wp_send_json_success([
        'message' => $message,
        'status' => $status
    ]);
}

add_action('wp_ajax_post_favorite', 'boxmoe_post_favorite');

// 处理删除收藏
function boxmoe_delete_favorite() {
    if (!is_user_logged_in()) {
        wp_send_json_error(['message' => '请先登录']);
        return;
    }

    $post_id = isset($_POST['post_id']) ? absint($_POST['post_id']) : 0;
    
    if (!$post_id) {
        wp_send_json_error(['message' => '无效的文章ID']);
        return;
    }

    $user_id = get_current_user_id();
    $favorites = get_user_meta($user_id, 'user_favorites', true);
    
    if (!is_array($favorites)) {
        wp_send_json_error(['message' => '没有找到收藏记录']);
        return;
    }
    $favorites = array_diff($favorites, array($post_id));
    update_user_meta($user_id, 'user_favorites', array_values($favorites));
    // Update post favorites count
    $count = (int)get_post_meta($post_id, 'post_favorites_count', true);
    $count = max(0, $count - 1);
    update_post_meta($post_id, 'post_favorites_count', $count);
    wp_send_json_success([
        'message' => '删除收藏成功'
    ]);
}

add_action('wp_ajax_delete_favorite', 'boxmoe_delete_favorite');

// 🔄 文章排序逻辑
function boxmoe_custom_post_order($query) {
    if (is_admin() || !$query->is_main_query()) {
        return;
    }
    
    // Only apply to home, archive, and search pages
    if (!$query->is_home() && !$query->is_archive() && !$query->is_search()) {
        return;
    }
    
    // 首页文章分类筛选
    if ($query->is_home()) {
        $selected_categories_str = get_boxmoe('boxmoe_home_article_categories', '');
        
        if (!empty($selected_categories_str)) {
            // 使用正则表达式分割字符串，支持，、,三个标点符号
            $categories_array = preg_split('/[,，、]+/', $selected_categories_str);
            // 过滤空值并转换为整数
            $categories_ids = array();
            foreach ($categories_array as $cat_id) {
                $cat_id = trim($cat_id);
                if (!empty($cat_id) && is_numeric($cat_id)) {
                    $categories_ids[] = intval($cat_id);
                }
            }
            // 移除重复ID
            $categories_ids = array_unique($categories_ids);
            
            if (!empty($categories_ids)) {
                // 获取当前的置顶文章
                $sticky_posts = get_option('sticky_posts');
                
                if (!empty($sticky_posts)) {
                    // 筛选出属于所选分类的置顶文章
                    $filtered_sticky_posts = array();
                    foreach ($sticky_posts as $sticky_post_id) {
                        $post_categories = wp_get_post_categories($sticky_post_id);
                        // 检查文章是否至少属于一个所选分类
                        $has_matching_category = !empty(array_intersect($post_categories, $categories_ids));
                        if ($has_matching_category) {
                            $filtered_sticky_posts[] = $sticky_post_id;
                        }
                    }
                    
                    // 更新置顶文章列表，只保留符合条件的置顶文章
                    $query->set('post__not_in', array_diff($sticky_posts, $filtered_sticky_posts));
                }
                
                // 设置分类筛选条件
                $query->set('category__in', $categories_ids);
            }
        }
    }

    if (isset($_GET['orderby'])) {
        $orderby = sanitize_text_field($_GET['orderby']);
        
        // Determine order
        $order = isset($_GET['order']) ? strtoupper(sanitize_text_field($_GET['order'])) : '';
        
        // Default to DESC if not specified, except for title where we might want ASC default
        if (empty($order)) {
             $order = ($orderby == 'title') ? 'ASC' : 'DESC';
        }
        
        // Validate order
        if (!in_array($order, ['ASC', 'DESC'])) {
            $order = 'DESC';
        }

        $query->set('order', $order);

        switch ($orderby) {
            case 'title':
                $query->set('orderby', 'title');
                break;
            case 'modified':
                $query->set('orderby', 'modified');
                break;
            case 'date':
                $query->set('orderby', 'date');
                break;
            case 'views':
                $query->set('meta_key', 'post_views_count');
                $query->set('orderby', 'meta_value_num');
                break;
            case 'likes':
                $query->set('meta_key', 'post_likes_count');
                $query->set('orderby', 'meta_value_num');
                break;
            case 'favorites':
                $query->set('meta_key', 'post_favorites_count');
                $query->set('orderby', 'meta_value_num');
                break;
        }
    }
}
add_action('pre_get_posts', 'boxmoe_custom_post_order');
