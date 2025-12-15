<?php
/**
 * @link https://www.boxmoe.com
 * @package lolimeow
 */

// 🥳 角色管理逻辑
// 🔗 处理角色的新增与删除
// 💕 AJAX 接口

if(!defined('ABSPATH')){
    echo'Look your sister';
    exit;
}

// 输出自定义角色表单 HTML
add_filter('optionsframework_boxmoe_custom_role_form', 'boxmoe_custom_role_form_callback', 10, 3);
function boxmoe_custom_role_form_callback($option_name, $value, $val) {
    return isset($value['custom_html']) ? $value['custom_html'] : '';
}

// 处理新增角色 AJAX
add_action('wp_ajax_boxmoe_add_role', 'boxmoe_add_role_callback');
function boxmoe_add_role_callback() {
    check_ajax_referer('boxmoe_role_manager_nonce', 'nonce');

    if (!current_user_can('manage_options')) {
        wp_send_json_error(array('message' => '权限不足'));
    }

    $role_slug = sanitize_text_field($_POST['role_slug']);
    $role_name = sanitize_text_field($_POST['role_name']);
    $copy_from = sanitize_text_field($_POST['copy_from']);
    $custom_caps_input = isset($_POST['custom_caps']) ? $_POST['custom_caps'] : array();

    // 严格过滤角色标识：只允许小写字母、数字和下划线
    $role_slug = strtolower($role_slug);
    $role_slug = preg_replace('/[^a-z0-9_]/', '_', $role_slug);
    $role_slug = preg_replace('/_+/', '_', $role_slug); // 合并多个下划线
    $role_slug = trim($role_slug, '_');

    if (empty($role_slug) || empty($role_name)) {
        wp_send_json_error(array('message' => '请填写完整的角色标识和名称（标识仅限字母数字下划线）'));
    }

    // 检查角色是否存在
    if (get_role($role_slug)) {
        wp_send_json_error(array('message' => '该角色标识已存在'));
    }

    $caps = array();
    if (!empty($copy_from)) {
        $source_role = get_role($copy_from);
        if ($source_role) {
            $caps = $source_role->capabilities;
        }
    } elseif (!empty($custom_caps_input) && is_array($custom_caps_input)) {
        // 自定义组合模式 (支持角色合并和细分权限)
        foreach ($custom_caps_input as $item) {
            $item = sanitize_text_field($item);
            $source_role = get_role($item);
            if ($source_role) {
                // 如果是角色Slug，合并该角色的权限
                $caps = array_merge($caps, $source_role->capabilities);
            } else {
                // 否则视为具体权限
                $caps[$item] = true;
            }
        }
        // 确保拥有基础阅读权限以便登录
        if (!isset($caps['read'])) {
            $caps['read'] = true;
        }
    } else {
        // 默认给读权限
        $caps = array('read' => true);
    }

    $result = add_role($role_slug, $role_name, $caps);

    if ($result) {
        wp_send_json_success(array('message' => '角色创建成功'));
    } else {
        wp_send_json_error(array('message' => '角色创建失败'));
    }
}

// 处理删除角色 AJAX
add_action('wp_ajax_boxmoe_delete_role', 'boxmoe_delete_role_callback');
function boxmoe_delete_role_callback() {
    check_ajax_referer('boxmoe_role_manager_nonce', 'nonce');

    if (!current_user_can('manage_options')) {
        wp_send_json_error(array('message' => '权限不足'));
    }

    $role_slug = sanitize_text_field($_POST['role_slug']);

    // 禁止删除系统核心角色
    $system_roles = array('administrator', 'editor', 'author', 'contributor', 'subscriber');
    if (in_array($role_slug, $system_roles)) {
        wp_send_json_error(array('message' => '系统内置角色不可删除'));
    }

    // 移除角色
    remove_role($role_slug);

    // 再次检查是否移除成功（get_role 返回 null 表示不存在）
    if (get_role($role_slug) === null) {
        wp_send_json_success(array('message' => '角色删除成功'));
    } else {
        wp_send_json_error(array('message' => '角色删除失败'));
    }
}

