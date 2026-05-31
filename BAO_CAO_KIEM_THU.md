# KẾ HOẠCH KIỂM THỬ - WOWFOOD SYSTEM

---

## 1. Tổng quan về tài liệu kế hoạch kiểm thử

### 1.1. Mục đích

Mục đích của tài liệu kế hoạch kiểm thử là:

- **Phạm vi thử nghiệm, các lĩnh vực trọng tâm và mục tiêu:** Xác định rõ ràng phạm vi kiểm thử cho hệ thống WowFood v1.0, bao gồm các module chức năng chính, tích hợp với các bên thứ ba, và các lĩnh vực trọng tâm cần kiểm thử như bảo mật, hiệu năng, và tương thích.
- **Trách nhiệm thử nghiệm:** Phân định rõ trách nhiệm của từng vai trò trong quá trình kiểm thử, bao gồm Project Manager, Test Lead, Tester, Developer, QA Engineer, và Stakeholder.
- **Chiến lược thử nghiệm cho các cấp độ và loại thử nghiệm cho bản phát hành này:** Định nghĩa chiến lược kiểm thử chi tiết cho unit testing, integration testing, system testing, và UAT, cùng với các loại hình kiểm thử như functional testing, non-functional testing, regression testing, và smoke testing.
- **Tiêu chí đầu vào và đầu ra:** Thiết lập tiêu chí rõ ràng để xác định khi nào một giai đoạn kiểm thử có thể bắt đầu (entry criteria) và khi nào được coi là hoàn thành (exit criteria).
- **Cơ sở của các ước tính thử nghiệm:** Cung cấp cơ sở cho việc ước tính thời gian, nhân lực, và resources cần thiết cho quá trình kiểm thử dựa trên complexity của hệ thống và số lượng test cases.
- **Mọi rủi ro, vấn đề, giả định và sự phụ thuộc của thử nghiệm:** Xác định và ghi nhận các rủi ro tiềm ẩn, vấn đề có thể gặp phải, các giả định được đưa ra, và các sự phụ thuộc cần được giải quyết trước khi bắt đầu kiểm thử.
- **Lịch trình kiểm thử và các mốc quan trọng:** Cung cấp lịch trình chi tiết cho toàn bộ quá trình kiểm thử với các mốc quan trọng (milestones) để theo dõi tiến độ và đảm bảo hoàn thành đúng deadline.
- **Các sản phẩm thử nghiệm:** Định nghĩa các deliverables của quá trình kiểm thử bao gồm test plan, test cases, test reports, bug reports, và các tài liệu liên quan.

### 1.2. Phạm vi

Tài liệu này nêu chi tiết về thử nghiệm sẽ được nhóm dự án thực hiện cho dự án WowFood - Hệ thống đặt đồ ăn online v1.0. Tài liệu này xác định các yêu cầu thử nghiệm chung và cung cấp góc nhìn tổng hợp về các hoạt động thử nghiệm của dự án. Mục đích của tài liệu là ghi lại:

- **Những gì sẽ được thử nghiệm:**
  - Tất cả các module chức năng của hệ thống WowFood v1.0
  - Tích hợp với các bên thứ ba: VNPay, MoMo, GHN, Email service
  - Kiểm thử bảo mật, hiệu năng, và tương thích
  - Kiểm thử trên môi trường development và staging

- **Cách thức thử nghiệm sẽ được thực hiện:**
  - Kết hợp giữa kiểm thử thủ công và kiểm thử tự động
  - Thực hiện theo quy trình: Unit Testing → Integration Testing → System Testing → Security Testing → Performance Testing → UAT
  - Sử dụng các công cụ kiểm thử chuyên nghiệp như Postman, Selenium, JMeter, OWASP ZAP, PHPUnit
  - Tuân thủ các tiêu chuẩn và best practices trong kiểm thử phần mềm

- **Những nguồn lực nào là cần thiết và khi nào:**
  - 2 Testers full-time trong 25 ngày làm việc
  - 1 Test Lead để quản lý và coordinate
  - Developers support trong quá trình fix bugs
  - QA Engineer cho security và performance testing
  - Stakeholder tham gia UAT trong 5 ngày
  - Các công cụ và môi trường test sẵn sàng trước khi bắt đầu

---

## 2. Testing Summary

### 2.1. Phạm vi của kiểm thử

#### 2.1.1. Trong phạm vi kiểm thử

Phạm vi kiểm thử của hệ thống WowFood bao gồm toàn bộ các chức năng chính của ứng dụng đặt đồ ăn online:

**Front-end (Khách hàng):**
- Đăng ký và đăng nhập tài khoản người dùng
- Xem danh mục món ăn và chi tiết món ăn
- Tìm kiếm món ăn theo tên
- Thêm món ăn vào giỏ hàng
- Quản lý giỏ hàng (thêm, xóa, cập nhật số lượng)
- Đặt hàng và thanh toán
- Theo dõi lịch sử đơn hàng
- Xem thông báo trạng thái đơn hàng
- Sử dụng voucher giảm giá
- Chat với admin
- Yêu cầu hoàn tiền
- Quên và đặt lại mật khẩu

**Back-end (Quản trị viên):**
- Đăng nhập hệ thống admin
- Quản lý danh mục món ăn
- Quản lý món ăn (thêm, sửa, xóa)
- Quản lý kích thước món ăn
- Quản lý món ăn phụ (side dishes)
- Quản lý đơn hàng
- Quản lý thanh toán
- Quản lý người dùng
- Quản lý voucher
- Quản lý chat với khách hàng
- Xử lý yêu cầu hoàn tiền
- Xem báo cáo doanh thu và thống kê

**API và Tích hợp:**
- API giỏ hàng
- API thanh toán VNPay
- API thanh toán MoMo
- API GHN (Giao Hàng Nhanh) - tính phí vận chuyển và địa chỉ
- API gửi email xác thực
- API chat real-time

**Kiểm thử tích hợp hệ thống:**
- Tích hợp giữa các module nội bộ
- Tích hợp với external APIs (VNPay, MoMo, GHN)
- Tích hợp với email service
- Kiểm thử end-to-end user flows

**Kiểm thử chấp nhận người dùng (UAT):**
- Xác nhận hệ thống đáp ứng business requirements
- Đánh giá trải nghiệm người dùng thực tế
- Sign-off trước khi release

#### 2.1.2. Ngoài phạm vi kiểm thử

Những gì nằm ngoài phạm vi kiểm thử cho nhóm dự án:

