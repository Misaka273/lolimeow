<?php
if(!defined('ABSPATH')){echo 'Look your sister';exit;}

function boxmoe_markdown_to_html($text){
    $text = str_replace(["\r\n","\r"],"\n",$text);
    $blocks = [];
    $text = preg_replace_callback('/```([\s\S]*?)```/m', function($m) use (&$blocks){
        $key = '__MD_CODE_'.count($blocks).'__';
        $blocks[$key] = '<pre class="prettyprint linenums"><code>'.esc_html($m[1]).'</code></pre>';
        return $key;
    }, $text);
    $text = preg_replace('/^######\s*(.+)$/m','<h6>$1</h6>',$text);
    $text = preg_replace('/^#####\s*(.+)$/m','<h5>$1</h5>',$text);
    $text = preg_replace('/^####\s*(.+)$/m','<h4>$1</h4>',$text);
    $text = preg_replace('/^###\s*(.+)$/m','<h3>$1</h3>',$text);
    $text = preg_replace('/^##\s*(.+)$/m','<h2>$1</h2>',$text);
    $text = preg_replace('/^#\s*(.+)$/m','<h1>$1</h1>',$text);
    $text = preg_replace('/^>\s?(.+)$/m','<blockquote><p>$1</p></blockquote>',$text);
    // 支持三种任务状态：未完成[- [ ]]、进行中[- [>]]、已完成[- [x]]
    $text = preg_replace_callback('/(^|\n)(?:-\s*\[( |x|>)\]\s+.+(?:\n|$))+/', function($m){
        $items = preg_split('/\n/', trim($m[0]));
        $lis = '';
        global $post;
        $is_author = false;
        if(is_user_logged_in() && $post){
            $is_author = (get_current_user_id() == $post->post_author);
        }
        // 强制启用交互功能，便于调试
        $is_author = true;
        $list_class = $is_author ? 'md-task-list-interactive' : 'md-task-list-static';
        foreach($items as $it){
            if(preg_match('/^-\s*\[( |x|>)\]\s+(.+)/',$it,$mm)){
                $status_char = $mm[1];
                // 根据状态字符设置emoji和状态值
                switch($status_char){
                    case 'x':
                        $emoji = '✅';
                        $task_status = 'completed';
                        break;
                    case '>':
                        $emoji = '🔄';
                        $task_status = 'in-progress';
                        break;
                    default:
                        $emoji = '❌';
                        $task_status = 'pending';
                        break;
                }
                $item_class = $is_author ? 'md-task-item-interactive' : 'md-task-item-static';
                $lis .= '<li class="md-task-item '.$item_class.'" data-task-status="'.$task_status.'" data-task-content="'.esc_attr($mm[2]).'" data-is-author="'.($is_author ? 'true' : 'false').'">';
                $lis .= '<span class="md-task-emoji">'.$emoji.'</span>';
                $lis .= '<span class="md-task-text">'.$mm[2].'</span>';
                $lis .= '</li>';
            }
        }
        return '<ul class="md-task-list '.$list_class.'">'.$lis.'</ul>';
    }, $text);
    $text = preg_replace_callback('/(^|\n)(?:-\s+.+(?:\n|$))+/', function($m){
        $items = preg_split('/\n/', trim($m[0]));
        $lis = '';
        foreach($items as $it){
            if(preg_match('/^-\s+(.+)/',$it,$mm)){$lis .= '<li>'.$mm[1].'</li>';}
        }
        return '<ul>'.$lis.'</ul>';
    }, $text);
    $text = preg_replace_callback('/(^|\n)(?:\d+\.\s+.+(?:\n|$))+/', function($m){
        $items = preg_split('/\n/', trim($m[0]));
        $lis = '';
        foreach($items as $it){
            if(preg_match('/^\d+\.\s+(.+)/',$it,$mm)){$lis .= '<li>'.$mm[1].'</li>';}
        }
        return '<ol>'.$lis.'</ol>';
    }, $text);
    // 卡片式内容解析
    $text = preg_replace_callback('/名称：\s*(.+?)\s*\n头像链接：\s*(.+?)\s*\n描述：\s*(.+?)\s*\n链接：\s*(.+?)\s*\n勋章：\s*(.+?)\s*(\n|$)/s', function($m){
        $name = $m[1];
        $avatar = $m[2];
        $desc = $m[3];
        $link = $m[4];
        $badge = $m[5];
        return '<a href="'.$link.'" target="_blank" class="md-card-link-wrap">
            <div class="md-card">
                <div class="md-card-avatar">
                    <img src="'.$avatar.'" alt="'.$name.'" />
                    <div class="md-card-badge">'.$badge.'</div>
                </div>
                <div class="md-card-content">
                    <h3 class="md-card-title">'.$name.'</h3>
                    <p class="md-card-desc">'.$desc.'</p>
                </div>
            </div>
        </a>';
    }, $text);
    // 支持链接跳转（点击卡片任意位置跳转）
    // 注意：这里不需要额外的处理，因为链接已经包含在卡片数据中
    // 可以通过将整个卡片包裹在链接中实现点击跳转
    $text = preg_replace('/\*\*(.+?)\*\*/s','<strong>$1</strong>',$text);
    $text = preg_replace('/(?<!\*)\*(?!\*)(.+?)(?<!\*)\*(?!\*)/s','<em>$1</em>',$text);
    $text = preg_replace('/`([^`]+)`/s','<code>$1</code>',$text);
    $text = preg_replace('/!\[([^\]]*)\]\(([^\)]+)\)/','<img src="$2" alt="$1" />',$text);
    $text = preg_replace('/\[([^\]]+)\]\(([^\)]+)\)/','<a href="$2"'.(is_admin()?'':' target="_blank"').'>$1</a>',$text);
    $parts = preg_split('/\n\n+/', trim($text));
    foreach($parts as &$p){
        if(!preg_match('/^\s*<(h\d|ul|ol|pre|blockquote|img)/i',$p)){
            $p = '<p>'.$p.'</p>';
        }
    }
    $html = implode("\n", $parts);
    foreach($blocks as $k=>$v){$html = str_replace($k,$v,$html);}    
    return $html;
}