// 搜索用户列表 AJAX
add_action('wp_ajax_boxmoe_search_users', 'boxmoe_search_users_callback');
function boxmoe_search_users_callback() {
    check_ajax_referer('boxmoe_role_manager_nonce', 'nonce');
    if (!current_user_can('manage_options')) {
        wp_send_json_error(array('message' => '权限不足'));
    }

    $search_term = isset($_POST['search']) ? sanitize_text_field($_POST['search']) : '';
    $paged = isset($_POST['paged']) ? intval($_POST['paged']) : 1;
    $number = 20; // 每页显示数量

    $args = array(
        'number' => $number,
        'paged' => $paged,
        'search_columns' => array('ID', 'user_login', 'user_email', 'user_nicename', 'display_name'),
        'orderby' => 'ID',
        'order' => 'ASC'
    );

    if (!empty($search_term)) {
        $args['search'] = '*' . $search_term . '*';
    }

    $user_query = new WP_User_Query($args);
    $users = $user_query->get_results();
    $total_users = $user_query->get_total();
    
    global $wp_roles;
    if (!isset($wp_roles)) $wp_roles = new WP_Roles();
    $all_roles = $wp_roles->roles;

    $response_users = array();
    foreach ($users as $user) {
        $user_roles = array();
        foreach ($user->roles as $role_key) {
            $role_name_raw = isset($all_roles[$role_key]['name']) ? $all_roles[$role_key]['name'] : $role_key;
            $role_name = function_exists('translate_user_role') ? translate_user_role($role_name_raw) : $role_name_raw;
            $user_roles[] = array(
                'slug' => $role_key,
                'name' => $role_name
            );
        }

        $response_users[] = array(
            'ID' => $user->ID,
            'display_name' => $user->display_name,
            'user_login' => $user->user_login,
            'user_email' => $user->user_email,
            'avatar' => function_exists('boxmoe_get_avatar_url') ? boxmoe_get_avatar_url($user->ID, 64) : get_avatar_url($user->ID, array('size' => 64)), // ⬅️ 使用主题自定义头像获取函数，支持QQ头像等
            'roles' => $user_roles
        );
    }

    // 获取所有可用角色供前端下拉选择
    $available_roles = array();
    foreach ($all_roles as $key => $role) {
        $role_name_display = function_exists('translate_user_role') ? translate_user_role($role['name']) : $role['name'];
        $available_roles[] = array(
            'slug' => $key,
            'name' => $role_name_display . ' (' . ucfirst($key) . ')'
        );
    }

    wp_send_json_success(array(
        'users' => $response_users,
        'total' => $total_users,
        'max_pages' => ceil($total_users / $number),
        'available_roles' => $available_roles
    ));
}

// 修改用户角色 AJAX
add_action('wp_ajax_boxmoe_change_user_role', 'boxmoe_change_user_role_callback');
function boxmoe_change_user_role_callback() {
    check_ajax_referer('boxmoe_role_manager_nonce', 'nonce');
    if (!current_user_can('manage_options')) {
        wp_send_json_error(array('message' => '权限不足'));
    }

    $user_id = intval($_POST['user_id']);
    $new_role = sanitize_text_field($_POST['new_role']);
    
    $user = get_user_by('id', $user_id);
    
    if (!$user) {
        wp_send_json_error(array('message' => '用户不存在'));
    }

    // 安全检查：防止自己移除自己的管理员权限
    if ($user->ID === get_current_user_id() && !in_array('administrator', $user->roles) && $new_role !== 'administrator') {
        // 如果当前是管理员，且新角色不是管理员，则检查是否还有其他管理员，或者简单地禁止自己修改自己的角色
        // 简单策略：禁止修改自己的角色，防止误操作
        wp_send_json_error(array('message' => '为了安全起见，您不能修改自己的角色'));
    }

    // 设置新角色（替换原有角色）
    $user->set_role($new_role);
    
    wp_send_json_success(array('message' => '用户角色已更新'));
}


