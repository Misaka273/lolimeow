"use strict";
// 主题初始化
var theme = {
	init: function() {
        theme.menu(), 
        theme.otpVarification(), 
        theme.popovers(), 
        theme.tooltip(), 
        theme.validation()
	},
	menu: () => {
		document.querySelectorAll(".dropdown-menu a.dropdown-toggle")
			.forEach((function(e) {
				e.addEventListener("click", (function(e) {
					if (!this.nextElementSibling.classList.contains("show")) {
						this.closest(".dropdown-menu")
							.querySelectorAll(".show")
							.forEach((function(e) {
								e.classList.remove("show")
							}))
					}
					this.nextElementSibling.classList.toggle("show");
					const t = this.closest("li.nav-item.dropdown.show");
					t && t.addEventListener("hidden.bs.dropdown", (function(e) {
						document.querySelectorAll(".dropdown-submenu .show")
							.forEach((function(e) {
								e.classList.remove("show")
							}))
					})), e.stopPropagation()
				}))
			}))
	},
	popovers: () => {
		[...document.querySelectorAll('[data-bs-toggle="popover"]')].map((e => new bootstrap.Popover(e)))
	},
	tooltip: () => {
		[...document.querySelectorAll('[data-bs-toggle="tooltip"]')].map((e => new bootstrap.Tooltip(e)))
	},
	validation: () => {
		const e = document.querySelectorAll(".needs-validation:not(#loginform)");
		Array.from(e)
			.forEach((e => {
				e.addEventListener("submit", (t => {
					e.checkValidity() || (t.preventDefault(), t.stopPropagation()), e.classList.add("was-validated")
				}), !1)
			}))
	},
	otpVarification: () => {
		document.moveToNextInput = function(e) {
			if (e.value.length === e.maxLength) {
				const t = Array.from(e.parentElement.children)
					.indexOf(e),
					n = e.parentElement.children[t + 1];
				n && n.focus()
			}
		}
	}
};
theme.init();

var navbar = document.querySelector(".navbar");
const navOffCanvasBtn = document.querySelectorAll(".offcanvas-nav-btn"),
    navOffCanvas = document.querySelector(".navbar:not(.navbar-clone) .offcanvas-nav");
let bsOffCanvas;
function toggleOffCanvas() {
    if (bsOffCanvas) {
        if (bsOffCanvas._isShown) {
            bsOffCanvas.hide();
            // 隐藏时移除active类
            navOffCanvasBtn.forEach(btn => btn.classList.remove("active"));
        } else {
            bsOffCanvas.show();
            // 显示时添加active类
            navOffCanvasBtn.forEach(btn => btn.classList.add("active"));
        }
    }
}
navOffCanvas && (bsOffCanvas = new bootstrap.Offcanvas(navOffCanvas, {
    scroll: !0,
    backdrop: true
}), navOffCanvasBtn.forEach((e => {
    e.addEventListener("click", (e => {
        toggleOffCanvas()
    }))
})));

// 监听Offcanvas的显示/隐藏事件，同步按钮状态
navOffCanvas && (navOffCanvas.addEventListener('show.bs.offcanvas', function () {
    navOffCanvasBtn.forEach(btn => btn.classList.add("active"));
}), navOffCanvas.addEventListener('hide.bs.offcanvas', function () {
    navOffCanvasBtn.forEach(btn => btn.classList.remove("active"));
}));
function showToast(message, isSuccess = true) {
    const toastId = 'toast-' + Date.now();
    // 动态读取当前网站设置的Favicon地址
    let siteLogo = '${ajax_object.themeurl}/assets/images/msg-tip.png'; // 默认图标
    const faviconLink = document.querySelector('link[rel*="icon"]');
    if (faviconLink && faviconLink.href) {
        siteLogo = faviconLink.href;
    }
    // 获取复制的实际内容，显示为"{文本}"
    const copyContent = '' + message;
    
    // 漫画风背景样式
    const comicBackground = `
        background: linear-gradient(135deg, #ff9a9e 0%, #fad0c4 99%, #fad0c4 100%);
        border-radius: 10px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
        font-family: 'Comic Sans MS', cursive, sans-serif;
    `;
    
    const toastHtml = `
        <div id="${toastId}" class="toast" role="alert" aria-live="assertive" aria-atomic="true" style="${comicBackground}">
            <div class="toast-header" style="background: rgba(255, 255, 255, 0.8); border-bottom: none;">
                <img src="${siteLogo}" class="rounded me-2 avatar-xs" alt="网站logo">
                <strong class="me-auto" style="color: #ff6b6b;">温馨提示</strong>
                <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
            <div class="toast-body" style="color: #2d3436;">
                ${copyContent}
            </div>
        </div>
    `;
    
    let toastContainer = document.querySelector('.toast-container');
    if (!toastContainer) {
        toastContainer = document.createElement('div');
        toastContainer.className = 'toast-container position-fixed bottom-0 end-0 p-3';
        document.body.appendChild(toastContainer);
    }   
    
    toastContainer.insertAdjacentHTML('beforeend', toastHtml);
    
    const toastElement = document.getElementById(toastId);
    // 移除默认背景类，使用自定义漫画风背景
    toastElement.className = `toast align-items-center border-0`;
    
    const toast = new bootstrap.Toast(toastElement, {
        autohide: true,
        delay: 5000
    });
    
    toast.show();
    
    toastElement.addEventListener('hidden.bs.toast', () => {
        toastElement.remove();
    });
}
// 搜索框初始化
function initSearchBox() {
    const searchBtns = document.querySelectorAll('.search-btn, .mobile-search-btn');
    const searchForms = document.querySelectorAll('.search-form, .mobile-search-form');
    
    searchBtns.forEach((btn, index) => {
        const form = searchForms[index];
        const input = form.querySelector('input[type="search"]');
        
        if (btn && form && input) {
            btn.addEventListener('click', function(e) {
                if (!form.classList.contains('active')) {
                    e.preventDefault();
                    e.stopPropagation();
                    form.classList.add('active');
                    setTimeout(() => {
                        input.focus();
                    }, 100);
                }
            });

            form.addEventListener('submit', function(e) {
                if (!input.value.trim()) {
                    e.preventDefault();
                }
            });

            document.addEventListener('click', function(e) {
                if (!form.contains(e.target) && !btn.contains(e.target)) {
                    form.classList.remove('active');
                }
            });

            input.addEventListener('keydown', function(e) {
                if (e.key === 'Escape') {
                    form.classList.remove('active');
                    input.blur();
                }
            });
        }
    });
}
// 用户面板初始化
function initMobileUserPanel() {
    const mobileUserBtn = document.querySelector('.mobile-user-btn');
    const mobileUserPanel = document.querySelector('.mobile-user-panel');  
    if(mobileUserBtn && mobileUserPanel) {
        mobileUserBtn.addEventListener('click', function() {
            if (!mobileUserPanel.classList.contains('active')) {
                mobileUserPanel.classList.remove('closing');
                mobileUserPanel.classList.add('active');
            } else {
                mobileUserPanel.classList.add('closing');
                mobileUserPanel.classList.remove('active');
            }
        });
        document.addEventListener('click', function(e) {
            if(!mobileUserPanel.contains(e.target) && !mobileUserBtn.contains(e.target)) {
                if (mobileUserPanel.classList.contains('active')) {
                    mobileUserPanel.classList.add('closing');
                    mobileUserPanel.classList.remove('active');
                }
            }
        });
    }
}

// 懒加载初始化
function initLazyLoad() {
    const lazyImages = document.querySelectorAll('img.lazy');
    const loadImage = (img) => {
      let ds = img.dataset && img.dataset.src ? img.dataset.src : '';
      if (!ds) {
        const attrs = ['original','lazy','lazySrc','srcLazy'];
        for (let i=0;i<attrs.length;i++){ const k = 'data-'+attrs[i].replace(/[A-Z]/g, m => '-' + m.toLowerCase()); const v = img.getAttribute(k); if (v) { ds = v; break; } }
        if (!ds && (img.getAttribute('src')||'').includes('/assets/images/loading.gif')) {
          const a = img.closest('a');
          const ah = a ? (a.getAttribute('data-src') || a.getAttribute('href') || '') : '';
          if (/\.(?:jpe?g|png|webp|gif)(\?.*)?$/i.test(ah)) ds = ah;
        }
      }
      if (!ds) { img.classList.remove('lazy'); return; }
      let base = ds, query = '';
      const qm = base.match(/^(.*?)(\?.*)$/);
      if (qm) { base = qm[1]; query = qm[2]; }
      if (/\.gif$/i.test(base)) { base = base.replace(/-\d+x\d+(?=\.gif$)/i, ''); }
      const fixed = base + (query || '');
      if (img.hasAttribute('srcset')) img.removeAttribute('srcset');
      if (img.hasAttribute('sizes')) img.removeAttribute('sizes');
      const onLoad = () => { img.classList.remove('lazy'); img.removeEventListener('load', onLoad); };
      const onError = () => { img.classList.remove('lazy'); img.removeAttribute('loading'); img.removeEventListener('error', onError); if (img.getAttribute('src') !== ds) img.setAttribute('src', ds); };
      img.addEventListener('load', onLoad);
      img.addEventListener('error', onError);
      img.src = fixed;
    };
    const forceLoadAll = () => {
      document.querySelectorAll('img.lazy').forEach(loadImage);
    };
    if ('IntersectionObserver' in window) {
      const imageObserver = new IntersectionObserver((entries, observer) => {
        entries.forEach(entry => {
          if (entry.isIntersecting) {
            const img = entry.target;
            loadImage(img);
            observer.unobserve(img);
          }
        });
      }, { rootMargin: '200px 0px', threshold: 0.01 });
      lazyImages.forEach(img => imageObserver.observe(img));
      const mo = new MutationObserver((mutations) => {
        mutations.forEach(m => {
          m.addedNodes && m.addedNodes.forEach(node => {
            if (node.nodeType === 1 && node.querySelectorAll) {
              node.querySelectorAll('img.lazy').forEach(img => imageObserver.observe(img));
            }
          });
        });
      });
      mo.observe(document.body, { childList: true, subtree: true });
      setTimeout(forceLoadAll, 2000);
      window.addEventListener('load', forceLoadAll, { once: true });
      window.addEventListener('scroll', () => {
        document.querySelectorAll('img.lazy').forEach(img => {
          const rect = img.getBoundingClientRect();
          if (rect.top < window.innerHeight + 300) loadImage(img);
        });
      });
    } else {
      lazyImages.forEach(loadImage);
    }
  }

