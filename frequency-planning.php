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
                        //print_r($out);

                        if (!empty($keys) && !empty($out)) {
                            foreach ($out as $key => $row) {
                                $sql = "select * from neighbors where serving_cell = '" . $row[0] . "'";
                                $result = mysqli_query($conn, $sql);

                                if (mysqli_num_rows($result) > 0) {
                                    $index = 1;
                                    $sectors = array();
                                    while($neighbor_sector = mysqli_fetch_assoc($result)) {
                                        $absolute_relative_angle = abs($neighbor_sector['relative_angle']);
                                        if($absolute_relative_angle <= 60) {
                                            $group = 1;
                                        }
                                        elseif ($absolute_relative_angle <= 120)
                                        {
                                            $group = 2;
                                        }
                                        else
                                        {
                                            $group = 3;
                                        }
                                        $sectors[$index] = array('group'=>$group, 'neighbor_sector_id' => $neighbor_sector['neighbor_cell'], 'distance' => $neighbor_sector['distance'], 'angle' => $neighbor_sector['angle'], 'relative_angle' => $neighbor_sector['relative_angle'],'neighbor_bcch' => $neighbor_sector['neighbor_bcch'],'neighbor_tch1' => $neighbor_sector['neighbor_tch1'] );
                                        $index++;
                                    }
                                }
                                asort($sectors);
                               /* echo '<pre>';
                                print_r($sectors);
                                echo '</pre>';
                                die();*/

                                //$base_sector['neighbors'] = $sectors;
                                $cost = array();
                                $base_sector = array('base_sector_id' => $row[0], 'neighbors' => $sectors );
                                for ( $freq=1; $freq<=25; $freq++)
                                {
                                    $cost[$freq]=0;

                                    foreach ($base_sector['neighbors'] as $neighbor) {
                                        //$cost_for_neighbor=0;
                                        $cost_for_neighbor=rand(10,100);

                                        /*if(($neighbor['neighbor_bcch']==$freq) && ($neighbor['distance'] == 0)) {
                                            $cost_for_neighbor=100000;
                                            //echo '100000';
                                        }
                                        elseif((($neighbor['neighbor_bcch']+1 == $freq) || ($neighbor['neighbor_bcch']-1 == $freq)) && $neighbor['distance'] == 0) {
                                             $cost_for_neighbor=50000;
                                             //echo '50000';
                                         }
                                        elseif(($neighbor['neighbor_bcch'] == $freq) && ($neighbor['distance'] > 0))
                                        {
                                            if ($neighbor['group'] == 1) {
                                                $cost_for_neighbor= (500/$neighbor['distance']);
                                            }
                                            elseif ($neighbor['group'] == 2) {
                                                $cost_for_neighbor = (300/$neighbor['distance']);
                                            }
                                            else {
                                                $cost_for_neighbor = (200/$neighbor['distance']);

                                            }
                                            //echo $cost_for_neighbor;
                                        }*/
                                        $cost[$freq] = $cost[$freq]+$cost_for_neighbor;
                                        /*echo '<pre>';
                                        print_r($cost[$freq]);
                                        echo '</pre>';*/
                                    }


                                }
                                asort($cost);
                                /*echo '<pre>';
                                print_r($cost);
                                echo '</pre>';*/



                                reset($cost);
                                $first_key = key($cost);
                                $lowest_costs = array_slice($cost, 0, 3,true);
                                /*echo '<pre>';
                                print_r($lowest_costs);
                                echo '</pre>';*/

                                $keys = array_keys($lowest_costs);
                                $third_key = $keys[2];

                                /*echo '<pre>';
                                print_r($third_key);
                                echo '</pre>';*/

                                $sql = "UPDATE neighbors SET neighbor_bcch = '".$first_key."', neighbor_tch1 = '".$third_key."', `updated_at` = '".date("Y-m-d H:i:s")."' WHERE serving_cell = '".$base_sector['base_sector_id']."'";
                                if ($conn->query($sql) === TRUE) {
                                    echo "Record Updated successfully";
                                } else {
                                    echo "Error: " . $sql . "<br>" . $conn->error;
                                }



                                /*echo '<pre>';
                                //print_r(count($base_sector));
                                print_r($base_sector);
                                echo '</pre>';
                                echo '...........................<br>';*/
                               // die();
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
            <a href="neighbor-planning.php">Neighbor Planning</a>
        </li>
        <li>
            <a class="active"  href="frequency-planning.php">Frequency Planning</a>
        </li>
        <li>
            <a href="psc-planning.php">PSC Planning</a>
        </li>
        <li>
            <a href="psi-planning.php">PSI Planning</a>
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