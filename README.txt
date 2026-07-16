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

A. Notes Features

The Notes module is the core functionality of UiTMNoteLink, allowing students to share and access academic resources in an organized and collaborative environment. It provides a complete workflow from uploading notes to downloading and reviewing study materials. The implementation includes note management, discovery, and moderation features that improve accessibility and encourage knowledge sharing among UiTM students.

1. Upload Notes

Students can upload study notes by providing:
         1. Note title and description
         2. Course, semester, and subject
         3. PDF/document file
         4. Note type (Free or Premium)

Uploaded notes are categorized automatically, making them easier for other students to discover. Premium notes allow contributors to assign a selling price before submission.

2. Browse Notes

Students can browse notes through dedicated programme and semester pages. Notes are organized by:
         1. Programme (e.g., CSC110, CSC230, CSC264, CSC267, CSC270)
         2. Semester
         3. Subject

The latest uploaded notes are displayed prominently to improve accessibility.

3. Search & Filter

The platform includes filtering features that allow students to quickly locate study materials by:
         1. Subject
         2. Course code
         3. Semester
         4. Note type (Free/Premium)

This reduces the time required to search for relevant academic resources.

4. View Notes

Before downloading, students can open a note to:
         1. Preview the document
         2. Read the description
         3. View uploader information
         4. Check whether the note is free or premium

Students can also read reviews left by other users before deciding to download or purchase the note.

5. Ratings & Comments

Each note supports community feedback through:
         1. Star ratings
         2. Written comments

Students can share their opinions and experiences, helping others identify high-quality study materials while encouraging contributors to upload useful content.

6. Premium Notes

Contributors may choose to publish notes as:

          1. Free – immediately downloadable by all students.
          2. Premium – requires payment before download.

Premium notes include a secure checkout page where students can complete payment before gaining access to the file.

7. Download Notes

After access is granted (either through free download or successful payment), students can download the study materials directly and keep them for offline revision. The system also records downloads for platform management purposes.

---

## Known Limitations

- **OTP email delivery:** The Forgot Password feature generates and sends OTP emails successfully via our email service (Brevo)
 — this can be confirmed in the service's delivery logs. However, delivery to some recipient providers (e.g. Gmail, Yahoo) may be blocked due to stricter 
 sender-authentication (SPF/DKIM) policies, since our sending address is a free email account rather than a verified custom domain. 
 This is an infrastructure limitation, not an application bug.

---
