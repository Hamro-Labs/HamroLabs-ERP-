                                <?php
                                include "config/dbcon.php";
                                include "Includes/header.php";
                                ?>

                                <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css" />
                                <nav class="navbar navbar-light justify-content-center fs-3 mb-5" style="background-color: #15a5e373;">
                                    Manage users
                                </nav>
                                <div class="container">
                                    <?php
                                    if (isset($_GET["msg"])) {
                                        $msg = $_GET["msg"];
                                        echo '<div class="alert alert-success alert-dismissible fade show" role="alert">' . $msg . '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>';
                                    }
                                    ?>
                                    <a href="add-new.php" class="btn btn-success mb-3 mt-4">Add New</a>
                                    <table class="table table-hover text-center">
                                        <thead class="table-dark">
                                            <tr>
                                                <th>id</th>
                                                <th>name</th>
                                                <th>address</th>
                                                <th>email</th>
                                                <th>faculty</th>
                                                <th>year_part</th>
                                                <th>contact</th>
                                                <th>password</th>
                                                <th>Role </th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            $sql = "SELECT * FROM `user` ";
                                            $result = mysqli_query($conn, $sql);
                                            while ($row = mysqli_fetch_assoc($result)) { ?>
                                                <tr>
                                                    <td><?php echo $row["id"] ?></td>
                                                    <td><?php echo $row["name"] ?></td>
                                                    <td><?php echo $row["address"] ?></td>
                                                    <td><?php echo $row["email"] ?></td>
                                                    <td><?php echo $row["faculty"] ?></td>
                                                    <td><?php echo $row["year_part"] ?></td>
                                                    <td><?php echo $row["contact"] ?></td>
                                                    <td><?php echo $row["password"] ?></td>
                                                    <td><?php echo $row["role"] ?></td>
                                                    <td>
                                                        <a href="edit.php?id=<?= $row['id'] ?>" class="text-primary me-2">
                                                            <i class="fa-solid fa-pen-to-square"></i>        
                                                        </a>
                                                        <a href="delete.php?id=<?= $row['id'] ?>" class="text-danger">
                                                            <i class="fa-solid fa-trash"></i>
                                                        </a>
                                                    </td>
                                                </tr>
                                            <?php 
                                            
                                        
                                        }
                                            
                                            
                                            ?>
                                        </tbody>
                                    </table>
                                </div>
                                <?php include('Includes/footer.php'); ?>