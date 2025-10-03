import toastr from "toastr"

class ToastrService {
    #defaultOptions = {
        progressBar: true,
        newestOnTop: true,
        timeOut: 5000
    }

    #toast;
    #additionalOptions = null;
    constructor() {
        if (!ToastrService.instance) {
            this.#toast = null;
            ToastrService.instance = this;
        }

        return ToastrService.instance;
    }

    showLoadingMessage() {
        this.#additionalOptions = {timeOut: 0};

        this.#generate(
            'info',
            Translator.trans('notifications.please_wait', null, 'messages', LOCALE)
        );
    }

    /**
     * @param {string} message
     * @param {string|null} title
     * @param {boolean} clearAll
     */
    success(message, title = null, clearAll = true) {
        this.#generate('success', message, title, clearAll);
    }

    /**
     * @param {string} message
     * @param {string|null} title
     * @param {boolean} clearAll
     */
    warning(message, title = null, clearAll = true) {
        this.#generate('warning', message, title, clearAll);
    }

    /**
     * @param {string} message
     * @param {string|null} title
     * @param {boolean} clearAll
     */
    error(message, title = null, clearAll = true) {
        this.#generate('error', message, title, clearAll);
    }

    remove() {
        toastr.clear();
        this.#toast = null;
        this.#additionalOptions = null;
    }

    /**
     * @param {object} options
     */
    addOptions(options) {
        this.#additionalOptions = options;
    }

    /**
     * @param {string} type
     * @param {string} message
     * @param {string|null} title
     * @param {boolean} clearAll
     */
    #generate(type, message, title = null, clearAll) {
        let toastrOptions = this.#defaultOptions;

        if (true === clearAll && this.#toast) {
            this.remove();
        }

        if (this.#additionalOptions) {
            toastrOptions = $.extend({}, this.#defaultOptions, this.#additionalOptions)
        }

        this.#toast = toastr[type](message, title, toastrOptions);
    }
}

const toastrService = new ToastrService();

Object.freeze(toastrService);

export default toastrService;
