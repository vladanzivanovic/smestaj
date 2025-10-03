class StringFilter {
    static stringFormat(str, arg, isAlphanumeric) {
        if (str) {
            if (arg.length > 0) {
                var args = arg,
                    pattern = isAlphanumeric ? /\$\{(.+?)\}/g : /\{(\d+)\}/g;

                return str.replace(pattern, function (v, i) {
                    return args[1 * i];
                });
            }
        }
    };
};

export default StringFilter;