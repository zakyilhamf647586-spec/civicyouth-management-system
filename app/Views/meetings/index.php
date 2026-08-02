<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<?php
$canCreateMeetings = auth_can('meetings.create');
$canUpdateMeetings = auth_can('meetings.update');
$canDeleteMeetings = auth_can('meetings.delete');
$canViewAttendanceRecap = auth_can('attendances.recap');
$hasMeetingRowActions = $canViewAttendanceRecap
    || $canUpdateMeetings
    || $canDeleteMeetings;
?>

<div class="page-header">
    <div>
        <h2>Agenda Rapat</h2>
        <p>Kelola jadwal, pembahasan, keputusan, dan catatan rapat organisasi.</p>
    </div>

    <?php if ($canCreateMeetings) : ?>
        <a href="<?= base_url('/meetings/create') ?>" class="btn btn-primary">+ Tambah Rapat</a>
    <?php endif; ?>
</div>

<?php if (session()->getFlashdata('success')) : ?>
    <div class="alert-success">
        <?= session()->getFlashdata('success') ?>
    </div>
<?php endif; ?>

<?php if (session()->getFlashdata('error')) : ?>
    <div class="alert-error">
        <?= session()->getFlashdata('error') ?>
    </div>
<?php endif; ?>

<div class="filter-card">
    <form action="<?= base_url('/meetings') ?>" method="get">
        <div class="filter-grid meeting-filter-grid">
            <div class="form-group">
                <label>Cari Rapat</label>
                <input
                    type="text"
                    name="keyword"
                    value="<?= esc($keyword ?? '') ?>"
                    placeholder="Cari judul, tempat, agenda, keputusan, atau catatan"
                >
            </div>

            <div class="form-group">
                <label>Status</label>
                <select name="status">
                    <option value="">Semua Status</option>
                    <option value="scheduled" <?= ($status ?? '') === 'scheduled' ? 'selected' : '' ?>>Terjadwal</option>
                    <option value="completed" <?= ($status ?? '') === 'completed' ? 'selected' : '' ?>>Selesai</option>
                    <option value="cancelled" <?= ($status ?? '') === 'cancelled' ? 'selected' : '' ?>>Dibatalkan</option>
                </select>
            </div>

            <div class="form-group">
                <label>Dari Tanggal</label>
                <input type="date" name="date_from" value="<?= esc($date_from ?? '') ?>">
            </div>

            <div class="form-group">
                <label>Sampai Tanggal</label>
                <input type="date" name="date_to" value="<?= esc($date_to ?? '') ?>">
            </div>

            <div class="filter-actions">
                <button type="submit" class="btn btn-primary">Terapkan</button>
                <a href="<?= base_url('/meetings') ?>" class="btn btn-secondary">Reset</a>
            </div>
        </div>
    </form>
</div>

<div class="table-card">
    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Judul Rapat</th>
                <th>Tanggal</th>
                <th>Waktu</th>
                <th>Tempat</th>
                <th>Status</th>
                <?php if ($hasMeetingRowActions) : ?>
                    <th width="250">Aksi</th>
                <?php endif; ?>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($meetings)) : ?>
                <?php
                    $currentPage = $pager->getCurrentPage('meetings');
                    $perPage     = $pager->getPerPage('meetings');
                    $no          = 1 + ($perPage * ($currentPage - 1));
                ?>

                <?php foreach ($meetings as $meeting) : ?>
                    <tr>
                        <td><?= $no++ ?></td>
                        <td>
                            <strong><?= esc($meeting['title']) ?></strong><br>
                            <small>
                                <?= esc($meeting['agenda'] ? substr($meeting['agenda'], 0, 90) . '...' : '-') ?>
                            </small>
                        </td>
                        <td><?= date('d M Y', strtotime($meeting['meeting_date'])) ?></td>
                        <td>
                            <?= $meeting['start_time'] ? esc(substr($meeting['start_time'], 0, 5)) : '-' ?>
                            -
                            <?= $meeting['end_time'] ? esc(substr($meeting['end_time'], 0, 5)) : '-' ?>
                        </td>
                        <td><?= esc($meeting['location'] ?? '-') ?></td>
                        <td>
                            <?php if ($meeting['status'] === 'scheduled') : ?>
                                <span class="badge badge-warning">Terjadwal</span>
                            <?php elseif ($meeting['status'] === 'completed') : ?>
                                <span class="badge badge-success">Selesai</span>
                            <?php else : ?>
                                <span class="badge badge-danger">Dibatalkan</span>
                            <?php endif; ?>
                        </td>
                        <?php if ($hasMeetingRowActions) : ?>
                            <td>
                                <?php if ($canViewAttendanceRecap) : ?>
                                    <a href="<?= base_url('/attendances/recap/' . $meeting['id']) ?>" class="btn btn-primary">Rekap</a>
                                <?php endif; ?>

                                <?php if ($canUpdateMeetings) : ?>
                                    <a href="<?= base_url('/meetings/edit/' . $meeting['id']) ?>" class="btn btn-warning">Edit</a>
                                <?php endif; ?>

                                <?php if ($canDeleteMeetings) : ?>
                                    <form
                                        action="<?= base_url('/meetings/delete/' . $meeting['id']) ?>"
                                        method="post"
                                        class="inline-action-form"
                                        onsubmit="return confirm('Yakin ingin menghapus agenda rapat ini?')"
                                    >
                                        <?= csrf_field() ?>
                                        <button type="submit" class="btn btn-danger">
                                            Hapus
                                        </button>
                                    </form>
                                <?php endif; ?>
                            </td>
                        <?php endif; ?>
                    </tr>
                <?php endforeach; ?>
            <?php else : ?>
                <tr>
                    <td colspan="<?= $hasMeetingRowActions ? 7 : 6 ?>" class="empty">Data rapat tidak ditemukan.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <div class="pagination-wrapper">
        <?= $pager->only(['keyword', 'status', 'date_from', 'date_to'])->links('meetings', 'default_full') ?>
    </div>
</div>

<?= $this->endSection() ?>