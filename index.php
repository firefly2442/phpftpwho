<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html;charset=utf-8">

<?php
//Want to disable the display of System Uptime?
//Set this variable to false
$display_system_uptime = true;


//Load style sheet
//<link rel="stylesheet" type="text/css" href="/stylesheets/default.css">
$template = $_GET['template'];
if ($template == "" || $template == "default") //load default style sheet
{
	echo "<link rel=\"stylesheet\" type=\"text/css\" href=\"stylesheets/default.css\">";
	$template = "default"; //in case template is not specified
}
else //load another style sheet
{
	echo "<link rel=\"stylesheet\" type=\"text/css\" href=\"stylesheets/" . $template . ".css\">";
}
?>

<title>phpftpwho</title>
</head>
<body>
<center>
<h2>Current FTP Access</h2>
</center>

<?php
//run ftpwho command
$output = shell_exec("ftpwho -v");

if ($output == "") //run alternative command
	$output = shell_exec("ftpwho -v -f /var/run/proftpd/proftpd.scoreboard");
	
if ($output == "") //run another alternative command
	$output = shell_exec("/usr/bin/ftpwho -v");
	
if ($output == "") //run another alternative command
	$output = shell_exec("/usr/local/bin/ftpwho -v");

if ($output == "")
	echo "<p class=\"error\"><b>Error, unable to run ftpwho.  Please check that Proftpd is running.</b></p><br>";
	


if (strpos($output, "up for") == true)  //only for standalone?
{
	$uptime = substr($output, strpos($output, "for") + 3, strlen($output));
	$uptime = substr($uptime, 0, strpos($uptime, "min") + 3);
	$output = substr($output, strpos($output, "min") + 3, strlen($output));
}
else  //only for inetd?
{
	$uptime = "Unknown";
	$output = substr($output, strpos($output, "daemon:") + 7, strlen($output));
}

if ($display_system_uptime == true)
{
	$computer_up = shell_exec("uptime");
	echo "<b>System Uptime: </b>\n" . $computer_up;
	echo "<br>";
}

echo "<b>FTP Server Uptime: </b>\n" . $uptime;


?>

<br><br>
<table>
<tr class="box_titles">
<td><b>ID</b></td>
<td><b>Username</b></td>
<td><b>Time Connected</b></td>
<td><b>Time Idle/Percent Complete</b></td>
<td><b>Operation</b></td>
<td><b>KB/s</b></td>
<td><b>Client</b></td>
<td><b>Server</b></td>
<td><b>Location</b></td>
</tr>
<?php

