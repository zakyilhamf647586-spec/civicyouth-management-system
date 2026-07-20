(function () {
    'use strict';

    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('[data-canva-master-link]')
            .forEach(function (link) {
                link.addEventListener('click', function (event) {
                    var confirmed = window.confirm(
                        'Master Canva harus tetap bersih. Setelah terbuka, buat salinan desain terlebih dahulu sebelum mengedit. Lanjutkan?'
                    );

                    if (!confirmed) {
                        event.preventDefault();
                    }
                });
            });

        var form = document.querySelector('[data-publication-form]');

        if (!form) {
            return;
        }

        var catalogElement = form.querySelector('[data-template-catalog]');
        var templateSelect = form.querySelector('[data-template-select]');
        var typeSelect = form.querySelector('#publication_type');
        var codeElement = form.querySelector('[data-template-code]');
        var nameElement = form.querySelector('[data-template-name]');
        var metaElement = form.querySelector('[data-template-meta]');
        var linkElement = form.querySelector('[data-template-link]');
        var templates = {};

        if (!catalogElement || !templateSelect) {
            return;
        }

        try {
            templates = JSON.parse(catalogElement.textContent || '{}');
        } catch (error) {
            return;
        }

        var updateTemplatePreview = function (syncType) {
            var code = templateSelect.value;
            var template = templates[code];

            if (!template) {
                return;
            }

            if (codeElement) {
                codeElement.textContent = code;
            }

            if (nameElement) {
                nameElement.textContent = template.name || '-';
            }

            if (metaElement) {
                var details = [template.format, template.pages ? template.pages + ' halaman' : '']
                    .filter(Boolean);

                metaElement.textContent = details.join(' · ');
            }

            if (linkElement) {
                linkElement.href = template.url || '#';
                linkElement.setAttribute(
                    'aria-label',
                    'Buka master Canva ' + (template.name || code)
                );
            }

            if (syncType && typeSelect && template.type) {
                typeSelect.value = template.type;
            }
        };

        templateSelect.addEventListener('change', function () {
            updateTemplatePreview(true);
        });

        if (typeSelect) {
            typeSelect.addEventListener('change', function () {
                var matchingOption = Array.prototype.find.call(
                    templateSelect.options,
                    function (option) {
                        var template = templates[option.value];

                        return template && template.type === typeSelect.value;
                    }
                );

                if (
                    templates[templateSelect.value]
                    && templates[templateSelect.value].type === typeSelect.value
                ) {
                    return;
                }

                if (matchingOption) {
                    templateSelect.value = matchingOption.value;
                    updateTemplatePreview(false);
                }
            });
        }

        updateTemplatePreview(false);
    });
})();
