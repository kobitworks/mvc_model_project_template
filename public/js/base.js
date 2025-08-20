// ナビゲーションのドロップダウン制御
document.addEventListener('DOMContentLoaded', function () {
  document.querySelectorAll('.site-nav .dropdown > a').forEach(function (anchor) {
    anchor.addEventListener('click', function (e) {
      if (anchor.getAttribute('href') === '#') {
        e.preventDefault();
      }
      anchor.parentElement.classList.toggle('open');
    });
  });
});
