---phpftpwho---

License: GPLv2 (see license.txt)

Installation is pretty simple.  Just copy the folder
to where you want it in your "www" directory in apache.

Now just navigate to your website and that directory.
It should come up and display your current stats.

If you want to disable the display of your System Uptime,
edit the index.php file with your favorite text editor.
Find the section at the top of the script that says:

$display_system_uptime = true;

and change it to:

$display_system_uptime = false;


*** Note ***
Make sure the server name does not have any spaces in it
otherwise this script will not correctly parse the information.
Thank you Michael for that tip! :)

