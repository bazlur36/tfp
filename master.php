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

                        $keys = array(); //store csv file headers
                        $out = array(); //store csv file data
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

                                //print_r(sizeof(sizeof($data)));
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
                                        //echo $row[2]." Updated successfully <br>";
                                    } else {
                                        echo "Error: " . $sql . "<br>" . $conn->error . " While processig " . $row[2];
                                    }

                                } else {
                                    $sql = "INSERT INTO sectors (site, sector, vendor, technology,  band,    lac, ci,  longitude,    latitude, height,  tilt,    azimuth, bcch,    bsic, tch1, tch2, tch3, tch4, tch5, tch6, tch7, tch8, tch9, tch10, tch11, tch12, psc, pci, created_at, updated_at)
                                        VALUES ('" . $row[1] . "','" . $row[2] . "','" . $row[3] . "','" . $row[4] . "','" . $row[5] . "','" . $row[6] . "','" . $row[7] . "','" . $row[8] . "','" . $row[9] . "','" . $row[10] . "','" . $row[11] . "','" . $row[12] . "','" . $row[13] . "','" . $row[14] . "','" . $row[15] . "','" . $row[16] . "','" . $row[17] . "','" . $row[18] . "','" . $row[19] . "','" . $row[20] . "','" . $row[21] . "','" . $row[22] . "','" . $row[23] . "','" . $row[24] . "','" . $row[25] . "','" . $row[26] . "','" . $row[27] . "','" . $row[28] . "','" . date("Y-m-d H:i:s") . "','" . date("Y-m-d H:i:s") . "')";
                                    if ($conn->query($sql) === TRUE) {
                                        //echo "New record created successfully for ".$row[2]."<br>" ;
                                    } else {
                                        echo "Error: " . $sql . "<br>" . $conn->error;
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
    $message = '<span class="red">Sectors updated successfully</span>';
}

//echo $_GET['page'].'.....';
$lower_limit = isset($_GET['page']) ? ($_GET['page'] - 1) * 21 : 0;
$lower_limit = $lower_limit <= 0 ? 0 : $lower_limit;

$sql_for_all_sectors = "SELECT * from sectors";
$all_sectors = mysqli_query($conn, $sql_for_all_sectors);

$sql = "SELECT * from sectors order by sector  ASC limit " . $lower_limit . ",21";
//echo $sql;
$sectors = mysqli_query($conn, $sql);

?>
<h1>Update Master Database</h1>
<div class="main-area">

    <ul class="main-left-navigation">
        <li>
            <a class="active" href="master.php">Update Master DB</a>
        </li>
        <li>
            <a href="neighbor-planning.php">Neighbor Planning</a>
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
                <button class="button" type="submit" value="PLAN">UPDATE</button>
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
                               href="master.php?page=<?php echo $page + 1; ?>"><?php echo $page + 1; ?></a>
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
