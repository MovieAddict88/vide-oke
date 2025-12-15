// Mock data for song list - will be replaced with API call
let songList = [];

const songListElement = document.getElementById('song-list');
const queueListElement = document.getElementById('queue-list');
const songNumberInput = document.getElementById('song-number-input');
const playButton = document.getElementById('play-button');
const nextButton = document.getElementById('next-button');
const videoPlayer = document.getElementById('video-player');

let songQueue = [];

// Fetch song list from API
async function fetchSongList() {
    try {
        const response = await fetch('../backend/api/songs.php');
        songList = await response.json();
        populateSongList();
    } catch (error) {
        console.error('Error fetching song list:', error);
    }
}


// Populate song list
function populateSongList() {
    songListElement.innerHTML = '';
    songList.forEach(song => {
        const li = document.createElement('li');
        li.textContent = `${song.song_number}: ${song.title} - ${song.artist}`;
        li.addEventListener('click', () => addSongToQueue(song));
        songListElement.appendChild(li);
    });
}

// Add song to queue
function addSongToQueue(song) {
    songQueue.push(song);
    renderQueue();
}

// Render the song queue
function renderQueue() {
    queueListElement.innerHTML = '';
    songQueue.forEach((song, index) => {
        const li = document.createElement('li');
        li.textContent = `${song.title} - ${song.artist}`;
        if (index === 0) {
            li.textContent += " (Now Playing)";
        }
        queueListElement.appendChild(li);
    });
}

// Play the current song
function playSong() {
    if (songQueue.length > 0) {
        videoPlayer.src = songQueue[0].video_source;
        videoPlayer.play();
        renderQueue();
    }
}

// Play the next song
function nextSong() {
    if (songQueue.length > 1) {
        songQueue.shift();
        playSong();
    } else if (songQueue.length === 1) {
        songQueue.shift();
        videoPlayer.src = '';
        renderQueue();
    }
}

// Event Listeners
playButton.addEventListener('click', () => {
    const songNumber = songNumberInput.value.trim();
    if (songNumber) {
        const song = songList.find(s => s.song_number === songNumber);
        if (song) {
            addSongToQueue(song);
            if (songQueue.length === 1) {
                playSong();
            }
            songNumberInput.value = '';
        } else {
            alert('Song not found!');
        }
    }
});

nextButton.addEventListener('click', nextSong);

videoPlayer.addEventListener('ended', nextSong);


// Initial load
fetchSongList();
