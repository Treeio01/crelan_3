document.addEventListener('DOMContentLoaded', function() {
    const burgerButton = document.getElementById('burger-menu-button');
    const mobileMenu = document.getElementById('mobile-menu');
    const mobileMenuLinks = document.querySelectorAll('.mobile-menu-link');
    const reviewsList = document.getElementById('reviews-list');
    const reviewForm = document.getElementById('review-form');
    const starRating = document.getElementById('star-rating');
    const ratingValueInput = document.getElementById('rating-value');

    if (burgerButton && mobileMenu) {
        burgerButton.addEventListener('click', function() {
            mobileMenu.classList.toggle('hidden');
        });
    }

    mobileMenuLinks.forEach(link => {
        link.addEventListener('click', function(event) {
            const targetId = this.getAttribute('href').substring(1);
            const targetElement = document.getElementById(targetId);
            if (targetElement) {
                window.scrollTo({
                    top: targetElement.offsetTop - 80, // Adjust for fixed header height
                    behavior: 'smooth'
                });
                mobileMenu.classList.add('hidden'); // Close menu after clicking a link
            }
        });
    });

    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            const targetId = this.getAttribute('href').substring(1);
            const targetElement = document.getElementById(targetId);
            if (targetElement) {
                window.scrollTo({
                    top: targetElement.offsetTop - 80, // Adjust for fixed header height
                    behavior: 'smooth'
                });
            }
        });
    });

    if (starRating && ratingValueInput) {
        starRating.addEventListener('click', function(e) {
            if (e.target.classList.contains('fa-star')) {
                const value = parseInt(e.target.dataset.value);
                ratingValueInput.value = value;
                starRating.querySelectorAll('.fa-star').forEach(star => {
                    if (parseInt(star.dataset.value) <= value) {
                        star.classList.remove('far');
                        star.classList.add('fas');
                    } else {
                        star.classList.remove('fas');
                        star.classList.add('far');
                    }
                });
            }
        });
    }

    if (reviewForm && reviewsList && ratingValueInput) {
        reviewForm.addEventListener('submit', function(e) {
            e.preventDefault();

            const reviewerName = document.getElementById('reviewer-name').value;
            const reviewText = document.getElementById('review-text').value;
            const rating = parseInt(ratingValueInput.value);

            if (!reviewerName || !reviewText || rating === 0) {
                alert('Please fill in all fields and provide a rating.');
                return;
            }

            const newReviewHtml = `
                <div class="bg-gray-800 p-6 rounded-lg shadow-lg">
                    <div class="flex items-center mb-4">
                        <img src="static/images/vector-flat-illustration-grayscale-avatar-600nw-2264922221-Photoroom.png" alt="Avatar" class="w-12 h-12 rounded-full object-cover mr-4">
                        <div>
                            <h4 class="text-xl font-semibold text-gray-100">${reviewerName}</h4>
                            <div class="flex text-yellow-400">
                                ${Array(rating).fill('<i class="fas fa-star"></i>').join('')}
                                ${Array(5 - rating).fill('<i class="far fa-star"></i>').join('')}
                            </div>
                        </div>
                    </div>
                    <p class="text-gray-300">"${reviewText}"</p>
                    <p class="text-gray-500 text-sm mt-4">Reviewed on ${new Date().toISOString().slice(0, 10)}</p>
                </div>
            `;

            const newReviewDiv = document.createElement('div');
            newReviewDiv.innerHTML = newReviewHtml.trim();
            reviewsList.prepend(newReviewDiv.firstChild); // Add new review to the top

            reviewForm.reset();
            ratingValueInput.value = '0';
            starRating.querySelectorAll('.fa-star').forEach(star => {
                star.classList.remove('fas');
                star.classList.add('far');
            });
        });
    }
});