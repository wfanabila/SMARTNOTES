# UiTMNoteLink

UiTMNoteLink is a web-based note-sharing platform built for UiTM students to upload, browse, rate, and download study notes 
organized by subject and semester.

**Live Demo:** http://localhost/uitmnotelink/front_page.html
**Google Drive for localhost table: https://drive.google.com/drive/folders/15d-IgxZf_th60kLfkoiA8iEGeKCUbT96?usp=sharing

---

## Project Description

UiTMNoteLink allows students to:
- Register and log in securely
- Upload and browse notes by subject/semester
- Rate and comment on notes
- View top contributors on a ranked leaderboard

Admins can manage students, notes, and monitor platform activity through a separate admin panel.

---

## Tech Stack

- **Backend:** PHP (procedural), MySQLi & PDO
- **Database:** MySQL
- **Frontend:** HTML, CSS, JavaScript
- **Email service:** Brevo (transactional email API) for OTP delivery
- **Hosting:** InfinityFree (a free hosting domain)

---

---

## How to Login as Admin

1. Go to the live site and click **Admin Login** (or navigate directly to `admin_login.php`).
2. Use the demo admin credentials:
   - Email: rahafizuddinrazali@gmail.com
   - Password: @Admin12345
3. Once logged in, you'll land on the **Admin Dashboard**, where you can:
   - Manage student accounts
   - Manage uploaded notes
   - View the admin-side Contributors ranking

> ⚠️ Note for reviewers: please use the this account above 

---

## How to Register & Login as Student

1. Go to the live site's front page and click **Register**.
2. Fill in the registration form with your student details (name, email, password, etc.) and submit.
3. Once registered, go to **Login** and sign in using the email and password you just registered with.
4. You'll be redirected to the **Student Dashboard**, where you can browse notes, upload your own, and manage your profile.

---

## How to View the Contributors Page

1. After logging in (as either student or admin), click **Contributors** in the navigation menu (top nav on desktop/tablet, bottom nav on mobile).
2. The page displays a ranked leaderboard of students based on:
   - **Number of notes uploaded** (primary ranking factor)
   - **Average rating received** on their notes (used as a tie-breaker among students with equal uploads)
3. Top 3 contributors are shown in a podium-style layout, followed by the rest of the ranked list.

---

## Other Features

*(To be filled in by teammates responsible for these sections — e.g. notes upload/browsing, admin management tools, ratings & comments, etc.)*

---

## Known Limitations

- **OTP email delivery:** The Forgot Password feature generates and sends OTP emails successfully via our email service (Brevo)
 — this can be confirmed in the service's delivery logs. However, delivery to some recipient providers (e.g. Gmail, Yahoo) may be blocked due to stricter 
 sender-authentication (SPF/DKIM) policies, since our sending address is a free email account rather than a verified custom domain. 
 This is an infrastructure limitation, not an application bug.

---
