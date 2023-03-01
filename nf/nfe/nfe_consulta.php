

<html>

<head>

<script src="https://code.jquery.com/jquery-1.10.2.js"></script>

</head>

<body>

</body>


<script>

	$( document ).ready(function() {
	   fConsultaNfe();
	});
	
	function fConsultaNfe()
	{
	   $.ajax({
	      type: "POST",
	      dataType: "json",
	      url: "nfeconsulta.php",
	      data: $('#fSGM875XML').serialize(),
	      success: function(json){


	          return;

	      }
	  
	  });
	}


</script>


</html>