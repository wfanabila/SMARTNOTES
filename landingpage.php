<?php
session_start();
$isAdminLanding = (($_SESSION['role'] ?? '') === 'admin');
$landingNotesLink = $isAdminLanding ? 'admin_notes.php' : 'all_notes.php';
$landingContributorsLink = $isAdminLanding ? 'admin_contributors.php' : 'contributors.php';
$landingDashboardLink = $isAdminLanding ? 'admin_dashboard.php' : 'user_dashboard.php';
$recentAdminNotes = [];
$landingSubjects = [];
if ($isAdminLanding) {
    require_once __DIR__ . '/db_config.php';
    $recentResult = $conn->query("SELECT n.noteID, n.title, s.subjectCode FROM notes n LEFT JOIN subject s ON s.subjectID = n.subjectID ORDER BY n.uploadDate DESC, n.noteID DESC LIMIT 3");
    if ($recentResult) {
        while ($note = $recentResult->fetch_assoc()) { $recentAdminNotes[] = $note; }
        $recentResult->close();
    }
    $subjectResult = $conn->query('SELECT subjectCode, subjectName FROM subject ORDER BY subjectCode');
    if ($subjectResult) {
        while ($subject = $subjectResult->fetch_assoc()) { $landingSubjects[] = $subject; }
        $subjectResult->close();
    }
    $conn->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>UiTMNoteLink</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/landingpage.css">
</head>
<body>
    <header class="site-header">
        <div class="header-brand">
            <img src="img/logo.PNG" alt="UiTMNoteLink Logo" class="brand-logo">
        </div>

        <nav class="nav-links">
            <a href="landingpage.php" class="nav-link active">Home</a>
            <a href="<?= $landingNotesLink ?>" class="nav-link">Notes</a>
            <a href="<?= $landingContributorsLink ?>" class="nav-link">Contributors</a>
            <a href="<?= $landingDashboardLink ?>" class="nav-link">Dashboard</a>
        </nav>
    </header>

    <main class="page-shell">
        <section class="hero-section">
            <div class="hero-copy">
                <h1 class="hero-title">
                    Exchange knowledge.
                    <span>Elevate your <span class="hero-highlight">academic journey.</span></span>
                </h1>

                <div class="search-block">
                    <label class="sr-only" for="hero-search">Search notes and courses</label>
                    <input id="hero-search" type="search" placeholder="Search for courses, notes" autocomplete="off">
                </div>
                
                <div class="landing-search-results" id="landing-search-results" hidden>
                    <div id="search-results-container"></div>
                    <p id="landing-search-empty" hidden>No matching notes or subjects found.</p>
                </div>
            </div>
        </section>

        <section class="explore-section">
            <div class="section-heading">
                <div>
                    <h2>Explore Programme</h2>
                    <p>Browse specialized archives curated for your major</p>
                </div>
            </div>

            <div class="tag-grid">
                <a href="csc270notes.php" class="tag-item">CSC270</a>
                <a href="csc264notes.php" class="tag-item">CSC264</a>
                <a href="csc267notes.php" class="tag-item">CSC267</a>
                <a href="csc230notes.php" class="tag-item">CSC230</a>
                <a href="csc110notes.php" class="tag-item">CSC110</a>
            </div>
        </section>

        <section class="recent-section">
            <h2>Recently Added</h2>
            <div class="recent-grid">
                <?php if ($isAdminLanding): ?>
                    <?php if (empty($recentAdminNotes)): ?>
                        <p>No notes have been uploaded yet.</p>
                    <?php endif; ?>
                    <?php foreach ($recentAdminNotes as $index => $note): ?>
                        <article class="note-card">
                            <a href="manage_notes.php">
                                <img src="img/bookmark<?= ($index % 3) + 1 ?>.png" alt="<?= htmlspecialchars($note['title'], ENT_QUOTES, 'UTF-8') ?>" class="note-thumb">
                                <div class="note-label"><?= htmlspecialchars($note['title'], ENT_QUOTES, 'UTF-8') ?></div>
                            </a>
                        </article>
                    <?php endforeach; ?>
                <?php else: ?>
                <article class="note-card">
                    <a href="display_note.php?note=101">
                        <img src="img/bookmark1.png" alt="CSC577 Chapter 1" class="note-thumb">
                        <div class="note-label">CSC577 Chapter 1</div>
                    </a>
                </article>

                <article class="note-card">
                    <a href="display_note.php?note=102">
                        <img src="img/bookmark2.png" alt="CSC577 Chapter 1" class="note-thumb">
                        <div class="note-label">CSC577 Chapter 1</div>
                    </a>
                </article>

                <article class="note-card">
                    <a href="display_note.php?note=103">
                        <img src="img/bookmark3.png" alt="CSC402 Chapter 3" class="note-thumb">
                        <div class="note-label">CSC402 Chapter 3</div>
                    </a>
                </article>
                <?php endif; ?>
            </div>
        </section>
    </main>
</body>
<script>
const landingSearch = document.getElementById('hero-search');
const results = document.getElementById('landing-search-results');
const emptyResult = document.getElementById('landing-search-empty');
const resultsContainer = document.getElementById('search-results-container');
const isAdmin = <?= $isAdminLanding ? 'true' : 'false' ?>;

// Debounce function to avoid too many requests
function debounce(func, delay) {
    let timeoutId;
    return function (...args) {
        clearTimeout(timeoutId);
        timeoutId = setTimeout(() => func.apply(this, args), delay);
    };
}

// Admin search - search in static data
function adminSearch(query) {
    const searchItems = document.querySelectorAll('[data-subject-result]');
    let matches = 0;
    
    searchItems.forEach(item => {
        const visible = item.dataset.search.includes(query);
        item.hidden = !visible;
        if (visible) matches++;
    });
    
    results.hidden = false;
    emptyResult.hidden = matches !== 0;
}

// Student/General search - search via API
function studentSearch(query) {
    if (!query || query.length < 2) {
        results.hidden = true;
        return;
    }
    
    fetch('search_api.php?q=' + encodeURIComponent(query))
        .then(response => response.json())
        .then(data => {
            if (data.success && data.results.length > 0) {
                // Clear previous results
                resultsContainer.innerHTML = '';
                
                // Build result items
                data.results.forEach(item => {
                    const resultDiv = document.createElement('a');
                    resultDiv.href = 'view_note.php?id=' + item.noteID;
                    resultDiv.className = 'search-result-item';
                    
                    const badge = item.noteType && item.noteType.toLowerCase() === 'paid' 
                        ? `<span class="result-badge premium">PREMIUM</span>`
                        : `<span class="result-badge free">FREE</span>`;
                    
                    resultDiv.innerHTML = `
                        <div class="result-info">
                            <strong>${escapeHtml(item.title)}</strong>
                            <small>${escapeHtml(item.subjectCode)} — ${escapeHtml(item.subjectName)}</small>
                        </div>
                        ${badge}
                    `;
                    
                    resultsContainer.appendChild(resultDiv);
                });
                
                results.hidden = false;
                emptyResult.hidden = true;
            } else {
                resultsContainer.innerHTML = '';
                results.hidden = false;
                emptyResult.hidden = false;
            }
        })
        .catch(error => {
            console.error('Search error:', error);
            resultsContainer.innerHTML = '';
            results.hidden = false;
            emptyResult.hidden = false;
        });
}

// Helper function to escape HTML
function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// Main search handler with debounce
const handleSearch = debounce(function() {
    const query = landingSearch.value.trim().toLowerCase();
    
    if (!query) {
        results.hidden = true;
        return;
    }
    
    if (isAdmin) {
        adminSearch(query);
    } else {
        studentSearch(query);
    }
}, 300);

landingSearch.addEventListener('input', handleSearch);

// Close search results when clicking outside
document.addEventListener('click', function(e) {
    if (!e.target.closest('.search-block') && !e.target.closest('.landing-search-results')) {
        results.hidden = true;
    }
});
</script>
</html>
