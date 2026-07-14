<?php

if (!isset($user)) {
    $user = ['studentName' => '', 'studentEmail' => '', 'profilePicture' => ''];
}
if (!isset($current_page)) {
    $current_page = '';
}
?>

<style>
    *, *::before, *::after {
    box-sizing: border-box;
    margin: 0;
    padding: 0;
}

body {
    font-family: 'Inter', sans-serif;
    background-color: #ffffff;
    color: #1a1a1a;
    min-height: 100vh;
    display: flex;
    flex-direction: column;
}

a {
    text-decoration: none;
    color: inherit;
}

/* header */
.topnav {
    position: fixed !important;
    top: 0 !important;
    left: 0 !important;
    right: 0 !important;
    z-index: 100 !important;
    height: 56px !important;
    background: #ffffff !important;
    border-bottom: 0.5px solid #e0ddd6 !important;
    display: flex !important;
    align-items: center !important;
    justify-content: space-between !important;
    padding: 0 24px !important;
}

.topnav__left {
    display: flex;
    align-items: center;
}

.topnav__logo {
    display: flex;
    align-items: center;
    gap: 10px;
}

.topnav__logo-icon img {
    height: 32px;
    width: auto;
    display: block;
}

.topnav__links {
    display: flex;
    align-items: center;
    gap: 4px;
    position: absolute;
    left: 50%;
    transform: translateX(-50%);
}

.topnav__links a {
    font-family: 'Inter', sans-serif;
    font-size: 16px;
    font-weight: 500;
    color: #6b6860;
    padding: 6px 14px;
    transition: background 0.15s, color 0.15s;
}

.topnav-link:hover {
    border-color: #6D3BD7 !important;
    color: #6D3BD7 !important;
}

.topnav__right {
    display: flex;
    align-items: center;
    position: relative;
}

.topnav__avatar {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    background: #ebe8e2;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #6b6860;
    cursor: pointer;
    transition: background 0.15s;
    overflow: hidden;
}

.topnav__avatar:hover {
    background: #dedad2;
}

/* body */
.layout {
    display: flex;
    margin-top: 40px;
    min-height: calc(100vh - 56px);
}

/* side bar */
.sidebar {
    position: fixed;
    top: 56px;
    left: 0;
    bottom: 0;
    z-index: 90;

    width: 64px;
    overflow: hidden;
    white-space: nowrap;
    transition: width 0.25s cubic-bezier(0.4, 0, 0.2, 1);

    background: #F6EDFF;
    border-right: 0.5px solid #e0ddd6;

    display: flex;
    flex-direction: column;
    padding: 12px 0;
}

.sidebar:hover {
    width: 220px;
}

.sidebar__nav {
    display: flex;
    flex-direction: column;
    gap: 2px;
    padding: 0 8px;
    flex: 1;
}

.sidebar__item {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 10px 12px;
    border-radius: 8px;
    font-family: 'Inter', sans-serif;
    font-size: 14px;
    font-weight: 500;
    color: #000000;
    cursor: pointer;
    transition: background 0.15s, color 0.15s;
    flex-shrink: 0;
}

.sidebar__item svg {
    width: 20px;
    height: 20px;
    flex-shrink: 0;
}

.sidebar__item span {
    opacity: 0;
    transition: opacity 0.15s ease 0.05s;
}

.sidebar:hover .sidebar__item span {
    opacity: 1;
}

.sidebar__item:hover {
    background: #d6c3ff;
    color: #1a1a1a;
}

.sidebar__item.active {
    background: #f3effe;
    color: #6D3BD7;
    border: 1.5px solid #6D3BD7;
    opacity: 1;
}

.sidebar__item.active:hover {
    background: #d6c3ff;
    color: #6D3BD7;
}

.sidebar__upload {
    display: flex;
    align-items: center;
    gap: 10px;
    margin: 12px 8px;
    padding: 10px 12px;
    border-radius: 8px;
    background: #6D3BD7;
    color: #ffffff;
    font-family: 'Inter', sans-serif;
    font-size: 13px;
    font-weight: 550;
    cursor: pointer;
    flex-shrink: 0;
    transition: background 0.15s;
}