$user_count = 0;
if (!strpos($output, "no users")) //one or more users connected
{
	$tok = strtok($output, " \n");
	while ($tok != "Service" && $tok != "class" && $tok != false)  //check for ending
	{
		$user_count++;
		echo "<tr class=\"general_row\"><td>" . $tok; //ID
		$username = strtok(" \n");
		if ($username == "(none)")
			echo "<td class=\"authentication\">";
		else
			echo "<td>";
		echo $username . "</td>"; //user

		$timeconnected = strtok(" \n");
		if ($timeconnected == "(" || $timeconnected == "[")
		{
			$timeconnected = strtok(" \n");
			echo "<td>" . substr($timeconnected, 0, strlen($timeconnected)-1) . "</td>"; //time connected
		}
		else
			echo "<td>" . substr($timeconnected, 1, strlen($timeconnected)-2) . "</td>"; //time connected
		$time_idle = strtok(" \n");
		if ($time_idle == "(authenticating)") //user hasn't logged in yet
		{
			$client = "-";
			$server = "-";
			$kbps = "-";
			$location = "-";
			echo "<td class=\"authentication\">";
			echo $time_idle . "</td>";
			echo "<td>-</td><td>";
			$time_idle = strtok(" \n");

				$time_idle = str_replace("\t", "", $time_idle);
				if ($time_idle == "KB/s:")
				{
					$kbps = strtok(" \n");
					$time_idle = strtok(" \n");
					$time_idle = str_replace("\t", "", $time_idle);
				}
				if ($time_idle == "client:")
				{
					$client = strtok(" \n");
					$client = $client . strtok(" \n");
					$client = str_replace("[", "\n[", $client); //separate with newline
					$time_idle = strtok(" \n");
					$time_idle = str_replace("\t", "", $time_idle);
				}
				if ($time_idle == "server:")
				{
					$server = strtok(" \n");
					$server = $server . strtok(" \n");
					$server = str_replace("(", "\n(", $server); //separate with newline
					$time_idle = strtok(" \n");
					$time_idle = str_replace("\t", "", $time_idle);
				}
			
			$tok = $time_idle;
			echo $kbps . "</td>";
			echo "<td>" . $client . "</td>";
			echo "<td>" . $server. "</td>";
			echo "<td>-</td>";
		}
		else //some type of operation is being performed or was performed
		{
			$client = "-";
			$server = "-";
			$kbps = "-";
			$location = "";
			echo "<td>";
			if ($time_idle == "(")
			{
				$time_idle = strtok(" \n");
				echo substr($time_idle, 0, strlen($time_idle)-1) . "</td>"; //time idle
			}
			else
				echo $time_idle . "</td>";
			$time_idle = strtok(" \n");

			echo "<td class=\"operation\">";
			while ($time_idle != "KB/s:" && $time_idle != "client:" && $time_idle != "idle")
			{
				echo " " . $time_idle;
				$time_idle = strtok(" \n");
				$time_idle = str_replace("\t", "", $time_idle);
			}

			if ($time_idle == "idle")
			{
				echo $time_idle . "</td>";
				$time_idle = strtok(" \n");
			}
			

				$time_idle = str_replace("\t", "", $time_idle);
				if ($time_idle == "KB/s:")
				{
					$kbps = strtok(" \n");
					$time_idle = strtok(" \n");
					$time_idle = str_replace("\t", "", $time_idle);
				}
				if ($time_idle == "client:")
				{
					$client = strtok(" \n");
					$client = $client . strtok(" \n");
					$client = str_replace("[", "\n[", $client); //separate with newline
					$time_idle = strtok(" \n");
					$time_idle = str_replace("\t", "", $time_idle);
				}
				if ($time_idle == "server:")
				{
					$server = strtok(" \n");
					$server = $server . strtok("\n");
					$server = str_replace("(", "\n(", $server); //separate with newline
					$time_idle = strtok(" \n");
					$time_idle = str_replace("\t", "", $time_idle);
				}
				if ($time_idle == "protocol:")
				{
					//throw this information out, don't really need it
					$time_idle = strtok(" \n");
					$time_idle = strtok(" \n");
					$time_idle = str_replace("\t", "", $time_idle);
				}
				if ($time_idle == "location:")
				{
					$time_idle = strtok(" \n");
					while (!is_numeric($time_idle) && $time_idle != "Service" && $time_idle != "class:") //accounts for spaces in directory names and files
					{
						$location = $location . " " . $time_idle;
						$time_idle = strtok(" \n");
					}
				}
				if ($time_idle == "class:")
				{
					//throw this information out, don't really need it
					$time_idle = strtok(" \n");
					$time_idle = strtok(" \n");
					$time_idle = str_replace("\t", "", $time_idle);
				}

			echo "</td><td>";
			echo $kbps . "</td>";
			echo "<td>" . $client . "</td>";
			echo "<td>" . $server . "</td>";
			echo "<td>" . $location . "</td>";
			$tok = $time_idle;
		}
		echo "</tr>\n";
	}
}
else
{
	//nobody is connected so display nothing
	echo "<tr><td>-</td><td>-</td><td>-</td><td>-</td><td>-</td><td>-</td><td>-</td><td>-</td><td>-</td></tr>\n";
}
?>

<tr class="box_titles"><td><b>Total Users: <?=$user_count?></b></td></tr>
</table>
<br><br>
<form method=GET ACTION="index.php">
Template:
<select name="template">

<?php
//find all .css files in folder
//add them as possible selections
//make selected be default or whatever $template variable is
if ($handle = opendir('stylesheets')) //open stylesheet directory
{
   while (false !== ($file = readdir($handle))) //find all files
	{
		if ($file != "." && $file != "..") //take out these non-files
		{
			if (substr($file, -4) == ".css") //make sure we only include .css file endings
			{
				$file = str_replace(".css", "", $file);
				echo "<option value=\"" . $file . "\" ";
				if ($template === $file)
				{
					echo "selected";
				}
				echo ">" . $file;
			}
		}
   }
   closedir($handle);
}
?>

</select>
<input type="submit" value="Submit">
</form>

<br><hr>
<center><a href="http://www.rivetcode.com">phpftpwho</a>
<br>Version: 1.06</center>
</body>
</html>
