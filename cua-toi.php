<?php
require_once __DIR__ . '/../includes/init.php';
/** @var PDO $pdo */
require_role('nguoidan');

$nguoiDungHienTai = current_user();
$nguoiTiemId = (int)($nguoiDungHienTai['nguoi_tiem_id'] ?? 0);

if ($nguoiTiemId <= 0) {
    flash('Tài khoản chưa gắn với hồ sơ người tiêm. Vui lòng liên hệ trung tâm.', 'danger');
    redirect('dashboard.php');
}
$cauLenhLichHen = $pdo->prepare(
    'SELECT lh.*, vx.ten_vac_xin
     FROM lich_hen lh
     JOIN vac_xin vx ON vx.id = lh.vac_xin_id
     WHERE lh.nguoi_tiem_id = ?
     ORDER BY lh.ngay_hen DESC, lh.id DESC'
);
$cauLenhLichHen->execute([$nguoiTiemId]);
$danhSachLichHen = $cauLenhLichHen->fetchAll();

$title = 'Lịch hẹn của tôi';
require __DIR__ . '/../includes/header.php';
?>
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <div>
        <div class="text-primary fw-bold small text-uppercase">TV4 / NGƯỜI DÂN / LỊCH HẸN CỦA TÔI</div>
        <h3 class="fw-bold mb-0"><?= e($title) ?></h3>
        <div class="text-muted small">
            <?= e($nguoiDungHienTai['ho_ten']) ?> &bull; Chỉ xem và hủy lịch của bản thân theo trạng thái cho phép.
        </div>
    </div>
    <a href="dat-lich.php" class="btn btn-primary">+ Đặt lịch tiêm</a>
</div>

<div class="alert alert-info">
    Bạn chỉ có thể hủy lịch Chờ xác nhận. Với lịch Đã xác nhận, vui lòng liên hệ trung tâm nếu cần hỗ trợ.
</div>


<div class="card shadow-sm mb-3">
    <div class="card-body">
        <h5 class="card-title fw-bold mb-1">Các lịch hẹn của bạn</h5>
        <p class="text-muted small mb-3">
            <?= count($danhSachLichHen) ?> lịch hẹn &bull; <?= e($nguoiDungHienTai['ho_ten']) ?>
        </p>

        <div class="text-primary small mb-2 d-md-none">&harr; Cuộn ngang để xem đầy đủ bảng</div>

        <div class="table-responsive">
            <table class="table table-bordered bg-white align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Vắc xin</th>
                        <th>Ngày hẹn</th>
                        <th>Buổi</th>
                        <th>Trạng thái</th>
                        <th>Ghi chú</th>
                        <th>Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!$danhSachLichHen): ?>
                        <tr>
                            <td colspan="6" class="text-center text-muted py-3">
                                Bạn chưa có lịch hẹn nào
                            </td>
                        </tr>
                    <?php endif; ?>

                    <?php foreach ($danhSachLichHen as $lichHen):
                        $thongTinTrangThai = TRANG_THAI_LICH[$lichHen['trang_thai']];
                        $tenTrangThai = $thongTinTrangThai[0];
                        $mauTrangThai = $thongTinTrangThai[1];
                        $maLichHienThi = 'LH' . str_pad((string)$lichHen['id'], 3, '0', STR_PAD_LEFT);
                    ?>
                        <tr>
                            <td class="fw-semibold"><?= e($lichHen['ten_vac_xin']) ?></td>
                            <td><?= ngay_vn($lichHen['ngay_hen']) ?></td>
                            <td><?= e($lichHen['buoi']) ?></td>
                            <td>
                                <span class="badge bg-<?= e($mauTrangThai) ?>">
                                    <?= e($tenTrangThai) ?>
                                </span>
                            </td>
                            <td>
                                <span class="text-muted"><?= e($maLichHienThi) ?></span>
                                <?php if (!empty($lichHen['ghi_chu'])): ?>
                                    &bull; <?= e($lichHen['ghi_chu']) ?>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($lichHen['trang_thai'] === 'cho_xac_nhan'): ?>
                                    <form method="post" action="capnhat.php" class="d-inline"
                                          onsubmit="return confirm('Hủy lịch hẹn này?')">
                                        <?php csrf_field(); ?>
                                        <input type="hidden" name="id" value="<?= (int)$lichHen['id'] ?>">
                                        <input type="hidden" name="hanh_dong" value="huy">
                                        <button type="submit" class="btn btn-sm btn-outline-danger">Hủy</button>
                                    </form>
                                <?php else: ?>
                                    <span class="text-muted">&mdash;</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require __DIR__ . '/../includes/footer.php'; ?>