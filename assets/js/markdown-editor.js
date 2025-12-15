;(function($){
  $(function(){
    if(!window.BoxmoeMdEditor||!BoxmoeMdEditor.enabled) return;
    var $ta = $('#content');
    if(!$ta.length) return;
    $('#content-tmce').hide();
    $('#content-html').hide();
    var $wrap = $('#wp-content-editor-container');
    var $bar = $('<div class="boxmoe-md-toolbar"></div>');
    var btn = function(text, cls){return $('<button type="button" class="md-btn '+(cls||'')+'">'+text+'</button>');};
    var $preview = $('<div class="boxmoe-md-preview"><div class="boxmoe-md-preview-inner"></div></div>');
    var $inner = $preview.find('.boxmoe-md-preview-inner');
    var insert = function(prefix, suffix, placeholder){
      var el = $ta.get(0);
      var start = el.selectionStart||0;
      var end = el.selectionEnd||start;
      var val = $ta.val();
      var sel = val.substring(start,end)|| (placeholder||'');
      var out = val.substring(0,start)+prefix+sel+suffix+val.substring(end);
      $ta.val(out);
      el.focus();
      el.selectionStart = start+prefix.length;
      el.selectionEnd = start+prefix.length+sel.length;
      
    };
    $bar.append(btn('加粗','md-bold').on('click',function(){insert('**','**','bold');}));
    $bar.append(btn('斜体','md-italic').on('click',function(){insert('*','*','italic');}));
    $bar.append(btn('H1').on('click',function(){insert('# ','','标题');}));
    $bar.append(btn('H2').on('click',function(){insert('## ','','副标题');}));
    $bar.append(btn('H3').on('click',function(){insert('### ','','小标题');}));
    $bar.append(btn('链接').on('click',function(){insert('[','](https://)','链接文字');}));
    $bar.append(btn('图片').on('click',function(){insert('![' ,'](https://)','alt');}));
    $bar.append(btn('代码').on('click',function(){insert('```\n','\n```','代码');}));
    $bar.append(btn('引用').on('click',function(){insert('> ','','引用');}));
    $bar.append(btn('无序列表').on('click',function(){insert('- ','','列表项');}));
    $bar.append(btn('任务清单').on('click',function(){insert('- [ ] ','','任务项');}));
    $bar.append(btn('有序列表').on('click',function(){insert('1. ','','列表项');}));
    $bar.append(btn('卡片').on('click',function(){insert('名称：\n头像链接：\n描述：\n链接：\n勋章：\n\n','','');}));
    
    // 视图切换按钮组
    var $btnEdit = btn('编辑', 'md-view-btn active').data('mode', 'edit');
    var $btnSplit = btn('分屏', 'md-view-btn').data('mode', 'split');
    var $btnPreview = btn('预览', 'md-view-btn').data('mode', 'preview');
    
    // 分隔符
    $bar.append('<span class="md-separator">|</span>');
    $bar.append($btnEdit);
    $bar.append($btnSplit);
    $bar.append($btnPreview);

    // 🛠️ 挂载Markdown工具栏
    function mountMdToolbar(){
      var $emoji = $('.quicktags-toolbar-emoji');
      var $qtToolbar = $('#qt_content_toolbar'); // ⬅️ 获取默认的Quicktags工具栏
      var $editorTools = $('#wp-content-editor-tools'); // ⬅️ 获取编辑器工具栏容器
      
      if($editorTools.length){
        // 🔄 改为一上一下布局，将Markdown工具栏放置在原生工具栏容器之后
        // 这样可以确保它位于原生工具栏（包括媒体按钮和Quicktags）的下方，且互不干扰
        $editorTools.after($bar); 
      } else if($qtToolbar.length){
        // 🔄 降级方案：如果找不到容器，则尝试插在Quicktags之后
        $qtToolbar.after($bar); 
      } else if($emoji.length){
        $emoji.after($bar);
      } else{
        $wrap.prepend($bar);
      }
      $wrap.append($preview);
    }
    mountMdToolbar();
    function render(){
      $.post(BoxmoeMdEditor.ajaxUrl,{action:'boxmoe_md_preview',nonce:BoxmoeMdEditor.nonce,markdown:$ta.val()},function(resp){
        if(resp && resp.success){
          $inner.html(resp.data.html);
        }
      });
    }

    // 视图切换逻辑
    var currentMode = 'edit';
    var renderTimer = null;

    function switchMode(mode){
        currentMode = mode;
        $bar.find('.md-view-btn').removeClass('active');
        if(mode === 'edit') $btnEdit.addClass('active');
        if(mode === 'split') $btnSplit.addClass('active');
        if(mode === 'preview') $btnPreview.addClass('active');

        $wrap.removeClass('mode-edit mode-split mode-preview').addClass('mode-' + mode);
        
        if(mode === 'edit'){
            $ta.show();
            $preview.hide();
        } else if(mode === 'split'){
            $ta.show();
            $preview.show();
            render(); // 立即渲染
        } else if(mode === 'preview'){
            $ta.hide();
            $preview.show();
            render(); // 立即渲染
        }
    }

    // 绑定点击事件
    $btnEdit.on('click', function(){ switchMode('edit'); });
    $btnSplit.on('click', function(){ switchMode('split'); });
    $btnPreview.on('click', function(){ switchMode('preview'); });

    // 实时预览（防抖）
    $ta.on('input propertychange', function(){
        if(currentMode === 'split'){
            clearTimeout(renderTimer);
            renderTimer = setTimeout(function(){
                render();
            }, 800); // 800ms 防抖
        }
    });

    // 初始化默认模式
    // switchMode('edit'); // 默认为编辑模式，CSS中已处理显隐，这里主要用于状态同步
  });
})(jQuery);
