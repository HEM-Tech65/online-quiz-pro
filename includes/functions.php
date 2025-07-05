<?php
require_once 'db.php';

class QuizFunctions {
    private $db;
    
    public function __construct() {
        global $db;
        $this->db = $db;
    }

    // Quiz-related functions
    public function createQuiz($title, $description, $timeLimit, $createdBy) {
        $title = $this->db->escapeString($title);
        $description = $this->db->escapeString($description);
        $timeLimit = (int)$timeLimit;
        $createdBy = (int)$createdBy;
        
        $sql = "INSERT INTO quizzes (title, description, time_limit, created_by) 
                VALUES ('$title', '$description', $timeLimit, $createdBy)";
        
        return $this->db->query($sql);
    }

    public function getQuizzes($forUser = null) {
        $sql = "SELECT q.*, u.name as creator_name FROM quizzes q 
                JOIN users u ON q.created_by = u.user_id";
        
        if ($forUser) {
            $forUser = (int)$forUser;
            $sql .= " WHERE q.created_by = $forUser";
        }
        
        $result = $this->db->query($sql);
        $quizzes = [];
        
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $quizzes[] = $row;
            }
        }
        
        return $quizzes;
    }

    // Question-related functions
    public function addQuestion($quizId, $text, $type, $marks, $options = []) {
        $quizId = (int)$quizId;
        $text = $this->db->escapeString($text);
        $type = $this->db->escapeString($type);
        $marks = (int)$marks;
        
        $sql = "INSERT INTO questions (quiz_id, text, type, marks) 
                VALUES ($quizId, '$text', '$type', $marks)";
        
        if ($this->db->query($sql)) {
            $questionId = $this->db->getLastInsertId();
            
            if ($type == 'mcq' || $type == 'true_false') {
                foreach ($options as $option) {
                    $optionText = $this->db->escapeString($option['text']);
                    $isCorrect = $option['is_correct'] ? 1 : 0;
                    $this->db->query("INSERT INTO options (question_id, text, is_correct) 
                                    VALUES ($questionId, '$optionText', $isCorrect)");
                }
            }
            
            return $questionId;
        }
        
        return false;
    }

    // Result-related functions
    public function submitQuiz($userId, $quizId, $answers) {
        // Calculate score
        $score = $this->calculateScore($quizId, $answers);
        
        // Save result
        $sql = "INSERT INTO results (user_id, quiz_id, score) 
                VALUES ($userId, $quizId, $score)";
        
        return $this->db->query($sql) ? $score : false;
    }

    private function calculateScore($quizId, $answers) {
        $score = 0;
        $quizId = (int)$quizId;
        
        // Get all questions for this quiz
        $questions = $this->getQuestions($quizId);
        
        foreach ($questions as $question) {
            $questionId = $question['question_id'];
            
            if ($question['type'] == 'mcq' || $question['type'] == 'true_false') {
                if (isset($answers[$questionId])) {
                    $selectedOptionId = (int)$answers[$questionId];
                    $isCorrect = $this->isOptionCorrect($selectedOptionId);
                    
                    if ($isCorrect) {
                        $score += $question['marks'];
                    }
                }
            } elseif ($question['type'] == 'short_answer') {
                // For short answer, we'd need manual grading in a real system
                // This is a simplified version
                if (!empty($answers[$questionId])) {
                    $score += $question['marks'] / 2; // Give half marks by default
                }
            }
        }
        
        return $score;
    }

    // Checks if a given option is correct
    private function isOptionCorrect($optionId) {
        $optionId = (int)$optionId;
        $sql = "SELECT is_correct FROM options WHERE option_id = $optionId";
        $result = $this->db->query($sql);
        if ($result && $row = $result->fetch_assoc()) {
            return (bool)$row['is_correct'];
        }
        return false;
    }

    // Additional helper functions would go here...

    // Retrieves all questions for a given quiz
    public function getQuestions($quizId) {
        $quizId = (int)$quizId;
        $sql = "SELECT * FROM questions WHERE quiz_id = $quizId";
        $result = $this->db->query($sql);
        $questions = [];
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $questions[] = $row;
            }
        }
        return $questions;
    }
}

$quizFunctions = new QuizFunctions();
?>