// 加载延迟初始化
function initBannerImage() {
    const bannerImg = document.querySelector('.boxmoe_header_banner_img');
    const siteMain = document.querySelector('.boxmoe_header_banner .site-main');
    if (!bannerImg || !siteMain) return;
    const img = bannerImg.querySelector('img');
    if (!img) return;

    // 确保内容最终会显示的函数
    const showContent = () => {
      bannerImg.classList.add('loaded');
      setTimeout(() => {
        siteMain.classList.add('loaded');
      }, 500);
    };

    // 如果图片已经加载完成
    if(img.complete) {
      showContent();
    } else {
      // 图片加载成功时
      img.addEventListener('load', showContent);
      // 图片加载失败时的后备方案
      img.addEventListener('error', showContent);
      // 添加超时机制，确保内容最终会显示
      setTimeout(showContent, 3000);
    }
}
// Headhesive初始化
function initStickyHeader() {
  const header = document.querySelector('.boxmoe_header .navbar');
  if (!header) return;
  let lastScrollTop = 0;
  const headerHeight = header.offsetHeight;
  window.addEventListener('scroll', () => {
    const scrollTop = window.scrollY || document.documentElement.scrollTop;
    if (!header) return;

    if (scrollTop > headerHeight) {
      if (scrollTop > lastScrollTop) {
        header.classList.add('scrolled');
        header.classList.remove('boxed', 'mx-auto', 'nav-down');
        header.classList.add('boxed', 'mx-auto', 'nav-up');
      } else {
        header.classList.add('scrolled');
        header.classList.remove('boxed', 'mx-auto', 'nav-up');
        header.classList.add('boxed', 'mx-auto', 'nav-down');
      }

    } else {
      header.classList.remove('boxed', 'mx-auto', 'scrolled', 'nav-up', 'nav-down');
    }  
    lastScrollTop = scrollTop;
  });
}

// 文章导读初始化
function initTableOfContents() {
    const content = document.querySelector('.single-content');
    const tocContainer = document.querySelector('.post-toc-container');
    const tocBtn = document.querySelector('.post-toc-btn');
    const toc = document.querySelector('.post-toc');
    const tocList = document.querySelector('.toc-list');   
    if(!content || !tocBtn || !toc || !tocList) return; 
    const headers = content.querySelectorAll('h1, h2, h3, h4');
    if(headers.length === 0) {
        tocContainer.style.display = 'none';
        return;
    }
    let isScrolling;
    const counters = [0, 0, 0, 0]; 
    let currentLevel = 0;
    headers.forEach((header, index) => {
        const level = parseInt(header.tagName[1]) - 1;     
        counters[level]++;
        for(let i = level + 1; i < 4; i++) counters[i] = 0; 
        
        const numberParts = [];
        for(let i = 0; i <= level; i++) {
            if(counters[i] > 0) numberParts.push(counters[i]);
        }
        const numberStr = numberParts.join('.');

        const link = document.createElement('a');
        const id = `header-${index}`;
        header.id = id;
        link.href = `#${id}`;
                link.textContent = `${numberStr} ${header.textContent}`;
        link.style.paddingLeft = `${level * 10}px`;
        tocList.appendChild(link);
    });
    const showOffset = 350;
    window.addEventListener('scroll', () => {
        const scrollPos = window.scrollY;
        if(scrollPos > showOffset) {
            tocContainer.classList.add('visible');
            tocBtn.classList.add('visible');
        } else {
            tocContainer.classList.remove('visible');
            tocBtn.classList.remove('visible');
            toc.classList.remove('show'); 
        }
        clearTimeout(isScrolling);
        isScrolling = setTimeout(() => {
            const links = tocList.querySelectorAll('a');
            let currentActive = null;
            
            const navHeight = document.querySelector('.navbar')?.offsetHeight || 0;
            const buffer = 20;
            for(let i = 0; i < headers.length; i++) {
                const headerRect = headers[i].getBoundingClientRect();
                if (headerRect.top <= navHeight + buffer && headerRect.bottom > navHeight) {
                    currentActive = links[i];
                    break;
                }
            }
            if (!currentActive) {
                for(let i = headers.length - 1; i >= 0; i--) {
                    const headerRect = headers[i].getBoundingClientRect();
                    if (headerRect.top <= navHeight + buffer) {
                        currentActive = links[i];
                        break;
                    }
                }
            }
            if(currentActive && !currentActive.classList.contains('active')) {
                links.forEach(link => link.classList.remove('active'));
                currentActive.classList.add('active');       
                const tocListRect = tocList.getBoundingClientRect();
                const activeLinkRect = currentActive.getBoundingClientRect();
                if (activeLinkRect.top < tocListRect.top) {
                    tocList.scrollTop -= (tocListRect.top - activeLinkRect.top + 50);
                } else if (activeLinkRect.bottom > tocListRect.bottom) {
                    tocList.scrollTop += (activeLinkRect.bottom - tocListRect.bottom + 50);
                }
            }
        }, 50);
    });
    tocList.addEventListener('click', (e) => {
        if(e.target.tagName === 'A') {
            e.preventDefault();     
            tocList.querySelectorAll('a').forEach(link => {
                link.classList.remove('active');
            });
            e.target.classList.add('active');
            
            const targetId = e.target.getAttribute('href').slice(1);
            const targetHeader = document.getElementById(targetId);
            
            if(targetHeader) {
                const navHeight = document.querySelector('.navbar')?.offsetHeight || 0;
                const targetPosition = targetHeader.getBoundingClientRect().top + window.scrollY - navHeight - 10;       
                const tocListRect = tocList.getBoundingClientRect();
                const clickedLinkRect = e.target.getBoundingClientRect();               
                if (clickedLinkRect.top < tocListRect.top) {
                    tocList.scrollTop += clickedLinkRect.top - tocListRect.top;
                } else if (clickedLinkRect.bottom > tocListRect.bottom) {
                    tocList.scrollTop += clickedLinkRect.bottom - tocListRect.bottom;
                }             
                window.scrollTo({
                    top: targetPosition,
                    behavior: 'smooth'
                });
            }
        }
    });
    tocBtn.addEventListener('click', () => {
        toc.classList.toggle('show');
    });
    document.addEventListener('click', (e) => {
        if(!toc.contains(e.target) && !tocBtn.contains(e.target)) {
            toc.classList.remove('show');
        }
    });
}

// 标签颜色初始化
function initTagColors() {
    const colors = [
        "#83ea6c", "#1dd7c2", "#85b2f4", "#ffcf00", "#f4c8c6", "#e6f2e4", 
        "#83ea6c", "#1dd7c2", "#85b2f4", "#0dcaf0", "#e8d8ff", "#ffd700", 
        "#ff7f50", "#6495ed", "#b0e0e6", "#ff6347", "#98fb98", "#dda0dd", 
        "#add8e6", "#ff4500", "#d3d3d3", "#00bfff", "#ff1493", "#ff6347", 
        "#8a2be2", "#7fff00", "#d2691e", "#a52a2a", "#9acd32", "#ff8c00", 
        "#dcdcdc", "#dc143c", "#f0e68c", "#ff00ff", "#4b0082", "#8b0000", 
        "#e9967a", "#ff00ff", "#2e8b57", "#3cb371", "#f5deb3", "#ff69b4"
    ];  
    document.querySelectorAll('.blog-post .tagfa').forEach((element, index) => {
        if (index < colors.length) {
            element.style.color = colors[index];
        }
    });   
    document.querySelectorAll('.tag-cloud .tagfa').forEach((element, index) => {
        if (index < colors.length) {
            element.style.color = colors[index];
        }
    });
}

// 一言初始化
function initHitokoto() {
    if (!document.getElementById('hitokoto')) return;
    const hitokotoParam = window.ajax_object ? window.ajax_object.hitokoto : 'a';
    fetch(`https://v1.hitokoto.cn/?c=${hitokotoParam}`)
        .then(response => response.json())
        .then(data => {
            const hitokotoEl = document.getElementById('hitokoto');
            hitokotoEl && (hitokotoEl.textContent = data.hitokoto);
        })
}

