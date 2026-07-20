<?php

namespace App\Controllers;

use CodeIgniter\Database\BaseConnection;

class DashboardController extends BaseController
{
    protected BaseConnection $db;

    public function __construct()
    {
        $this->db = db_connect();
    }

    public function index()
    {
        $activeMembers = $this->countActiveMembers();
        $membersWithoutPhone = $this->countMembersWithoutPhone();

        $finance = $this->getFinanceSummary();

        $nextMeeting = $this->getNextMeeting();
        $upcomingMeetings = $this->getUpcomingMeetings(3);

        $completedActivities = $this->countActivitiesByStatus([
            'completed',
            'selesai',
        ]);

        $missingActivityCovers =
            $this->countActivitiesWithoutCover();

        $publicationSummary =
            $this->getActivityPublicationSummary();

        $reviewQueue =
            $this->getActivityReviewQueue(5);

        $scheduledPublicationQueue =
            $this->getScheduledPublicationQueue(3);

        $activitiesWithoutProgram =
            $this->countActivitiesWithoutProgram();

        $publishedPrograms = $this->countProgramsByStatus([
            'published',
        ]);

        $draftPrograms = $this->countProgramsByStatus([
            'draft',
        ]);

        $unreadMessages = $this->countMessagesByStatus([
            'unread',
        ]);

        $attentionItems = $this->buildAttentionItems(
            $membersWithoutPhone,
            $missingActivityCovers,
            $unreadMessages,
            $draftPrograms,
            $publicationSummary,
            $activitiesWithoutProgram,
            $nextMeeting
        );

        return view('dashboard/index', [
            'title' => 'Ringkasan Organisasi',

            'activeMembers' => $activeMembers,

            'finance' => $finance,

            'nextMeeting' => $nextMeeting,

            'upcomingMeetings' => $upcomingMeetings,

            'completedActivities' => $completedActivities,

            'publishedPrograms' => $publishedPrograms,

            'draftPrograms' => $draftPrograms,

            'missingActivityCovers' =>
                $missingActivityCovers,

            'publicationSummary' =>
                $publicationSummary,

            'reviewQueue' => $reviewQueue,

            'scheduledPublicationQueue' =>
                $scheduledPublicationQueue,

            'activitiesWithoutProgram' =>
                $activitiesWithoutProgram,

            'unreadMessages' => $unreadMessages,

            'attentionItems' => $attentionItems,

            'recentActivities' =>
                $this->getRecentPortalActivities(),

            'quickActions' => [
                [
                    'label' => 'Tambah Anggota',
                    'description' =>
                        'Daftarkan anggota GARDA 01.',
                    'url' => '/members/create',
                    'icon' => 'icon-members',
                ],
                [
                    'label' => 'Catat Kegiatan',
                    'description' =>
                        'Tambahkan agenda atau dokumentasi.',
                    'url' => '/activities/create',
                    'icon' => 'icon-activity',
                ],
                [
                    'label' => 'Buat Rapat',
                    'description' =>
                        'Jadwalkan rapat organisasi.',
                    'url' => '/meetings/create',
                    'icon' => 'icon-meeting',
                ],
                [
                    'label' => 'Catat Transaksi',
                    'description' =>
                        'Tambahkan kas masuk atau keluar.',
                    'url' => '/cash/create',
                    'icon' => 'icon-cash',
                ],
                [
                    'label' => 'Buat Konten AI',
                    'description' =>
                        'Siapkan konten media sosial.',
                    'url' => '/content-studio',
                    'icon' => 'icon-ai',
                ],
                [
                    'label' => 'Lihat Website',
                    'description' =>
                        'Buka website publik GARDA 01.',
                    'url' => '/',
                    'icon' => 'icon-globe',
                    'newTab' => true,
                ],
            ],
        ]);
    }

    private function tableExists(string $table): bool
    {
        try {
            return $this->db->tableExists($table);
        } catch (\Throwable $e) {
            return false;
        }
    }

    private function getFields(string $table): array
    {
        if (!$this->tableExists($table)) {
            return [];
        }

        try {
            return $this->db->getFieldNames($table);
        } catch (\Throwable $e) {
            return [];
        }
    }

