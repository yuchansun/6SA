function likePost(button) {
    const postId = button.getAttribute('data-post-id');

    fetch('likePost.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ postId })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            button.classList.toggle('liked', data.liked);
            const icon = button.querySelector('i');
            icon.classList.toggle('bi-heart', !data.liked);
            icon.classList.toggle('bi-heart-fill', data.liked);
            button.querySelector('span').textContent = data.newLikesCount;
        } else {
            alert(data.message);
        }
    });
}

function likeComment(button) {
    const commentId = button.getAttribute('data-comment-id');

    fetch('likeComment.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ commentId })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            button.classList.toggle('liked', data.liked);
            const icon = button.querySelector('i');
            icon.classList.toggle('bi-heart', !data.liked);
            icon.classList.toggle('bi-heart-fill', data.liked);
            button.querySelector('span').textContent = data.newLikesCount;
        } else {
            alert(data.message);
        }
    });
}

document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.btn-like').forEach(btn => {
        btn.addEventListener('click', function () {
            if (btn.hasAttribute('data-post-id')) {
                likePost(btn);
            } else if (btn.hasAttribute('data-comment-id')) {
                likeComment(btn);
            }
        });
    });
});