// 🔐 登录状态管理
const LoginStatusManager = (() => {
    // 配置项
    const config = {
        checkInterval: 30000, // 30秒检查一次
        retryAttempts: 3, // 重试次数
        retryDelay: 2000, // 重试延迟
        localStorageKey: 'boxmoe_login_status', // 本地存储键名
        localStorageTTL: 604800000 // 本地存储有效期（7天）
    };
    
    // 状态
    let isChecking = false;
    let currentAttempt = 0;
    
    /**
     * 从本地存储获取登录状态
     */
    const getLoginStatusFromLocalStorage = () => {
        try {
            const stored = localStorage.getItem(config.localStorageKey);
            if (!stored) {
                return null;
            }
            
            const data = JSON.parse(stored);
            const now = Date.now();
            
            // 检查是否过期
            if (now - data.timestamp > config.localStorageTTL) {
                localStorage.removeItem(config.localStorageKey);
                return null;
            }
            
            return data;
        } catch (error) {
            console.warn('从本地存储获取登录状态失败:', error);
            localStorage.removeItem(config.localStorageKey);
            return null;
        }
    };
    
    /**
     * 将登录状态保存到本地存储
     */
    const saveLoginStatusToLocalStorage = (isLoggedIn, userInfo = {}) => {
        try {
            const data = {
                is_logged_in: isLoggedIn,
                user_info: userInfo,
                timestamp: Date.now()
            };
            localStorage.setItem(config.localStorageKey, JSON.stringify(data));
        } catch (error) {
            console.warn('将登录状态保存到本地存储失败:', error);
        }
    };
    
    /**
     * 清除本地存储的登录状态
     */
    const clearLoginStatusFromLocalStorage = () => {
        try {
            localStorage.removeItem(config.localStorageKey);
        } catch (error) {
            console.warn('清除本地存储的登录状态失败:', error);
        }
    };
    
    /**
     * 检查登录状态
     */
    const checkLoginStatus = async () => {
        if (isChecking || !window.ajax_object) {
            return;
        }
        
        isChecking = true;
        currentAttempt++;
        
        try {
            const response = await fetch(ajax_object.ajaxurl, {
                method: 'POST',
                credentials: 'same-origin',
                body: new URLSearchParams({
                    action: 'boxmoe_check_login_status',
                    nonce: ajax_object.nonce
                })
            });
            
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            
            const data = await response.json();
            
            if (data.success) {
                updateLoginUI(data.data.is_logged_in, data.data.user_info);
                saveLoginStatusToLocalStorage(data.data.is_logged_in, data.data.user_info);
                currentAttempt = 0; // 重置重试次数
            } else {
                throw new Error(data.data?.message || '登录状态检查失败');
            }
        } catch (error) {
            console.warn('登录状态检查失败:', error);
            
            // 重试机制
            if (currentAttempt < config.retryAttempts) {
                setTimeout(() => {
                    checkLoginStatus();
                }, config.retryDelay);
            } else {
                // 重试次数耗尽，使用本地存储状态
                console.warn('登录状态检查重试次数耗尽，使用本地存储状态');
                const storedStatus = getLoginStatusFromLocalStorage();
                if (storedStatus) {
                    updateLoginUI(storedStatus.is_logged_in, storedStatus.user_info);
                }
                currentAttempt = 0;
            }
        } finally {
            isChecking = false;
        }
    };
    
    /**
     * 更新登录UI
     */
    const updateLoginUI = (isLoggedIn, userInfo = {}) => {
        // 检查本地状态
        const currentIsLoggedIn = window.ajax_object?.is_user_logged_in === 'true';
        
        // 如果状态没有变化，跳过更新
        if (currentIsLoggedIn === isLoggedIn) {
            return;
        }
        
        // 更新全局状态
        if (window.ajax_object) {
            window.ajax_object.is_user_logged_in = isLoggedIn ? 'true' : 'false';
        }
        
        // 重新渲染登录相关UI
        renderLoginUI(isLoggedIn, userInfo);
        
        // 如果从登录状态变为未登录状态，清除本地存储
        if (currentIsLoggedIn && !isLoggedIn) {
            clearLoginStatusFromLocalStorage();
        }
    };
    
    /**
     * 渲染登录UI
     */
    const renderLoginUI = (isLoggedIn, userInfo) => {
        try {
            // 处理移动端用户面板
            const mobileUserBtn = document.querySelector('.mobile-user-btn');
            const mobileUserPanels = document.querySelectorAll('.mobile-user-panel');
            
            if (mobileUserPanels.length > 0) {
                // 移除所有现有面板
                mobileUserPanels.forEach(panel => {
                    try {
                        panel.remove();
                    } catch (error) {
                        console.warn('移除移动端用户面板失败:', error);
                    }
                });
                
                // 创建新的用户面板
                const newPanel = document.createElement('div');
                newPanel.className = 'mobile-user-panel';
                
                try {
                    if (isLoggedIn) {
                        newPanel.innerHTML = `
                            <div class="user-panel-content">
                                <div class="mobile-user-wrapper">
                                    <div class="mobile-logged-menu">
                                        <a href="${getUserCenterLink()}" class="mobile-menu-item">
                                            <i class="fa fa-user-circle"></i>
                                            <span>会员中心</span></a>
                                            ${isAdmin() ? `
                                        <a href="${admin_url()}" class="mobile-menu-item">
                                            <i class="fa fa-cog"></i>
                                            <span>后台管理</span></a>
                                            ` : ''}
                                        <a href="${getLogoutUrl()}" class="mobile-menu-item">
                                            <i class="fa fa-sign-out"></i>
                                            <span>注销登录</span></a>
                                    </div>
                                </div>
                            </div>
                        `;
                    } else {
                        newPanel.innerHTML = `
                            <div class="user-panel-content">
                                <div class="mobile-user-wrapper">
                                    <div class="mobile-logged-menu">
                                    <div class="user-wrapper d-lg-flex">
                                <div class="user-login-wrap">
                                <a href="${getLoginLink()}" class="user-login">
                                <span class="login-text">登录</span></a>
                                </div>
                                <span class="divider">or</span>
                                <div class="user-reg-wrap">
                                <a href="${getRegisterLink()}" class="user-reg">
                                <span class="reg-text">注册</span></a></div>
                                <img src="${ajax_object.themeurl}/assets/images/up-new-iocn.png" class="new-tag" alt="up-new-iocn">
                                </div>
                                    </div>
                                </div>
                            </div>
                        `;
                    }
                } catch (error) {
                    console.error('创建用户面板HTML失败:', error);
                    return;
                }
                
                if (mobileUserBtn && mobileUserBtn.parentElement) {
                    try {
                        mobileUserBtn.parentElement.appendChild(newPanel);
                    } catch (error) {
                        console.warn('添加移动端用户面板失败:', error);
                    }
                }
            }
            
            // 处理桌面端用户面板
            const desktopUserWrappers = document.querySelectorAll('.user-wrapper, .logged-user-wrapper');
            
            if (desktopUserWrappers.length > 0) {
                // 移除所有现有面板
                desktopUserWrappers.forEach(wrapper => {
                    try {
                        wrapper.remove();
                    } catch (error) {
                        console.warn('移除桌面端用户面板失败:', error);
                    }
                });
                
                // 创建新的桌面用户面板
                const navRightSection = document.querySelector('.nav-right-section');
                if (navRightSection) {
                    const newWrapper = document.createElement('div');
                    
                    try {
                        if (isLoggedIn) {
                            newWrapper.className = 'logged-user-wrapper d-none d-lg-flex';
                            newWrapper.innerHTML = `
                                <div class="user-info-wrap d-flex align-items-center dropdown">
                                    <a href="${getUserCenterLink()}" class="dropdown-toggle d-flex align-items-center" data-bs-toggle="dropdown" aria-expanded="false">
                                        <div class="user-avatar">
                                        <img src="${ajax_object.themeurl}/assets/images/loading.gif" data-src="${getUserAvatarUrl(userInfo.user_id || 0)}" alt="avatar" class="img-fluid rounded-3 lazy">
                                    </div>
                                        <div class="user-info">
                                            <div class="user-name">${userInfo.display_name || '用户'}</div>
                                            <div class="user-email">${userInfo.user_email || ''}</div>
                                    </div>
                                    </a>
                                    <ul class="dropdown-menu dropdown-menu-end">
                                      <li>
                                        <a class="dropdown-item" href="${getUserCenterLink()}">
                                          <i class="fa fa-user-circle"></i>会员中心</a>
                                      </li>
                                      ${isAdmin() ? `
                                      <li>
                                        <a class="dropdown-item" target="_blank" href="${admin_url()}">
                                          <i class="fa fa-cog"></i>后台管理</a>
                                      </li>
                                      ` : ''}
                                      <li>
                                        <a class="dropdown-item" href="${getLogoutUrl()}">
                                          <i class="fa fa-sign-out"></i>注销登录</a>
                                      </li>
                                    </ul>
                                </div>
                            `;
                        } else {
                            newWrapper.className = 'user-wrapper d-none d-lg-flex';
                            newWrapper.innerHTML = `
                                <div class="user-login-wrap">
                                <a href="${getLoginLink()}" class="user-login">
                                <span class="login-text">登录</span></a>
                                </div>
                                <span class="divider">or</span>
                                <div class="user-reg-wrap">
                                <a href="${getRegisterLink()}" class="user-reg">
                                <span class="reg-text">注册</span></a></div>
                                <img src="${ajax_object.themeurl}/assets/images/up-new-iocn.png" class="new-tag" alt="up-new-iocn">
                            `;
                        }
                    } catch (error) {
                        console.error('创建桌面用户面板HTML失败:', error);
                        return;
                    }
                    
                    try {
                        navRightSection.appendChild(newWrapper);
                    } catch (error) {
                        console.warn('添加桌面端用户面板失败:', error);
                    }
                }
            }
        } catch (error) {
            console.error('渲染登录UI失败:', error);
        }
    };
    
    /**
     * 辅助函数：获取用户中心链接
     */
    const getUserCenterLink = () => {
        return typeof boxmoe_user_center_link_page === 'function' ? boxmoe_user_center_link_page() : '#';
    };
    
    /**
     * 辅助函数：获取登录链接
     */
    const getLoginLink = () => {
        return typeof boxmoe_sign_in_link_page === 'function' ? boxmoe_sign_in_link_page() : '#';
    };
    
    /**
     * 辅助函数：获取注册链接
     */
    const getRegisterLink = () => {
        return typeof boxmoe_sign_up_link_page === 'function' ? boxmoe_sign_up_link_page() : '#';
    };
    
    /**
     * 辅助函数：获取注销链接
     */
    const getLogoutUrl = () => {
        return typeof wp_logout_url === 'function' ? wp_logout_url(home_url()) : '#';
    };
    
    /**
     * 辅助函数：获取用户头像URL
     */
    const getUserAvatarUrl = (userId) => {
        return typeof boxmoe_get_avatar_url === 'function' ? boxmoe_get_avatar_url(userId, 100) : `${ajax_object.themeurl}/assets/images/avatar.png`;
    };
    
    /**
     * 辅助函数：检查是否为管理员
     */
    const isAdmin = () => {
        // 简单检查，实际应用中应通过服务器返回
        return false;
    };
    
    /**
     * 初始化登录状态管理
     */
    const init = () => {
        // 初始化时首先检查本地存储状态
        const storedStatus = getLoginStatusFromLocalStorage();
        if (storedStatus) {
            updateLoginUI(storedStatus.is_logged_in, storedStatus.user_info);
        }
        
        // 初始AJAX检查
        checkLoginStatus();
        
        // 定期检查
        setInterval(() => {
            checkLoginStatus();
        }, config.checkInterval);
        
        // 页面可见性变化时检查
        document.addEventListener('visibilitychange', () => {
            if (!document.hidden) {
                checkLoginStatus();
            }
        });
        
        // 监听网络状态变化
        window.addEventListener('online', () => {
            console.log('网络连接恢复，检查登录状态');
            checkLoginStatus();
        });
    };
    
    return {
        init,
        checkLoginStatus
    };
})();

