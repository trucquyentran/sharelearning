<?php $servername = "localhost";
$username = "root";
$password = "";
$dbname = "luanvan";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Không có kết nối: " . $conn->connect_error);
}
mysqli_set_charset($conn, 'UTF8');

unset($thongbao);

$nguoidung = isset($_SESSION['Admin']) ? $_SESSION['Admin'] : "";

    $bai_viet_moi = "SELECT a.bv_ma, nd_hinh, nd_hoten, a.nd_username, bv_tieude, bv_ngaydang
                    FROM bai_viet a 
                    LEFT JOIN nguoi_dung b ON a.nd_username = b.nd_username
                    ORDER BY bv_ngaydang DESC";
    $result_bai_viet_moi = mysqli_query($conn,$bai_viet_moi);
    while ($row_bai_viet_moi = mysqli_fetch_array($result_bai_viet_moi)) { 

        $thongbao[] = array(
            "ma_bv" => $row_bai_viet_moi["bv_ma"],
            "bl_ma" => "",
            "hinh" => $row_bai_viet_moi["nd_hinh"],
            "hoten" => $row_bai_viet_moi["nd_hoten"],
            "username" => $row_bai_viet_moi["nd_username"],
            "tieude" => $row_bai_viet_moi["bv_tieude"],
            "ngaydang" => $row_bai_viet_moi["bv_ngaydang"],
            "ma_bl" => "",
            "nd_bl" => "",
            "da_xem" => ""
        );
    } 

    $bai_viet_xem = "SELECT a.bv_ma, nd_hinh, nd_hoten, a.nd_username, bv_tieude, bv_ngaydang, ls_thoigian
                    FROM bai_viet a 
                    LEFT JOIN nguoi_dung b ON a.nd_username = b.nd_username
                    LEFT JOIN lich_su_xem l ON l.bv_ma = a.bv_ma
                    where l.nd_username = '$nguoidung'
                    ORDER BY bv_ngaydang DESC";
    $result_bai_viet_xem = mysqli_query($conn,$bai_viet_xem);
    while ($row_bai_viet_xem = mysqli_fetch_array($result_bai_viet_xem)) { 
        $found = false;

        // Kiểm tra xem dữ liệu mới có tồn tại trong mảng $thongbao[] không
        foreach ($thongbao as $key => $value) {
            if ($value['ma_bv'] == $row_bai_viet_xem["bv_ma"]) {
                $found = true;
                // Nếu đã tồn tại 'ma_bv', kiểm tra và cập nhật 'da_xem' thành "Đã xem"
                if (empty($thongbao[$key]['da_xem'])) {
                $thongbao[$key]['da_xem'] = "Đã xem";
                }
                break;
            }
        }
    } 

    $binhluan_bv_moi = "SELECT c.bv_ma, bl_noidung, a.bl_ma, nd_hinh, nd_hoten, c.nd_username, bv_tieude, bl_thoigian
                        FROM binh_luan a  
                        LEFT JOIN bai_viet c ON c.bv_ma = a.bv_ma 
                        LEFT JOIN nguoi_dung b ON c.nd_username = b.nd_username
                        ORDER BY bl_thoigian DESC";
    $result_binhluan_bv_moi = mysqli_query($conn,$binhluan_bv_moi);
    while ($row_binhluan_bv_moi = mysqli_fetch_array($result_binhluan_bv_moi)) { 
        $thongbao[] = array(
        "bl_ma" => $row_binhluan_bv_moi["bl_ma"],
        "ma_bv" => $row_binhluan_bv_moi["bv_ma"],
        "hinh" => $row_binhluan_bv_moi["nd_hinh"],
        "hoten" => $row_binhluan_bv_moi["nd_hoten"],
        "username" => $row_binhluan_bv_moi["nd_username"],
        "tieude" => $row_binhluan_bv_moi["bv_tieude"],
        "ngaydang" => $row_binhluan_bv_moi["bl_thoigian"],
        "ma_bl" => $row_binhluan_bv_moi["bl_ma"],
        "nd_bl" => $row_binhluan_bv_moi["bl_noidung"],
        "da_xem" =>  ""

        );
    } 


    $binhluan_bv_xem = "SELECT c.bv_ma, bl_noidung, a.bl_ma, nd_hinh, nd_hoten, c.nd_username, bv_tieude, bl_thoigian, ls_thoigian
        FROM binh_luan a  
        LEFT JOIN bai_viet c ON c.bv_ma = a.bv_ma 
        LEFT JOIN nguoi_dung b ON c.nd_username = b.nd_username
        LEFT JOIN lich_su_xem l ON l.bl_ma = a.bl_ma
        where l.nd_username = '$nguoidung'
        ORDER BY bl_thoigian DESC";
    $result_binhluan_bv_xem = mysqli_query($conn,$binhluan_bv_xem);
    while ($row_binhluan_bv_xem = mysqli_fetch_array($result_binhluan_bv_xem)) { 
        $found = false;

        // Kiểm tra xem dữ liệu mới có tồn tại trong mảng $thongbao[] không
        foreach ($thongbao as $key => $value) {
            if ($value['bl_ma'] == $row_binhluan_bv_xem["bl_ma"]) {
                $found = true;
                // Nếu đã tồn tại 'ma_bv', kiểm tra và cập nhật 'da_xem' thành "Đã xem"
                if (empty($thongbao[$key]['da_xem'])) {
                    $thongbao[$key]['da_xem'] = "Đã xem";
                }
                break;
            }
        }
    } 


