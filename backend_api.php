<?php
// Izinkan akses dari mana saja (CORS)
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With, Origin, Accept");

// Handle preflight OPTIONS request secara eksplisit
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}

// ==============================================================================
// KONFIGURASI DATABASE HOSTING
// ==============================================================================
// UBAH 3 BARIS DI BAWAH INI SESUAI DENGAN DETAIL MYSQL DI PANEL HOSTING ANDA!
// ==============================================================================
$host = "localhost";        // Biarkan "localhost" (99% hosting menggunakan ini)
$user = "GANTI_USERNAME";   // Contoh: "u123456_admin", BUKAN "root"
$pass = "GANTI_PASSWORD";   // Contoh: "P4ssw0rdKuat123!"
$dbname = "GANTI_NAMADB";   // Contoh: "u123456_asndb", biasanya ada prefix di depannya

// Matikan pesan error bawaan PHP agar tidak merusak format JSON balikan
error_reporting(0);
mysqli_report(MYSQLI_REPORT_OFF);

// Buat koneksi
$conn = new mysqli($host, $user, $pass, $dbname);

// Cek koneksi dengan format JSON yang valid
if ($conn->connect_error) {
    http_response_code(500);
    die(json_encode([
        "success" => false, 
        "error" => "Koneksi database hosting gagal. Cek username/password di api.php!", 
        "detail" => $conn->connect_error
    ]));
}

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    // AMBIL DATA (READ)
    $sql = "SELECT * FROM pegawai ORDER BY id DESC";
    $result = $conn->query($sql);
    $data = [];
    
    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            // Pastikan ID dikembalikan sebagai integer untuk konsistensi di frontend
            $row['id'] = (int)$row['id'];
            $data[] = $row;
        }
    }
    echo json_encode($data);
    
} elseif ($method === 'POST') {
    // TERIMA DATA JSON DARI FRONTEND
    $input = json_decode(file_get_contents('php://input'), true);
    $action = $input['action'] ?? '';

    if ($action === 'create') {
        // TAMBAH DATA (CREATE)
        $stmt = $conn->prepare("INSERT INTO pegawai (nama, nip, jenis_asn, golongan, jabatan, bagian) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssss", $input['nama'], $input['nip'], $input['jenis_asn'], $input['golongan'], $input['jabatan'], $input['bagian']);
        
        if($stmt->execute()){
            echo json_encode(["success" => true, "id" => $conn->insert_id]);
        } else {
            echo json_encode(["success" => false, "error" => $stmt->error]);
        }
        $stmt->close();
        
    } elseif ($action === 'update') {
        // UBAH DATA (UPDATE)
        $stmt = $conn->prepare("UPDATE pegawai SET nama=?, nip=?, jenis_asn=?, golongan=?, jabatan=?, bagian=? WHERE id=?");
        $stmt->bind_param("ssssssi", $input['nama'], $input['nip'], $input['jenis_asn'], $input['golongan'], $input['jabatan'], $input['bagian'], $input['id']);
        
        if($stmt->execute()){
            echo json_encode(["success" => true]);
        } else {
            echo json_encode(["success" => false, "error" => $stmt->error]);
        }
        $stmt->close();
        
    } elseif ($action === 'delete') {
        // HAPUS DATA (DELETE)
        $stmt = $conn->prepare("DELETE FROM pegawai WHERE id=?");
        $stmt->bind_param("i", $input['id']);
        
        if($stmt->execute()){
            echo json_encode(["success" => true]);
        } else {
            echo json_encode(["success" => false, "error" => $stmt->error]);
        }
        $stmt->close();
    }
}
$conn->close();
?>