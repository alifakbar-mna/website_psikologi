<?php

return [
    'roles' => [
        'admin' => 'Admin',
        'dosen' => 'Dosen',
        'asdos' => 'ASDOS',
        'mahasiswa' => 'Mahasiswa',
    ],

    'permissions' => [
        'catalog' => 'Lihat katalog',
        'read' => 'Baca laporan',
        'read_all' => 'Baca tanpa izin sementara',

        'request_access' => 'Ajukan akses',

        'upload' => 'Upload laporan (dengan izin sementara)',
        'upload_direct' => 'Upload tanpa izin sementara dan langsung diterima',

        'bookmarks' => 'Simpanan dan like',

        'review_uploads' => 'Review upload',
        'manage_documents' => 'Kelola laporan',
        'manage_categories' => 'Kelola kategori',

        'approve_access' => 'Persetujuan akses',
        'grant_access' => 'Berikan akses sementara',

        'manage_users' => 'Kelola pengguna dan import',
        'manage_permissions' => 'Kelola hak akses',

        'dashboard' => 'Dashboard',
        'activity_logs' => 'Log aktivitas',
    ],

    'defaults' => [
        'admin' => [
            '*',
        ],

        'dosen' => [
            'catalog',
            'read',
            'read_all',
            'request_access',
            'upload',
            'upload_direct',
            'bookmarks',
            'manage_categories',
            'approve_access',
        ],

        'asdos' => [
            'catalog',
            'read',
            'request_access',
            'upload',
            'bookmarks',
        ],

        'mahasiswa' => [
            'catalog',
            'read',
            'request_access',
            'upload',
            'bookmarks',
        ],
    ],
];