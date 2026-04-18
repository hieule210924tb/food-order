<?php include(__DIR__ . '/../config/constants.php');?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <!-- Important to make website responsive -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>WowFood - Food Delivery</title>

    <!-- Link our CSS file - dùng SITEURL để CSS/ảnh luôn đúng khi mở từ mọi đường dẫn (trang con user/, ...) -->
    <link rel="stylesheet" href="<?php echo SITEURL; ?>css/style.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
</head>
<style>
.menu ul a .bi, .mobile-menu-panel a .bi { margin-right: 6px; opacity: 0.9; }
.mobile-menu-btn .bi { font-size: 1.5rem; }
.food-search {
    background-image: url('<?php echo SITEURL; ?>image/bg.jpg');
    background-size: cover;
    background-repeat: no-repeat;
    background-position: center;
    padding: 7% 0;
}

/* Mobile menu styles */
.mobile-menu-btn { display: none; border: 0; background: transparent; font-size: 28px; cursor: pointer; padding: 8px; position: absolute; right: 12px; top: 50%; transform: translateY(-50%); z-index: 1101; }
.mobile-menu-panel { position: fixed; left: 0; right: 0; top: 79px; bottom: 0; background: #fff; z-index: 1200; transform: translateX(100%); transition: transform 280ms ease; overflow-y: auto; -webkit-overflow-scrolling: touch; box-shadow: -2px 0 8px rgba(0,0,0,0.12); }
.mobile-menu-panel.open { transform: translateX(0); }
.mobile-menu-panel .panel-inner { padding: 16px; }
.mobile-menu-panel .panel-close { display: none; }
.mobile-menu-panel .panel-search { margin-bottom: 16px; }
.mobile-menu-panel .panel-search input { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 6px; box-sizing: border-box; }
.mobile-menu-panel .panel-search button { width: 100%; padding: 10px 12px; margin-top: 8px; border-radius: 6px; background: #ff6b81; color: #fff; border: 0; cursor: pointer; }
.mobile-menu-panel ul { list-style: none; padding: 0; margin: 0; }
.mobile-menu-panel li { padding: 12px 8px; border-bottom: 1px solid #f0f0f0; }
.mobile-menu-panel a { color: #333; text-decoration: none; display: block; }
/* Chỉ mobile: nền bg.jpg, ẩn logo, icon trắng */
@media (max-width: 500px) {
    .menu { display: none; }
    .mobile-menu-btn { display: inline-block; }
    .food-search form { display: none !important; }
    .navbar { background-color: transparent !important; border: none !important; box-shadow: none !important; background-image: url('<?php echo SITEURL; ?>image/bg.jpg') !important; background-size: cover !important; background-position: center !important; }
    .navbar .logo { display: none; }
    .navbar .mobile-menu-btn { color: #fff; text-shadow: 0 1px 3px rgba(0,0,0,0.5); }
}
</style>

<body>
    <!-- Navbar Section Starts Here -->
    <section class="navbar"
        style="position: fixed;top: 0;left: 0;width: 100%;background-color: white;z-index: 1000;border-bottom: 1px solid #eee; box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);height: 79px;">
        <div class="container">
            <div class="logo">
                <a href="<?php echo SITEURL; ?>" title="WowFood - Food Delivery">
                    <img src="<?php echo SITEURL; ?>image/logo.png" alt="WowFood Logo" class="img-responsive">
                </a>
            </div>
            <!-- Mobile menu toggle -->
            <button id="mobileMenuBtn" class="mobile-menu-btn" aria-label="Open menu"><i class="bi bi-list"></i></button>

            <div class="menu text-right">
                <ul>
                    <li>
                        <a href="<?php echo SITEURL ;?>"><i class="bi bi-house-door"></i> Trang chủ</a>
                    </li>
                    <li>
                        <a href="<?php echo SITEURL ;?>categories.php"><i class="bi bi-grid"></i> Danh mục</a>
                    </li>
                    <li>
                        <a href="<?php echo SITEURL ;?>food.php"><i class="bi bi-egg-fried"></i> Món ăn</a>
                    </li>
                    <li>
                        <a href="<?php echo SITEURL; ?>user/cart.php" style="position: relative;">
                            <i class="bi bi-cart3"></i> Giỏ hàng
                            <span id="cartBadge" class="chat-badge" style="display: none;">0</span>
                        </a>
                    </li>
                    <?php
                    if(isset($_SESSION['user'])){
                        $display_name = isset($_SESSION['user_full_name']) ? $_SESSION['user_full_name'] : $_SESSION['user'];
                        ?>
                    <?php if(isset($_SESSION['user_id'])): ?>
                    <li>
                        <a href="<?php echo SITEURL; ?>user/order-history.php"><i class="bi bi-box-seam"></i> Đơn hàng</a>
                    </li>
                    <li>
                        <a href="<?php echo SITEURL; ?>user/voucher.php"><i class="bi bi-ticket-perforated"></i> Voucher</a>
                    </li>
                    <li>
                        <a href="<?php echo SITEURL; ?>user/notifications.php" style="position: relative;">
                            <i class="bi bi-bell"></i> Thông báo
                            <span id="orderNotifBadge" class="chat-badge" style="display: none;">0</span>
                        </a>
                    </li>
                    <li>
                        <a href="<?php echo SITEURL; ?>user/chat.php" id="chatLink" style="position: relative;">
                            <i class="bi bi-chat-dots"></i> Chat
                            <span id="chatBadge" class="chat-badge" style="display: none;">0</span>
                        </a>
                    </li>
                    <?php endif; ?>
                    <li>
                        <a href="#" onclick="confirmLogout('<?php echo SITEURL; ?>user/logout.php'); return false;"><i class="bi bi-box-arrow-right"></i> Đăng
                            xuất (<?php echo htmlspecialchars($display_name); ?>)</a>
                    </li>
                    <?php
                    }
                    else{
                        ?>
                    <li>
                        <a href="<?php echo SITEURL ;?>user/login.php"><i class="bi bi-box-arrow-in-right"></i> Đăng nhập</a>
                    </li>
                    <li>
                        <a href="<?php echo SITEURL ;?>user/register.php"><i class="bi bi-person-plus"></i> Đăng ký</a>
                    </li>
                    <?php
                    }
                    ?>
                    <?php
                    // Chỉ hiển thị link Admin nếu:
                    // 1. Chưa đăng nhập, hoặc
                    // 2. Đã đăng nhập bằng tài khoản admin (có admin_id)
                    // Không hiển thị nếu đã đăng nhập bằng tài khoản user thường (có user_id nhưng không có admin_id)
                    if(!isset($_SESSION['user']) || isset($_SESSION['admin_id'])){
                        ?>
                    <li>
                        <a href="<?php echo SITEURL ;?>admin/login.php"><i class="bi bi-shield-lock"></i> Admin</a>
                    </li>
                    <?php
                    }
                    ?>
                </ul>
            </div>

            <!-- Mobile scroll-in panel -->
            <div id="mobileMenuPanel" class="mobile-menu-panel" aria-hidden="true">
                <div class="panel-inner">
                    <div class="panel-search">
                        <form action="<?php echo SITEURL; ?>food-search.php" method="GET">
                            <input type="search" name="search" placeholder="Tìm món, danh mục...">
                            <button type="submit">Tìm kiếm</button>
                        </form>
                    </div>
                    <ul>
                        <li><a href="<?php echo SITEURL ;?>"><i class="bi bi-house-door"></i> Trang chủ</a></li>
                        <li><a href="<?php echo SITEURL ;?>categories.php"><i class="bi bi-grid"></i> Danh mục</a></li>
                        <li><a href="<?php echo SITEURL ;?>food.php"><i class="bi bi-egg-fried"></i> Món ăn</a></li>
                        <li><a href="<?php echo SITEURL; ?>user/cart.php"><i class="bi bi-cart3"></i> Giỏ hàng <span id="cartBadgeMobile" class="chat-badge" style="display:none;float:right;margin-top:-2px;">0</span></a></li>
                        <?php if(isset($_SESSION['user'])) { $display_name = isset($_SESSION['user_full_name']) ? $_SESSION['user_full_name'] : $_SESSION['user']; ?>
                        <?php if(isset($_SESSION['user_id'])): ?>
                        <li><a href="<?php echo SITEURL; ?>user/order-history.php"><i class="bi bi-box-seam"></i> Đơn hàng</a></li>
                        <li><a href="<?php echo SITEURL; ?>user/voucher.php"><i class="bi bi-ticket-perforated"></i> Voucher</a></li>
                        <li><a href="<?php echo SITEURL; ?>user/notifications.php"><i class="bi bi-bell"></i> Thông báo <span id="orderNotifBadgeMobile" class="chat-badge" style="display:none;float:right;margin-top:-2px;">0</span></a></li>
                        <li><a href="<?php echo SITEURL; ?>user/chat.php" id="chatLinkMobile"><i class="bi bi-chat-dots"></i> Chat <span id="chatBadgeMobile" class="chat-badge" style="display:none;float:right;margin-top:-2px;">0</span></a></li>
                        <?php endif; ?>
                        <li><a href="#" onclick="confirmLogout('<?php echo SITEURL; ?>user/logout.php'); return false;"><i class="bi bi-box-arrow-right"></i> Đăng xuất (<?php echo htmlspecialchars($display_name); ?>)</a></li>
                        <?php } else { ?>
                        <li><a href="<?php echo SITEURL ;?>user/login.php"><i class="bi bi-box-arrow-in-right"></i> Đăng nhập</a></li>
                        <li><a href="<?php echo SITEURL ;?>user/register.php"><i class="bi bi-person-plus"></i> Đăng ký</a></li>
                        <?php } ?>
                        <?php if(!isset($_SESSION['user']) || isset($_SESSION['admin_id'])) { ?>
                        <li><a href="<?php echo SITEURL ;?>admin/login.php"><i class="bi bi-shield-lock"></i> Admin</a></li>
                        <?php } ?>
                    </ul>
                </div>
            </div>

            <div class="clearfix"></div>
        </div>
    </section>
    <!-- Navbar Section Ends Here -->

    <!-- SweetAlert2 for Logout Confirmation -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
    .chat-badge {
        position: absolute;
        top: -8px;
        right: -8px;
        background: linear-gradient(135deg, #ff6b81 0%, #ff4757 100%);
        color: white;
        border-radius: 50%;
        padding: 2px 6px;
        font-size: 11px;
        font-weight: bold;
        min-width: 18px;
        height: 18px;
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
        animation: pulse 2s infinite;
    }

    /* Tắt scroll cho SweetAlert2 */
    .swal2-no-scroll {
        overflow: hidden !important;
    }

    .swal2-popup.swal2-no-scroll {
        overflow-y: visible !important;
        max-height: none !important;
    }

    .swal2-html-container.swal2-no-scroll {
        overflow: visible !important;
        max-height: none !important;
    }

    @keyframes pulse {

        0%,
        100% {
            transform: scale(1);
        }

        50% {
            transform: scale(1.1);
        }
    }

    #chatLink { position: relative; display: inline-block; }
    .addcart-modal { text-align: left; }
    .addcart-row { margin-bottom: 16px; }
    .addcart-row label { display: block; margin-bottom: 6px; font-weight: bold; color: #2f3542; }
    .addcart-qty { display: flex; align-items: center; gap: 8px; }
    .addcart-qty button { width: 36px; height: 36px; border: 1px solid #ddd; background: #fff; border-radius: 6px; cursor: pointer; font-size: 1.1em; }
    .addcart-qty input { width: 60px; text-align: center; padding: 8px; border: 1px solid #ddd; border-radius: 6px; }
    .addcart-sizes { display: flex; flex-wrap: wrap; gap: 8px; }
    .swal-size-opt { padding: 8px 14px; border: 2px solid #ddd; border-radius: 8px; cursor: pointer; font-size: 0.9em; transition: all 0.2s; }
    .swal-size-opt:hover { border-color: #ff6b81; }
    .swal-size-opt.selected { background: #ff6b81; color: white; border-color: #ff6b81; }
    .addcart-sides { max-height: 140px; overflow-y: auto; }
    .addcart-collapse { margin-bottom: 12px; border: 1px solid #eee; border-radius: 8px; overflow: hidden; }
    .addcart-collapse-head { display: flex; align-items: center; gap: 8px; padding: 10px 12px; cursor: pointer; background: #f8f8f8; user-select: none; }
    .addcart-collapse-head:hover { background: #f0f0f0; }
    .addcart-collapse-head label { margin: 0; cursor: pointer; flex: 1; }
    .addcart-collapse-icon { font-size: 0.7em; transition: transform 0.2s; display: inline-block; }
    .addcart-collapse:not(.open) .addcart-collapse-icon { transform: rotate(-90deg); }
    .addcart-collapse-body { max-height: 180px; overflow: hidden; transition: max-height 0.25s ease; }
    .addcart-collapse-body > div { padding: 12px; }
    .addcart-collapse:not(.open) .addcart-collapse-body { max-height: 0 !important; }
    .addcart-collapse:not(.open) .addcart-collapse-body > div { padding-top: 0; padding-bottom: 0; }
    .swal-side-item { display: flex; align-items: center; gap: 8px; margin-bottom: 8px; }
    .swal-side-item input { width: 18px; height: 18px; cursor: pointer; }
    .swal-side-item label { margin: 0; font-weight: normal; cursor: pointer; flex: 1; }
    .addcart-total { margin-top: 16px; padding-top: 12px; border-top: 1px solid #eee; font-size: 1.1em; color: #ff6b81; }
    .addcart-row input[type="text"] { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 6px; box-sizing: border-box; }
    </style>
    <script>
    function confirmLogout(logoutUrl) {
        Swal.fire({
            title: 'Bạn có chắc chắn muốn đăng xuất?',
            text: 'Bạn sẽ phải đăng nhập lại để tiếp tục sử dụng',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Có, đăng xuất',
            cancelButtonText: 'Hủy'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = logoutUrl;
            }
        });
    }

    // Load và cập nhật số tin nhắn chưa đọc
    function updateChatBadge() {
        const chatBadge = document.getElementById('chatBadge');
        if (!chatBadge) return;

        fetch('<?php echo SITEURL; ?>api/get-unread-count.php')
            .then(response => response.text())
            .then(text => {
                try {
                    var data = text ? JSON.parse(text) : null;
                    if (data && data.success) {
                        var count = data.unread_count || 0;
                        if (count > 0) {
                            chatBadge.textContent = count > 99 ? '99+' : count;
                            chatBadge.style.display = 'flex';
                        } else {
                            chatBadge.style.display = 'none';
                        }
                    }
                } catch (e) { /* response not JSON, ignore */ }
            })
            .catch(function() {});
    }

    // Cập nhật badge khi trang load
    if (document.getElementById('chatBadge')) {
        updateChatBadge();
        setInterval(updateChatBadge, 5000);
    }

    function updateOrderNotifBadge() {
        var badge = document.getElementById('orderNotifBadge');
        if (!badge) return;
        fetch('<?php echo SITEURL; ?>api/get-order-notification-count.php')
            .then(response => response.text())
            .then(text => {
                try {
                    var data = text ? JSON.parse(text) : null;
                    if (data && data.success) {
                        var count = data.unread_count || 0;
                        if (count > 0) {
                            badge.textContent = count > 99 ? '99+' : count;
                            badge.style.display = 'flex';
                        } else {
                            badge.style.display = 'none';
                        }
                        var mobile = document.getElementById('orderNotifBadgeMobile');
                        if (mobile) { mobile.textContent = badge.textContent; mobile.style.display = badge.style.display; }
                    }
                } catch (e) {}
            })
            .catch(function() {});
    }
    if (document.getElementById('orderNotifBadge')) {
        updateOrderNotifBadge();
        setInterval(updateOrderNotifBadge, 5000);
    }

    // Hàm thêm vào giỏ hàng (foodId, foodPrice)
    let cartOptionsCache = null;
    async function addToCart(foodId, foodPrice = 0) {
        if (!cartOptionsCache) {
            try {
                const r = await fetch('<?php echo SITEURL; ?>api/get-options.php');
                const d = await r.json();
                cartOptionsCache = d.sizes && d.side_dishes ? d : { sizes: [{id:1,name:'Nhỏ',price_add:0},{id:2,name:'Vừa',price_add:5},{id:3,name:'Lớn',price_add:10}], side_dishes: [{id:1,name:'Trứng ốp la',price:8},{id:2,name:'Nem rán',price:10},{id:3,name:'Khoai tây chiên',price:12},{id:4,name:'Salad',price:6},{id:5,name:'Nước ngọt',price:5},{id:6,name:'Trà đá',price:3}] };
            } catch (e) {
                cartOptionsCache = { sizes: [{id:1,name:'Nhỏ',price_add:0},{id:2,name:'Vừa',price_add:5},{id:3,name:'Lớn',price_add:10}], side_dishes: [{id:1,name:'Trứng ốp la',price:8},{id:2,name:'Nem rán',price:10},{id:3,name:'Khoai tây chiên',price:12},{id:4,name:'Salad',price:6},{id:5,name:'Nước ngọt',price:5},{id:6,name:'Trà đá',price:3}] };
            }
        }
        const sizes = cartOptionsCache.sizes || [];
        const sides = cartOptionsCache.side_dishes || [];
        const fmt = (n) => new Intl.NumberFormat('vi-VN').format(n) + ' đ';
        const defaultSizeId = sizes[0] ? sizes[0].id : 1;
        let sizeId = defaultSizeId;
        let sideIds = [];
        const getSizePrice = () => (sizes.find(s=>s.id==sizeId)||{}).price_add || 0;
        const getSidePrice = () => sideIds.reduce((sum,id)=> sum + ((sides.find(s=>s.id==id)||{}).price||0), 0);
        const calcTotal = (qty) => (foodPrice + getSizePrice() + getSidePrice()) * (qty||1);

        const sizesHtml = sizes.map(s=>`<span class="swal-size-opt ${s.id==defaultSizeId?'selected':''}" data-id="${s.id}" data-add="${s.price_add}">${s.name} (+${fmt(s.price_add)})</span>`).join('');
        const sidesHtml = sides.map(s=>`<div class="swal-side-item"><input type="checkbox" class="swal-side-cb" data-id="${s.id}" data-price="${s.price}"><label>${s.name} (+${fmt(s.price)})</label></div>`).join('');

        const html = `
            <div class="addcart-modal">
                <div class="addcart-row"><label>Số lượng:</label>
                    <div class="addcart-qty"><button type="button" id="swal-dec">-</button>
                    <input type="number" id="swal-quantity" value="1" min="1">
                    <button type="button" id="swal-inc">+</button></div>
                </div>
                <div class="addcart-row addcart-collapse open">
                    <div class="addcart-collapse-head" onclick="this.parentElement.classList.toggle('open')">
                        <span class="addcart-collapse-icon"><i class="bi bi-chevron-down"></i></span>
                        <label>Kích thước</label>
                    </div>
                    <div class="addcart-collapse-body"><div class="addcart-sizes">${sizesHtml}</div></div>
                </div>
                <div class="addcart-row addcart-collapse">
                    <div class="addcart-collapse-head" onclick="this.parentElement.classList.toggle('open')">
                        <span class="addcart-collapse-icon"><i class="bi bi-chevron-down"></i></span>
                        <label>Món/nước kèm</label>
                    </div>
                    <div class="addcart-collapse-body"><div class="addcart-sides">${sidesHtml}</div></div>
                </div>
                <div class="addcart-row"><label>Ghi chú:</label>
                    <input type="text" id="swal-note" placeholder="VD: ăn cay, không cay...">
                </div>
                <div class="addcart-total">Tạm tính: <strong id="swal-total">${fmt(calcTotal(1))}</strong></div>
            </div>
        `;

        const updateTotal = () => {
            const qty = parseInt(document.getElementById('swal-quantity').value) || 1;
            document.getElementById('swal-total').textContent = fmt(calcTotal(qty));
        };

        Swal.fire({
            title: 'Thêm vào giỏ hàng',
            html,
            width: '480px',
            showCancelButton: true,
            confirmButtonText: 'Thêm vào giỏ',
            cancelButtonText: 'Hủy',
            confirmButtonColor: '#ff6b81',
            cancelButtonColor: '#6c757d',
            customClass: { popup: 'swal2-no-scroll', htmlContainer: 'swal2-no-scroll' },
            didOpen: () => {
                const c = Swal.getHtmlContainer();
                c.querySelector('#swal-dec').onclick = () => {
                    const i = c.querySelector('#swal-quantity');
                    if (parseInt(i.value) > 1) { i.value = parseInt(i.value) - 1; updateTotal(); }
                };
                c.querySelector('#swal-inc').onclick = () => {
                    const i = c.querySelector('#swal-quantity');
                    i.value = (parseInt(i.value)||1) + 1; updateTotal();
                };
                c.querySelector('#swal-quantity').onchange = updateTotal;
                c.querySelectorAll('.swal-size-opt').forEach(el => {
                    el.onclick = () => {
                        c.querySelectorAll('.swal-size-opt').forEach(x=>x.classList.remove('selected'));
                        el.classList.add('selected');
                        sizeId = parseInt(el.dataset.id);
                        updateTotal();
                    };
                });
                c.querySelectorAll('.swal-side-cb').forEach(el => {
                    el.onchange = () => {
                        sideIds = Array.from(c.querySelectorAll('.swal-side-cb:checked')).map(x=>parseInt(x.dataset.id));
                        updateTotal();
                    };
                });
            },
            preConfirm: () => {
                const qty = parseInt(document.getElementById('swal-quantity').value) || 1;
                if (qty < 1) { Swal.showValidationMessage('Số lượng phải lớn hơn 0!'); return false; }
                return { quantity: qty, note: document.getElementById('swal-note').value, size_id: sizeId, side_dish_ids: sideIds };
            }
        }).then((result) => {
            if (result.isConfirmed) {
                const v = result.value;
                const fd = new FormData();
                fd.append('food_id', foodId);
                fd.append('quantity', v.quantity);
                fd.append('note', v.note);
                fd.append('size_id', v.size_id);
                fd.append('side_dish_ids', v.side_dish_ids.join(','));
                fetch('<?php echo SITEURL; ?>api/add-to-cart.php', { method: 'POST', body: fd })
                    .then(r=>r.json())
                    .then(data => {
                        if (data.success) {
                            Swal.fire({ icon: 'success', title: 'Thành công!', text: data.message, confirmButtonColor: '#ff6b81',
                                showCancelButton: true, confirmButtonText: 'Xem giỏ hàng', cancelButtonText: 'Tiếp tục mua', timer: 3000 })
                                .then(r => { if (r.isConfirmed) window.location.href = '<?php echo SITEURL; ?>user/cart.php'; });
                            updateCartBadge();
                        } else {
                            if (data.message && data.message.indexOf('đăng nhập') !== -1) {
                                Swal.fire({ icon: 'warning', title: 'Yêu cầu đăng nhập', text: data.message, confirmButtonColor: '#ff6b81' })
                                    .then(() => { window.location.href = '<?php echo SITEURL; ?>user/login.php?redirect=cart'; });
                            } else Swal.fire('Lỗi!', data.message, 'error');
                        }
                    })
                    .catch(e => Swal.fire('Lỗi!', 'Có lỗi xảy ra!', 'error'));
            }
        });
    }

    // Cập nhật badge giỏ hàng
    function updateCartBadge() {
        const badge = document.getElementById('cartBadge');
        if (!badge) return;

        fetch('<?php echo SITEURL; ?>api/get-cart-count.php')
            .then(response => response.text())
            .then(text => {
                try {
                    var data = text ? JSON.parse(text) : null;
                    if (data && typeof data.count !== 'undefined') {
                        if (data.count > 0) {
                            badge.textContent = data.count > 99 ? '99+' : data.count;
                            badge.style.display = 'flex';
                        } else {
                            badge.style.display = 'none';
                        }
                    }
                } catch (e) { /* response not JSON, ignore */ }
            })
            .catch(function() {});
    }

    // Load badge khi trang load
    updateCartBadge();
    setInterval(updateCartBadge, 3000);
    </script>

    <script>
    document.addEventListener('DOMContentLoaded', function(){
        const btn = document.getElementById('mobileMenuBtn');
        const panel = document.getElementById('mobileMenuPanel');
        const closeBtn = document.getElementById('mobileMenuClose');

        function openPanel(){ if(panel){ panel.classList.add('open'); panel.setAttribute('aria-hidden','false'); document.body.style.overflow = 'hidden'; } }
        function closePanel(){ if(panel){ panel.classList.remove('open'); panel.setAttribute('aria-hidden','true'); document.body.style.overflow = ''; } }

        if(btn) btn.addEventListener('click', function(e){ e.preventDefault(); if(panel && panel.classList.contains('open')){ closePanel(); } else { openPanel(); } });
        if(closeBtn) closeBtn.addEventListener('click', function(e){ e.preventDefault(); closePanel(); });

        function syncBadges(){
            const cart = document.getElementById('cartBadge');
            const cartM = document.getElementById('cartBadgeMobile');
            if(cart && cartM){ cartM.style.display = cart.style.display; cartM.textContent = cart.textContent; }
            const chat = document.getElementById('chatBadge');
            const chatM = document.getElementById('chatBadgeMobile');
            if(chat && chatM){ chatM.style.display = chat.style.display; chatM.textContent = chat.textContent; }
        }
        syncBadges();
        setInterval(syncBadges, 1500);
    });
    </script>