    private function firstExistingField(
        string $table,
        array $candidates
    ): ?string {
        $fields = $this->getFields($table);

        foreach ($candidates as $candidate) {
            if (in_array($candidate, $fields, true)) {
                return $candidate;
            }
        }

        return null;
    }

    private function countActiveMembers(): int
    {
        if (!$this->tableExists('members')) {
            return 0;
        }

        $statusField = $this->firstExistingField(
            'members',
            [
                'membership_status',
                'member_status',
                'status',
            ]
        );

        $builder = $this->db->table('members');

        if (!$statusField) {
            return $builder->countAllResults();
        }

        return $builder
            ->whereIn($statusField, [
                'active',
                'aktif',
            ])
            ->countAllResults();
    }

    private function countMembersWithoutPhone(): int
    {
        if (!$this->tableExists('members')) {
            return 0;
        }

        $phoneField = $this->firstExistingField(
            'members',
            [
                'phone',
                'phone_number',
                'whatsapp',
                'no_hp',
                'telephone',
            ]
        );

        if (!$phoneField) {
            return 0;
        }

        $statusField = $this->firstExistingField(
            'members',
            [
                'membership_status',
                'member_status',
                'status',
            ]
        );

        $builder = $this->db->table('members');

        if ($statusField) {
            $builder->whereIn($statusField, [
                'active',
                'aktif',
            ]);
        }

        return $builder
            ->groupStart()
            ->where(
                $phoneField . ' IS NULL',
                null,
                false
            )
            ->orWhere($phoneField, '')
            ->groupEnd()
            ->countAllResults();
    }

    private function getFinanceSummary(): array
    {
        $summary = [
            'balance' => 0,
            'totalIncome' => 0,
            'totalExpense' => 0,
            'monthlyIncome' => 0,
            'monthlyExpense' => 0,
        ];

        if (!$this->tableExists('cash_transactions')) {
            return $summary;
        }

        $amountField = $this->firstExistingField(
            'cash_transactions',
            [
                'amount',
                'nominal',
                'value',
            ]
        );

        $typeField = $this->firstExistingField(
            'cash_transactions',
            [
                'transaction_type',
                'type',
                'jenis',
            ]
        );

        $dateField = $this->firstExistingField(
            'cash_transactions',
            [
                'transaction_date',
                'date',
                'tanggal',
                'created_at',
            ]
        );

        if (!$amountField || !$typeField) {
            return $summary;
        }

        $rows = $this->db
            ->table('cash_transactions')
            ->get()
            ->getResultArray();

        $currentMonth = date('Y-m');

        foreach ($rows as $row) {
            $amount = $this->normalizeAmount(
                $row[$amountField] ?? 0
            );

            $type = mb_strtolower(
                trim((string) ($row[$typeField] ?? ''))
            );

            $isIncome = in_array($type, [
                'income',
                'pemasukan',
                'masuk',
                'credit',
                'kredit',
            ], true);

            $isExpense = in_array($type, [
                'expense',
                'pengeluaran',
                'keluar',
                'debit',
            ], true);

            if ($isIncome) {
                $summary['totalIncome'] += $amount;
            }

            if ($isExpense) {
                $summary['totalExpense'] += $amount;
            }

            $transactionDate = $dateField
                ? (string) ($row[$dateField] ?? '')
                : '';

            $isCurrentMonth =
                $transactionDate !== ''
                && substr($transactionDate, 0, 7)
                    === $currentMonth;

            if ($isCurrentMonth && $isIncome) {
                $summary['monthlyIncome'] += $amount;
            }

            if ($isCurrentMonth && $isExpense) {
                $summary['monthlyExpense'] += $amount;
            }
        }

        $summary['balance'] =
            $summary['totalIncome']
            - $summary['totalExpense'];

        return $summary;
    }

    private function normalizeAmount($value): float
    {
        if (is_numeric($value)) {
            return (float) $value;
        }

        $clean = preg_replace(
            '/[^0-9,.\-]/',
            '',
            (string) $value
        );

        if ($clean === null || $clean === '') {
            return 0;
        }

        if (
            str_contains($clean, '.')
            && str_contains($clean, ',')
        ) {
            $clean = str_replace('.', '', $clean);
            $clean = str_replace(',', '.', $clean);
        } elseif (
            str_contains($clean, '.')
            && preg_match('/\.\d{3}$/', $clean)
        ) {
            $clean = str_replace('.', '', $clean);
        } elseif (str_contains($clean, ',')) {
            $clean = str_replace(',', '.', $clean);
        }

        return (float) $clean;
    }

