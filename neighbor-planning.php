<!DOCTYPE HTML>
<html lang="en-US">
<head>
    <meta charset="UTF-8">
    <title> Telecom Frequency Planner </title>
    <link rel="stylesheet" type="text/css" href="style.css" media="all"/>
    <link rel="shortcut icon" type="image/x-icon" href="favicon.ico"/>
</head>
<body>
<div class="header-area">



</div>
<?php
include 'connection.php';


$message = null;
if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $allowed_extensions = array('csv', 'xls', 'xlsx');

    $upload_path = 'uploads';

    if (!empty($_FILES['file'])) {


        if ($_FILES['file']['error'] == 0) {

            // check extension
            $file = explode(".", $_FILES['file']['name']);
            $extension = array_pop($file);

            if (in_array($extension, $allowed_extensions)) {

                if (move_uploaded_file($_FILES['file']['tmp_name'], $upload_path . '/' . $_FILES['file']['name'])) {
                    // echo $extension;

                    if (($handle = fopen($upload_path . '/' . $_FILES['file']['name'], "r")) !== false) {

                        $keys = array();
                        $out = array();

                        $insert = array();

                        $line = 1;

                        while (($row = fgetcsv($handle, 0, ',', '"')) !== FALSE) {

                            foreach ($row as $key => $value) {
                                if ($line === 1) {
                                    $keys[$key] = $value;
                                } else {
                                    $out[$line][$key] = $value;

                                }
                            }
                            $line++;
                        }
                        fclose($handle);

                        if (!empty($keys) && !empty($out)) {
                            $truncate_sql = "TRUNCATE TABLE neighbors";
                            $truncated = mysqli_query($conn, $truncate_sql);

                            foreach ($out as $row) {

                                $sql = "select * from sectors where sector = '" . $row[0] . "'";
                                $base_sector = $conn->query($sql)->fetch_assoc();
                                $sql = "select * from sectors where sector != '" . $base_sector['sector'] . "'";
                                $result = mysqli_query($conn, $sql);


                                if (mysqli_num_rows($result) > 0) {
                                    $index = 1;
                                    $distances = array();
                                    while($neighbor_sector = mysqli_fetch_assoc($result)) {
                                        // print_r($base_sector['azimuth']);
                                        //die();
                                        $distance=haversineGreatCircleDistance($base_sector['latitude'],$base_sector['longitude'],$neighbor_sector['latitude'],$neighbor_sector['longitude']);
                                        $angle=bearing($base_sector['latitude'],$base_sector['longitude'],$neighbor_sector['latitude'],$neighbor_sector['longitude']);
                                        $relative_angle=($angle-$base_sector['azimuth']);
                                        $sectors[$neighbor_sector['sector']] = array('distance' => $distance, 'angle' => $angle, 'relative_angle' => $relative_angle,'bcch' => $neighbor_sector['bcch'],'tch1' => $neighbor_sector['tch1'],);
                                        $index++;
                                    }
                                    asort($sectors);

                                    $rank = 1;
                                    $sliced_sectors = array_slice($sectors, 0, 20);
                                   /* echo '<pre>';
                                    print_r(count($sliced_sectors));
                                    echo '</pre>';
                                    die();*/

                                    foreach ($sliced_sectors as $key => $neighbor_sector) {
                                        $sql = "INSERT INTO neighbors (serving_cell, neighbor_cell, distance, angle, relative_angle,  rank, serving_bcch, serving_tch1,neighbor_bcch, neighbor_tch1, created_at, updated_at)
                                        VALUES ('" . $base_sector['sector']. "','" . $key . "','" . $neighbor_sector['distance'] . "','" . $neighbor_sector['angle'] . "','" . $neighbor_sector['relative_angle'] . "','" . $rank . "','" . $base_sector['bcch'] . "','" . $base_sector['tch1'] . "','" . $neighbor_sector['bcch'] . "','" . $neighbor_sector['tch1'] . "','" . date("Y-m-d H:i:s") . "','" . date("Y-m-d H:i:s") . "')";
                                        if ($conn->query($sql) === TRUE) {
                                            //echo "New record created successfully for " . $base_sector['sector'] . "<br>";
                                        } else {
                                            echo "Error: " . $sql . "<br>" . $conn->error;
                                        }
                                        $rank++;
                                    }
                                }
                            }
                        }
                    }
                }
            } else {
                $message = '<span class="red">Only .csv, .xls, .xlsx file format are allowed</span>';
            }
        } else {
            $message = '<span class="red">There was a problem with your file</span>';
        }
    }
}

