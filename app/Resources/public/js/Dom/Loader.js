class Loader {
    static generateLoader(selector, text, mask)
    {
        if (!text) {
            text = 'Učitavanje...';
        }
        let maskClass = mask === true ? 'hasMask' : '';

        let html = `<div class="loader-spinner ${maskClass}">
                        <div class="spinners">
                          <div class="dot1"></div>
                          <div class="dot2"></div>
                        </div>
                        <p class="loader-spinner__text">${text}</p>
                      </div>`;

        if (mask === true) {
            html += '<div class="loader-mask"></div>';
        }

        selector.append(html);
    }

    static pageLoaderToggle()
    {
        const loaderElm = $('#page-loader');

        if (loaderElm.hasClass('hide')) {
            $('#page-loader').fadeOut('slow', function () {
                $(this).removeClass('hide');
            });

            return;
        }

        $('#page-loader').addClass('hide');
    }

    static removeLoader()
    {
        $('.loader-spinner').remove();
    }

    static hideLoader() {
        if (!$('#page-loader').hasClass('hide')) {
            $('#page-loader').addClass('hide');
        }
    }
}

export default Loader;