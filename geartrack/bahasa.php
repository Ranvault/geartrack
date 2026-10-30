<?php

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

// Bahasa default
if (!isset($_SESSION['bahasa'])) {
    $_SESSION['bahasa'] = 'id';
}

// Daftar terjemahan
$teks = [

    'id' => [
        // Umum
        'dashboard' => 'Dashboard',
        'judul_aplikasi' => 'GearTrack',
        'deskripsi_aplikasi' => 'Sistem Informasi Manajemen Penyewaan Perlengkapan Outdoor',
        'selamat_datang' => 'Selamat Datang',
        'bahasa' => 'Bahasa',
        'keluar' => 'Keluar',
        'aksi' => 'Aksi',
        'no' => 'No.',

        // Menu navigasi
        'perlengkapan' => 'Data Perlengkapan',
        'penyewa' => 'Data Penyewa',
        'transaksi' => 'Transaksi Penyewaan',
        'pengembalian' => 'Pengembalian',
        'pemeriksaan' => 'Pemeriksaan Kondisi',
        'riwayat' => 'Riwayat Transaksi',
        'laporan' => 'Laporan',

        // Perlengkapan
        'judul_perlengkapan' => 'Data Perlengkapan',
        'deskripsi_perlengkapan' => 'Kelola data perlengkapan outdoor.',
        'tambah_perlengkapan' => 'Tambah Perlengkapan',
        'nama_perlengkapan' => 'Nama Perlengkapan',
        'jenis' => 'Jenis Perlengkapan',
        'stok_total' => 'Stok Total',
        'stok_tersedia' => 'Stok Tersedia',
        'stok_perawatan' => 'Stok Perawatan',
        'stok_tidak_layak' => 'Stok Tidak Layak',
        'status_stok' => 'Status Stok',
        'harga_perhari' => 'Harga per Hari',
        'tersedia' => 'Tersedia',
        'stok_habis' => 'Stok Habis',
        'edit' => 'Edit',
        'hapus' => 'Hapus',
        'simpan' => 'Simpan',
        'batal' => 'Batal',
        'kembali' => 'Kembali',
        'belum_ada_data' => 'Belum ada data.',

        // Penyewa
        'judul_penyewa' => 'Data Penyewa',
        'tambah_penyewa' => 'Tambah Penyewa',
        'nama_penyewa' => 'Nama Penyewa',
        'no_telepon' => 'Nomor Telepon',
        'deskripsi_form_penyewa' => 'Masukkan identitas penyewa perlengkapan outdoor.',
        'nama_telepon_wajib' => 'Nama penyewa dan nomor telepon wajib diisi.',
        'batas_karakter_penyewa' => 'Nama dan nomor telepon maksimal 100 karakter.',
        'gagal_simpan_penyewa' => 'Data penyewa gagal disimpan.',
        'simpan_penyewa' => 'Simpan Penyewa',
        'edit_penyewa' => 'Edit Penyewa',
        'deskripsi_edit_penyewa' => 'Perbarui informasi penyewa.',
        'simpan_perubahan' => 'Simpan Perubahan',
        'gagal_perbarui_penyewa' => 'Data penyewa gagal diperbarui.',

        // Transaksi
        'judul_transaksi' => 'Transaksi Penyewaan',
        'tambah_transaksi' => 'Tambah Transaksi',
        'tanggal_sewa' => 'Tanggal Sewa',
        'tanggal_rencana_kembali' => 'Tanggal Rencana Kembali',
        'tanggal_kembali_aktual' => 'Tanggal Kembali Aktual',
        'status_transaksi' => 'Status Transaksi',
        'total_harga' => 'Total Harga',

        // Pengembalian dan kondisi
        'judul_pengembalian' => 'Pengembalian Perlengkapan',
        'judul_pemeriksaan' => 'Pemeriksaan Kondisi Perlengkapan',
        'kondisi_barang' => 'Kondisi Barang',
        'catatan' => 'Catatan',
        'simpan_pemeriksaan' => 'Simpan Pemeriksaan',

        // Riwayat dan laporan
        'judul_riwayat' => 'Riwayat Transaksi',
        'judul_laporan' => 'Laporan',
        'lihat_detail' => 'Lihat Detail',

        // Pesan
        'konfirmasi_hapus' => 'Apakah kamu yakin ingin menghapus data ini?',
        'data_berhasil_disimpan' => 'Data berhasil disimpan.',
        'data_gagal_disimpan' => 'Data gagal disimpan.',
        'data_berhasil_dihapus' => 'Data berhasil dihapus.',
        'data_gagal_dihapus' => 'Data gagal dihapus.',
        'pilih_bahasa' => 'Pilih Bahasa',
        'data_berhasil_ditambahkan' => 'Perlengkapan berhasil ditambahkan.',
        'data_berhasil_diperbarui' => 'Perlengkapan berhasil diperbarui.',
        'data_tidak_ditemukan' => 'Data perlengkapan tidak ditemukan.',
        'id_tidak_valid' => 'ID perlengkapan tidak valid.',
        'konfirmasi_logout' => 'Yakin ingin logout?',

        //Penyewa
        'judul_penyewa' => 'Data Penyewa',
        'deskripsi_penyewa' => 'Kelola informasi penyewa perlengkapan outdoor.',
        'kembali_dashboard' => 'Kembali ke Dashboard',
        'daftar_penyewa' => 'Daftar Penyewa',
        'belum_ada_data_penyewa' => 'Belum ada data penyewa.',
        'penyewa_berhasil_dihapus' => 'Data penyewa berhasil dihapus.',
        'penyewa_memiliki_transaksi' => 'Penyewa tidak dapat dihapus karena sudah memiliki transaksi.',
        'penyewa_gagal_dihapus' => 'Data penyewa gagal dihapus.',
        'konfirmasi_hapus_penyewa' => 'Yakin ingin menghapus penyewa ini?',
    ],

    'en' => [
        // General
        'dashboard' => 'Dashboard',
        'judul_aplikasi' => 'GearTrack',
        'deskripsi_aplikasi' => 'Outdoor Equipment Rental Management Information System',
        'selamat_datang' => 'Welcome',
        'bahasa' => 'Language',
        'keluar' => 'Logout',
        'aksi' => 'Actions',
        'no' => 'No.',

        // Navigation
        'perlengkapan' => 'Equipment',
        'penyewa' => 'Renters',
        'transaksi' => 'Rental Transactions',
        'pengembalian' => 'Returns',
        'pemeriksaan' => 'Condition Inspection',
        'riwayat' => 'Transaction History',
        'laporan' => 'Reports',

        // Equipment
        'judul_perlengkapan' => 'Equipment Data',
        'deskripsi_perlengkapan' => 'Manage outdoor equipment data.',
        'tambah_perlengkapan' => 'Add Equipment',
        'nama_perlengkapan' => 'Equipment Name',
        'jenis' => 'Equipment Type',
        'stok_total' => 'Total Stock',
        'stok_tersedia' => 'Available Stock',
        'stok_perawatan' => 'Stock Under Maintenance',
        'stok_tidak_layak' => 'Unusable Stock',
        'status_stok' => 'Stock Status',
        'harga_perhari' => 'Price per Day',
        'tersedia' => 'Available',
        'stok_habis' => 'Out of Stock',
        'edit' => 'Edit',
        'hapus' => 'Delete',
        'simpan' => 'Save',
        'batal' => 'Cancel',
        'kembali' => 'Back',
        'belum_ada_data' => 'No data available.',

        // Renters
        'judul_penyewa' => 'Renter Data',
        'tambah_penyewa' => 'Add Renter',
        'nama_penyewa' => 'Renter Name',
        'no_telepon' => 'Phone Number',
        'judul_penyewa' => 'Renter Data',
        'deskripsi_penyewa' => 'Manage outdoor equipment renter information.',
        'kembali_dashboard' => 'Back to Dashboard',
        'daftar_penyewa' => 'Renter List',
        'belum_ada_data_penyewa' => 'No renter data available.',
        'penyewa_berhasil_dihapus' => 'Renter data deleted successfully.',
        'penyewa_memiliki_transaksi' => 'This renter cannot be deleted because they have existing transactions.',
        'penyewa_gagal_dihapus' => 'Failed to delete renter data.',
        'konfirmasi_hapus_penyewa' => 'Are you sure you want to delete this renter?',
        'deskripsi_form_penyewa' => 'Enter the identity details of the outdoor equipment renter.',
        'nama_telepon_wajib' => 'Renter name and phone number are required.',
        'batas_karakter_penyewa' => 'Name and phone number must not exceed 100 characters.',
        'gagal_simpan_penyewa' => 'Failed to save renter data.',
        'simpan_penyewa' => 'Save Renter',
        'edit_penyewa' => 'Edit Renter',
        'deskripsi_edit_penyewa' => 'Update renter information.',
        'simpan_perubahan' => 'Save Changes',
        'gagal_perbarui_penyewa' => 'Failed to update renter data.',
        

        // Transactions
        'judul_transaksi' => 'Rental Transactions',
        'tambah_transaksi' => 'Add Transaction',
        'tanggal_sewa' => 'Rental Date',
        'tanggal_rencana_kembali' => 'Expected Return Date',
        'tanggal_kembali_aktual' => 'Actual Return Date',
        'status_transaksi' => 'Transaction Status',
        'total_harga' => 'Total Price',

        // Returns and inspection
        'judul_pengembalian' => 'Equipment Returns',
        'judul_pemeriksaan' => 'Equipment Condition Inspection',
        'kondisi_barang' => 'Item Condition',
        'catatan' => 'Notes',
        'simpan_pemeriksaan' => 'Save Inspection',

        // History and reports
        'judul_riwayat' => 'Transaction History',
        'judul_laporan' => 'Reports',
        'lihat_detail' => 'View Details',

        // Messages
        'konfirmasi_hapus' => 'Are you sure you want to delete this data?',
        'data_berhasil_disimpan' => 'Data saved successfully.',
        'data_gagal_disimpan' => 'Failed to save data.',
        'data_berhasil_dihapus' => 'Data deleted successfully.',
        'data_gagal_dihapus' => 'Failed to delete data.',
        'pilih_bahasa' => 'Select Language',
        'data_berhasil_ditambahkan' => 'Equipment added successfully.',
        'data_berhasil_diperbarui' => 'Equipment updated successfully.',
        'data_tidak_ditemukan' => 'Equipment data not found.',
        'id_tidak_valid' => 'Invalid equipment ID.',
        'konfirmasi_logout' => 'Are you sure you want to log out?',

    ],
];

// Fungsi untuk mengambil terjemahan
function t($kunci)
{
    global $teks;

    $bahasa = $_SESSION['bahasa'] ?? 'id';

    return $teks[$bahasa][$kunci]
        ?? $teks['id'][$kunci]
        ?? $kunci;
}
?>