// Sắp xếp mảng $thongbao dựa trên cột "ngaydang" giảm dần
if(!empty($thongbao)){
    usort($thongbao, function($a, $b) {
        return strtotime($b['ngaydang']) - strtotime($a['ngaydang']);
        });
}



if(isset($_POST['chitiet_bv'])){
    $xem_bv_ma = $_POST['xem_bv_ma'];
    $bv_user = $_POST['bv_user'];

    // Thực hiện truy vấn SQL để kiểm tra xem bài viết đã được xem hay chưa
    $ls_xem = "SELECT bv_ma FROM lich_su_xem WHERE nd_username = '".$_SESSION['Admin']."' AND bv_ma = '$xem_bv_ma'";
    $result_ls_xem = mysqli_query($conn, $ls_xem);

    if(mysqli_num_rows($result_ls_xem) == 0){
        // Nếu bài viết chưa được xem, thêm vào lịch sử xem
        $them_ls_xem = "INSERT INTO lich_su_xem (nd_username, bv_ma, ls_thoigian) VALUES ('".$_SESSION['Admin']."', '$xem_bv_ma', NOW()) ";
        mysqli_query($conn, $them_ls_xem);
    }

    // Chuyển hướng sau khi kiểm tra và thêm vào lịch sử xem
    header("Location: Xem_BaiViet.php?this_bv_ma=$xem_bv_ma&tg=$bv_user");
}
if(isset($_POST['chitiet_bl'])){
    $xem_bl_ma = $_POST['xem_bl_ma'];
    $bl_user = $_POST['bv_user'];
    // $bl_bv = $_POST['xem_bv_ma'];

    // Thực hiện truy vấn SQL để kiểm tra xem bài viết đã được xem hay chưa
    $ls_xem = "SELECT bv_ma FROM lich_su_xem WHERE nd_username = '".$_SESSION['Admin']."' AND bl_ma = '$xem_bl_ma'";
    $result_ls_xem = mysqli_query($conn, $ls_xem);

    if(mysqli_num_rows($result_ls_xem) == 0){
        // Nếu bài viết chưa được xem, thêm vào lịch sử xem
        $them_ls_xem = "INSERT INTO lich_su_xem (nd_username, bl_ma, ls_thoigian) VALUES ('".$_SESSION['Admin']."', '$xem_bl_ma', NOW()) ";
        mysqli_query($conn, $them_ls_xem);
    }

    // Chuyển hướng sau khi kiểm tra và thêm vào lịch sử xem
    header("Location: Xem_BinhLuan.php?bl_ma=$xem_bl_ma&tg=$bl_user");
}

if (isset($_POST['doc_tatca'])) {
    foreach ($thongbao as $danh_dau) {
        // Thực hiện truy vấn SQL để kiểm tra xem bài viết đã được xem hay chưa
        if (empty($danh_dau['da_xem'])) {
            // Kiểm tra xem bình luận đã xem
            if (!empty($danh_dau['ma_bl'])) {
                $ls_xem_bl = "SELECT bl_ma FROM lich_su_xem WHERE nd_username = '" . $_SESSION['Admin'] . "' AND bl_ma = '" . $danh_dau['ma_bl'] . "'";
                $result_ls_xem_bl = mysqli_query($conn, $ls_xem_bl);

                if (mysqli_num_rows($result_ls_xem_bl) == 0) {
                    // Nếu bài viết chưa được xem, thêm vào lịch sử xem
                    $them_ls_xem_bl = "INSERT INTO lich_su_xem (nd_username, bl_ma, ls_thoigian) VALUES ('" . $_SESSION['Admin'] . "', '" . $danh_dau['ma_bl'] . "', NOW())";
                    mysqli_query($conn, $them_ls_xem_bl);
                }
            }

            // Kiểm tra xem bài viết đã xem
            if (!empty($danh_dau['ma_bv'])) {
                $ls_xem_bv = "SELECT bv_ma FROM lich_su_xem WHERE nd_username = '" . $_SESSION['Admin'] . "' AND bv_ma = '" . $danh_dau['ma_bv'] . "'";
                $result_ls_xem_bv = mysqli_query($conn, $ls_xem_bv);

                if (mysqli_num_rows($result_ls_xem_bv) == 0) {
                    // Nếu bài viết chưa được xem, thêm vào lịch sử xem
                    $them_ls_xem_bv = "INSERT INTO lich_su_xem (nd_username, bv_ma, ls_thoigian) VALUES ('" . $_SESSION['Admin'] . "', '" . $danh_dau['ma_bv'] . "', NOW())";
                    mysqli_query($conn, $them_ls_xem_bv);
                }
            }
        }
    }
    $currentPage = basename($_SERVER['SCRIPT_FILENAME']);
    header("Location: $currentPage");
    // echo '<script type="text/javascript">location.reload(true);</script>';

}

?>