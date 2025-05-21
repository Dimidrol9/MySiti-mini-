/**
 * Скрипт для перевірки активності користувача з модальним вікном і синхронізацією між сторінками
 */
document.addEventListener('DOMContentLoaded', function() {
    console.log('Скрипт activity_check.js запущено');

    const SESSION_TIMEOUT = 240000; // 4 хвилини в мілісекундах
    let secondsLeft = 30; // Таймер для відповіді в модальному вікні
    let timerInterval; // Інтервал для таймера модального вікна

    // Функція для збереження поточної сторінки
    function saveCurrentPage() {
        const currentUrl = window.location.href;
        sessionStorage.setItem('returnToPage', currentUrl);
        console.log('Збережено поточну сторінку:', currentUrl);
    }

    // Функція для запуску таймера модального вікна
    function startTimer() {
        clearInterval(timerInterval);
        secondsLeft = 30;
        const timerElement = document.getElementById('timer');
        if (timerElement) {
            timerElement.textContent = secondsLeft;
        }
        timerInterval = setInterval(() => {
            secondsLeft--;
            if (timerElement) {
                timerElement.textContent = secondsLeft;
            }
            if (secondsLeft <= 0) {
                console.log('Час вийшов, перенаправлення на login.php');
                window.location.href = 'login.php';
            }
        }, 1000);
    }

    // Функція для показу модального вікна
    function showActivityModal(question) {
        const activityQuestion = document.getElementById('activityQuestion');
        const errorMessage = document.getElementById('errorMessage');
        const activityModal = document.getElementById('activityModal');
        if (activityQuestion && errorMessage && activityModal) {
            activityQuestion.textContent = question;
            errorMessage.textContent = '';
            activityModal.style.display = 'block';
            startTimer();
            console.log('Модальне вікно відкрито з питанням:', question);
        } else {
            console.error('Елементи модального вікна не знайдено, перенаправлення на verify_activity.php');
            saveCurrentPage();
            window.location.href = '/auth_app/verify_activity.php';
        }
    }

    // Функція для приховування модального вікна
    function hideActivityModal() {
        const activityModal = document.getElementById('activityModal');
        if (activityModal) {
            activityModal.style.display = 'none';
            clearInterval(timerInterval);
            console.log('Модальне вікно приховано');
        }
    }

    // Функція для перевірки активності через AJAX
    function checkActivity() {
        console.log('Перевірка активності через AJAX');
        fetch('/auth_app/check_activity.php', { method: 'GET' })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    showActivityModal(data.question);
                    // Зберігаємо час початку перевірки
                    sessionStorage.setItem('activityCheckTime', Date.now());
                    console.log('Час перевірки збережено:', sessionStorage.getItem('activityCheckTime'));
                } else {
                    console.log('Користувач не авторизований, перенаправлення на login.php');
                    window.location.href = 'login.php';
                }
            })
            .catch(error => {
                console.error('Помилка AJAX-запиту:', error);
                console.log('Перенаправлення на verify_activity.php через помилку');
                saveCurrentPage();
                window.location.href = '/auth_app/verify_activity.php';
            });
    }

    // Функція для надсилання відповіді
    function submitAnswer(answer) {
        console.log('Надсилання відповіді:', answer);
        fetch('/auth_app/check_activity.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'answer=' + encodeURIComponent(answer)
        })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    hideActivityModal();
                    // Оновлюємо час перевірки після успішної відповіді
                    sessionStorage.setItem('activityCheckTime', Date.now());
                    console.log('Успішна відповідь, оновлено час перевірки');
                    scheduleNextCheck();
                } else {
                    const errorMessage = document.getElementById('errorMessage');
                    if (errorMessage) {
                        errorMessage.textContent = data.message;
                        console.log('Помилка відповіді:', data.message);
                        startTimer(); // Перезапускаємо таймер для повторної спроби
                    }
                }
            })
            .catch(error => {
                console.error('Помилка надсилання відповіді:', error);
                saveCurrentPage();
                window.location.href = '/auth_app/verify_activity.php';
            });
    }

    // Функція для планування наступної перевірки
    function scheduleNextCheck() {
        // Очищаємо попередній таймер
        clearTimeout(window.activityCheckTimeout);

        // Обчислюємо час до наступної перевірки
        const lastCheckTime = parseInt(sessionStorage.getItem('activityCheckTime')) || Date.now();
        const timeSinceLastCheck = Date.now() - lastCheckTime;
        const timeToNextCheck = Math.max(0, SESSION_TIMEOUT - timeSinceLastCheck);

        console.log(`Планування наступної перевірки через ${timeToNextCheck / 1000} секунд`);
        window.activityCheckTimeout = setTimeout(() => {
            console.log('Запланована перевірка активності спрацювала');
            checkActivity();
        }, timeToNextCheck);
    }

    // Ініціалізація при завантаженні сторінки
    if (!sessionStorage.getItem('activityCheckTime')) {
        console.log('Немає збереженого часу перевірки, запуск нової перевірки');
        sessionStorage.setItem('activityCheckTime', Date.now());
        checkActivity();
    } else {
        console.log('Знайдено збережений час перевірки, планування наступної');
        scheduleNextCheck();
    }

    

    // Додаємо обробники подій для кнопок
    const yesButton = document.getElementById('yesButton');
    const noButton = document.getElementById('noButton');
    if (yesButton && noButton) {
        yesButton.addEventListener('click', () => {
            console.log('Натискання кнопки "Так"');
            submitAnswer('Так');
        });
        noButton.addEventListener('click', () => {
            console.log('Натискання кнопки "Ні"');
            submitAnswer('Ні');
        });
    } else {
        console.warn('Кнопки модального вікна не знайдено');
    }
});