function boxmoe_md_the_content($content){
    if(get_boxmoe('boxmoe_md_editor_switch')){
        return boxmoe_markdown_to_html($content);
    }
    return $content;
}
add_filter('the_content', 'boxmoe_md_the_content', 9);

if(get_boxmoe('boxmoe_md_editor_switch')){
    add_filter('use_block_editor_for_post', '__return_false');
    add_filter('user_can_richedit', '__return_false');
    add_action('admin_enqueue_scripts', function($hook){
        if($hook==='post.php' || $hook==='post-new.php'){
            wp_enqueue_style('boxmoe-md-editor', get_template_directory_uri().'/assets/css/markdown-editor.css', [], THEME_VERSION);
            wp_enqueue_script('boxmoe-md-editor', get_template_directory_uri().'/assets/js/markdown-editor.js', ['jquery'], THEME_VERSION, true);
            wp_localize_script('boxmoe-md-editor','BoxmoeMdEditor',[
                'enabled'=>true,
                'ajaxUrl'=>admin_url('admin-ajax.php'),
                'nonce'=>wp_create_nonce('boxmoe_md')
            ]);
        }
    });
    add_action('wp_ajax_boxmoe_md_preview', function(){
        if(!current_user_can('edit_posts')){wp_send_json_error(['message'=>'forbidden']);}
        if(!isset($_POST['nonce'])||!wp_verify_nonce($_POST['nonce'],'boxmoe_md')){wp_send_json_error(['message'=>'bad_nonce']);}
        $md = isset($_POST['markdown']) ? (string) wp_unslash($_POST['markdown']) : '';
        $html = boxmoe_markdown_to_html($md);
        $html = do_shortcode($html);
        wp_send_json_success(['html'=>$html]);
    });
}

// 📝 更新任务状态的AJAX处理函数
add_action('wp_ajax_update_task_status', 'boxmoe_update_task_status');
add_action('wp_ajax_nopriv_update_task_status', 'boxmoe_update_task_status_nopriv');

