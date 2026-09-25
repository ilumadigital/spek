document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('[data-contact-form]').forEach((form) => {
        const choices = Array.from(form.querySelectorAll('input[name="request_type"]'));
        const fields = form.querySelector('[data-contact-fields]');
        const message = form.querySelector('textarea[name="message"]');
        const counter = form.querySelector('[data-contact-counter]');
        const submit = form.querySelector('button[type="submit"]');

        if (!fields || choices.length === 0) {
            return;
        }

        const controls = Array.from(fields.querySelectorAll('input, textarea, select, button'));

        const syncType = () => {
            const selected = choices.some((choice) => choice.checked);
            fields.hidden = !selected;

            controls.forEach((control) => {
                if (control !== submit) {
                    control.disabled = !selected;
                }
            });

            form.classList.toggle('has-request-type', selected);
        };

        const syncCounter = () => {
            if (!message || !counter) {
                return;
            }

            counter.textContent = `${message.value.length} / 3000`;
        };

        choices.forEach((choice) => {
            choice.addEventListener('change', syncType);
        });

        if (message) {
            message.addEventListener('input', syncCounter);
        }

        form.addEventListener('submit', () => {
            if (submit) {
                submit.disabled = true;
                submit.classList.add('is-submitting');
            }
        });

        syncType();
        syncCounter();
    });
});