    private function getNextMeeting(): ?array
    {
        $meetings = $this->getUpcomingMeetings(1);

        return $meetings[0] ?? null;
    }

    private function getUpcomingMeetings(
        int $limit = 3
    ): array {
        if (!$this->tableExists('meetings')) {
            return [];
        }

        $dateField = $this->firstExistingField(
            'meetings',
            [
                'meeting_date',
                'schedule_date',
                'scheduled_at',
                'date',
                'tanggal',
            ]
        );

        if (!$dateField) {
            return [];
        }

        $titleField = $this->firstExistingField(
            'meetings',
            [
                'title',
                'meeting_title',
                'agenda',
                'topic',
                'name',
            ]
        );

        $locationField = $this->firstExistingField(
            'meetings',
            [
                'location',
                'place',
                'venue',
                'lokasi',
            ]
        );

        $statusField = $this->firstExistingField(
            'meetings',
            [
                'status',
                'meeting_status',
            ]
        );

        $builder = $this->db
            ->table('meetings')
            ->where(
                $dateField . ' >=',
                date('Y-m-d')
            )
            ->orderBy($dateField, 'ASC')
            ->limit($limit);

        $rows = $builder
            ->get()
            ->getResultArray();

        $result = [];

        foreach ($rows as $row) {
            $result[] = [
                'id' => $row['id'] ?? null,

                'title' => $titleField
                    ? ($row[$titleField]
                        ?? 'Rapat GARDA 01')
                    : 'Rapat GARDA 01',

                'date' => $row[$dateField] ?? null,

                'location' => $locationField
                    ? ($row[$locationField] ?? '-')
                    : '-',

                'status' => $statusField
                    ? ($row[$statusField]
                        ?? 'scheduled')
                    : 'scheduled',
            ];
        }

        return $result;
    }

    private function countActivitiesByStatus(
        array $statuses
    ): int {
        if (!$this->tableExists('activities')) {
            return 0;
        }

        $statusField = $this->firstExistingField(
            'activities',
            [
                'status',
                'activity_status',
            ]
        );

        $builder = $this->db->table('activities');

        if (!$statusField) {
            return $builder->countAllResults();
        }

        return $builder
            ->whereIn($statusField, $statuses)
            ->countAllResults();
    }

    private function countActivitiesWithoutCover(): int
    {
        if (!$this->tableExists('activities')) {
            return 0;
        }

        $coverField = $this->firstExistingField(
            'activities',
            [
                'documentation_file',
                'cover_image',
                'thumbnail',
                'image',
            ]
        );

        if (!$coverField) {
            return 0;
        }

        return $this->db
            ->table('activities')
            ->groupStart()
            ->where(
                $coverField . ' IS NULL',
                null,
                false
            )
            ->orWhere($coverField, '')
            ->groupEnd()
            ->countAllResults();
    }

    private function countProgramsByStatus(
        array $statuses
    ): int {
        if (!$this->tableExists('programs')) {
            return 0;
        }

        $statusField = $this->firstExistingField(
            'programs',
            [
                'status',
                'publication_status',
            ]
        );

        $builder = $this->db->table('programs');

        if (!$statusField) {
            return $builder->countAllResults();
        }

        return $builder
            ->whereIn($statusField, $statuses)
            ->countAllResults();
    }

    private function countMessagesByStatus(
        array $statuses
    ): int {
        if (!$this->tableExists('contact_messages')) {
            return 0;
        }

        $statusField = $this->firstExistingField(
            'contact_messages',
            ['status']
        );

        if (!$statusField) {
            return 0;
        }

        return $this->db
            ->table('contact_messages')
            ->whereIn($statusField, $statuses)
            ->countAllResults();
    }


