<?php
session_start();

$host = "localhost";
$username = "root";
$password = "";
$dbname = "smartnotes";

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

$stmt = $pdo->prepare("SELECT studentName, studentEmail, profilePicture FROM student WHERE studentID = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    die("User data could not be retrieved.");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>User Dashboard</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href='https://fonts.googleapis.com/css?family=Inter' rel='stylesheet'>
    <link rel="stylesheet" href="css/dashboard.css">
    <link rel="stylesheet" href="https://www.w3schools.com/w3css/5/w3.css">
</head>
<body>
    

    <nav class="topnav">
        <div class="topnav__left">
            <a class="topnav__logo" href="#">
                <span class="topnav__logo-icon">
                    <img src="img/logo.PNG" alt="UiTMNoteLink Logo">
                </span>
            </a>
        </div>

        <!-- top bar -->
        <div class="topnav__links">
            <a href="#" class="w3-bar-item w3-button w3-hover-none w3-border-white w3-bottombar topnav-link">Home</a>
            <a href="#" class="w3-bar-item w3-button w3-hover-none w3-border-white w3-bottombar topnav-link">Notes</a>
            <a href="#" class="w3-bar-item w3-button w3-hover-none w3-border-white w3-bottombar topnav-link">Contributors</a>
            <a href="user_dashboard.php" class="w3-bar-item w3-button w3-hover-none w3-border-white w3-bottombar topnav-link">Dashboard</a> 
        </div>

        <!-- pfp avatar -->
        <div class="topnav__right">
            <div class="topnav__avatar">
                <?php if (!empty($user['profilePicture'])): ?>
                    <img src="<?php echo htmlspecialchars($user['profilePicture']); ?>" alt="Profile picture" style="width:100%;height:100%;object-fit:cover;border-radius:50%;">
                <?php else: ?>
                    <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" fill="none"
                         viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="8" r="4"/>
                        <path d="M4 20c0-4 3.6-7 8-7s8 3 8 7"/>
                    </svg>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <?php include 'sidebar.php'; ?>
    
        <!-- user dashboard -->
        <main class="main">
            <div class="main__container">

                <header class="profile" id="section-overview">
                    <h1 class="profile__name"><?php echo htmlspecialchars($user['studentName']); ?>'s Dashboard</h1>
                    <p class="profile__bio">
                        Passionate about full-stack development, distributed systems, and building collaborative tools for students. Currently focused on optimizing peer-to-peer resource sharing.
                    </p>
                </header>

                <section class="stats">
                    <div class="stat-card stat-card--points">
                        <span class="stat-card__label">Points Earned:</span>
                        <div class="stat-card__amount">
                            RM50.00 
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                <line x1="5" y1="12" x2="19" y2="12"></line>
                                <polyline points="12 5 19 12 12 19"></polyline>
                            </svg>
                        </div>
                        <p class="stat-card__note">You've earned 10 points this week. Keep sharing high-quality resources to climb the ranks.</p>
                    </div>

                    <div class="stat-card stat-card--uploads">
                        <div class="stat-up-icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                <line x1="12" y1="19" x2="12" y2="5"></line>
                                <polyline points="5 12 12 5 19 12"></polyline>
                            </svg>
                        </div>
                        <span class="stat-count">30</span>
                        <h3 class="stat-title">Uploads</h3>
                        <p class="stat-sub">+3 this month</p>
                    </div>
                </section>

                <div class="tabs" id="section-notes">
                    <button class="tab active" id="tab-mynotes" onclick="switchTab('mynotes')">My Notes</button>
                    <button class="tab" id="tab-bookmarks" onclick="switchTab('bookmarks')">Bookmarks</button>
                </div>

                <div class="tab-panel active" id="panel-mynotes">
                    <section class="notes-grid">
                        <article class="note-card">
                            <div class="note-card__thumb">
                                <img src="img/mynotes1.png" alt="CSC660 Cover" style="width: 100%; height: 100%; object-fit: cover;">
                                
                                <button class="note-card__menu">⋮</button>
                                
                                <div class="note-card__dropdown">
                                    <button class="dropdown-item btn-edit">Edit</button>
                                    <button class="dropdown-item btn-delete">Delete</button>
                                </div>
                            </div>
                            <div class="note-card__body">
                                <div class="note-card__tags">
                                    <span class="badge">CSC660</span>
                                    <span class="badge">Semester 4</span>
                                </div>
                                <h4 class="note-card__title">CSC660 Chapter 1 Slide</h4>
                                <p class="note-card__sub">Preliminary Considerations</p>
                                <div class="note-card__date">
                                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>
                                    Oct 5, 2025
                                </div>
                            </div>
                        </article>

                        <article class="note-card">
                            <div class="note-card__thumb">
                                <img src="img/mynotes2.png" alt="CSC577 Cover" style="width: 100%; height: 100%; object-fit: cover;">
                                
                                <button class="note-card__menu">⋮</button>
                                
                                <div class="note-card__dropdown">
                                    <button class="dropdown-item btn-edit">Edit</button>
                                    <button class="dropdown-item btn-delete">Delete</button>
                                </div>
                            </div>
                            <div class="note-card__body">
                                <div class="note-card__tags">
                                    <span class="badge">CSC577</span>
                                    <span class="badge">Semester 4</span>
                                </div>
                                <h4 class="note-card__title">CSC577 Chapter 2 Slide</h4>
                                <p class="note-card__sub">Software Processes</p>
                                <div class="note-card__date">
                                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>
                                    Sept 15, 2025
                                </div>
                            </div>
                        </article>

                        <a class="note-card note-card--upload" href="upload_notes.php" style="text-decoration:none; cursor:pointer;">
                            <div class="note-card--upload__icon">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                                    <polyline points="17 8 12 3 7 8"></polyline>
                                    <line x1="12" y1="3" x2="12" y2="15"></line>
                                </svg>
                            </div>
                            <span class="note-card--upload__title">New Upload</span>
                            <p class="note-card--upload__sub">Upload and share your notes with your friends.</p>
                        </a>
                    </section>
                </div>

                <div class="tab-panel" id="panel-bookmarks">
                    <section class="notes-grid">
                        <article class="note-card">
                            <div class="note-card__thumb">
                                <img src="img/bookmark1.png" alt="CSC520 Cover" style="width: 100%; height: 100%; object-fit: cover;">

                                <button class="note-card__menu">⋮</button>

                                <div class="note-card__dropdown">
                                    <button class="dropdown-item btn-unbookmark">Remove</button>
                                    <!-- <button class="dropdown-item">Download</button> -->
                                </div>
                            </div>
                            <div class="note-card__body">
                                <div class="note-card__tags">
                                    <span class="badge">CSC520</span>
                                    <span class="badge">Semester 3</span>
                                </div>
                                <h4 class="note-card__title">CSC520 Chapter 8 Slide</h4>
                                <p class="note-card__sub">Main Memory</p>
                            </div>
                        </article>

                        <article class="note-card">
                            <div class="note-card__thumb">
                                <img src="img/bookmark2.png" alt="ICT602 Cover" style="width: 100%; height: 100%; object-fit: cover;">

                                <button class="note-card__menu">⋮</button>

                                <div class="note-card__dropdown">
                                    <button class="dropdown-item btn-unbookmark">Remove</button>
                                    <!-- <button class="dropdown-item">Download</button> -->
                                </div>
                            </div>
                            <div class="note-card__body">
                                <div class="note-card__tags">
                                    <span class="badge">ICT602</span>
                                    <span class="badge">Semester 3</span>
                                </div>
                                <h4 class="note-card__title">ICT602 Chapter 2 Slide</h4>
                                <p class="note-card__sub">Mobile Devices</p>
                            </div>
                        </article>

                        <article class="note-card">
                            <div class="note-card__thumb">
                                <img src="img/bookmark3.png" alt="ICT551 Cover" style="width: 100%; height: 100%; object-fit: cover;">

                                <button class="note-card__menu">⋮</button>

                                <div class="note-card__dropdown">
                                    <button class="dropdown-item btn-unbookmark">Remove</button>
                                    <!-- <button class="dropdown-item">Download</button> -->
                                </div>
                            </div>
                            <div class="note-card__body">
                                <div class="note-card__tags">
                                    <span class="badge">ICT551</span>
                                    <span class="badge">Semester 4</span>
                                </div>
                                <h4 class="note-card__title">ICT551 Chapter 1 Slide</h4>
                                <p class="note-card__sub">Overview to The Field of HCI</p>
                            </div>
                        </article>
                    </section>
                </div>

            </div>
        </main>
    </div>

    <script>
    

    const sidebarItems = document.querySelectorAll('.sidebar__item');
    sidebarItems.forEach(item => {
        item.addEventListener('click', function (e) {
            sidebarItems.forEach(i => i.classList.remove('active'));
            this.classList.add('active');
        });
    });

    // menu dropdown for delete or edit note
    const menuButtons = document.querySelectorAll('.note-card__menu');

    menuButtons.forEach(button => {
        button.addEventListener('click', function (event) {
            event.stopPropagation();
            
            const parentThumb = this.parentElement;
            const currentDropdown = parentThumb.querySelector('.note-card__dropdown');
            
            document.querySelectorAll('.note-card__dropdown').forEach(dropdown => {
                if (dropdown !== currentDropdown) {
                    dropdown.classList.remove('show');
                }
            });
            
            currentDropdown.classList.toggle('show');
        });
    });

    window.addEventListener('click', function () {
        document.querySelectorAll('.note-card__dropdown').forEach(dropdown => {
            dropdown.classList.remove('show');
        });
    });

    function switchTab(tabName) {

        document.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));
        document.getElementById('tab-' + tabName).classList.add('active');

        document.querySelectorAll('.tab-panel').forEach(p => p.classList.remove('active'));
        document.getElementById('panel-' + tabName).classList.add('active');

        setSidebarActive('nav-' + tabName);
    }

    function setSidebarActive(id) {
        ['nav-overview', 'nav-mynotes', 'nav-bookmarks'].forEach(navId => {
            document.getElementById(navId).classList.remove('active');
        });
        document.getElementById(id).classList.add('active');
    }

    function navigateTo(section) {
        if (section === 'overview') {
            setSidebarActive('nav-overview');

            document.getElementById('section-overview').scrollIntoView({ behavior: 'smooth', block: 'start' });

            const el = document.getElementById('section-overview');
            el.classList.remove('section-highlight');
            void el.offsetWidth; 
            el.classList.add('section-highlight');
            setTimeout(() => el.classList.remove('section-highlight'), 1000);

        } else if (section === 'mynotes') {
            switchTab('mynotes');
            document.getElementById('section-notes').scrollIntoView({ behavior: 'smooth', block: 'start' });

        } else if (section === 'bookmarks') {
            switchTab('bookmarks');
            document.getElementById('section-notes').scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
    }


    const settingsBtn = document.getElementById('nav-settings');
    const accountPopup = document.getElementById('account-popup');

    function toggleSettingsPopup(event) {
        event.preventDefault();
        event.stopPropagation();
        accountPopup.classList.toggle('show');
    }

    window.addEventListener('click', function (event) {
        if (accountPopup.classList.contains('show') &&
            !accountPopup.contains(event.target) &&
            event.target !== settingsBtn) {
            accountPopup.classList.remove('show');
        }
    });

    
    </script>

</body>
</html>