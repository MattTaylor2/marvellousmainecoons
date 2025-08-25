document.addEventListener('DOMContentLoaded', function () {
  const toggle = document.getElementById('menu-toggle');
  const nav = document.getElementById('site-nav');

  toggle.addEventListener('click', function () {
    nav.classList.toggle('active');
  });
});
