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
    <title>Help Center</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href='https://fonts.googleapis.com/css?family=Inter' rel='stylesheet'>
    <link rel="stylesheet" href="css/help_center.css">
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
            <a href="#" class="topnav-link">Home</a>
            <a href="#" class="topnav-link">Notes</a>
            <a href="#" class="topnav-link">Contributors</a>
            <a href="user_dashboard.php" class="topnav-link">Dashboard</a>
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

        <!-- help center content -->
        <main class="main">
            <div class="main__container">

                <header class="help-hero">
                    <h1 class="help-hero__title">How can we help you?</h1>

                    <div class="help-search">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <circle cx="11" cy="11" r="7"/>
                            <line x1="21" y1="21" x2="16.65" y2="16.65"/>
                        </svg>
                        <input type="text" id="faq-search" placeholder="Search for guides, policies or topics...">
                    </div>
                </header>

                <section class="faq-section">
                    <p class="faq-section__label">Frequently Asked Questions</p>

                    <div class="faq-list" id="faq-list">

                        <div class="faq-item" data-faq>
                            <button class="faq-item__question" onclick="toggleFaq(this)">
                                <span>How do I verify my institutional email?</span>
                                <svg class="faq-item__chevron" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <polyline points="6 9 12 15 18 9"/>
                                </svg>
                            </button>
                            <div class="faq-item__answer">
                                <div class="faq-item__answer-inner">
                                    Go to Account Settings and click "Verify Email." We'll send a verification link to your UiTM student email address.
                                </div>
                            </div>
                        </div>

                        <div class="faq-item" data-faq>
                            <button class="faq-item__question" onclick="toggleFaq(this)">
                                <span>What file formats are supported for uploads?</span>
                                <svg class="faq-item__chevron" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <polyline points="6 9 12 15 18 9"/>
                                </svg>
                            </button>
                            <div class="faq-item__answer">
                                <div class="faq-item__answer-inner">
                                    You can upload notes in PDF, DOCX, PPTX, and image formats (PNG, JPG). Each file must be under 25MB. For best results, we recommend converting handwritten notes to PDF before uploading.
                                </div>
                            </div>
                        </div>

                        <div class="faq-item" data-faq>
                            <button class="faq-item__question" onclick="toggleFaq(this)">
                                <span>How are my earnings calculated?</span>
                                <svg class="faq-item__chevron" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <polyline points="6 9 12 15 18 9"/>
                                </svg>
                            </button>
                            <div class="faq-item__answer">
                                <div class="faq-item__answer-inner">
                                    Points are earned based on uploads, downloads of your notes by other users, and quality ratings. Higher-rated and frequently downloaded notes earn more points, which can be redeemed for rewards through your dashboard.
                                </div>
                            </div>
                        </div>

                        <div class="faq-item" data-faq>
                            <button class="faq-item__question" onclick="toggleFaq(this)">
                                <span>Can I withdraw my content after it has been cited?</span>
                                <svg class="faq-item__chevron" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <polyline points="6 9 12 15 18 9"/>
                                </svg>
                            </button>
                            <div class="faq-item__answer">
                                <div class="faq-item__answer-inner">
                                    Yes, you can remove your notes anytime from "My Notes" by deleting them. However, if other users have already downloaded or cited your content, those existing copies will remain in their possession.
                                </div>
                            </div>
                        </div>

                    </div>

                    <p class="faq-empty" id="faq-empty">No results found for your search.</p>
                </section>

            </div>
        </main>
    </div>

    <script>
    function toggleFaq(buttonEl) {
        const item = buttonEl.closest('.faq-item');
        const answer = item.querySelector('.faq-item__answer');
        const isOpen = item.classList.contains('open');

        document.querySelectorAll('.faq-item.open').forEach(openItem => {
            if (openItem !== item) {
                openItem.classList.remove('open');
                openItem.querySelector('.faq-item__answer').style.maxHeight = null;
            }
        });

        if (isOpen) {
            item.classList.remove('open');
            answer.style.maxHeight = null;
        } else {
            item.classList.add('open');
            answer.style.maxHeight = answer.scrollHeight + 'px';
        }
    }

    // search filter
    const searchInput = document.getElementById('faq-search');
    const faqItems = document.querySelectorAll('.faq-item[data-faq]');
    const faqEmpty = document.getElementById('faq-empty');

    searchInput.addEventListener('input', function () {
        const query = this.value.trim().toLowerCase();
        let visibleCount = 0;

        faqItems.forEach(item => {
            const question = item.querySelector('.faq-item__question span').textContent.toLowerCase();
            const answer = item.querySelector('.faq-item__answer-inner').textContent.toLowerCase();
            const matches = question.includes(query) || answer.includes(query);

            if (matches) {
                item.style.display = '';
                visibleCount++;
            } else {
                item.style.display = 'none';
                item.classList.remove('open');
                item.querySelector('.faq-item__answer').style.maxHeight = null;
            }
        });

        faqEmpty.classList.toggle('show', visibleCount === 0);
    });

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