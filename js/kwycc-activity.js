document.addEventListener('DOMContentLoaded', function () {
    const modal = document.getElementById('activity-signup-modal');
    const overlay = document.getElementById('activity-modal-overlay');
    const closeBtn = document.querySelector('.activity-modal-close');
    const cancelBtn = document.getElementById('modal-cancel-btn');
    const signupForm = document.getElementById('activity-signup-form');
    const activityCards = document.querySelectorAll('.activity-card');
    const sortDropdown = document.getElementById('activity-sort');

    let currentPostId = null;

    // Open Modal
    function openModal(postId) {
        currentPostId = postId;
        const card = document.querySelector(`[data-post-id="${postId}"]`);

        if (!card) return;

        // Get data from card
        const title = card.querySelector('.activity-card-title').textContent;
        const imageImg = card.querySelector('.activity-card-image img');
        const dateSpan = card.querySelector('.activity-meta-text');
        const details = {};

        card.querySelectorAll('.activity-detail').forEach(detail => {
            const label = detail.querySelector('.activity-detail-label').textContent.trim();
            const value = detail.querySelector('.activity-detail-value').textContent.trim();
            details[label] = value;
        });

        // Populate modal
        document.getElementById('modal-activity-title').textContent = title;
        document.getElementById('modal-activity-image').src = imageImg.src;
        document.getElementById('modal-activity-date').textContent = dateSpan ? dateSpan.textContent : '';
        document.getElementById('modal-activity-name').textContent = details['活動:'] || title;
        document.getElementById('modal-activity-location').textContent = details['地點:'] || '';
        document.getElementById('modal-activity-seats').textContent = details['名額:'] || '';
        document.getElementById('modal-activity-deadline').textContent = details['截止日期:'] || '';

        // Show modal
        modal.style.display = 'block';
        overlay.style.display = 'block';
        document.body.style.overflow = 'hidden';
    }

    // Close Modal
    function closeModal() {
        modal.style.display = 'none';
        overlay.style.display = 'none';
        document.body.style.overflow = 'auto';
        signupForm.reset();
        currentPostId = null;
    }

    // Event Listeners
    activityCards.forEach(card => {
        const btn = card.querySelector('.activity-btn-signup');
        if (btn) {
            btn.addEventListener('click', function (e) {
                e.stopPropagation();
                const postId = this.getAttribute('data-post-id');
                openModal(postId);
            });
        }
    });

    closeBtn.addEventListener('click', closeModal);
    cancelBtn.addEventListener('click', closeModal);
    overlay.addEventListener('click', closeModal);

    // Form Submission
    signupForm.addEventListener('submit', function (e) {
        e.preventDefault();

        const formData = new FormData(signupForm);
        const data = {
            action: 'submit_activity_signup',
            post_id: currentPostId,
            chinese_name: formData.get('chinese_name'),
            english_name: formData.get('english_name'),
            phone: formData.get('phone'),
            id_number: formData.get('id_number'),
            category: formData.get('category'),
            category_confirm: formData.get('category_confirm'),
            nonce: document.querySelector('input[name="activity_nonce"]').value
        };

        fetch(ajaxurl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: new URLSearchParams(data)
        })
            .then(response => response.json())
            .then(result => {
                if (result.success) {
                    alert('報名成功!');
                    closeModal();
                } else {
                    alert('報名失敗，請稍後重試');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('出現錯誤');
            });
    });

    // Sort functionality
    if (sortDropdown) {
        sortDropdown.addEventListener('change', function () {
            const url = new URL(window.location);
            url.searchParams.set('sort', this.value);
            window.location.href = url.toString();
        });
    }
});