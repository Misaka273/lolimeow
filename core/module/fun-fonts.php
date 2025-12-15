<?php
if(!defined('ABSPATH')){exit;}

function boxmoe_fonts_inline_style(){
    if(!get_boxmoe('boxmoe_custom_font_switch')){return;}
    $fonts = get_boxmoe('boxmoe_fonts');
    if(!is_array($fonts) || empty($fonts)){return;}
    $css = '';
    foreach($fonts as $f){
        $name = isset($f['name']) ? trim($f['name']) : '';
        $src = '';
        if(!empty($f['woff2'])){ $src = trim($f['woff2']); }
        elseif(!empty($f['url'])){ $src = trim($f['url']); }
        if($name && $src){
            $css .= "@font-face{font-family:'".esc_attr($name)."';src:url(".esc_url($src).") format('woff2');font-display:swap;}";
        }
    }
    $default = get_boxmoe('boxmoe_default_font');
    if(!empty($default) && $default !== 'default'){
        $fallback = "-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,'Helvetica Neue',Arial,'Noto Sans',sans-serif";
        $css .= "body{font-family:'".esc_attr($default)."',".$fallback.";}";
    }
    
    // 欢迎语独立字体
    $welcome_font = get_boxmoe('boxmoe_welcome_font');
    if(!empty($welcome_font) && $welcome_font !== 'default'){
        $fallback = "-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,'Helvetica Neue',Arial,'Noto Sans',sans-serif";
        $css .= ".boxmoe-typing-animation{font-family:'".esc_attr($welcome_font)."',".$fallback.";}";
    }
    
    if($css){
        wp_add_inline_style('boxmoe-style', $css);
    }
}
add_action('wp_enqueue_scripts','boxmoe_fonts_inline_style',13);

function boxmoe_fonts_theme_json($theme_json){
    if(!get_boxmoe('boxmoe_custom_font_switch')){return $theme_json;}
    $fonts_cfg = get_boxmoe('boxmoe_fonts');
    if(!is_array($fonts_cfg)){return $theme_json;}
    $fonts = array();
    foreach($fonts_cfg as $f){
        $name = isset($f['name']) ? trim($f['name']) : '';
        $src = '';
        if(!empty($f['woff2'])){ $src = trim($f['woff2']); }
        elseif(!empty($f['url'])){ $src = trim($f['url']); }
        if($name && $src){
            $slug = sanitize_title($name);
            $fonts[] = array(
                'name' => $name,
                'slug' => $slug,
                'fontFamily' => '"'.$name.'"',
                'fontFace' => array(
                    array(
                        'fontFamily' => $name,
                        'fontStyle' => 'normal',
                        'fontWeight' => '400',
                        'src' => array($src)
                    )
                )
            );
        }
    }
    if($fonts){
        $data = array(
            'settings' => array(
                'typography' => array(
                    'fontFamilies' => array(
                        'theme' => $fonts
                    )
                )
            )
        );
        $theme_json->update_with($data);
    }
    return $theme_json;
}
add_filter('wp_theme_json_data_theme','boxmoe_fonts_theme_json',10,1);

function boxmoe_fonts_editor_assets(){
    if(!get_boxmoe('boxmoe_custom_font_switch')){return;}
    $fonts = get_boxmoe('boxmoe_fonts');
    if(!is_array($fonts) || empty($fonts)){return;}
    $css = '';
    foreach($fonts as $f){
        $name = isset($f['name']) ? trim($f['name']) : '';
        $src = '';
        if(!empty($f['woff2'])){ $src = trim($f['woff2']); }
        elseif(!empty($f['url'])){ $src = trim($f['url']); }
        if($name && $src){
            $css .= "@font-face{font-family:'".esc_attr($name)."';src:url(".esc_url($src).") format('woff2');font-display:swap;}";
        }
    }
    $default = get_boxmoe('boxmoe_default_font');
    if(!empty($default) && $default !== 'default'){
        $fallback = "-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,'Helvetica Neue',Arial,'Noto Sans',sans-serif";
        $css .= "body{font-family:'".esc_attr($default)."',".$fallback.";}";
    }
    if($css){
        wp_add_inline_style('wp-block-library', $css);
    }
}
add_action('enqueue_block_editor_assets','boxmoe_fonts_editor_assets');

function boxmoe_tinymce_config($init){
    if(!get_boxmoe('boxmoe_custom_font_switch')){ return $init; }
    $fonts = get_boxmoe('boxmoe_fonts');
    if(!is_array($fonts) || empty($fonts)){ return $init; }
    $custom_formats = array();
    $css = '';
    foreach($fonts as $f){
        $name = isset($f['name']) ? trim($f['name']) : '';
        $src = '';
        if(!empty($f['woff2'])){ $src = trim($f['woff2']); }
        elseif(!empty($f['url'])){ $src = trim($f['url']); }
        if($name && $src){
            $custom_formats[] = $name.'='.$name;
            $css .= "@font-face{font-family:'".esc_attr($name)."';src:url(".esc_url($src).") format('woff2');font-display:swap;}";    
        }
    }
    if(!empty($custom_formats)){
        $existing = isset($init['font_formats']) ? trim($init['font_formats']) : '';
        if(!$existing){ $existing = boxmoe_tinymce_default_font_formats(); }
        $init['font_formats'] = implode(';', $custom_formats) . ';' . $existing;
        $init['content_style'] = ( isset($init['content_style']) ? $init['content_style'] : '' ) . $css;
    }
    $default = get_boxmoe('boxmoe_default_font');
    if(!empty($default) && $default !== 'default'){
        $fallback = "-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,'Helvetica Neue',Arial,'Noto Sans',sans-serif";
        $init['content_style'] = ( isset($init['content_style']) ? $init['content_style'] : '' ) . "body{font-family:'".esc_attr($default)."',".$fallback.";}";
    }
    return $init;
}
add_filter('tiny_mce_before_init','boxmoe_tinymce_config');