    /**
     * Ringkasan seluruh status publikasi kegiatan.
     */
    private function getActivityPublicationSummary(): array
    {
        $summary = [
            'total'     => 0,
            'draft'     => 0,
            'review'    => 0,
            'published' => 0,
            'scheduled' => 0,
            'archived'  => 0,
        ];

        if (
            !$this->tableExists('activities')
            || !$this->firstExistingField(
                'activities',
                ['publication_status']
            )
        ) {
            return $summary;
        }

        try {
            $rows = $this->db
                ->table('activities')
                ->select(
                    'publication_status, COUNT(*) AS total',
                    false
                )
                ->groupBy('publication_status')
                ->get()
                ->getResultArray();

            foreach ($rows as $row) {
                $status = (string) (
                    $row['publication_status'] ?? ''
                );

                $total = (int) ($row['total'] ?? 0);

                if (array_key_exists($status, $summary)) {
                    $summary[$status] = $total;
                }

                $summary['total'] += $total;
            }
        } catch (\Throwable $e) {
            return $summary;
        }

        return $summary;
    }

    /**
     * Antrean konten yang menunggu pemeriksaan pengurus.
     */
    private function getActivityReviewQueue(
        int $limit = 5
    ): array {
        return $this->getActivityPublicationQueue(
            'review',
            $limit,
            'ASC'
        );
    }

    /**
     * Daftar publikasi terjadwal terdekat.
     */
    private function getScheduledPublicationQueue(
        int $limit = 3
    ): array {
        return $this->getActivityPublicationQueue(
            'scheduled',
            $limit,
            'ASC'
        );
    }

    /**
     * Query bersama untuk antrean review dan publikasi terjadwal.
     */
    private function getActivityPublicationQueue(
        string $status,
        int $limit,
        string $direction = 'ASC'
    ): array {
        if (
            !$this->tableExists('activities')
            || !$this->firstExistingField(
                'activities',
                ['publication_status']
            )
        ) {
            return [];
        }

        $activityFields = $this->getFields('activities');

        $selectedFields = [
            'activities.id',
            'activities.title',
            'activities.publication_status',
        ];

        foreach ([
            'summary',
            'description',
            'activity_date',
            'location',
            'updated_at',
            'scheduled_at',
            'program_id',
        ] as $field) {
            if (in_array($field, $activityFields, true)) {
                $selectedFields[] = 'activities.' . $field;
            }
        }

        $builder = $this->db
            ->table('activities')
            ->select(implode(', ', $selectedFields))
            ->where(
                'activities.publication_status',
                $status
            );

        $programNameField = $this->firstExistingField(
            'programs',
            ['name', 'title', 'label']
        );

        if (
            $this->tableExists('programs')
            && $programNameField
            && in_array('program_id', $activityFields, true)
        ) {
            $builder
                ->select(
                    'programs.'
                    . $programNameField
                    . ' AS program_name'
                )
                ->join(
                    'programs',
                    'programs.id = activities.program_id',
                    'left'
                );
        }

        if (
            $status === 'scheduled'
            && in_array('scheduled_at', $activityFields, true)
        ) {
            $builder
                ->where(
                    'activities.scheduled_at IS NOT NULL',
                    null,
                    false
                )
                ->orderBy(
                    'activities.scheduled_at',
                    $direction
                );
        } elseif (in_array('updated_at', $activityFields, true)) {
            $builder->orderBy(
                'activities.updated_at',
                $direction
            );
        } else {
            $builder->orderBy(
                'activities.id',
                $direction
            );
        }

        try {
            $rows = $builder
                ->limit(max(1, $limit))
                ->get()
                ->getResultArray();
        } catch (\Throwable $e) {
            return [];
        }

        $result = [];

        foreach ($rows as $row) {
            $summary = trim(
                (string) ($row['summary'] ?? '')
            );

            if ($summary === '') {
                $summary = trim(
                    strip_tags(
                        (string) ($row['description'] ?? '')
                    )
                );
            }

            if (mb_strlen($summary) > 150) {
                $summary = mb_substr($summary, 0, 147)
                    . '...';
            }

            $result[] = [
                'id' => (int) ($row['id'] ?? 0),
                'title' => (string) (
                    $row['title'] ?? 'Kegiatan GARDA 01'
                ),
                'summary' => $summary !== ''
                    ? $summary
                    : 'Ringkasan kegiatan belum tersedia.',
                'program_name' => (string) (
                    $row['program_name']
                    ?? 'Belum dikategorikan'
                ),
                'activity_date' =>
                    $row['activity_date'] ?? null,
                'location' => (string) (
                    $row['location'] ?? '-'
                ),
                'updated_at' => $row['updated_at'] ?? null,
                'scheduled_at' =>
                    $row['scheduled_at'] ?? null,
                'publication_status' => (string) (
                    $row['publication_status'] ?? $status
                ),
            ];
        }

        return $result;
    }

