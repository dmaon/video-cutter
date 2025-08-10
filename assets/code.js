const mergePlayer = document.querySelector("#merge-player");
const mergePlayerSlider = document.querySelector("#merge-player-slider");
const videoClips = document.querySelectorAll(".video-merge-clip");

let currentVideo = 0;
let currentSliderValue = 0;
let videoPlayerPlayFlag = false;
let duration = 0;

if (mergeList.length > 0) {
  // update time slider based on videos duration
  mergeList.forEach((element) => {
    duration += Math.abs(element.end_point - element.start_point);
  });
  mergePlayerSlider.setAttribute("max", Math.ceil(duration));

  mergePlayer.src = mergeList[currentVideo].after_address;
  mergePlayer.type = mergeList[currentVideo].type;
  mergePlayer.load();

  mergePlayer.addEventListener("ended", () => {
    currentVideo++;
    if (currentVideo < mergeList.length) {
      mergePlayer.src = mergeList[currentVideo].after_address;
      mergePlayer.type = mergeList[currentVideo].type;
      mergePlayer.load();
      mergePlayer.play();
    }
  });

  let isSeeking = false;

  mergePlayer.addEventListener("timeupdate", () => {
    if (isSeeking) return; // skip updating slider while seeking

    let currentSliderValue = 0;
    for (let i = 0; i < currentVideo; i++) {
      currentSliderValue += videoClips[i].duration;
    }
    currentSliderValue += mergePlayer.currentTime;
    mergePlayerSlider.value = currentSliderValue;
  });

  mergePlayerSlider.addEventListener("input", (event) => {
    isSeeking = true;

    const currentSliderValue = mergePlayerSlider.value;
    let findCurrentVideo = 0;
    let cumulativeDuration = 0;

    // find video index
    while (cumulativeDuration <= currentSliderValue) {
      cumulativeDuration += videoClips[findCurrentVideo++].duration;
    }
    findCurrentVideo -= 1; // because we need indexing to strat from 0, and remove extra addition in while loop

    // find video current time
    cumulativeDuration = 0;
    for (let i = 0; i <= findCurrentVideo; i++) {
      cumulativeDuration += videoClips[i].duration;
    }

    // set video
    if (findCurrentVideo !== currentVideo) {
      currentVideo = findCurrentVideo;
      mergePlayer.src = mergeList[findCurrentVideo].after_address;
      mergePlayer.type = mergeList[findCurrentVideo].type;
      mergePlayer.load();
    }

    // set correct time based on video

    let extraDuration = 0; // get before durations
    for (let i = 0; i < findCurrentVideo; i++) {
      extraDuration += videoClips[i].duration;
    }

    mergePlayer.currentTime = currentSliderValue - extraDuration;

    isSeeking = false;
  });

  mergePlayer.addEventListener("click", () => {
    videoPlayerPlayFlag = !videoPlayerPlayFlag;
    if (videoPlayerPlayFlag) {
      mergePlayer.play();
    } else {
      mergePlayer.pause();
    }
  });
}