- **Kiểm thử hardware và network infrastructure:** Không kiểm thử server hardware, network equipment, và infrastructure layer
- **Kiểm thử các module chưa được phát triển:** Các tính năng chưa được implement sẽ không được kiểm thử
- **Kiểm thử trên môi trường production thực tế:** Kiểm thử chỉ thực hiện trên development và staging environment
- **Kiểm thử các tính năng không nằm trong yêu cầu ban đầu:** Chỉ kiểm thử các chức năng đã được xác định trong requirements document
- **Kiểm thử khả năng sử dụng bởi người dùng cuối:** Usability testing sẽ được thực hiện bởi stakeholders trong UAT, không bởi test team
- **Kiểm thử tích hợp bởi nhà cung cấp:** Tích hợp với VNPay, MoMo, GHN được kiểm thử ở mức độ API callback, không kiểm thử phía nhà cung cấp
- **Kiểm thử load testing quy mô lớn:** Load testing chỉ ở mức độ cơ bản (1000 users), không kiểm thử ở quy mô enterprise
- **Kiểm thử trên các thiết bị legacy:** Không kiểm thử trên các browser hoặc device version cũ
- **Kiểm thử localization:** Chỉ kiểm thử ngôn ngữ tiếng Việt, không kiểm thử multi-language
- **Kiểm thử accessibility:** Không kiểm thử accessibility standards (WCAG)

### 2.2. Phương pháp tiếp cận kiểm thử

Phương pháp kiểm thử được thực hiện theo quy trình:

**Kiểm thử thủ công (Manual Testing):**
- Kiểm thử chức năng (Functional Testing)
- Kiểm thử giao diện người dùng (UI/UX Testing)
- Kiểm thử luồng người dùng (User Flow Testing)
- Kiểm thử tương thích (Compatibility Testing)

**Kiểm thử tự động (Automated Testing):**
- Kiểm thử API endpoints
- Kiểm thử tích hợp thanh toán
- Kiểm thử tích hợp vận chuyển

**Phương pháp kiểm thử theo cấp độ:**
- Kiểm thử đơn vị (Unit Testing)
- Kiểm thử tích hợp (Integration Testing)
- Kiểm thử hệ thống (System Testing)
- Kiểm thử chấp nhận (User Acceptance Testing)

---

## 3. Phân tích phạm vi và các lĩnh vực trọng tâm cần kiểm thử

### 3.1. Nội dung phiên bản

**Phiên bản hiện tại:** WowFood v1.0

**Các module chính:**

1. **Module Xác thực (Authentication Module)**
   - Đăng ký người dùng với xác thực email
   - Đăng nhập người dùng
   - Đăng nhập admin
   - Quên mật khẩu
   - Đặt lại mật khẩu
   - Xác thực mã OTP

2. **Module Sản phẩm (Product Module)**
   - Quản lý danh mục món ăn
   - Quản lý món ăn
   - Quản lý kích thước món ăn
   - Quản lý món ăn phụ
   - Tìm kiếm món ăn
   - Hiển thị món ăn bán chạy

3. **Module Giỏ hàng (Cart Module)**
   - Thêm món vào giỏ
   - Xóa món khỏi giỏ
   - Cập nhật số lượng
   - Lưu giỏ hàng theo user
   - Khôi phục giỏ hàng khi đăng nhập

4. **Module Đơn hàng (Order Module)**
   - Đặt hàng
   - Theo dõi trạng thái đơn hàng
   - Hủy đơn hàng
   - Quản lý đơn hàng (admin)
   - Thông báo trạng thái đơn hàng

5. **Module Thanh toán (Payment Module)**
   - Thanh toán tiền mặt (COD)
   - Thanh toán VNPay
   - Thanh toán MoMo
   - Xử lý callback thanh toán
   - Quản lý giao dịch thanh toán

6. **Module Vận chuyển (Shipping Module)**
   - Tích hợp GHN API
   - Tính phí vận chuyển
   - Quản lý địa chỉ giao hàng
   - Theo dõi vận chuyển

7. **Module Voucher (Voucher Module)**
   - Tạo voucher
   - Áp dụng voucher
   - Kiểm tra điều kiện voucher
   - Quản lý voucher

8. **Module Chat (Chat Module)**
   - Chat giữa user và admin
   - Thông báo tin nhắn mới
   - Đánh dấu đã đọc

9. **Module Hoàn tiền (Refund Module)**
   - Yêu cầu hoàn tiền
   - Xử lý hoàn tiền
   - Quản lý lịch sử hoàn tiền

10. **Module Báo cáo (Report Module)**
    - Thống kê doanh thu
    - Thống kê món ăn bán chạy
    - Xuất báo cáo PDF

### 3.2. Nền tảng kiểm thử

**Môi trường phát triển:**
- Hệ điều hành: Windows
- Web Server: XAMPP (Apache)
- Database: MySQL/MariaDB 10.4+
- PHP Version: PHP 7.4+
- Browser: Chrome, Firefox, Edge, Safari

**Môi trường production:**
- Web Server: Apache/Nginx
- Database: MySQL/MariaDB
- PHP Version: PHP 7.4+
- SSL/TLS: HTTPS

**Công cụ kiểm thử:**
- Postman: Kiểm thử API
- phpMyAdmin: Quản lý database
- Browser DevTools: Debug và kiểm thử UI
- Chrome DevTools: Performance testing
- JMeter: Load testing (nếu cần)

**Tài nguyên kiểm thử:**
- Database test: food-oder-optimized
- API test environment: Sandbox VNPay, MoMo test
- GHN test environment: Dev environment

---

## 4. Mục tiêu tiến trình kiểm thử

Phần này trình bày chi tiết các mục tiêu kiểm tra tiến trình mà nhóm dự án sẽ thực hiện. Bảng dưới đây liệt kê các chức năng cần kiểm thử, mục tiêu kiểm thử, tiêu chí đánh giá, và mức độ ưu tiên.