// 点赞功能初始化
function initPostLikes() {
    document.querySelectorAll('.like-btn').forEach(btn => {
        const postId = btn.dataset.postId;
        if(localStorage.getItem(`post_${postId}_liked`)) {
            btn.classList.add('liked');
            btn.querySelector('i').classList.add('text-primary');
        }
        
        btn.addEventListener('click', async function(e) {
            e.preventDefault();
            
            if(this.classList.contains('processing') || localStorage.getItem(`post_${postId}_liked`)) {
                return;
            }
            
            this.classList.add('processing');
            
            try {
                const formData = new FormData();
                formData.append('action', 'post_like');
                formData.append('post_id', postId);
                
                const response = await fetch(ajax_object.ajaxurl, {
                    method: 'POST',
                    credentials: 'same-origin',
                    body: formData
                });
                
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                
                const data = await response.json();
                
                if (data.success) {
                    const count = data.data.count;
                    btn.querySelector('.like-count').textContent = count;
                    localStorage.setItem(`post_${postId}_liked`, 'true');
                    btn.classList.add('liked');
                    btn.querySelector('i').classList.add('text-primary');
                } else {
                    console.warn('点赞失败:', data.data.message);
                }
            } catch (error) {
                console.error('点赞请求失败:', error);
            } finally {
                this.classList.remove('processing');
            }
        });
    });
}

// 打赏功能初始化
function initReward() {
    const rewardBtn = document.querySelector('.reward-btn');
    const rewardModal = document.querySelector('.reward-modal');
    const rewardClose = document.querySelector('.reward-close');

    if (rewardBtn && rewardModal) {
        rewardBtn.addEventListener('click', () => {
            rewardModal.classList.add('show');
        });

        rewardModal.addEventListener('click', (e) => {
            if (e.target === rewardModal) {
                rewardModal.classList.remove('show');
            }
        });

        if (rewardClose) {
            rewardClose.addEventListener('click', () => {
                rewardModal.classList.remove('show');
            });
        }
    }
}

// 收藏功能初始化
function initPostFavorites() {
    document.querySelectorAll('.favorite-btn').forEach(btn => {
        const postId = btn.dataset.postId;
        
        btn.addEventListener('click', async function(e) {
            e.preventDefault();
            
            if(this.classList.contains('processing')) {
                return;
            }
            
            this.classList.add('processing');
            
            try {
                const formData = new FormData();
                formData.append('action', 'post_favorite');
                formData.append('post_id', postId);
                
                const response = await fetch(ajax_object.ajaxurl, {
                    method: 'POST',
                    credentials: 'same-origin',
                    body: formData
                });
                
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                
                const data = await response.json();
                
                if (data.success) {
                    const favoriteText = this.querySelector('.favorite-text');
                    if (data.data.status) {
                        this.classList.add('favorited');
                        favoriteText.textContent = '已收藏';
                    } else {
                        this.classList.remove('favorited');
                        favoriteText.textContent = '收藏';
                    }
                } else {
                    console.warn('收藏操作失败:', data.data.message);
                }
            } catch (error) {
                console.error('收藏请求失败:', error);
            } finally {
                this.classList.remove('processing');
            }
        });
    });
}

// 主题切换初始化
const ThemeSwitcher = (() => {
    "use strict";
    const getStoredTheme = () => localStorage.getItem("theme");
    const getPreferredTheme = () => {
        const storedTheme = getStoredTheme();
        return storedTheme || (window.matchMedia("(prefers-color-scheme: dark)").matches ? "dark" : "light");
    };
    const setTheme = theme => {
        const isAutoDark = theme === "auto" && window.matchMedia("(prefers-color-scheme: dark)").matches;
        document.documentElement.setAttribute("data-bs-theme", isAutoDark ? "dark" : theme);
    };
    const updateActiveState = (theme, focus = false) => {
        const themeSwitcher = document.querySelector(`[data-bs-theme-value="${theme}"]`);
        if (!themeSwitcher) return;

        document.querySelectorAll("[data-bs-theme-value]").forEach(btn => {
            btn.classList.toggle("active", btn === themeSwitcher);
            btn.setAttribute("aria-pressed", btn === themeSwitcher);
        });
        const mainThemeBtn = document.querySelector('.bd-theme i');
        if (mainThemeBtn) {
            mainThemeBtn.className = theme === 'light' ? 'fa fa-sun-o' :
                                   theme === 'dark' ? 'fa fa-moon-o' :
                                   'fa fa-adjust';
        }

        focus && themeSwitcher.focus();
    };
    const init = () => {
        const preferredTheme = getPreferredTheme();
        setTheme(preferredTheme);
        updateActiveState(preferredTheme);
        document.querySelectorAll("[data-bs-theme-value]").forEach(button => {
            button.addEventListener("click", () => {
                const theme = button.dataset.bsThemeValue;
                const current = document.documentElement.getAttribute("data-bs-theme") || "light";
                const nextEffective = theme === "auto" ? (window.matchMedia("(prefers-color-scheme: dark)").matches ? "dark" : "light") : theme;
                animateThemeToggle(button, current, nextEffective);
                localStorage.setItem("theme", theme);
                setTheme(theme);
                updateActiveState(theme, true);
            });
        });
        window.matchMedia("(prefers-color-scheme: dark)").addEventListener("change", e => {
            const storedTheme = getStoredTheme();
            storedTheme === "auto" && setTheme(getPreferredTheme());
        });
    };

    return { init };
})();

// 代码高亮初始化
function initPrettyPrint() {
    const prettyprintElements = document.querySelectorAll('.prettyprint');
    if (prettyprintElements.length && window.prettyPrint) {
        window.prettyPrint();
    }
}

function initCodeCopy() {
    const container = document.querySelector('.boxmoe-container');
    if (!container) return;
    const preElements = container.querySelectorAll('pre');
    preElements.forEach((pre, index) => {
        const btnCopy = document.createElement('div');
        btnCopy.className = 'btn-copy';
        const copySpan = document.createElement('span');
        copySpan.className = 'single-copy copy';
        copySpan.setAttribute('title', '点击复制本段代码');
        copySpan.innerHTML = '<i class="fa fa-files-o"></i> 复制代码';
        btnCopy.appendChild(copySpan);
        pre.insertBefore(btnCopy, pre.firstChild);
        const codeList = pre.querySelector('ol.linenums');
        if (codeList) {
            codeList.id = `copy${index}`;
            const lines = Array.from(codeList.querySelectorAll('li')).map(li => li.textContent.replace(/\u00A0/g, ''));
            const text = lines.join('\n').replace(/^\s*\n|\n\s*$/g, '');
            copySpan.setAttribute('data-clipboard-text', text);
        } else {
            const codeEl = pre.querySelector('code');
            const raw = codeEl ? (codeEl.textContent || '') : (pre.textContent || '');
            const btnText = btnCopy.textContent || '';
            const text = raw.replace(btnText, '').replace(/\u00A0/g, '').replace(/^\s*\n|\n\s*$/g, '');
            copySpan.setAttribute('data-clipboard-text', text);
        }
    });
    const clipboard = new ClipboardJS('.copy');
    clipboard.on('success', function(e) {
        e.clearSelection();
        const trigger = e.trigger;
        trigger.innerHTML = '<span style="color:#32cd32"><i class="fa fa-check-square-o" aria-hidden="true"></i> 复制成功</span>';     
        setTimeout(() => {
            trigger.innerHTML = '<i class="fa fa-files-o"></i> 复制代码';
        }, 3000);
        if (window._copyBannerShow) {
            try { window._copyBannerShow(); } catch(_) {}
        }
    });
    clipboard.on('error', function(e) {
        console.error('Action:', e.action);
        console.error('Trigger:', e.trigger);
        alert("复制失败，请手动复制");
    });
}

// Preloader初始化
function initPreloader() {
    const preloader = document.querySelector('.preloader');
    if (!preloader) return;
    preloader.style.display = 'flex';
    window.addEventListener('load', () => {
        setTimeout(() => {
            preloader.style.opacity = '0';
            setTimeout(() => {
                preloader.style.display = 'none';
            }, 500); 
        }, 1000);
    });
}

