document.getElementById('profileForm').addEventListener('submit', function(e) {
    const name = document.querySelector('input[name="name"]').value;
    const phone = document.querySelector('input[name="phone"]').value;
    const error = document.getElementById('error');
    if (name && name.length > 50) {
        e.preventDefault();
        error.textContent = 'Ім\'я не може бути довшим за 50 символів';
    }
    if (phone && !/^\+?\d{10,15}$/.test(phone)) {
        e.preventDefault();
        error.textContent = 'Введіть коректний номер телефону (10-15 цифр)';
    }
});