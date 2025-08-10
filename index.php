<?php

// no security check, and no fancy ui and interaction with js
// all ffmpeg commands used in a simple manner with no advanced commands

session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// for using system libraries for ffpmeg
putenv('LD_LIBRARY_PATH=/usr/lib/x86_64-linux-gnu');

$upload_folder = "uploads/";
$output_folder = "outputs/";
$mergeFileLink = "";

if (!isset($_SESSION["listOfVideos"]))
  $_SESSION["listOfVideos"] = []; // {id:time, title, type, size, address}

if (!isset($_SESSION["mergeList"]))
  $_SESSION["mergeList"] = []; // {id:time, address, type, after_address, start_point, end_point}

$op = isset($_POST["op"]) ? $_POST["op"] : "";
$selectVideoFile = isset($_FILES["select-video-file"]) ? $_FILES["select-video-file"] : null;


if ($op == "add-video" && $selectVideoFile != null && $selectVideoFile["name"] != "") {
  $id = time();
  $tempAddress = $selectVideoFile["tmp_name"];
  $videoFullName = $selectVideoFile["name"];
  $videoType = $selectVideoFile["type"];
  $videoSize = $selectVideoFile["size"];
  $newName = $id . '-' . $videoFullName;

  if (move_uploaded_file($tempAddress, $upload_folder . $newName)) {
    array_push($_SESSION["listOfVideos"], [
      "id" => $id,
      "title" => basename($newName),
      "type" => $videoType,
      "size" => $videoSize,
      "address" => $upload_folder . $newName,
    ]);
  }
}

if ($op == "reset") {
  $_SESSION["listOfVideos"] = [];
  $_SESSION["mergeList"] = [];
  $mergeFileLink = "";

  // remove all dump files in upload folder
  $files = glob($upload_folder . "*"); // get all file names
  foreach ($files as $file) { // iterate files
    if (is_file($file)) {
      unlink($file); // delete file
    }
  }

  // remove all dump files in output folder
  $files = glob($output_folder . "*"); // get all file names
  foreach ($files as $file) { // iterate files
    if (is_file($file)) {
      unlink($file); // delete file
    }
  }
}

if ($op == "remove-item" && isset($_POST["video-id"]) && is_numeric($_POST["video-id"])) {
  $videoId = $_POST["video-id"];
  $tempArray = [];
  foreach ($_SESSION["listOfVideos"] as $key => $value) {
    if ($value["id"] != $videoId)
      array_push($tempArray, $value);
    else {
      if (is_file($value['address']))
        unlink($value['address']); // remove file too
    }
  }
  $_SESSION["listOfVideos"] = $tempArray;
}

if (
  $op == "cut-video" && isset($_POST["video-address"]) && isset($_POST["video-type"]) &&
  isset($_POST["start-point"]) && is_numeric($_POST["start-point"]) &&
  isset($_POST["end-point"]) && is_numeric($_POST["end-point"])
) {
  $id = time();
  $videoAddress = $_POST["video-address"];
  $videoType = $_POST["video-type"];
  $startPoint = (int) $_POST["start-point"];
  $endPoint = (int) $_POST["end-point"];
  $outputAddress = $output_folder . 'output-' . $id . '.mp4';

  // cut video and save in a new address
  shell_exec('ffmpeg -i ' . $videoAddress . ' -ss ' . $startPoint . ' -to ' . $endPoint . ' -c copy ' . $outputAddress);

  // add to merge list
  array_push($_SESSION["mergeList"], [ // {id:time, address, type, after_address, start_point, end_point}
    "id" => $id,
    "address" => $videoAddress,
    "type" => $videoType,
    "after_address" => $outputAddress,
    "start_point" => $startPoint,
    "end_point" => $endPoint,
  ]);
}


