<?php

        $hostname_DB = "127.0.0.1";
		$database_DB = "danish_db";
		$username_DB = "root";
		$password_DB = "";
		
		try
		{
			$CONNPDO = new PDO("mysql:host=".$hostname_DB.";dbname=".$database_DB.";charset=UTF8", $username_DB, $password_DB, array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_TIMEOUT => 3));
		}
		catch(PDOException $e)
		{
			$CONNPDO = null;
		}
		if($CONNPDO != null)
		{
			$getdata_PRST = $CONNPDO -> prepare("SELECT * FROM listing");
			$getdata_PRST -> execute() or die($CONNPDO->errorInfo());
			$count = $getdata_PRST->rowCount();
			
			if($count != 0)
			{
				$response = "<table class='table table-striped'><tr><th>Apartment ID</th><th>Country</th><th>Price</th><th>Square Meters</th><th>Availability</th><th>Parking</th><th>Rooms</th><th>Bathrooms</th><th>New Building</th><th>Energy Class</th><th>Category</th><th>Description</th></tr>";
			
				while($getdata_RSLT = $getdata_PRST->fetch(PDO::FETCH_ASSOC,PDO::FETCH_ORI_NEXT))
				{
					$temp_apartment_id = $getdata_RSLT["apartment_id"];
					if($temp_apartment_id > 0)
					{
						$apartment_id = $temp_apartment_id;
					}
					else
					{
						$apartment_id = "N/A";
					}
					$country = $getdata_RSLT["country"];
					$temp_price = $getdata_RSLT["price"];
					if($temp_price == 0)
					{
						$price = "N/A";
					}
					else
					{
						$price = $temp_price;
					}
					$temp_sq_meters = $getdata_RSLT["sq_meters"];
					if($temp_sq_meters == 0)
					{
						$sq_meters = "N/A";
					}
					else
					{
						$sq_meters = $temp_sq_meters;
					}
					$availability = $getdata_RSLT["availability"];
					$parking = $getdata_RSLT["parking"];
					if($parking == "1")
					{
						$parking = "YES";
					}
					else
					{
						$parking = "NO";
					}
					$rooms = $getdata_RSLT["rooms"];
					if($rooms == "0")
					{
						$rooms = "N/A";
					}
					$temp_bathrooms = $getdata_RSLT["bathrooms"];
					if($temp_bathrooms == 0)
					{
						$bathrooms = "N/A"; 
					}
					else
					{
						$bathrooms = $temp_bathrooms;
					}
					$temp = $getdata_RSLT["newly_built"];
					if($temp == 1)
					{
						$newly_built = "YES";
					}
					else
					{
						$newly_built = "NO";
					}
					
					$energy_class = $getdata_RSLT["energy_class"];
					$categor = $getdata_RSLT["category_id"];
					if($categor == 1)
					{
						$category = "House";
					}
					elseif($categor == 2)
					{
						$category = "Apartment";
					}
					elseif($categor == 3)
					{
						$category = "Office";
					}
					elseif($categor == 4)
					{
						$category = "Warehouse";
					}
					elseif($categor == 5)
					{
						$category = "Land";
					}
					else
					{
						$category = "N/A";
					}
					$temp_descr = $getdata_RSLT["description"];
					if($temp_descr == "")
					{
						$description = "N/A";
					}
					else
					{
						$description = $temp_descr;
					}
					
					$response .= "<tr><td>$apartment_id</td><td>$country</td><td>$price</td><td>$sq_meters</td><td>$availability</td><td>$parking</td><td>$rooms</td><td>$bathrooms</td><td>$newly_built</td><td>$energy_class</td><td>$category</td><td>$description</td></tr>";
				}
				$response .= "</table>";
			}
			else
			{
				$response = "<span style='color:red';>No items stored in db!</span>";
			}
		}
?>
<!DOCTYPE html>
<html>
<head>
<title>DataBase Status</title>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
</head>
<body>
<center>
<div class="container">
<h3 style="color:red;">DataBase Status</h3>
<hr>
<h4 style="color:gray;">Check current Database status</h4>
<br>
</div>
<div class="container">
<span style="float:left; border:1px solid white; width:200px; word-wrap:break-word;"><b>Important Note:</b><br>It must be mentioned that not all contents of the XML files are stored.For example information such <u>Allowed Number of pets</u> and <u>ceiling Height</u> are not stored in db.</span>&nbsp;&nbsp;&nbsp;
<span style="float:right; width:900px; height:500px; overflow-y:auto; overflow-x:auto; word-wrap:break-word;"> <?php echo $response; ?></span>
</div>
<br>
<hr>
<br>
<div class="container">
<a href="index.php">Click here to submit new XML</a>
</div>
<br>
<hr>
</center>
</body>
</html>