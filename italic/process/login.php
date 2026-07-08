<?php
session_start();

include "../config/database.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    $stmt = $conn->prepare("
        SELECT *
        FROM anggota
        WHERE email=?
        LIMIT 1
    ");

    if (!$stmt) {
        $conn->close();
        header("Location: ../login.php?error=system");
        exit;
    }

    $stmt->bind_param("s", $email);
    $stmt->execute();

    $result = $stmt->get_result();

    if ($result->num_rows == 1) {

        $user = $result->fetch_assoc();

        if (password_verify($password, $user['kata_sandi_hash'])) {

            session_regenerate_id(true);

            $_SESSION['login'] = true;
            $_SESSION['id'] = $user['id_anggota'];
            $_SESSION['nama'] = $user['nama_lengkap'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['kode'] = $user['kode_anggota'];

            $stmt->close();
            $conn->close();

            $redirectTarget = $_POST['redirect'] ?? '';
            if ($redirectTarget !== '' && preg_match('#^[a-zA-Z0-9_\-./]+\.php(\?[a-zA-Z0-9=&_\-.]*)?$#', $redirectTarget)) {
                header("Location: ../" . $redirectTarget);
            } else {
                header("Location: ../account.php");
            }
            exit;

        } else {

            $stmt->close();
            $conn->close();

            header("Location: ../login.php?error=password");
            exit;

        }

    } else {

        $stmt->close();
        $conn->close();

        header("Location: ../login.php?error=email");
        exit;

    }

} else {

    header("Location: ../login.php");
    exit;

}
?>