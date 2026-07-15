<?php
require_once __DIR__ . '/admin_bootstrap.php';
$activeAdminPage = 'contributors';

$contributors = [];
$sql = "
    SELECT 
        s.studentID,
        s.studentName,
        s.programme,
        '' AS profilePicture,
        COUNT(DISTINCT n.noteID) AS totalUploads,
        ROUND(AVG(c.rating), 1) AS avgRating
    FROM student s
    INNER JOIN notes n ON n.studentID = s.studentID
    LEFT JOIN comment c ON c.noteID = n.noteID
    GROUP BY s.studentID, s.studentName, s.programme
    ORDER BY avgRating DESC, totalUploads DESC
";

$result = $conn->query($sql);
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $row['avgRating'] = $row['avgRating'] ?? 0.0;
        $contributors[] = $row;
    }
    $result->close();
}
$conn->close();

$podium = array_slice($contributors, 0, 3);
$restRanking = array_slice($contributors, 3);

function avatarUrl($name, $profilePicture = '') {
    if (!empty($profilePicture)) {
        return htmlspecialchars($profilePicture, ENT_QUOTES, 'UTF-8');
    }
    return "https://ui-avatars.com/api/?name=" . urlencode($name) . "&background=F6EDFF&color=6D3BD7&size=128";
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin Contributors | UiTM NoteLink</title>
    <link rel="stylesheet" href="css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .contributors-page { padding: 24px 32px; }
        .contributors-page h1 { margin: 0 0 8px; font-size: 32px; }
        .contributors-page p { margin: 0 0 32px; color: #6b6860; line-height: 1.6; }
        .podium_container { display: flex; justify-content: center; gap: 20px; flex-wrap: wrap; margin-bottom: 42px; }
        .podium_card { background: #ffffff; border: 3px solid #e0ddd6; border-radius: 14px; padding: 24px 20px; text-align: center; width: 100%; max-width: 260px; transition: transform 0.25s ease, box-shadow 0.25s ease; }
        .podium_card:hover { transform: scale(1.03); box-shadow: 0 10px 24px rgba(0, 0, 0, 0.08); }
        .rank_1 { background: #6D3BD7; border-color: rgba(255,255,255,0.45); box-shadow: 0 20px 40px rgba(109,59,215,0.25); }
        .avatar_wrap { position: relative; display: inline-flex; margin-bottom: 12px; }
        .badge_rank { position: absolute; top: -6px; right: -6px; width: 26px; height: 26px; display: flex; align-items: center; justify-content: center; background: #F6EDFF; color: #333; font-size: 11px; font-weight: 700; border-radius: 50%; border: 2px solid #fff; box-shadow: 0 2px 6px rgba(0,0,0,0.12); }
        .badge_overall { display: inline-block; background: #f4b400; color: #333; font-size: 12px; font-weight: 700; padding: 4px 14px; border-radius: 20px; margin-bottom: 14px; }
        .avatar { width: 62px; height: 62px; border-radius: 50%; object-fit: cover; display: block; }
        .avatar_large { width: 76px; height: 76px; border: 3px solid #f4b400; margin-bottom: 10px; }
        .glow_gold { box-shadow: 0 0 0 8px rgba(244, 180, 0, 0.3), 0 0 45px 16px rgba(244, 180, 0, 0.85); }
        .glow_silver { box-shadow: 0 0 0 8px rgba(200, 200, 210, 0.35), 0 0 40px 15px rgba(190, 190, 200, 0.85); }
        .glow_bronze { box-shadow: 0 0 0 8px rgba(205, 127, 50, 0.32), 0 0 40px 15px rgba(205, 127, 50, 0.85); }
        .contributor_name { font-size: 16px; font-weight: 700; color: #1a1a1a; margin-bottom: 3px; }
        .contributor_role { font-size: 12px; color: #6b6860; margin-bottom: 14px; }
        .stats_divider { width: 100%; height: 1px; background: rgba(0,0,0,0.12); margin: 6px 0 18px; }
        .stats_row { display: flex; justify-content: space-around; gap: 12px; }
        .stat_block { display: flex; flex-direction: column; align-items: center; }
        .stat_label { font-size: 10px; font-weight: 600; color: #9a9690; text-transform: uppercase; letter-spacing: 0.4px; margin-bottom: 4px; }
        .stat_value { font-size: 18px; font-weight: 700; color: #1a1a1a; }
        .star { color: #f4b400; font-size: 15px; }
        .ranking_panel { background: #ffffff; border: 1px solid #e0ddd6; border-radius: 16px; padding: 22px; }
        .rankings_header { display: grid; grid-template-columns: 80px 1fr 160px 140px; gap: 0; padding: 0 16px 12px; font-size: 11px; font-weight: 600; color: #9a9690; text-transform: uppercase; letter-spacing: 0.4px; }
        .ranking_row { display: grid; grid-template-columns: 80px 1fr 160px 140px; align-items: center; border: 1px solid #e0ddd6; border-radius: 12px; padding: 16px 16px; margin-bottom: 10px; font-size: 15px; color: #1a1a1a; font-weight: 600; background: #fff; transition: transform 0.18s ease, border-color 0.18s ease, box-shadow 0.18s ease; }
        .ranking_row:hover { transform: translateX(6px); border-color: #d6c3ff; box-shadow: 0 6px 16px rgba(109, 59, 215, 0.12); }
        .col_contributor { display: flex; align-items: center; gap: 10px; }
        .row_avatar { width: 30px; height: 30px; border-radius: 50%; object-fit: cover; }
        @media (max-width: 880px) { .podium_container { flex-direction: column; align-items: center; } }
        @media (max-width: 700px) { .ranking_row, .rankings_header { grid-template-columns: 1fr; } .rankings_header { display: none; } .ranking_row { text-align: left; } }
    </style>
</head>
<body>
<div class="layout">
    <?php include __DIR__ . '/admin_nav.php'; ?>
    <main class="content">
        <header class="topbar">
            <div class="top-nav">
                <a href="admin.php">Dashboard</a>
                <a href="admin_contributors.php" class="active">Contributors</a>
            </div>
            <a class="profile-area" href="adminprofile.php" aria-label="Open profile">
                <div class="profile-circle"><?= admin_escape($adminInitial) ?></div>
                <span><?= admin_escape($adminName) ?></span>
            </a>
        </header>

        <div class="contributors-page">
            <h1>Contributors</h1>
            <p>All student contributors are shown here in one place. No student account is highlighted from the admin view.</p>

            <?php if (empty($contributors)): ?>
                <div class="empty-state">No contributors yet. Students must upload notes first.</div>
            <?php else: ?>
                <div class="podium_container">
                    <?php
                    $displayOrder = [1, 0, 2];
                    foreach ($displayOrder as $i):
                        if (!isset($podium[$i])) continue;
                        $p = $podium[$i];
                        $rank = $i + 1;
                        $isFirst = ($rank === 1);
                        $glowClass = $rank === 1 ? 'glow_gold' : ($rank === 2 ? 'glow_silver' : 'glow_bronze');
                    ?>
                        <div class="podium_card <?= $isFirst ? 'rank_1' : '' ?>">
                            <?php if ($isFirst): ?>
                                <div class="badge_overall">#1 Overall</div>
                                <img class="avatar avatar_large <?= $glowClass ?>" src="<?= avatarUrl($p['studentName'], $p['profilePicture']) ?>" alt="<?= htmlspecialchars($p['studentName'], ENT_QUOTES, 'UTF-8') ?>">
                            <?php else: ?>
                                <div class="avatar_wrap">
                                    <img class="avatar <?= $glowClass ?>" src="<?= avatarUrl($p['studentName'], $p['profilePicture']) ?>" alt="<?= htmlspecialchars($p['studentName'], ENT_QUOTES, 'UTF-8') ?>">
                                    <div class="badge_rank">#<?= $rank ?></div>
                                </div>
                            <?php endif; ?>
                            <div class="contributor_name <?= $isFirst ? 'name_light' : '' ?>"><?= htmlspecialchars($p['studentName'], ENT_QUOTES, 'UTF-8') ?></div>
                            <div class="contributor_role <?= $isFirst ? 'role_light' : '' ?>"><?= htmlspecialchars($p['programme'], ENT_QUOTES, 'UTF-8') ?></div>
                            <div class="stats_divider <?= $isFirst ? 'divider_light' : '' ?>"></div>
                            <div class="stats_row">
                                <div class="stat_block">
                                    <span class="stat_label <?= $isFirst ? 'label_light' : '' ?>">Uploads</span>
                                    <span class="stat_value <?= $isFirst ? 'value_light' : '' ?>"><?= $p['totalUploads'] ?></span>
                                </div>
                                <div class="stat_block">
                                    <span class="stat_label <?= $isFirst ? 'label_light' : '' ?>">Rating</span>
                                    <span class="stat_value <?= $isFirst ? 'value_light' : '' ?>"><?= number_format($p['avgRating'], 1) ?> <span class="star">★</span></span>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <?php if (!empty($restRanking)): ?>
                    <div class="ranking_panel">
                        <div class="rankings_header">
                            <div>Rank</div>
                            <div>Contributor</div>
                            <div>Total Uploads</div>
                            <div>Avg Rating</div>
                        </div>
                        <?php foreach ($restRanking as $index => $r): $rank = $index + 4; ?>
                            <div class="ranking_row">
                                <div>#<?= $rank ?></div>
                                <div class="col_contributor">
                                    <img class="row_avatar" src="<?= avatarUrl($r['studentName'], $r['profilePicture']) ?>" alt="<?= htmlspecialchars($r['studentName'], ENT_QUOTES, 'UTF-8') ?>">
                                    <?= htmlspecialchars($r['studentName'], ENT_QUOTES, 'UTF-8') ?>
                                </div>
                                <div><?= $r['totalUploads'] ?></div>
                                <div><?= number_format($r['avgRating'], 1) ?> <span class="star">★</span></div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </main>
</div>
</body>
</html>