function initRunningDays() {
    const start = new Date(ajax_object.running_days);
    const daysEl = document.getElementById('runtime-days');
    const hoursEl = document.getElementById('runtime-hours');
    const minutesEl = document.getElementById('runtime-minutes');
    const secondsEl = document.getElementById('runtime-seconds');
    if (!daysEl || !hoursEl || !minutesEl || !secondsEl || !(start instanceof Date) || isNaN(start)) return;
    const update = () => {
        const now = new Date();
        let diff = now.getTime() - start.getTime();
        if (diff < 0) diff = 0;
        const days = Math.floor(diff / (1000 * 60 * 60 * 24));
        const hours = Math.floor((diff / (1000 * 60 * 60)) % 24);
        const minutes = Math.floor((diff / (1000 * 60)) % 60);
        const seconds = Math.floor((diff / 1000) % 60);
        daysEl.textContent = days;
        hoursEl.textContent = hours;
        minutesEl.textContent = minutes;
        secondsEl.textContent = seconds;
    };
    update();
    setInterval(update, 1000);
}

function animateThemeToggle(btn, cur, nxt){
    try{
        var vw = window.innerWidth||document.documentElement.clientWidth;
        var vh = window.innerHeight||document.documentElement.clientHeight;
        var r = btn && btn.getBoundingClientRect ? btn.getBoundingClientRect() : { left: vw/2, top: 60, width: 0, height: 0 };
        var cx = Math.round(r.left + r.width/2);
        var cy = Math.round(r.top + r.height/2);
        var dx = Math.max(cx, vw - cx);
        var dy = Math.max(cy, vh - cy);
        var radius = Math.ceil(Math.hypot(dx, dy));
        var prevBg = (function(){
            try{
                var cs = window.getComputedStyle(document.body);
                var bgFull = cs.getPropertyValue('background');
                var bgImg = cs.getPropertyValue('background-image');
                var bgCol = cs.getPropertyValue('background-color');
                var val = String(bgFull||'').trim();
                if (val) return val;
                if (String(bgImg||'').trim() && String(bgCol||'').trim()) return String(bgImg).trim() + ', ' + String(bgCol).trim();
                if (String(bgImg||'').trim()) return String(bgImg).trim();
                if (String(bgCol||'').trim()) return String(bgCol).trim();
            }catch(_){}
            try{
                var rs = window.getComputedStyle(document.documentElement);
                var varBg = rs.getPropertyValue('--ish-bg');
                if (varBg && String(varBg).trim()) return String(varBg).trim();
            }catch(_){}
            try{ var s2 = window.getComputedStyle(document.documentElement).backgroundColor; if (s2) return s2; }catch(_){}
            return cur==='dark' ? 'rgb(18, 18, 18)' : 'rgb(255, 255, 255)';
        })();
        var overlay = document.createElement('div');
        overlay.style.position = 'fixed';
        overlay.style.left = '0';
        overlay.style.top = '0';
        overlay.style.right = '0';
        overlay.style.bottom = '0';
        overlay.style.zIndex = '-1';
        overlay.style.pointerEvents = 'none';
        overlay.style.background = prevBg;
        overlay.style.willChange = 'clip-path';
        overlay.style.clipPath = 'circle('+radius+'px at '+cx+'px '+cy+'px)';
        overlay.style.transition = 'clip-path 520ms ease-in-out';
        if (document.body.firstChild) { document.body.insertBefore(overlay, document.body.firstChild); } else { document.body.appendChild(overlay); }
        requestAnimationFrame(function(){ overlay.style.clipPath = 'circle(0px at '+cx+'px '+cy+'px)'; });
        var cleanup = function(){ overlay.removeEventListener('transitionend', cleanup); if (overlay && overlay.parentNode) overlay.parentNode.removeChild(overlay); };
        overlay.addEventListener('transitionend', cleanup);
    }catch(_){}
}

// 📝 任务清单交互和自动保存功能 - Emoji版
function initTaskList() {
    const container = document.querySelector('.single-content');
    if (!container) return;
    
    console.log('初始化任务清单交互功能');
    
    // 获取文章ID的多种方式
    let postId = document.body.getAttribute('data-post-id');
    
    // 如果body没有data-post-id属性，尝试从URL中获取
    if (!postId) {
        // 匹配URL中的数字ID，支持多种URL格式
        const urlMatch = window.location.pathname.match(/\d+/);
        if (urlMatch) {
            postId = urlMatch[0];
        } else {
            // 尝试从当前页面的其他元素获取，比如文章编辑页面
            const editForm = document.querySelector('#post');
            if (editForm) {
                const postIdInput = editForm.querySelector('#post_ID');
                if (postIdInput) {
                    postId = postIdInput.value;
                }
            }
        }
    }
    
    // 前端本地切换任务状态
    const toggleTaskState = (taskItem) => {
        const currentStatus = taskItem.dataset.taskStatus;
        let newStatus = '';
        let newEmoji = '';
        
        // 根据当前状态计算下一个状态
        // 状态循环：pending → in-progress → completed → pending
        switch(currentStatus) {
            case 'pending':
                newStatus = 'in-progress';
                newEmoji = '📃';
                break;
            case 'in-progress':
                newStatus = 'completed';
                newEmoji = '✅';
                break;
            case 'completed':
                newStatus = 'pending';
                newEmoji = '❌';
                break;
            // default:
            //     newStatus = 'pending';
            //     newEmoji = '❌';
            //     break;
        }
        
        // 更新本地状态
        taskItem.dataset.taskStatus = newStatus;
        const emojiSpan = taskItem.querySelector('.md-task-emoji');
        emojiSpan.textContent = newEmoji;
        
        console.log('本地切换任务状态:', newStatus);
        
        // 自动保存任务状态到服务器
        saveTaskState(taskItem, currentStatus);
    };
    
    // 自动保存任务状态到服务器
    const saveTaskState = async (taskItem, currentStatus) => {
        const taskContent = taskItem.dataset.taskContent;
        
        try {
            // 确保post_id存在
            if (!postId) {
                console.error('更新任务状态失败: 无法获取文章ID');
                return;
            }
            
            const formData = new FormData();
            formData.append('action', 'update_task_status');
            formData.append('post_id', postId);
            formData.append('task_content', taskContent);
            formData.append('current_status', currentStatus);
            
            // 确保ajax_object存在
            if (!window.ajax_object || !window.ajax_object.ajaxurl) {
                console.error('更新任务状态失败: 无法获取AJAX URL');
                return;
            }
            
            const response = await fetch(window.ajax_object.ajaxurl, {
                method: 'POST',
                credentials: 'same-origin',
                body: formData
            });
            
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            
            const data = await response.json();
            
            if (data.success) {
                console.log('任务状态保存成功，新状态:', data.data.new_status);
                // 如果服务器返回了新状态，使用服务器返回的状态
                if (data.data && data.data.new_status) {
                    taskItem.dataset.taskStatus = data.data.new_status;
                    const emojiSpan = taskItem.querySelector('.md-task-emoji');
                    let newEmoji = '';
                    switch(data.data.new_status) {
                        case 'pending':
                            newEmoji = '❌';
                            break;
                        case 'in-progress':
                            newEmoji = '📃';
                            break;
                        case 'completed':
                            newEmoji = '✅';
                            break;
                    }
                    emojiSpan.textContent = newEmoji;
                }
            } else {
                console.warn('更新任务状态失败:', data.data.message);
                // 恢复原状态
                // 这里可以根据需要添加恢复逻辑
            }
        } catch (error) {
            console.error('更新任务状态请求失败:', error);
        }
    };
    
    // 任务项点击处理函数
    const handleTaskItemClick = function(e) {
        e.preventDefault();
        e.stopPropagation();
        
        console.log('任务项点击事件触发:', e.target);
        
        // 查找最近的任务项
        const taskItem = this.closest('.md-task-item');
        if (!taskItem) {
            console.log('未找到任务项');
            return;
        }
        
        console.log('找到任务项:', taskItem);
        
        // 检查是否为可交互任务项
        if (!taskItem.classList.contains('md-task-item-interactive')) {
            console.log('任务项不可交互');
            return;
        }
        
        console.log('任务项可交互，切换状态');
        toggleTaskState(taskItem);
    };
    
    // 直接绑定点击事件到所有任务项
    const taskItems = document.querySelectorAll('.md-task-item');
    console.log('找到任务项数量:', taskItems.length);
    
    taskItems.forEach(taskItem => {
        // 绑定任务项点击事件
        taskItem.addEventListener('click', handleTaskItemClick);
        
        // 绑定emoji点击事件
        const emojiSpan = taskItem.querySelector('.md-task-emoji');
        if (emojiSpan) {
            emojiSpan.addEventListener('click', handleTaskItemClick);
        }
        
        // 绑定任务文本点击事件
        const taskText = taskItem.querySelector('.md-task-text');
        if (taskText) {
            taskText.addEventListener('click', handleTaskItemClick);
        }
    });
}

