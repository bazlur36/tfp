<!DOCTYPE html>
<html>
<body>

<h1>Vendors::Edit</h1>
<ul>
  <li><a href="vendors.php">Vendors</a></li>
  <li><a href="site-types.php">Site Types</a></li>
  <li><a href="sites.php">Sites</a></li>
</ul>
<?php

include 'connection.php';

if(isset($_POST['name'])) {
	
	//$sql = "UPDATE vendors (name,updated_at) VALUES ('".$_POST["name"]."','".date("Y-m-d H:i:s")."') where id = '".$_GET["id"]."'";
	$sql = "UPDATE vendors SET name = '".$_POST["name"]."', `updated_at` = '".date("Y-m-d H:i:s")."' WHERE id = '".$_GET["id"]."'";
	if ($conn->query($sql) === TRUE) {
		echo "Record Updated successfully";
	} else {
		echo "Error: " . $sql . "<br>" . $conn->error;
	}
}
else {
	$sql = "SELECT * from vendors where id = '".$_GET["id"]."'";
	if ($conn->query($sql) === TRUE) {
		echo "New record created successfully";
	} else {
		echo "Error: " . $sql . "<br>" . $conn->error;
	}
	$result = mysqli_query($conn, $sql);
	while ($row = mysqli_fetch_assoc($result))
	{
	?>
		<form method="POST" action="">
		  Name:<br>
		  <input type="text" value="<?php echo $row['name']; ?>" name="name"><br>
		 <button type="submit" value="Submit">Submit</button>
		</form>
	<?php
	}
}


?>


<?php

print_r($_REQUEST);

mysqli_close($conn);
?>




	

</body>
</html>