| Ref | Chức năng | Mục tiêu kiểm thử | Tiêu chí đánh giá | Tham chiếu chéo | Độ ưu tiên |
|-----|-----------|-------------------|-------------------|-----------------|------------|
| TC-001 | Đăng ký người dùng | Xác minh người dùng có thể đăng ký tài khoản mới với xác thực email | - Form validation hoạt động đúng<br>- Email xác thực được gửi<br>- User có thể xác thực email<br>- Account được tạo thành công | Requirements v1.0, Section 3.1 | P1 |
| TC-002 | Đăng nhập người dùng | Xác minh người dùng có thể đăng nhập với thông tin đúng | - Login với email/password đúng thành công<br>- Login với thông tin sai thất bại<br>- Session được tạo đúng<br>- Redirect đúng trang | Requirements v1.0, Section 3.2 | P1 |
| TC-003 | Quên mật khẩu | Xác minh quy trình reset mật khẩu hoạt động đúng | - Email reset được gửi<br>- Link reset hoạt động<br>- Mật khẩu mới được cập nhật<br>- User có thể login với mật khẩu mới | Requirements v1.0, Section 3.3 | P2 |
| TC-004 | Xem danh mục món ăn | Xác minh danh mục món ăn hiển thị đúng | - Tất cả category hiển thị<br>- Image hiển thị đúng<br>- Click vào category điều hướng đúng | Requirements v1.0, Section 4.1 | P1 |
| TC-005 | Tìm kiếm món ăn | Xác minh chức năng tìm kiếm hoạt động đúng | - Tìm kiếm theo tên trả về kết quả đúng<br>- Không có kết quả hiển thị thông báo<br>- Search case-insensitive | Requirements v1.0, Section 4.2 | P2 |
| TC-006 | Thêm món vào giỏ | Xác minh món ăn được thêm vào giỏ hàng đúng | - Món được thêm với số lượng đúng<br>- Giá tính toán đúng<br>- Giỏ hàng cập nhật real-time | Requirements v1.0, Section 5.1 | P1 |
| TC-007 | Quản lý giỏ hàng | Xác minh các thao tác trên giỏ hàng hoạt động đúng | - Cập nhật số lượng thành công<br>- Xóa món thành công<br>- Tổng tiền tính toán đúng | Requirements v1.0, Section 5.2 | P1 |
| TC-008 | Đặt hàng | Xác minh quy trình đặt hàng hoàn thành | - Thông tin khách hàng được lưu<br>- Đơn hàng được tạo<br>- Order code được sinh ra<br>- Trạng thái đơn hàng là Pending | Requirements v1.0, Section 6.1 | P1 |
| TC-009 | Thanh toán VNPay | Xác minh tích hợp VNPay hoạt động đúng | - Payment URL được tạo đúng<br>- Redirect đến VNPay thành công<br>- Callback được xử lý đúng<br>- Trạng thái thanh toán cập nhật đúng | VNPay Integration Guide | P1 |
| TC-010 | Thanh toán MoMo | Xác minh tích hợp MoMo hoạt động đúng | - Payment URL được tạo đúng<br>- Redirect đến MoMo thành công<br>- IPN được xử lý đúng<br>- Trạng thái thanh toán cập nhật đúng | MoMo Integration Guide | P1 |
| TC-011 | Thanh toán COD | Xác minh thanh toán tiền mặt hoạt động đúng | - Đơn hàng được tạo với payment method COD<br>- Trạng thái thanh toán là pending<br>- Admin có thể cập nhật thanh toán | Requirements v1.0, Section 6.4 | P1 |
| TC-012 | Tính phí vận chuyển GHN | Xác minh tích hợp GHN tính phí hoạt động đúng | - API gọi thành công<br>- Phí vận chuyển tính toán đúng<br>- Phí hiển thị đúng cho user | GHN API Documentation | P1 |
| TC-013 | Áp dụng voucher | Xác minh voucher được áp dụng đúng | - Voucher valid được áp dụng<br>- Giảm giá tính toán đúng<br>- Voucher invalid bị từ chối<br>- Điều kiện voucher được kiểm tra | Requirements v1.0, Section 7.1 | P2 |
| TC-014 | Theo dõi đơn hàng | Xác minh user có thể theo dõi trạng thái đơn hàng | - Lịch sử đơn hàng hiển thị<br>- Trạng thái cập nhật real-time<br>- Thông báo được gửi | Requirements v1.0, Section 6.5 | P1 |
| TC-015 | Chat với admin | Xác minh chức năng chat hoạt động đúng | - Tin nhắn được gửi và nhận<br>- Real-time update<br>- Thông báo tin nhắn mới | Requirements v1.0, Section 8.1 | P2 |
| TC-016 | Yêu cầu hoàn tiền | Xác minh quy trình yêu cầu hoàn tiền hoạt động đúng | - Yêu cầu được tạo<br>- Lý do được lưu<br>- Admin nhận được thông báo | Requirements v1.0, Section 9.1 | P2 |
| TC-017 | Quản lý đơn hàng (Admin) | Xác minh admin có thể quản lý đơn hàng | - Xem tất cả đơn hàng<br>- Cập nhật trạng thái<br>- Hủy đơn hàng<br>- Xuất báo cáo | Requirements v1.0, Section 10.1 | P1 |
| TC-018 | Quản lý sản phẩm (Admin) | Xác minh admin có thể quản lý sản phẩm | - Thêm/sửa/xóa món ăn<br>- Upload image<br>- Cập nhật giá và số lượng | Requirements v1.0, Section 10.2 | P1 |
| TC-019 | Báo cáo doanh thu | Xác minh báo cáo doanh thu hiển thị đúng | - Doanh thu tính toán đúng<br>- Chart hiển thị đúng<br>- Filter hoạt động đúng<br>- Xuất PDF thành công | Requirements v1.0, Section 10.3 | P2 |
| TC-020 | Bảo mật - Authentication | Xác minh authentication và authorization hoạt động đúng | - Session timeout hoạt động<br>- Protected routes không thể truy cập trực tiếp<br>- Role-based access control hoạt động | OWASP Top 10 | P1 |
| TC-021 | Bảo mật - SQL Injection | Xác minh hệ thống không bị SQL Injection | - Input sanitization hoạt động<br>- Prepared statements được sử dụng<br>- Không có SQL injection vulnerability | OWASP Top 10 | P1 |
| TC-022 | Bảo mật - XSS | Xác minh hệ thống không bị XSS attack | - Output encoding hoạt động<br>- User input được sanitize<br>- Không có XSS vulnerability | OWASP Top 10 | P1 |
| TC-023 | Hiệu suất - API Response | Xác minh API response time đạt target | - Response time < 2s cho 90% requests<br>- Response time < 5s cho 99% requests<br>- No timeout errors | Performance Requirements | P1 |
| TC-024 | Hiệu suất - Page Load | Xác minh page load time đạt target | - Page load time < 3s<br>- Time to Interactive < 4s<br>- First Contentful Paint < 1.5s | Performance Requirements | P1 |
| TC-025 | Hiệu suất - Load Testing | Xác minh hệ thống chịu tải tốt | - Hỗ trợ 100 concurrent users<br>- Error rate < 0.1%<br>- Throughput > 100 req/s | Performance Requirements | P2 |

---

## 5. Kiểm thử khác

