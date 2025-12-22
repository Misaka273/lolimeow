<?php
/**
 * @link https://gl.baimu.live
 * @package 白木🥰
 * @description 音乐播放器设置面板
 * @author 初叶🍂 <www.chuyel.top>
 */

//boxmoe.com===安全设置=阻止直接访问主题文件
if(!defined('ABSPATH')){exit;}

$options[] = array(
    'name' => '音乐播放器设置',
    'id' => 'music_player_settings',
    'icon' => 'dashicons-playlist-audio',
    'type' => 'heading');

$options[] = array(
    'group' => 'start',
    'group_title' => '音乐播放器基本设置',
    'name' => '启用音乐播放器',
    'id' => 'boxmoe_music_player_switch',
    'type' => 'checkbox',
    'std' => false,
    'desc' => '开启后将在网站中显示音乐播放器');

$options[] = array(
    'name' => '播放器位置',
    'id' => 'boxmoe_music_player_position',
    'std' => 'bottom-right',
    'type' => 'select',
    'options' => array(
        'top-left' => '左上角',
        'top-right' => '右上角',
        'bottom-left' => '左下角',
        'bottom-right' => '右下角'
    ));

$options[] = array(
    'name' => '播放器大小',
    'id' => 'boxmoe_music_player_size',
    'std' => 'medium',
    'type' => 'select',
    'options' => array(
        'small' => '小',
        'medium' => '中',
        'large' => '大'
    ));

$options[] = array(
    'name' => '自动播放',
    'id' => 'boxmoe_music_player_autoplay',
    'type' => 'checkbox',
    'std' => false,
    'desc' => '开启后页面加载时自动播放音乐');

$options[] = array(
    'name' => '默认音量',
    'id' => 'boxmoe_music_player_volume',
    'std' => '50',
    'type' => 'text',
    'class' => 'mini',
    'desc' => '设置播放器默认音量（0-100）');

$options[] = array(
    'name' => '循环播放',
    'id' => 'boxmoe_music_player_loop',
    'type' => 'checkbox',
    'std' => true,
    'desc' => '开启后音乐将循环播放');

$options[] = array(
    'name' => '播放器主题色',
    'id' => 'boxmoe_music_player_theme_color',
    'std' => '#8b3dff',
    'type' => 'color',
    'desc' => '设置播放器的主题颜色');

$options[] = array(
    'group' => 'end',
    'name' => '自定义CSS',
    'id' => 'boxmoe_music_player_custom_css',
    'std' => '',
    'settings' => array('rows' => 3),
    'type' => 'textarea',
    'desc' => '添加自定义CSS样式，用于调整播放器外观');

$options[] = array(
    'group' => 'start',
    'group_title' => '音乐源设置',
    'name' => '音乐服务器',
    'id' => 'boxmoe_music_player_server',
    'std' => 'netease',
    'type' => 'select',
    'options' => array(
        'netease' => '网易云音乐',
        'tencent' => 'QQ音乐',
        'xiami' => '虾米音乐'
    ));

$options[] = array(
    'name' => '音乐类型',
    'id' => 'boxmoe_music_player_type',
    'std' => 'playlist',
    'type' => 'select',
    'options' => array(
        'song' => '单曲',
        'album' => '专辑',
        'artist' => '艺术家',
        'playlist' => '歌单'
    ));

$options[] = array(
    'name' => '自定义API地址',
    'id' => 'boxmoe_music_player_api',
    'std' => '',
    'type' => 'text',
    'desc' => '输入自定义音乐API地址，留空则使用预设API');

$options[] = array(
    'name' => '预设API选择',
    'id' => 'boxmoe_music_player_default_api',
    'std' => 'default',
    'type' => 'select',
    'options' => array(
        'default' => '默认 API',
        'tencent_vip' => '仅 QQ 音乐 API（支持会员）'
    ),
    'desc' => '选择预设的音乐API，当自定义API为空时使用');

$options[] = array(
    'group' => 'end',
    'name' => '音乐ID',
    'id' => 'boxmoe_music_player_id',
    'std' => '6814606449',
    'type' => 'text',
    'desc' => '输入音乐ID，如网易云音乐歌单ID');
