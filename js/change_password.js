document.getElementById('changePasswordForm').addEventListener('submit', function(e) {
    const oldPassword = document.querySelector('input[name="old_password"]').value;
    const newPassword = document.querySelector('input[name="new_password"]').value;
    const error = document.getElementById('error');
    if (oldPassword.length < 6 || newPassword.length < 6) {
        e.preventDefault();
        error.textContent = 'Паролі мають бути не коротше 6 символів';
    } else if (oldPassword === newPassword) {
        e.preventDefault();
        error.textContent = 'Новий пароль не може бути таким самим, як старий';
    }
});