### 5.1. Bảo mật (Security Testing)

**Chi tiết security testing sẽ được thực hiện:**

Security testing sẽ được thực hiện bởi QA Engineer với sự hỗ trợ từ Test Lead. Các hoạt động bao gồm:

1. **Vulnerability Scanning:** Sử dụng OWASP ZAP để quét tự động các lỗ hổng bảo mật known vulnerabilities
2. **Manual Security Testing:** Thực hiện manual testing cho SQL injection, XSS, CSRF, và authentication bypass
3. **Penetration Testing:** Thử nghiệm penetration testing cơ bản để xác định các lỗ hổng tiềm ẩn
4. **Code Review:** Review code để xác định các security issues tiềm ẩn
5. **Configuration Review:** Kiểm tra configuration của server và application để đảm bảo security best practices

**Các điểm kiểm tra bảo mật:**

- Xác thực và ủy quyền (Authentication & Authorization)
- Bảo mật mật khẩu (Password Security)
- Bảo mật API (API Security)
- Bảo mật thanh toán (Payment Security)
- Bảo mật dữ liệu (Data Security)
- Bảo mật session (Session Security)

**Công cụ bảo mật:**
- OWASP ZAP: Vulnerability scanning
- Burp Suite: Web security testing
- SQLMap: SQL injection testing
- XSStrike: XSS testing

### 5.2. Kiểm thử hiệu năng (S&V - Scalability & Volume)

**Chi tiết stress và volume testing sẽ được thực hiện:**

Performance testing sẽ được thực hiện bởi QA Engineer sử dụng Apache JMeter. Các hoạt động bao gồm:

1. **Load Testing:** Kiểm thử hệ thống dưới tải bình thường (100, 500, 1000 concurrent users)
2. **Stress Testing:** Kiểm thử hệ thống dưới tải vượt quá giới hạn để xác định breaking point
3. **Volume Testing:** Kiểm thử hệ thống với lượng dữ liệu lớn (100,000 orders, 10,000 foods, 50,000 users)
4. **Database Performance Testing:** Đo lường query execution time và kiểm tra index optimization
5. **API Performance Testing:** Đo lường API response time, throughput, và error rate

**Cách thức thực hiện:**
- Sử dụng Apache JMeter để tạo test scripts
- Thực hiện testing trên staging environment
- Thu thập metrics và analysis results
- So sánh với KPI targets
- Report findings và recommendations

**Người thực hiện:** QA Engineer

**Kết quả mong đợi:**
- Response time < 2s cho API
- Page load time < 3s
- Throughput > 100 requests/second
- Error rate < 0.1%
- CPU usage < 80%
- Memory usage < 70%

### 5.3. Kiểm thử đơn vị (Unit Testing)

**Chi tiết unit testing sẽ được thực hiện:**

Unit testing là verification của các module hoặc "units" code riêng lẻ. Unit testing sẽ được thực hiện bởi Developers trong quá trình phát triển.

**Các module cần kiểm thử đơn vị:**

1. **Helper Functions:**
   - Hàm validate email
   - Hàm validate phone
   - Hàm format currency
   - Hàm calculate total
   - Hàm generate order code

2. **Database Functions:**
   - Hàm connect database
   - Hàm execute query
   - Hàm fetch data
   - Hàm insert/update/delete

3. **Payment Functions:**
   - Hàm calculate VNPay signature
   - Hàm validate VNPay callback
   - Hàm calculate MoMo signature
   - Hàm validate MoMo callback

4. **Shipping Functions:**
   - Hàm calculate shipping fee
   - Hàm validate address
   - Hàm format GHN request

5. **Voucher Functions:**
   - Hàm validate voucher
   - Hàm calculate discount
   - Hàm check voucher conditions

**Framework kiểm thử đơn vị:**
- PHPUnit (cho PHP)
- Mock objects cho database
- Test doubles cho external APIs

**Coverage target:**
- Line coverage > 70%
- Branch coverage > 60%
- Function coverage > 80%

### 5.4. Kiểm thử tích hợp (Integration Testing)

**Chi tiết integration testing sẽ được thực hiện:**

Integration testing sẽ được thực hiện bởi Tester và Developer để kiểm tra tích hợp giữa các module và với external systems.

**Các điểm tích hợp cần kiểm thử:**

1. **Tích hợp Database:**
   - Kiểm tra kết nối database
   - Kiểm tra transaction rollback
   - Kiểm tra foreign key constraints
   - Kiểm tra data integrity

2. **Tích hợp VNPay:**
   - Kiểm tra tạo payment URL
   - Kiểm tra xử lý callback thành công
   - Kiểm tra xử lý callback thất bại
   - Kiểm tra query transaction
   - Kiểm tra refund transaction

3. **Tích hợp MoMo:**
   - Kiểm tra tạo payment URL
   - Kiểm tra xử lý IPN thành công
   - Kiểm tra xử lý IPN thất bại
   - Kiểm tra query transaction
   - Kiểm tra refund transaction

4. **Tích hợp GHN:**
   - Kiểm tra lấy danh sách tỉnh/thành
   - Kiểm tra lấy danh sách quận/huyện
   - Kiểm tra lấy danh sách phường/xã
   - Kiểm tra tính phí vận chuyển
   - Kiểm tra tạo đơn hàng vận chuyển
   - Kiểm tra hủy đơn hàng vận chuyển

5. **Tích hợp Email:**
   - Kiểm tra gửi email xác thực
   - Kiểm tra gửi email reset password
   - Kiểm tra gửi email thông báo đơn hàng

6. **Tích hợp Front-end và Back-end:**
   - Kiểm tra API endpoints
   - Kiểm tra AJAX requests
   - Kiểm tra form submissions
   - Kiểm tra session management

7. **Tích hợp Module:**
   - Kiểm tra flow: Đăng ký -> Đăng nhập -> Đặt hàng -> Thanh toán
   - Kiểm tra flow: Đặt hàng -> Thanh toán -> Xác nhận -> Giao hàng
   - Kiểm tra flow: Đặt hàng -> Thanh toán -> Hủy -> Hoàn tiền
   - Kiểm tra flow: Sử dụng voucher -> Thanh toán -> Áp dụng giảm giá

**Công cụ kiểm thử tích hợp:**
- Postman Collections
- Newman (CLI for Postman)
- Selenium WebDriver
- Cypress

---

## 6. Chiến lược kiểm thử

### 6.1. Mức độ kiểm thử

Trình bày chi tiết các cấp độ thử nghiệm dự kiến sẽ được áp dụng và ai có trách nhiệm chính (P) và phụ (S) trong việc thực hiện thử nghiệm này.