    private function countActivitiesWithoutProgram(): int
    {
        if (!$this->tableExists('activities')) {
            return 0;
        }

        $programField = $this->firstExistingField(
            'activities',
            ['program_id']
        );

        if (!$programField) {
            return 0;
        }

        try {
            return $this->db
                ->table('activities')
                ->groupStart()
                    ->where(
                        $programField . ' IS NULL',
                        null,
                        false
                    )
                    ->orWhere($programField, 0)
                ->groupEnd()
                ->countAllResults();
        } catch (\Throwable $e) {
            return 0;
        }
    }

    private function buildAttentionItems(
        int $membersWithoutPhone,
        int $missingActivityCovers,
        int $unreadMessages,
        int $draftPrograms,
        array $publicationSummary,
        int $activitiesWithoutProgram,
        ?array $nextMeeting
    ): array {
        $items = [];

        $reviewActivities = (int) (
            $publicationSummary['review'] ?? 0
        );

        $draftActivities = (int) (
            $publicationSummary['draft'] ?? 0
        );

        $scheduledActivities = (int) (
            $publicationSummary['scheduled'] ?? 0
        );

        if ($reviewActivities > 0) {
            $items[] = [
                'tone' => 'danger',
                'title' => $reviewActivities
                    . ' kegiatan menunggu tinjauan',
                'description' =>
                    'Periksa narasi dan dokumentasi sebelum konten diterbitkan.',
                'url' =>
                    '/activities?publication_status=review',
                'action' => 'Buka Antrean',
            ];
        }

        if ($draftActivities > 0) {
            $items[] = [
                'tone' => 'info',
                'title' => $draftActivities
                    . ' kegiatan masih berupa draft',
                'description' =>
                    'Lengkapi konten agar dapat dikirim untuk ditinjau.',
                'url' =>
                    '/activities?publication_status=draft',
                'action' => 'Lihat Draft',
            ];
        }

        if ($scheduledActivities > 0) {
            $items[] = [
                'tone' => 'info',
                'title' => $scheduledActivities
                    . ' kegiatan dijadwalkan terbit',
                'description' =>
                    'Pastikan waktu publikasi dan isi konten sudah tepat.',
                'url' =>
                    '/activities?publication_status=scheduled',
                'action' => 'Periksa Jadwal',
            ];
        }

        if ($activitiesWithoutProgram > 0) {
            $items[] = [
                'tone' => 'warning',
                'title' => $activitiesWithoutProgram
                    . ' kegiatan belum memiliki pilar program',
                'description' =>
                    'Hubungkan kegiatan ke pilar GARDA 01 agar arsip lebih terstruktur.',
                'url' => '/activities',
                'action' => 'Kategorikan',
            ];
        }

        if ($membersWithoutPhone > 0) {
            $items[] = [
                'tone' => 'warning',
                'title' =>
                    $membersWithoutPhone
                    . ' anggota belum memiliki nomor telepon',

                'description' =>
                    'Lengkapi kontak agar koordinasi organisasi lebih mudah.',

                'url' => '/members',
                'action' => 'Periksa Anggota',
            ];
        }

        if ($missingActivityCovers > 0) {
            $items[] = [
                'tone' => 'warning',
                'title' =>
                    $missingActivityCovers
                    . ' kegiatan belum memiliki cover',

                'description' =>
                    'Tambahkan foto agar dokumentasi publik tampil profesional.',

                'url' => '/activities',
                'action' => 'Kelola Kegiatan',
            ];
        }

        if ($unreadMessages > 0) {
            $items[] = [
                'tone' => 'danger',
                'title' =>
                    $unreadMessages
                    . ' pesan publik belum dibaca',

                'description' =>
                    'Periksa pesan warga atau tawaran kolaborasi yang masuk.',

                'url' => '/messages?status=unread',
                'action' => 'Buka Pesan',
            ];
        }

        if ($draftPrograms > 0) {
            $items[] = [
                'tone' => 'info',
                'title' =>
                    $draftPrograms
                    . ' program masih berstatus draft',

                'description' =>
                    'Tinjau program sebelum diterbitkan di website publik.',

                'url' => '/programs?status=draft',
                'action' => 'Tinjau Program',
            ];
        }

        if (!empty($nextMeeting['date'])) {
            $meetingTimestamp = strtotime(
                (string) $nextMeeting['date']
            );

            $todayTimestamp = strtotime(
                date('Y-m-d')
            );

            if ($meetingTimestamp !== false) {
                $daysUntil = (int) floor(
                    ($meetingTimestamp - $todayTimestamp)
                    / 86400
                );

                if ($daysUntil >= 0 && $daysUntil <= 7) {
                    $items[] = [
                        'tone' => 'info',

                        'title' => $daysUntil === 0
                            ? 'Rapat dijadwalkan hari ini'
                            : 'Rapat berlangsung dalam '
                                . $daysUntil
                                . ' hari',

                        'description' =>
                            (string) (
                                $nextMeeting['title']
                                ?? 'Rapat GARDA 01'
                            ),

                        'url' => '/meetings',
                        'action' => 'Lihat Agenda',
                    ];
                }
            }
        }

        if ($items === []) {
            $items[] = [
                'tone' => 'success',

                'title' =>
                    'Tidak ada masalah mendesak',

                'description' =>
                    'Data utama organisasi berada dalam kondisi baik.',

                'url' => '/activities',
                'action' => 'Lihat Kegiatan',
            ];
        }

        return $items;
    }

