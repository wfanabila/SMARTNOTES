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

$isAdminHelp = (($_SESSION['role'] ?? '') === 'admin');
if ($isAdminHelp) {
    require_once __DIR__ . '/admin_bootstrap.php';
}

$currentStudentID = !$isAdminHelp && isset($_SESSION['user_id'])
    ? (int) $_SESSION['user_id']
    : (!$isAdminHelp && isset($_SESSION['studentID']) ? (int) $_SESSION['studentID'] : 0);

$currentName = $isAdminHelp ? $adminName : "Guest";
$currentEmail = $isAdminHelp ? $adminEmail : "";
$currentPicture = "";

$user = $isAdminHelp
    ? ['studentName' => $adminName, 'studentEmail' => $adminEmail, 'profilePicture' => '']
    : ['studentName' => '', 'studentEmail' => '', 'profilePicture' => ''];

if ($currentStudentID > 0) {
    // profilePicture is optional in older SmartNotes databases, so do not
    // request it here. avatarUrl() supplies a generated avatar when needed.
    $stmtMe = $conn->prepare("SELECT studentName, studentEmail FROM student WHERE studentID = ?");
    $stmtMe->bind_param("i", $currentStudentID);
    $stmtMe->execute();
    $stmtMe->bind_result($meName, $meEmail);
    if ($stmtMe->fetch()) {
        $currentName = $meName;
        $currentEmail = $meEmail;
        $currentPicture = '';

        $user = [
            'studentName'    => $meName,
            'studentEmail'   => $meEmail,
            'profilePicture' => '',
        ];
    }
    $stmtMe->close();
}

$current_page = 'contributors';

$sql = "
    SELECT 
        s.studentID,
        s.studentName,
        s.programme,
        '' AS profilePicture,
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

