<?php

	// Chat GPT queries:
	// Can you write the code for a website that has links to all the files in a directory called "recordings"?
	// Thanks! Can you add an upload button that only accepts .m4a files?

  $targetDirectory = 'files/';
  $targetFile = $targetDirectory . basename($_FILES["fileToUpload"]["name"]);
  $uploadOk = 1;
  $fileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));

  // Check if file already exists
  if (file_exists($targetFile)) {
    echo $targetFile;
    echo "File already exists.";
    $uploadOk = 0;
  }

  // Check file extension
  if ($fileType !== "m4a") {
    echo $fileType;
    echo "Only .m4a files are allowed.";
    $uploadOk = 0;
  }

  // Check if $uploadOk is set to 0 by an error
  if ($uploadOk == 0) {
    echo "Sorry, your file was not uploaded.";
  } else {
    // Move the uploaded file to the target directory
    if (move_uploaded_file($_FILES["fileToUpload"]["tmp_name"], $targetFile)) {
      echo "The file " . basename($_FILES["fileToUpload"]["name"]) . " has been uploaded.";
    } else {
      echo "Sorry, there was an error uploading your file.";
    }
  }
?>