| Mức độ kiểm thử | Bên ngoài | Nhóm kiểm thử | Khách hàng |
|-----------------|-----------|--------------|------------|
| Kiểm thử đơn vị | P | S | - |
| Kiểm thử tích hợp | S | P | - |
| Kiểm thử hệ thống | - | P | S |
| Kiểm thử chấp nhận người dùng - UAT | - | S | P |
| Kiểm thử xác minh sản phẩm | - | S | P |

**Giải thích:**
- **P (Primary):** Trách nhiệm chính trong việc thực hiện kiểm thử
- **S (Secondary):** Trách nhiệm phụ hoặc hỗ trợ
- **-:** Không tham gia

**Chi tiết:**
- **Kiểm thử đơn vị:** Thực hiện bởi Developers (Bên ngoài), Test Team hỗ trợ review
- **Kiểm thử tích hợp:** Thực hiện bởi Test Team (Nhóm kiểm thử), Developers hỗ trợ
- **Kiểm thử hệ thống:** Thực hiện bởi Test Team (Nhóm kiểm thử), Khách hàng tham gia UAT
- **UAT:** Thực hiện bởi Khách hàng (Stakeholder), Test Team hỗ trợ
- **Kiểm thử xác minh sản phẩm:** Thực hiện bởi Khách hàng, Test Team hỗ trợ

### 6.2. Loại hình kiểm thử

Chi tiết các loại hình kiểm thử được nhóm dự án thực hiện và các mục tiêu tiêu chuẩn của họ.

| Loại hình kiểm thử | Objectives |
|-------------------|------------|
| Yêu cầu tiến trình | Mục tiêu là xác minh rằng ứng dụng:<br>• Đáp ứng các yêu cầu đã xác định<br>• Thực hiện và hoạt động chính xác<br>• Xử lý đúng các điều kiện lỗi<br>• Giao diện hoạt động chính xác<br>• Tải dữ liệu thành công<br><br>Kiểm tra chức năng sẽ diễn ra theo cách lặp lại và được kiểm soát, đảm bảo giải pháp phù hợp với các yêu cầu đã xác định. |
| Regression testing | Mục tiêu là xác minh rằng các thay đổi mới không ảnh hưởng đến các chức năng đã hoạt động. Regression testing sẽ được thực hiện sau mỗi lần fix bug hoặc thêm tính năng mới để đảm bảo tính ổn định của hệ thống. |
| Smoke testing | Mục tiêu là xác minh các chức năng cốt lõi hoạt động ổn định. Smoke testing sẽ được thực hiện nhanh trước mỗi release để đảm bảo hệ thống đủ stable để tiếp tục testing chi tiết. |
| Performance testing | Mục tiêu là đo lường và xác minh hiệu suất hệ thống đạt các KPI đã định (response time, throughput, scalability). |
| Security testing | Mục tiêu là xác định và khắc phục các lỗ hổng bảo mật tiềm ẩn, đảm bảo an toàn dữ liệu và authentication/authorization hoạt động đúng. |
| Compatibility testing | Mục tiêu là xác minh hệ thống hoạt động đúng trên các browser và device khác nhau. |
| Usability testing | Mục tiêu là đánh giá trải nghiệm người dùng và ease of use của hệ thống. |

### 6.3. Lịch trình kiểm thử

Cung cấp lịch trình kiểm tra cho nhóm dự án dưới dạng bảng. Nêu chi tiết từng loại kiểm tra, chức năng và mức độ ưu tiên.

| Nội dung công việc | Thời gian bắt đầu | Thời gian kết thúc | Người thực hiện | Ràng buộc công việc |
|--------------------|-------------------|-------------------|----------------|-------------------|
| Kế hoạch kiểm thử | Ngày 1 | Ngày 1 | Test Lead | Requirements document đã sẵn sàng |
| Tìm hiểu tài liệu yêu cầu | Ngày 1 | Ngày 2 | Test Team | Requirements document đã sẵn sàng |
| Đào tạo nhân sự | Ngày 2 | Ngày 2 | Test Lead | Trainers có sẵn |
| Triển khai môi trường QA | Ngày 2 | Ngày 3 | QA Engineer | Infrastructure sẵn sàng |
| Kiểm thử chức năng – Lặp lần 1 | Ngày 3 | Ngày 7 | Testers | Test cases đã được viết |
| Kiểm thử tích hợp – Lần 1 | Ngày 8 | Ngày 12 | Testers & Developers | Unit testing hoàn thành |
| Kiểm thử hệ thống – Lần 1 | Ngày 13 | Ngày 19 | Testers | Integration testing hoàn thành |
| Kiểm thử bảo mật | Ngày 20 | Ngày 22 | QA Engineer | System testing hoàn thành |
| Kiểm thử hiệu năng | Ngày 23 | Ngày 24 | QA Engineer | System testing hoàn thành |
| UAT – Lần 1 | Ngày 25 | Ngày 29 | Stakeholders | System testing hoàn thành |
| Fix bugs từ UAT | Ngày 30 | Ngày 31 | Developers | UAT hoàn thành |
| Regression testing | Ngày 32 | Ngày 33 | Testers | Bugs đã được fix |
| Final review và sign-off | Ngày 34 | Ngày 34 | Test Lead & PM | Tất cả testing hoàn thành |

**Tổng thời gian:** 34 ngày làm việc

### 6.4. Tiện ích, dữ liệu và môi trường

#### 6.4.1. Môi trường kiểm thử

Chi tiết về môi trường kiểm thử cần thiết và ngày khả dụng.

**Trình duyệt:**
- **Windows:** Edge (bản mới nhất), Chrome (bản mới nhất), Firefox (bản mới nhất)
- **MacOS:** Chrome (bản mới nhất), Safari (bản mới nhất)

**Thiết bị:**
- Desktop/Laptop: Windows 10+, MacOS 10.15+
- Mobile: iOS 13+, Android 10+ (optional)

**Hệ điều hành:** Từ hệ điều hành Windows 10 trở lên, MacOS 10.15+

**Môi trường Development:**
- URL: http://localhost/php/Do_An_Cnpm/
- Database: food-oder-optimized (local)
- Purpose: Development và unit testing
- Access: Developers
- Ngày khả dụng: Đã sẵn sàng

**Môi trường Staging:**
- URL: [Staging URL]
- Database: food-oder-staging
- Purpose: Integration và system testing
- Access: Testers và Developers
- Data: Sample data gần giống production
- Ngày khả dụng: Ngày 3

