// assets/js/app.js
document.addEventListener('DOMContentLoaded', function(){
  const loginForm = document.getElementById('loginForm');
  if (loginForm) {
    loginForm.addEventListener('submit', function(e){
      const u = loginForm.querySelector('input[name="username"]').value.trim();
      const p = loginForm.querySelector('input[name="password"]').value.trim();
      if (!u || !p) {
        e.preventDefault();
        alert('Please enter username and password.');
      }
    });
  }
});
