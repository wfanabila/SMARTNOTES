<?php
session_start();

$isAdminHelp = (($_SESSION['role'] ?? '') === 'admin');
if ($isAdminHelp) {
    require_once __DIR__ . '/admin_bootstrap.php';
    // sidebar.php expects these keys; map the signed-in admin to the same view data.
    $user = [
        'studentName' => $adminName,
        'studentEmail' => $adminEmail,
        'profilePicture' => $admin['profilePicture'] ?? '',
    ];
    $current_page = 'help_center';
    $activePage = 'help';
} else {

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

$stmt = $pdo->prepare("SELECT studentName, studentEmail FROM student WHERE studentID = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    die("User data could not be retrieved.");
}

$activePage = 'help';
$current_page = 'help_center';
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
    </script>

</body>
</html>
