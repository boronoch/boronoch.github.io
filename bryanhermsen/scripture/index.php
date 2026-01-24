<!DOCTYPE html>
<html>
<head>
  <title>The Word Proclaimed</title>
</head>
<body>
  <h1>Recordings</h1>
  <br>
            
  <ul>
    <?php
        $directory = 'files/';

        // Get all files in the directory
        $files = scandir($directory);

        // Remove "." and ".." from the file list
        $files = array_diff($files, array('.', '..'));
	  
	    // Sort files in reverse alphabetical order
        rsort($files);

        // Loop through the files and create links
        foreach ($files as $file) {
            echo '<li><a href="' . $directory . $file . '" target="_blank">' . $file . '</a></li>';
        }
      
    ?>
  </ul>
</body>
</html>
