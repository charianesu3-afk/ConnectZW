<?php
/* Creates / upgrades the tables ConnectZW needs. Runs automatically once per browser session. */
function ensure_schema() {
    global $conn;
    $run = function ($sql) use ($conn) { mysqli_query($conn, $sql); };

    $cols = [];
    if ($r = mysqli_query($conn, "SHOW COLUMNS FROM businesses")) {
        while ($row = mysqli_fetch_assoc($r)) $cols[] = $row['Field'];
    }
    $add = [
        'email'       => "VARCHAR(150) NULL",
        'website'     => "VARCHAR(200) NULL",
        'city'        => "VARCHAR(80) NULL",
        'province'    => "VARCHAR(60) NULL",
        'opens'       => "TIME NULL",
        'closes'      => "TIME NULL",
        'open_days'   => "VARCHAR(10) NOT NULL DEFAULT 'daily'",
        'is_verified' => "TINYINT(1) NOT NULL DEFAULT 0",
    ];
    foreach ($add as $c => $def) {
        if ($cols && !in_array($c, $cols, true)) $run("ALTER TABLE businesses ADD COLUMN $c $def");
    }

    $run("CREATE TABLE IF NOT EXISTS business_services (
        id INT AUTO_INCREMENT PRIMARY KEY, business_id INT NOT NULL,
        service_name VARCHAR(120) NOT NULL, price VARCHAR(60) NULL,
        FOREIGN KEY (business_id) REFERENCES businesses(id) ON DELETE CASCADE) ENGINE=InnoDB");

    $run("CREATE TABLE IF NOT EXISTS business_stats (
        business_id INT NOT NULL, day DATE NOT NULL,
        views INT NOT NULL DEFAULT 0, calls INT NOT NULL DEFAULT 0, whatsapp INT NOT NULL DEFAULT 0,
        emails INT NOT NULL DEFAULT 0, websites INT NOT NULL DEFAULT 0,
        PRIMARY KEY (business_id, day),
        FOREIGN KEY (business_id) REFERENCES businesses(id) ON DELETE CASCADE) ENGINE=InnoDB");

    $run("CREATE TABLE IF NOT EXISTS posts (
        id INT AUTO_INCREMENT PRIMARY KEY, title VARCHAR(200) NOT NULL, slug VARCHAR(220) NOT NULL UNIQUE,
        excerpt VARCHAR(300) NULL, content MEDIUMTEXT NOT NULL, image VARCHAR(255) NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP) ENGINE=InnoDB");

    $run("CREATE TABLE IF NOT EXISTS subscribers (
        id INT AUTO_INCREMENT PRIMARY KEY, email VARCHAR(150) NOT NULL UNIQUE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP) ENGINE=InnoDB");

    // Fill in city/province for older rows
    if ($r = mysqli_query($conn, "SELECT id, location FROM businesses WHERE city IS NULL OR city = ''")) {
        while ($row = mysqli_fetch_assoc($r)) {
            $city = trim(explode(',', (string)$row['location'])[0]);
            $prov = province_of($city);
            $st = mysqli_prepare($conn, "UPDATE businesses SET city = ?, province = ? WHERE id = ?");
            mysqli_stmt_bind_param($st, "ssi", $city, $prov, $row['id']);
            mysqli_stmt_execute($st); mysqli_stmt_close($st);
        }
    }
}
