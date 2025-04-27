
document.getElementById('togglePasswordBtn').addEventListener('click', function() {
    const passwordField = document.getElementById('password');
    const type = (passwordField.type === 'password' ? 'text' : 'password');
    passwordField.type = type;
});
