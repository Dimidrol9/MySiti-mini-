document.addEventListener('DOMContentLoaded', function() {
    const questions = [
        'Ви ще тут?',
        'Все гаразд?',
        'Продовжуємо працювати?',
        'Ви активні?',
        'Ще не пішли?'
    ];
    const modal = document.getElementById('activityModal');
    const questionElement = document.getElementById('activityQuestion');
    const confirmButton = document.getElementById('confirmActivity');
    let timeoutId;

    function showActivityCheck() {
        const randomQuestion = questions[Math.floor(Math.random() * questions.length)];
        questionElement.textContent = randomQuestion;
        modal.style.display = 'flex';

        // Таймаут на 30 секунд
        timeoutId = setTimeout(() => {
            window.location.href = 'logout.php';
        }, 30000);

        confirmButton.onclick = function() {
            fetch('check_activity.php', { method: 'POST' })
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        modal.style.display = 'none';
                        clearTimeout(timeoutId);
                    } else {
                        window.location.href = 'logout.php';
                    }
                })
                .catch(() => {
                    window.location.href = 'logout.php';
                });
        };
    }

    // Перший виклик через 5 хвилин, потім кожні 5 хвилин
    setTimeout(() => {
        showActivityCheck();
        setInterval(showActivityCheck, 300000); // 5 хвилин = 300000 мс
    }, 300000);
});