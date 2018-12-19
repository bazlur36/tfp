<!DOCTYPE HTML>
<html lang="en-US">
<head>
	<meta charset="UTF-8">
	<title> Telecom Frequency Planner </title>
	<link rel="stylesheet" type="text/css" href="style.css" media="all" />
	<link rel="shortcut icon" type="image/x-icon" href="favicon.ico" />
</head>
<body>
<div class="header-area">
	<div class="Fiber_network">
		<a class="active"  href="#"> Home </a>
	</div>
	<div class="Home">
		<a href="sites.php"> Sites </a>
	</div>
	<div class="Coverage_2G">
		<a href="sectors.php"> Sectors </a>
	</div>
	<div class="Coverage_3G">
		<a href="vendors.php"> Vendors </a>
	</div>
	<div class="Fiber_network">
		<a href="oss-data.php"> OSS Data </a>
	</div>


</div>
	<h1>PSC Planner</h1>
	<div class="main-area">

		<ul class="main-left-navigation">
			<li>
				<a href="neighbor-planning.php">Neighbor Planning</a>
			</li>
			<li>
				<a href="frequency-planning.php">Frequency Planning</a>
			</li>
			<li>
				<a class="active" href="psc-planning.php">PSC Planning</a>
			</li>
			<li>
				<a href="psi-planning.php">PSI Planning</a>
			</li><li>
				<a href="bsic-planning.php">BSIC Planning</a>
			</li>
		</ul>
		<div class="content">
			<div class="field"><label>Cells</label><input type="file"></div>

			<div class="field"><label>Assignment</label>
				<select>
					<option>Octade</option>
					<option>Random</option>
				</select>
			</div>
			<div class="field"><label>Method</label>
				<select>
					<option>Distance</option>
					<option>Handover</option>
				</select>
			</div>

			<div class="field">
				<input class="button" type="button" value="PLAN">
			</div>
		</div>
	</div>
	<div class="footer-area">
		Copyright: Banglalink/Planning
	</div>
</body>
</html>