<?php
include "myconnector.php";
session_start();

if (!isset($_GET['file_url'])) {
    die("No file selected.");
}

$file_url = urldecode($_GET['file_url']);
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
        <div class="nav-center">Classix</div>
        <!-- User Info -->
        <div class="user-info">
            <img src="Images/user-svgrepo-com.svg" alt="User Icon">
        </div>
    </nav>

    <!-- Sidebar -->
    <div class="sidebar" id="sidebar">
        <ul>
            <li><a href="#"><img src="Images/home-svgrepo-com.svg" alt="Home Icon"> Home</a></li>
            <li><a href="#"><img src="Images/idea-svgrepo-com.svg" alt="Features Icon">Features</a></li>
            <li><a href="#"><img src="Images/about-filled-svgrepo-com.svg" alt="About-Us Icon">About Us</a></li>
            <li>
                <a href="#" class="dropdown-toggle">Here</a>
                <ul class="dropdown-menu">
                    <li><a href="#"><img src="Images/calendar-month-svgrepo-com.svg" alt="Calendar Icon"> Calendar</a></li>
                    <li><a href="#"><img src="Images/book-svgrepo-com.svg" alt="Modules Icon"> Modules</a></li>
                    <li><a href="#"><img src="Images/user-svgrepo-com.svg" alt="Tutors Icon"> Tutor</a></li>
                    <li><a href="#"><img src="Images/progress-svgrepo-com.svg" alt="Progress Icon">Progress</a></li>
                    <li><a href="#"><img src="Images/settings-2-svgrepo-com.svg" alt="Settings Icon"> Settings</a></li>
                </ul>
            <li>
        </ul>
    </div>
    <div id="pdf-viewer-container">
        <h1>PDF Viewer</h1>
        <button id="playPauseBtn" disabled>▶️ Play</button>
        <button id="stopBtn" disabled>⏹️ Stop</button>
        <label for="speedControl">Speed:</label>
        <input type="range" id="speedControl" min="0.5" max="2" step="0.1" value="1">
        <span id="speedValue">1x</span>
        <button id="highlightNarrateBtn" disabled>🎤 Narrate Highlighted</button>
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

            let pdfText = '';
            let currentWord = '';
            let utterance = null;
            let isPaused = false;

            pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.8.162/pdf.worker.min.js';

            async function extractTextFromPdf(fileUrl) {
                const pdfDoc = await pdfjsLib.getDocument(fileUrl).promise;
                let fullText = '';
                for (let i = 1; i <= pdfDoc.numPages; i++) {
                    const page = await pdfDoc.getPage(i);
                    const textContent = await page.getTextContent();
                    let pageText = '';
                    textContent.items.forEach(item => {
                        pageText += item.str + ' ';
                    });
                    fullText += pageText.trim() + '\n\n';
                }
                return fullText.trim();
            }

            function renderTextToSpans(text) {
                const parts = text.split(/(\s+|[.,!?;:"'“”‘’\-\(\)\[\]{}])/g).filter(Boolean);
                const fragment = document.createDocumentFragment();
                parts.forEach(part => {
                    if (part.trim() === '') {
                        fragment.appendChild(document.createTextNode(part));
                        return;
                    }
                    if (/^\w+$/u.test(part.trim())) {
                        const span = document.createElement('span');
                        span.className = 'word';
                        span.textContent = part;
                        span.tabIndex = 0;
                        fragment.appendChild(span);
                    } else {
                        fragment.appendChild(document.createTextNode(part));
                    }
                });
                return fragment;
            }

            async function loadPdf(fileUrl) {
                contentDiv.innerHTML = 'Loading document... Please wait.';
                playPauseBtn.disabled = true;
                stopBtn.disabled = true;
                highlightNarrateBtn.disabled = true;
                try {
                    const text = await extractTextFromPdf(fileUrl);
                    pdfText = text;
                    contentDiv.innerHTML = '';
                    const fragment = renderTextToSpans(text);
                    contentDiv.appendChild(fragment);
                    playPauseBtn.disabled = false;
                    stopBtn.disabled = false;
                    highlightNarrateBtn.disabled = false;
                } catch (e) {
                    contentDiv.innerHTML = 'Failed to load PDF content.';
                    console.error(e);
                }
            }

            loadPdf('<?php echo htmlspecialchars($file_url); ?>');

            playPauseBtn.addEventListener('click', () => {
                if (!utterance) {
                    utterance = new SpeechSynthesisUtterance(pdfText);
                    utterance.lang = 'en-US';
                    utterance.rate = parseFloat(speedControl.value);
                    utterance.onend = () => {
                        playPauseBtn.textContent = '▶️ Play';
                        utterance = null;
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

            stopBtn.addEventListener('click', () => {
                if (utterance) {
                    window.speechSynthesis.cancel();
                    utterance = null;
                    playPauseBtn.textContent = '▶️ Play';
                }
            });

            speedControl.addEventListener('input', () => {
                speedValue.textContent = `${speedControl.value}x`;
                if (utterance) {
                    utterance.rate = parseFloat(speedControl.value);
                }
            });

            highlightNarrateBtn.addEventListener('click', () => {
                const selectedText = window.getSelection().toString().trim();
                if (selectedText) {
                    if (utterance) {
                        window.speechSynthesis.cancel();
                    }
                    utterance = new SpeechSynthesisUtterance(selectedText);
                    utterance.lang = 'en-US';
                    utterance.rate = parseFloat(speedControl.value);
                    window.speechSynthesis.speak(utterance);
                } else {
                    alert('Please highlight text to narrate.');
                }
            });
            
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

            document.addEventListener('click', (e) => {
                if (!popup.contains(e.target) && !e.target.classList.contains('word')) {
                    popup.style.display = 'none';
                }
            });

            playBtn.addEventListener('click', () => {
                if (!currentWord) return;
                const utterance = new SpeechSynthesisUtterance(currentWord);
                utterance.lang = 'en-US';
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