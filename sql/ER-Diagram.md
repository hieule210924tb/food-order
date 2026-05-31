# Sơ đồ Mô hình ER - WowFood Database

## Tổng quan
Database: `food-oder`  
Engine: InnoDB  
Charset: utf8mb4_unicode_ci

---

## Các Bảng và Mối quan hệ

### 1. tbl_admin (Quản trị viên)
```
┌─────────────────────────────────────┐
│           tbl_admin                │
├─────────────────────────────────────┤
│ id (PK)                            │
│ full_name                          │
│ email (UNIQUE)                     │
│ username (UNIQUE)                  │
│ password                           │
│ phone                              │
│ created_at                         │
└─────────────────────────────────────┘
```

**Mối quan hệ:**
- 1:N → tbl_refund (processed_by)
- 1:N → tbl_chat (admin_id)

---

### 2. tbl_user (Người dùng)
```
┌─────────────────────────────────────┐
│            tbl_user                 │
├─────────────────────────────────────┤
│ id (PK)                            │
│ full_name                          │
│ username (UNIQUE)                  │
│ password                           │
│ email (UNIQUE)                     │
│ phone                              │
│ address                            │
│ ghn_province_id                    │
│ ghn_district_id                    │
│ ghn_ward_code                      │
│ status                             │
│ created_at                         │
└─────────────────────────────────────┘
```

**Mối quan hệ:**
- 1:N → tbl_cart (user_id) [CASCADE DELETE]
- 1:N → tbl_order (user_id) [SET NULL]
- 1:N → tbl_payment (user_id) [CASCADE DELETE]
- 1:N → tbl_order_notification (user_id) [CASCADE DELETE]
- 1:N → tbl_refund (user_id) [CASCADE DELETE]
- 1:N → tbl_chat (user_id) [CASCADE DELETE]

---

### 3. tbl_category (Danh mục món ăn)
```
┌─────────────────────────────────────┐
│         tbl_category                │
├─────────────────────────────────────┤
│ id (PK)                            │
│ title                              │
│ featured                           │
│ active                             │
│ image_name                         │
└─────────────────────────────────────┘
```

**Mối quan hệ:**
- 1:N → tbl_food (category_id) [RESTRICT DELETE]

---

### 4. tbl_food (Món ăn)
```
┌─────────────────────────────────────┐
│           tbl_food                 │
├─────────────────────────────────────┤
│ id (PK)                            │
│ title                              │
│ description                        │
│ price                              │
│ image_name                         │
│ category_id (FK → tbl_category)    │
│ featured                           │
│ active                             │
│ quantity                           │
└─────────────────────────────────────┘
```

**Mối quan hệ:**
- N:1 → tbl_category (category_id)
- 1:N → tbl_cart (food_id) [CASCADE DELETE]

---

### 5. tbl_size (Kích thước món ăn)
```
┌─────────────────────────────────────┐
│           tbl_size                 │
├─────────────────────────────────────┤
│ id (PK)                            │
│ name                               │
│ price_add                          │
│ sort_order                         │
└─────────────────────────────────────┘
```

**Mối quan hệ:**
- Không có FK (bảng tham chiếu)

---

### 6. tbl_side_dish (Món ăn kèm)
```
┌─────────────────────────────────────┐
│        tbl_side_dish                │
├─────────────────────────────────────┤
│ id (PK)                            │
│ name                               │
│ price                              │
│ type (food/drink)                  │
│ sort_order                         │
└─────────────────────────────────────┘
```

**Mối quan hệ:**
- Không có FK (bảng tham chiếu)

---

### 7. tbl_cart (Giỏ hàng)
```
┌─────────────────────────────────────┐
│           tbl_cart                 │
├─────────────────────────────────────┤
│ id (PK)                            │
│ user_id (FK → tbl_user)            │
│ food_id (FK → tbl_food)            │
│ food_name                          │
│ price                              │
│ quantity                           │
│ note                               │
│ created_at                         │
└─────────────────────────────────────┘
```

**Mối quan hệ:**
- N:1 → tbl_user (user_id) [CASCADE DELETE]
- N:1 → tbl_food (food_id) [CASCADE DELETE]

---

### 8. tbl_order (Đơn hàng)
```
┌─────────────────────────────────────┐
│           tbl_order                │
├─────────────────────────────────────┤
│ id (PK)                            │
│ order_code (UNIQUE)                │
│ user_id (FK → tbl_user)            │
│ food                               │
│ order_details (JSON)               │
│ price                              │
│ qty                                │
│ total                              │
│ shipping_fee                       │
│ order_date                         │
│ status                             │
│ customer_name                      │
│ customer_contact                   │
│ customer_email                     │
│ customer_address                   │
│ to_district_id                     │
│ to_ward_code                       │
│ order_weight_gram                  │
│ ghn_order_code                     │
│ ghn_sort_code                      │
│ ghn_status                         │
│ payment_method                     │
│ payment_status                     │
│ note                               │
│ payment_id (FK → tbl_payment)      │
│ expires_at                         │
└─────────────────────────────────────┘
```

