const singleton = Symbol('FormService');

class FormService {
    constructor() {
        let Class = new.target;

        if (!Class[singleton]) {
            Class[singleton] = this;
        }

        return Class[singleton];
    }
    sanitize(data) {
        return data.filter(obj => obj.value && obj.value.length > 0);
    }
}

const formService = new FormService();

export default formService;