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
    <div class="Fiber_network">
        <a class="active" href="#"> Home </a>
    </div>
    <div class="Home">
        <a href="sites.php"> Sites </a>
    </div>
    <div class="Coverage_2G">
        <a href="sectors.php"> Sectors </a>
    </div>
    <div class="Coverage_3G">
        <a href="$rows.php"> $rows </a>
    </div>
    <div class="Fiber_network">
        <a href="oss-data.php"> OSS Data </a>
    </div>


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
                                        $absolute_relative_angle = abs($relative_angle);
                                        if($absolute_relative_angle <= 60) { $group = 1; }
                                        elseif ($absolute_relative_angle <= 120) { $group = 2; }
                                        else { $group = 3; }

                                        if( $distance == 0  ) {
                                            $cost = 10000;
                                        }
                                        else {
                                            if ( $group == 1 ) { $cost = 500/$distance; }
                                            elseif ( $group == 2 ) { $cost = 300/$distance; }
                                            else { $cost = 200/$distance; }
                                        }



                                        $sectors[$neighbor_sector['sector']] = array('distance' => $distance, 'angle' => $angle, 'relative_angle' => $relative_angle);
                                        $index++;
                                    }
                                    asort($sectors);

                                    /*1.	Plan neighbors: Count-30
2.	Group them with reference angle: 0 to +-60 degree-Front, +-60 to +-120 degree-side, rest-back
3.	Rank them as per distance for 3 groups
4.	Calculate cost for each frequency in a cell
a.	Co with 0 distance, cost=10000
b.	Co with other neighbors, cost for front= 500/distance, cost for side=300/distance, cost for back=200/distance
c.	Adjacent with o distance, cost=5000
5.	Rank Frequencies with ascending order of cost
6.	Pick required no of frequencies and make final assignment*/




                                    $rank = 1;
                                    array_slice($sectors, 0, 9);
                                    foreach ($sectors as $key => $neighbor_sector) {
                                        $sql = "INSERT INTO neighbors (serving_cell, neighbor_cell, distance, angle, relative_angle,  rank, created_at, updated_at)
                                        VALUES ('" . $base_sector['sector']. "','" . $key . "','" . $neighbor_sector['distance'] . "','" . $neighbor_sector['angle'] . "','" . $neighbor_sector['relative_angle'] . "','" . $rank . "','" . date("Y-m-d H:i:s") . "','" . date("Y-m-d H:i:s") . "')";
                                        if ($conn->query($sql) === TRUE) {
                                            echo "New record created successfully for " . $base_sector['sector'] . "<br>";
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
                $message = '<span class="red">Only .csv file format is allowed</span>';
            }
        } else {
            $message = '<span class="red">There was a problem with your file</span>';
        }
    }
}

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
            <a class="active" href="neighbor-planning.php">Neighbor Planning</a>
        </li>
        <li>
            <a href="frequency-planning.php">Frequency Planning</a>
        </li>
        <li>
            <a href="psc-planning.php">PSC Planning</a>
        </li>
        <li>
            <a  href="psi-planning.php">PSI Planning</a>
        </li>
        <li>
            <a href="master.php">Update Master DB</a>
        </li>

        <li>
            <a href="bsic-planning.php">BSIC Planning</a>
        </li>
    </ul>
    <div class="content">
        <?php echo $message; ?>
        <form method="post" action="" enctype="multipart/form-data">
            <div class="field"><label>Cells</label><input name="file" type="file"></div>

            <div class="field"><label>Assignment</label>
                <select>
                    <option>SSH</option>
                    <option>Random</option>
                </select>
            </div>
            <div class="field">
                <button class="button" type="submit" value="PLAN">PLAN</button>
            </div>
        </form>
    </div>
</div>
<div class="footer-area">
    Copyright: Banglalink/Planning
</div>
</body>
</html>