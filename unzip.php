
<?php

    // if $fileName php variable is set than    


    $zip = new ZipArchive;
    $fileName="tfp-plus.zip";          // create object
    $res = $zip->open($fileName);   // open archive
    if ($res === TRUE) {
      $zip->extractTo('./');        // extract contents to destination directory
      $zip->close();               //close the archieve    
      echo 'Extracted file "'.$fileName.'"';
    } else {
      echo 'Cannot find the file name "'.$fileName.'" (the file name should include extension (.zip, ...))';
    }

?>