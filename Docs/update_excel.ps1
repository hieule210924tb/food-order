# Script cập nhật file Excel hiện tại với 4 sheet theo nhóm chức năng
$csvPath = "e:\Xampp\htdocs\PHP\Do_An_Cnpm\Docs\TEST_CASE_SIMPLE.csv"
$xlsxPath = "e:\Xampp\htdocs\PHP\Do_An_Cnpm\Docs\TEST_CASE_WOWFOOD.xlsx"

# Đọc dữ liệu CSV
$data = Import-Csv $csvPath -Encoding UTF8
Write-Host "Đã đọc $($data.Count) test case từ CSV"

# Nhóm dữ liệu theo cột "Nhóm chức năng"
$groups = $data | Group-Object -Property "Nhóm chức năng"
Write-Host "Phát hiện $($groups.Count) nhóm chức năng"

# Mở file Excel hiện tại
$excel = New-Object -ComObject Excel.Application
$excel.Visible = $false
$workbook = $excel.Workbooks.Open($xlsxPath)

# Xóa tất cả sheet hiện tại
$sheetCount = $workbook.Sheets.Count
for ($i = $sheetCount; $i -ge 1; $i--) {
    $sheet = $workbook.Sheets.Item($i)
    $sheet.Delete()
}
Write-Host "Đã xóa $sheetCount sheet cũ"

# Tạo sheet cho mỗi nhóm chức năng
foreach ($group in $groups) {
    $groupName = $group.Name
    $groupData = $group.Group
    
    # Tạo tên sheet hợp lệ (max 31 ký tự, không có ký tự đặc biệt)
    $sheetName = $groupName -replace '[\\/:*?\[\]]', ''
    if ($sheetName.Length -gt 31) {
        $sheetName = $sheetName.Substring(0, 31)
    }
    
    # Tạo sheet mới
    $sheet = $workbook.Sheets.Add()
    $sheet.Name = $sheetName
    
    # Điền header
    $sheet.Cells.Item(1, 1).Value = "ID"
    $sheet.Cells.Item(1, 2).Value = "Test Case Description"
    $sheet.Cells.Item(1, 3).Value = "Pre-condition"
    $sheet.Cells.Item(1, 4).Value = "Test Case Procedure"
    $sheet.Cells.Item(1, 5).Value = "Expected Output"
    $sheet.Cells.Item(1, 6).Value = "Result"
    $sheet.Cells.Item(1, 7).Value = "Test date"
    $sheet.Cells.Item(1, 8).Value = "Tester"
    $sheet.Cells.Item(1, 9).Value = "Note"
    
    # Điền dữ liệu cho từng test case trong nhóm
    $row = 2
    foreach ($item in $groupData) {
        $sheet.Cells.Item($row, 1).Value = $item."ID"
        $sheet.Cells.Item($row, 2).Value = $item."Test Case Description"
        $sheet.Cells.Item($row, 3).Value = $item."Pre-condition"
        $sheet.Cells.Item($row, 4).Value = $item."Test Case Procedure"
        $sheet.Cells.Item($row, 5).Value = $item."Expected Output"
        $sheet.Cells.Item($row, 6).Value = $item."Result"
        $sheet.Cells.Item($row, 7).Value = $item."Test date"
        $sheet.Cells.Item($row, 8).Value = $item."Tester"
        $sheet.Cells.Item($row, 9).Value = $item."Note"
        $row++
    }
    
    # Format
    $sheet.Columns.AutoFit() | Out-Null
    $sheet.Rows.Item(1).Font.Bold = $true
    $sheet.Rows.Item(1).Interior.Color = 0x1F4E79
    $sheet.Rows.Item(1).Font.Color = 0xFFFFFF
    
    # Auto-fit height cho các dòng dữ liệu
    $sheet.UsedRange.Rows.AutoFit() | Out-Null
    
    Write-Host "Đã tạo sheet: $sheetName với $($groupData.Count) test case"
}

# Lưu file Excel
$workbook.Save()
$workbook.Close()
$excel.Quit()
[System.Runtime.Interopservices.Marshal]::ReleaseComObject($excel) | Out-Null

Write-Host "================================================"
Write-Host "Đã cập nhật file Excel thành công!"
Write-Host "File: $xlsxPath"
Write-Host "Số lượng sheet: $($groups.Count)"
Write-Host "================================================"
