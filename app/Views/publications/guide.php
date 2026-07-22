<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<?= $this->include('publications/_assets') ?>

<div class="publication-admin-page publication-guide-page">

<div class="page-header publication-page-header">
    <div>
        <span class="publication-eyebrow">
            Panduan Pengurus
        </span>

        <h2>Memahami Publikasi Sosial</h2>

        <p>
            Panduan satu halaman untuk membedakan website publik,
            pekerjaan Canva, dan konten Instagram.
        </p>
    </div>

    <a
        href="<?= base_url('/publications') ?>"
        class="btn btn-primary"
    >
        Kembali ke Publikasi
    </a>
</div>

<section class="publication-guide-clarity">
    <article>
        <span>Website Publik</span>
        <h3>Data Kegiatan</h3>
        <p>
            Digunakan untuk berita, dokumentasi kegiatan, galeri,
            dan informasi yang tampil pada halaman publik website.
        </p>

        <a
            href="<?= base_url('/activities') ?>"
            class="btn btn-secondary"
        >
            Buka Data Kegiatan
        </a>
    </article>

    <div aria-hidden="true">≠</div>

    <article>
        <span>Instagram</span>
        <h3>Publikasi Sosial</h3>
        <p>
            Digunakan untuk brief Instagram, caption, master Canva,
            review, jadwal, tautan tayang, dan performa konten.
        </p>

        <a
            href="<?= base_url('/publications') ?>"
            class="btn btn-secondary"
        >
            Buka Publikasi Sosial
        </a>
    </article>
</section>

<section class="publication-guide-warning">
    <strong>Tidak ada proses posting otomatis.</strong>

    <p>
        Portal membantu mengelola pekerjaan. Pengurus tetap
        menduplikasi desain di Canva, mengunggahnya ke Instagram
        secara manual, lalu mencatat tautan hasil tayangnya.
    </p>
</section>

<section class="publication-guide-process">
    <div>
        <span>Alur Kerja</span>
        <h3>Dari kegiatan sampai Instagram</h3>
    </div>

    <ol>
        <li>
            <span>1</span>
            <div>
                <strong>Catat kegiatan</strong>
                <p>
                    Masukkan kegiatan ke Data Kegiatan agar dapat
                    dikelola dan—jika diterbitkan—tampil di website.
                </p>
            </div>
        </li>

        <li>
            <span>2</span>
            <div>
                <strong>Buat konten Instagram</strong>
                <p>
                    Dari Publikasi Sosial, pilih kegiatan tersebut
                    lalu buat brief otomatis.
                </p>
            </div>
        </li>

        <li>
            <span>3</span>
            <div>
                <strong>Kerjakan di Canva</strong>
                <p>
                    Buka master, buat salinan, edit desain, lalu
                    simpan tautan desain kerja ke Portal.
                </p>
            </div>
        </li>

        <li>
            <span>4</span>
            <div>
                <strong>Review dan jadwalkan</strong>
                <p>
                    Periksa naskah, desain, foto, dan waktu tayang
                    sebelum konten dinyatakan siap.
                </p>
            </div>
        </li>

        <li>
            <span>5</span>
            <div>
                <strong>Posting manual</strong>
                <p>
                    Unggah ke Instagram, salin URL postingan, lalu
                    tandai sebagai Dipublikasikan di Portal.
                </p>
            </div>
        </li>
    </ol>
</section>

<section class="publication-guide-statuses">
    <div>
        <span>Arti Status</span>
        <h3>Baca status sebagai petunjuk tindakan berikutnya</h3>
    </div>

    <div>
        <?php foreach (
            $workflowStatuses as $value => $label
        ) : ?>
            <article>
                <span
                    class="publication-status publication-status--<?= esc(
                        $value,
                        'attr'
                    ) ?>"
                >
                    <?= esc($label) ?>
                </span>

                <p>
                    <?= esc(
                        $workflowDescriptions[$value]
                        ?? 'Lanjutkan sesuai alur kerja.'
                    ) ?>
                </p>
            </article>
        <?php endforeach; ?>
    </div>
</section>

<section class="publication-guide-when">
    <div>
        <span>Keputusan Cepat</span>
        <h3>Kapan memakai modul yang mana?</h3>
    </div>

    <div>
        <article>
            <strong>
                “Saya ingin kegiatan tampil di website.”
            </strong>
            <p>
                Gunakan Data Kegiatan dan pastikan status
                publikasinya aktif.
            </p>
        </article>

        <article>
            <strong>
                “Saya ingin membuat feed atau carousel.”
            </strong>
            <p>
                Gunakan Publikasi Sosial, pilih master Canva, lalu
                kerjakan desainnya.
            </p>
        </article>

        <article>
            <strong>
                “Saya sudah selesai membuat desain.”
            </strong>
            <p>
                Tempel tautan Canva kerja, kirim untuk review, lalu
                tentukan target tayang.
            </p>
        </article>

        <article>
            <strong>
                “Konten sudah diunggah ke Instagram.”
            </strong>
            <p>
                Simpan URL Instagram dan ubah status menjadi
                Dipublikasikan.
            </p>
        </article>
    </div>
</section>

</div>

<?= $this->endSection() ?>