    private function getRecentPortalActivities(): array
    {
        $items = [];

        $items = array_merge(
            $items,
            $this->getRecentActivityRecords()
        );

        $items = array_merge(
            $items,
            $this->getRecentMeetingRecords()
        );

        $items = array_merge(
            $items,
            $this->getRecentMessageRecords()
        );

        $items = array_merge(
            $items,
            $this->getRecentCashRecords()
        );

        usort(
            $items,
            static function (
                array $first,
                array $second
            ): int {
                return ($second['timestamp'] ?? 0)
                    <=> ($first['timestamp'] ?? 0);
            }
        );

        return array_slice($items, 0, 8);
    }

    private function getRecentActivityRecords(): array
    {
        if (!$this->tableExists('activities')) {
            return [];
        }

        $titleField = $this->firstExistingField(
            'activities',
            [
                'title',
                'name',
                'activity_name',
            ]
        );

        $dateField = $this->firstExistingField(
            'activities',
            [
                'updated_at',
                'created_at',
                'activity_date',
                'date',
            ]
        );

        $locationField = $this->firstExistingField(
            'activities',
            [
                'location',
                'place',
                'lokasi',
            ]
        );

        $builder = $this->db->table('activities');

        if ($dateField) {
            $builder->orderBy($dateField, 'DESC');
        } else {
            $builder->orderBy('id', 'DESC');
        }

        $rows = $builder
            ->limit(3)
            ->get()
            ->getResultArray();

        $items = [];

        foreach ($rows as $row) {
            $date = $dateField
                ? ($row[$dateField] ?? null)
                : null;

            $items[] = [
                'type' => 'activity',
                'icon' => 'icon-activity',

                'title' => $titleField
                    ? ($row[$titleField]
                        ?? 'Kegiatan GARDA 01')
                    : 'Kegiatan GARDA 01',

                'description' => $locationField
                    ? ($row[$locationField]
                        ?? 'Data kegiatan diperbarui')
                    : 'Data kegiatan diperbarui',

                'date' => $date,

                'timestamp' => $date
                    ? (strtotime((string) $date) ?: 0)
                    : 0,

                'url' => isset($row['id'])
                    ? '/activities/edit/' . $row['id']
                    : '/activities',
            ];
        }

        return $items;
    }