**Môi trường Production:**
- URL: [Production URL]
- Database: food-oder-prod
- Purpose: Live system
- Access: Admin only
- Data: Real user data
- Ngày khả dụng: Sau release

#### 6.4.2. Các thiết bị khác

Chi tiết các hệ thống có yêu cầu được truy cập để thực thi kiểm thử:

- **Database Server:** MySQL/MariaDB 10.4+ với quyền truy cập read/write
- **Email Server:** SMTP server để test email sending
- **File Server:** Để upload và test file operations
- **External APIs:** Access đến sandbox environments của VNPay, MoMo, GHN

#### 6.4.3. Yêu cầu kiểm thử

Chi tiết các yêu cầu để bắt đầu kiểm thử.

Mỗi người tham gia thử nghiệm sẽ cần quyền truy cập sau:

- Trình duyệt web có quyền truy cập vào mạng nội bộ
- Quyền truy cập vào cơ sở dữ liệu food-oder-optimized và công cụ SQL cơ sở dữ liệu có liên quan (phpMyAdmin)
- Quyền truy cập vào Postman để kiểm thử API
- Quyền truy cập vào JIRA/Trello để báo cáo lỗi
- Quyền truy cập vào Git để version control
- Quyền truy cập vào Microsoft Excel để báo cáo lỗi (nếu cần)

#### 6.4.4. Yêu cầu dữ liệu

Định nghĩa yêu cầu cài đặt dữ liệu bắt đầu kiểm thử:

- **User test accounts:** 10 accounts với các role khác nhau (admin, user)
- **Product test data:** 50 món ăn với các category khác nhau
- **Order test data:** 100 đơn hàng với các trạng thái khác nhau (Pending, Confirmed, Delivered, Cancelled)
- **Voucher test data:** 5 vouchers với các loại khác nhau (percent, fixed)
- **Payment test data:** Sample transactions cho VNPay và MoMo sandbox
- **Chat test data:** Sample conversations giữa user và admin

Dữ liệu sẽ được setup trước khi bắt đầu testing (Ngày 2-3).

#### 6.4.5. Tài nguyên và kĩ năng

Mô tả các yêu cầu về loại tài nguyên để thực hiện kiểm thử:

- **Kĩ năng SQL:** Để query và verify data trong database
- **Kĩ năng PHP:** Để hiểu code và hỗ trợ debugging
- **Kĩ năng xử lý công nghệ mạng:** Để troubleshoot network issues
- **Kĩ năng sử dụng Postman:** Để kiểm thử API
- **Kĩ năng sử dụng JMeter:** Để performance testing (cho QA Engineer)
- **Kĩ năng security testing:** Để vulnerability scanning (cho QA Engineer)

### 6.5. Công cụ kiểm thử

Mô tả các công cụ cần thiết cho kiểm thử.

| Bước thực hiện | Công cụ |
|----------------|--------|
| Test case creation | Microsoft Word / Confluence |
| Test case tracking | JIRA / Trello |
| Test case execution | Manual / Postman / Selenium |
| Test case management | JIRA / Trello |
| Defect management | JIRA / Trello |
| Unit testing | PHPUnit |
| API testing | Postman / Newman |
| Web automation | Selenium WebDriver / Cypress |
| Security testing | OWASP ZAP / Burp Suite / SQLMap |
| Performance testing | Apache JMeter |
| Code coverage | PHPUnit Coverage |
| Database management | phpMyAdmin |
| Version control | Git |
| Communication | Slack / Microsoft Teams |

### 6.6. Vai trò

Xác định vai trò và trách nhiệm của những người sẽ chịu trách nhiệm hoặc tương tác với môi trường.

| Vai trò | Nhân viên | Trách nhiệm |
|--------|----------|-------------|
| Release Manager | [Project Manager Name] | Responsible for overall establishment, coordination and support of the test environment |
| Test Manager | [Test Lead Name] | Responsible for advising release manager of environment requirements for planning, establishment and ongoing support |
| Project Manager | [Project Manager Name] | Escalation point for environment issues, approve test plan, manage timeline and resources |
| Test Lead | [Test Lead Name] | Create detailed test plan, assign tasks to testers, review test cases and test reports, coordinate with developers |
| Tester | [Tester 1 Name, Tester 2 Name] | Write and execute test cases, report bugs with details, verify bug fixes, perform regression testing |
| Developer | [Developer 1 Name, Developer 2 Name] | Write unit tests, fix bugs reported by testers, support testers during testing, review test cases |
| QA Engineer | [QA Engineer Name] | Set up test environment, set up automated test suite, perform performance and security testing, CI/CD integration |
| Business Analyst/Stakeholder | [Stakeholder Name] | Define acceptance criteria, participate in UAT, sign-off for release, provide feedback |

---

## 7. Giả thiết và ràng buộc

### 7.1. Giả thiết

Chi tiết bất kỳ giả định nào được đưa ra để thử nghiệm.

Ví dụ, các thành viên nhóm phát triển và phân tích kinh doanh sẽ có mặt để cung cấp hỗ trợ, đào tạo và giải quyết lỗi cho các thành viên nhóm thử nghiệm khi cần thiết.

**Giả thiết về môi trường:**
- Môi trường development và staging sẵn sàng trước khi bắt đầu testing
- Database test được setup với dữ liệu mẫu đầy đủ
- External APIs (VNPay, MoMo, GHN) có sandbox environment hoạt động
- Email service có thể gửi email trong môi trường test

**Giả thiết về resources:**
- Đủ nhân lực cho kế hoạch kiểm thử (2 testers, 1 test lead)
- Developers có sẵn để support và fix bugs
- Stakeholder có sẵn để tham gia UAT
- Budget cho công cụ kiểm thử (nếu cần license)

**Giả thiết về timeline:**
- Development hoàn thành trước khi bắt đầu testing
- Không có thay đổi lớn trong requirements trong quá trình testing
- Bugs được fix trong thời gian quy định
- UAT có thể hoàn thành trong 5 ngày

**Giả thiết về documents:**
- Requirements document đã được phê duyệt
- Technical design document có sẵn
- API documentation đầy đủ
- User stories và acceptance criteria rõ ràng

### 7.2. Ràng buộc

Chi tiết kiểm tra sự phụ thuộc.

Ví dụ, quyền truy cập vào hệ thống trong môi trường thử nghiệm sẽ được quản trị viên hệ thống cấu hình cho tất cả các thành viên nhóm thử nghiệm được xác định trước khi bắt đầu thử nghiệm.

**Ràng buộc về thời gian:**
- Tổng thời gian testing: 34 ngày làm việc
- Deadline release: [Date]
- Không thể extend timeline trừ khi có approval từ PM