// 🎬 视频播放器初始化
function initVideoPlayer() {
    const videos = document.querySelectorAll('.single-content video');
    if (!videos.length) return;

    videos.forEach(video => {
        // 检查是否已经初始化
        if (video.dataset.videoInitialized) return;
        video.dataset.videoInitialized = 'true';

        // 创建视频容器
        const container = document.createElement('div');
        container.className = 'video-container';
        video.parentNode.insertBefore(container, video);
        container.appendChild(video);

        // 创建播放按钮
        const playBtn = document.createElement('button');
        playBtn.className = 'play-btn';
        playBtn.innerHTML = '<i class="fa fa-play"></i>';
        container.appendChild(playBtn);

        // 创建控制栏
        const controls = document.createElement('div');
        controls.className = 'video-controls';
        container.appendChild(controls);

        // 创建进度条容器
        const progressContainer = document.createElement('div');
        progressContainer.className = 'progress-container';
        controls.appendChild(progressContainer);

        // 创建进度条
        const progressBar = document.createElement('div');
        progressBar.className = 'progress-bar';
        progressContainer.appendChild(progressBar);

        // 创建看板娘元素
        const knb = document.createElement('div');
        knb.className = 'progress-knb';
        progressContainer.appendChild(knb);

        // 创建控制按钮组
        const btnGroup = document.createElement('div');
        btnGroup.className = 'video-btn-group';
        controls.appendChild(btnGroup);

        // 创建播放时间
        const timeDisplay = document.createElement('div');
        timeDisplay.className = 'video-time';
        timeDisplay.textContent = '00:00 / 00:00';
        btnGroup.appendChild(timeDisplay);

        // 创建播放/暂停按钮
        const toggleBtn = document.createElement('button');
        toggleBtn.className = 'video-btn';
        toggleBtn.innerHTML = '<i class="fa fa-play"></i>';
        btnGroup.appendChild(toggleBtn);

        // 创建音量控制
        const volumeControl = document.createElement('div');
        volumeControl.className = 'volume-control';
        btnGroup.appendChild(volumeControl);

        // 创建音量按钮
        const volumeBtn = document.createElement('button');
        volumeBtn.className = 'video-btn';
        volumeBtn.innerHTML = '<i class="fa fa-volume-up"></i>';
        volumeControl.appendChild(volumeBtn);

        // 创建音量滑块
        const volumeSlider = document.createElement('input');
        volumeSlider.className = 'volume-slider';
        volumeSlider.type = 'range';
        volumeSlider.min = '0';
        volumeSlider.max = '1';
        volumeSlider.step = '0.1';
        volumeSlider.value = '1';
        volumeControl.appendChild(volumeSlider);
        
        // 🎯 创建倍速播放按钮
        const speedBtn = document.createElement('button');
        speedBtn.className = 'video-btn speed-btn';
        speedBtn.innerHTML = '<span>1.0x</span>';
        btnGroup.appendChild(speedBtn);
        
        // 🎯 创建倍速选择菜单
        const speedMenu = document.createElement('div');
        speedMenu.className = 'speed-menu';
        speedMenu.innerHTML = `
            <div class="speed-option" data-speed="0.5">0.5x</div>
            <div class="speed-option" data-speed="0.75">0.75x</div>
            <div class="speed-option active" data-speed="1">1.0x</div>
            <div class="speed-option" data-speed="1.25">1.25x</div>
            <div class="speed-option" data-speed="1.5">1.5x</div>
            <div class="speed-option" data-speed="1.75">1.75x</div>
            <div class="speed-option" data-speed="2">2.0x</div>
            <div class="speed-option" data-speed="2.5">2.5x</div>
            <div class="speed-option" data-speed="3">3.0x</div>
        `;
        controls.appendChild(speedMenu);

        // 创建网页全屏按钮
        const webFullscreenBtn = document.createElement('button');
        webFullscreenBtn.className = 'video-btn web-fullscreen-btn';
        webFullscreenBtn.innerHTML = '<i class="fa fa-arrows-alt"></i>';
        webFullscreenBtn.title = '网页全屏';
        btnGroup.appendChild(webFullscreenBtn);

        // 创建画中画按钮
        const pipBtn = document.createElement('button');
        pipBtn.className = 'video-btn pip-btn';
        pipBtn.innerHTML = '<i class="fa fa-clone"></i>';
        pipBtn.title = '画中画';
        btnGroup.appendChild(pipBtn);

        // 创建镜像画面按钮
        const mirrorBtn = document.createElement('button');
        mirrorBtn.className = 'video-btn mirror-btn';
        mirrorBtn.innerHTML = '<i class="fa fa-refresh"></i>';
        mirrorBtn.title = '镜像画面';
        btnGroup.appendChild(mirrorBtn);

        // 创建全屏按钮
        const fullscreenBtn = document.createElement('button');
        fullscreenBtn.className = 'video-btn fullscreen-btn';
        fullscreenBtn.innerHTML = '<i class="fa fa-expand"></i>';
        fullscreenBtn.title = '全屏';
        btnGroup.appendChild(fullscreenBtn);

        // 🎬 更新播放时间
        function updateTime() {
            const current = formatTime(video.currentTime);
            const duration = formatTime(video.duration);
            timeDisplay.textContent = `${current} / ${duration}`;
        }

        // 🎬 格式化时间
        function formatTime(seconds) {
            if (isNaN(seconds)) return '00:00';
            const mins = Math.floor(seconds / 60);
            const secs = Math.floor(seconds % 60);
            return `${mins.toString().padStart(2, '0')}:${secs.toString().padStart(2, '0')}`;
        }

        // 🎬 更新进度条
        function updateProgress() {
            if (isNaN(video.duration)) return;
            const progress = (video.currentTime / video.duration) * 100;
            // 移除CSS过渡效果，提高更新流畅度
            progressBar.style.transition = 'none';
            knb.style.transition = 'none';
            progressBar.style.width = `${progress}%`;
            
            // 更新看板娘位置
            knb.style.left = `${progress}%`;
        }
        
        // 🎬 优化进度条更新机制，使用requestAnimationFrame提高流畅度
        let animationFrameId = null;
        function smoothUpdateProgress() {
            updateProgress();
            animationFrameId = requestAnimationFrame(smoothUpdateProgress);
        }
        
        // 🎬 开始流畅更新进度条
        function startSmoothProgress() {
            if (!animationFrameId) {
                smoothUpdateProgress();
            }
        }
        
        // 🎬 停止流畅更新进度条
        function stopSmoothProgress() {
            if (animationFrameId) {
                cancelAnimationFrame(animationFrameId);
                animationFrameId = null;
            }
        }

        // 🎬 播放/暂停切换
        function togglePlay() {
            if (video.paused || video.ended) {
                // 视频结束后重置时间
                if (video.ended) {
                    video.currentTime = 0;
                }
                // 播放视频并处理可能的错误
                video.play().catch(err => {
                    console.error(`Error attempting to play video: ${err.message}`);
                });
                toggleBtn.innerHTML = '<i class="fa fa-pause"></i>';
                playBtn.innerHTML = '<i class="fa fa-pause"></i>';
            } else {
                video.pause();
                toggleBtn.innerHTML = '<i class="fa fa-play"></i>';
                playBtn.innerHTML = '<i class="fa fa-play"></i>';
            }
        }

        // 🎬 点击进度条跳转
        progressContainer.addEventListener('click', (e) => {
            const rect = progressContainer.getBoundingClientRect();
            const x = e.clientX - rect.left;
            const width = rect.width;
            const percent = x / width;
            video.currentTime = percent * video.duration;
        });

        // 🎬 音量控制
        volumeSlider.addEventListener('input', () => {
            video.volume = volumeSlider.value;
            if (video.volume === 0) {
                volumeBtn.innerHTML = '<i class="fa fa-volume-off"></i>';
            } else if (video.volume < 0.5) {
                volumeBtn.innerHTML = '<i class="fa fa-volume-down"></i>';
            } else {
                volumeBtn.innerHTML = '<i class="fa fa-volume-up"></i>';
            }
        });

        // 🎬 音量按钮切换静音
        volumeBtn.addEventListener('click', () => {
            if (video.volume > 0) {
                volumeSlider.value = 0;
                video.volume = 0;
                volumeBtn.innerHTML = '<i class="fa fa-volume-off"></i>';
            } else {
                volumeSlider.value = 1;
                video.volume = 1;
                volumeBtn.innerHTML = '<i class="fa fa-volume-up"></i>';
            }
        });

        // 🎬 网页全屏功能 - 修复版本，改为占满浏览器视口
        let isWebFullscreen = false;
        webFullscreenBtn.addEventListener('click', () => {
            isWebFullscreen = !isWebFullscreen;
            const body = document.body;
            const html = document.documentElement;
            
            if (isWebFullscreen) {
                // 进入网页全屏模式（占满视口）
                // 保存原始样式
                body.dataset.originalOverflow = body.style.overflow;
                html.dataset.originalOverflow = html.style.overflow;
                body.dataset.originalMargin = body.style.margin;
                html.dataset.originalMargin = html.style.margin;
                
                // 设置为占满视口
                body.style.overflow = 'hidden';
                html.style.overflow = 'hidden';
                body.style.margin = '0';
                html.style.margin = '0';
                
                // 更新按钮图标
                webFullscreenBtn.innerHTML = '<i class="fa fa-compress"></i>';
            } else {
                // 退出网页全屏模式
                // 恢复原始样式
                body.style.overflow = body.dataset.originalOverflow || '';
                html.style.overflow = html.dataset.originalOverflow || '';
                body.style.margin = body.dataset.originalMargin || '';
                html.style.margin = html.dataset.originalMargin || '';
                
                // 清除自定义数据属性
                delete body.dataset.originalOverflow;
                delete html.dataset.originalOverflow;
                delete body.dataset.originalMargin;
                delete html.dataset.originalMargin;
                
                // 更新按钮图标
                webFullscreenBtn.innerHTML = '<i class="fa fa-arrows-alt"></i>';
            }
        });
        
        // 🎬 画中画功能
        pipBtn.addEventListener('click', () => {
            if (document.pictureInPictureElement) {
                // 退出画中画
                document.exitPictureInPicture().catch(err => {
                    console.error(`Error attempting to exit picture-in-picture: ${err.message}`);
                });
            } else {
                // 进入画中画
                if (video.requestPictureInPicture) {
                    video.requestPictureInPicture().catch(err => {
                        console.error(`Error attempting to enable picture-in-picture: ${err.message}`);
                    });
                }
            }
        });
        
        // 🎬 监听画中画状态变化
        video.addEventListener('enterpictureinpicture', () => {
            pipBtn.innerHTML = '<i class="fa fa-times"></i>';
        });
        
        video.addEventListener('leavepictureinpicture', () => {
            pipBtn.innerHTML = '<i class="fa fa-clone"></i>';
        });
        
        // 🎬 镜像画面功能
        let isMirrored = false;
        mirrorBtn.addEventListener('click', () => {
            isMirrored = !isMirrored;
            if (isMirrored) {
                video.style.transform = 'scaleX(-1)';
                mirrorBtn.innerHTML = '<i class="fa fa-check"></i>';
            } else {
                video.style.transform = '';
                mirrorBtn.innerHTML = '<i class="fa fa-refresh"></i>';
            }
        });

        // 🎬 全屏切换 - 修复版本
        fullscreenBtn.addEventListener('click', () => {
            // 使用容器元素进行全屏，确保菜单可见
            const targetElement = container;
            
            // 处理不同浏览器的全屏API兼容性
            const fullscreenApi = {
                request: targetElement.requestFullscreen || 
                         targetElement.webkitRequestFullscreen || 
                         targetElement.mozRequestFullScreen || 
                         targetElement.msRequestFullscreen,
                exit: document.exitFullscreen || 
                      document.webkitExitFullscreen || 
                      document.mozCancelFullScreen || 
                      document.msExitFullscreen,
                element: document.fullscreenElement || 
                         document.webkitFullscreenElement || 
                         document.mozFullScreenElement || 
                         document.msFullscreenElement
            };
            
            if (!fullscreenApi.element) {
                // 进入全屏
                if (fullscreenApi.request) {
                    fullscreenApi.request.call(targetElement).catch(err => {
                        console.error(`Error attempting to enable fullscreen: ${err.message}`);
                    });
                }
            } else {
                // 退出全屏
                if (fullscreenApi.exit) {
                    fullscreenApi.exit.call(document);
                }
            }
        });
        
        // 🎬 全屏状态变化 - 修复版本
        function handleFullscreenChange() {
            const fullscreenApi = {
                element: document.fullscreenElement || 
                         document.webkitFullscreenElement || 
                         document.mozFullScreenElement || 
                         document.msFullscreenElement
            };
            
            if (fullscreenApi.element) {
                // 更新普通全屏按钮状态
                fullscreenBtn.innerHTML = '<i class="fa fa-compress"></i>';
                
                // 全屏时添加特殊样式
                container.classList.add('fullscreen');
                // 确保视频在全屏容器中占满空间
                video.style.width = '100%';
                video.style.height = '100%';
                // 确保控制栏可见
                controls.style.opacity = '1';
                controls.style.transform = 'translateY(0)';
            } else {
                // 更新普通全屏按钮状态
                fullscreenBtn.innerHTML = '<i class="fa fa-expand"></i>';
                
                // 退出全屏时移除特殊样式
                container.classList.remove('fullscreen');
                // 恢复视频原始尺寸
                video.style.width = '';
                video.style.height = '';
                // 恢复控制栏的悬停显示效果
                controls.style.opacity = '';
                controls.style.transform = '';
            }
        }
        
        // 添加多浏览器兼容的全屏事件监听
        document.addEventListener('fullscreenchange', handleFullscreenChange);
        document.addEventListener('webkitfullscreenchange', handleFullscreenChange);
        document.addEventListener('mozfullscreenchange', handleFullscreenChange);
        document.addEventListener('MSFullscreenChange', handleFullscreenChange);
        
        // 🎯 倍速菜单交互逻辑
        let isSpeedMenuOpen = false;
        
        // 🎯 切换倍速菜单显示状态
        speedBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            isSpeedMenuOpen = !isSpeedMenuOpen;
            
            if (isSpeedMenuOpen) {
                // 计算倍速按钮的位置，将菜单定位在按钮正上方
                const btnRect = speedBtn.getBoundingClientRect();
                const controlRect = controls.getBoundingClientRect();
                
                // 计算菜单位置：水平居中于按钮，垂直在按钮上方
                const left = btnRect.left - controlRect.left + btnRect.width / 2 - speedMenu.offsetWidth / 2;
                const bottom = controls.offsetHeight - btnRect.top + controlRect.top + btnRect.height + 10;
                
                // 设置菜单位置
                speedMenu.style.left = `${left}px`;
                speedMenu.style.right = 'auto';
                speedMenu.style.bottom = `${bottom}px`;
            }
            
            speedMenu.classList.toggle('show', isSpeedMenuOpen);
        });
        
        // 🎯 倍速选项点击事件
        speedMenu.addEventListener('click', (e) => {
            if (e.target.classList.contains('speed-option')) {
                const speed = parseFloat(e.target.dataset.speed);
                
                // 更新视频播放速度
                video.playbackRate = speed;
                
                // 更新倍速按钮显示
                speedBtn.innerHTML = `<span>${speed}x</span>`;
                
                // 更新选中状态
                speedMenu.querySelectorAll('.speed-option').forEach(option => {
                    option.classList.remove('active');
                });
                e.target.classList.add('active');
                
                // 关闭菜单
                isSpeedMenuOpen = false;
                speedMenu.classList.remove('show');
            }
        });
        
        // 🎯 点击外部关闭倍速菜单
        document.addEventListener('click', (e) => {
            if (!speedBtn.contains(e.target) && !speedMenu.contains(e.target)) {
                isSpeedMenuOpen = false;
                speedMenu.classList.remove('show');
            }
        });

        // 🎬 全屏状态下的菜单自动隐藏逻辑
        let menuHideTimer = null;
        let isMenuVisible = true;
        
        // 🎬 显示控制菜单
        function showControls() {
            if (!container.classList.contains('fullscreen')) return;
            
            clearTimeout(menuHideTimer);
            isMenuVisible = true;
            controls.style.opacity = '1';
            controls.style.transform = 'translateY(0)';
            
            // 显示播放按钮
            playBtn.style.opacity = '1';
            playBtn.style.transform = 'translate(-50%, -50%) scale(1)';
        }
        
        // 🎬 隐藏控制菜单
        function hideControls() {
            if (!container.classList.contains('fullscreen')) return;
            if (video.paused || video.ended) return;
            
            isMenuVisible = false;
            // 只改变透明度，不改变位置，确保点击区域不变
            controls.style.opacity = '0';
            controls.style.transform = 'translateY(0)';
            
            // 隐藏播放按钮
            playBtn.style.opacity = '0';
            playBtn.style.transform = 'translate(-50%, -50%) scale(1)';
        }
        
        // 🎬 延迟隐藏控制菜单
        function delayHideControls() {
            if (!container.classList.contains('fullscreen')) return;
            if (video.paused || video.ended) return;
            
            clearTimeout(menuHideTimer);
            menuHideTimer = setTimeout(hideControls, 3000); // 3秒后自动隐藏
        }
        
        // 🎬 事件监听
        // 使用requestAnimationFrame替代timeupdate事件，提高进度条流畅度
        video.addEventListener('play', () => {
            startSmoothProgress();
            // 播放开始后延迟隐藏菜单
            delayHideControls();
        });
        
        video.addEventListener('pause', () => {
            stopSmoothProgress();
            // 暂停时显示菜单
            showControls();
        });
        
        video.addEventListener('ended', () => {
            stopSmoothProgress();
            toggleBtn.innerHTML = '<i class="fa fa-play"></i>';
            playBtn.innerHTML = '<i class="fa fa-play"></i>';
            // 结束时显示菜单
            showControls();
        });
        
        video.addEventListener('timeupdate', updateTime);
        video.addEventListener('loadedmetadata', updateTime);
        
        // 页面离开时清理资源
        window.addEventListener('beforeunload', () => {
            stopSmoothProgress();
            clearTimeout(menuHideTimer);
        });
        
        // 🎬 鼠标移动事件 - 显示控制菜单
        container.addEventListener('mousemove', () => {
            if (container.classList.contains('fullscreen')) {
                showControls();
                delayHideControls();
            }
        });
        
        // 🎬 鼠标离开事件 - 隐藏控制菜单
        container.addEventListener('mouseleave', () => {
            if (container.classList.contains('fullscreen')) {
                hideControls();
            }
        });
        
        // 🎬 控制菜单交互时保持显示
        controls.addEventListener('mousemove', () => {
            if (container.classList.contains('fullscreen')) {
                showControls();
                delayHideControls();
            }
        });
        
        // 🎬 控制按钮点击时保持显示
        btnGroup.addEventListener('click', () => {
            if (container.classList.contains('fullscreen')) {
                showControls();
                delayHideControls();
            }
        });
        
        // 🎬 进度条交互时保持显示
        progressContainer.addEventListener('click', () => {
            if (container.classList.contains('fullscreen')) {
                showControls();
                delayHideControls();
            }
        });
        
        // 🎬 倍速菜单交互时保持显示
        speedMenu.addEventListener('click', () => {
            if (container.classList.contains('fullscreen')) {
                showControls();
                delayHideControls();
            }
        });

        // 🎬 点击视频播放/暂停
        container.addEventListener('click', (e) => {
            // 确保点击的不是控制按钮或播放按钮
            if (!e.target.closest('.video-controls') && 
                !e.target.closest('.video-btn') && 
                !e.target.closest('.play-btn')) {
                togglePlay();
            }
        });
        
        // 🎬 播放/暂停按钮点击事件
        toggleBtn.addEventListener('click', togglePlay);
        
        // 🎬 中间播放按钮点击事件
        playBtn.addEventListener('click', (e) => {
            e.stopPropagation(); // 阻止事件冒泡
            togglePlay();
        });
        
        // 🎬 控制栏事件委托 - 确保即使隐藏也能点击
        controls.addEventListener('click', (e) => {
            // 检查点击的是否是播放/暂停按钮或其子元素
            if (e.target.closest('.video-btn')) {
                // 显示控制菜单
                showControls();
                delayHideControls();
                // 如果点击的是播放/暂停按钮，触发播放/暂停
                if (e.target.closest('.video-btn') === toggleBtn || e.target.closest('.video-btn').querySelector('.fa-play, .fa-pause')) {
                    togglePlay();
                }
            }
        });
        
        // 🎬 确保视频可以交互
        video.style.pointerEvents = 'auto';
        // 只允许必要的事件，禁用原生控件
        video.controls = false;
        
        // 🎬 确保控制栏始终可点击
        controls.style.pointerEvents = 'auto';
        toggleBtn.style.pointerEvents = 'auto';

        // 🎬 加载看板娘图片
        function loadKnbImage() {
            // 检查是否开启了看板娘功能
            if (window.boxmoe_lolijump_switch === '1') {
                const knbImg = window.boxmoe_lolijump_img || 'lolisister1';
                let knbSrc = '';
                if (knbImg.startsWith('http') || knbImg.startsWith('//')) {
                    knbSrc = knbImg;
                } else {
                    knbSrc = `${ajax_object.themeurl}/assets/images/top/${knbImg}.gif`;
                }
                knb.style.backgroundImage = `url(${knbSrc})`;
            }
        }

        // 🎬 初始化看板娘
        loadKnbImage();
    });
}

