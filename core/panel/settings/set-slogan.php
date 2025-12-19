<?php
if(!defined('ABSPATH')){echo'Look your sister';exit;}

$options[] = array(
    'name' => __('页面标语设置', 'ui_boxmoe_com'),
    'icon' => 'dashicons-admin-page',
    'type' => 'heading'
);

$options[] = array(
    'name' => __('移除默认图标', 'ui_boxmoe_com'),
    'id'   => 'boxmoe_slogan_remove_icon',
    'type' => 'checkbox',
    'std'  => false,
    'desc' => __('开启后将移除标语前的默认图标', 'ui_boxmoe_com')
);

$options[] = array(
    'name' => __('首页标语', 'ui_boxmoe_com'),
    'id'   => 'boxmoe_slogan_home_text',
    'type' => 'text',
    'std'  => '首页'
);

$options[] = array(
    'name' => __('分类页标语', 'ui_boxmoe_com'),
    'id'   => 'boxmoe_slogan_category_text',
    'type' => 'text',
    'std'  => '分类'
);

$options[] = array(
    'name' => __('标签页标语', 'ui_boxmoe_com'),
    'id'   => 'boxmoe_slogan_tag_text',
    'type' => 'text',
    'std'  => '标签'
);

$options[] = array(
    'name' => __('搜索页标语', 'ui_boxmoe_com'),
    'id'   => 'boxmoe_slogan_search_text',
    'type' => 'text',
    'std'  => '搜索'
);

$options[] = array(
    'name' => __('404页标语', 'ui_boxmoe_com'),
    'id'   => 'boxmoe_slogan_404_text',
    'type' => 'text',
    'std'  => '404'
);

$options[] = array(
    'name' => __('作者页标语', 'ui_boxmoe_com'),
    'id'   => 'boxmoe_slogan_author_text',
    'type' => 'text',
    'std'  => '作者'
);

$options[] = array(
    'name' => __('日期页标语', 'ui_boxmoe_com'),
    'id'   => 'boxmoe_slogan_date_text',
    'type' => 'text',
    'std'  => '日期'
);

$options[] = array(
    'name' => __('归档页标语', 'ui_boxmoe_com'),
    'id'   => 'boxmoe_slogan_archive_text',
    'type' => 'text',
    'std'  => '归档'
);

$options[] = array(
    'name' => __('文章页标语', 'ui_boxmoe_com'),
    'id'   => 'boxmoe_slogan_post_text',
    'type' => 'text',
    'std'  => '文章'
);

$options[] = array(
    'name' => __('页面标语（默认）', 'ui_boxmoe_com'),
    'id'   => 'boxmoe_slogan_page_text',
    'type' => 'text',
    'std'  => '页面'
);

$options[] = array(
    'group' => 'start',
    'group_title' => '特定模板标语',
    'name' => __('友链页面标语', 'ui_boxmoe_com'),
    'id'   => 'boxmoe_slogan_page_links_text',
    'type' => 'text',
    'std'  => '我的朋友'
);

$options[] = array(
    'group' => 'end',
    'name' => __('会员中心页面标语', 'ui_boxmoe_com'),
    'id'   => 'boxmoe_slogan_page_user_center_text',
    'type' => 'text',
    'std'  => '会员中心'
);

