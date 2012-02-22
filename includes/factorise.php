<html>
		<head><ul style= color:white;><center>Is your number prime???</center>
		<title>Prime Numbers</title>
		</head>
		<body bgcolor="black">
		<ul style=color:white;>
		<p>Enter Number<form action="prime.php" method="post">

		<?php
		print(" <p> <input type = 'text' name= 'number' size= '8'>");
		$number = $_POST['number'];
		For($y = 2; $y <= $number; $y++) {
		  $z = $number % $y;
		  If($z ==0) {

				print("$y is a factor.");
				break;
		  }
		}
		?>
		</body>
</html>