function avatarUrl($name, $profilePicture = '') {
    if (!empty($profilePicture)) {
        return htmlspecialchars($profilePicture);
    }
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
        .topnav__links a.topnav-link {
            border-bottom: 4px solid transparent;
            padding-bottom: 6px;
            transition: border-color 0.15s, color 0.15s;
        }
        .topnav__links a.topnav-link:hover {
            border-bottom-width: 4px !important;
            border-bottom-color: #6D3BD7 !important;
            color: #6D3BD7 !important;
        }
        .topnav__links a.topnav-link.nav-active {
            border-bottom-width: 4px !important;
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
            flex-wrap: nowrap;
            max-width: 900px;
            margin-left: auto;
            margin-right: auto;
        }

        .podium_card {
            background: #ffffff;
            border: 3px solid #e0ddd6;
            border-radius: 14px;
            padding: 24px 20px;
            text-align: center;
            display: flex;
            flex-direction: column;
            align-items: center;
            flex: 1 1 210px;
            min-width: 0;
            max-width: 240px;
            transition: transform 0.25s ease, box-shadow 0.25s ease;
        }

        .podium_card:hover {
            transform: scale(1.04);
            box-shadow: 0 10px 24px rgba(0, 0, 0, 0.08);
        }

        .rank_1 {
            background: #6D3BD7;
            border: 3px solid rgba(255, 255, 255, 0.45);
            flex-basis: 240px;
            max-width: 270px;
            padding: 30px 22px;
            box-shadow: 0 15px 30px rgba(109, 59, 215, 0.3);
        }

        .rank_1:hover {
            transform: scale(1.04);
            box-shadow: 0 20px 40px rgba(109, 59, 215, 0.4);
        }

        .avatar_wrap {
            position: relative;
            display: inline-block;
            margin-bottom: 12px;
        }

        .badge_rank {
            position: absolute;
            top: -6px;
            right: -6px;
            width: 26px;
            height: 26px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #F6EDFF;
            color: #333333;
            font-size: 11px;
            font-weight: 700;
            border-radius: 50%;
            border: 2px solid #ffffff;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.12);
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
            display: block;
        }

        .avatar_large {
            width: 76px;
            height: 76px;
            border: 3px solid #f4b400;
            margin-bottom: 10px;
        }

        /* rank glow rings */
        .glow_gold { box-shadow: 0 0 0 8px rgba(244, 180, 0, 0.3), 0 0 45px 16px rgba(244, 180, 0, 0.85); }
        .glow_silver { box-shadow: 0 0 0 8px rgba(200, 200, 210, 0.35), 0 0 40px 15px rgba(190, 190, 200, 0.85); }
        .glow_bronze { box-shadow: 0 0 0 8px rgba(205, 127, 50, 0.32), 0 0 40px 15px rgba(205, 127, 50, 0.85); }

        .stats_divider {
            width: 100%;
            height: 1px;
            background: rgba(0, 0, 0, 0.12);
            margin: 6px 0 16px;
        }

        .divider_light {
            background: rgba(255, 255, 255, 0.55);
        }

        .contributor_name {
            font-size: 16px;
            font-weight: 700;
            color: #1a1a1a;
            width: 100%;
            margin-bottom: 3px;
        }

        .name_light { color: #ffffff; font-size: 19px; }

        .contributor_role {
            font-size: 12px;
            color: #6b6860;
            width: 100%;
        }

        .role_light { color: #d8c9f5; }

        .stats_row { display: flex; justify-content: space-around; width: 100%; }
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
            grid-template-columns: 80px 1fr 160px 140px;
            padding: 0 24px 10px;
            font-size: 11px;
            font-weight: 600;
            color: #9a9690;
            text-transform: uppercase;
            letter-spacing: 0.4px;
        }

        .rankings_header > div:nth-child(3),
        .rankings_header > div:nth-child(4) {
            text-align: center;
        }

        .ranking_row {
            display: grid;
            grid-template-columns: 80px 1fr 160px 140px;
            align-items: center;
            background: #ffffff;
            border: 1px solid #e0ddd6;
            border-radius: 12px;
            padding: 16px 24px;
            margin-bottom: 10px;
            font-size: 15px;
            color: #1a1a1a;
            font-weight: 600;
            transition: transform 0.18s ease, border-color 0.18s ease, box-shadow 0.18s ease;
        }

        .ranking_row:hover {
            transform: translateX(6px);
            border-color: #d6c3ff;
            box-shadow: 0 6px 16px rgba(109, 59, 215, 0.12);
        }

        .ranking_row > div:nth-child(3),
        .ranking_row > div:nth-child(4) {
            text-align: center;
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

        @media (max-width: 700px) {
            .podium_container { flex-wrap: wrap; max-width: 100%; }
            .podium_card, .rank_1 { flex: 1 1 100%; max-width: 320px; }
        }

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
                        <?php
                            $glowClass = $rank === 1 ? 'glow_gold' : ($rank === 2 ? 'glow_silver' : 'glow_bronze');
                        ?>
                        <?php if ($isFirst): ?>
                            <div class="badge_overall">#1 Overall</div>
                            <img class="avatar avatar_large <?= $glowClass ?>" src="<?= avatarUrl($p['studentName'], $p['profilePicture']) ?>" alt="<?= htmlspecialchars($p['studentName']) ?>">
                        <?php else: ?>
                            <div class="avatar_wrap">
                                <img class="avatar <?= $glowClass ?>" src="<?= avatarUrl($p['studentName'], $p['profilePicture']) ?>" alt="<?= htmlspecialchars($p['studentName']) ?>">
                                <div class="badge_rank">#<?= $rank ?></div>
                            </div>
                        <?php endif; ?>

                        <div class="contributor_name <?= $isFirst ? 'name_light' : '' ?>"><?= htmlspecialchars($p['studentName']) ?></div>
                        <div class="contributor_role <?= $isFirst ? 'role_light' : '' ?>"><?= htmlspecialchars($p['programme']) ?></div>

                        <div class="stats_divider <?= $isFirst ? 'divider_light' : '' ?>"></div>

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
                            <img class="row_avatar" src="<?= avatarUrl($r['studentName'], $r['profilePicture']) ?>" alt="<?= htmlspecialchars($r['studentName']) ?>">
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

<script>
    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('.topnav__links a.topnav-link').forEach(function (link) {
            if (link.getAttribute('href') === 'contributors.php') {
                link.classList.add('nav-active');
            }
        });
    });
</script>

</body>
</html>