if ($op == "merge-videos" && count($_SESSION["mergeList"]) > 0) {
  $id = time();
  $mergeFileLink = $output_folder . 'merge-' . $id . '.mp4';
  $fileListPath = $output_folder . 'merge-' . $id . '.txt';



  $fileContent = "";
  foreach ($_SESSION["mergeList"] as $key => $value) {
    $fileAddress = substr(strstr($value["after_address"], "/"), 1);
    $fileContent .= "file '" . addslashes($fileAddress) . "'\n";
  }

  if (file_put_contents($fileListPath, $fileContent)) {
    // cut video and save in a new address
    shell_exec('ffmpeg -f concat -safe 0 -i ' . $fileListPath . ' -c copy ' . $mergeFileLink);
    // echo 'ffmpeg -f concat -safe 0 -i ' . $fileListPath . ' -c copy ' . $mergeFileLink;
  }
}
?>

<!doctype html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Video Cutter</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-rbsA2VBKQhggwzxH7pPCaAqO46MgnOM80zW1RWuH61DGLwZJEdK2Kadq2F9CUG65" crossorigin="anonymous">


  <style>
  </style>

</head>

<body>
  <div class="container p-5">

    <div class="d-flex flex-column flex-md-row align-items-center pb-3 mb-4 border-bottom">
      <span class="fs-4">Video Cutter</span>
    </div>


    <ul class="nav nav-tabs" id="myTab" role="tablist">
      <li class="nav-item" role="presentation">
        <button class="nav-link active" id="cut-tab" data-bs-toggle="tab" data-bs-target="#cut-tab-pane" type="button" role="tab" aria-controls="cut-tab-pane" aria-selected="true">Cut</button>
      </li>
      <li class="nav-item" role="presentation">
        <button class="nav-link" id="merge-tab" data-bs-toggle="tab" data-bs-target="#merge-tab-pane" type="button" role="tab" aria-controls="merge-tab-pane" aria-selected="false">Merge</button>
      </li>
    </ul>
    <div class="tab-content" id="myTabContent">
      <div class="tab-pane fade show active" id="cut-tab-pane" role="tabpanel" aria-labelledby="cut-tab" tabindex="0">

        <?php
        if ($mergeFileLink != "")
          echo '<p class="mt-4">You merge file is ready. click <a href="' . $mergeFileLink . '" target="_blank">here</a>. </p>';
        ?>

        <form method="post" enctype="multipart/form-data" class="mt-2">
          <div class="input-group">
            <input type="file" accept="video/mp4" class="form-control" id="select-video-file" name="select-video-file" aria-describedby="select-video-file" aria-label="Upload">
            <button class="btn btn-outline-secondary" type="submit" name="op" value="add-video" id="add-video-btn">Add video</button>
            <button class="btn btn-warning" type="submit" name="op" value="reset" id="add-video-btn">Reset</button>
          </div>
        </form>

        <div id="videos-list">
          <?php
          if (count($_SESSION["listOfVideos"]) < 1)
            echo '<div class="mt-3">No video to show.</div>';
          else {
            foreach ($_SESSION["listOfVideos"] as $key => $value) : ?>
              <form method="post">
                <div class="mt-3">
                  <div class="border border-1 p-4 rounded">

                    <div class="container">
                      <div class="row">
                        <label for="exampleFormControlInput<?= $key + 1 ?>" class="form-label">Video address</label>
                        <div class="input-group mb-3" id="exampleFormControlInput<?= $key + 1 ?>">
                          <input type="hidden" name="video-id" value="<?= $value['id'] ?>" />
                          <input type="hidden" name="video-address" value="<?= $value['address'] ?>" />
                          <input type="hidden" name="video-type" value="<?= $value['type'] ?>" />
                          <input type="text" disabled style="width: 80%;" for="inputGroupSelect<?= $key + 1 ?>" value="<?= $value['address'] ?>" />
                          <button class="btn btn-danger" style="width: 20%;" id="inputGroupSelect<?= $key + 1 ?>" type="submit" name="op" value="remove-item">Remove this video</button>
                        </div>
                      </div>

                      <div class="row">
                        <div class="col">
                          <video id="video-<?= $key + 1 ?>" width="100%" height="100%" controls>
                            <source src="<?= $value['address'] ?>" type="<?= $value['type'] ?>">
                            Your browser does not support the video tag.
                          </video>
                        </div>
                        <div class="col">
                          <label for="start-point<?= $key + 1 ?>" class="form-label">Start point</label>
                          <input type="range" name="start-point" min="0" max="0" class="form-range" id="start-point<?= $key + 1 ?>">
                          <label for="end-point<?= $key + 1 ?>" class="form-label">End point</label>
                          <input type="range" name="end-point" min="0" max="0" class="form-range" id="end-point<?= $key + 1 ?>">
                          <button type="submit" name="op" value="cut-video" class="btn btn-primary">Cut and add to merge list</button>
                        </div>
                        <script>
                          let video<?= $key + 1 ?> = document.querySelector("#video-<?= $key + 1 ?>");
                          let startPoint<?= $key + 1 ?> = document.querySelector("#start-point<?= $key + 1 ?>");
                          let endPoint<?= $key + 1 ?> = document.querySelector("#end-point<?= $key + 1 ?>");

                          video<?= $key + 1 ?>.addEventListener('loadedmetadata', () => {
                            if (!isNaN(video<?= $key + 1 ?>.duration)) {
                              startPoint<?= $key + 1 ?>.setAttribute("max", Math.floor(video<?= $key + 1 ?>.duration));
                              endPoint<?= $key + 1 ?>.setAttribute("max", Math.floor(video<?= $key + 1 ?>.duration));
                            }
                          });

                          startPoint<?= $key + 1 ?>.addEventListener('input', function(event) {
                            video<?= $key + 1 ?>.currentTime = event.target.value;
                            if (event.target.value >= endPoint<?= $key + 1 ?>.value) {
                              endPoint<?= $key + 1 ?>.value = event.target.value;
                            }
                          });

                          endPoint<?= $key + 1 ?>.addEventListener('input', function(event) {
                            video<?= $key + 1 ?>.currentTime = event.target.value;
                            if (event.target.value <= startPoint<?= $key + 1 ?>.value) {
                              startPoint<?= $key + 1 ?>.value = event.target.value;
                            }
                          });
                        </script>
                      </div>
                    </div>

                    <div>

                    </div>
                  </div>
                </div>
              </form>
          <?php
            endforeach;
          }
          ?>
        </div>

      </div>
      <div class="tab-pane fade" id="merge-tab-pane" role="tabpanel" aria-labelledby="merge-tab" tabindex="0">

        <?php
        if (count($_SESSION["mergeList"]) < 1)
          echo '<div class="mt-3">No video to show.</div>';
        else {
          echo '<div class="mt-3">';
          echo '<form method="post"><button type="submit" name="op" value="merge-videos" class="btn btn-primary">Merge all</button></form>'; ?>



          <div class="mt-3">
            <div class="row" style="max-height: 400px;">
              <div class="col-sm-12 col-md-10 m-0 p-2">
                <video class="border border-1" id="merge-player" width="100%" height="400px" style="cursor: pointer;">
                  <source src="" type="">
                  Your browser does not support the video tag.
                </video>
                <div class="col">
                  <input type="range" min="0" max="0" class="form-range" id="merge-player-slider">
                </div>
                <script>
                  let mergeList = <?= json_encode($_SESSION["mergeList"]) ?>;
                </script>
              </div>
              <div class="col-sm-12 col-md-2 overflow-scroll m-0 p-1">
                <?php foreach ($_SESSION["mergeList"] as $key => $value) : ?>
                  <video class="border border-1 video-merge-clip" id="video-merge-<?= $key + 1 ?>" width="100%" height="100px" controls>
                    <source src="<?= $value['after_address'] ?>" type="<?= $value['type'] ?>">
                    Your browser does not support the video tag.
                  </video>
                <?php endforeach; ?>
              </div>
            </div>
          </div>




        <?php echo "</div>"; // end of container
        }
        ?>

      </div>
    </div>





  </div>


  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-kenU1KFdBIe4zVF0s0G1M5b4hcpxyD9F7jL+jjXkk+Q2h455rYXK/7HAuoJl+0I4" crossorigin="anonymous"></script>
  <script src="assets/code.js"></script>
</body>

</html>