.sidebar__upload svg {
    width: 18px;
    height: 18px;
    flex-shrink: 0;
}

.sidebar__upload span {
    opacity: 0;
    transition: opacity 0.15s ease 0.05s;
    white-space: nowrap;
}

.sidebar:hover .sidebar__upload span {
    opacity: 1;
}

.sidebar__footer {
    border-top: 0.5px solid #e0ddd6;
    padding: 8px;
    display: flex;
    flex-direction: column;
    gap: 2px;
    position: relative;
}

.main {
    margin-left: 64px;
    flex: 1;
    transition: margin-left 0.25s cubic-bezier(0.4, 0, 0.2, 1);
    min-height: calc(100vh - 56px);
}

.sidebar:hover ~ .main {
    margin-left: 220px;
}

.main__container {
    max-width: 900px;
    margin: 0 auto;
    padding: 48px 40px;
}

.account-popup {
    position: fixed;
    left: 76px;
    bottom: 70px;
    width: 240px;
    z-index: 200;
    background: #ffffff;
    border: 0.5px solid #e0ddd6;
    border-radius: 14px;
    box-shadow: 0 8px 24px rgba(0,0,0,0.14);
    padding: 12px;
    display: none;
    flex-direction: column;
    gap: 6px;
}

.account-popup.show {
    display: flex;
}

.account-popup__label {
    font-size: 11px;
    font-weight: 700;
    color: #9c988e;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    margin-bottom: 2px;
}

.account-popup__user {
    display: flex;
    align-items: center;
    gap: 8px;
}

.account-popup__avatar {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    background: #ebe8e2;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #6b6860;
    overflow: hidden;
    flex-shrink: 0;
}

.account-popup__info {
    min-width: 0;
}

.account-popup__name {
    font-family: 'Inter', sans-serif;
    font-size: 13px;
    font-weight: 700;
    color: #1a1a1a;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.account-popup__email {
    font-family: 'Inter', sans-serif;
    font-size: 12px;
    color: #6b6860;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.account-popup__divider {
    height: 1px;
    background: #e0ddd6;
    margin-bottom: 8px;
}

.account-popup__item {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 9px 10px;
    border-radius: 8px;
    font-family: 'Inter', sans-serif;
    font-size: 13px;
    font-weight: 500;
    color: #1a1a1a;
    cursor: pointer;
    transition: background 0.15s;
}

.account-popup__item:hover,
.account-popup__item--active {
    background: #f3effe;
    color: #6D3BD7;
}

.account-popup__item--logout:hover {
    background: #fdeaea;
    color: #c0392b;
}
</style>

<nav class="topnav">
    <div class="topnav__left">
        <a class="topnav__logo" href="user_dashboard.php">
            <span class="topnav__logo-icon">
                <img src="img/logo.PNG" alt="UiTMNoteLink Logo">
            </span>
        </a>
    </div>

        <!-- top bar -->
        <div class="topnav__links">
            <a href="#" class="w3-bar-item w3-button w3-hover-none w3-border-white w3-bottombar topnav-link">Home</a>
            <a href="all_notes.php" class="w3-bar-item w3-button w3-hover-none w3-border-white w3-bottombar topnav-link">Notes</a>
            <a href="contributors.php" class="w3-bar-item w3-button w3-hover-none w3-border-white w3-bottombar topnav-link">Contributors</a>
            <a href="user_dashboard.php" class="w3-bar-item w3-button w3-hover-none w3-border-white w3-bottombar topnav-link">Dashboard</a> 
        </div>

    <!-- pfp avatar -->
    <div class="topnav__right">
        <a class="topnav__avatar" href="account_setting.php" title="Account Setting">
            <?php if (!empty($user['profilePicture'])): ?>
                <img src="<?php echo htmlspecialchars($user['profilePicture']); ?>" alt="Profile picture" style="width:100%;height:100%;object-fit:cover;border-radius:50%;">
            <?php else: ?>
                <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" fill="none"
                     viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="8" r="4"/>
                    <path d="M4 20c0-4 3.6-7 8-7s8 3 8 7"/>
                </svg>
            <?php endif; ?>
        </a>
    </div>
