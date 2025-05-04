<?php
require_once 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $postId = $_POST['post_id'];
    $title = $_POST['title'];
    $content = $_POST['content'];

    $updatePost = $conn->prepare("UPDATE posts SET Title = ?, Content = ? WHERE Post_ID = ?");
    $updatePost->bind_param('ssi', $title, $content, $postId);

    if ($updatePost->execute()) {
        // Calculate the page number for the post
        $postsPerPage = 5; // Ensure this matches the pagination logic
        $positionResult = $conn->query("SELECT COUNT(*) AS position FROM posts WHERE Post_Time > (SELECT Post_Time FROM posts WHERE Post_ID = $postId)");
        $position = $positionResult->fetch_assoc()['position'];
        $page = floor($position / $postsPerPage) + 1;

        // Redirect to the specific post with pagination and highlight
        header("Location: blog-details.php?page=$page&highlight_id=$postId");
        exit;
    }
}
//test
?>