<?php
$db_host = "localhost";
$db_user = "root";
$db_pass = "";
$db_name = "smartnotes";

$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

session_start();
$currentStudentID = isset($_SESSION['studentID']) ? (int)$_SESSION['studentID'] : 0;

// Fetch current logged-in student's info for the account popup
$currentName = "Guest";
$currentEmail = "";
if ($currentStudentID > 0) {
    $stmtMe = $conn->prepare("SELECT studentName, studentEmail FROM student WHERE studentID = ?");
    $stmtMe->bind_param("i", $currentStudentID);
    $stmtMe->execute();
    $stmtMe->bind_result($meName, $meEmail);
    if ($stmtMe->fetch()) {
        $currentName = $meName;
        $currentEmail = $meEmail;
    }
    $stmtMe->close();
}

$sql = "
    SELECT 
        s.studentID,
        s.studentName,
        s.programme,
        COUNT(DISTINCT n.noteID) AS totalUploads,
        ROUND(AVG(r.ratingValue), 1) AS avgRating
    FROM student s
    INNER JOIN notes n ON n.studentID = s.studentID
    LEFT JOIN rating r ON r.noteID = n.noteID
    GROUP BY s.studentID, s.studentName, s.programme
    ORDER BY avgRating DESC, totalUploads DESC
";

$result = $conn->query($sql);
$contributors = [];
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $row['avgRating'] = $row['avgRating'] ?? 0.0;
        $contributors[] = $row;
    }
}
$conn->close();

$podium = array_slice($contributors, 0, 3);
$restRanking = array_slice($contributors, 3);

function avatarUrl($name) {
    return "https://ui-avatars.com/api/?name=" . urlencode($name) . "&background=F6EDFF&color=6D3BD7&size=128";
}

