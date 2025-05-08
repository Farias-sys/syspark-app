const resetButtons = document.querySelectorAll('button[type="reset"]');

resetButtons.forEach(btn => {
    btn.addEventListener('click', event => {
        event.preventDefault();

        const inputs = document.querySelectorAll('input.form-control');
        inputs.forEach(input => {
            input.value = '';
        });

    });
});