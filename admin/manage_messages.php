<?php

session_start();

include "../includes/config.php";

/* =========================================
   ADMIN ACCESS PROTECTION
========================================= */

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../index.php");
    exit;
}


/* =========================================
   CREATE CSRF TOKEN
========================================= */

if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$csrf_token = $_SESSION['csrf_token'];

$message_status = "";


/* =========================================
   DELETE MESSAGE
========================================= */

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    if (
        isset($_POST['delete_message']) &&
        isset($_POST['csrf_token'])
    ) {

        if (!hash_equals($csrf_token, $_POST['csrf_token'])) {

            $message_status = "Invalid security token.";

        } else {

            $message_id = filter_input(
                INPUT_POST,
                'message_id',
                FILTER_VALIDATE_INT
            );

            if ($message_id) {

                $stmt = mysqli_prepare(
                    $conn,
                    "DELETE FROM contact_messages WHERE id = ?"
                );

                mysqli_stmt_bind_param(
                    $stmt,
                    "i",
                    $message_id
                );

                if (mysqli_stmt_execute($stmt)) {

                    $message_status = "Message deleted successfully.";

                } else {

                    $message_status = "Unable to delete the message.";
                }

                mysqli_stmt_close($stmt);
            }
        }
    }


    /* =====================================
       MARK MESSAGE AS READ
    ===================================== */

    if (
        isset($_POST['mark_read']) &&
        isset($_POST['csrf_token'])
    ) {

        if (!hash_equals($csrf_token, $_POST['csrf_token'])) {

            $message_status = "Invalid security token.";

        } else {

            $message_id = filter_input(
                INPUT_POST,
                'message_id',
                FILTER_VALIDATE_INT
            );

            if ($message_id) {

                $stmt = mysqli_prepare(
                    $conn,
                    "UPDATE contact_messages
                     SET status = 'Read'
                     WHERE id = ?"
                );

                mysqli_stmt_bind_param(
                    $stmt,
                    "i",
                    $message_id
                );

                if (mysqli_stmt_execute($stmt)) {

                    $message_status = "Message marked as read.";

                } else {

                    $message_status = "Unable to update the message.";
                }

                mysqli_stmt_close($stmt);
            }
        }
    }
}


/* =========================================
   GET ALL CONTACT MESSAGES
========================================= */

$query = mysqli_query(
    $conn,
    "SELECT *
     FROM contact_messages
     ORDER BY created_at DESC"
);

?>

<!DOCTYPE html>

<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Manage Messages | ConnectZW Admin</title>

    <link
        rel="stylesheet"
        href="admin.css"
    >

    <style>

        .messages-container {
            padding: 30px;
        }

        .messages-header {
            margin-bottom: 25px;
        }

        .messages-header h1 {
            margin-bottom: 8px;
        }

        .messages-header p {
            color: #6b7280;
        }

        .message-alert {
            padding: 14px 18px;
            margin-bottom: 20px;
            border-radius: 8px;
            background: #eff6ff;
            color: #1d4ed8;
        }

        .messages-table-container {
            background: #ffffff;
            border-radius: 12px;
            overflow-x: auto;
            box-shadow: 0 4px 15px rgba(0,0,0,0.08);
        }

        .messages-table {
            width: 100%;
            border-collapse: collapse;
        }

        .messages-table th,
        .messages-table td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid #e5e7eb;
        }

        .messages-table th {
            background: #f8fafc;
            color: #111827;
        }

        .messages-table tr:hover {
            background: #f8fafc;
        }

        .message-text {
            max-width: 300px;
            white-space: normal;
            word-break: break-word;
        }

        .status {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: 600;
        }

        .status-unread {
            background: #dbeafe;
            color: #1d4ed8;
        }

        .status-read {
            background: #dcfce7;
            color: #166534;
        }

        .action-form {
            display: inline-block;
            margin: 2px;
        }

        .btn-read {
            background: #2563eb;
            color: white;
            border: none;
            padding: 8px 12px;
            border-radius: 6px;
            cursor: pointer;
        }

        .btn-delete {
            background: #dc2626;
            color: white;
            border: none;
            padding: 8px 12px;
            border-radius: 6px;
            cursor: pointer;
        }

        .btn-read:hover,
        .btn-delete:hover {
            opacity: 0.9;
        }

        .no-messages {
            padding: 40px;
            text-align: center;
            color: #6b7280;
        }

    </style>

