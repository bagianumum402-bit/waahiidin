-- Buat database baru
CREATE DATABASE IF NOT EXISTS asn_db;
USE asn_db;

-- Buat tabel untuk menyimpan data pegawai
CREATE TABLE IF NOT EXISTS pegawai (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(255) NOT NULL,
    nip VARCHAR(50) NOT NULL,
    jenis_asn VARCHAR(50) NOT NULL,
    golongan VARCHAR(100) NOT NULL,
    jabatan VARCHAR(255) NOT NULL,
    bagian VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);