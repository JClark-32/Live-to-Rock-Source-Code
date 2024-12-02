var vidId = 'M7lc1UVf-VE';  // !!TO-DO -- REPLACE WITH CALL OR WHATEVER FROM USER INPUT

// Loads the IFrame Player API code asynchronously
var tag = document.createElement('script');

tag.src = "https://www.youtube.com/iframe_api";
var firstScriptTag = document.getElementsByTagName('script')[0];
firstScriptTag.parentNode.insertBefore(tag, firstScriptTag);

// Creates an <iframe> (and YouTube player) after the API code downloads
var player;
function onYouTubeIframeAPIReady() {
  player = new YT.Player('player', {
    height: '390',
    width: '640',
    videoId: vidId,
    playerVars: {
      'playsinline': 1
    },
    events: {}
  });
}