</nav>

<!-- side bar -->
<div class="layout">

    <aside class="sidebar">
        <nav class="sidebar__nav">

            <a class="sidebar__item <?php echo ($current_page === 'overview') ? 'active' : ''; ?>"
               href="user_dashboard.php"
               id="nav-overview"
               <?php if ($current_page === 'dashboard'): ?>onclick="navigateTo('overview'); return false;"<?php endif; ?>>
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904 9 18.75l-.813-2.846a4.5 4.5 0 0 0-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 0 0 3.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 0 0 3.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 0 0-3.09 3.09ZM18.259 8.715 18 9.75l-.259-1.035a3.375 3.375 0 0 0-2.455-2.456L14.25 6l1.036-.259a3.375 3.375 0 0 0 2.455-2.456L18 2.25l.259 1.035a3.375 3.375 0 0 0 2.456 2.456L21.75 6l-1.035.259a3.375 3.375 0 0 0-2.456 2.456ZM16.894 20.567 16.5 21.75l-.394-1.183a2.25 2.25 0 0 0-1.423-1.423L13.5 18.75l1.183-.394a2.25 2.25 0 0 0 1.423-1.423l.394-1.183.394 1.183a2.25 2.25 0 0 0 1.423 1.423l1.183.394-1.183.394a2.25 2.25 0 0 0-1.423 1.423Z" />
                </svg>
                <span>Overview</span>
            </a>

            <a class="sidebar__item <?php echo ($current_page === 'mynotes') ? 'active' : ''; ?>"
               href="user_dashboard.php"
               id="nav-mynotes"
               <?php if ($current_page === 'dashboard'): ?>onclick="navigateTo('mynotes'); return false;"<?php endif; ?>>
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                </svg>
                <span>My Notes</span>
            </a>

            <a class="sidebar__item <?php echo ($current_page === 'bookmarks') ? 'active' : ''; ?>"
               href="user_dashboard.php"
               id="nav-bookmarks"
               <?php if ($current_page === 'dashboard'): ?>onclick="navigateTo('bookmarks'); return false;"<?php endif; ?>>
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M17.593 3.322c1.1.128 1.907 1.077 1.907 2.185V21L12 17.25 4.5 21V5.507c0-1.108.806-2.057 1.907-2.185a48.507 48.507 0 0 1 11.186 0Z" />
                </svg>
                <span>Bookmarks</span>
            </a>
        </nav>

        <!-- upload note -->
        <a class="sidebar__upload" href="upload_notes.php">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                <line x1="12" y1="5" x2="12" y2="19"/>
                <line x1="5" y1="12" x2="19" y2="12"/>
            </svg>
            <span>Upload New Note</span>
        </a>

        <div class="sidebar__footer">
            <a class="sidebar__item <?php echo ($current_page === 'help_center') ? 'active' : ''; ?>" href="help_center.php">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m11.25 11.25.041-.02a.75.75 0 0 1 1.063.852l-.708 2.836a.75.75 0 0 0 1.063.853l.041-.021M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9-3.75h.008v.008H12V8.25Z" />
                </svg>
                <span>Help Center</span>
            </a>

            <!-- settings popup trigger -->
            <a class="sidebar__item <?php echo ($current_page === 'settings') ? 'active' : ''; ?>" href="#" id="nav-settings" onclick="toggleSettingsPopup(event)">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.325.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 0 1 1.37.49l1.296 2.247a1.125 1.125 0 0 1-.26 1.431l-1.003.827c-.293.241-.438.613-.43.992a7.723 7.723 0 0 1 0 .255c-.008.378.137.75.43.991l1.004.827c.424.35.534.955.26 1.43l-1.298 2.247a1.125 1.125 0 0 1-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.47 6.47 0 0 1-.22.128c-.331.183-.581.495-.644.869l-.213 1.281c-.09.543-.56.94-1.11.94h-2.594c-.55 0-1.019-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 0 1-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 0 1-1.369-.49l-1.297-2.247a1.125 1.125 0 0 1 .26-1.431l1.004-.827c.292-.24.437-.613.43-.991a6.932 6.932 0 0 1 0-.255c.007-.38-.138-.751-.43-.992l-1.004-.827a1.125 1.125 0 0 1-.26-1.43l1.297-2.247a1.125 1.125 0 0 1 1.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.086.22-.128.332-.183.582-.495.644-.869l.214-1.28Z" />
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                </svg>
                <span>Settings</span>
            </a>

            <div class="account-popup" id="account-popup">
                <p class="account-popup__label">Account</p>

                <div class="account-popup__user">
                    <div class="account-popup__avatar">
                        <?php if (!empty($user['profilePicture'])): ?>
                            <img src="<?php echo htmlspecialchars($user['profilePicture']); ?>" alt="Profile picture" style="width:100%;height:100%;object-fit:cover;border-radius:50%;">
                        <?php else: ?>
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <circle cx="12" cy="8" r="4"/>
                                <path d="M4 20c0-4 3.6-7 8-7s8 3 8 7"/>
                            </svg>
                        <?php endif; ?>
                    </div>
                    <div class="account-popup__info">
                        <p class="account-popup__name"><?php echo htmlspecialchars($user['studentName']); ?></p>
                        <p class="account-popup__email"><?php echo htmlspecialchars($user['studentEmail']); ?></p>
                    </div>
                </div>

                <div class="account-popup__divider"></div>

                <a class="account-popup__item <?php echo ($current_page === 'settings') ? 'account-popup__item--active' : ''; ?>" href="account_setting.php">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="16" height="16">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.325.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 0 1 1.37.49l1.296 2.247a1.125 1.125 0 0 1-.26 1.431l-1.003.827c-.293.241-.438.613-.43.992a7.723 7.723 0 0 1 0 .255c-.008.378.137.75.43.991l1.004.827c.424.35.534.955.26 1.43l-1.298 2.247a1.125 1.125 0 0 1-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.47 6.47 0 0 1-.22.128c-.331.183-.581.495-.644.869l-.213 1.281c-.09.543-.56.94-1.11.94h-2.594c-.55 0-1.019-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 0 1-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 0 1-1.369-.49l-1.297-2.247a1.125 1.125 0 0 1 .26-1.431l1.004-.827c.292-.24.437-.613.43-.991a6.932 6.932 0 0 1 0-.255c.007-.38-.138-.751-.43-.992l-1.004-.827a1.125 1.125 0 0 1-.26-1.43l1.297-2.247a1.125 1.125 0 0 1 1.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.086.22-.128.332-.183.582-.495.644-.869l.214-1.28Z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                    </svg>
                    <span>Account Setting</span>
                </a>

                <a class="account-popup__item account-popup__item--logout" href="logout.php">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="16" height="16">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 9V5.25A2.25 2.25 0 0 1 10.5 3h6a2.25 2.25 0 0 1 2.25 2.25v13.5A2.25 2.25 0 0 1 16.5 21h-6a2.25 2.25 0 0 1-2.25-2.25V15M12 9l-3 3m0 0 3 3m-3-3H21" />
                    </svg>
                    <span>Log Out</span>
                </a>
            </div>
        </div>
    </aside>

<script>
(function () {
    const settingsBtn = document.getElementById('nav-settings');
    const accountPopup = document.getElementById('account-popup');
    if (!settingsBtn || !accountPopup) return;

    window.toggleSettingsPopup = function (event) {
        event.preventDefault();
        event.stopPropagation();
        accountPopup.classList.toggle('show');
    };

    window.addEventListener('click', function (event) {
        if (accountPopup.classList.contains('show') &&
            !accountPopup.contains(event.target) &&
            event.target !== settingsBtn) {
            accountPopup.classList.remove('show');
        }
    });
})();
</script>