import PaginationMapper from "../Mapper/PaginationMapper";

export default (() => {
    let Public = {},
        Private = {};

    Private.data = {};
    Private.event;
    Private.mapper = new PaginationMapper();

    /**
     * This method render pagination html
     * @param e
     * @param data
     */
    Public.init = function (e, data) {

        if(data.totalPages < 2) {
            return false;
        }
        Private.mapper.wrapper.removeClass('hide');
        Private.data = data;
        Private.event = e;

        tjq('.dynamic-li').remove();

        Private.setFirstPage();
        Private.setPrevPages();
        Private.setActivePage();
        Private.setNextPages();
        Private.setLastPage();
    };

    /**
     * Check and set disabled to prev and first pages or
     * add onclick event to li element for change pages
     * @return {*}
     */
    Private.setFirstPage = function () {
        Private.mapper.first.removeClass('disabled');
        Private.mapper.prev. removeClass('disabled');
        if(Private.data.disableFirst) {
            Private.mapper.first.addClass('disabled');
            Private.mapper.prev. addClass('disabled');
        }

        Private.mapper.prev.attr('data-page', Private.data.prevPage);

        return Private.data.disableFirst;
    };

    /**
     * Check and set disabled to next and last pages or
     * add onclick event to li element for change pages
     * @return {*}
     */
    Private.setLastPage = function () {
        Private.mapper.last.removeClass('disabled');
        Private.mapper.next.removeClass('disabled');
        if(Private.data.disableLast) {
            Private.mapper.last.addClass('disabled');
            Private.mapper.next.addClass('disabled');
        }

        Private.mapper.last.attr('data-page', Private.data.totalPages);
        Private.mapper.next.attr('data-page', Private.data.nextPage);

        return Private.data.disableLast;
    };

    Private.setActivePage = function () {
        tjq('.active a', Private.mapper.wrapper).text(Private.data.currentPage);
    };

    /**
     * Generate html for 3 previous pages
     */
    Private.setPrevPages = function () {
        var i = 1;
        if(Private.data.currentPage > 1){
            while(i <= 3){
                var prev = parseInt(Private.data.currentPage) - i;

                if(prev < 1)
                    break;

                tjq('<li>', { 'class': 'dynamic-li', 'data-page': prev }).append(
                    tjq('<a>', {
                        href: '#',
                        text: prev,
                    })
                ).insertAfter('.prev', Private.mapper.wrapper);

                i++;
            }
        }
    };

    /**
     * Generate html for 3 next pages
     */
    Private.setNextPages = function () {
        var i = 1;
        if(Private.data.currentPage < Private.data.totalPages){
            while(i <= 3){
                var next = parseInt(Private.data.currentPage) + i;

                if(next > Private.data.totalPages)
                    break;

                tjq('<li>', { 'class': 'dynamic-li', 'data-page': next }).append(
                    tjq('<a>', {
                        href: '#',
                        text: next,
                    })
                ).insertBefore('.next', Private.mapper.wrapper);

                i++;
            }
        }
    };

    return Public;
});