// 🚀 回到顶部功能实现
function initBackToTop() {
    // 使用事件委托来确保即使元素动态生成也能正常工作
    document.addEventListener('click', function(e) {
        // 检查点击的是否是看板元素或其子元素
        const target = e.target.closest('#lolijump');
        if (target) {
            e.preventDefault();
            // 使用setTimeout确保事件冒泡完成
            setTimeout(() => {
                window.scrollTo({
                    top: 0,
                    behavior: 'smooth'
                });
            }, 0);
        }
    });
}

// DOM加载完成后初始化
document.addEventListener("DOMContentLoaded", () => {
    const run = fn => { try { fn(); } catch(_) {} };
    run(initPreloader);
    run(initSearchBox);
    run(initLazyLoad);
    run(initMobileUserPanel);
    run(initBannerImage);
    run(initStickyHeader);
    run(initTableOfContents);
    run(initTagColors);
    run(initHitokoto);
    run(initPostLikes);
    run(initReward);
    run(initPostFavorites);
    run(ThemeSwitcher.init);
    run(initPrettyPrint);
    run(initCodeCopy);
    run(initRunningDays);
    run(initTaskList);
    run(initVideoPlayer);
    run(initBackToTop);
    (function initGifFix(){
        try{
            const imgs = document.querySelectorAll('.single-content img');
            imgs.forEach(img => {
                const ds = img.dataset && img.dataset.src ? img.dataset.src : '';
                const src = img.getAttribute('src') || '';
                const target = (ds && /\.gif(\?.*)?$/i.test(ds)) ? ds : src;
                if (!target || !/\.gif(\?.*)?$/i.test(target)) return;
                let base = target; let query = '';
                const qm = base.match(/^(.*?)(\?.*)$/);
                if (qm) { base = qm[1]; query = qm[2]; }
                base = base.replace(/-\d+x\d+(?=\.gif$)/i, '');
                const fixed = base + query;
                if (img.hasAttribute('srcset')) img.removeAttribute('srcset');
                if (img.hasAttribute('sizes')) img.removeAttribute('sizes');
                if (img.classList.contains('lazy')) img.classList.remove('lazy');
                if (img.getAttribute('loading') === 'lazy') img.removeAttribute('loading');
                if (img.getAttribute('src') !== fixed) img.setAttribute('src', fixed);
            });
        }catch(_){}
    })();
    Fancybox.bind("[data-fancybox]", {});
    document.querySelectorAll('.switch-account-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            const guestInputs = document.querySelector('.guest-inputs');
            if(guestInputs) {
                guestInputs.classList.toggle('active');
                btn.classList.toggle('active');
            }
        });
    });
    (function initCopyNoticeBanner(){
        let banner = document.querySelector('.copy-banner');
        if (!banner) {
            banner = document.createElement('div');
            banner.className = 'copy-banner';
            banner.innerHTML = '<i class="fa fa-copy"></i> 您拷贝了本站内容，记得注释来源哦~(￣y▽,￣)╭ 👍🏻';
            document.body.appendChild(banner);
        }
        let timer = null;
        const show = function(){
            if (timer) { try { clearTimeout(timer); } catch(_) {} }
            banner.classList.remove('mask-run');
            void banner.offsetWidth;
            banner.classList.add('mask-run');
            banner.classList.add('show');
            timer = setTimeout(function(){
                banner.classList.remove('show');
                banner.classList.remove('mask-run');
            }, 1500);
        };
        window._copyBannerShow = show;
        document.addEventListener('copy', show);
    })();
});