function boxmoe_tinymce_buttons($buttons){
    if(!in_array('fontselect', $buttons)){
        array_unshift($buttons, 'fontselect');
    }
    return $buttons;
}
add_filter('mce_buttons','boxmoe_tinymce_buttons');

function boxmoe_tinymce_default_font_formats(){
    return 'Andale Mono=andale mono,monospace;'
        .'Arial=arial,helvetica,sans-serif;'
        .'Arial Black=arial black,avant garde;'
        .'Book Antiqua=book antiqua,palatino;'
        .'Comic Sans MS=comic sans ms;'
        .'Courier New=courier new,courier;'
        .'Georgia=georgia;'
        .'Helvetica=helvetica;'
        .'Impact=impact,chicago;'
        .'Symbol=symbol;'
        .'Tahoma=tahoma,arial,helvetica,sans-serif;'
        .'Terminal=terminal,monaco;'
        .'Times New Roman=times new roman,times;'
        .'Trebuchet MS=trebuchet ms,geneva;'
        .'Verdana=verdana,geneva;'
        .'Webdings=webdings;'
        .'Wingdings=wingdings,zapf dingbats';
}

function boxmoe_fonts_table_render($option_name, $value, $val){
    $rows = is_array($val) ? $val : array();
    $html = '<div class="boxmoe-fonts-table" data-option-name="'.esc_attr($option_name).'">';
    $html .= '<div class="fonts-table-header"><span>显示名称</span><span>woff2 上传</span><span>woff2 链接</span><span>操作</span></div>';
    
    $idx = 0;
    foreach($rows as $row){
        $name = isset($row['name']) ? $row['name'] : '';
        $woff2 = isset($row['woff2']) ? $row['woff2'] : '';
        $url = isset($row['url']) ? $row['url'] : '';
        $hidden = '<input type="hidden" name="'.$option_name.'[boxmoe_fonts]['.$idx.'][name]" value="'.esc_attr($name).'" />'
                .'<input type="hidden" name="'.$option_name.'[boxmoe_fonts]['.$idx.'][woff2]" value="'.esc_attr($woff2).'" />'
                .'<input type="hidden" name="'.$option_name.'[boxmoe_fonts]['.$idx.'][url]" value="'.esc_attr($url).'" />';
        $actions = '<div class="fonts-actions">'
            .'<button type="button" class="btn-pill btn-blue boxmoe-font-edit" data-index="'.$idx.'">修改</button>'
            .'<button type="button" class="btn-pill btn-red boxmoe-font-delete-row" data-index="'.$idx.'">删除</button>'
            .'</div>';
        $html .= '<div class="fonts-table-row" data-index="'.$idx.'">'
            .'<div class="cell cell-name"><span class="cell-text">'.esc_html($name ?: '未设置').'</span>'.$hidden.'</div>'
            .'<div class="cell cell-upload"><span class="cell-text">'.esc_html($woff2 ?: '未设置').'</span></div>'
            .'<div class="cell cell-url"><span class="cell-text">'.esc_html($url ?: '未设置').'</span></div>'
            .'<div class="cell cell-actions">'.$actions.'</div>'
            .'</div>';
        $idx++;
    }
    $html .= '</div>';
    // Modal
    $html .= '<div id="boxmoe-fonts-modal-mask" class="of-modal-mask" style="display:none">'
        .'<div id="boxmoe-fonts-modal" class="of-modal">'
        .'<div class="of-modal-header">新增/编辑自定义字体</div>'
        .'<div class="of-modal-body">'
        .'<label>显示名称</label><input type="text" id="bmf-name" class="of-input" placeholder="例如：My Sans">'
        .'<label style="display:block;margin-top:8px">上传 woff2</label>'
        .'<input type="text" id="bmf-woff2" class="upload of-input" placeholder="没有选择文件" />'
        .'<input type="button" id="bmf-upload-btn" class="upload-button button" value="上传" />'
        .'<div class="screenshot" id="bmf-woff2-image"></div>'
        .'<label style="display:block;margin-top:8px">自定义 woff2 链接</label>'
        .'<input type="text" id="bmf-url" class="of-input" placeholder="https://...">'
        .'<div id="bmf-hint" class="form-hint">上传或链接二选一，至少填写其一</div>'
        .'<div id="bmf-error" class="form-error" style="display:none"></div>'
        .'</div>'
        .'<div class="of-modal-actions">'
        .'<button type="button" id="bmf-cancel" class="of-btn of-btn-secondary">取消</button>'
        .'<button type="button" id="bmf-save" class="of-btn of-btn-primary">保存</button>'
        .'</div>'
        .'</div>'
        .'</div>';
    return $html;
}
add_filter('optionsframework_fonts_table','boxmoe_fonts_table_render',10,3);
