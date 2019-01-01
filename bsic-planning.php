<!DOCTYPE HTML>
<html lang="en-US">
<head>
    <meta charset="UTF-8">
    <title> Telecom Frequency Planner::BSIC </title>
    <link rel="stylesheet" type="text/css" href="style.css" media="all"/>
    <link rel="shortcut icon" type="image/x-icon" href="favicon.ico"/>
</head>
<body>
<div class="header-area">



</div>
<?php
include 'connection.php';

$message = null;
$lower_limit = isset($_GET['page']) ? ($_GET['page'] - 1) * 21 : 0;
$lower_limit = $lower_limit <= 0 ? 0 : $lower_limit;

$sql_for_all_sectors = "SELECT * from sectors";
$all_sectors = mysqli_query($conn, $sql_for_all_sectors);

$sql = "SELECT * from sectors order by sector  ASC limit " . $lower_limit . ",21";
$sectors = mysqli_query($conn, $sql);
if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $sql_for_all_sectors = "SELECT * from sectors";
    $all_sectors = mysqli_query($conn, $sql_for_all_sectors);

    if (mysqli_num_rows($all_sectors) > 0) {
        $count = 0;
        while ($row = mysqli_fetch_assoc($all_sectors)) {
            $bsic = decoct($count % 64);
            if($count < 64) {
                //die($bsic);
                $sql = "UPDATE sectors SET bsic = " . $bsic . ", `updated_at` = '" . date("Y-m-d H:i:s") . "' WHERE sector = '" . $row['sector'] . "'";
                if ($conn->query($sql) === TRUE) {
                    // echo "Record Updated successfully";
                } else {
                    echo "Error: " . $sql . "<br>" . $conn->error;
                }

                $sql = "UPDATE neighbors SET bsic = " . $bsic . ", `updated_at` = '" . date("Y-m-d H:i:s") . "' WHERE serving_cell = '" . $row['sector'] . "'";
                if ($conn->query($sql) === TRUE) {
                    // echo "Record Updated successfully";
                } else {
                    echo "Error: " . $sql . "<br>" . $conn->error;
                }

            }
            else {
                //echo $bsic;
                $sql = "select * from neighbors where serving_cell = '" . $row['sector'] . "' order by distance ASC limit 1,50";
               // echo $sql;
                $result = mysqli_query($conn, $sql);
                //print_r(mysqli_num_rows($result)) ;

                if (mysqli_num_rows($result) > 0) {
                    $index = 1;
                    $sectors = array();
                    $same_bsic_nearby = 0;
                    while ($neighbor_sector = mysqli_fetch_assoc($result)) {
                        if($same_bsic_nearby != 1) {
                            if ($neighbor_sector['bsic'] != $bsic) {
                                $same_bsic_nearby = 0;
                            } else {
                                $same_bsic_nearby = 1;
                            }
                        }
                    }
                    if($same_bsic_nearby == 0) {
                        $sql = "UPDATE sectors SET bsic = " . $bsic . ", `updated_at` = '" . date("Y-m-d H:i:s") . "' WHERE sector = '" . $row['sector'] . "'";
                        if ($conn->query($sql) === TRUE) {
                            // echo "Record Updated successfully";
                        } else {
                            echo "Error: " . $sql . "<br>" . $conn->error;
                        }

                        $sql = "UPDATE neighbors SET bsic = " . $bsic . ", `updated_at` = '" . date("Y-m-d H:i:s") . "' WHERE serving_cell = '" . $row['sector'] . "'";
                        if ($conn->query($sql) === TRUE) {
                            // echo "Record Updated successfully";
                        } else {
                            echo "Error: " . $sql . "<br>" . $conn->error;
                        }
                    }
                }


            }
            //echo $bsic;
            $count++;
        }
    } else {
        echo "0 results";
    }

    $sql = "SELECT * from sectors order by sector  ASC limit 1,21";
    $sectors = mysqli_query($conn, $sql);


}
?>
<h1>BSIC Planner</h1>
<div class="main-area">

    <ul class="main-left-navigation">
        <li>
            <a href="master.php">Update Master DB</a>
        </li>
        <li>
            <a href="neighbor-planning.php">Neighbor Planning</a>
        </li>
        <li>
            <a href="frequency-planning.php">Frequency Planning</a>
        </li>
        <li>
            <a class="active" href="bsic-planning.php">BSIC Planning</a>
        </li>
    </ul>
    <div class="content">
        <?php echo $message; ?>
        <form method="post" action="" enctype="multipart/form-data">
            <div class="field">
                <button class="button" type="submit" value="PLAN">PLAN</button>
            </div>
        </form>
        <table cellpadding="5" ; border="1" style="text-align: center">
            <tr>
                <th>#</th>
                <th>Sector</th>
                <th>BCCH</th>
                <th>TCH1</th>
                <th>BSIC</th>
                <th>Last Updated</th>
            </tr>
            <?php
            if (mysqli_num_rows($sectors) > 0) {
                $count = 1;
                while ($row = mysqli_fetch_assoc($sectors)) {
                    ?>
                    <tr>
                        <td><?php echo $row['id'] ?></td>
                        <td><?php echo $row['sector'] ?></td>
                        <td><?php echo $row['bcch'] ?></td>
                        <td><?php echo $row['tch1'] ?></td>
                        <td><?php echo $row['bsic'] ?></td>

                        <td><?php echo $row['updated_at'] ?></td>
                    </tr>
                    <?php
                    $count++;
                }

                ?>

                <?php
                $total_items = mysqli_num_rows($all_sectors);
                $total_pages = intval(mysqli_num_rows($all_sectors) / 21);
                //echo $total_items.'....'.$total_pages;
                ?>
                <tr>
                    <td colspan="6">
                        <?php
                        for ($page = 0; $page <= $total_pages; $page++) {
                            ?>
                            <a style="font-weight: bold"
                               href="bsic-planning.php?page=<?php echo $page + 1; ?>"><?php echo $page + 1; ?></a>
                            <?php
                        }
                        ?>

                    </td>
                </tr>

                <?php
            } else {
                echo "0 results";
            }

            mysqli_close($conn);
            ?>
        </table>

    </div>
</div>
<div class="footer-area">
    Copyright: Banglalink/Planning
</div>
</body>
</html>