</head>


<body>


<?php include "sidebar.php"; ?>


<div class="main-content">


    <div class="messages-container">


        <div class="messages-header">

            <h1>Messages</h1>

            <p>
                View and manage messages sent through the
                ConnectZW contact form.
            </p>

        </div>


        <?php if ($message_status !== ""): ?>

            <div class="message-alert">

                <?php
                echo htmlspecialchars($message_status);
                ?>

            </div>

        <?php endif; ?>


        <div class="messages-table-container">


            <?php if (mysqli_num_rows($query) > 0): ?>


                <table class="messages-table">

                    <thead>

                        <tr>

                            <th>ID</th>

                            <th>Name</th>

                            <th>Email</th>

                            <th>Subject</th>

                            <th>Message</th>

                            <th>Status</th>

                            <th>Date</th>

                            <th>Actions</th>

                        </tr>

                    </thead>


                    <tbody>


                    <?php while ($row = mysqli_fetch_assoc($query)): ?>


                        <tr>

                            <td>
                                <?php echo $row['id']; ?>
                            </td>


                            <td>
                                <?php
                                echo htmlspecialchars(
                                    $row['name']
                                );
                                ?>
                            </td>


                            <td>
                                <?php
                                echo htmlspecialchars(
                                    $row['email']
                                );
                                ?>
                            </td>


                            <td>
                                <?php
                                echo htmlspecialchars(
                                    $row['subject']
                                );
                                ?>
                            </td>


                            <td class="message-text">

                                <?php
                                echo nl2br(
                                    htmlspecialchars(
                                        $row['message']
                                    )
                                );
                                ?>

                            </td>


                            <td>

                                <?php if ($row['status'] === 'Unread'): ?>

                                    <span class="status status-unread">
                                        Unread
                                    </span>

                                <?php else: ?>

                                    <span class="status status-read">
                                        Read
                                    </span>

                                <?php endif; ?>

                            </td>


                            <td>

                                <?php
                                echo date(
                                    "d M Y H:i",
                                    strtotime($row['created_at'])
                                );
                                ?>

                            </td>


                            <td>


                                <?php if ($row['status'] === 'Unread'): ?>

                                    <form
                                        method="POST"
                                        class="action-form"
                                    >

                                        <input
                                            type="hidden"
                                            name="csrf_token"
                                            value="<?php
                                            echo htmlspecialchars(
                                                $csrf_token
                                            );
                                            ?>"
                                        >

                                        <input
                                            type="hidden"
                                            name="message_id"
                                            value="<?php
                                            echo $row['id'];
                                            ?>"
                                        >

                                        <button
                                            type="submit"
                                            name="mark_read"
                                            class="btn-read"
                                        >
                                            Mark Read
                                        </button>

                                    </form>

                                <?php endif; ?>


                                <form
                                    method="POST"
                                    class="action-form"
                                    onsubmit="return confirm('Are you sure you want to delete this message?');"
                                >

                                    <input
                                        type="hidden"
                                        name="csrf_token"
                                        value="<?php
                                        echo htmlspecialchars(
                                            $csrf_token
                                        );
                                        ?>"
                                    >

                                    <input
                                        type="hidden"
                                        name="message_id"
                                        value="<?php
                                        echo $row['id'];
                                        ?>"
                                    >

                                    <button
                                        type="submit"
                                        name="delete_message"
                                        class="btn-delete"
                                    >
                                        Delete
                                    </button>

                                </form>


                            </td>

                        </tr>


                    <?php endwhile; ?>


                    </tbody>

                </table>


            <?php else: ?>


                <div class="no-messages">

                    <h2>📭 No Messages Yet</h2>

                    <p>
                        Messages submitted through the
                        Contact Us page will appear here.
                    </p>

                </div>


            <?php endif; ?>


        </div>


    </div>


</div>


</body>

</html>