**Mối quan hệ:**
- N:1 → tbl_user (user_id) [SET NULL]
- 1:1 → tbl_payment (payment_id) [SET NULL]
- 1:N → tbl_payment (order_code) [CASCADE DELETE]
- 1:N → tbl_order_notification (order_code) [CASCADE DELETE]
- 1:N → tbl_refund (order_code)

---

### 9. tbl_payment (Thanh toán)
```
┌─────────────────────────────────────┐
│          tbl_payment               │
├─────────────────────────────────────┤
│ id (PK)                            │
│ order_code (FK → tbl_order)        │
│ user_id (FK → tbl_user)            │
│ payment_method                     │
│ amount                             │
│ payment_status                     │
│ transaction_id                     │
│ payment_gateway_response           │
│ failure_reason                     │
│ paid_at                            │
│ expires_at                         │
│ created_at                         │
│ updated_at                         │
│ request_id (UNIQUE)               │
│ order_id (UNIQUE)                 │
│ raw_response                       │
└─────────────────────────────────────┘
```

**Mối quan hệ:**
- N:1 → tbl_order (order_code) [CASCADE DELETE]
- N:1 → tbl_user (user_id) [CASCADE DELETE]
- 1:N → tbl_order (payment_id) [SET NULL]
- 1:N → tbl_refund (payment_id) [CASCADE DELETE]

---

### 10. tbl_order_notification (Thông báo đơn hàng)
```
┌─────────────────────────────────────┐
│   tbl_order_notification            │
├─────────────────────────────────────┤
│ id (PK)                            │
│ order_code (FK → tbl_order)        │
│ user_id (FK → tbl_user)            │
│ message                            │
│ is_read                            │
│ created_at                         │
└─────────────────────────────────────┘
```

**Mối quan hệ:**
- N:1 → tbl_order (order_code) [CASCADE DELETE]
- N:1 → tbl_user (user_id) [CASCADE DELETE]

---

### 11. tbl_refund (Hoàn tiền)
```
┌─────────────────────────────────────┐
│           tbl_refund               │
├─────────────────────────────────────┤
│ id (PK)                            │
│ order_code                         │
│ payment_id (FK → tbl_payment)      │
│ user_id (FK → tbl_user)            │
│ refund_amount                      │
│ refund_reason                      │
│ refund_status                      │
│ refund_method                      │
│ refund_transaction_id              │
│ processed_by (FK → tbl_admin)      │
│ processed_at                       │
│ created_at                         │
│ updated_at                         │
└─────────────────────────────────────┘
```

**Mối quan hệ:**
- N:1 → tbl_payment (payment_id) [CASCADE DELETE]
- N:1 → tbl_user (user_id) [CASCADE DELETE]
- N:1 → tbl_admin (processed_by) [SET NULL]

---

### 12. tbl_chat (Chat hỗ trợ)
```
┌─────────────────────────────────────┐
│           tbl_chat                 │
├─────────────────────────────────────┤
│ id (PK)                            │
│ user_id (FK → tbl_user)            │
│ admin_id (FK → tbl_admin)          │
│ sender_type (user/admin)           │
│ message                            │
│ is_read                            │
│ created_at                         │
└─────────────────────────────────────┘
```

**Mối quan hệ:**
- N:1 → tbl_user (user_id) [CASCADE DELETE]
- N:1 → tbl_admin (admin_id) [SET NULL]

---

### 13. tbl_verification (Mã xác thực)
```
┌─────────────────────────────────────┐
│       tbl_verification             │
├─────────────────────────────────────┤
│ id (PK)                            │
│ email                              │
│ phone                              │
│ verification_code                 │
│ verification_type (email/phone)   │
│ expires_at                         │
│ is_verified                        │
│ attempts                           │
│ created_at                         │
└─────────────────────────────────────┘
```

**Mối quan hệ:**
- Không có FK (bảng độc lập)

---

### 14. tbl_voucher (Mã giảm giá)
```
┌─────────────────────────────────────┐
│          tbl_voucher               │
├─────────────────────────────────────┤
│ id (PK)                            │
│ code (UNIQUE)                      │
│ type (percent/fixed)               │
│ value                              │
│ min_order                          │
│ max_discount                       │
│ status (active/inactive)           │
│ valid_from                         │
│ valid_to                           │
│ created_at                         │
└─────────────────────────────────────┘
```

