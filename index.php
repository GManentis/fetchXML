<?php
if(isset($_POST["submit"]))
{
	if(isset($_FILES["data"]) && $_FILES["data"]["error"] == 0 && isset($_POST["country"]))//checking if file and country is set.Country is important for format fetching
	{
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
			$data = file_get_contents($_FILES["data"]["tmp_name"]);
			$country = $_POST["country"];
			$xml = simplexml_load_string($data) or die("Error: Cannot create object");//loading contents from xml to object
			
			
			if($country == "Russia")//For Russian XML
			{ 
				$response = "";// response for xml parts
				$counter = 0; // xml parts
				foreach($xml->children() as $apartment)//fetching child nodes 
				{
					//checking the node for each XML part 
					++$counter;
					if(isset($apartment->price) && isset($apartment->cat) && isset($apartment->Purpose))//checking the parameter that must be present in our loaded XML
					{
						/* Concept for XML:
						for each child node of the main XML,it is checked if proper child nodes are set properly
						if set properly then the $number variable is increased and the variable is inserted to $variables
						in the form of an ordered list so as to inform user for successfully fetched data in the end of the process 
						
						The way the results are shown go like this:
						XML Part/Node -> variables/child nodes 
						
						In case the important parameters are not present in inserted XML,
						errors will be shown for each part/node 

						*/
						$variables = "";// the children nodes that are successfully fetched
						$number = 0;//the counter for successfully fetched variables
						$country = "Russia";
						$variables .= "<li>country</li>";
						++$number;
						$temp1 = "";
						$temp2 = "";
						$temp3 = "";
						if(isset($apartment->id))
						{
							++$number;
							$variables .= "<li>apartment_id</li>";
						}
						$id = $apartment->id;
						
						if(isset($apartment->roomsNO))
						{
							++$number;
							$variables .= "<li>rooms</li>";
						}
						$rooms = $apartment->roomsNO;
						
						if(isset($apartment->bathroomsNO))
						{
							++$number;
							$variables .= "<li>bathrooms</li>";
						}
						$bathrooms = $apartment->bathroomsNO;
						
						if(isset($apartment->new_development))
						{	
							++$number;
							$variables .= "<li>newly_built</li>";
						}
						
						$temp_built = $apartment->new_development;
						if($temp_built == "true")//making sure that newly built will be properly stored
						{
							$newly_built = TRUE;
						}
						else
						{
							$newly_built = FALSE;
						}
						
						if(isset($apartment->Purpose))
						{
							++$number;
							$variables .= "<li>availability</li>";
						}
						$temp1 = $apartment -> Purpose;
						//setting fetched availability and transforming it to danish standards
						if($temp1 == 7)
						{
							$availability = "rent";
						}
						elseif($temp1 == 8)
						{
							$availability = "sale";
						}
						
						if(isset($apartment->energy_class))
						{
							++$number;
							$variables .= "<li>energy_class</li>";
						}
						$temp2 = $apartment->energy_class;
						
						//setting russian energy class to danish standards
						if($temp2 == "K" || $temp2 == "L" || $temp2 == "M")
						{
							$energy_class = "A-C";
						}
						elseif($temp2 == "O" || $temp2 == "P")
						{
							$energy_class = "D-E";
						}
						elseif($temp2 == "P")
						{
							$energy_class = "F";
						}
						else
						{
							$energy_class = "N/A";
						}
						
						//since price and sq meters are set as integers in db ,we have to be sure that in case square meters and price are float type,they will be properly stored
						if(isset($apartment->sq_meters))
						{
							++$number;
							$variables .= "<li>square_meters</li>";
						}
						$sq_meters = floor($apartment->sq_meters);
						
						++$number;
						$variables .= "<li>price</li>";
						$price = floor($apartment->price);
						
						++$number;
						$variables .= "<li>category</li>";
						$category_id = $apartment->cat;
						
						if(isset($apartment->listing_description))
						{
							++$number;
							$variables .= "<li>description</li>";
						}
						$description = $apartment->listing_description;
						
						if(isset($apartment->parkingSpot))
						{
							++$number;
							$variables .= "<li>parking</li>";
						}
						$temp3 = $apartment->parkingSpot;
						//parking spot in xml shows the number of available spots.Since there 1 or more spots,parking is available 
						if($temp3 > 0)
						{
							$parking = TRUE;
						}
						else
						{
							$parking = FALSE;
						}
						
						$adddata_PRST = $CONNPDO->prepare("INSERT INTO listing(apartment_id, country, price, sq_meters, availability, parking, rooms, bathrooms,	newly_built, energy_class, category_id,	description) VALUES(:apartment_id, :country, :price, :sq_meters, :availability, :parking, :rooms, :bathrooms,	:newly_built, :energy_class, :category_id,	:description)");
						$adddata_PRST -> bindValue(":apartment_id",$id);
						$adddata_PRST -> bindValue(":country",$country);
						$adddata_PRST -> bindValue(":price",$price);
						$adddata_PRST -> bindValue(":sq_meters",$sq_meters);
						$adddata_PRST -> bindValue(":availability",$availability);
						$adddata_PRST -> bindValue(":parking",$parking,PDO::PARAM_BOOL);
						$adddata_PRST -> bindValue(":rooms",$rooms);
						$adddata_PRST -> bindValue(":bathrooms",$bathrooms);
						$adddata_PRST -> bindValue(":newly_built",$newly_built,PDO::PARAM_BOOL);
						$adddata_PRST -> bindValue(":energy_class",$energy_class);
						$adddata_PRST -> bindValue(":category_id",$category_id);
						$adddata_PRST -> bindValue(":description",$description);
						$adddata_PRST -> execute() or die($CONNPDO->errorInfo());
						
						
						$response .= "<span style='color:green;'>Data from Part ".$counter." has been successfully stored!<br>".$number." variables have been fetched:<br><ol>".$variables."</ol></span><br>";
							
					}
					else
					{
						$response .= "<span style='color:red;'>An error occured in Part ".$counter.",XML Part could not be saved!</span><br>";
					}
				}
				
			}
			elseif( $country = "Germany")//For German listing XML,same train of thought as the Russian XML type
			{ 	
				$response = "";
				$counter = 0;
				
				foreach($xml->children() as $apartment)
				{
					$number = 0;
					$variables = "";
					
					++$counter;
					if(isset($apartment->listingPrice) && isset($apartment->listingType) && isset($apartment->listingCateg))//checking the parameter that must be present in our loaded XML
					{
						/* Concept for XML:
						for each child node of the main XML,it is checked if proper child nodes are set properly
						if set properly then the $number variable is increased and the variable is inserted to $variables
						in the form of an ordered list so as to inform user for successfully fetched data in the end of the process 
						
						The way the results are shown go like this:
						XML Part/Node -> variables/child nodes 
						
						In case the important parameters are not present in inserted XML,
						errors will be shown for each part/node 

						*/
						$country = "Germany";
						++$number;
						$variables = "<li>country</li>";
						
						$category_id = 0;
						
						$temp1 = "";
						$temp2 = "";
						$temp3 = "";
						
						if(isset($apartment->listingId))
						{
							++$number;
							$variables .= "<li>Apartment_id</li>";
						}
						$id = $apartment->listingId;
						
						if(isset($apartment->rooms))
						{
							++$number;
							$variables .= "<li>rooms</li>";
						}
						$rooms = $apartment->rooms;
						
						if(isset($apartment->newBuilding))
						{
							++$number;
							$variables .= "<li>newly_built</li>";
						}
						$temp1 = $apartment->newBuilding;
						//checking if building is new or not.If it's new(yes) then the newly built will be boolean true else false
						if($temp1 == "yes")
						{
							$newly_built = TRUE ;
						}
						else
						{
							$newly_built = FALSE;
						}
						
						if(isset($apartment->listingType))
						{
							++$number;
							$variables .= "<li>availability</li>";
						}
						$availability = $apartment -> listingType;
						
						if(isset($apartment->enrg_class) && $apartment->enrg_class > 63 && $apartment->enrg_class < 68)
						{
							++$number;
							$variables .= "<li>energy_class</li>";
						}
						$energy_class = $apartment->enrg_class;
						
						$bathrooms = 0;
						$tempo = $apartment->bathrooms;
						//bathrooms are in 2 categories full or WC.Each one is treated as one bathroom.In case there are both a full bathroom and WC in one building,then it is considered as having two bathrooms
						if($apartment->bathrooms->fullBathroom == "yes")
						{
							$bathrooms++;
						}
						if($apartment->bathrooms->WC == "yes")
						{
							$bathrooms++;
						}
						
						if($bathrooms != NULL)
						{
							++$number;
							$variables .= "<li>bathrooms</li>";
						}
						
						
						if(isset($apartment->squareMeters))
						{
							++$number;
							$variables .= "<li>Square Meters</li>";
						}
						$sq_meters = floor($apartment->squareMeters);
						
						++$number;
						$variables .= "<li>price</li>";
						$price = floor($apartment->listingPrice);
						
						if(isset($apartment->listingCateg))
						{
							++$number;
							$variables .= "<li>listing category</li>";
						}
						$temp2 = $apartment -> listingCateg;
						//checking listing category and converting it to proper for the database id.Listing is store in the second db table
						if($temp2 == "63")
						{
							$category_id = 2;
	
						}
						elseif($temp2 == "64")
						{
							$category_id = 4;
							
						}
						elseif($temp2 == "65")
						{
							$category_id = 3;
							
						}
						elseif($temp2 == "66")
						{
							$category_id = 5;
							
						}
						elseif($temp2 == "67")
						{
							$category_id = 1;
							
						}
						else
						{
							$category_id = 0;
						}
						
						if(isset($apartment->description))
						{
							++$number;
							$variables .= "<li>description</li>";
						}
						$description = $apartment->description;
						
						if(isset($apartment->with_parking))
						{
							++$number;
							$variables .= "<li>parking</li>";
						}
						$temp3 = $apartment->with_parking;
						//checking if parking exists and insert proper boolean value to variable
						if($temp3 == "true")
						{
							$parking = TRUE;
						}
						else
						{
							$parking = FALSE;
						}
						
						$adddata_PRST = $CONNPDO->prepare("INSERT INTO listing(apartment_id, country,price,	sq_meters, availability, parking, rooms, bathrooms,	newly_built, energy_class, category_id,	description) VALUES(:apartment_id, :country, :price, :sq_meters, :availability, :parking, :rooms, :bathrooms,	:newly_built, :energy_class, :category_id,	:description)");
						$adddata_PRST -> bindValue(":apartment_id",$id);
						$adddata_PRST -> bindValue(":country",$country);
						$adddata_PRST -> bindValue(":price",$price);
						$adddata_PRST -> bindValue(":sq_meters",$sq_meters);
						$adddata_PRST -> bindValue(":availability",$availability);
						$adddata_PRST -> bindValue(":parking",$parking,PDO::PARAM_BOOL);
						$adddata_PRST -> bindValue(":rooms",$rooms);
						$adddata_PRST -> bindValue(":bathrooms",$bathrooms);
						$adddata_PRST -> bindValue(":newly_built",$newly_built,PDO::PARAM_BOOL);
						$adddata_PRST -> bindValue(":energy_class",$energy_class);
						$adddata_PRST -> bindValue(":category_id",$category_id);
						$adddata_PRST -> bindValue(":description",$description);
						$adddata_PRST -> execute() or die($CONNPDO->errorInfo());
						
						$response .= "<span style='color:green;'>Data from Part ".$counter." has been successfully stored!<br>".$number." variables have been fetched:<br><ol>".$variables."</ol></span><br>";;
							
					}
					else
					{
						$response .= "<span style='color:red;'>An error occured in Part ".$counter.",please check again the file you uploaded,Thank you in advance!</span>";
					}
				}
				
			}
						
		}
		else
		{
			$response = "<span style='color:red;'>An error occured in PDO connection!</span>";
		}			
	}
	else
	{
		$response = "<span style='color:red;'>Please Insert legit File and Info!Thank you!</span>";//in case the two needed parameters are not properly set
	}
}
else
{
	$response = "";
}
?>
<!DOCTYPE html>
<html>
<head>
<title>Fetch XML</title>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
</head>
<body>
<center>
<div class="container">
<h3 style="color:red;">Fetch XML</h3>
<h4 style="color:gray;">Please insert your XML file and the country of origin(for formatting reasons) and click Submit XML!</h4>
</div>
<hr>
<div class="container">
<form action="<?php echo $_SERVER["PHP_SELF"]; ?>" method="post" enctype="multipart/form-data">
    <b><i>Select file to upload:<i></b>
	<br><br>
    <input style="width:300px;" class="form-control-file" type="file" name="data" accept="text/xml">
	<br>
    <select style="width:300px;" class="form-control" name="country">
	<option value="Russia">Russia</option>
	<option value="Germany">Germany</option>
	</select>
	<br>
	<input type="submit" class="btn btn-primary" name="submit" value="Submit XML!">
</form>
</div>
<br>
<?php echo $response; ?>
<hr>
<div class="container">
<a href="fetchStatus.php">Click Here to check current database status!</a>
</div>
<br>
<hr>
<br>
</center>
</body>
</html>
