jQuery(document).ready(function($){

	var optionsframework_upload;
	var optionsframework_selector;

	function optionsframework_add_file(event, selector) {

		var upload = $(".uploaded-file"), frame;
		var $el = $(this);
		optionsframework_selector = selector;

		event.preventDefault();

		// If the media frame already exists, reopen it.
		if ( optionsframework_upload ) {
			optionsframework_upload.open();
		} else {
			// Create the media frame.
			optionsframework_upload = wp.media.frames.optionsframework_upload =  wp.media({
				// Set the title of the modal.
				title: $el.data('choose') || '选择图片',

				// Customize the submit button.
				button: {
					// Set the text of the button.
					text: $el.data('update') || '使用此图片',
					// Tell the button not to close the modal, since we're
					// going to refresh the page when the image is selected.
					close: false
				}
			});

			// When an image is selected, run a callback.
			optionsframework_upload.on( 'select', function() {
				// Grab the selected attachment.
				var attachment = optionsframework_upload.state().get('selection').first();
				optionsframework_upload.close();
				// 更新输入框的值
				var uploadInput = optionsframework_selector.find('.upload, .of-input');
				uploadInput.val(attachment.attributes.url).addClass('has-file');
				// 更新预览图，确保预览图等比例显示
				var screenshot = optionsframework_selector.find('.screenshot');
				if ( attachment.attributes.type == 'image' ) {
					screenshot.empty().append('<img src="' + attachment.attributes.url + '" style="max-width: 162px; max-height: 75px; object-fit: contain; background: #f5f5f5;">').show();
				}
				// 更新按钮状态
				var uploadButton = optionsframework_selector.find('.upload-button');
				var removeButton = optionsframework_selector.find('.remove-file');
				uploadButton.val('替换');
				removeButton.show();
			});

		}

		// Finally, open the modal.
		optionsframework_upload.open();
	}

	// 重置功能
	function optionsframework_reset_file(selector, defaultUrl) {
		// 重置输入框的值
		var uploadInput = selector.find('.upload, .of-input');
		uploadInput.val(defaultUrl);
		// 重置预览图，确保预览图等比例显示
		var screenshot = selector.find('.screenshot');
		if (defaultUrl) {
			screenshot.empty().append('<img src="' + defaultUrl + '" style="max-width: 162px; max-height: 75px; object-fit: contain; background: #f5f5f5;">').show();
		} else {
			screenshot.empty().hide();
		}
	}

	// 绑定替换按钮事件
	$('.upload-button').click( function( event ) {
    	optionsframework_add_file(event, $(this).parents('.section'));
    });

    // 绑定重置按钮事件
    $('.reset-button').click(function(event) {
    	event.preventDefault();
    	var defaultUrl = $(this).data('default');
    	optionsframework_reset_file($(this).parents('.section'), defaultUrl);
    });

    // 绑定确认按钮事件，显示顶部横幅提示
    $('.confirm-button').click(function(event) {
    	event.preventDefault();
    	// 显示顶部横幅提示
    	let banner = document.querySelector('.copy-banner');
    	if (!banner) {
    		banner = document.createElement('div');
    		banner.className = 'copy-banner';
    		document.body.appendChild(banner);
    	}
    	banner.innerHTML = '<i class="fa fa-check-circle"></i> 已替换，点击保存设置即可生效🎉';
    	let timer = null;
    	const show = function() {
    		if (timer) { try { clearTimeout(timer); } catch(_) {} }
    		banner.classList.remove('mask-run');
    		void banner.offsetWidth;
    		banner.classList.add('mask-run');
    		banner.classList.add('show');
    		timer = setTimeout(function() {
    			banner.classList.remove('show');
    			banner.classList.remove('mask-run');
    		}, 5000);
    	};
    	show();
    });
    
    // 绑定删除按钮事件
    $('.remove-file').click(function(event) {
    	event.preventDefault();
    	const btn = $(this);
    	const selector = btn.parents('.section');
    	const uploadInput = selector.find('.upload, .of-input');
    	const screenshot = selector.find('.screenshot');
    	const uploadButton = selector.find('.upload-button');
    	
    	// 清空输入框
    	uploadInput.val('').removeClass('has-file');
    	// 清空预览图
    	screenshot.empty().hide();
    	// 重置按钮状态
    	btn.hide();
    	uploadButton.show().val('上传');
    });

});