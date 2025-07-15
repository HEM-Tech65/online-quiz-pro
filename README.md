Online Quiz System

Description

The Online Quiz System is a web-based platform designed to streamline the creation, deployment, and management of quizzes for academic and training environments. 
Developed by Group 4 of Group E for the CSSD 218 (Software Engineering) course at Ghana Communication Technology University, this system enables instructors to create and manage quizzes efficiently while providing students with an intuitive interface to take quizzes and receive instant results. The platform supports multiple question types, timed quizzes, randomized questions, and automated grading, reducing administrative overhead and promoting digital learning.

Features

Admin Capabilities:
Create, edit, and delete quizzes.
Add, modify, and remove multiple-choice (MCQ) and short-answer questions.
Assign quizzes to specific users or groups.
View and export quiz results.


User Capabilities:
Register and log in to access quizzes.
Select and attempt available quizzes.
View quiz history and scores with instant feedback upon submission.


Quiz Functionality:
Supports multiple-choice and short-answer question types.
Configurable time limits for quizzes.
Randomized question order to prevent cheating.
Automatic grading for objective questions.


Non-Functional Features:
Scalable to handle multiple users with minimal latency.
Secure user authentication and authorization.
Reliable with minimal downtime and data backup mechanisms.
Responsive and intuitive interface for both admins and users.



Tech Stack

Frontend: HTML, CSS, JavaScript, Bootstrap
Backend: PHP
Database: phpMyAdmin (MySQL)
Version Control: GitHub

System Architecture
The system follows a three-tier architecture:

Presentation Layer: User interface built with HTML, CSS, JavaScript, and Bootstrap for a responsive design.
Application Layer: Backend logic implemented using PHP for quiz management and evaluation.
Database Layer: phpMyAdmin (MySQL) for storing user data, quizzes, questions, options, and results.

Database Schema
The system uses the following entities and relationships:

User: UserID (PK), Name, Email (Unique), Password (Hashed), Role (Admin/Student)
Quiz: QuizID (PK), Title, Description, TimeLimit, CreatedBy (FK → User)
Question: QuestionID (PK), QuizID (FK → Quiz), Text, Type (MCQ/Short Answer), Marks
Option: OptionID (PK), QuestionID (FK → Question), Text, IsCorrect (Boolean)
Result: ResultID (PK), UserID (FK → User), QuizID (FK → Quiz), Score, SubmissionDate

Relationships:

One Admin can create many Quizzes (1:M).
One Quiz contains many Questions (1:M).
One Question has multiple Options (1:M).
One User can have many Results (1:M).
One Quiz can have many Results (1:M).

Installation

Clone the Repository:
git clone https://github.com/HEM-Tech65/online-quiz-pro.git


Navigate to the Project Directory:
cd online-quiz-pro


Set Up the Environment:

Install a web server (e.g., Apache) and PHP.
Install MySQL and phpMyAdmin for database management.
Configure the database connection in the PHP configuration file (e.g., config.php).


Import the Database:

Create a MySQL database using phpMyAdmin.
Import the provided SQL schema file (if available in the repository) or manually create tables based on the ER diagram above.


Install Dependencies:

Ensure PHP and required extensions (e.g., mysqli) are installed.
If using additional libraries (e.g., Bootstrap), include them via CDN or local files.


Start the Server:

Place the project files in the web server’s root directory (e.g., htdocs for Apache).
Start the web server and MySQL service.



Usage

Access the Application:
Open a browser and navigate to http://localhost/online-quiz-pro (or the appropriate URL based on your server setup).


Admin Usage:
Log in with admin credentials.
Create quizzes, add questions, and assign them to users or groups via the admin dashboard.
View and export quiz results for analysis.


User Usage:
Register or log in as a student.
Select an available quiz from the user dashboard.
Answer questions within the time limit and submit to receive instant results.
View quiz history and scores.



Contributing
Contributions are welcome! To contribute:

Fork the repository.

Create a new branch:
git checkout -b feature-branch


Commit your changes:
git commit -m 'Add some feature'


Push to the branch:
git push origin feature-branch


Open a pull request for review.


Future Enhancements

Implement AI-based proctoring to enhance quiz integrity.
Add advanced analytics for deeper performance insights.
Integrate with Learning Management Systems (LMS) for broader compatibility.

Acknowledgments
Developed by Group 4 of Group E for the CSSD 218 (Software Engineering) course at Ghana Communication Technology University, Tesano Campus, June 2025.
