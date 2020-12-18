<!doctype html>
<html>
<head>
	<script type="text/javascript" src="../log4javascript.js"></script>
	<script type="text/javascript">
		var log = log4javascript.getDefaultLogger();
	</script>
	<script type="text/javascript" src="../moment.js"></script>
	<script src="../jquery-3.3.1.min.js"></script>
	<script type="text/javascript" src="../CommonScriptFunctions.js"></script>

	<?php
	include('../CommonPHPFunctions.php');
	include('../CouponFunctions.php');

	if (!isLoggedIn()) {
		$_SESSION['msg'] = "Prima devi fare il login";
		header('location: ../index.php');
	}
	?>
	<meta charset="utf-8">
	<title>PromoCash - Stato Coda</title>

	<style type='text/css'>
	  .my-legend .legend-title {
		text-align: left;
		margin-bottom: 5px;
		font-weight: bold;
		font-size: 90%;
		}
	  .my-legend .legend-scale ul {
		margin: 0;
		margin-bottom: 5px;
		padding: 0;
		float: left;
		list-style: none;
		}
	  .my-legend .legend-scale ul li {
		font-size: 80%;
		list-style: none;
		margin-left: 0;
		line-height: 18px;
		margin-bottom: 2px;
		}
	  .my-legend ul.legend-labels li span {
		display: block;
		float: left;
		height: 16px;
		width: 30px;
		margin-right: 5px;
		margin-left: 0;
		border: 1px solid #999;
		}
	  .my-legend a {
		color: #777;
		}
	</style>	
	
	<script>
	</script>
</head>

<body>
	<div class='my-legend'>
		<div class='legend-title'>Significato dei colori</div>
		<div class='legend-scale'>
		  <ul class='legend-labels'>
			<li><span style='background:#FFEE00;'></span>Coupon da Elaborare</li>
			<li><span style='background:#C0C0C0;'></span>Coupon in lavorazione</li>
			<li><span style='background:#00FF00;'></span>Coupon Inserito</li>
			<li><span style='background:#FF0000;'></span>Errore API</li>
			<li><span style='background:#FF4400;'></span>Errore di comunicazione</li>
		  </ul>
		</div>
		<div style="clear:both;"></div> 
	</div>
	
	<form id="frmDelay" class="frmCommon" method="post" action="<?=($_SERVER['PHP_SELF'])?>" enctype="multipart/form-data">
		<fieldset>Intervallo
		<select name="selected_filtro_tempo" onchange="this.form.submit()">
			<?php	
                            $filtro_tempo = isset($_POST['selected_filtro_tempo']) ? $_POST['selected_filtro_tempo'] : 'today';	
			?>
			
			<option value='all'			<?php if ($filtro_tempo == "all") 			echo "selected=\"selected\""; ?> >Tutto</option>
			<option value='last_mouth'	<?php if ($filtro_tempo == "last_mouth") 	echo "selected=\"selected\""; ?> >Ultimo Mese</option>
			<option value='last_week'	<?php if ($filtro_tempo == "last_week")		echo "selected=\"selected\""; ?> >Ultima Settimana</option>
			<option value='today'		<?php if ($filtro_tempo == "today")			echo "selected=\"selected\""; ?> >Oggi</option>
		</select>
		</fieldset>
	</form>
	
	<div id="divElenco_Coupon" >
		<table {table-layout:fixed}>
			<tr>
				<th>Coupon</th>
				<!--<th>Status</th>-->
				<th>merchant_code</th>
				<th>phone_service</th>
				<th>coupon_value</th>
				<th>start_date</th>
				<th>start_time</th>
				<th>stop_date</th>
				<th>stop_time</th>
                                <th>Unlimited</th>
				<th>Errore</th>
				<th>Descrizione</th>
			</tr>
		<?php
		// Assign the query
		$query = "SELECT coupon_code, status, merchant_code, phone_service, coupon_value, start_date, start_time, stop_date,
				stop_time, unlimited, error_description, error_message ";
		$query.= "FROM coupon_queue";
		$query.= " WHERE username='".$_SESSION['db_user']."' AND password='".$_SESSION['db_password']."' AND customer_limit='".$_SESSION['user']['username']."'";
		$today= date("Y-m-d");
		switch($filtro_tempo){
			case 'all':
				//non faccio nessun filtro
				break;
			case 'last_mouth':
				$query.= " AND STR_TO_DATE(`start_date`, '%d/%c/%Y') > (NOW() - INTERVAL 30 DAY) ";
				break;
			case 'last_week':
				$query.= " AND STR_TO_DATE(`start_date`, '%d/%c/%Y') > (NOW() - INTERVAL 7 DAY) ";
				break;
			case 'today':
				$query.= " AND STR_TO_DATE(`start_date`, '%d/%c/%Y') > (NOW() - INTERVAL 1 DAY) ";
				break;
			default:
				break;
		}
		$query.=" ORDER BY last_action DESC";
		logWrite("info","$query");

		// Execute the query
		$result = mysqli_query($db,$query);
		if (!$result){
			die ("Could not query the database: <br />". mysqli_error($db));
		}
		?>
		<?php
		// Change colours
		function getcolour($status)
		{
			if($status == "1")
				return '#FFEE00';//giallo da elaborare
			elseif($status == "2")
				return '#C0C0C0';//grigio in lavorazione
			elseif($status == "0")
				return '#00FF00';//verde chiaro ok
			elseif($status < 0)
				return '#FF0000';//rosso errore api
			elseif($status == "99")
				return '#FF4400';//arancione errore comunicazione
		}
		// Fetch and display the results	
		while ($row = mysqli_fetch_array($result)){
			$coupon_code = $row["coupon_code"];
			$status = $row["status"];
			$merchant_code = $row["merchant_code"];
			$phone_service = $row["phone_service"];
			$coupon_value = $row["coupon_value"];
			$coupon_value = $row["coupon_value"];
			$start_date = $row["start_date"];
			$start_time = $row["start_time"];
			$stop_date = $row["stop_date"];
			$stop_time = $row["stop_time"];
                        $unlimited = $row["unlimited"];
			$CodeDescription= $row["error_description"];
			$MessageError= $row["error_message"];
			echo "<tr>";
			echo "<td bgcolor='". getcolour($status) ."';>$coupon_code</td>";
			echo "<td bgcolor='". getcolour($status) ."';>$merchant_code</td>";
			echo "<td bgcolor='". getcolour($status) ."';>$phone_service</td>";
			echo "<td bgcolor='". getcolour($status) ."';>$coupon_value</td>";
			echo "<td bgcolor='". getcolour($status) ."';>$start_date</td>";
			echo "<td bgcolor='". getcolour($status) ."';>$start_time</td>";
			echo "<td bgcolor='". getcolour($status) ."';>$stop_date</td>";
			echo "<td bgcolor='". getcolour($status) ."';>$stop_time</td>";
                        echo "<td bgcolor='". getcolour($status) ."';>$unlimited</td>";
			echo "<td overflow:hidden; white-space:nowrap; bgcolor='". getcolour($status) ."';>$CodeDescription</td>";
			echo "<td overflow:hidden; white-space:nowrap; bgcolor='". getcolour($status) ."';>$MessageError</td>";
			echo "</tr>";
		}
		?>
		</table>	
	</div>
	
</body>
</html>