<?php
/**
 * Bước 3: Chọn địa chỉ giao hàng – Danh sách Tỉnh / Quận / Phường từ GHN.
 * GET: action=province | district | ward; province_id (khi action=district); district_id (khi action=ward)
 */
ob_start();
require_once __DIR__ . '/../config/constants.php';
ob_end_clean();

header('Content-Type: application/json; charset=utf-8');

$action = isset($_GET['action']) ? trim(strtolower($_GET['action'])) : '';
if (!in_array($action, ['province', 'district', 'ward'], true)) {
    echo json_encode(['success' => false, 'data' => [], 'message' => 'Thiếu hoặc sai action (province|district|ward).']);
    exit;
}

if (!defined('GHN_TOKEN') || GHN_TOKEN === '') {
    echo json_encode(['success' => false, 'data' => [], 'message' => 'Chưa cấu hình GHN.']);
    exit;
}

$base = defined('GHN_API_BASE') ? GHN_API_BASE : 'https://dev-online-gateway.ghn.vn/shiip/public-api';
$headers = ['Token: ' . GHN_TOKEN, 'Content-Type: application/json'];

if ($action === 'province') {
    $url = $base . '/master-data/province';
    $ch = curl_init($url);
    curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER => true, CURLOPT_HTTPHEADER => $headers]);
    $response = curl_exec($ch);
    curl_close($ch);
    if ($response === false) { echo json_encode(['success' => false, 'data' => [], 'message' => 'Không kết nối được GHN.']); exit; }
    $data = json_decode($response, true);
    if (!is_array($data) || (int)($data['code'] ?? 0) !== 200 || !isset($data['data'])) {
        echo json_encode(['success' => false, 'data' => [], 'message' => $data['message'] ?? 'Lỗi GHN.']); exit;
    }
    $list = is_array($data['data']) ? $data['data'] : (is_object($data['data']) ? (array)$data['data'] : []);
    if (is_object($list) || (is_array($list) && isset($list[0]) === false)) $list = array_values((array)$list);
    echo json_encode(['success' => true, 'data' => $list]); exit;
}

if ($action === 'district') {
    $province_id = isset($_GET['province_id']) ? (int) $_GET['province_id'] : 0;
    if ($province_id <= 0) { echo json_encode(['success' => false, 'data' => [], 'message' => 'Thiếu province_id.']); exit; }
    $url = $base . '/master-data/district';
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true, CURLOPT_POST => true, CURLOPT_HTTPHEADER => $headers,
        CURLOPT_POSTFIELDS => json_encode(['province_id' => $province_id]),
    ]);
    $response = curl_exec($ch);
    curl_close($ch);
    if ($response === false) { echo json_encode(['success' => false, 'data' => [], 'message' => 'Không kết nối được GHN.']); exit; }
    $data = json_decode($response, true);
    if (!is_array($data) || (int)($data['code'] ?? 0) !== 200 || !isset($data['data'])) {
        echo json_encode(['success' => false, 'data' => [], 'message' => $data['message'] ?? 'Lỗi GHN.']); exit;
    }
    $list = is_array($data['data']) ? $data['data'] : array_values((array)$data['data']);
    echo json_encode(['success' => true, 'data' => $list]); exit;
}

if ($action === 'ward') {
    $district_id = isset($_GET['district_id']) ? (int) $_GET['district_id'] : 0;
    if ($district_id <= 0) { echo json_encode(['success' => false, 'data' => [], 'message' => 'Thiếu district_id.']); exit; }
    $url = $base . '/master-data/ward?district_id=' . $district_id;
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true, CURLOPT_POST => true, CURLOPT_HTTPHEADER => $headers,
        CURLOPT_POSTFIELDS => json_encode(['district_id' => $district_id]),
    ]);
    $response = curl_exec($ch);
    curl_close($ch);
    if ($response === false) { echo json_encode(['success' => false, 'data' => [], 'message' => 'Không kết nối được GHN.']); exit; }
    $data = json_decode($response, true);
    if (!is_array($data) || (int)($data['code'] ?? 0) !== 200 || !isset($data['data'])) {
        echo json_encode(['success' => false, 'data' => [], 'message' => $data['message'] ?? 'Lỗi GHN.']); exit;
    }
    $raw = $data['data'];
    if (is_array($raw)) {
        $list = $raw;
    } elseif (is_object($raw)) {
        $arr = (array) $raw;
        if (isset($arr['WardCode']) || isset($arr['ward_code'])) {
            $list = [$arr];
        } else {
            $list = array_values($arr);
        }
    } else {
        $list = [];
    }
    echo json_encode(['success' => true, 'data' => $list]); exit;
}
