(() => {
    "use strict";

    const button = document.querySelector(
        "[data-copy-external-review]"
    );

    if (!button) {
        return;
    }

    button.addEventListener("click", async () => {
        const selector = button.getAttribute(
            "data-copy-target"
        );

        const input = selector
            ? document.querySelector(selector)
            : null;

        if (!input) {
            return;
        }

        const originalLabel = button.textContent;

        try {
            await navigator.clipboard.writeText(
                input.value
            );

            button.textContent = "Tersalin";
        } catch (error) {
            input.focus();
            input.select();
            document.execCommand("copy");
            button.textContent = "Tersalin";
        }

        window.setTimeout(() => {
            button.textContent = originalLabel;
        }, 1800);
    });
})();