**Ràng buộc về budget:**
- Budget cho công cụ testing: [Amount]
- Prefer open-source tools
- License cost phải được approval trước

**Ràng buộc về resources:**
- Chỉ có 2 testers full-time
- Developers có thể bị pull sang các project khác
- Stakeholder chỉ có sẵn trong specific hours

**Ràng buộc kỹ thuật:**
- Phải tương thích với PHP 7.4+
- Phải hoạt động trên MySQL/MariaDB 10.4+
- Phải tương thích với các browser chính (Chrome, Firefox, Edge, Safari)
- Không thể thay đổi architecture trong quá trình testing

**Ràng buộc về chất lượng:**
- Không thể release với critical bugs
- Code coverage phải >70%
- Performance KPI phải đạt target
- Security vulnerabilities phải được fix trước release

---

## 8. Điều khoản chấp thuận

Detail the entry and exit criteria that are used to determine when a phase of testing (or level of testing) is able to commence and when testing is considered to be completed.

**Entry Criteria (Tiêu chí đầu vào):**

Để bắt đầu một giai đoạn kiểm thử, các điều kiện sau phải được đáp ứng:

1. **Unit Testing:**
   - Code đã được commit và review
   - Development environment sẵn sàng
   - Test cases đã được viết

2. **Integration Testing:**
   - Unit testing hoàn thành với coverage >70%
   - Staging environment sẵn sàng
   - Test data đã được setup
   - External APIs sandbox accessible

3. **System Testing:**
   - Integration testing hoàn thành
   - Tất cả integration bugs đã được fix
   - Test environment stable

4. **Security Testing:**
   - System testing hoàn thành
   - OWASP ZAP installed và configured
   - Security test cases đã được viết

5. **Performance Testing:**
   - System testing hoàn thành
   - JMeter installed và configured
   - Performance test scripts đã được viết

6. **UAT:**
   - Tất cả testing phases hoàn thành
   - Critical và high bugs đã được fix
   - System stable trên staging environment
   - Documentation hoàn chỉnh

**Exit Criteria (Tiêu chí đầu ra):**

Một giai đoạn kiểm thử được coi là hoàn thành khi:

1. **Unit Testing:**
   - Tất cả unit tests pass
   - Code coverage >70%
   - Không có critical bugs

2. **Integration Testing:**
   - Tất cả integration tests pass
   - Tất cả external APIs integration hoạt động đúng
   - Integration bugs < 5

3. **System Testing:**
   - Tất cả functional tests pass
   - Không có critical bugs
   - High bugs < 5
   - Medium bugs < 10

4. **Security Testing:**
   - Không có critical/high security vulnerabilities
   - OWASP Top 10 vulnerabilities được address
   - Security report đã được review

5. **Performance Testing:**
   - Response time < 2s cho API
   - Page load time < 3s
   - Throughput > 100 requests/second
   - Error rate < 0.1%

6. **UAT:**
   - Stakeholder approval
   - Business requirements được đáp ứng
   - User experience acceptable
   - Documentation hoàn chỉnh

---

## 9. Kế hoạch tài liệu

### 9.1. Mốc kiểm thử

Dưới đây là các mốc kiểm tra cấp độ cao được nêu chi tiết.

| Mốc thời gian | Thời gian kết thúc | Thời gian kết thúc thực tế | Tài nguyên |
|---------------|-------------------|---------------------------|------------|
| Milestone 1: Unit Testing Complete | Ngày 3 | [Điền sau khi hoàn thành] | Developers, Test Lead |
| Milestone 2: Integration Testing Complete | Ngày 12 | [Điền sau khi hoàn thành] | Testers, Developers |
| Milestone 3: System Testing Complete | Ngày 19 | [Điền sau khi hoàn thành] | Testers |
| Milestone 4: Security Testing Complete | Ngày 22 | [Điền sau khi hoàn thành] | QA Engineer |
| Milestone 5: Performance Testing Complete | Ngày 24 | [Điền sau khi hoàn thành] | QA Engineer |
| Milestone 6: UAT Complete | Ngày 29 | [Điền sau khi hoàn thành] | Stakeholders, Test Team |
| Milestone 7: Release Ready | Ngày 34 | [Điền sau khi hoàn thành] | All Team Members |

### 9.2. Kế hoạch đào tạo

Các yêu cầu đào tạo sau đây đã được xác định để đảm bảo việc thử nghiệm có thể bắt đầu.

| Yêu cầu đào tạo | Nhân viên | Ngày | Trạng thái |
|----------------|----------|------|-----------|
| Overview hệ thống WowFood | Testers | Ngày 2 | Pending |
| Tool training (Postman, Selenium, JMeter) | Testers | Ngày 2 | Pending |
| Test case writing best practices | Testers | Ngày 2 | Pending |
| Unit testing với PHPUnit | Developers | Ngày 1 | Pending |
| UAT overview | Stakeholders | Ngày 25 | Pending |

### 9.3. Quản lý lỗi

Chi tiết cách quản lý lỗi cho dự án này. Chi tiết công cụ quản lý lỗi nào sẽ được sử dụng.

**Công cụ quản lý lỗi:** JIRA

**Quy trình báo cáo lỗi:**

1. **Bug Discovery:** Tester phát hiện bug, reproduce bug, document steps to reproduce
2. **Bug Reporting:** Log bug vào JIRA với đầy đủ thông tin (severity, priority, description, steps, expected vs actual, screenshots)
3. **Bug Triage:** Test Lead review bug, assign severity và priority, estimate fix time
4. **Bug Fixing:** Developer fix bug, mark as "Ready for Test", notify tester
5. **Bug Verification:** Tester verify fix, re-test affected areas, close bug hoặc reopen nếu không fix

**Severity Levels:**
- **Critical:** System crash, data loss, security breach - Fix within 4 hours
- **High:** Major functionality broken - Fix within 24 hours
- **Medium:** Minor functionality broken - Fix within 48 hours
- **Low:** Cosmetic issues, typos - Fix within 1 week

**Priority Levels:**
- **P1:** Must fix before release
- **P2:** Should fix before release if possible
- **P3:** Can fix in next release
- **P4:** Nice to have

**Bug Metrics:**
- Track: Total bugs found, bugs by severity, bugs by module, fix time
- Report: Daily bug report, weekly summary
- Target: <10% bugs reopen rate

---

## 10. Định nghĩa

Các từ viết tắt và thuật ngữ sau đây đã được sử dụng trong toàn bộ tài liệu này.

