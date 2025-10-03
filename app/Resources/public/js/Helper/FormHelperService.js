class FormHelperService {
    static sanitize(data) {
        return data.filter(obj => obj.value && obj.value.length > 0);
    }
}

export default FormHelperService;