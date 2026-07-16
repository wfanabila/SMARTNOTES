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

B. User Dashboard

The User Dashboard serves as the student's central hub after logging in, giving a quick snapshot of their activity and quick access to their notes and bookmarks.

1. Profile Overview

The dashboard header displays the student's name, profile picture, and bio (editable via Account Settings). If no bio has been set, a friendly prompt encourages the student to add one.

2. Stats Summary

Two stat cards give students an at-a-glance view of their contributions:
         1. Points Earned – total earnings (RM) from premium note sales, with a weekly breakdown of sales and earnings
         2. Uploads – total number of notes the student has uploaded

3. My Notes Tab

Displays all notes uploaded by the student as cards, each showing the subject code, note type (Free/Premium with price), title, description, average star rating, review count, and upload date. Each note card includes a dropdown menu to:
         1. View note details
         2. Edit the note
         3. Remove the note (with confirmation prompt, deletes both the database record and the stored file)

4. Bookmarks Tab

Displays notes the student has bookmarked from browsing the platform, with the same card layout as My Notes. Students can remove a bookmark directly from the card's dropdown menu. Bookmarks automatically refresh via AJAX when the page loads or becomes visible again (e.g., returning from viewing a note), keeping the list in sync without a full page reload.

5. Quick Upload Access

A dedicated "New Upload" card is always present in the My Notes grid, giving students a one-click shortcut to the upload page.

---

C. Account Settings

The Account Settings page allows students (and admins, via a shared view) to manage their profile information and account security.

1. Profile Information

Students can update their:
         1. Full name and email address
         2. Programme and semester
         3. Bio

Changes are validated server-side (name and email are required) and saved directly to the database.

2. Profile Picture

Students can upload a profile picture via a drag-and-drop modal or file picker. The system:
         1. Accepts only JPG/PNG formats, up to 5MB
         2. Automatically deletes the previous profile picture when a new one is uploaded
         3. Displays validation errors for unsupported file types or oversized files

3. Password Management

Students can change their password through a modal with:
         1. Current password verification
         2. New password confirmation matching
         3. Minimum 8-character length requirement
         4. A check to ensure the new password differs from the current one

---

D. Help Center

The Help Center provides self-service support for both students and admins.

1. FAQ Accordion

Common questions are organized into an expandable/collapsible list covering topics like email verification, supported upload formats, how earnings are calculated, and content withdrawal policy.

2. Live Search & Filter

A search bar lets users filter FAQs in real time by matching their query against both the question and answer text, with an empty-state message shown when no results match.

3. Shared Admin/Student View

The Help Center dynamically adapts based on session role — admins see the same interface populated with their own account details via the admin bootstrap, while students see their own profile context, avoiding the need for two separate pages.

E. Landing Page
This is the main page that students see after logging into the SMARTNOTES system. It serves as the central hub for discovering and accessing study materials.

How to Use:
Search for Materials – Use the search bar at the top to find study notes by entering a course code, subject name, or keyword.

Quick Filters – Click on any of the programme code buttons (e.g., CSC110, CSC264, CSC267, CSC230, CSC270) to instantly filter notes specific to your course.

Browse Latest Uploads – Scroll down to view the most recently uploaded study materials, displayed as colorful cards.

Access a Note – Click on any note card to open the note details page, where you can preview and download the material.

Key Features:
Intuitive search functionality

One-click programme code filters

Real-time display of latest notes

Clean and responsive card-based layout


F. Admin Management Module
This page allows administrators to view, monitor, and manage all registered student accounts.

How to Use:
View All Students – The main table displays a complete list of registered students with their name, email, join date, and total notes contributed.

Search for a Student – Use the search bar to quickly find a specific student by name or email.

Filter by Status – Use the filter tabs (All, Active, Pending, Suspended) to sort students based on their account status.

View Student Profile – Click the View button to see a student's full profile and activity.

Update or Suspend Account – Click the Update button to modify student details, change their role, or suspend their account if necessary.

Add New Student – Click the Add Student button (blue) to manually create and invite a new student account into the system.

Key Features:
Comprehensive student directory

Search and filter capabilities

Account management tools (view, update, suspend)

Manual student registration

G. Manage Notes
This page enables administrators to review, approve, or reject study materials uploaded by students before they are published to the platform.

How to Use:
View All Submissions – The main table shows all uploaded notes with details such as Title, Subject, Uploader's Name, and Price (Free or RM X.XX).

Filter by Status – Use the filter tabs (All, Pending, Approved, Rejected) to sort notes based on their moderation status.

Review a Note – Click the View button to inspect the submitted content and ensure it meets quality guidelines.

Approve a Note – Click the green Approve button to publish the note and make it available to all students.

Reject a Note – Click the orange Reject button to decline a submission if it violates platform guidelines.

Key Features:
Centralized moderation dashboard

Status-based filtering

Quick approve/reject actions

Transparent submission review process

H. Admin Notes View
This page provides administrators with a student-like view of all published notes, making it easy to browse and monitor available content.

How to Use:
Filter Notes – Use the dedicated filter panel on the side to sort notes by Subject or Type (Free / Premium).

Search for Materials – Use the search bar at the top right to quickly locate specific study materials.

Browse Published Notes – All approved notes are displayed as neat, organized cards showing the subject code, uploader name, and note type (Free or Premium).

Key Features:
Admin-friendly browsing interface

Subject and type filters

Quick search functionality

Card-based visual display
---

## Known Limitations

- **OTP email delivery:** The Forgot Password feature generates and sends OTP emails successfully via our email service (Brevo)
 — this can be confirmed in the service's delivery logs. However, delivery to some recipient providers (e.g. Gmail, Yahoo) may be blocked due to stricter 
 sender-authentication (SPF/DKIM) policies, since our sending address is a free email account rather than a verified custom domain. 
 This is an infrastructure limitation, not an application bug.

---