| Thuật ngữ/Từ viết tắt | Định nghĩa/Mô tả |
|----------------------|-----------------|
| Unit Testing | Kiểm thử ở mức độ function/method, kiểm tra từng đơn vị code độc lập |
| Integration Testing | Kiểm thử tích hợp giữa các module hoặc với external systems |
| System Testing | Kiểm thử toàn bộ hệ thống như một complete entity |
| UAT | User Acceptance Testing - Kiểm thử chấp nhận bởi end-user để xác nhận hệ thống đáp ứng business requirements |
| Smoke Testing | Kiểm thử nhanh các chức năng cốt lõi để xác nhận hệ thống stable |
| Regression Testing | Kiểm thử lại sau khi fix bug hoặc thêm tính năng để đảm bảo không ảnh hưởng chức năng cũ |
| Functional Testing | Kiểm thử chức năng theo requirements |
| Non-Functional Testing | Kiểm thử các thuộc tính phi chức năng như performance, security, usability |
| Load Testing | Kiểm thử hệ thống dưới tải bình thường |
| Stress Testing | Kiểm thử hệ thống dưới tải vượt quá giới hạn |
| Volume Testing | Kiểm thử hệ thống với lượng dữ liệu lớn |
| Security Testing | Kiểm thử lỗ hổng bảo mật |
| Performance Testing | Kiểm thử hiệu suất hệ thống (response time, throughput, scalability) |
| Test Case | Tập hợp các steps, inputs, expected results để kiểm thử một chức năng cụ thể |
| Test Suite | Tập hợp các test cases liên quan |
| Bug | Defect hoặc error trong hệ thống khiến chức năng không hoạt động đúng |
| Severity | Mức độ nghiêm trọng của bug (impact trên system) |
| Priority | Mức độ ưu tiên fix bug (business importance) |
| Coverage | Tỷ lệ code được test |
| Mock Object | Object giả lập để thay thế real object trong testing |
| Stub | Function giả lập trả về predefined values |
| API | Application Programming Interface - Interface cho phép các ứng dụng giao tiếp với nhau |
| Endpoint | URL cụ thể của API |
| Callback | Function được gọi sau khi một event xảy ra (ví dụ: payment callback) |
| Sandbox | Môi trường test cho external services |
| CI/CD | Continuous Integration/Continuous Deployment - Quy trình tự động hóa build, test, deploy |
| KPI | Key Performance Indicator - Chỉ số hiệu suất chính |
| P1, P2, P3, P4 | Mức độ ưu tiên (Priority 1-4) |
| SQL Injection | Loại attack inject malicious SQL code |
| XSS | Cross-Site Scripting - Loại attack inject malicious scripts |
| CSRF | Cross-Site Request Forgery - Loại attack force user execute unwanted actions |
| OWASP | Open Web Application Security Project |
| VNPay | Cổng thanh toán điện tử Việt Nam |
| MoMo | Ví điện tử MoMo |
| GHN | Giao Hàng Nhanh - Dịch vụ vận chuyển |

---

## 11. References

The following documents have been used to assist in creation of this document.

| # | Document name | Version | Comments |
|---|---------------|---------|---------|
| 1 | WowFood Requirements Document v1.0 | 1.0 | Yêu cầu chức năng và phi chức năng |
| 2 | WowFood Technical Design Document | 1.0 | Thiết kế kỹ thuật hệ thống |
| 3 | WowFood API Documentation | 1.0 | Tài liệu API endpoints |
| 4 | WowFood Database Schema | 1.0 | Schema database |
| 5 | WowFood User Stories | 1.0 | User stories và acceptance criteria |
| 6 | OWASP Testing Guide v4.2 | 4.2 | Guide cho security testing |
| 7 | ISTQB Foundation Level Syllabus | 2018 | Standard cho testing |
| 8 | PHP Documentation | 8.1 | Tài liệu PHP |
| 9 | MySQL Documentation | 8.0 | Tài liệu MySQL |
| 10 | VNPay Integration Guide | Latest | Guide tích hợp VNPay |
| 11 | MoMo Integration Guide | Latest | Guide tích hợp MoMo |
| 12 | GHN API Documentation | Latest | Tài liệu GHN API |
| 13 | ISO/IEC 25010 | 2011 | Software Quality Model |
| 14 | IEEE 829 | 2008 | Test Documentation Standard |
| 15 | PHPUnit Documentation | 9.5 | Tài liệu PHPUnit |
| 16 | Postman Documentation | Latest | Tài liệu Postman |
| 17 | Selenium WebDriver Documentation | 4.0 | Tài liệu Selenium |
| 18 | JMeter User Manual | 5.5 | Tài liệu JMeter |
| 19 | OWASP ZAP User Guide | 2.11 | Tài liệu OWASP ZAP |

---

## 12. Points of Contact

The following people can be contacted in reference to this document.

**Primary Contact**

| Name | Title/Organisation | Phone | Email |
|------|-------------------|-------|-------|
| [Test Lead Name] | Test Lead / WowFood Project Team | [Phone] | [Email] |

**Secondary Contact**

| Name | Title/Organisation | Phone | Email |
|------|-------------------|-------|-------|
| [Project Manager Name] | Project Manager / WowFood Project Team | [Phone] | [Email] |

**Additional Contacts**

| Name | Title/Organisation | Phone | Email |
|------|-------------------|-------|-------|
| [QA Engineer Name] | QA Engineer / WowFood Project Team | [Phone] | [Email] |
| [Developer Lead Name] | Technical Lead / WowFood Project Team | [Phone] | [Email] |
| [Stakeholder Name] | Business Owner / Customer | [Phone] | [Email] |

---

## Kết luận

Kế hoạch kiểm thử này cung cấp chiến lược toàn diện và chi tiết cho việc kiểm thử hệ thống WowFood v1.0. Việc thực hiện nghiêm ngặt kế hoạch này sẽ đảm bảo:

- Chất lượng sản phẩm đạt tiêu chuẩn cao
- Tính bảo mật và an toàn dữ liệu được đảm bảo
- Hiệu suất hệ thống ổn định và có khả năng mở rộng
- Trải nghiệm người dùng tốt và đáp ứng business requirements
- Giảm thiểu rủi ro khi triển khai production
- Tuân thủ timeline và budget

Kế hoạch kiểm thử cần được review và cập nhật thường xuyên theo sự thay đổi của requirements, timeline, và resources. Communication hiệu quả giữa các team members là yếu tố then chốt để thành công.

---

**Document Information:**
- Version: 1.0
- Date: May 25, 2026
- Author: Test Team
- Approved by: [Project Manager Name]
- Next Review Date: [Date]
