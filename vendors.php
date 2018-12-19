<!DOCTYPE html>
<html>
<body>
<head>

    <meta charset="UTF-8">
    <title> Telecom Frequency Planner </title>
    <link rel="stylesheet" type="text/css" href="style.css" media="all" />
    <link rel="shortcut icon" type="image/x-icon" href="favicon.ico" />
</head>
<div class="header-area">
    <div class="Fiber_network">
        <a href="index.php"> Home </a>
    </div>
    <div class="Home">
        <a href="sites.php"> Sites </a>
    </div>
    <div class="Coverage_2G">
        <a href="sectors.php"> Sectors </a>
    </div>
    <div class="Coverage_3G">
        <a class="active" href="vendors.php"> Vendors </a>
    </div>
    <div class="Fiber_network">
        <a href="oss-data.php"> OSS Data </a>
    </div>


</div>
<div class="main-area">
    <h1>Telecom Frequency Planner::Vendors</h1>
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
        </li><li>
            <a href="bsic-planning.php">BSIC Planning</a>
        </li>
    </ul>
</div>
<div class="footer-area">
    Copyright: Banglalink/Planning
</div>
<h1>TFP - Telecom Frequency Planner::Vendors</h1>
<ul>
  <li><a href="vendors.php">Vendors</a></li>
  <li><a href="site-types.php">Site Types</a></li>
  <li><a href="sites.php">Sites</a></li>
</ul>
<?php

include 'connection.php';

$sql = "SELECT * from  vendors";
$result = mysqli_query($conn, $sql);
?>
<table>
	<tr>
		<th>ID</th>
		<th>NAME</th>
		<th>Updated at</th>
		<th>Actions</th>
		
	</tr>
	<?php
if (mysqli_num_rows($result) > 0) {
    while($row = mysqli_fetch_assoc($result)) {
		?><tr>
			<td><?php echo $row["id"]; ?></td>
			<td><?php echo $row["name"]; ?></td>
			<td><?php echo $row["updated_at"]; ?></td>
			<td><a href="vendors_edit.php?id=<?php echo $row["id"]; ?>">Edit</a></td>
		</tr>
		<?php
    }
} else {
    echo "0 results";
}

mysqli_close($conn);
?>
</table>

<a href="vendors_create.php">Add New</a>

	

</body>
</html>