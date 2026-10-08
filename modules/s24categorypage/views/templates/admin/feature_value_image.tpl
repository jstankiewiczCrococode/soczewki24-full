(function () {
    'use strict';

    function addFeatureValueImageField() {
        var form =
            document.querySelector('form[name="feature_value"]') ||
            document.querySelector('#feature_value_form');

        if (!form) {
            var featureInput = document.querySelector(
                '[name="feature_value[feature_id]"]'
            );

            if (featureInput) {
                form = featureInput.closest('form');
            }
        }

        if (!form) {
            return;
        }

        if (form.querySelector('[name="s24_feature_value_image"]')) {
            return;
        }

        form.setAttribute('enctype', 'multipart/form-data');

        var wrapper = document.createElement('div');
        wrapper.className = 'form-group row';

        wrapper.innerHTML =
            '<label class="form-control-label col-lg-3">' +
                'Obrazek' +
            '</label>' +
            '<div class="col-lg-9">' +
                '<input ' +
                    'type="file" ' +
                    'name="s24_feature_value_image" ' +
                    'accept=".jpg,.jpeg,.png,.webp" ' +
                    'class="form-control" ' +
                '>' +
                '<small class="form-text text-muted">' +
                    'JPG, JPEG, PNG lub WEBP' +
                '</small>' +
            '</div>';

        var submitButton = form.querySelector(
            'button[type="submit"], input[type="submit"]'
        );

        if (submitButton) {
            var submitContainer = submitButton.closest(
                '.form-group, .form-footer, .card-footer'
            );

            if (submitContainer && submitContainer.parentNode) {
                submitContainer.parentNode.insertBefore(
                    wrapper,
                    submitContainer
                );
                return;
            }
        }

        form.appendChild(wrapper);
    }

    function init() {
        addFeatureValueImageField();

        setTimeout(addFeatureValueImageField, 500);
        setTimeout(addFeatureValueImageField, 1500);
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();