// 在后台加载必要的 JS 脚本 (直接注入到 admin_footer 以简化流程)
add_action('admin_footer', 'boxmoe_role_manager_script');
function boxmoe_role_manager_script() {
    // 仅在主题设置页面加载 (同时兼容 options-framework-theme 和 boxmoe_options)
    if (isset($_GET['page']) && ($_GET['page'] == 'options-framework-theme' || $_GET['page'] == 'boxmoe_options')) {
        ?>
        <script type="text/javascript">
        jQuery(document).ready(function($) {
            console.log('Boxmoe Role Manager Script Loaded');

            // 🥳 自定义弹窗逻辑
            // 扁平圆角风，倒计时自动关闭
            function showBoxmoeToast(message, type, reloadAfter) {
                // type: success, error
                // reloadAfter: boolean (whether to reload page after close)
                
                // 移除现有的 toast
                $('.boxmoe-toast-wrapper').remove();

                var icon = type === 'success' ? '<span class="dashicons dashicons-yes-alt" style="color:#4caf50;font-size:24px;width:24px;height:24px;"></span>' : '<span class="dashicons dashicons-dismiss" style="color:#f44336;font-size:24px;width:24px;height:24px;"></span>';
                var color = type === 'success' ? '#4caf50' : '#f44336';
                
                var html = `
                <div class="boxmoe-toast-wrapper" style="position:fixed;top:50px;left:50%;transform:translateX(-50%);z-index:99999;opacity:0;transition:opacity 0.3s ease;">
                    <div class="boxmoe-toast" style="background:#fff;box-shadow:0 4px 12px rgba(0,0,0,0.15);border-radius:50px;padding:12px 24px;display:flex;align-items:center;gap:12px;min-width:300px;border:1px solid #eee;">
                        ${icon}
                        <div class="boxmoe-toast-content" style="flex-grow:1;">
                            <div style="font-size:14px;font-weight:600;color:#333;">${message}</div>
                            ${reloadAfter ? '<div class="boxmoe-toast-timer" style="font-size:12px;color:#999;margin-top:2px;">3秒后自动刷新...</div>' : ''}
                        </div>
                        <button type="button" class="boxmoe-toast-close" style="background:none;border:none;cursor:pointer;color:#ccc;padding:0;"><span class="dashicons dashicons-no-alt"></span></button>
                    </div>
                </div>
                `;

                $('body').append(html);
                
                // 显示动画
                setTimeout(function() {
                    $('.boxmoe-toast-wrapper').css('opacity', '1');
                    $('.boxmoe-toast-wrapper').css('top', '80px'); // Slide down effect
                }, 10);

                // 关闭函数
                function closeToast() {
                    $('.boxmoe-toast-wrapper').css('opacity', '0');
                    $('.boxmoe-toast-wrapper').css('top', '50px');
                    setTimeout(function() {
                        $('.boxmoe-toast-wrapper').remove();
                        if (reloadAfter) {
                            location.reload();
                        }
                    }, 300);
                }

                // 点击关闭
                $('.boxmoe-toast-close').on('click', function() {
                    // 如果需要刷新，手动关闭是否也要刷新？
                    // 通常手动关闭意味着用户已阅，如果操作成功了，还是得刷新才能看到效果。
                    // 但如果是为了看清楚报错信息，手动关闭就不刷新（error case usually reloadAfter=false）
                    closeToast(); 
                });

                // 自动关闭倒计时
                var duration = 3000;
                setTimeout(function() {
                    closeToast();
                }, duration);
            }

            // 用户列表与搜索管理
            var usersListContainer = $('#boxmoe_users_list_container');
            if(usersListContainer.length > 0) {
                usersListContainer.html('<div class="fonts-table-row" style="justify-content:center;padding:20px;">正在初始化组件...</div>');
            }

            // 角色标识输入限制
            $('#boxmoe_new_role_slug').on('input', function() {
                var val = $(this).val();
                val = val.toLowerCase().replace(/[^a-z0-9_]/g, '_');
                $(this).val(val);
            });

            // 角色分配模式切换
            $('input[name="boxmoe_role_mode"]').on('change', function() {
                var mode = $(this).val();
                if (mode === 'custom') {
                    $('#boxmoe_role_inherit_wrap').slideUp();
                    $('#boxmoe_role_custom_wrap').slideDown();
                } else {
                    $('#boxmoe_role_inherit_wrap').slideDown();
                    $('#boxmoe_role_custom_wrap').slideUp();
                }
            });

            // 🥳 Modal Logic
            var $capsModal = $('#boxmoe_caps_modal');
            
            // Open Modal
            $('#boxmoe_open_caps_modal').on('click', function() {
                $capsModal.fadeIn(200);
            });

            // Close Modal Function
            function closeCapsModal() {
                $capsModal.fadeOut(200);
            }

            // Close triggers
            $('.boxmoe-modal-close, .boxmoe-modal-close-btn, .boxmoe-modal-backdrop').on('click', function() {
                closeCapsModal();
            });

            // Confirm Selection
            $('.boxmoe-modal-confirm').on('click', function() {
                var count = $('.boxmoe_custom_cap_single:checked').length;
                $('#boxmoe_selected_caps_count').text('已选择 ' + count + ' 项权限');
                closeCapsModal();
            });

            // 新增角色
            $('#boxmoe_add_role_btn').on('click', function(e) {
                e.preventDefault();
                var btn = $(this);
                var slug = $('#boxmoe_new_role_slug').val();
                var name = $('#boxmoe_new_role_name').val();
                
                var mode = $('input[name="boxmoe_role_mode"]:checked').val();
                var copy = '';
                var custom_caps = [];

                if (mode === 'inherit' || !mode) { // 兼容旧版或者默认
                    copy = $('#boxmoe_new_role_copy').val();
                } else {
                    $('.boxmoe_custom_cap_single:checked').each(function() {
                        custom_caps.push($(this).val());
                    });
                    if (custom_caps.length === 0) {
                        alert('请至少选择一项权限功能');
                        return;
                    }
                }
                
                if (!slug || !name) {
                    alert('请填写角色标识和名称');
                    return;
                }

                btn.prop('disabled', true).text('处理中...');

                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'boxmoe_add_role',
                        nonce: '<?php echo wp_create_nonce("boxmoe_role_manager_nonce"); ?>',
                        role_slug: slug,
                        role_name: name,
                        copy_from: copy,
                        custom_caps: custom_caps
                    },
                    success: function(response) {
                        if (response.success) {
                            showBoxmoeToast(response.data.message, 'success', true);
                        } else {
                            showBoxmoeToast(response.data.message, 'error', false);
                            btn.prop('disabled', false).text('创建新角色');
                        }
                    },
                    error: function() {
                        showBoxmoeToast('请求失败，请稍后重试', 'error', false);
                        btn.prop('disabled', false).text('创建新角色');
                    }
                });
            });

            // 删除角色
            $('.boxmoe-delete-role-btn').on('click', function(e) {
                e.preventDefault();
                if (!confirm('确定要删除这个角色吗？此操作不可恢复。该角色下的用户将失去此角色身份。')) {
                    return;
                }

                var btn = $(this);
                var slug = btn.data('slug');

                btn.prop('disabled', true).text('...');

                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'boxmoe_delete_role',
                        nonce: '<?php echo wp_create_nonce("boxmoe_role_manager_nonce"); ?>',
                        role_slug: slug
                    },
                    success: function(response) {
                        if (response.success) {
                            showBoxmoeToast(response.data.message, 'success', true);
                        } else {
                            showBoxmoeToast(response.data.message, 'error', false);
                            btn.prop('disabled', false).text('删除');
                        }
                    },
                    error: function() {
                        showBoxmoeToast('请求失败', 'error', false);
                        btn.prop('disabled', false).text('删除');
                    }
                });
            });

            // 用户列表与搜索管理
            // var usersListContainer = $('#boxmoe_users_list_container'); // 上面已经定义
            var paginationContainer = $('#boxmoe_users_pagination');
            var availableRoles = []; // 存储所有可用角色，用于构建下拉菜单
            
            function renderUserTable(users) {
                if (users.length === 0) {
                    usersListContainer.html('<div class="fonts-table-row" style="justify-content:center;padding:20px;">没有找到匹配的用户</div>');
                    return;
                }

                var html = '';
                $.each(users, function(i, user) {
                    var rolesHtml = '';
                    var currentRoleSlug = '';
                    if (user.roles && user.roles.length > 0) {
                        // 默认显示第一个角色，点击可修改
                        currentRoleSlug = user.roles[0].slug;
                        rolesHtml = '<div class="boxmoe-role-editor" data-userid="' + user.ID + '" data-currentrole="' + currentRoleSlug + '">';
                        rolesHtml += '<span class="current-role-label" title="点击修改角色">' + user.roles[0].name + ' <span class="dashicons dashicons-edit"></span></span>';
                        rolesHtml += '</div>';
                    } else {
                        rolesHtml = '<div class="boxmoe-role-editor" data-userid="' + user.ID + '" data-currentrole="">';
                        rolesHtml += '<span class="current-role-label" style="color:#999;" title="点击设置角色">无角色 <span class="dashicons dashicons-edit"></span></span>';
                        rolesHtml += '</div>';
                    }

                    html += '<div class="fonts-table-row">';
                    
                    // 用户资料
                    html += '<div class="cell" style="display:flex;align-items:center;">';
                    html += '<img src="' + user.avatar + '" style="width:40px;height:40px;border-radius:50%;margin-right:10px;">';
                    html += '<div>';
                    html += '<div style="font-weight:bold;">' + user.display_name + '</div>';
                    html += '<div style="font-size:12px;color:#999;">@' + user.user_login + '</div>';
                    html += '</div>';
                    html += '</div>';

                    // 当前角色 (点击修改)
                    html += '<div class="cell">';
                    html += rolesHtml;
                    html += '</div>';

                    // 联系方式
                    html += '<div class="cell cell-text">';
                    html += user.user_email;
                    html += '</div>';

                    // 用户ID
                    html += '<div class="cell">';
                    html += '#' + user.ID;
                    html += '</div>';

                    html += '</div>';
                });
                usersListContainer.html(html);
            }

            function renderPagination(currentPage, maxPages) {
                if (maxPages <= 1) {
                    paginationContainer.empty();
                    return;
                }
                
                var html = '';
                if (currentPage > 1) {
                    html += '<button type="button" class="button boxmoe-pagination-btn" data-page="' + (currentPage - 1) + '">上一页</button> ';
                }
                html += '<span style="margin:0 10px;">第 ' + currentPage + ' 页 / 共 ' + maxPages + ' 页</span> ';
                if (currentPage < maxPages) {
                    html += '<button type="button" class="button boxmoe-pagination-btn" data-page="' + (currentPage + 1) + '">下一页</button>';
                }
                paginationContainer.html(html);
            }

            function loadUsers(searchTerm, page) {
                usersListContainer.html('<div class="fonts-table-row" style="justify-content:center;padding:20px;">正在加载...</div>');
                
                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'boxmoe_search_users',
                        nonce: '<?php echo wp_create_nonce("boxmoe_role_manager_nonce"); ?>',
                        search: searchTerm,
                        paged: page
                    },
                    success: function(response) {
                        // 增加错误处理鲁棒性
                        if (response && response.success) {
                            availableRoles = response.data.available_roles;
                            renderUserTable(response.data.users);
                            renderPagination(page, response.data.max_pages);
                            // 存储当前搜索状态
                            $('#boxmoe_user_search_btn').data('current-page', page);
                        } else {
                            var errorMsg = '未知错误';
                            if (response && response.data && response.data.message) {
                                errorMsg = response.data.message;
                            } else if (typeof response === 'string') {
                                errorMsg = '服务器返回错误：' + response.substring(0, 100) + '...';
                            }
                            usersListContainer.html('<div class="fonts-table-row" style="color:red;justify-content:center;padding:20px;">' + errorMsg + '</div>');
                        }
                    },
                    error: function(xhr, status, error) {
                        var msg = '请求失败';
                        if(error) msg += ': ' + error;
                        usersListContainer.html('<div class="fonts-table-row" style="color:red;justify-content:center;padding:20px;">' + msg + '</div>');
                    }
                });
            }

            // 初始加载
            loadUsers('', 1);

            // 搜索按钮
            $('#boxmoe_user_search_btn').on('click', function(e) {
                e.preventDefault();
                var searchTerm = $('#boxmoe_user_search_input').val();
                loadUsers(searchTerm, 1);
            });

            // 回车搜索
            $('#boxmoe_user_search_input').on('keypress', function(e) {
                if(e.which == 13) {
                    e.preventDefault();
                    $('#boxmoe_user_search_btn').click();
                }
            });

            // 分页点击
            $(document).on('click', '.boxmoe-pagination-btn', function(e) {
                e.preventDefault();
                var page = $(this).data('page');
                var searchTerm = $('#boxmoe_user_search_input').val();
                loadUsers(searchTerm, page);
            });

            // 角色修改逻辑 (点击文本显示下拉框)
            $(document).on('click', '.boxmoe-role-editor .current-role-label', function(e) {
                e.stopPropagation(); // 防止冒泡
                var container = $(this).parent();
                
                // 如果已经有下拉框了，就不再创建
                if (container.find('select').length > 0) return;

                var currentRole = container.data('currentrole');
                var userId = container.data('userid');
                
                var selectHtml = '<select class="boxmoe-role-select" data-userid="' + userId + '" style="max-width:150px;">';
                $.each(availableRoles, function(i, role) {
                    var selected = (role.slug == currentRole) ? 'selected' : '';
                    selectHtml += '<option value="' + role.slug + '" ' + selected + '>' + role.name + '</option>';
                });
                selectHtml += '</select>';

                container.html(selectHtml);
                container.find('select').focus();
            });

            // 监听下拉框变化 (即时保存)
            $(document).on('change', '.boxmoe-role-select', function(e) {
                var select = $(this);
                var container = select.parent();
                var userId = select.data('userid');
                var newRole = select.val();
                
                // 禁用下拉框防止重复提交
                select.prop('disabled', true);

                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'boxmoe_change_user_role',
                        nonce: '<?php echo wp_create_nonce("boxmoe_role_manager_nonce"); ?>',
                        user_id: userId,
                        new_role: newRole
                    },
                    success: function(response) {
                        if (response.success) {
                            // 更新当前角色数据
                            container.data('currentrole', newRole);
                            // 刷新该行显示
                            var roleName = select.find('option:selected').text();
                            roleName = roleName.split('(')[0].trim();
                            container.html('<span class="current-role-label" title="点击修改角色">' + roleName + ' <span class="dashicons dashicons-edit"></span></span>');
                        } else {
                            showBoxmoeToast(response.data.message, 'error', false);
                            // 恢复下拉框
                            select.prop('disabled', false);
                        }
                    },
                    error: function() {
                        showBoxmoeToast('请求失败', 'error', false);
                        select.prop('disabled', false);
                    }
                });
            });

            // 失去焦点时恢复 (如果没改变)
            $(document).on('blur', '.boxmoe-role-select', function(e) {
                 var select = $(this);
                 // 稍微延迟一下，防止是因为点击了option触发的blur
                 setTimeout(function(){
                     if(select.closest('body').length > 0 && !select.prop('disabled')) {
                         var container = select.parent();
                         var currentRole = container.data('currentrole');
                         
                         var roleName = '未知角色';
                         for(var i=0; i<availableRoles.length; i++) {
                             if(availableRoles[i].slug == currentRole) {
                                 roleName = availableRoles[i].name;
                                 break;
                             }
                         }
                         roleName = roleName.split('(')[0].trim(); 
                         if(!roleName) roleName = currentRole;

                         container.html('<span class="current-role-label" title="点击修改角色">' + roleName + ' <span class="dashicons dashicons-edit"></span></span>');
                     }
                 }, 200);
            });

        });
        </script>
        <style>
            /* 修正嵌套 heading 的样式，使其与主题原生样式一致 */
            .boxmoe-role-manager-form .heading {
                display: inline-block;
                font-size: 13px;
                padding: 5px;
                color: #333;
                background-color: #fff;
                border-radius: 5px;
                border: 1px #efefef solid;
                line-height: 17px;
                margin-bottom: 0;
            }
            .boxmoe-role-manager-form .heading .dashicons {
                font-size: 14px;
                line-height: 17px;
                vertical-align: middle;
                margin-right: 3px;
                color: #555;
            }
            .boxmoe-role-manager-form .option {
                padding: 10px 0;
            }
            .boxmoe-role-manager-form .controls {
                margin-bottom: 5px;
            }
            .boxmoe-role-manager-form .explain {
                font-size: 12px;
                color: #999;
                margin-top: 5px;
            }
            /* 输入框样式修正 */
            .boxmoe-role-manager-form input.of-input,
            .boxmoe-role-manager-form select.of-input {
                background-color: #efefef;
                border: 1px solid transparent;
                border-radius: 5px;
                padding: 8px 10px;
                box-shadow: none;
                height: auto;
            }
            .boxmoe-role-manager-form input.of-input:focus,
            .boxmoe-role-manager-form select.of-input:focus {
                background-color: #fff;
                border-color: #ccc;
                outline: none;
                box-shadow: 0 0 5px rgba(0,0,0,0.1);
            }
            .boxmoe-delete-role-btn {
                color: #ef4444;
                border: 1px solid #fca5a5;
                background: #fef2f2;
                border-radius: 4px;
                padding: 2px 8px;
                cursor: pointer;
                font-size: 12px;
                margin-left: 10px;
            }
            .boxmoe-delete-role-btn:hover {
                background: #fee2e2;
                border-color: #ef4444;
            }
        </style>
        <?php
    }
}
