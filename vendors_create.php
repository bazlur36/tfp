<!DOCTYPE html>
<html>
<body>

<h1>TFP - Telecom Frequency Planner::Vendors</h1>
<ul>
  <li><a href="index.php">Vendors</a></li>
  <li><a href="site-types.php">Site Types</a></li>
  <li><a href="sites.php">Sites</a></li>
</ul>
<?php

include 'connection.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST'){
	$sql = "INSERT INTO vendors (name,created_at,updated_at)
        VALUES ('".$_POST["name"]."','".date("Y-m-d H:i:s")."','".date("Y-m-d H:i:s")."')";
	if ($conn->query($sql) === TRUE) {
		echo "New record created successfully";
	} else {
		echo "Error: " . $sql . "<br>" . $conn->error;
	}
}


?>
<form method="POST" action="">
  Name:<br>
  <input type="text" name="name"><br>
 <button type="submit" value="Submit">Submit</button>
</form>

<?php

print_r($_REQUEST);

mysqli_close($conn);
?>




	

</body>
</html>