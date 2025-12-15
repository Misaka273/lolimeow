<?php
/**
 * @link https://www.boxmoe.com
 * @package lolimeow
 */

// 🥳 样式统一管理
// 🔗 全局功能激活函数
// 💕 tabs 插件

//boxmoe.com===安全设置=阻止直接访问主题文件
if(!defined('ABSPATH')){
    echo'Look your sister';
    exit;
}

$options[] = array(
    'name' => __('用户身份管理', 'ui_boxmoe_com'),
    'icon' => 'dashicons-id-alt',
    'type' => 'heading');

$options[] = array(
    'group' => 'start',
    'group_title' => '用户身份管理说明',
    'type' => 'info', 
    'desc' => '<div class="boxmoe-info-alert">在这里您可以自定义各个用户角色的显示名称，新增自定义角色，以及删除不需要的角色。</div>'
);

$options[] = array(
    'group' => 'end',
    'type' => 'info',
    'std' => ''
);

// 获取所有可编辑的角色
global $wp_roles;
if ( ! isset( $wp_roles ) ) {
    $wp_roles = new WP_Roles();
}
$roles = $wp_roles->get_names();

// 构建角色选择下拉菜单（用于继承权限）
$role_options_html = '<option value="">不复制（仅读权限）</option>';
foreach ($roles as $r_key => $r_name) {
    // 尝试获取翻译后的角色名称（中文）
    $r_translated_name = translate_user_role($r_name);
    // 使用角色Key作为英文名称（首字母大写）
    $r_english_name = ucfirst($r_key);
    $role_options_html .= '<option value="' . esc_attr($r_key) . '">' . esc_html($r_translated_name . ' ' . $r_english_name) . '</option>';
}