include_once("sidebar.php");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Top Contributors – UiTMNoteLink</title>
    <link href="https://fonts.googleapis.com/css?family=Inter:400,500,600,700&display=swap" rel="stylesheet">
    <style>
        /* ── Fix: nav hover underline + active page highlight (thicker, per request) ── */
        .topnav__links a.topnav-link {
            border-bottom: 3px solid transparent;
            padding-bottom: 6px;
            transition: border-color 0.15s, color 0.15s;
        }
        .topnav__links a.topnav-link:hover {
            border-bottom-color: #6D3BD7 !important;
            color: #6D3BD7 !important;
        }
        .topnav__links a.topnav-link.nav-active {
            border-bottom-color: #6D3BD7 !important;
            color: #6D3BD7 !important;
            font-weight: 700;
        }

        .contrib-header {
            text-align: center;
            margin-bottom: 36px;
        }
        .contrib-header h1 {
            font-size: 32px;
            font-weight: 700;
            color: #1a1a1a;
            margin-bottom: 10px;
        }
        .contrib-header p {
            font-size: 14px;
            color: #6b6860;
            line-height: 1.6;
        }

        .podium_container {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 20px;
            margin-bottom: 48px;
            flex-wrap: wrap;
        }

        .podium_card {
            background: #ffffff;
            border: 1px solid #e0ddd6;
            border-radius: 14px;
            padding: 22px 24px;
            text-align: center;
            width: 190px;
        }

        .rank_1 {
            background: #6D3BD7;
            border: none;
            width: 210px;
            padding: 30px 24px;
            box-shadow: 0 15px 30px rgba(109, 59, 215, 0.3);
        }

        .badge_rank {
            display: inline-block;
            background: #F6EDFF;
            color: #333333;
            font-size: 12px;
            font-weight: 600;
            padding: 3px 12px;
            border-radius: 20px;
            margin-bottom: 10px;
        }

        .badge_overall {
            display: inline-block;
            background: #f4b400;
            color: #333333;
            font-size: 12px;
            font-weight: 700;
            padding: 4px 14px;
            border-radius: 20px;
            margin-bottom: 12px;
        }

        .avatar {
            width: 62px;
            height: 62px;
            border-radius: 50%;
            object-fit: cover;
            margin-bottom: 10px;
        }

        .avatar_large {
            width: 76px;
            height: 76px;
            border: 3px solid #f4b400;
        }

        .contributor_name {
            font-size: 16px;
            font-weight: 700;
            color: #1a1a1a;
        }

        .name_light { color: #ffffff; font-size: 19px; }

        .contributor_role {
            font-size: 12px;
            color: #6b6860;
            margin-bottom: 16px;
        }

        .role_light { color: #d8c9f5; }

        .stats_row { display: flex; justify-content: space-around; }
        .stat_block { display: flex; flex-direction: column; align-items: center; }

        .stat_label {
            font-size: 10px;
            font-weight: 600;
            color: #9a9690;
            letter-spacing: 0.4px;
            text-transform: uppercase;
            margin-bottom: 4px;
        }

        .label_light { color: #d8c9f5; }

        .stat_value { font-size: 18px; font-weight: 700; color: #1a1a1a; }
        .value_light { color: #ffffff; font-size: 23px; }

        .star { color: #f4b400; font-size: 15px; }

        .rankings_title {
            font-size: 18px;
            font-weight: 700;
            color: #1a1a1a;
            margin-bottom: 18px;
        }
        .rankings_subtitle { font-weight: 400; color: #9a9690; font-size: 14px; }

        .rankings_header {
            display: grid;
            grid-template-columns: 70px 1fr 130px 110px;
            padding: 0 20px 8px;
            font-size: 11px;
            font-weight: 600;
            color: #9a9690;
            text-transform: uppercase;
            letter-spacing: 0.4px;
        }

        .ranking_row {
            display: grid;
            grid-template-columns: 70px 1fr 130px 110px;
            align-items: center;
            background: #ffffff;
            border: 1px solid #e0ddd6;
            border-radius: 12px;
            padding: 12px 20px;
            margin-bottom: 10px;
            font-size: 14px;
            color: #1a1a1a;
            font-weight: 600;
        }

        .current_user_row { background: #F6EDFF; border-color: #d6c3ff; }

        .col_contributor { display: flex; align-items: center; gap: 10px; }

        .row_avatar { width: 30px; height: 30px; border-radius: 50%; object-fit: cover; }

        .you_badge {
            background: #ffffff;
            border: 1px solid #d6c3ff;
            color: #6D3BD7;
            font-size: 10px;
            font-weight: 600;
            padding: 2px 9px;
            border-radius: 20px;
            margin-left: 4px;
        }

        .empty-state { text-align: center; padding: 60px 20px; color: #6b6860; }

        /* ── Account/Settings popup ── */
        .account-popup {
            display: none;
            position: fixed;
            left: 76px;
            bottom: 70px;
            width: 230px;
            background: #ffffff;
            border: 1px solid #e0ddd6;
            border-radius: 12px;
            padding: 14px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.12);
            z-index: 300;
            font-family: 'Inter', sans-serif;
        }
        .account-popup.show { display: block; }
        .account-popup__label {
            font-size: 11px;
            font-weight: 700;
            color: #9a9690;
            text-transform: uppercase;
            letter-spacing: 0.4px;
            margin-bottom: 10px;
        }
        .account-popup__user { display: flex; align-items: center; gap: 10px; margin-bottom: 10px; }
        .account-popup__avatar {
            width: 34px; height: 34px; border-radius: 50%;
            background: #1a1a1a; color: #fff;
            display: flex; align-items: center; justify-content: center;
        }
        .account-popup__name { font-size: 14px; font-weight: 700; color: #1a1a1a; }
        .account-popup__email { font-size: 12px; color: #6b6860; }
        .account-popup__divider { height: 1px; background: #e0ddd6; margin: 10px 0; }
        .account-popup__item {
            display: flex; align-items: center; gap: 8px;
            font-size: 13px; font-weight: 600; color: #333333;
            text-decoration: none; padding: 8px 6px; border-radius: 8px;
        }
        .account-popup__item:hover { background: #F6EDFF; }
        .account-popup__item--logout { color: #dc2626; }

        @media (max-width: 600px) {
            .rankings_header { display: none; }
            .ranking_row { grid-template-columns: 1fr; gap: 4px; text-align: left; }
        }
    </style>
</head>
<body>

<div class="main">
    <div class="main__container">

        <div class="contrib-header">
            <h1>Top Contributors</h1>
            <p>Celebrating the students who make learning accessible for everyone.<br>Your contributions build the future of education</p>
        </div>

        <?php if (empty($contributors)): ?>

            <div class="empty-state">No contributors yet. Be the first to upload a note!</div>

        <?php else: ?>

            <div class="podium_container">
                <?php
                $displayOrder = [1, 0, 2];
                foreach ($displayOrder as $i):
                    if (!isset($podium[$i])) continue;
                    $p = $podium[$i];
                    $rank = $i + 1;
                    $isFirst = ($rank === 1);
                ?>
                    <div class="podium_card <?= $isFirst ? 'rank_1' : ($rank === 2 ? 'rank_2' : 'rank_3') ?>">
                        <?php if ($isFirst): ?>
                            <div class="badge_overall">#1 Overall</div>
                        <?php else: ?>
                            <div class="badge_rank">#<?= $rank ?></div>
                        <?php endif; ?>

                        <img class="avatar <?= $isFirst ? 'avatar_large' : '' ?>" src="<?= avatarUrl($p['studentName']) ?>" alt="<?= htmlspecialchars($p['studentName']) ?>">
                        <div class="contributor_name <?= $isFirst ? 'name_light' : '' ?>"><?= htmlspecialchars($p['studentName']) ?></div>
                        <div class="contributor_role <?= $isFirst ? 'role_light' : '' ?>"><?= htmlspecialchars($p['programme']) ?></div>

                        <div class="stats_row">
                            <div class="stat_block">
                                <span class="stat_label <?= $isFirst ? 'label_light' : '' ?>">Uploads</span>
                                <span class="stat_value <?= $isFirst ? 'value_light' : '' ?>"><?= $p['totalUploads'] ?></span>
                            </div>
                            <div class="stat_block">
                                <span class="stat_label <?= $isFirst ? 'label_light' : '' ?>">Rating</span>
                                <span class="stat_value <?= $isFirst ? 'value_light' : '' ?>"><?= number_format($p['avgRating'], 1) ?> <span class="star">&#9733;</span></span>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <?php if (!empty($restRanking)): ?>
            <div>
                <h2 class="rankings_title">Rankings <span class="rankings_subtitle">- #4 and below</span></h2>

                <div class="rankings_header">
                    <div>Rank</div>
                    <div>Contributor</div>
                    <div>Total Uploads</div>
                    <div>Avg Rating</div>
                </div>

                <?php foreach ($restRanking as $index => $r):
                    $rank = $index + 4;
                    $isYou = ($currentStudentID > 0 && (int)$r['studentID'] === $currentStudentID);
                ?>
                    <div class="ranking_row <?= $isYou ? 'current_user_row' : '' ?>">
                        <div>#<?= $rank ?></div>
                        <div class="col_contributor">
                            <img class="row_avatar" src="<?= avatarUrl($r['studentName']) ?>" alt="<?= htmlspecialchars($r['studentName']) ?>">
                            <?= htmlspecialchars($r['studentName']) ?>
                            <?php if ($isYou): ?><span class="you_badge">You</span><?php endif; ?>
                        </div>
                        <div><?= $r['totalUploads'] ?></div>
                        <div><?= number_format($r['avgRating'], 1) ?> <span class="star">&#9733;</span></div>
                    </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>

        <?php endif; ?>

    </div>
</div>

<!-- Account popup, triggered by the Settings gear icon in sidebar.php -->
<div class="account-popup" id="account-popup">
    <p class="account-popup__label">Account</p>
    <div class="account-popup__user">
        <div class="account-popup__avatar">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <circle cx="12" cy="8" r="4"/>
                <path d="M4 20c0-4 3.6-7 8-7s8 3 8 7"/>
            </svg>
        </div>
        <div>
            <p class="account-popup__name"><?= htmlspecialchars($currentName) ?></p>
            <p class="account-popup__email"><?= htmlspecialchars($currentEmail) ?></p>
        </div>
    </div>
    <div class="account-popup__divider"></div>
    <a class="account-popup__item" href="account_setting.html">Account Setting</a>
    <a class="account-popup__item account-popup__item--logout" href="logout.php">Log Out</a>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const settingsLink = document.querySelector('.sidebar__footer a:last-child');
        const popup = document.getElementById('account-popup');

        if (settingsLink && popup) {
            settingsLink.addEventListener('click', function (e) {
                e.preventDefault();
                e.stopPropagation();
                popup.classList.toggle('show');
            });

            // Close on a single click anywhere outside — uses .contains() instead of
            // strict equality so clicks on icons/child elements are handled correctly
            document.addEventListener('click', function (e) {
                if (!popup.classList.contains('show')) return;
                if (popup.contains(e.target)) return;
                if (settingsLink.contains(e.target)) return;
                popup.classList.remove('show');
            });
        }

        // Highlight "Contributors" as the active nav link
        document.querySelectorAll('.topnav__links a.topnav-link').forEach(function (link) {
            if (link.getAttribute('href') === 'contributors.php') {
                link.classList.add('nav-active');
            }
        });
    });
</script>

</body>
</html>