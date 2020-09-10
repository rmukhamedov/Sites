<!DOCTYPE HTML>
<html>
<head>
<title>PHP Form</title>
<meta charset="utf-8">
<link href="styles.css" rel="stylesheet" type="text/css" />
</head>
<body>
	<?php
		$feedback = $fname = $lname = $address = $anumber = $tnumber = $ssn = $total = $term = "";
		if (isset($_POST["submit"])){
			//remove whitespace from form inputs
			$fname = preg_replace('/\s+/', '', $_POST["fname"]);
			$lname = preg_replace('/\s+/', '', $_POST["lname"]);
			$address = trim($_POST["address"]);
			$anumber = preg_replace('/\s+/', '', $_POST["anumber"]);
			$tnumber = preg_replace('/\s+/', '', $_POST["tnumber"]);
			$ssn = preg_replace('/\s+/', '', $_POST["ssn"]);
			$total = preg_replace('/\s+/', '', $_POST["total"]);
			$term = preg_replace('/\s+/', '', $_POST["term"]);
			
			if (empty($fname) || empty($lname) || empty($address) || empty($tnumber) || empty($ssn) || empty($total) || empty($term)){
				//checks if required inputs are empty
				$feedback = "Please fill out all required fields marked with *";
			}else if(!preg_match("/^[0-9]\d{2}-\d{3}-\d{4}$/", $tnumber)){
				//checks for valid phone format
				$feedback = "Enter valid phone number (xxx-xxx-xxxx)";
			}else if(!preg_match("/^[0-9]\d{2}-\d{2}-\d{4}$/", $ssn)){
				//checks for valid ssn format
				$feedback = "Please enter valid SSN (xxx-xx-xxxx)";
			}else{
				$feedback = "Your information has been submitted";
				loanchart($total, $_POST["points"], $term);
			}
		}
		
		function loanChart($principal,$rate,$term,$per='12'){
			echo''."\n\t".'';
	
			$payment=($principal*(($rate*pow((1+$rate),$term)))/(pow((1+$rate),$term)-1));
	
			echo"<p id='paymentAmount'>Yearly Payment: $<em>".number_format($payment,2)."</em></p><table>";
			echo"\n\t".'<tr><td>Year: </td><td>Principal Payment: </td><td>Interest Payment: </td><td>Remaining Principal: </td></tr>';
	
			for($year=1;$year<=$term;$year++){
				$interestPayment=$principal*$rate;
				$principalPayment=$payment-$interestPayment;
				$principal-=$principalPayment;
		
				if($principal<=0){
					$principal=0;
				}
		
				echo"\n\t".'<tr><td>'.$year.'</td><td>$'.number_format($principalPayment,2).'</td><td>$'.number_format($interestPayment,2).'</td><td>$'.number_format($principal,2).'</td></tr>';
			}
			echo'</table>';
		}	
	?>
	
	<form method="post" action="index.php">
		<fieldset id="info">
			<legend>Customer Info</legend>
			<table>
			<tr>
				<td><label class="formlabel">First Name: </label></td>
				<td><input type="text" name="fname" class="forminput" value="<?php echo $fname;?>"/></td>
				<td class="req">*</td>
			</tr><tr>
				<td><label class="formlabel">Last Name: </label></td>
				<td><input type="text" name="lname" class="forminput" value="<?php echo $lname;?>"/></td>
				<td class="req">*</td>
			</tr><tr>
				<td><label class="formlabel">Street Address: </label></td>
				<td><input type="text" name="address" class="forminput" value="<?php echo $address;?>"/></td>
				<td class="req">*</td>
			</tr><tr>
				<td><label class="formlabel">Appartment Number: </label></td>
				<td><input type="text" name="anumber" class="forminput" value="<?php echo $anumber;?>"/></td>
				<td></td>
			</tr><tr>
				<td><label class="formlabel">Telephone Number: </label></td>
				<td><input type="text" name="tnumber" class="forminput" value="<?php echo $tnumber;?>"/></td>
				<td class="req">*</td>
			</tr><tr>
				<td><label class="formlabel">SSN: </label></td>
				<td><input type="text" name="ssn" class="forminput" value="<?php echo $ssn;?>"/></td>
				<td class="req">*</td>
			</tr><tr>
				<td><label class="formlabel">Total amount borrowed: </label></td>
				<td><input type="number" name="total" class="forminput" value="<?php echo $total;?>"/></td>
				<td class="req">*</td>
			</tr><tr>
				<td><label class="formlabel">Length of loan in months: </label></td>
				<td><input type="number" name="term" class="forminput" value="<?php echo $term;?>"/></td>
				<td class="req">*</td>
			</tr>
			</table>
		</fieldset>
		<fieldset>
			<legend>Points</legend>
			<select name="points">
				<option value="6.75">0.0</option>
				<option value="6.62">0.5</option>
				<option value="6.50">1.0</option>
				<option value="6.38">1.5</option>
				<option value="6.25">2.0</option>
			</select>
			<input type="submit" name="submit" value="Submit"/>
		</fieldset>	
	</form>	
	<p id="feedback"><?php echo $feedback; ?></p>
</body>
</html>