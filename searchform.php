<?php
/**
 * @link https://www.boxmoe.com
 * @package lolimeow
 * @author 专收爆米花
 * @author 白木 <https://gl.baimu.live/864> (二次创作)
 */
//boxmoe.com===安全设置=阻止直接访问主题文件
if(!defined('ABSPATH')){echo'Look your sister';exit;}
?>
<form role="search" method="get" class="search-form" action="<?php echo esc_url(home_url('/')); ?>">
    <div class="search-wrap">
        <input type="search" class="search-input" placeholder="<?php echo esc_attr_x('搜索...', 'placeholder', 'text_domain'); ?>" 
               value="<?php echo get_search_query(); ?>" name="s" required>
        <button type="submit" class="search-submit">
            <i class="fa fa-search"></i>
        </button>
    </div>
</form>