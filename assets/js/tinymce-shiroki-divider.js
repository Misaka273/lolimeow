/* 🌊 shiroki分割线TinyMCE插件
 * 🕊️白木 原创开发 🔗gl.baimu.live
 */
(function() {
    tinymce.create('tinymce.plugins.ShirokiDivider', {
        init: function(editor) {
            editor.addButton('shiroki_divider', {
                text: '分割线',
                tooltip: '插入粉紫蓝渐变波浪分割线',
                icon: false,
                onclick: function() {
                    editor.insertContent('\n<!--shiroki-divider-->\n');
                }
            });

            editor.addButton('shiroki_nbsp', {
                text: '换行',
                tooltip: '插入换行符 &nbsp;',
                icon: false,
                onclick: function() {
                    editor.insertContent('\n&nbsp;\n');
                }
            });
        },
        createControl: function() {
            return null;
        }
    });

    tinymce.PluginManager.add('shiroki_divider', tinymce.plugins.ShirokiDivider);
})();
