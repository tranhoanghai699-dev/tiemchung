<?php
require_once __DIR__ . '/../includes/init.php';
/** @var PDO $pdo */
require_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('dashboard.php');
}

$nguoiDungHienTai = current_user();
$trangQuayVe = $nguoiDungHienTai['vai_tro'] === 'nguoidan'
    ? 'lichhen/cua-toi.php'
    : 'lichhen/index.php';

$lichHenId = input_id($_POST, 'id');
$hanhDong = post_text('hanh_dong');

$cauLenhTimLich = $pdo->prepare('SELECT * FROM lich_hen WHERE id = ?');
$cauLenhTimLich->execute([$lichHenId]);
$lichHenHienTai = $cauLenhTimLich->fetch();

if (!$lichHenHienTai) {
    flash('Không tìm thấy lịch hẹn.', 'danger');
    redirect($trangQuayVe);
}

$trangThaiMoi = null;
if ($nguoiDungHienTai['vai_tro'] === 'nguoidan') {
    if ($lichHenHienTai['nguoi_tiem_id'] == $nguoiDungHienTai['nguoi_tiem_id']
        && $hanhDong === 'huy'
        && $lichHenHienTai['trang_thai'] === 'cho_xac_nhan') {
        $trangThaiMoi = 'da_huy';
    }
} else {
    if ($hanhDong === 'xac_nhan' && $lichHenHienTai['trang_thai'] === 'cho_xac_nhan') {
        $trangThaiMoi = 'da_xac_nhan';
    }
    if ($hanhDong === 'huy' && in_array($lichHenHienTai['trang_thai'], ['cho_xac_nhan', 'da_xac_nhan'], true)) {
        $trangThaiMoi = 'da_huy';
    }
}

if (!$trangThaiMoi) {
    flash('Thao tác không hợp lệ.', 'danger');
    redirect($trangQuayVe);
}

$cauLenhCapNhat = $pdo->prepare('UPDATE lich_hen SET trang_thai = ? WHERE id = ? AND trang_thai = ?');
$cauLenhCapNhat->execute([$trangThaiMoi, $lichHenId, $lichHenHienTai['trang_thai']]);

if ($cauLenhCapNhat->rowCount() !== 1) {
    flash('Lịch đã thay đổi bởi thao tác khác. Hãy tải lại.', 'warning');
} else {
    $tenTrangThaiMoi = TRANG_THAI_LICH[$trangThaiMoi][0];
    flash('Đã cập nhật lịch hẹn: ' . $tenTrangThaiMoi . '.');
}

redirect($trangQuayVe);