function likePost(button) {
    const postId = button.getAttribute('data-post-id');
  
    fetch('like-post.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: `post_id=${postId}`
    })
    .then(response => response.text())
    .then(data => {
      if (data === 'success') {
        button.classList.add('liked');
        button.disabled = true;
        let countSpan = button.querySelector('span');
        countSpan.textContent = parseInt(countSpan.textContent) + 1;
      } else {
        alert(data);
      }
    });
  }
  
  document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.btn-like').forEach(btn => {
      btn.addEventListener('click', function() {
        if (!btn.classList.contains('liked')) {
          likePost(btn);
        } else {
          alert('你已經點過讚了');
        }
      });
    });
  });
  