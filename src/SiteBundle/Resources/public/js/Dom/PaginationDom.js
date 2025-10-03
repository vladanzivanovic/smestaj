class PaginationDom {
    constructor(mapper) {
        this.mapper = mapper;
    }

    generate(data) {
        let html = ``;
        let url = location.href;
        let queryPrefix = '?';

        if (location.search.length > 0) {
            queryPrefix = '&';
        }

        url += `${queryPrefix}${Translator.trans('page', null, 'messages', LOCALE)}=`;

        $('.pagination .pages').remove();

        this.generatePrevPage(data, url);
        this.generateNextPage(data, url);
        this.generateLastPage(data, url);

        $('.pagination .first').removeClass('disabled');
        $('.pagination .first a').prop('href', location.href);

        if (data.disableFirst) {
            $('.pagination .first').addClass('disabled');
        }

        let prev = data.prevPage;
        let prevMax = data.currentPage - 3;
        let next = data.nextPage;
        let nextMax = data.nextPage + 3;

        if (nextMax >= data.totalPages) {
            nextMax = data.totalPages;
        }

        if (data.currentPage > 1) {
            while (prev > prevMax) {
                $(`<li class="pages"><a href="${url}${prev}">${prev}</a></li>`).insertAfter('.prev');

                prev--;
            }
        }

        $(`<li class="pages active"><a href="${url}${data.currentPage}">${data.currentPage}</a></li>`).insertAfter('.prev');

        if (data.currentPage < data.totalPages) {
            while (nextMax >= next) {
                $(`<li class="pages"><a href="${url}${next}">${next}</a></li>`).insertBefore('.next');

                next++;
            }
        }

        return html;
    };

    generatePrevPage(data, url) {
        $('.pagination .prev').removeClass('disabled');
        $('.pagination .prev a').prop('href', url + data.prevPage);

        if (data.currentPage === data.prevPage) {
            $('.pagination .prev').addClass('disabled');
            $('.pagination .prev a').prop('href', location.href);
        }
    }

    generateNextPage(data, url) {
        $('.pagination .next').removeClass('disabled');
        $('.pagination .next a').prop('href', url + data.nextPage);

        if (data.disableLast) {
            $('.pagination .next').addClass('disabled');
        }
    }

    generateLastPage(data, url) {
        $('.pagination .last').removeClass('disabled');
        $('.pagination .next a').prop('href', url + data.totalPages);

        if (data.currentPage === data.totalPages) {
            $('.pagination .last').addClass('disabled');
        }
    }
}

export default PaginationDom;