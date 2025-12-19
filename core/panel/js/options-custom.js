/**
 * Custom scripts needed for the colorpicker, image button selectors,
 * and navigation tabs.
 */

jQuery(document).ready(function($) {

	// Loads the color pickers
	$('.of-color').wpColorPicker();

	// Image Options
	$('.of-radio-img-img').click(function(){
		$(this).parent().parent().find('.of-radio-img-img').removeClass('of-radio-img-selected');
		$(this).addClass('of-radio-img-selected');
	});

	$('.of-radio-img-label').hide();
	$('.of-radio-img-img').show();
	$('.of-radio-img-radio').hide();

	// Loads tabbed sections if they exist
	if ( $('.nav-tab-wrapper').length > 0 ) {
		options_framework_tabs();
	}

	function options_framework_tabs() {

		var $group = $('.group'),
			$navtabs = $('.nav-tab-wrapper li a'),
			active_tab = '';

		// Hides all the .group sections to start
		$group.hide();
		$('.nav-tab-wrapper li').removeClass('active');

		// Find if a selected tab is saved in localStorage
		if ( typeof(localStorage) != 'undefined' ) {
			active_tab = localStorage.getItem('active_tab');
		}

		// If active tab is saved and exists, load it's .group
		if ( active_tab != '' && $(active_tab).length ) {
			$(active_tab).fadeIn();
			$(active_tab + '-tab').parent('li').addClass('active');
		} else {
			$('.group:first').fadeIn();
			$('.nav-tab-wrapper li:first').addClass('active');
		}

		// Bind tabs clicks
		$navtabs.click(function(e) {

			e.preventDefault();

			$('.nav-tab-wrapper li').removeClass('active');

			$(this).parent('li').addClass('active');
			$(this).blur();

			if (typeof(localStorage) != 'undefined' ) {
				localStorage.setItem('active_tab', $(this).attr('href') );
			}

			var selected = $(this).attr('href');

			$group.hide();
			$(selected).fadeIn();

		});
	}

	var $search = $('#of-search-input');
	if ($search.length) {
		function normalize(s){return (s||'').toLowerCase();}
		function fuzzyMatch(q, text){
			q = normalize(q);
			text = normalize(text);
			if (!q) return true;
			if (text.indexOf(q) !== -1) return true;
			var i=0,j=0;
			while (i<q.length && j<text.length){
				if (q.charCodeAt(i) === text.charCodeAt(j)) i++;
				j++;
			}
			return i===q.length;
		}
		function restoreTabs(){
			var $groups = $('.group');
			var href = $('.nav-tab-wrapper li.active a').attr('href') || $('.nav-tab-wrapper li a').first().attr('href');
			$groups.hide();
			if (href) $(href).fadeIn();
			$('.group .section').show();
		}
		function applySearch(){
			var q = $search.val().trim();
			var $groups = $('.group');
			if (!q){
				restoreTabs();
				return;
			}
			$groups.show();
			$('.nav-tab-wrapper li').removeClass('active');
			$('.group .section').each(function(){
				var $s = $(this);
				var name = $s.find('h4.heading').text();
				var gidText = $s.closest('.group').find('.boxmoe_tab_header').first().text();
				var hay = (name||'') + ' ' + (gidText||'');
				if (fuzzyMatch(q, hay)){
					$s.show();
				} else {
					$s.hide();
				}
			});
			$groups.each(function(){
				var $g = $(this);
				if ($g.find('.section:visible').length === 0){
					$g.hide();
				} else {
					$g.show();
				}
			});
		}
		$search.on('input', applySearch);
	}

	// Custom Board List Logic
	$(document).on('click', '.custom-board-add', function(e) {
		e.preventDefault();
		var $btn = $(this);
		var namePrefix = $btn.data('name');
		var $wrap = $btn.closest('.custom-board-list-wrap');
		var $items = $wrap.find('.custom-board-items');
		
		// Create a media frame
		var frame = wp.media({
			title: '选择看板图片',
			button: { text: '添加到列表' },
			multiple: false
		});
		
		frame.on('select', function() {
			var attachment = frame.state().get('selection').first().toJSON();
			var timestamp = new Date().getTime();
			var itemUrlName = namePrefix + '[' + timestamp + '][url]';
			var itemNameName = namePrefix + '[' + timestamp + '][name]';
			
			var html = '<div class="custom-board-item" style="width:150px;border:1px solid #ddd;padding:10px;border-radius:5px;background:#fff;text-align:center;">';
			html += '<div class="custom-board-preview" style="margin-bottom:10px;height:150px;display:flex;align-items:center;justify-content:center;overflow:hidden;background:#f5f5f5;">';
			html += '<img src="' + attachment.url + '" style="max-width:100%;max-height:100%;object-fit:contain;">';
			html += '</div>';
			html += '<input type="hidden" name="' + itemUrlName + '" value="' + attachment.url + '" class="custom-board-url">';
			html += '<div class="custom-board-input-group">';
			html += '<input type="text" name="' + itemNameName + '" value="" class="custom-board-name" placeholder=" ">';
			html += '<span class="custom-board-floating-label" data-normal="请输入名称" data-active="名称"></span>';
			html += '</div>';
			html += '<div class="actions" style="display:flex;justify-content:space-between;flex-wrap:wrap;gap:5px;">';
			html += '<button type="button" class="button button-secondary custom-board-enable" data-url="' + attachment.url + '" style="width:100%;margin-bottom:5px;">启动</button>';
			html += '<button type="button" class="button custom-board-replace" data-update="选择图片" data-choose="选择看板图片" style="flex:1;">替换</button>';
			html += '<button type="button" class="button custom-board-delete" style="color:#b32d2e;border-color:#b32d2e;flex:1;">删除</button>';
			html += '</div></div>';
			
			$items.append(html);
		});
		
		frame.open();
	});
	
	$(document).on('click', '.custom-board-replace', function(e) {
		e.preventDefault();
		var $btn = $(this);
		var $item = $btn.closest('.custom-board-item');
		var $input = $item.find('.custom-board-url');
		var $img = $item.find('img');
		var $enableBtn = $item.find('.custom-board-enable');
		
		var frame = wp.media({
			title: $btn.data('choose'),
			button: { text: $btn.data('update') },
			multiple: false
		});
		
		frame.on('select', function() {
			var attachment = frame.state().get('selection').first().toJSON();
			$input.val(attachment.url);
			$img.attr('src', attachment.url);
			$enableBtn.data('url', attachment.url);
			
			// If this item was enabled, update the main radio selection too
			if($enableBtn.hasClass('button-primary')) {
				updateMainRadio(attachment.url);
			}
		});
		
		frame.open();
	});
	
	$(document).on('click', '.custom-board-delete', function(e) {
		e.preventDefault();
		if(confirm('确定要删除吗？')) {
			var $item = $(this).closest('.custom-board-item');
			// If this item was enabled, maybe we should select default?
			// For now just remove it.
			$item.remove();
		}
	});
	
	// Add board by direct URL
	$(document).on('click', '.custom-board-add-by-url', function(e) {
		e.preventDefault();
		var $btn = $(this);
		var namePrefix = $btn.data('name');
		var $wrap = $btn.closest('.custom-board-list-wrap');
		var $items = $wrap.find('.custom-board-items');
		var $urlInput = $('#custom-board-direct-url');
		var url = $urlInput.val().trim();
		
		if (!url) {
			alert('请输入图片链接');
			return;
		}
		
		// Simple URL validation
		var urlPattern = /^https?:\/\/(www\.)?[-a-zA-Z0-9@:%._\+~#=]{1,256}\.[a-zA-Z0-9()]{1,6}\b([-a-zA-Z0-9()@:%_\+.~#?&//=]*)$/;
		if (!urlPattern.test(url)) {
			alert('请输入有效的图片链接');
			return;
		}
		
		var timestamp = new Date().getTime();
		var itemUrlName = namePrefix + '[' + timestamp + '][url]';
		var itemNameName = namePrefix + '[' + timestamp + '][name]';
		
		var html = '<div class="custom-board-item" style="width:150px;border:1px solid #ddd;padding:10px;border-radius:5px;background:#fff;text-align:center;">';
		html += '<div class="custom-board-preview" style="margin-bottom:10px;height:150px;display:flex;align-items:center;justify-content:center;overflow:hidden;background:#f5f5f5;">';
		html += '<img src="' + url + '" style="max-width:100%;max-height:100%;object-fit:contain;">';
		html += '</div>';
		html += '<input type="hidden" name="' + itemUrlName + '" value="' + url + '" class="custom-board-url">';
		html += '<div class="custom-board-input-group">';
		html += '<input type="text" name="' + itemNameName + '" value="" class="custom-board-name" placeholder=" ">';
		html += '<span class="custom-board-floating-label" data-normal="请输入名称" data-active="名称"></span>';
		html += '</div>';
		html += '<div class="actions" style="display:flex;justify-content:space-between;flex-wrap:wrap;gap:5px;">';
		html += '<button type="button" class="button button-secondary custom-board-enable" data-url="' + url + '" style="width:100%;margin-bottom:5px;">启动</button>';
		html += '<button type="button" class="button custom-board-replace" data-update="选择图片" data-choose="选择看板图片" style="flex:1;">替换</button>';
		html += '<button type="button" class="button custom-board-delete" style="color:#b32d2e;border-color:#b32d2e;flex:1;">删除</button>';
		html += '</div></div>';
		
		$items.append(html);
		$urlInput.val(''); // Clear input
	});
	
	// Allow Enter key to add by URL
	$(document).on('keypress', '#custom-board-direct-url', function(e) {
		if (e.which == 13) { // Enter key
			e.preventDefault();
			$('.custom-board-add-by-url').click();
		}
	});

	// Enable button logic
	$(document).on('click', '.custom-board-enable', function(e) {
		e.preventDefault();
		var $btn = $(this);
		var url = $btn.data('url');
		
		if ($btn.hasClass('disabled')) return; // Already enabled
		
		// Reset all enable buttons in the list
		$('.custom-board-enable').removeClass('button-primary disabled').addClass('button-secondary').text('启动');
		
		// Set this one to enabled
		$btn.removeClass('button-secondary').addClass('button-primary disabled').text('已启动');
		
		// Update the main radio input
		updateMainRadio(url);
	});
	
	function updateMainRadio(url) {
		// Find radio with this value
		var $radio = $('input[name$="[boxmoe_lolijump_img]"][value="' + url + '"]');
		
		if ($radio.length > 0) {
			$radio.prop('checked', true);
		} else {
			// If radio doesn't exist (new item), create a hidden one or check if we can add it to the radio group container
			// The radio group container usually has class 'controls' or similar in OF.
			// Let's look for the radio group container by finding one of the radios
			var $anyRadio = $('input[name$="[boxmoe_lolijump_img]"]').first();
			if ($anyRadio.length > 0) {
				// Uncheck all radios
				$('input[name$="[boxmoe_lolijump_img]"]').prop('checked', false);
				
				// Create a hidden radio and append it to the form so it submits
				// We need to make sure we don't duplicate
				var radioName = $anyRadio.attr('name');
				var $existingHidden = $('input.custom-board-hidden-radio[value="' + url + '"]');
				
				if($existingHidden.length === 0) {
					var $hiddenRadio = $('<input type="radio" class="custom-board-hidden-radio" style="display:none;" checked="checked">');
					$hiddenRadio.attr('name', radioName);
					$hiddenRadio.val(url);
					$anyRadio.parent().append($hiddenRadio);
				} else {
					$existingHidden.prop('checked', true);
				}
			}
		}
	}
	
	// Listen for changes on the main radio group to update buttons
	$(document).on('change', 'input[name$="[boxmoe_lolijump_img]"]', function() {
		var val = $(this).val();
		
		// Reset all buttons
		$('.custom-board-enable').removeClass('button-primary disabled').addClass('button-secondary').text('启动');
		
		// Find button with this url
		var $btn = $('.custom-board-enable[data-url="' + val + '"]');
		if ($btn.length > 0) {
			$btn.removeClass('button-secondary').addClass('button-primary disabled').text('已启动');
		}
	});

});