    private function getRecentMeetingRecords(): array
    {
        if (!$this->tableExists('meetings')) {
            return [];
        }

        $titleField = $this->firstExistingField(
            'meetings',
            [
                'title',
                'meeting_title',
                'agenda',
                'topic',
                'name',
            ]
        );

        $dateField = $this->firstExistingField(
            'meetings',
            [
                'updated_at',
                'created_at',
                'meeting_date',
                'date',
            ]
        );

        $builder = $this->db->table('meetings');

        if ($dateField) {
            $builder->orderBy($dateField, 'DESC');
        } else {
            $builder->orderBy('id', 'DESC');
        }

        $rows = $builder
            ->limit(2)
            ->get()
            ->getResultArray();

        $items = [];

        foreach ($rows as $row) {
            $date = $dateField
                ? ($row[$dateField] ?? null)
                : null;

            $items[] = [
                'type' => 'meeting',
                'icon' => 'icon-meeting',

                'title' => $titleField
                    ? ($row[$titleField]
                        ?? 'Rapat GARDA 01')
                    : 'Rapat GARDA 01',

                'description' =>
                    'Agenda rapat organisasi',

                'date' => $date,

                'timestamp' => $date
                    ? (strtotime((string) $date) ?: 0)
                    : 0,

                'url' => isset($row['id'])
                    ? '/meetings/edit/' . $row['id']
                    : '/meetings',
            ];
        }

        return $items;
    }

    private function getRecentMessageRecords(): array
    {
        if (!$this->tableExists('contact_messages')) {
            return [];
        }

        $dateField = $this->firstExistingField(
            'contact_messages',
            [
                'created_at',
                'updated_at',
            ]
        );

        $builder = $this->db
            ->table('contact_messages');

        if ($dateField) {
            $builder->orderBy($dateField, 'DESC');
        } else {
            $builder->orderBy('id', 'DESC');
        }

        $rows = $builder
            ->limit(2)
            ->get()
            ->getResultArray();

        $items = [];

        foreach ($rows as $row) {
            $date = $dateField
                ? ($row[$dateField] ?? null)
                : null;

            $items[] = [
                'type' => 'message',
                'icon' => 'icon-message',

                'title' =>
                    $row['subject']
                    ?? 'Pesan publik baru',

                'description' =>
                    'Dikirim oleh '
                    . ($row['name'] ?? 'Pengunjung'),

                'date' => $date,

                'timestamp' => $date
                    ? (strtotime((string) $date) ?: 0)
                    : 0,

                'url' => isset($row['id'])
                    ? '/messages/' . $row['id']
                    : '/messages',
            ];
        }

        return $items;
    }

    private function getRecentCashRecords(): array
    {
        if (!$this->tableExists('cash_transactions')) {
            return [];
        }

        $amountField = $this->firstExistingField(
            'cash_transactions',
            [
                'amount',
                'nominal',
                'value',
            ]
        );

        $typeField = $this->firstExistingField(
            'cash_transactions',
            [
                'transaction_type',
                'type',
                'jenis',
            ]
        );

        $descriptionField =
            $this->firstExistingField(
                'cash_transactions',
                [
                    'description',
                    'notes',
                    'keterangan',
                    'category',
                ]
            );

        $dateField = $this->firstExistingField(
            'cash_transactions',
            [
                'transaction_date',
                'date',
                'tanggal',
                'created_at',
            ]
        );

        $builder = $this->db
            ->table('cash_transactions');

        if ($dateField) {
            $builder->orderBy($dateField, 'DESC');
        } else {
            $builder->orderBy('id', 'DESC');
        }

        $rows = $builder
            ->limit(2)
            ->get()
            ->getResultArray();

        $items = [];

        foreach ($rows as $row) {
            $date = $dateField
                ? ($row[$dateField] ?? null)
                : null;

            $type = $typeField
                ? mb_strtolower(
                    (string) ($row[$typeField] ?? '')
                )
                : '';

            $isExpense = in_array($type, [
                'expense',
                'pengeluaran',
                'keluar',
                'debit',
            ], true);

            $amount = $amountField
                ? $this->normalizeAmount(
                    $row[$amountField] ?? 0
                )
                : 0;

            $items[] = [
                'type' => 'cash',
                'icon' => 'icon-cash',

                'title' => $isExpense
                    ? 'Pengeluaran kas'
                    : 'Pemasukan kas',

                'description' =>
                    ($descriptionField
                        ? ($row[$descriptionField]
                            ?? 'Transaksi organisasi')
                        : 'Transaksi organisasi')
                    . ' · Rp'
                    . number_format(
                        $amount,
                        0,
                        ',',
                        '.'
                    ),

                'date' => $date,

                'timestamp' => $date
                    ? (strtotime((string) $date) ?: 0)
                    : 0,

                'url' => '/cash',
            ];
        }

        return $items;
    }
}