$lower_limit = isset($_GET['page']) ? ($_GET['page'] - 1) * 21 : 0;
$lower_limit = $lower_limit <= 0 ? 0 : $lower_limit;

$sql_for_all_neighbors = "SELECT * from neighbors";
$all_neighbors = mysqli_query($conn, $sql_for_all_neighbors);
//print_r($all_neighbors);
$sql = "SELECT * from neighbors order by serving_cell  ASC, neighbor_cell asc, distance asc  limit " . $lower_limit . ",21";
//echo $sql;
$neighbors = mysqli_query($conn, $sql);



function haversineGreatCircleDistance(
    $latitudeFrom, $longitudeFrom, $latitudeTo, $longitudeTo, $earthRadius = 6371000)
{
    // convert from degrees to radians
    $latFrom = deg2rad($latitudeFrom);
    $lonFrom = deg2rad($longitudeFrom);
    $latTo = deg2rad($latitudeTo);
    $lonTo = deg2rad($longitudeTo);

    $latDelta = $latTo - $latFrom;
    $lonDelta = $lonTo - $lonFrom;

    $angle = 2 * asin(sqrt(pow(sin($latDelta / 2), 2) +
            cos($latFrom) * cos($latTo) * pow(sin($lonDelta / 2), 2)));
    return $angle * $earthRadius;
}

function bearing($lat1, $long1, $lat2, $long2) {

    $dlong= $long1-$long2;
    $dlat=$lat1-$lat2;
    $primary_angle = atan($dlong/$dlat)*180/3.14;
    if ($primary_angle < 0 && $dlat < 0){
        $final_angle = $primary_angle + 360;
    }
    elseif($primary_angle < 0 && $dlong < 0){
        $final_angle = $primary_angle + 180;
    }
    else {
        $final_angle = $primary_angle;
    }
    return $final_angle > 0 ? $final_angle : 0;
}


?>
<h1>PSI Planner</h1>
<div class="main-area">

    <ul class="main-left-navigation">
        <li>
            <a href="master.php">Update Master DB</a>
        </li>
        <li>
            <a class="active"  href="neighbor-planning.php">Neighbor Planning</a>
        </li>
        <li>
            <a href="frequency-planning.php">Frequency Planning</a>
        </li>
        <li>
            <a href="bsic-planning.php">BSIC Planning</a>
        </li>
    </ul>
    <div class="content">
        <?php echo $message; ?>
        <form method="post" action="" enctype="multipart/form-data">
            <div class="field"><label>Cells</label><input name="file" type="file"></div>
            <div class="field">
                <button class="button" type="submit" value="PLAN">PLAN</button>
            </div>
        </form>
        <table cellpadding="5" ; border="1" style="text-align: center">
            <tr>
                <th>#</th>
                <th>Serving Cell</th>
                <th>Neighbor Cell</th>
                <th>Distance</th>
                <th>Angle</th>
                <th>Relative Angle</th>
                <th>Last Updated</th>
            </tr>
            <?php
            if (mysqli_num_rows($neighbors ) > 0) {
                $count = 1;
                while ($row = mysqli_fetch_assoc($neighbors)) {
                    ?>
                    <tr>
                        <td><?php echo $row['id'] ?></td>
                        <td><?php echo $row['serving_cell'] ?></td>
                        <td><?php echo $row['neighbor_cell'] ?></td>
                        <td><?php echo $row['distance'] ?></td>
                        <td><?php echo $row['angle'] ?></td>
                        <td><?php echo $row['relative_angle'] ?></td>
                        <td><?php echo $row['updated_at'] ?></td>
                    </tr>
                    <?php
                    $count++;
                }

                ?>

                <?php

                $total_items = mysqli_num_rows($all_neighbors);
                $total_pages = intval(mysqli_num_rows($all_neighbors) / 21);
                //echo $total_items.'....'.$total_pages;
                ?>
                <tr>
                    <td colspan="7">
                        <?php
                        for ($page = 0; $page <= $total_pages; $page++) {
                            ?>
                            <a style="font-weight: bold"
                               href="neighbor-planning.php?page=<?php echo $page + 1; ?>"><?php echo $page + 1; ?></a>
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