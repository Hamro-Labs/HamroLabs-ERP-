        <?php
        include "config/dbcon.php";

        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['auth']) || !in_array($_SESSION['auth_user']['role'], ['Admin', 'admin'])) {
            header("Location: ../login.php");
            exit();
        }

        include "Includes/header.php";
        ?>

            <div class="container m-2 p-5">

                <table class="table table-hover text-center m-6 p-5">
                    <thead class="table-dark">
                        <tr>
                           <th>id</th>
                            <th>name</th>
                            <th>E-mail</th>
                            <th>Subject</th>
                            <th>Message</th>
                            <th>Action</th>    
                            <th>Time </th>           
                        </tr>
                    </thead>
                    <tbody>
                        <?php

                        $select = "SELECT * FROM contact";
                        $query = mysqli_query($conn, $select);
                        while ($row = mysqli_fetch_assoc($query)) { ?>
                            <tr>
                                <td><?php echo $row["id"] ?></td>
                                <td><?php echo $row["name"] ?></td>
                                <td><?php echo $row["email"] ?></td>
                                <td><?php echo $row["subject"] ?></td>
                                <td><?php echo $row["message"] ?></td>
                                <td>
                                    <a href="reply.php?id=<?= $row['id'] ?>" class="text-primary me-2">
                                        <i class="fa-solid fa-reply"></i>
                                    </a>
                                    <a href="view_message.php?id=<?= $row['id'] ?>" class="text-danger">
                                        <i class="fa-solid fa-eye"></i>
                                    </a>
                                </td>
                                <td><?php echo $row["created_at"] ?></td>
                            </tr>
                            <?php
                        }


                        ?>
                    </tbody>
                </table>
            </div>
        <?php include 'Includes/footer.php'; ?>