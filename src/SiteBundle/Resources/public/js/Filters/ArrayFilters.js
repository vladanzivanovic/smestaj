class ArrayFilters {
    static getObjectByParams(data, filterParam, returnWithPosition) {
        var output = [];
        var property = filterParam.name;
        var propertyVal = filterParam.value;

        for (var i = 0; i < data.length; i++) {
            if (propertyVal == data[i][property]) {
                var selected = data[i];

                if (returnWithPosition) {
                    selected = {
                        data: data[i],
                        index: i
                    };
                }
                output.push(selected);
            }
        }
        return output;
    }
}

export default ArrayFilters;