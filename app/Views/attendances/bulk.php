<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<div class="page-header">
    <div>
        <h2>Input Absensi Massal</h2>
        <p>Catat kehadiran seluruh anggota aktif dalam satu halaman.</p>
    </div>

    <a href="<?= base_url('/attendances/recap/' . $meeting['id']) ?>" class="btn btn-secondary">Kembali ke Rekap</a>
</div>

<?php if (session()->getFlashdata('error')) : ?>
    <div class="alert-error">
        <?= session()->getFlashdata('error') ?>
    </div>
<?php endif; ?>

<div class="section">
    <h3><?= esc($meeting['title']) ?></h3>
    <p>
        <strong>Tanggal:</strong> <?= date('d M Y', strtotime($meeting['meeting_date'])) ?><br>
        <strong>Waktu:</strong>
        <?= $meeting['start_time'] ? esc(substr($meeting['start_time'], 0, 5)) : '-' ?>
        -
        <?= $meeting['end_time'] ? esc(substr($meeting['end_time'], 0, 5)) : '-' ?><br>
        <strong>Tempat:</strong> <?= esc($meeting['location'] ?? '-') ?>
    </p>
</div>

<br>

<div class="bulk-actions">
    <button type="button" class="btn btn-primary" onclick="setAllAttendance('present')">Set Semua Hadir</button>
    <button type="button" class="btn btn-warning" onclick="setAllAttendance('permission')">Set Semua Izin</button>
    <button type="button" class="btn btn-danger" onclick="setAllAttendance('absent')">Set Semua Tidak Hadir</button>
    <button type="button" class="btn btn-secondary" onclick="resetAttendance()">Reset Pilihan</button>
</div>

<br>

<form action="<?= base_url('/attendances/bulk-save/' . $meeting['id']) ?>" method="post">
    <?= csrf_field() ?>

    <div class="table-card">
        <table>
            <thead>
                <tr>
                    <th>No</th>
                    <th>Nama Anggota</th>
                    <th>RT</th>
                    <th>Jabatan/Posisi</th>
                    <th>Status Kehadiran</th>
                    <th>Catatan</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($members)) : ?>
                    <?php $no = 1; foreach ($members as $member) : ?>
                        <tr>
                            <td><?= $no++ ?></td>
                            <td>
                                <strong><?= esc($member['full_name']) ?></strong>
                                <?php if (!empty($member['attendance_id'])) : ?>
                                    <br><small class="text-muted">Sudah tercatat sebelumnya</small>
                                <?php endif; ?>
                            </td>
                            <td><?= esc($member['rt'] ?? '-') ?></td>
                            <td><?= esc($member['position'] ?? '-') ?></td>
                            <td>
                                <select
                                    name="attendance_status[<?= $member['member_id'] ?>]"
                                    class="attendance-select"
                                >
                                    <option value="">Belum Dicatat</option>
                                    <option value="present" <?= ($member['attendance_status'] ?? '') === 'present' ? 'selected' : '' ?>>
                                        Hadir
                                    </option>
                                    <option value="permission" <?= ($member['attendance_status'] ?? '') === 'permission' ? 'selected' : '' ?>>
                                        Izin
                                    </option>
                                    <option value="absent" <?= ($member['attendance_status'] ?? '') === 'absent' ? 'selected' : '' ?>>
                                        Tidak Hadir
                                    </option>
                                </select>
                            </td>
                            <td>
                                <input
                                    type="text"
                                    name="note[<?= $member['member_id'] ?>]"
                                    value="<?= esc($member['note'] ?? '') ?>"
                                    placeholder="Catatan jika diperlukan"
                                >
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else : ?>
                    <tr>
                        <td colspan="6" class="empty">Belum ada anggota aktif.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <br>

    <button type="submit" class="btn btn-primary">Simpan Absensi Massal</button>
    <a href="<?= base_url('/attendances/recap/' . $meeting['id']) ?>" class="btn btn-secondary">Batal</a>
</form>

<script>
    function setAllAttendance(status) {
        const selects = document.querySelectorAll('.attendance-select');

        selects.forEach(function(select) {
            select.value = status;
        });
    }

    function resetAttendance() {
        const selects = document.querySelectorAll('.attendance-select');

        selects.forEach(function(select) {
            select.value = '';
        });
    }
</script>

<?= $this->endSection() ?>