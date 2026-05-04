document.addEventListener('DOMContentLoaded', () => {
  if (typeof lucide !== 'undefined') {
    lucide.createIcons();
  }

  document.getElementById('loginForm').addEventListener('submit', function (e) {
    e.preventDefault();

    const emailInput = document.getElementById('loginEmail');
    const passInput = document.getElementById('loginPassword');
    const btn = document.getElementById('loginBtn');
    const errorMsg = document.getElementById('errorMsg');

    btn.innerHTML = 'Verifying...';
    btn.classList.add('btnloading');
    btn.disabled = true;
    errorMsg.classList.remove('visible');

    emailInput.classList.remove('inputerror');
    passInput.classList.remove('inputerror');

    setTimeout(() => {
      const foundUser = window.MOCK_DATA.users.find(
        u => u.email === emailInput.value && u.password === passInput.value
      );

      if (foundUser) {
        localStorage.setItem('userRole', foundUser.role);
        localStorage.setItem('userName', foundUser.name);
        btn.innerHTML = '✓ Success';
        btn.classList.remove('btnloading');
        btn.classList.add('btnsuccess');

        setTimeout(() => {
          window.location.href = 'dashboard.html';
        }, 400);

      } else {
        btn.innerHTML = 'Log in';
        btn.classList.remove('btnloading');
        btn.disabled = false;
        errorMsg.classList.add('visible');
        emailInput.classList.add('inputerror');
        passInput.classList.add('inputerror');
      }
    }, 600);
  });
});
