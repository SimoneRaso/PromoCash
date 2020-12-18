<!DOCTYPE html>
<html>
<head>
	<?php include('../CommonPHPFunctions.php');?>
        <?php include('AdminPHPFunctions.php');?>
	<title>PromoCash - Modifica Amministratori</title>
	<link rel="stylesheet" type="text/css" href="../style.css">
	<style>
		.header {
			background: #003366;
		}
		button[name=register_btn] {
			background: #003366;
		}
	</style>
</head>
<body>
	<div class="header">
		<h2>Admin - Pannello Amministratori / Rivenditori</h2>
	</div>
	
	<?php $results = mysqli_query($db, "SELECT * FROM retailers WHERE user_type='retailer' AND status=1");?>
	
	<a href="home.php" style="color: green;">Torna alla Home</a>

	<div id="div_tabella_Utenti">
		<form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>" id="frm_tabella_Utenti">
			<table id="tab_utenti" class="table-fill">
			<thead>
				<tr>
					<th class="text-left">Id</th>
					<th class="text-left">UserName</th>
					<th class="text-left">NomeVisualizzato</th>
					<th class="text-left">UserType</th>
					<th class="text-left">User API</th>
					<th class="text-left">Token API</th>
				</tr>
			</thead>

			<tbody class="table-hover" style="cursor:pointer">
			<?php while ($row = mysqli_fetch_array($results)) { ?>
				<tr>
					<td><?php echo $row['id']; ?></td>
					<td><?php echo $row['username']; ?></td>
					<td><?php echo $row['nomevisualizzato']; ?></td>
					<td><?php echo $row['user_type']; ?></td>
					<td><?php echo $row['utente_DB_Telecash']; ?></td>
					<td><?php echo $row['password_DB_Telecash']; ?></td>
					<td>
						<a href="admin_panel.php?edit_retailer=<?php echo $row['id']; ?>" class="edit_btn" >Edit</a>
					</td>
					<td>
						<button type="submit" name="del_retailer" onclick="return confirm('Vuoi veramente cancellare questo Rivenditore?')" value="<?php echo $row['id']; ?>"> Delete</button>
					</td>
					<td> 
						<button type="submit" name="admin_username" value="<?php echo $row['username']; ?>"> Entra</button>
					</td>
				</tr>
			<?php } ?>
				<tr><td></td></tr>
			</tbody>
			</table> 
		</form>
	</div>
	
	<div id="divform_utenti">
	<form method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>">
		
		<?php echo display_error(); ?>
		<?php echo display_message(); ?>

		<input type="hidden" name="userid" value="<?php if (isset($id)) echo $id; ?>">
		<div class="input-group">
			<label>Username</label>
			<input type="text" name="username" value="<?php if (isset($username)) echo $username; ?>">
		</div>
		<div class="input-group">
			<label>Nome Visualizzato</label>
			<input type="text" name="nomevisualizzato" value="<?php if (isset($nomevisualizzato)) echo $nomevisualizzato; ?>">
		</div>		
		<div class="input-group">
			<label>User type</label>
			<select name="user_type" id="user_type">
<!--				<option value=""></option>-->
				<option value="admin"
						<?php if (isset($user_type)&&($user_type =='admin')) echo ' selected="selected"';?>
						>Admin</option>
				<option value="retailer"
						<?php if (isset($user_type)&&($user_type =='retailer')) echo ' selected="selected"';?>
						>Retailer</option>
<!--				<option value="user"
						<?php if (isset($user_type)&&($user_type =='user' )) echo ' selected="selected"';?>>User</option>
-->			</select>
		</div>
		<div class="input-group">
			<label>Password</label>
			<input type="password" name="password_1">
		</div>
		<div class="input-group">
			<label>Confirm new password</label>
			<input type="password" name="password_2">
		</div>
		<div class="input-group">
			<label>Utente DB Telecash</label>
			<input type="text" name="user_DB_Telecash" value="<?php if (isset($utente_DB_Telecash)) echo $utente_DB_Telecash; ?>">
		</div>
		<div class="input-group">
			<label>Password DB Telecash</label>
			<input type="text" name="psw_DB_Telecash" value="<?php if (isset($password_DB_Telecash)) echo $password_DB_Telecash; ?>">
		</div>
		<div class="input-group">
			<?php if (isset($update) ): ?>
			<button class="btn" type="submit" name="update_retailer" style="background: #556B2F;" >update</button>
			<?php else: ?>
			<button class="btn" type="submit" name="save_retailer" >Save</button>
			<?php endif ?>
		</div>
	</form>
	</div>
	<script>
		//Aggiungo alle celle della tabella utenti il click di richiamo alla funzione edit 
		var table = document.getElementById("tab_utenti");
		if (table != null) 
		{
			for (var i = 0; i < table.rows.length; i++) 
			{
				table.rows[i].onclick = function (e) 
				{
					window.location="admin_panel.php?edit=\""+e.currentTarget.getElementsByTagName("td")[0].innerHTML+"\"";
				};
			}
		}
		else
			alert ("tab_utenti è null!");
	
	</script>

</body>
</html>