// Back to Top Button
document.addEventListener('DOMContentLoaded', function() {
    // Back to Top Button
    var backToTopButton = document.getElementById('backToTop');
    if (backToTopButton) {
        window.addEventListener('scroll', function() {
            if (window.pageYOffset > 300) {
                backToTopButton.style.display = 'block';
            } else {
                backToTopButton.style.display = 'none';
            }
        });
        
        backToTopButton.addEventListener('click', function(e) {
            e.preventDefault();
            window.scrollTo({top: 0, behavior: 'smooth'});
        });
    }
    
    // Ajax comment submission
    var commentForm = document.getElementById('commentForm');
    if (commentForm) {
        commentForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            var formData = new FormData(this);
            
            fetch(window.location.href, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Thêm comment mới vào DOM
                    var commentList = document.getElementById('commentList');
                    if (commentList.querySelector('.text-center.text-muted')) {
                        commentList.innerHTML = '';
                    }
                    
                    var newComment = document.createElement('div');
                    newComment.innerHTML = data.html;
                    commentList.insertBefore(newComment.firstChild, commentList.firstChild);
                    
                    // Xóa nội dung textarea
                    document.getElementById('comment').value = '';
                    
                    // Hiển thị thông báo thành công
                    var alertSuccess = document.createElement('div');
                    alertSuccess.className = 'alert alert-success';
                    alertSuccess.textContent = data.message;
                    
                    var formContainer = commentForm.parentNode;
                    formContainer.insertBefore(alertSuccess, commentForm);
                    
                    // Xóa thông báo sau 3 giây
                    setTimeout(function() {
                        alertSuccess.remove();
                    }, 3000);
                } else {
                    if (data.redirect) {
                        window.location.href = data.redirect;
                    } else {
                        // Hiển thị thông báo lỗi
                        var alertError = document.createElement('div');
                        alertError.className = 'alert alert-danger';
                        alertError.textContent = data.message;
                        
                        var formContainer = commentForm.parentNode;
                        formContainer.insertBefore(alertError, commentForm);
                        
                        // Xóa thông báo sau 3 giây
                        setTimeout(function() {
                            alertError.remove();
                        }, 3000);
                    }
                }
            })
            .catch(error => {
                console.error('Error:', error);
            });
        });
    }
    
    // Favorites functionality
    const favoriteButtons = document.querySelectorAll('.favorite-btn');
    favoriteButtons.forEach(button => {
        button.addEventListener('click', function() {
            const postId = this.getAttribute('data-post-id');
            
            fetch(SITE_URL + '/ajax/toggle_favorite.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: 'post_id=' + postId
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Toggle the heart icon and active class
                    const icon = this.querySelector('i');
                    if (data.is_favorite) {
                        icon.classList.remove('far');
                        icon.classList.add('fas');
                        this.classList.add('active');
                    } else {
                        icon.classList.remove('fas');
                        icon.classList.add('far');
                        this.classList.remove('active');
                    }
                    
                    // Show notification
                    const notification = document.createElement('div');
                    notification.className = 'position-fixed top-0 end-0 p-3';
                    notification.style.zIndex = '5000';
                    
                    notification.innerHTML = `
                        <div class="toast show" role="alert" aria-live="assertive" aria-atomic="true">
                            <div class="toast-header">
                                <strong class="me-auto">Thông báo</strong>
                                <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
                            </div>
                            <div class="toast-body">
                                ${data.message}
                            </div>
                        </div>
                    `;
                    
                    document.body.appendChild(notification);
                    
                    // Remove notification after 3 seconds
                    setTimeout(() => {
                        notification.remove();
                    }, 3000);
                } else {
                    if (data.redirect) {
                        window.location.href = data.redirect;
                    }
                }
            })
            .catch(error => {
                console.error('Error:', error);
            });
        });
    });
}); 