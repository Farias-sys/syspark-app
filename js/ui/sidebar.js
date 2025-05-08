const burger   = document.getElementById('burger');
const sidebar  = document.getElementById('sidebar');
const wrapper  = document.querySelector('.main-wrapper');

burger.addEventListener('click', () => {
    sidebar.classList.toggle('open');
    wrapper.classList.toggle('shift');
    burger.classList.toggle('open');
});