// 🎨 主题切换动画效果
function animateThemeToggle(btn, cur, nxt){
    try{
        var vw = window.innerWidth||document.documentElement.clientWidth;
        var vh = window.innerHeight||document.documentElement.clientHeight;
        var r = btn && btn.getBoundingClientRect ? btn.getBoundingClientRect() : { left: vw/2, top: 60, width: 0, height: 0 };
        var cx = Math.round(r.left + r.width/2);
        var cy = Math.round(r.top + r.height/2);
        var dx = Math.max(cx, vw - cx);
        var dy = Math.max(cy, vh - cy);
        var radius = Math.ceil(Math.hypot(dx, dy));
        var prevBg = (function(){
            try{
                var cs = window.getComputedStyle(document.body);
                var bgFull = cs.getPropertyValue('background');
                var bgImg = cs.getPropertyValue('background-image');
                var bgCol = cs.getPropertyValue('background-color');
                var val = String(bgFull||'').trim();
                if (val) return val;
                if (String(bgImg||'').trim() && String(bgCol||'').trim()) return String(bgImg).trim() + ', ' + String(bgCol).trim();
                if (String(bgImg||'').trim()) return String(bgImg).trim();
                if (String(bgCol||'').trim()) return String(bgCol).trim();
            }catch(_){}
            try{
                var rs = window.getComputedStyle(document.documentElement);
                var varBg = rs.getPropertyValue('--ish-bg');
                if (varBg && String(varBg).trim()) return String(varBg).trim();
            }catch(_){}
            try{ var s2 = window.getComputedStyle(document.documentElement).backgroundColor; if (s2) return s2; }catch(_){}
            return cur==='dark' ? 'rgb(18, 18, 18)' : 'rgb(255, 255, 255)';
        })();
        var overlay = document.createElement('div');
        overlay.style.position = 'fixed';
        overlay.style.left = '0';
        overlay.style.top = '0';
        overlay.style.right = '0';
        overlay.style.bottom = '0';
        overlay.style.zIndex = '-1';
        overlay.style.pointerEvents = 'none';
        overlay.style.background = prevBg;
        overlay.style.willChange = 'clip-path';
        overlay.style.clipPath = 'circle('+radius+'px at '+cx+'px '+cy+'px)';
        overlay.style.transition = 'clip-path 520ms ease-in-out';
        if (document.body.firstChild) { document.body.insertBefore(overlay, document.body.firstChild); } else { document.body.appendChild(overlay); }
        requestAnimationFrame(function(){ overlay.style.clipPath = 'circle(0px at '+cx+'px '+cy+'px)'; });
        var cleanup = function(){ overlay.removeEventListener('transitionend', cleanup); if (overlay && overlay.parentNode) overlay.parentNode.removeChild(overlay); };
        overlay.addEventListener('transitionend', cleanup);
    }catch(_){}
}

// 🌈 Banner打字动画效果
document.addEventListener('DOMContentLoaded', function() {
    const target = document.querySelector('.boxmoe-typing-animation'); // ⬅️ 获取打字动画容器
    if (!target) return;

    const text = target.getAttribute('data-text'); // ⬅️ 获取要显示的文字
    if (!text) return;

    // 🎨 彩虹配色方案
    const colors = [
        '#FF0000', '#FF7F00', '#FFFF00', '#00FF00', '#0000FF', '#4B0082', '#9400D3',
        '#FF1493', '#00CED1', '#FFD700', '#ADFF2F', '#FF69B4'
    ];

    let isDeleting = false;
    let charIndex = 0;
    let lastColor = '';

    // 🎲 获取随机颜色，避免与上一个颜色相同
    function getRandomColor() {
        let newColor;
        do {
            newColor = colors[Math.floor(Math.random() * colors.length)];
        } while (newColor === lastColor);
        lastColor = newColor;
        return newColor;
    }

    // ⌨️ 打字动画逻辑
    function type() {
        if (isDeleting) {
            // 删除逻辑
            if (charIndex > 0) {
                const spans = target.querySelectorAll('span');
                if (spans.length > 0) {
                    spans[spans.length - 1].remove();
                }
                charIndex--;
                setTimeout(type, 100); 
            } else {
                isDeleting = false;
                setTimeout(type, 500);
            }
        } else {
            // 输入逻辑
            if (charIndex < text.length) {
                const span = document.createElement('span');
                span.textContent = text.charAt(charIndex);
                span.style.color = getRandomColor();
                target.appendChild(span);
                charIndex++;
                setTimeout(type, 200);
            } else {
                // 完成输入，等待3秒
                isDeleting = true;
                setTimeout(type, 3000); 
            }
        }
    }

    type(); // ⬅️ 启动动画
});

// 🔐 初始化登录状态管理
document.addEventListener('DOMContentLoaded', function() {
    if (typeof LoginStatusManager !== 'undefined') {
        LoginStatusManager.init();
    }
});

