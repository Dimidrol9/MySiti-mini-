document.getElementById('registerForm').addEventListener('submit', function(e) {
    const email = document.querySelector('input[name="email"]').value;
    const password = document.querySelector('input[name="password"]').value;
    const error = document.getElementById('error');
    if (!email.includes('@')) {
        e.preventDefault();
        error.textContent = 'Введіть коректний email';
    } else if (password.length < 6) {
        e.preventDefault();
        error.textContent = 'Пароль має бути не коротше 6 символів';
    }
});