**Mối quan hệ:**
- Không có FK (bảng độc lập)

---

## Sơ đồ Mối quan hệ (Text-based)

```
┌──────────────┐
│  tbl_admin   │
└──────┬───────┘
       │
       ├── processed_by ──→ tbl_refund
       │
       └── admin_id ─────→ tbl_chat

┌──────────────┐
│  tbl_user    │
└──────┬───────┘
       │
       ├── user_id ─────→ tbl_cart
       │
       ├── user_id ─────→ tbl_order
       │
       ├── user_id ─────→ tbl_payment
       │
       ├── user_id ─────→ tbl_order_notification
       │
       ├── user_id ─────→ tbl_refund
       │
       └── user_id ─────→ tbl_chat

┌──────────────┐
│ tbl_category │
└──────┬───────┘
       │
       └── category_id ─→ tbl_food

┌──────────────┐
│  tbl_food    │
└──────┬───────┘
       │
       └── food_id ─────→ tbl_cart

┌──────────────┐
│  tbl_order   │
└──────┬───────┘
       │
       ├── order_code ──→ tbl_payment
       │
       ├── order_code ──→ tbl_order_notification
       │
       ├── order_code ──→ tbl_refund
       │
       └── payment_id ──→ tbl_payment (1:1)

┌──────────────┐
│  tbl_payment │
└──────┬───────┘
       │
       ├── payment_id ──→ tbl_order
       │
       └── payment_id ──→ tbl_refund

Bảng độc lập (không có FK):
- tbl_size
- tbl_side_dish
- tbl_verification
- tbl_voucher
```

---

## Các Ràng buộc (Constraints)

### Foreign Keys
1. **fk_food_category**: tbl_food.category_id → tbl_category.id (RESTRICT)
2. **fk_cart_user**: tbl_cart.user_id → tbl_user.id (CASCADE)
3. **fk_cart_food**: tbl_cart.food_id → tbl_food.id (CASCADE)
4. **fk_order_user**: tbl_order.user_id → tbl_user.id (SET NULL)
5. **fk_order_payment**: tbl_order.payment_id → tbl_payment.id (SET NULL)
6. **fk_payment_user**: tbl_payment.user_id → tbl_user.id (CASCADE)
7. **fk_payment_order_code**: tbl_payment.order_code → tbl_order.order_code (CASCADE)
8. **fk_order_notif_user**: tbl_order_notification.user_id → tbl_user.id (CASCADE)
9. **fk_order_notif_order**: tbl_order_notification.order_code → tbl_order.order_code (CASCADE)
10. **fk_refund_payment**: tbl_refund.payment_id → tbl_payment.id (CASCADE)
11. **fk_refund_user**: tbl_refund.user_id → tbl_user.id (CASCADE)
12. **fk_refund_admin**: tbl_refund.processed_by → tbl_admin.id (SET NULL)
13. **fk_chat_admin**: tbl_chat.admin_id → tbl_admin.id (SET NULL)
14. **fk_chat_user**: tbl_chat.user_id → tbl_user.id (CASCADE)

### Unique Keys
- tbl_admin.username
- tbl_admin.email
- tbl_user.username
- tbl_user.email
- tbl_order.order_code
- tbl_payment.request_id
- tbl_payment.order_id
- tbl_voucher.code

### Check Constraints
- tbl_food.price >= 0
- tbl_food.quantity >= 0
- tbl_cart.quantity > 0
- tbl_cart.price >= 0
- tbl_order.qty > 0
- tbl_order.total >= 0
- tbl_order.shipping_fee >= 0
- tbl_order.order_weight_gram > 0
- tbl_payment.amount >= 0
- tbl_refund.refund_amount >= 0
- tbl_voucher.value >= 0
- tbl_voucher.min_order >= 0
- tbl_voucher.max_discount >= 0

---

## Indexes
- idx_user_phone, idx_user_status
- idx_food_category
- idx_cart_user, idx_cart_food
- idx_order_user, idx_order_payment_status, idx_order_payment_method, idx_order_status_date, idx_order_user_date, idx_order_expires_at
- idx_payment_order_code, idx_payment_user_id, idx_payment_status, idx_payment_transaction_id, idx_payment_order_status, idx_payment_expires_at
- idx_order_notif_user, idx_order_notif_user_read_created
- idx_refund_order_code, idx_refund_payment_id, idx_refund_user_id, idx_refund_status, idx_refund_user_created, idx_refund_status_created, idx_refund_admin
- idx_chat_admin, idx_chat_user, idx_chat_user_created, idx_chat_user_read_created
- idx_verification_email, idx_verification_code
