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
                        /* echo "<pre>";
                        print_r($keys);
                        echo "</pre>";
                        echo "<pre>";
                        print_r($out);
                        echo "</pre>";*/
                        // die("Halted");
                        fclose($handle);
                        if (!empty($keys) && !empty($out)) {

                            foreach ($out as $row) {


                                $sql = "select * from sectors where sector = '" . $row[2] . "'";
                                $data = $conn->query($sql)->fetch_assoc();
                                print_r(sizeof(sizeof($data)));
                                if (sizeof($data) > 0) {
                                    $sql = "UPDATE sectors set site = '" . $row[1] . "',
                                                                vendor = '" . $row[3] . "', 
                                                                technology = '" . $row[4] . "', 
                                                                 band = '" . $row[5] . "',
                                                                 lac = '" . $row[6] . "',
                                                                 ci = '" . $row[7] . "',
                                                                 longitude = '" . $row[8] . "',
                                                                 latitude = '" . $row[9] . "', 
                                                                 height = '" . $row[10] . "',  
                                                                 tilt = '" . $row[11] . "',    
                                                                 azimuth = '" . $row[12] . "', 
                                                                 bcch = '" . $row[13] . "',    
                                                                 bsic = '" . $row[14] . "', 
                                                                 tch1 = '" . $row[15] . "', 
                                                                 tch2 = '" . $row[16] . "', 
                                                                 tch3 = '" . $row[17] . "', 
                                                                 tch4 = '" . $row[18] . "', 
                                                                 tch5 = '" . $row[19] . "', 
                                                                 tch6 = '" . $row[20] . "', 
                                                                 tch7 = '" . $row[21] . "', 
                                                                 tch8 = '" . $row[22] . "', 
                                                                 tch9 = '" . $row[23] . "', 
                                                                 tch10 = '" . $row[24] . "', 
                                                                 tch11 = '" . $row[25] . "', 
                                                                 tch12 = '" . $row[26] . "', 
                                                                 psc = '" . $row[27] . "', 
                                                                 pci = '" . $row[28] . "', 
                                                updated_at = '" . date("Y-m-d H:i:s") . "' where sector = '" . $row[2] . "'";

                                    if ($conn->query($sql) === TRUE) {
                                        echo $row[2] . " Updated successfully <br>";
                                    } else {
                                        echo "Error: " . $sql . "<br>" . $conn->error . " While processig " . $row[2];
                                    }

                                } else {
                                    $sql = "INSERT INTO sectors (site, sector, vendor, technology,  band,    lac, ci,  longitude,    latitude, height,  tilt,    azimuth, bcch,    bsic, tch1, tch2, tch3, tch4, tch5, tch6, tch7, tch8, tch9, tch10, tch11, tch12, psc, pci, created_at, updated_at)
                                        VALUES ('" . $row[1] . "','" . $row[2] . "','" . $row[3] . "','" . $row[4] . "','" . $row[5] . "','" . $row[6] . "','" . $row[7] . "','" . $row[8] . "','" . $row[9] . "','" . $row[10] . "','" . $row[11] . "','" . $row[12] . "','" . $row[13] . "','" . $row[14] . "','" . $row[15] . "','" . $row[16] . "','" . $row[17] . "','" . $row[18] . "','" . $row[19] . "','" . $row[20] . "','" . $row[21] . "','" . $row[22] . "','" . $row[23] . "','" . $row[24] . "','" . $row[25] . "','" . $row[26] . "','" . $row[27] . "','" . $row[28] . "','" . date("Y-m-d H:i:s") . "','" . date("Y-m-d H:i:s") . "')";
                                    if ($conn->query($sql) === TRUE) {
                                        echo "New record created successfully for " . $row[2] . "<br>";
                                    } else {
                                        echo "Error: " . $sql . "<br>" . $conn->error;
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


?>
<h1>Import Master Data</h1>
<div class="main-area">

    <ul class="main-left-navigation">
        <li>
            <a href="neighbor-planning.php">Neighbor Planning</a>
        </li>
        <li>
            <a href="frequency-planning.php">Frequency Planning</a>
        </li>
        <li>
            <a href="psc-planning.php">PSC Planning</a>
        </li>
        <li>
            <a href="psi-planning.php">PSI Planning</a>
        </li>
        <li>
            <a class="active" href="master.php">Update Master DB</a>
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