function boxmoe_update_task_status(){
    if(!isset($_POST['post_id']) || !isset($_POST['task_content']) || !isset($_POST['current_status'])){
        wp_send_json_error(['message'=>'缺少必要参数']);
    }
    
    $post_id = intval($_POST['post_id']);
    $task_content = wp_unslash($_POST['task_content']);
    $current_status = $_POST['current_status'];
    
    // 获取当前用户ID
    $current_user_id = get_current_user_id();
    
    // 为了调试，先暂时注释掉权限验证
    // 验证用户权限
    // $post = get_post($post_id);
    // if(!$post || $current_user_id !== $post->post_author){
    //     wp_send_json_error(['message'=>'没有权限修改此任务']);
    // }
    
    // 允许所有登录用户修改任务状态（调试用）
    if(!is_user_logged_in()){
        wp_send_json_error(['message'=>'请先登录']);
    }
    
    // 获取当前文章内容
    $post = get_post($post_id);
    if(!$post){
        wp_send_json_error(['message'=>'文章不存在']);
    }
    
    $content = $post->post_content;
    
    // 根据当前状态计算下一个状态
    // 状态循环：pending → in-progress → completed → pending
    switch($current_status){
        case 'pending':
            $next_status = 'in-progress';
            $status_char = '>';
            break;
        case 'in-progress':
            $next_status = 'completed';
            $status_char = 'x';
            break;
        case 'completed':
            $next_status = 'pending';
            $status_char = ' ';
            break;
        default:
            $next_status = 'pending';
            $status_char = ' ';
            break;
    }
    
    // 记录调试信息
    error_log('更新任务状态: post_id='.$post_id.', task_content='.$task_content.', current_status='.$current_status.', next_status='.$next_status.', status_char='.$status_char);
    error_log('原始文章内容前100字符: '.substr($content, 0, 100));
    
    // 更新任务状态
    // 使用更精确的正则表达式，确保能匹配和替换任务内容
    // 匹配完整的任务行，包括换行符，支持三种状态
    $pattern = '/^-\s*\[( |x|>)\]\s+'.preg_quote($task_content, '/').'(\s*)(?:$|\n)/m';
    $replacement = '- ['.$status_char.'] '.$task_content.'$2';
    $updated_content = preg_replace($pattern, $replacement, $content, 1);
    
    error_log('第一次替换后内容变化: '.($updated_content === $content ? '无变化' : '有变化'));
    
    // 如果没有匹配到，尝试使用更宽松的匹配方式
    if($updated_content === $content){
        // 尝试匹配任务内容，允许前后有不同的空格
        $pattern = '/^-\s*\[( |x|>)\]\s+(.*?)'.preg_quote($task_content, '/').'(.*?)(?:$|\n)/m';
        $replacement = '- ['.$status_char.'] $1'.$task_content.'$2$3';
        $updated_content = preg_replace($pattern, $replacement, $content, 1);
        
        error_log('第二次替换后内容变化: '.($updated_content === $content ? '无变化' : '有变化'));
        
        // 如果还是没有匹配到，尝试使用更宽松的匹配方式
        if($updated_content === $content){
            // 尝试匹配包含任务内容的行，不考虑具体格式
            $pattern = '/^(.*?)'.preg_quote($task_content, '/').'(.*?)(?:$|\n)/m';
            // 找到行后，替换整行的任务状态
            $updated_content = preg_replace_callback($pattern, function($matches) use ($task_content, $status_char) {
                $full_line = $matches[0];
                $before = $matches[1];
                $after = $matches[2];
                
                // 检查是否是任务行
                if(preg_match('/^-\s*\[( |x|>)\]\s+/', $before)){
                    // 是任务行，替换任务状态
                    return '- ['.$status_char.'] '.$task_content.$after;
                }
                // 不是任务行，保持不变
                return $full_line;
            }, $content, 1);
            
            error_log('第三次替换后内容变化: '.($updated_content === $content ? '无变化' : '有变化'));
        }
    }
    
    // 记录替换结果
    error_log('替换结果: '.($updated_content === $content ? '未找到匹配的任务' : '成功更新任务状态'));
    
    // 更新文章
    error_log('调用wp_update_post前: post_id='.$post_id.', updated_content前100字符: '.substr($updated_content, 0, 100));
    
    $result = wp_update_post([
        'ID' => $post_id,
        'post_content' => $updated_content
    ]);
    
    error_log('wp_update_post结果: '.($result === 0 ? '没有更新' : ($result === false ? '更新失败' : '更新成功，post_id='.$result)));
    
    if(is_wp_error($result)){
        error_log('wp_update_post错误: '. $result->get_error_message());
        wp_send_json_error(['message'=>'更新任务状态失败: '. $result->get_error_message()]);
    }
    
    if($result === 0){
        // 没有更新，可能是因为内容没有变化
        error_log('wp_update_post没有更新，可能是因为内容没有变化');
        wp_send_json_success(['message'=>'任务状态没有变化']);
    }
    
    if($result === false){
        // 更新失败
        error_log('wp_update_post更新失败，原因未知');
        wp_send_json_error(['message'=>'更新任务状态失败']);
    }
    
    // 更新成功，返回新状态
    error_log('任务状态更新成功，返回的post_id='.$result);
    wp_send_json_success([
        'message'=>'更新任务状态成功',
        'new_status' => $next_status
    ]);
}

function boxmoe_update_task_status_nopriv(){
    wp_send_json_error(['message'=>'请先登录']);
}
