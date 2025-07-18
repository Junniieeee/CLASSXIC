<?php
include "myconnector.php";
session_start();

if (!isset($_GET['file_url'])) {
    die("No file selected.");
}

$file_url = urldecode($_GET['file_url']);
$role = isset($_SESSION['role']) ? $_SESSION['role'] : '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modules</title>
    <link rel="stylesheet" href="modules.css">
</head>
<body>
    <nav class="navbar">
    <!-- Burger Menu -->
        <div class="burger-menu" onclick="toggleSidebar()">
            <div></div>
            <div></div>
            <div></div>
        </div>
        <!-- Title -->
        <div class="nav-center">ClassXic</div>
        <!-- User Info -->
        <div class="user-info">
            <img src="Images/user-svgrepo-com.svg" alt="User Icon">
        </div>
    </nav>

    <!-- Sidebar -->
    <?php if ($role === 'tutor'): ?>
    <div class="sidebar" id="sidebar">
        <ul>
            <li><a href="tutorlanding.php"><img src="Images/home-svgrepo-com.svg" alt="Home Icon"> Home</a></li>
            <li><a href="tutorcalendar.php"><img src="Images/calendar-month-svgrepo-com.svg" alt="Calendar Icon"> Calendar</a></li>
            <li><a href="tutormodule.php"><img src="Images/book-svgrepo-com.svg" alt="Modules Icon"> Modules</a></li>
            <li><a href="studentlist.php"><img src="Images/user-svgrepo-com.svg" alt="Students Icon"> Students</a></li>
            <!-- <li><a href="progress.php"><img src="Images/progress-svgrepo-com.svg" alt="Progress Icon">Progress</a></li>-->
            <li>
                <a href="#" class="dropdown-toggle">-Option-</a>
                <ul class="dropdown-menu">
                    <li><a href="#features-section"><img src="Images/idea-svgrepo-com.svg" alt="Features Icon">Features</a></li>
                    <li><a href="#about-us"><img src="Images/about-filled-svgrepo-com.svg" alt="About-Us Icon">About Us</a></li>
                    <li><a href="#settings"><img src="Images/settings-2-svgrepo-com.svg" alt="Settings Icon"> Settings</a></li>
                    <li><a href="logout.php"><img src="Images/logout-svgrepo-com.svg" alt="Logout Icon">Log out</a></li>
                </ul>
            <li>
        </ul>
    </div>
    <?php else: ?>
    <div class="sidebar" id="sidebar">
        <ul>
            <li><a href="landingpage.php"><img src="Images/home-svgrepo-com.svg" alt="Home Icon"> Home</a></li>
            <li><a href="calendar.php"><img src="Images/calendar-month-svgrepo-com.svg" alt="Calendar Icon"> Calendar</a></li>
            <li><a href="studentmodule.php"><img src="Images/book-svgrepo-com.svg" alt="Modules Icon"> Modules</a></li>
            <li><a href="tutorlist.php"><img src="Images/user-svgrepo-com.svg" alt="Tutors Icon"> Tutor</a></li>
            <!-- <li><a href="progress.php"><img src="Images/progress-svgrepo-com.svg" alt="Progress Icon">Progress</a></li>-->
            <li>
                <a href="#" class="dropdown-toggle">-Option-</a>
                <ul class="dropdown-menu">
                    <li><a href="#features-section"><img src="Images/idea-svgrepo-com.svg" alt="Features Icon">Features</a></li>
                    <li><a href="#about-us"><img src="Images/about-filled-svgrepo-com.svg" alt="About-Us Icon">About Us</a></li>
                    <li><a href="#settings"><img src="Images/settings-2-svgrepo-com.svg" alt="Settings Icon"> Settings</a></li>
                    <li><a href="logout.php"><img src="Images/logout-svgrepo-com.svg" alt="Logout Icon">Log out</a></li>
                </ul>
            <li>
        </ul>
    </div>
    <?php endif; ?>
    <div id="pdf-viewer-container">
        <h1>PDF Viewer</h1>
        <button id="playPauseBtn" disabled>▶️ Play</button>
        <button id="stopBtn" disabled>⏹️ Stop</button>
        <label for="speedControl">Speed:</label>
        <input type="range" id="speedControl" min="0.5" max="2" step="0.1" value="1">
        <span id="speedValue">1x</span>
        <button id="highlightNarrateBtn" disabled>🎤 Narrate Highlighted</button>
        <div style="margin-bottom:10px;">
            <label for="voiceSelect">Voice:</label>
            <select id="voiceSelect"></select>
        </div>
        <div id="content" tabindex="0" aria-live="polite" aria-label="Converted PDF text will appear here"></div>

        <div id="popup" role="dialog" aria-modal="true" aria-live="assertive" aria-hidden="true">
            <h3 id="popup-word">Word</h3>
            <p><strong>Phonetic:</strong> <span id="popup-phonetic">-</span></p>
            <p><strong>Meaning:</strong> <span id="popup-meaning">-</span></p>
            <button id="playPronunciation" aria-label="Play pronunciation">
                🔊
                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M3 10v4l6 4V6l-6 4zm13.5 2c0-1.77-1.02-3.29-2.5-4.03v8.06c1.48-.73 2.5-2.25 2.5-4.03z"/></svg>
            </button>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.8.162/pdf.min.js"></script>
    <script>
        (() => {
            const contentDiv = document.getElementById('content');
            const playPauseBtn = document.getElementById('playPauseBtn');
            const stopBtn = document.getElementById('stopBtn');
            const speedControl = document.getElementById('speedControl');
            const speedValue = document.getElementById('speedValue');
            const highlightNarrateBtn = document.getElementById('highlightNarrateBtn');
            const popup = document.getElementById('popup');
            const popupWord = document.getElementById('popup-word');
            const popupPhonetic = document.getElementById('popup-phonetic');
            const popupMeaning = document.getElementById('popup-meaning');
            const playBtn = document.getElementById('playPronunciation');
            const voiceSelect = document.getElementById('voiceSelect');
            let voices = [];

            let pdfText = '';
            let currentWord = '';
            let wordBoundaries = [];
            let wordSpans = [];
            let utterance = null;
            let isPaused = false;
            let currentWordIdx = 0;
            let startWordIdx = 0; // Add this at the top with your other let variables

            // Helper for TTS highlighting
            function highlightWord(index) {
                document.querySelectorAll('.word').forEach(span => {
                    span.classList.remove('tts-highlight');
                });
                const span = document.querySelector(`.word[data-word-index="${index}"]`);
                if (span) {
                    span.classList.add('tts-highlight');
                }
            }

            // After rendering the text, collect word spans and their positions
            function collectWordSpans() {
                wordSpans = Array.from(document.querySelectorAll('.word'));
                wordBoundaries = [];
                let charCount = 0;
                wordSpans.forEach(span => {
                    wordBoundaries.push({
                        start: charCount,
                        end: charCount + span.textContent.length
                    });
                    charCount += span.textContent.length + 1; // +1 for space or separator
                });
            }

            pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.8.162/pdf.worker.min.js';
            async function extractTextItemsFromPdf(fileUrl) {
                const pdfDoc = await pdfjsLib.getDocument(fileUrl).promise;
                let items = [];
                for (let i = 1; i <= pdfDoc.numPages; i++) {
                    const page = await pdfDoc.getPage(i);
                    const textContent = await page.getTextContent();
                    let lastY = null;
                    let lastX = null;
                    let line = '';
                    let lineFontSizes = [];
                    textContent.items.forEach(item => {
                        const thisY = item.transform[5];
                        const thisX = item.transform[4];

                        // New line if Y changes significantly
                        if (lastY !== null && Math.abs(thisY - lastY) > 5) { // Adjust threshold
                            if (line.trim().length > 0) {
                                items.push({
                                    text: line.trim(),
                                    fontSize: Math.round(Math.max(...lineFontSizes))
                                });
                            }
                            line = '';
                            lineFontSizes = [];
                            lastX = null;
                        }

                        // Add space if X gap is big (e.g. > 2)
                        if (lastX !== null && Math.abs(thisX - lastX) > 2) {
                            line += ' ';
                        }
                        line += item.str;
                        lineFontSizes.push(item.transform[0]);
                        lastY = thisY;
                        lastX = thisX + item.width; // move to end of current item
                    });

                    // Push last line of the page
                    if (line.trim().length > 0) {
                        items.push({
                            text: line.trim(),
                            fontSize: Math.round(Math.max(...lineFontSizes))
                        });
                    }

                    // Add a page break (optional)
                    items.push({text: '', fontSize: 0});
                }
                return items;
            }

            function renderTextItemsToHtml(items) {
                const fragment = document.createDocumentFragment();
                let wordIndex = 0;
                items.forEach(item => {
                    if (!item.text.trim()) return;
                    let className = '';
                    if (item.fontSize >= 20) {
                        className = 'pdf-title';
                    } else if (item.fontSize >= 16) {
                        className = 'pdf-subtitle';
                    } else {
                        className = 'pdf-paragraph';
                    }
                    const div = document.createElement('div');
                    div.className = className;
                    // Word highlighting support
                    item.text.split(/(\s+|[.,!?;:"'“”‘’\-\(\)\[\]{}])/g).forEach(part => {
                        if (part.trim() === '') {
                            div.appendChild(document.createTextNode(part));
                            return;
                        }
                        if (/^\w+$/u.test(part.trim())) {
                            const span = document.createElement('span');
                            span.className = 'word';
                            span.textContent = part;
                            span.tabIndex = 0;
                            span.setAttribute('data-word-index', wordIndex++);
                            div.appendChild(span);
                        } else {
                            div.appendChild(document.createTextNode(part));
                        }
                    });
                    fragment.appendChild(div);
                });
                return fragment;
            }

            async function loadPdf(fileUrl) {
                contentDiv.innerHTML = 'Loading document... Please wait.';
                playPauseBtn.disabled = true;
                stopBtn.disabled = true;
                highlightNarrateBtn.disabled = true;
                try {
                    const items = await extractTextItemsFromPdf(fileUrl);
                    pdfText = items.map(item => item.text).join('\n');
                    contentDiv.innerHTML = '';
                    const fragment = renderTextItemsToHtml(items);
                    contentDiv.appendChild(fragment);
                    collectWordSpans(); // <-- Add this after rendering
                    playPauseBtn.disabled = false;
                    stopBtn.disabled = false;
                    highlightNarrateBtn.disabled = false;
                } catch (e) {
                    contentDiv.innerHTML = 'Failed to load PDF content.';
                    console.error(e);
                }
            }

            loadPdf('<?php echo htmlspecialchars($file_url); ?>');

            function populateVoices() {
                voices = window.speechSynthesis.getVoices();
                voiceSelect.innerHTML = '';
                voices.forEach((voice, i) => {
                    const option = document.createElement('option');
                    option.value = i;
                    option.textContent = `${voice.name} (${voice.lang})${voice.default ? ' [default]' : ''}`;
                    voiceSelect.appendChild(option);
                });
            }
            window.speechSynthesis.onvoiceschanged = populateVoices;
            populateVoices();

            playPauseBtn.addEventListener('click', () => {
                if (!utterance) {
                    wordSpans = Array.from(document.querySelectorAll('.word'));
                    let wordIdx = startWordIdx; // Start from selected word or 0
                    // Build text from startWordIdx
                    let speakText = wordSpans.slice(startWordIdx).map(span => span.textContent).join(' ');
                    utterance = new SpeechSynthesisUtterance(speakText);
                    utterance.lang = 'en-US';
                    utterance.rate = parseFloat(speedControl.value);
                    const selectedVoice = voices[voiceSelect.value] || voices[0];
                    utterance.voice = selectedVoice;

                    utterance.onboundary = function(event) {
                        if (event.name === 'word') {
                            highlightWord(wordIdx++);
                        }
                    };
                    utterance.onend = () => {
                        playPauseBtn.textContent = '▶️ Play';
                        utterance = null;
                        highlightWord(-1);
                        startWordIdx = 0; // Reset to beginning after finish
                    };
                    window.speechSynthesis.speak(utterance);
                    playPauseBtn.textContent = '⏸️ Pause';
                } else if (isPaused) {
                    window.speechSynthesis.resume();
                    playPauseBtn.textContent = '⏸️ Pause';
                    isPaused = false;
                } else {
                    window.speechSynthesis.pause();
                    playPauseBtn.textContent = '▶️ Play';
                    isPaused = true;
                }
            });

            // When stop is clicked, reset to beginning
            stopBtn.addEventListener('click', () => {
                if (utterance) {
                    window.speechSynthesis.cancel();
                    utterance = null;
                    playPauseBtn.textContent = '▶️ Play';
                    highlightWord(-1); // Remove highlight
                    startWordIdx = 0; // Reset to beginning
                }
            });

            // When a word is clicked, start reading from that word
            contentDiv.addEventListener('click', async (event) => {
                const target = event.target;
                if (!target.classList.contains('word')) return;

                currentWord = target.textContent;
                popupWord.textContent = currentWord;
                popupPhonetic.textContent = 'Loading...';
                popupMeaning.textContent = 'Loading...';
                popup.style.display = 'block';

                // Position the popup beside the clicked word
                const rect = target.getBoundingClientRect();
                popup.style.top = `${rect.bottom + window.scrollY + 5}px`;
                popup.style.left = `${rect.left + window.scrollX}px`;
                // API call
                try {
                    const response = await fetch(`https://api.dictionaryapi.dev/api/v2/entries/en/${encodeURIComponent(currentWord.toLowerCase())}`);
                    if (!response.ok) throw new Error('No data found');
                    const data = await response.json();
                    const entry = data[0];
                    popupPhonetic.textContent = entry.phonetic || '-';
                    popupMeaning.textContent = entry.meanings[0]?.definitions[0]?.definition || 'No information found.';
                } catch (e) {
                    popupPhonetic.textContent = '-';
                    popupMeaning.textContent = 'No information found.';
                }

                // Start reading from clicked word
                if (utterance) {
                    window.speechSynthesis.cancel();
                    utterance = null;
                }
                startWordIdx = parseInt(target.getAttribute('data-word-index'));
                playPauseBtn.textContent = '⏸️ Pause';
                playPauseBtn.click(); // Start reading from this word
            });

            document.addEventListener('click', (e) => {
                if (!popup.contains(e.target) && !e.target.classList.contains('word')) {
                    popup.style.display = 'none';
                }
            });

            playBtn.addEventListener('click', () => {
                if (!currentWord) return;
                const utterance = new SpeechSynthesisUtterance(currentWord);
                utterance.lang = 'en-US';
                let selectedVoiceIndex = voiceSelect.value;
                if (selectedVoiceIndex === "" || !voices[selectedVoiceIndex]) {
                    utterance.voice = voices[0];
                } else {
                    utterance.voice = voices[selectedVoiceIndex];
                }
                window.speechSynthesis.speak(utterance);
            });

            voiceSelect.addEventListener('change', () => {
                if (utterance) {
                    window.speechSynthesis.cancel();
                    utterance = null;
                    playPauseBtn.textContent = '▶️ Play';
                    highlightWord(-1); // Remove highlight
                    // Optionally, auto-restart narration with new voice:
                    // playPauseBtn.click();
                }
            });

            speedControl.addEventListener('input', () => {
                speedValue.textContent = speedControl.value + 'x';
            });

            // Show phonetic popup on hover
            contentDiv.addEventListener('mouseover', async (event) => {
                const target = event.target;
                if (!target.classList.contains('word')) return;

                currentWord = target.textContent;
                popupWord.textContent = currentWord;
                popupPhonetic.textContent = 'Loading...';
                popupMeaning.textContent = 'Loading...';
                popup.style.display = 'block';

                // Position the popup beside the hovered word
                const rect = target.getBoundingClientRect();
                popup.style.top = `${rect.bottom + window.scrollY + 5}px`;
                popup.style.left = `${rect.left + window.scrollX}px`;

                // API call
                try {
                    const response = await fetch(`https://api.dictionaryapi.dev/api/v2/entries/en/${encodeURIComponent(currentWord.toLowerCase())}`);
                    if (!response.ok) throw new Error('No data found');
                    const data = await response.json();
                    const entry = data[0];
                    popupPhonetic.textContent = entry.phonetic || '-';
                    popupMeaning.textContent = entry.meanings[0]?.definitions[0]?.definition || 'No information found.';
                } catch (e) {
                    popupPhonetic.textContent = '-';
                    popupMeaning.textContent = 'No information found.';
                }
            });

            // Hide phonetic popup on mouseout, but NOT if moving to the popup
            contentDiv.addEventListener('mouseout', (event) => {
                const target = event.target;
                if (!target.classList.contains('word')) return;
                // If moving to the popup, don't hide
                if (event.relatedTarget && popup.contains(event.relatedTarget)) return;
                popup.style.display = 'none';
            });

            // Also hide the popup when mouse leaves the popup itself
            popup.addEventListener('mouseleave', () => {
                popup.style.display = 'none';
            });

            highlightNarrateBtn.addEventListener('click', () => {
    // Find the currently highlighted word
    const highlighted = document.querySelector('.word.tts-highlight');
    let wordToSpeak = '';
    if (highlighted) {
        wordToSpeak = highlighted.textContent;
    } else {
        // Optionally, fallback to selected text
        const selection = window.getSelection();
        wordToSpeak = selection.toString().trim();
    }
    if (!wordToSpeak) return;

    const utterance = new SpeechSynthesisUtterance(wordToSpeak);
    utterance.lang = 'en-US';
    let selectedVoiceIndex = voiceSelect.value;
    if (selectedVoiceIndex === "" || !voices[selectedVoiceIndex]) {
        utterance.voice = voices[0];
    } else {
        utterance.voice = voices[selectedVoiceIndex];
    }
    window.speechSynthesis.speak(utterance);
});
        })();

        //burger
        function toggleSidebar() {
      const sidebar = document.getElementById('sidebar');
      sidebar.classList.toggle('active');
    }

    document.addEventListener('click', function (event) {
      const sidebar = document.getElementById('sidebar');
      const burgerMenu = document.querySelector('.burger-menu');

      // Close sidebar if clicked outside
      if (!sidebar.contains(event.target) && !burgerMenu.contains(event.target)) {
        sidebar.classList.remove('active');
      }
    });

    document.querySelectorAll('.dropdown-toggle').forEach(item => {
        item.addEventListener('click', function(event) {
            event.preventDefault(); // Prevent the default anchor behavior
            const dropdownMenu = this.nextElementSibling; // Get the dropdown menu
            dropdownMenu.classList.toggle('active'); // Toggle the active class
        });
    });
    // Close the dropdown if clicked outside
    document.addEventListener('click', function(event) {
        const dropdowns = document.querySelectorAll('.dropdown-menu');
        dropdowns.forEach(dropdown => {
            if (!dropdown.previousElementSibling.contains(event.target) && dropdown.classList.contains('active')) {
                dropdown.classList.remove('active'); // Close the dropdown if clicked outside
            }
        });
    });

    </script>
</body>
</html>