// 新增角色表单 HTML
$add_role_form = '
<div class="boxmoe-role-manager-form">
    
    <!-- 角色标识 -->
    <div class="section-role-item" style="margin-bottom: 20px;">
        <h4 class="heading"><span class="dashicons dashicons-id-alt"></span> 角色标识 (英文)</h4>
        <div class="option">
            <div class="controls">
                <input type="text" id="boxmoe_new_role_slug" placeholder="例如: vip_user" class="of-input" style="width:100%;">
            </div>
            <div class="explain">请输入角色的唯一标识符，仅限英文小写字母和下划线。</div>
        </div>
    </div>
    
    <!-- 角色名称 -->
    <div class="section-role-item" style="margin-bottom: 20px;">
        <h4 class="heading"><span class="dashicons dashicons-nametag"></span> 角色名称 (显示名)</h4>
        <div class="option">
            <div class="controls">
                <input type="text" id="boxmoe_new_role_name" placeholder="例如: VIP会员" class="of-input" style="width:100%;">
            </div>
            <div class="explain">显示在后台和前端的用户身份名称。</div>
        </div>
    </div>

    <!-- 权限分配模式 -->
    <div class="section-role-item" style="margin-bottom: 20px;">
        <h4 class="heading"><span class="dashicons dashicons-admin-settings"></span> 权限分配模式</h4>
        <div class="option">
            <div class="controls">
                 <label style="margin-right: 15px; cursor: pointer;"><input type="radio" name="boxmoe_role_mode" value="inherit" checked> 继承现有角色 (单选)</label>
                 <label style="cursor: pointer;"><input type="radio" name="boxmoe_role_mode" value="custom"> 自定义组合 (多选)</label>
            </div>
            <div class="explain">选择“继承”将完全复制某一个角色的权限；选择“自定义”可以组合多个角色的能力。</div>
        </div>
    </div>

    <!-- 继承权限 -->
    <div id="boxmoe_role_inherit_wrap" class="section-role-item" style="margin-bottom: 20px;">
        <h4 class="heading"><span class="dashicons dashicons-admin-network"></span> 继承权限自</h4>
        <div class="option">
            <div class="controls">
                <select id="boxmoe_new_role_copy" class="of-input" style="width:100%;">' . $role_options_html . '</select>
            </div>
            <div class="explain">新角色将拥有所选角色的所有权限。如果不选，默认仅拥有“读取”权限。</div>
        </div>
    </div>

    <!-- 自定义权限 -->';

    // 定义能力映射
    $capabilities_list = array(
        'publish_posts' => '发布文章',
        'publish_pages' => '发布页面',
        'edit_posts' => '编辑文章',
        'edit_pages' => '编辑页面',
        'delete_posts' => '删除文章',
        'delete_pages' => '删除页面',
        'upload_files' => '上传文件',
        'moderate_comments' => '审核评论',
        'manage_categories' => '管理分类',
        'manage_links' => '管理链接',
        'install_plugins' => '安装插件',
        'activate_plugins' => '启用插件',
        'delete_plugins' => '卸载插件',
        'install_themes' => '安装主题',
        'switch_themes' => '切换主题',
        'delete_themes' => '卸载主题',
        'create_users' => '创建用户',
        'edit_users' => '编辑用户',
        'delete_users' => '删除用户',
        'promote_users' => '分配角色',
        'import' => '导入数据',
        'export' => '导出数据',
        'manage_options' => '站点设置'
    );

    // 生成复选框网格 HTML
    $caps_grid_html = '';
    foreach ($capabilities_list as $cap_key => $cap_label) {
        $caps_grid_html .= '
        <div class="boxmoe-cap-item" style="width: 25%; float: left; margin-bottom: 15px; box-sizing: border-box; padding-right: 10px;">
            <label style="display: flex; align-items: center; cursor: pointer; user-select: none;">
                <input type="checkbox" class="boxmoe_custom_cap_single" value="' . $cap_key . '" style="margin-right: 8px;">
                <span style="font-size: 13px; color: #555;">' . $cap_label . '</span>
            </label>
        </div>';
    }
    // 清除浮动
    $caps_grid_html .= '<div style="clear:both;"></div>';
    
    $add_role_form .= '
    <div id="boxmoe_role_custom_wrap" class="section-role-item" style="margin-bottom: 20px; display:none;">
        <h4 class="heading"><span class="dashicons dashicons-list-view"></span> 角色能力细分</h4>
        <div class="option">
            <div class="controls">
                <button type="button" id="boxmoe_open_caps_modal" class="button" style="padding: 5px 15px; height: auto; display: inline-flex; align-items: center; gap: 5px;">
                    <span class="dashicons dashicons-edit"></span> 选择权限功能
                </button>
                <span id="boxmoe_selected_caps_count" style="margin-left: 10px; color: #666; font-size: 12px; line-height: 30px; display: inline-block; vertical-align: middle;">已选择 0 项权限</span>
            </div>
            <div class="explain">点击按钮打开权限选择窗口，勾选该角色需要具备的具体功能。</div>
        </div>
    </div>

    <!-- 权限选择模态框 (扁平圆角风) -->
    <div id="boxmoe_caps_modal" style="display:none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; z-index: 100000;">
        <div class="boxmoe-modal-backdrop" style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); backdrop-filter: blur(2px);"></div>
        <div class="boxmoe-modal-content" style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); background: #fff; width: 700px; max-width: 90%; border-radius: 16px; box-shadow: 0 15px 40px rgba(0,0,0,0.2); overflow: hidden; display: flex; flex-direction: column; max-height: 85vh; animation: boxmoeModalFadeIn 0.3s ease;">
            <div class="boxmoe-modal-header" style="padding: 20px 25px; border-bottom: 1px solid #f0f0f0; display: flex; justify-content: space-between; align-items: center; background: #fff;">
                <h3 style="margin: 0; font-size: 18px; font-weight: 600; color: #333;">选择权限功能</h3>
                <button type="button" class="boxmoe-modal-close" style="background: none; border: none; font-size: 24px; cursor: pointer; color: #ccc; line-height: 1; padding: 0;">&times;</button>
            </div>
            <div class="boxmoe-modal-body" style="padding: 25px; overflow-y: auto; flex-grow: 1; background: #fafafa;">
                <div style="background: #fff; padding: 20px; border-radius: 8px; border: 1px solid #eee;">
                ' . $caps_grid_html . '
                </div>
            </div>
            <div class="boxmoe-modal-footer" style="padding: 15px 25px; border-top: 1px solid #f0f0f0; text-align: right; background: #fff;">
                <button type="button" class="button boxmoe-modal-close-btn" style="margin-right: 10px;">取消</button>
                <button type="button" class="button button-primary boxmoe-modal-confirm">确定选择</button>
            </div>
        </div>
    </div>
    <style>
    @keyframes boxmoeModalFadeIn {
        from { opacity: 0; transform: translate(-50%, -45%); }
        to { opacity: 1; transform: translate(-50%, -50%); }
    }
    .boxmoe-cap-item:hover label span { color: #2271b1; }
    </style>

    <div class="boxmoe-form-group-btn" style="text-align: right; margin-top: 20px;">
        <button type="button" id="boxmoe_add_role_btn" class="button button-primary boxmoe-add-role-btn">创建新角色</button>
    </div>
</div>
';

$options[] = array(
    'group' => 'start',
    'group_title' => '新增角色',
    'id' => 'boxmoe_add_new_role_area',
    'type' => 'boxmoe_custom_role_form',
    'custom_html' => $add_role_form
);

$options[] = array(
    'group' => 'end',
    'id' => 'boxmoe_add_new_role_end',
    'type' => 'boxmoe_custom_role_form',
    'custom_html' => '',
    'desc' => ''
);

$system_roles = array('administrator', 'editor', 'author', 'contributor', 'subscriber');
$roles_table_html = '<div class="boxmoe-role-manager-form">';

$system_roles_html = '';
$custom_roles_html = '';

foreach ($roles as $role_key => $role_name) {
    // 🔍 尝试获取翻译后的角色名称
    $translated_name = translate_user_role($role_name); // ⬅️ 获取角色翻译名称
    
    // 💾 获取保存的自定义名称
    $custom_name = get_boxmoe('boxmoe_custom_role_name_' . $role_key, $role_name); // ⬅️ 从选项中获取自定义名称
    
    // 🏷️ 确定最终显示名称
    if (empty($custom_name) || $custom_name == $role_name) {
        $display_name = $translated_name;
    } else {
        $display_name = $custom_name;
    }
    
    // 🛡️ 判断是否为系统角色
    $is_system = in_array($role_key, $system_roles);

    $role_item_html = '
    <div class="section-role-item" style="margin-bottom: 20px;">
        <h4 class="heading">
            <span class="dashicons dashicons-admin-users"></span> ' . esc_html($role_key) . '
            ' . ($is_system ? '<span style="float:right;display:inline-block;padding:3px 8px;background:#2271b1;color:#fff;border-radius:4px;font-size:11px;font-weight:normal;letter-spacing:1px;margin-left: 10px;">系统内置</span>' : '') . '
        </h4>
        <div class="option">
            <div class="controls" style="display:flex;gap:10px;align-items:center;">
                <input type="text" 
                       name="options-framework-theme[boxmoe_custom_role_name_' . esc_attr($role_key) . ']" 
                       value="' . esc_attr($display_name) . '" 
                       class="of-input boxmoe-role-name-input" 
                       style="flex-grow:1;"
                       data-role-slug="' . esc_attr($role_key) . '">
                ' . ($is_system ? '' : '<button type="button" class="button boxmoe-delete-role-btn" data-slug="' . esc_attr($role_key) . '" style="border-color:#f44336;color:#f44336;">删除</button>') . '
            </div>
            <div class="explain" style="margin-top:8px;clear:both;display:block;">角色标识ID: <code style="margin:0 4px;background:rgba(0,0,0,0.05);padding:2px 5px;border-radius:3px;">' . esc_html($role_key) . '</code> <span style="color:#888;">您可以修改上面的显示名称。</span></div>
        </div>
    </div>';
    
    if ($is_system) {
        $system_roles_html .= $role_item_html;
    } else {
        $custom_roles_html .= $role_item_html;
    }
}

// 拼接HTML，优先显示自定义角色，然后是折叠的系统角色
$roles_table_html .= $custom_roles_html;

if (!empty($system_roles_html)) {
    $roles_table_html .= '
    <div class="boxmoe-system-roles-toggle" style="margin-bottom: 20px; text-align: center; border-top: 1px dashed #ddd; padding-top: 20px;">
        <button type="button" id="boxmoe_toggle_system_roles_btn" class="button" style="width: 100%; display: block;">
            <span class="dashicons dashicons-arrow-down-alt2"></span> 展开系统内置角色管理
        </button>
    </div>
    <div id="boxmoe_system_roles_container" style="display:none;">
        ' . $system_roles_html . '
    </div>
    <script>
    jQuery(document).ready(function($) {
        $("#boxmoe_toggle_system_roles_btn").on("click", function() {
            var $container = $("#boxmoe_system_roles_container");
            var $btn = $(this);
            if ($container.is(":visible")) {
                $container.slideUp();
                $btn.html(\'<span class="dashicons dashicons-arrow-down-alt2"></span> 展开系统内置角色管理\');
            } else {
                $container.slideDown();
                $btn.html(\'<span class="dashicons dashicons-arrow-up-alt2"></span> 折叠系统内置角色管理\');
            }
        });
    });
    </script>
    ';
}

$roles_table_html .= '</div>';

$options[] = array(
    'group' => 'start',
    'group_title' => '现有角色管理',
    'id' => 'boxmoe_existing_roles_area',
    'type' => 'boxmoe_custom_role_form',
    'desc' => '<div class="boxmoe-info-alert">修改名称后请点击底部的“保存设置”按钮生效。删除操作即时生效。</div>',
    'custom_html' => $roles_table_html
);

$options[] = array(
    'group' => 'end',
    'id' => 'boxmoe_existing_roles_end',
    'type' => 'boxmoe_custom_role_form',
    'custom_html' => '',
    'desc' => ''
);

/* 
// 移除旧的循环生成代码
$roles_keys = array_keys($roles);
$first_role = $roles_keys[0];
$last_role = end($roles_keys);

foreach ($roles as $role_key => $role_name) {
    ...
}
*/

$permission_manager_form = '
<div class="boxmoe-role-manager-form">
    <div class="section-role-item" style="margin-bottom: 20px;">
        <h4 class="heading"><span class="dashicons dashicons-search"></span> 搜索用户</h4>
        <div class="option">
            <div class="controls" style="display:flex;gap:10px;">
                <input type="text" id="boxmoe_user_search_input" placeholder="输入用户ID、用户名、邮箱或显示名称进行搜索..." class="of-input" style="flex-grow:1;">
                <button type="button" id="boxmoe_user_search_btn" class="button button-primary">搜索</button>
            </div>
            <div class="explain">支持模糊搜索。留空搜索显示所有用户。</div>
        </div>
    </div>
    
    <div class="section-role-item" style="margin-bottom: 20px;">
        <h4 class="heading"><span class="dashicons dashicons-admin-users"></span> 用户列表与角色管理</h4>
        <div class="option">
            <div class="boxmoe-fonts-table boxmoe-users-table">
                <div class="fonts-table-header">
                    <div class="cell">用户资料</div>
                    <div class="cell">当前角色 (点击修改)</div>
                    <div class="cell">联系方式</div>
                    <div class="cell">用户ID</div>
                </div>
                <div id="boxmoe_users_list_container">
                    <!-- 用户列表将通过 AJAX 加载到这里 -->
                    <div class="fonts-table-row" style="justify-content:center;padding:20px;">
                        正在加载用户数据...
                    </div>
                </div>
            </div>
            <div id="boxmoe_users_pagination" style="margin-top:15px;text-align:center;"></div>
        </div>
    </div>
</div>
';

$options[] = array(
    'group' => 'start',
    'group_title' => '用户权限管理',
    'id' => 'boxmoe_user_permission_area',
    'type' => 'boxmoe_custom_role_form',
    'custom_html' => $permission_manager_form
);

$options[] = array(
    'group' => 'end',
    'id' => 'boxmoe_user_permission_end',
    'type' => 'boxmoe_custom_role_form',
    'custom_html' => '',
    'desc' => ''
);

