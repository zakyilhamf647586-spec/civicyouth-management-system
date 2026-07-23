document.addEventListener('DOMContentLoaded', function () {
    var editor = document.querySelector(
        '[data-navigation-editor]'
    );

    if (!editor) {
        return;
    }

    var list = editor.querySelector(
        '[data-navigation-list]'
    );

    var template = document.querySelector(
        '[data-navigation-template]'
    );

    var addButton = editor.querySelector(
        '[data-navigation-add]'
    );

    var emptyState = editor.querySelector(
        '[data-navigation-empty]'
    );

    var maximumItems = Number(
        editor.getAttribute('data-maximum-items')
        || 20
    );

    if (!list || !template) {
        return;
    }

    function slugify(value) {
        return String(value || '')
            .toLowerCase()
            .trim()
            .replace(/[^a-z0-9_-]+/g, '-')
            .replace(/^-+|-+$/g, '')
            .slice(0, 80);
    }

    function rows() {
        return Array.prototype.slice.call(
            list.querySelectorAll(
                '[data-navigation-row]'
            )
        );
    }

    function reindex() {
        rows().forEach(function (row, index) {
            row.querySelectorAll('[name]').forEach(
                function (field) {
                    field.name = field.name.replace(
                        /items\[\d+\]/,
                        'items[' + index + ']'
                    );
                }
            );

            var number = row.querySelector(
                '[data-navigation-number]'
            );

            if (number) {
                number.textContent = String(index + 1);
            }

            var keyInput = row.querySelector(
                '[data-item-key]'
            );

            var labelInput = row.querySelector(
                '[data-item-label]'
            );

            if (
                keyInput
                && !keyInput.value
                && labelInput
                && labelInput.value
            ) {
                keyInput.value = slugify(
                    labelInput.value
                );
            }
        });

        if (emptyState) {
            emptyState.hidden = rows().length !== 0;
        }

        if (addButton) {
            addButton.disabled =
                rows().length >= maximumItems;
        }
    }

    function bindRow(row) {
        var up = row.querySelector(
            '[data-navigation-up]'
        );

        var down = row.querySelector(
            '[data-navigation-down]'
        );

        var remove = row.querySelector(
            '[data-navigation-remove]'
        );

        var label = row.querySelector(
            '[data-item-label]'
        );

        var key = row.querySelector(
            '[data-item-key]'
        );

        if (up) {
            up.addEventListener('click', function () {
                var previous =
                    row.previousElementSibling;

                if (previous) {
                    list.insertBefore(row, previous);
                    reindex();
                }
            });
        }

        if (down) {
            down.addEventListener('click', function () {
                var next = row.nextElementSibling;

                if (next) {
                    list.insertBefore(next, row);
                    reindex();
                }
            });
        }

        if (remove) {
            remove.addEventListener(
                'click',
                function () {
                    if (
                        window.confirm(
                            'Hapus item ini dari draft navigasi?'
                        )
                    ) {
                        row.remove();
                        reindex();
                    }
                }
            );
        }

        if (label && key) {
            label.addEventListener('input', function () {
                if (!key.dataset.touched) {
                    key.value = slugify(label.value);
                }
            });

            key.addEventListener('input', function () {
                key.dataset.touched = '1';
            });
        }
    }

    rows().forEach(bindRow);

    if (addButton) {
        addButton.addEventListener('click', function () {
            var currentRows = rows();

            if (currentRows.length >= maximumItems) {
                window.alert(
                    'Jumlah item sudah mencapai batas.'
                );
                return;
            }

            var index = currentRows.length;
            var html = template.innerHTML.replace(
                /__INDEX__/g,
                String(index)
            );

            var wrapper =
                document.createElement('div');

            wrapper.innerHTML = html.trim();

            var row = wrapper.firstElementChild;

            if (!row) {
                return;
            }

            list.appendChild(row);
            bindRow(row);
            reindex();

            var label = row.querySelector(
                '[data-item-label]'
            );

            if (label) {
                label.focus();
            }
        });
    }

    reindex();
});
